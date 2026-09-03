<?php

/**
 * CTA Quotes — quotes grid inside the dark blue CTA section.
 *
 * Quote source:
 *   organisations  → pulls from Theme Settings > Global Content > Organisation Quotes
 *   practitioners  → pulls from Theme Settings > Global Content > Learning Practitioner Quotes
 *   custom         → uses quotes entered directly on this block
 *
 * @package elahub
 */

$heading    = trim((string) (get_sub_field('heading') ?: ''));
$btn_label  = trim((string) (get_sub_field('button_label') ?: ''));
$btn_url    = trim((string) (get_sub_field('button_url') ?: ''));
$btn_aria   = trim((string) (get_sub_field('button_aria_label') ?: ''));
$source     = get_sub_field('quote_source') ?: 'organisations';

// Resolve quotes from the correct source
switch ($source) {
	case 'organisations':
		$quotes_raw = function_exists('get_field')
			? (get_field('default_quotes_organisations', 'option') ?: [])
			: [];
		break;

	case 'practitioners':
		$quotes_raw = function_exists('get_field')
			? (get_field('default_quotes_practitioners', 'option') ?: [])
			: [];
		break;

	case 'custom':
	default:
		$quotes_raw = get_sub_field('quotes') ?: [];
		break;
}

if (empty($quotes_raw) && ! $heading) {
	return;
}
?>

<div class="px-6 md:px-8 lg:px-8 py-8 md:py-10 lg:py-16">

	<?php if ($heading || ($btn_label && $btn_url)) : ?>
		<div class="mb-10 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between lg:gap-12">
			<?php if ($heading) : ?>
				<h2 class="!text-white lg:max-w-2xl"><?php echo esc_html($heading); ?></h2>
			<?php endif; ?>

			<?php if ($btn_label && $btn_url) : ?>
				<div class="self-start">
					<?php
					get_template_part(
						'template-parts/components/button',
						null,
						[
							'url'        => $btn_url,
							'label'      => $btn_label,
							'variant'    => 'white',
							'aria_label' => $btn_aria ?: $btn_label,
						]
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if (! empty($quotes_raw)) : ?>
		<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
			<?php foreach ($quotes_raw as $quote_row) : ?>
				<div>
					<?php
					get_template_part(
						'template-parts/components/quote-card',
						null,
						[
							'variant'      => 'dark',
							'quote'        => $quote_row['quote'] ?? '',
							'author_name'  => $quote_row['author_name'] ?? '',
							'author_role'  => $quote_row['author_role'] ?? '',
							'organisation' => $quote_row['organisation'] ?? '',
							'avatar'       => $quote_row['avatar'] ?? null,
							'stars'        => (int) ($quote_row['stars'] ?? 5),
						]
					);
					?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

</div>