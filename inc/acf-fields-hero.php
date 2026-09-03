<?php

/**
 * ACF local fields: Homepage Hero — secondary button
 *
 * NOTE: acf_add_local_field on an existing DB group causes ACF to override
 * the whole group and hide all DB fields. So we do NOT register locally here.
 *
 * The template (hero-section.php) already reads hero_button_2_label and
 * hero_button_2_url via get_field(). You just need to add these two fields
 * manually in ACF Admin → Hero Settings:
 *
 *   Field 1: Text | Label: "Button 2 Label" | Name: hero_button_2_label
 *   Field 2: URL  | Label: "Button 2 URL"   | Name: hero_button_2_url
 *
 * @package elahub
 */

if (! defined('ABSPATH')) {
    exit;
}
