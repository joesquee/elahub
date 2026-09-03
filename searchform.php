<?php

/**
 * Custom search form
 *
 * Overrides the WordPress default to render a visible <label> so the input is
 * labelled for all users, not just screen-reader users.
 *
 * Used everywhere get_search_form() is called (404.php, search.php, etc.)
 *
 * @package elahub
 */

$unique_id = wp_unique_id( 'search-form-' );
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $unique_id ); ?>" class="search-form__label">
		<?php esc_html_e( 'Search', 'elahub' ); ?>
	</label>
	<div class="search-form__row">
		<input
			type="search"
			id="<?php echo esc_attr( $unique_id ); ?>"
			class="search-field"
			placeholder="<?php echo esc_attr_x( 'Search&hellip;', 'placeholder', 'elahub' ); ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			name="s"
			autocomplete="off">
		<button type="submit" class="search-submit">
			<span aria-hidden="true"><i class="fa-solid fa-magnifying-glass"></i></span>
			<span class="sr-only"><?php esc_html_e( 'Search', 'elahub' ); ?></span>
		</button>
	</div>
</form>
