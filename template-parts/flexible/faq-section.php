<?php

/**
 * FAQ Section — flexible content layout
 *
 * ACF layout name: faq_section
 *
 * @package elahub
 */

$use_global = (bool) get_sub_field('use_global_faq');

$default_badge_text  = elahub_get_option_field('faq_default_badge_text', 'FAQs');
$default_badge_icon  = elahub_get_option_field('faq_default_badge_icon_class', 'fa-solid fa-circle-question');
$default_heading     = elahub_get_option_field('faq_default_heading', 'Frequently asked questions');
$default_body        = elahub_get_option_field('faq_default_body', '');
$default_source      = elahub_get_option_field('faq_default_source', 'all');
$default_cat_slug    = elahub_get_option_field('faq_default_category_slug', '');
$default_manual      = elahub_get_option_field('faq_default_manual_posts', []);

$badge_text = $use_global ? $default_badge_text : trim((string) (get_sub_field('faq_badge_text') ?: ''));
$badge_icon = $use_global ? $default_badge_icon : trim((string) (get_sub_field('faq_badge_icon_class') ?: 'fa-solid fa-circle-question'));
$heading    = $use_global ? $default_heading    : trim((string) (get_sub_field('faq_heading') ?: ''));
$body       = $use_global ? $default_body       : trim((string) (get_sub_field('faq_body') ?: ''));
$source     = $use_global ? $default_source     : (get_sub_field('faq_source') ?: 'all');
$cat_slug   = $use_global ? $default_cat_slug   : trim((string) (get_sub_field('faq_category_slug') ?: ''));
$manual_ids = $use_global ? $default_manual     : get_sub_field('faq_manual_posts');
$limit      = (int) (get_sub_field('faq_limit') ?: 0);

$query_args = [
	'post_type'              => 'faq',
	'post_status'            => 'publish',
	'posts_per_page'         => $limit > 0 ? $limit : -1,
	'orderby'                => 'menu_order title',
	'order'                  => 'ASC',
	'ignore_sticky_posts'    => true,
	'no_found_rows'          => true,
	'update_post_meta_cache' => false,
	'update_post_term_cache' => false,
];

if ('manual' === $source && ! empty($manual_ids)) {
	$ids                    = array_map('intval', is_array($manual_ids) ? $manual_ids : [$manual_ids]);
	$query_args['post__in'] = $ids;
	$query_args['orderby']  = 'post__in';
} elseif ('by_category' === $source && $cat_slug) {
	$query_args['tax_query'] = [
		[
			'taxonomy' => 'faq_category',
			'field'    => 'slug',
			'terms'    => $cat_slug,
		],
	];
}

$faq_query = new WP_Query($query_args);

if (! $heading && ! $faq_query->have_posts()) {
	return;
}

$section_uid = 'faq-' . wp_unique_id();
?>

