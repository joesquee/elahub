<?php

/**
 * Theme self-deploy over HTTPS.
 *
 * The deploy pipeline rsyncs over SSH to Hostinger on port 65002. That port is
 * unreliable from GitHub runners: on the squee-support repo, runs 12, 13, 18,
 * 24, 28, 29 and 30 all died before touching a file, some of them mid-session,
 * which meant hand retriggering and — worse — believing a change was live when
 * it had never shipped. Retrying inside the workflow did not help, because the
 * port can be unreachable for minutes at a stretch.
 *
 * HTTPS to the site itself is the one path already known to be reliable: it is
 * how the site serves every page. So the runner posts a zip of the theme here
 * and this unpacks it. No SSH, no host keys, no keyscan. SSH stays as a
 * fallback, which is also how the very first deploy of this file gets here.
 *
 * The route, header and constant keep the Squee names used by the other
 * Squee-managed sites on purpose, so all four repos can share one workflow
 * file that differs only in URL, rsync target and website UID. Divergence
 * between them has already cost a day of debugging once.
 *
 * Security. This endpoint writes PHP into the active theme, so anyone holding
 * the token can run code on the site. It is therefore:
 *   - disabled unless SQUEE_DEPLOY_TOKEN is defined in wp-config.php,
 *   - compared with hash_equals against a header, never a query string,
 *   - refused over plain HTTP,
 *   - validated: the payload must look like this theme (style.css with the
 *     right Theme Name, plus functions.php) before a single file is copied,
 *   - additive, never deleting, matching the old rsync's behaviour so a bad
 *     payload cannot empty the theme.
 *
 * Add to wp-config.php:
 *     define('SQUEE_DEPLOY_TOKEN', '<a long random string>');
 * and store the same value as the DEPLOY_TOKEN secret on the repo.
 *
 * @package elahub
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Must appear in the payload's style.css header for it to be accepted. */
if (!defined('SQUEE_DEPLOY_THEME_NAME')) {
    define('SQUEE_DEPLOY_THEME_NAME', 'elahub');
}

if (!function_exists('squee_deploy_token')) {
    /** The shared secret ('' = endpoint disabled). */
    function squee_deploy_token(): string
    {
        return defined('SQUEE_DEPLOY_TOKEN') ? (string) SQUEE_DEPLOY_TOKEN : '';
    }
}

if (!function_exists('squee_deploy_copy_dir')) {
    /**
     * Copy a directory tree over another, creating what is missing.
     * Additive: nothing in the destination is removed.
     *
     * @return array{files:int,bytes:int}
     */
    function squee_deploy_copy_dir(string $from, string $to): array
    {
        $files = 0;
        $bytes = 0;

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($items as $item) {
            $rel  = substr($item->getPathname(), strlen($from) + 1);
            $dest = $to . '/' . $rel;

            if ($item->isDir()) {
                if (!is_dir($dest)) {
                    wp_mkdir_p($dest);
                }
                continue;
            }
            if (copy($item->getPathname(), $dest)) {
                $files++;
                $bytes += (int) $item->getSize();
            }
        }

        return array('files' => $files, 'bytes' => $bytes);
    }
}

if (!function_exists('squee_deploy_unzip')) {
    /**
     * Extract a zip without touching WP_Filesystem.
     *
     * Prefers the ZipArchive extension and falls back to the PclZip copy that
     * ships with WordPress — the same fallback unzip_file() itself uses.
     *
     * @return true|WP_Error
     */
    function squee_deploy_unzip(string $file, string $to)
    {
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($file) === true) {
                $ok = $zip->extractTo($to);
                $zip->close();
                if ($ok) {
                    return true;
                }
            }
            // fall through to PclZip rather than failing outright
        }

        // The theme's own copy of PclZip (renamed so it can never collide
        // with another). The host's shared core build at
        // /opt/h5g/flavors/wp-7.0.4/ has a PclZip that rejects every valid
        // archive with BAD_FORMAT (13 Aug 2026); this site's core files are
        // not its own, so the only unzipper we can trust is one we ship.
        require_once __DIR__ . '/class-squee-pclzip.php';
        $archive = new Squee_PclZip($file);

        // PclZip parses the archive with plain string functions. With
        // mbstring.func_overload active those become multibyte-aware and the
        // central directory parse fails as PCLZIP_ERR_BAD_FORMAT (-10),
        // "Invalid archive structure" — which is exactly what this endpoint
        // returned on every payload while the other three sites, which go
        // through core's unzip_file(), were fine. Core guards its own PclZip
        // call the same way in _unzip_file_pclzip(); this mirrors it.
        mbstring_binary_safe_encoding();
        $result  = $archive->extract(PCLZIP_OPT_PATH, $to, PCLZIP_OPT_REPLACE_NEWER);
        reset_mbstring_encoding();
        if ($result === 0) {
            return new WP_Error(
                'squee_deploy_unzip',
                'Could not unzip the payload: ' . $archive->errorInfo(true)
                    . ' [received=' . filesize($file) . ' bytes, md5=' . md5_file($file)
                    . ', php=' . PHP_VERSION . ']',
                array('status' => 500)
            );
        }
        return true;
    }
}

