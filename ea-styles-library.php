<?php
/**
 * Plugin Name:       Styles Library
 * Description:       This is custom block pattern builder.
 * Version:           2.0.3
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            BlockStyles
 * Author URI:        https://profiles.wordpress.org/blockstyles/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       ea-styles-library
 * Tags:              Block pattern, Block editor, Block contents
 *
 * @package           ea-styles-library
 */

namespace EASL;

use EASL\Classes\StylesLibrary;

// If this file called directly then abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check required PHP version.
if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Constant as plugin version.
 */
if ( ! defined( 'EASL_VERSION' ) ) {
	define( 'EASL_VERSION', '2.0.3' );
}

/**
 * Constant as dir of plugin.
 */
if ( ! defined( 'EASL_PLUGIN_DIR_NAME' ) ) {
	define( 'EASL_PLUGIN_DIR_NAME', untrailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );
}

/**
 * Constant as plugin path.
 */
if ( ! defined( 'EASL_PLUGIN_PATH' ) ) {
	define( 'EASL_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
}

/**
 * Constant as plugin URL.
 */
if ( ! defined( 'EASL_PLUGIN_URL' ) ) {
	define( 'EASL_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
}

// Initiate the class instances.
StylesLibrary::get_instance();
