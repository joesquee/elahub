<?php

/**
 * Mobile nav walker for eLaHub
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
	exit;
}

class ELaHub_Mobile_Nav_Walker extends Walker_Nav_Menu
{
	private $parent_stack = array();

	public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
	{
		if (! is_object($item)) {
			return;
		}

		$item_id   = (int) $item->ID;
		$title     = apply_filters('the_title', $item->title, $item_id);
		$url       = ! empty($item->url) ? $item->url : '';
		$classes   = empty($item->classes) ? array() : (array) $item->classes;
		$has_child = in_array('menu-item-has-children', $classes, true);

		$output .= '<li class="border-b border-primary-border/70 last:border-b-0">';

		if ($has_child) {
			$submenu_id = 'elahub-mobile-submenu-' . $item_id;

			$this->parent_stack[] = array(
				'id' => $item_id,
			);

			$output .= '<button type="button" class="flex w-full items-center justify-between gap-4 border-0 bg-transparent py-5 text-left text-text shadow-none outline-none ring-0 transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2" data-elahub-mobile-subtoggle aria-expanded="false" aria-controls="' . esc_attr($submenu_id) . '">';
			$output .= '<span>' . esc_html($title) . '</span>';
			$output .= '<span class="flex min-h-6 min-w-6 shrink-0 items-center justify-center text-primary-dark" aria-hidden="true">';
			$output .= '<svg class="elahub-mobile-chevron h-5 w-5 transition-transform duration-200" viewBox="0 0 20 20" fill="none" focusable="false"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			$output .= '</span>';
			$output .= '</button>';
			return;
		}

		$link_classes = 'block py-5 text-text transition hover:text-primary-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-dark focus-visible:ring-offset-2';

		if ($depth > 0) {
			$link_classes .= ' pl-5';
		}

		$output .= '<a class="' . esc_attr($link_classes) . '" href="' . esc_url($url) . '">' . esc_html($title) . '</a>';
	}

	public function end_el(&$output, $item, $depth = 0, $args = null)
	{
		$output .= "</li>\n";
	}

	public function start_lvl(&$output, $depth = 0, $args = null)
	{
		$depth      = (int) $depth;
		$indent     = str_repeat("\t", $depth);
		$parent     = end($this->parent_stack);
		$parent_id  = isset($parent['id']) ? (int) $parent['id'] : 0;
		$submenu_id = $parent_id ? 'elahub-mobile-submenu-' . $parent_id : '';

		$output .= "\n{$indent}<ul id=\"" . esc_attr($submenu_id) . "\" class=\"hidden m-0 flex list-none flex-col pl-5 p-0\">\n";
	}

	public function end_lvl(&$output, $depth = 0, $args = null)
	{
		$indent = str_repeat("\t", (int) $depth);
		$output .= "{$indent}</ul>\n";

		if (! empty($this->parent_stack)) {
			array_pop($this->parent_stack);
		}
	}
}
