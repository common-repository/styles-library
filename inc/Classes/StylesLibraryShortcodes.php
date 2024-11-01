<?php

namespace EASL\Classes;

class StylesLibraryShortcodes {

	public static function init_shortcodes() {
		add_shortcode( 'easl_year', array( get_called_class(), 'display_year' ) );
		add_shortcode( 'easl_site_title', array( get_called_class(), 'site_title_shortcode' ) );
	}

	// Display the year defined on the server
	public static function display_year() {
		$year = date( 'Y' );
		return $year;
	}


	// Display the site title
	public static function site_title_shortcode() {
		return get_bloginfo( 'name' );
	}

}
