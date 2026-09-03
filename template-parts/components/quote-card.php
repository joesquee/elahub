<?php

/**
 * Quote card component
 *
 * Two variants matched to Figma, using Tailwind standard classes and
 * input.css design tokens throughout. Arbitrary values only where no
 * token or standard class exists.
 *
 * DARK (node 1165:71916) — CTA section
 *   bg: #0d526d (no token — inline style)
 *   border: rgba(255,255,255,0.1) — white/10
 *   radius: rounded-lg + rounded-br-[33px] (one-off px value)
 *   gap: gap-6, padding: px-6 py-8
 *   Avatar: h-16 w-16 (≈72px), bg-primary-light, initials text-xl bold
 *   Name:   text-xl font-semibold (≈22.5px, Figma 25px)
 *   Role:   text-base font-semibold, white/90
 *   Quote:  text-base font-normal, white/85, leading-[1.8]
 *   Company:text-base font-bold, white/90
 *   Stars:  text-xl, text-primary-light / white/20
 *
 * LIGHT (node 637:15079) — testimonials section
 *   bg: bg-surface-alt (#f1f1f1 ≈ Figma #f1f3f4)
 *   border: border-primary-border
 *   radius: rounded-lg (uniform)
 *   gap: gap-4, padding: px-6 py-8
 *   Avatar: h-14 w-14 (≈63px), bg-primary-light/50, initials text-lg bold
 *   Name:   text-lg font-bold (≈20px, Figma 21px), text-primary-dark
 *   Role:   text-[16px] font-normal, text-primary-dark
 *   Quote:  text-base font-normal, text-primary-dark, leading-[1.8]
 *   Company:text-[16px] font-bold, text-primary
 *   Stars:  text-base, text-primary / text-primary-border
 *
 * @package elahub
 *
 * @param array $args {
 *   @type string $variant       'dark' (default) or 'light'
 *   @type string $quote         Quote text (required)
 *   @type string $author_name   Author name
 *   @type string $author_role   Job title / role
 *   @type string $organisation  Organisation / company
 *   @type array  $avatar        ACF image array — falls back to initials
 *   @type int    $stars         1–5, default 5
 * }
 */

$args         = $args ?? [];
$variant      = $args['variant'] ?? 'dark';
$quote        = trim((string) ($args['quote'] ?? ''));
$author_name  = trim((string) ($args['author_name'] ?? ''));
$author_role  = trim((string) ($args['author_role'] ?? ''));
$organisation = trim((string) ($args['organisation'] ?? ''));
$avatar       = $args['avatar'] ?? null;
$stars        = max(0, min(5, (int) ($args['stars'] ?? 5)));

if (! $quote) {
	return;
}

$is_dark = 'dark' === $variant;

$initials = '';
if ($author_name) {
	$parts    = explode(' ', $author_name);
	$initials = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
}

$avatar_src = '';
$avatar_alt = $author_name;
if (is_array($avatar) && ! empty($avatar['url'])) {
	$avatar_src = $avatar['url'];
	$avatar_alt = $avatar['alt'] ?: $author_name;
}
?>