<section
	id="<?php echo esc_attr($section_uid); ?>"
	class="relative py-8 md:py-12 lg:py-16">

	<?php
	get_template_part('template-parts/components/section-bg', null, ['side' => 'left',  'show_dots' => true]);
	get_template_part('template-parts/components/section-bg', null, ['side' => 'right', 'show_dots' => false]);
	?>

	<div class="container relative z-10">
		<div class="flex flex-col items-center gap-10">

			<?php /* ---- Header — centred per Figma ---- */ ?>
			<div class="flex max-w-2xl flex-col items-start gap-4 text-left md:items-center md:text-center">

				<?php if ($badge_text) : ?>
					<?php
					get_template_part(
						'template-parts/components/eyebrow',
						null,
						['text' => $badge_text, 'icon_class' => $badge_icon]
					);
					?>
				<?php endif; ?>

				<?php if ($heading) : ?>
					<h2 id="<?php echo esc_attr($section_uid); ?>-heading" class="!mb-0">
						<?php echo esc_html($heading); ?>
					</h2>
				<?php endif; ?>

				<?php if ($body) : ?>
					<p class="!mb-0">
						<?php echo nl2br(esc_html($body)); ?>
					</p>
				<?php endif; ?>

			</div>

			<?php /* ---- Accordion ---- */ ?>
			<?php /* Figma accordion width: 1180px ÷ 18px base = 65.556rem */ ?>
			<?php if ($faq_query->have_posts()) : ?>
				<div
					class="w-full max-w-[65.556rem] flex flex-col gap-4"
					role="list"
					data-elahub-faq="<?php echo esc_attr($section_uid); ?>">

					<?php $index = 0;
					while ($faq_query->have_posts()) : $faq_query->the_post();
						$item_id  = $section_uid . '-item-' . $index;
						$panel_id = $item_id . '-panel';
						$is_open  = 0 === $index;
					?>

						<div
							class="rounded-lg border border-primary-border bg-primary-softest focus-within:outline-none focus-within:ring-2 focus-within:ring-primary-dark focus-within:ring-offset-2"
							role="listitem">

							<button
								type="button"
								class="elahub-faq__trigger flex w-full cursor-pointer items-center gap-8 px-[1.625rem] py-4 text-left focus:outline-none focus-visible:outline-none"
								aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
								aria-controls="<?php echo esc_attr($panel_id); ?>">

								<?php /* Question text — underlined when open via JS toggling elahub-faq__question--open */ ?>
								<span class="elahub-faq__question flex-1 !text-base !font-bold !leading-[1.8] !tracking-[-0.01em] text-text transition-all <?php echo $is_open ? 'underline underline-offset-2' : ''; ?>">
									<?php the_title(); ?>
								</span>

								<span
									class="elahub-faq__icon flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-light transition-transform duration-300 <?php echo $is_open ? 'rotate-180' : ''; ?>"
									aria-hidden="true">
									<i class="fa-solid fa-chevron-down !text-xs text-primary-dark"></i>
								</span>

							</button>

							<div
								id="<?php echo esc_attr($panel_id); ?>"
								class="elahub-faq__panel overflow-hidden transition-[max-height] duration-500 ease-in-out"
								style="max-height: <?php echo $is_open ? '2000px' : '0px'; ?>;"
								aria-hidden="<?php echo $is_open ? 'false' : 'true'; ?>">
								<div class="elahub-rich-text px-[1.625rem] pb-5 pt-1 !text-[16px] !leading-[1.8] !tracking-[-0.01em] text-text/80">
									<?php echo wp_kses_post(get_the_content()); ?>
								</div>
							</div>

						</div>

					<?php $index++;
					endwhile;
					wp_reset_postdata(); ?>

				</div>
			<?php endif; ?>

		</div>
	</div>
</section>

<script>
	(function() {
		var container = document.querySelector('[data-elahub-faq="<?php echo esc_js($section_uid); ?>"]');
		if (!container) return;

		var triggers = Array.from(container.querySelectorAll('.elahub-faq__trigger'));

		triggers.forEach(function(trigger) {
			trigger.addEventListener('click', function() {
				var isOpen = trigger.getAttribute('aria-expanded') === 'true';
				var panelId = trigger.getAttribute('aria-controls');
				var panel = panelId ? document.getElementById(panelId) : null;
				var icon = trigger.querySelector('.elahub-faq__icon');
				var question = trigger.querySelector('.elahub-faq__question');

				trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');

				if (panel) {
					panel.style.maxHeight = isOpen ? '0px' : panel.scrollHeight + 'px';
					panel.setAttribute('aria-hidden', isOpen ? 'true' : 'false');
				}

				if (icon) {
					icon.classList.toggle('rotate-180', !isOpen);
				}

				if (question) {
					question.classList.toggle('underline', !isOpen);
					question.classList.toggle('underline-offset-2', !isOpen);
				}
			});

			trigger.addEventListener('keydown', function(e) {
				var idx = triggers.indexOf(trigger);

				if (e.key === 'ArrowDown') {
					e.preventDefault();
					(triggers[idx + 1] || triggers[0]).focus();
				}
				if (e.key === 'ArrowUp') {
					e.preventDefault();
					(triggers[idx - 1] || triggers[triggers.length - 1]).focus();
				}
				if (e.key === 'Home') {
					e.preventDefault();
					triggers[0].focus();
				}
				if (e.key === 'End') {
					e.preventDefault();
					triggers[triggers.length - 1].focus();
				}
			});
		});
	}());
</script>