add_action('rest_api_init', function () {

    register_rest_route('squee/v1', '/self-deploy', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'permission_callback' => function (WP_REST_Request $req) {
            $expected = squee_deploy_token();
            if ($expected === '') {
                return new WP_Error(
                    'squee_deploy_disabled',
                    'SQUEE_DEPLOY_TOKEN is not set on this site, so deploys are disabled.',
                    array('status' => 501)
                );
            }
            if (!is_ssl()) {
                return new WP_Error('squee_deploy_insecure', 'HTTPS required.', array('status' => 403));
            }
            $given = (string) $req->get_header('x-squee-deploy-token');
            if ($given === '' || !hash_equals($expected, $given)) {
                return new WP_Error('squee_deploy_forbidden', 'Bad deploy token.', array('status' => 401));
            }
            return true;
        },
        'callback'            => function (WP_REST_Request $req) {
            // This endpoint's reply must be machine-readable JSON. With
            // WP_DEBUG display on, plugin deprecation notices (All-In-One WP
            // Migration's iterator classes, loaded mid-request) get printed
            // straight into the response body, which turned the deploy reply
            // into HTML on 13 Aug 2026 and made runs unreadable. Site-wide
            // debug settings live in wp-config.php and have been clobbered by
            // concurrent edits once already, so the endpoint defends itself.
            @ini_set('display_errors', '0');

            $body = $req->get_body();
            if ($body === '' || strlen($body) < 1024) {
                return new WP_Error('squee_deploy_empty', 'No zip payload received.', array('status' => 400));
            }

            // Transport hardening. Something between the runner and here
            // rewrites raw binary request bodies: on nlee run #5 the runner
            // sent 8344 bytes (md5 d56ca256..., unzip -t clean) and this
            // endpoint received 8464 bytes (md5 3b705ec5...) — 120 bytes ADDED
            // to a byte-perfect archive. That is why every PclZip theory was a
            // dead end: the unzipper was being handed a corrupted file and was
            // right to reject it. It is also why swapping PHP versions changed
            // nothing.
            //
            // So the archive now travels base64-encoded, which is pure ASCII
            // and survives any charset conversion or re-encoding in the path.
            // Raw zips are still accepted, both so an older runner keeps
            // working and so the two can be compared.
            $rawLen = strlen($body);
            $rawMd5 = md5($body);
            $wasBase64 = false;
            if (strncmp($body, "PK\x03\x04", 4) !== 0) {
                $decoded = base64_decode(preg_replace('/\s+/', '', $body), true);
                if ($decoded !== false && strncmp($decoded, "PK\x03\x04", 4) === 0) {
                    $body     = $decoded;
                    $wasBase64 = true;
                }
            }
            // Where do the extra bytes come from? Two candidates left, and
            // this tells them apart. If php://input matches what the runner
            // sent but get_body() does not, WordPress is rewriting the body
            // and the network is innocent. If php://input is already long,
            // the bytes were added before PHP ever saw them.
            //
            // The head and tail hex show WHAT was added: chunked-transfer
            // framing, a multipart boundary, an appended block or an injected
            // prefix all look completely different here.
            $phpInput    = (string) @file_get_contents('php://input');
            $phpInputLen = strlen($phpInput);
            $sample      = $phpInputLen ? $phpInput : $body;
            $headHex     = bin2hex(substr($sample, 0, 48));
            $tailHex     = bin2hex(substr($sample, -48));
            $headTxt     = preg_replace('/[^\x20-\x7e]/', '.', substr($sample, 0, 48));
            $tailTxt     = preg_replace('/[^\x20-\x7e]/', '.', substr($sample, -48));

            $transport = sprintf(
                'transport=%s, body=%d/%s, decoded=%d/%s, phpinput=%d/%s, cl=%s, te=%s, ce=%s, ct=%s'
                    . ', head=%s "%s", tail=%s "%s"',
                $wasBase64 ? 'base64' : 'raw',
                $rawLen,
                $rawMd5,
                strlen($body),
                md5($body),
                $phpInputLen,
                $phpInputLen ? md5($phpInput) : '-',
                isset($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : '-',
                isset($_SERVER['HTTP_TRANSFER_ENCODING']) ? $_SERVER['HTTP_TRANSFER_ENCODING'] : '-',
                isset($_SERVER['HTTP_CONTENT_ENCODING']) ? $_SERVER['HTTP_CONTENT_ENCODING'] : '-',
                isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '-',
                $headHex,
                $headTxt,
                $tailHex,
                $tailTxt
            );

            // If php://input is intact, prefer it: that alone fixes the deploy
            // and proves the diagnosis in the same run.
            if ($phpInputLen && $phpInputLen !== $rawLen) {
                $candidate = $phpInput;
                if (strncmp($candidate, "PK\x03\x04", 4) !== 0) {
                    $dec = base64_decode(preg_replace('/\s+/', '', $candidate), true);
                    if ($dec !== false && strncmp($dec, "PK\x03\x04", 4) === 0) {
                        $candidate = $dec;
                    }
                }
                if (strncmp($candidate, "PK\x03\x04", 4) === 0) {
                    $body       = $candidate;
                    $transport .= ', usedPhpInput=yes';
                }
            }

            require_once ABSPATH . 'wp-admin/includes/file.php';

            // Deliberately NOT WP_Filesystem()/unzip_file(). When
            // get_filesystem_method() decides direct writes are unavailable,
            // WP_Filesystem() builds the FTP transport and blocks on a socket
            // connect until it times out. On this site that hung every deploy
            // for 90s+ without writing a single file, while a bad-token
            // request to the same route answered in under 200ms at payloads up
            // to 60KB — which is what proved the stall was inside the handler
            // rather than in front of it.
            $zip = wp_tempnam('squee-deploy.zip');
            if (!$zip || file_put_contents($zip, $body) === false) {
                return new WP_Error('squee_deploy_tmp', 'Could not write the upload to disk.', array('status' => 500));
            }

            $work = trailingslashit(get_temp_dir()) . 'squee-deploy-' . wp_generate_password(8, false, false);
            if (!wp_mkdir_p($work)) {
                @unlink($zip);
                return new WP_Error('squee_deploy_tmp', 'Could not create a work directory.', array('status' => 500));
            }

            // Core's unzip_file(), exactly as on the three working sites.
            // FS_METHOD is 'direct' in this site's wp-config, so WP_Filesystem
            // cannot fall into the FTP transport the hand-rolled extractor was
            // written to avoid. The hand-rolled PclZip call returned
            // PCLZIP_ERR_BAD_FORMAT on byte-perfect payloads on BOTH PHP 8.5
            // and 8.3 (13 Aug 2026), while this path accepts identical zips.
            $unzipped = squee_deploy_unzip($zip, $work);
            if (is_wp_error($unzipped)) {
                // Name the actual PclZip implementation in play. unzip_file()
                // only loads core's copy if no class called PclZip exists yet,
                // so a plugin that bundles its own (the All-In-One WP
                // Migration fork loads its libs into every request) silently
                // substitutes it for core's.
                $pclzip_file = class_exists('PclZip')
                    ? (new ReflectionClass('PclZip'))->getFileName()
                    : 'not loaded';
                $unzipped->add_data(array('status' => 500));
                $unzipped = new WP_Error(
                    'squee_deploy_unzip',
                    $unzipped->get_error_message()
                        . ' [' . $transport
                        . ', ondisk=' . filesize($zip)
                        . ' bytes, php=' . PHP_VERSION . ']',
                    array('status' => 500)
                );
                @unlink($zip);
                return $unzipped;
            }
            @unlink($zip);

            // The zip may contain the theme at its root or one level down.
            $root = $work;
            if (!file_exists($root . '/style.css')) {
                foreach ((array) glob($work . '/*', GLOB_ONLYDIR) as $dir) {
                    if (file_exists($dir . '/style.css')) {
                        $root = $dir;
                        break;
                    }
                }
            }

            // Validate before touching anything. A payload that is not this
            // theme must never be copied over the live one.
            $style = $root . '/style.css';
            if (!file_exists($style) || !file_exists($root . '/functions.php')) {
                return new WP_Error(
                    'squee_deploy_not_a_theme',
                    'Payload does not contain style.css and functions.php.',
                    array('status' => 422)
                );
            }
            $header = (string) file_get_contents($style, false, null, 0, 2048);
            if (stripos($header, SQUEE_DEPLOY_THEME_NAME) === false) {
                return new WP_Error(
                    'squee_deploy_wrong_theme',
                    'style.css is not the ' . SQUEE_DEPLOY_THEME_NAME . ' theme.',
                    array('status' => 422)
                );
            }

            $copied = squee_deploy_copy_dir($root, untrailingslashit(get_template_directory()));

            // Clear bytecode so the new PHP is what actually runs. Without this
            // a deploy can appear to succeed while the old code keeps serving.
            $opcache = false;
            if (function_exists('opcache_reset')) {
                $opcache = (bool) @opcache_reset();
            }

            return rest_ensure_response(array(
                'deployed'     => true,
                'files'        => $copied['files'],
                'bytes'        => $copied['bytes'],
                'commit'       => (string) $req->get_header('x-squee-deploy-commit'),
                'opcacheReset' => $opcache,
                'transport'    => $transport,
                'theme'        => get_template(),
                'at'           => wp_date('Y-m-d H:i:s'),
            ));
        },
    ));
});