<?php if ($is_dark) : ?>

	<div
		class="flex flex-col gap-6 rounded-lg rounded-br-[33px] border px-6 py-8"
		style="background-color:#0d526d; border-color:rgba(255,255,255,0.1);">

		<?php if ($author_name) : ?>
			<div class="flex items-center gap-4">

				<div class="shrink-0">
					<?php if ($avatar_src) : ?>
						<img
							src="<?php echo esc_url($avatar_src); ?>"
							alt="<?php echo esc_attr($avatar_alt); ?>"
							class="h-16 w-16 rounded-full object-cover object-center"
							loading="lazy"
							decoding="async">
					<?php else : ?>
						<div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-light">
							<span class="!text-xl !font-bold !leading-none !text-primary-dark" aria-hidden="true">
								<?php echo esc_html($initials); ?>
							</span>
						</div>
					<?php endif; ?>
				</div>

				<div class="flex min-w-0 flex-col gap-1 text-white/90">
					<h3 class="!m-0 !text-xl !font-semibold !leading-[1.42] !text-white">
						<?php echo esc_html($author_name); ?>
					</h3>
					<?php if ($author_role) : ?>
						<p class="!m-0 !text-base !font-semibold !leading-[1.8] !tracking-[-0.01em]">
							<?php echo esc_html($author_role); ?>
						</p>
					<?php endif; ?>
				</div>

			</div>
		<?php endif; ?>

		<div class="flex flex-col gap-2">
			<blockquote class="!m-0">
				<p class="!m-0 !text-base !font-normal !leading-[1.8] !tracking-[-0.01em] text-white/85">
					<?php echo esc_html($quote); ?>
				</p>
			</blockquote>
			<?php if ($organisation) : ?>
				<p class="!m-0 !text-base !font-bold !leading-[1.8] !tracking-[-0.01em] text-white/90">
					<?php echo esc_html($organisation); ?>
				</p>
			<?php endif; ?>
		</div>

		<?php if ($stars > 0) : ?>
			<div class="flex items-center gap-1.5" aria-label="<?php echo esc_attr($stars); ?> out of 5 stars">
				<?php for ($i = 0; $i < 5; $i++) : ?>
					<i
						class="fa-solid fa-star !text-xl <?php echo $i < $stars ? 'text-primary-light' : 'text-white/20'; ?>"
						aria-hidden="true"></i>
				<?php endfor; ?>
			</div>
		<?php endif; ?>

	</div>

<?php else : ?>

	<div class="flex flex-col gap-4 rounded-lg border border-primary-border bg-surface-alt px-6 py-8">

		<?php if ($author_name) : ?>
			<div class="flex items-center gap-3">

				<div class="shrink-0">
					<?php if ($avatar_src) : ?>
						<img
							src="<?php echo esc_url($avatar_src); ?>"
							alt="<?php echo esc_attr($avatar_alt); ?>"
							class="h-14 w-14 rounded-full object-cover object-center"
							loading="lazy"
							decoding="async">
					<?php else : ?>
						<div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary-light/50">
							<span class="!text-lg !font-bold !leading-none !text-primary-dark" aria-hidden="true">
								<?php echo esc_html($initials); ?>
							</span>
						</div>
					<?php endif; ?>
				</div>

				<div class="flex min-w-0 flex-col gap-0.5">
					<h3 class="!m-0 !text-lg !font-bold !leading-[1.5] !tracking-[-0.01em] !text-primary-dark">
						<?php echo esc_html($author_name); ?>
					</h3>
					<?php if ($author_role) : ?>
						<p class="!m-0 !text-[16px] !font-normal !leading-[1.8] !tracking-[-0.01em] !text-primary-dark">
							<?php echo esc_html($author_role); ?>
						</p>
					<?php endif; ?>
				</div>

			</div>
		<?php endif; ?>

		<div class="flex flex-col gap-2">
			<blockquote class="!m-0">
				<p class="!m-0 !text-base !font-normal !leading-[1.8] !tracking-[-0.01em] !text-primary-dark">
					<?php echo esc_html($quote); ?>
				</p>
			</blockquote>
			<?php if ($organisation) : ?>
				<p class="!m-0 !text-[16px] !font-bold !leading-[1.8] !tracking-[-0.01em] !text-primary">
					<?php echo esc_html($organisation); ?>
				</p>
			<?php endif; ?>
		</div>

		<?php if ($stars > 0) : ?>
			<div class="flex items-center gap-1.5" aria-label="<?php echo esc_attr($stars); ?> out of 5 stars">
				<?php for ($i = 0; $i < 5; $i++) : ?>
					<i
						class="fa-solid fa-star !text-base <?php echo $i < $stars ? 'text-primary' : 'text-primary-border'; ?>"
						aria-hidden="true"></i>
				<?php endfor; ?>
			</div>
		<?php endif; ?>

	</div>

<?php endif; ?>