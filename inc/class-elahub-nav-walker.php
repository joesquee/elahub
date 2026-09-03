<?php

/**
 * Desktop nav walker for eLaHub
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

class ELaHub_Nav_Walker extends Walker_Nav_Menu
{
	private $parent_stack = array();
	private $children_cache = array();

	public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output)
	{
		if (! $element) {
			return;
		}

		$element->elahub_has_children = ! empty($children_elements[$element->ID]) || $this->item_has_menu_children((int) $element->ID);

		parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
	}

	public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
	{
		if (! is_object($item)) {
			return;
		}

		$depth        = (int) $depth;
		$item_id      = (int) $item->ID;
		$title        = apply_filters('the_title', $item->title, $item_id);
		$url          = ! empty($item->url) ? $item->url : '';
		$classes      = is_array($item->classes) ? $item->classes : array();
		$has_children = ! empty($item->elahub_has_children) || in_array('menu-item-has-children', $classes, true) || $this->item_has_menu_children($item_id);
		$is_top       = 0 === $depth;

		$output .= '<li class="elahub-nav-item' . ($is_top ? ' elahub-nav-item--top relative' : '') . ($has_children ? ' menu-item-has-children' : '') . '">';

		$chevron = '<svg class="elahub-nav-chevron h-4 w-4 transition-transform duration-200" viewBox="0 0 20 20" fill="none" aria-hidden="true" focusable="false"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		if ($is_top && $has_children) {
			$panel_id = 'elahub-submenu-' . $item_id;

			$this->parent_stack[] = array(
				'id' => $item_id,
			);

			$output .= '<button type="button" class="elahub-nav-toggle !cursor-pointer group inline-flex items-center gap-2 text-text transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2" data-elahub-menu-toggle aria-haspopup="true" aria-expanded="false" aria-controls="' . esc_attr($panel_id) . '">';
			$output .= '<span>' . esc_html($title) . '</span>' . $chevron;
			$output .= '</button>';
			return;
		}

		$link_classes = $is_top
			? 'inline-flex items-center text-text transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2'
			: 'elahub-submenu-link text-text underline underline-offset-[0.18em] transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2';

		$output .= '<a class="' . esc_attr($link_classes) . '" href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
	}

	public function end_el(&$output, $item, $depth = 0, $args = null)
	{
		$output .= "</li>\n";
	}

	public function start_lvl(&$output, $depth = 0, $args = null)
	{
		$depth  = (int) $depth;
		$indent = str_repeat("\t", $depth);

		if (0 === $depth) {
			$parent    = end($this->parent_stack);
			$parent_id = isset($parent['id']) ? (int) $parent['id'] : 0;
			$panel_id  = $parent_id ? 'elahub-submenu-' . $parent_id : '';
			$card      = $this->get_acf_card($parent_id);
			$has_card  = ! empty($card);
			$vector    = get_template_directory_uri() . '/assets/Vector.svg';

			$output .= "\n{$indent}<div id=\"" . esc_attr($panel_id) . "\" class=\"elahub-submenu hidden absolute left-1/2 top-full z-[90] mt-5 -translate-x-1/2 overflow-hidden border border-primary-border bg-white shadow-[0_20px_60px_rgba(16,24,40,0.14)]\">\n";
			$output .= '<div class="elahub-submenu-bg" aria-hidden="true">';
			$output .= '<img class="elahub-submenu-pattern" src="' . esc_url($vector) . '" alt="">';
			$output .= '<div class="elahub-submenu-glow"></div>';
			$output .= '</div>';
			$output .= '<div class="elahub-submenu-inner">';
			$output .= '<div class="elahub-submenu-layout' . ($has_card ? ' elahub-submenu-layout--with-card' : '') . '">';
			$output .= '<ul class="elahub-submenu-links m-0 flex list-none flex-col items-start p-0">';
			return;
		}

		$output .= "\n{$indent}<ul class=\"m-0 flex list-none flex-col items-start gap-4 p-0\">\n";
	}

	public function end_lvl(&$output, $depth = 0, $args = null)
	{
		$indent = str_repeat("\t", (int) $depth);

		if (0 === (int) $depth) {
			$parent  = end($this->parent_stack);
			$item_id = isset($parent['id']) ? (int) $parent['id'] : 0;
			$card    = $this->get_acf_card($item_id);

			$output .= "{$indent}</ul>\n";

			if (! empty($card)) {
				$output .= '<div class="elahub-submenu-card">';
				$output .= '<div class="elahub-submenu-card__content">';
				$output .= '<h3 class="elahub-submenu-card__title !text-lg">' . esc_html($card['title']) . '</h3>';
				$output .= '<p class="!text-sm">' . esc_html($card['text']) . '</p>';

				ob_start();
				get_template_part(
					'template-parts/components/button',
					null,
					array(
						'url'   => $card['url'],
						'label' => $card['button'],
						'class' => '',
					)
				);
				$output .= ob_get_clean();

				$output .= '</div>';
				$output .= '</div>';
			}

			$output .= '</div>';
			$output .= '</div>';
			$output .= "</div>\n";

			if (! empty($this->parent_stack)) {
				array_pop($this->parent_stack);
			}
			return;
		}

		$output .= "{$indent}</ul>\n";
	}

	private function item_has_menu_children($item_id)
	{
		$item_id = (int) $item_id;

		if (! $item_id) {
			return false;
		}

		if (isset($this->children_cache[$item_id])) {
			return $this->children_cache[$item_id];
		}

		$children = get_posts(
			array(
				'post_type'              => 'nav_menu_item',
				'posts_per_page'         => 1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'meta_query'             => array(
					array(
						'key'   => '_menu_item_menu_item_parent',
						'value' => (string) $item_id,
					),
				),
			)
		);

		$this->children_cache[$item_id] = ! empty($children);
		return $this->children_cache[$item_id];
	}

	private function get_acf_card($item_id)
	{
		if (! function_exists('get_field') || ! $item_id) {
			return array();
		}

		$acf_id = 'menu_item_' . $item_id;

		if (! get_field('enable_mega_menu_card', $acf_id)) {
			return array();
		}

		$title  = trim((string) get_field('mega_menu_card_title', $acf_id));
		$text   = trim(wp_strip_all_tags((string) get_field('mega_menu_card_text', $acf_id)));
		$button = trim((string) get_field('mega_menu_card_button_label', $acf_id));
		$link   = get_field('mega_menu_card_button_link', $acf_id);
		$url    = is_array($link) && ! empty($link['url']) ? $link['url'] : '';

		if ('' === $title || '' === $text || '' === $button || '' === $url) {
			return array();
		}

		return array(
			'title'  => $title,
			'text'   => $text,
			'button' => $button,
			'url'    => $url,
		);
	}
}
