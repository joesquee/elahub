<?php

/**
 * Reusable decorative background component — dot pattern + glow.
 *
 * Usage:
 *   get_template_part( 'template-parts/components/section-bg', null, [
 *       'side' => 'left',      // 'left' or 'right' (default: 'right')
 *       'show_dots' => true,   // optional, default true
 *   ] );
 *
 * The parent section needs `position:relative` and `overflow-x:hidden`.
 * Elements bleed freely in the vertical direction by design.
 *
 * @package elahub
 */

$side      = (isset($args['side']) && 'left' === $args['side']) ? 'left' : 'right';
$show_dots = ! isset($args['show_dots']) || (bool) $args['show_dots'];
$dot_svg   = get_template_directory_uri() . '/assets/main-circle-dots.svg';

/*
 * Values derived from Figma (1728px outer frame):
 *   Glow:  w=944px → 55%, bleeds off edge → right/left: -30%
 *   Dots:  w=985px → 57%, bleeds further  → right/left: -46%
 *   Both start slightly above section top  → top: -4% / -15rem
 */
?>
<div class="pointer-events-none absolute inset-0" aria-hidden="true">
	<div style="position:absolute; width:55%; aspect-ratio:1; <?php echo $side; ?>:-30%; top:-15rem; border-radius:9999px; background:rgba(0,85,125,0.2); filter:blur(11rem);"></div>
	<?php if ($show_dots) : ?>
		<img
			src="<?php echo esc_url($dot_svg); ?>"
			alt=""
			width="985"
			height="986"
			loading="lazy"
			decoding="async"
			style="position:absolute; width:57%; <?php echo $side; ?>:-46%; top:-4%; max-width:none; opacity:0.2;">
	<?php endif; ?>
</div>