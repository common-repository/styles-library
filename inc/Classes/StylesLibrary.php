<?php
/**
 * Styles library main class.
 *
 * This class is responsible to handle feature and actions.
 *
 * @author    Easily Amused, Inc.
 * @package   ea-styles-library
 */

namespace EASL\Classes;

// Disable the direct access to this class.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use EASL\Classes\PatternAndTypesRESTController;
use EASL\Classes\StylesLibraryShortcodes;

/**
 * Class Style_Library
 */
class StylesLibrary {

	use \EASL\Traits\Singleton;

	/**
	 * Construct method.
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * To setup action/filter.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_pattern_cpt_and_taxonomies' ) );
		add_action( 'init', array( $this, 'register_block_patterns' ) );
		add_action( 'init', array( $this, 'register_block_pattern_category' ) );
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// add_action( 'admin_menu', array( $this, 'register_submenu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_front_scripts' ) );
		add_filter( 'post_row_actions', array( $this, 'add_cpt_action_export' ), 10, 2 );

		// Ajax actions hook call.
		add_action( 'wp_ajax_easl_save_pattern', array( $this, 'easl_save_pattern' ) );
		add_action( 'wp_ajax_easl_get_terms', array( $this, 'easl_get_terms' ) );
		add_action( 'wp_ajax_easl_block_pattern_import', array( $this, 'easl_block_pattern_import' ) );
		add_action( 'wp_ajax_easl_export_block_pattern', array( $this, 'easl_export_block_pattern' ) );
		add_action( 'init', array( $this, 'register_font_block_style' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'after_setup_theme', array( $this, 'eabs_add_editor_style' ) );
		add_action( 'admin_footer-edit.php', array( $this, 'add_import_form_templates' ) );

		add_action( 'add_meta_boxes_ea_block_pattern', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_ea_block_pattern', array( $this, 'save_metabox' ) );

		add_action( 'activated_plugin', array( $this, 'clear_api_cache' ) );
		add_action( 'deactivated_plugin', array( $this, 'clear_api_cache' ) );
	}

	/**
	 * Function to add the fonts in editor styles.
	 */
	public function eabs_add_editor_style() {
		add_editor_style( array( 'https://fonts.googleapis.com/css2?family=Flow+Block&display=swap' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'flow-block-google-fonts',
			'https://fonts.googleapis.com/css2?family=Flow+Block&display=swap',
			array(),
			EASL_VERSION,
			false
		);
	}

	/**
	 * Function to register the font block style.
	 */
	public function register_font_block_style() {

		$allowed_blocks = array(
			'core/freeform',
			'core/heading',
			'core/html',
			'core/list',
			'core/media-text',
			'core/paragraph',
			'core/preformatted',
			'core/pullquote',
			'core/quote',
			'core/table',
			'core/verse',
		);

		/**
		 * Filters the blocks for flow block style.
		 *
		 * @param array $allowed_blocks List of allowed blocks.
		 */
		$allowed_blocks = apply_filters( 'easl_flow_block_style_supported_blocks', $allowed_blocks );

		foreach ( $allowed_blocks as $block ) {
			register_block_style(
				$block,
				array(
					'name'         => 'easl-flow-block-style',
					'label'        => __( 'Placeholder', 'ea-styles-library' ),
					'inline_style' => ".is-style-easl-flow-block-style, .editor-styles-wrapper .is-style-easl-flow-block-style { font-family: 'Flow Block'; }",
				)
			);
		}
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 0.0.1
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'ea-styles-library',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

	/**
	 * Register block pattern cpt.
	 */
	public function register_pattern_cpt_and_taxonomies() {

		$labels = array(
			'name'                  => apply_filters( 'easl_block_pattern_cpt_title', esc_html__( 'Styles Library', 'ea-styles-library' ) ),
			'singular_name'         => esc_html__( 'Block Pattern', 'ea-styles-library' ),
			'search_items'          => esc_html__( 'Search Patterns', 'ea-styles-library' ),
			'all_items'             => esc_html__( 'All Patterns', 'ea-styles-library' ),
			'add_new'               => esc_html__( 'Add New', 'ea-styles-library' ),
			'add_new_item'          => esc_html__( 'Add New Pattern', 'ea-styles-library' ),
			'new_item'              => esc_html__( 'Add New Pattern', 'ea-styles-library' ),
			'edit_item'             => esc_html__( 'Edit Pattern', 'ea-styles-library' ),
			'not_found'             => esc_html__( 'No patterns found.', 'ea-styles-library' ),
			'view_item'             => esc_html__( 'View Pattern', 'ea-styles-library' ),
			'view_items'            => esc_html__( 'View Patterns', 'ea-styles-library' ),
			'uploaded_to_this_item' => esc_html__( 'Upload to this pattern', 'ea-styles-library' ),
			'item_published'        => esc_html__( 'Pattern published', 'ea-styles-library' ),
			'item_updated'          => esc_html__( 'Pattern updated', 'ea-styles-library' ),
			'insert_into_item'      => esc_html__( 'Insert into pattern', 'ea-styles-library' ),
			'items_list'            => esc_html__( 'Patterns list', 'ea-styles-library' ),
		);

		$args = array(
			'description'         => esc_html__( 'Block pattern builder', 'ea-styles-library' ),
			'labels'              => $labels,
			'capability_type'     => 'page',
			'menu_icon'           => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDI3LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9ImJsb2NrLXBhdHRlcm5zIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiCgkgeT0iMHB4IiB2aWV3Qm94PSIwIDAgMTI4IDEyOCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTI4IDEyODsiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPgoJLnN0MHtmaWxsOiMxRTFFMUU7fQo8L3N0eWxlPgo8cGF0aCBjbGFzcz0ic3QwIiBkPSJNNDAuNiwzNi43aDgzLjFjMi4yLDAsNCwxLjgsNCw0djgzLjFjMCwyLjItMS44LDQtNCw0SDQwLjZjLTIuMiwwLTQtMS44LTQtNFY0MC42CglDMzYuNywzOC41LDM4LjUsMzYuNyw0MC42LDM2Ljd6Ii8+CjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik05MS4zLDE4LjVWMC4zSDEyLjhDNS45LDAuMywwLjMsNS45LDAuMywxMi44djc4LjVoMTguMlYyMS43YzAtMS44LDEuNS0zLjIsMy4yLTMuMkg5MS4zeiIvPgo8L3N2Zz4K',
			'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields', 'revisions' ),
			'taxonomies'          => array(),
			'exclude_from_search' => true,
			'show_in_rest'        => true,
			'show_ui'             => true,
			'show_in_admin_bar'   => false,
			'rewrite'             => false,
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'has_archive'         => false,
			'public'              => false,
			'publicly_queryable'  => false,
		);

		register_post_type( 'ea_block_pattern', $args );

		$labels = array(
			'name'              => esc_html_x( 'Categories', 'pattern category taxonomy general name', 'ea-styles-library' ),
			'singular_name'     => esc_html_x( 'pattern-category', 'pattern category taxonomy singular name', 'ea-styles-library' ),
			'search_items'      => esc_html__( 'Search Categories', 'ea-styles-library' ),
			'all_items'         => esc_html__( 'All Categories', 'ea-styles-library' ),
			'parent_item'       => esc_html__( 'Parent Category', 'ea-styles-library' ),
			'parent_item_colon' => esc_html__( 'Parent Category:', 'ea-styles-library' ),
			'edit_item'         => esc_html__( 'Edit Category', 'ea-styles-library' ),
			'update_item'       => esc_html__( 'Update Category', 'ea-styles-library' ),
			'add_new_item'      => esc_html__( 'Add New Category', 'ea-styles-library' ),
			'new_item_name'     => esc_html__( 'New Category', 'ea-styles-library' ),
			'menu_name'         => esc_html__( 'Categories', 'ea-styles-library' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'pattern-category' ),
		);

		register_taxonomy( 'pattern-category', array( 'ea_block_pattern' ), $args );

		$labels = array(
			'name'              => esc_html_x( 'Keywords', 'pattern keyword taxonomy general name', 'ea-styles-library' ),
			'singular_name'     => esc_html_x( 'Keyword', 'pattern keyword taxonomy singular name', 'ea-styles-library' ),
			'search_items'      => esc_html__( 'Search Keywords', 'ea-styles-library' ),
			'all_items'         => esc_html__( 'All Keywords', 'ea-styles-library' ),
			'parent_item'       => esc_html__( 'Parent Keyword', 'ea-styles-library' ),
			'parent_item_colon' => esc_html__( 'Parent Keyword:', 'ea-styles-library' ),
			'edit_item'         => esc_html__( 'Edit Keyword', 'ea-styles-library' ),
			'update_item'       => esc_html__( 'Update Keyword', 'ea-styles-library' ),
			'add_new_item'      => esc_html__( 'Add New Keyword', 'ea-styles-library' ),
			'new_item_name'     => esc_html__( 'New Keyword', 'ea-styles-library' ),
			'menu_name'         => esc_html__( 'Keywords', 'ea-styles-library' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'pattern-keyword' ),
		);

		register_taxonomy( 'pattern-keyword', array( 'ea_block_pattern' ), $args );
	}

	/**
	 * Function to register the cpt post as block pattern.
	 */
	public function register_block_patterns() {

		// Fetch all custom posts and register each as a pattern.
		$posts = get_posts(
			array(
				'post_type'   => 'ea_block_pattern',
				'post_status' => 'publish',
				'numberposts' => -1,
				'fields'      => array( 'id', 'post_title', 'post_content', 'post_name' ),
			)
		);

		$patterns = array_map( array( $this, 'cpt_post_to_pattern_mapping' ), $posts );

		foreach ( $patterns as $pattern ) {
			register_block_pattern( $pattern['name'], $pattern );
		}
	}

	/**
	 * Convert the cpt ea_block_pattern to pattern structure.
	 *
	 * @since 0.0.1
	 * @param Object $post  Post Object.
	 *
	 * @return array
	 */
	public function cpt_post_to_pattern_mapping( $post ) {

		if ( empty( $post ) ) {
			return $post;
		}

		$pattern = array(
			'title'         => $post->post_title,
			'content'       => $post->post_content,
			'name'          => '#' . $post->ID . '-' . $post->post_name,
			'description'   => esc_html( get_post_meta( $post->ID, 'easl_description', true ) ),
			'viewportWidth' => esc_html( get_post_meta( $post->ID, 'easl_viewport_width', true ) ),
		);

		$categories = array();
		$keywords   = array();
		$terms      = wp_get_post_terms( $post->ID, array( 'pattern-category', 'pattern-keyword' ), array() );

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( 'pattern-keyword' === $term->taxonomy ) {
					$keywords[] = $term->slug;
				} elseif ( 'pattern-category' === $term->taxonomy ) {
					$categories[] = $term->slug;
				}
			}
		}

		$pattern['categories'] = $categories;
		$pattern['keywords']   = $keywords;

		return $pattern;
	}

	/**
	 * Function to register the cpt post catgories as block pattern category.
	 */
	public function register_block_pattern_category() {

		$args = array(
			'taxonomy' => 'pattern-category',
			'type'     => 'ea_block_pattern',
			'orderby'  => 'name',
			'order'    => 'ASC',
		);

		// Fetch all custom posts and register each as a pattern.
		$categories = get_categories( $args );
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				register_block_pattern_category(
					$category->slug,
					array( 'label' => ucfirst( $category->name ) )
				);
			}
		}
	}

	/**
	 * Init Shortcodes
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		StylesLibraryShortcodes::init_shortcodes();
	}

	/**
	 * Add sub menu pages in block pattern admin.
	 *
	 * @since 0.0.1
	 */
	public function register_submenu_page() {

		add_submenu_page(
			'edit.php?post_type=ea_block_pattern',
			__( 'Pattern Library', 'ea-styles-library' ),
			__( 'Pattern Library', 'ea-styles-library' ),
			'manage_options',
			'pattern_library',
			array( $this, 'block_pattern_library_page' )
		);
	}

	/**
	 * Add template to the block pattern template page.
	 *
	 * @since 0.0.1
	 */
	public function block_pattern_library_page() {

		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		include_once EASL_PLUGIN_PATH . '/inc/templates/block-pattern-upload.php';
	}

	/**
	 * Enqueue scripts needed in front end for extra funcionalities
	 *
	 * @return void
	 */
	public function add_front_scripts() {

		wp_enqueue_script(
			'easl_frontend_components',
			EASL_PLUGIN_URL . '/build/frontend.js',
			array( 'jquery' ),
			EASL_VERSION
		);

		wp_enqueue_style(
			'easl_frontend_components_styles',
			EASL_PLUGIN_URL . '/build/frontend.css',
			array(),
			EASL_VERSION,
			false
		);
	}

	/**
	 * Enqueue a script in the edit.php.
	 *
	 * @return void
	 */
	public function add_admin_scripts() {

		wp_register_script(
			'easl_block_pattern_script',
			EASL_PLUGIN_URL . '/build/index.js',
			array( 'wp-plugins', 'wp-edit-post', 'wp-i18n', 'wp-element', 'wp-data', 'wp-compose', 'wp-hooks', 'wp-block-editor', 'wp-editor', 'jquery' ),
			EASL_VERSION,
			true
		);

		wp_localize_script(
			'easl_block_pattern_script',
			'ea_block',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'ea_block_nonce' ),
			)
		);

		wp_enqueue_script( 'easl_block_pattern_script' );

		wp_enqueue_style(
			'easl_block_pattern_style',
			EASL_PLUGIN_URL . '/build/admin.css',
			array(),
			EASL_VERSION,
			false
		);

		wp_enqueue_style(
			'easl_block_pattern_editor_style',
			EASL_PLUGIN_URL . '/build/style-admin.css',
			array(),
			EASL_VERSION,
			false
		);

	}

	/**
	 * Function to add the download pattern action link.
	 *
	 * @since 0.0.1
	 *
	 * @param Array   $actions List of post actions.
	 * @param WP_Post $post    Post object.
	 *
	 * @return Array
	 */
	public function add_cpt_action_export( $actions, $post ) {

		if ( empty( $post ) ) {
			return $actions;
		}

		if ( 'ea_block_pattern' === $post->post_type ) {
			$actions['download_link'] = '<a href="javascript: void(0)" data-post-id="' . (int) $post->ID . '" class="easl-export-block-pattern">' . esc_html__( 'Export', 'ea-styles-library' ) . '</a>';
		}

		return $actions;
	}

	/**
	 * Function to get the terms.
	 */
	public function easl_get_terms() {

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ea_block_nonce' ) ) {
			wp_send_json_error( __( 'Invalid Nonce data', 'ea-styles-library' ) );
		}

		$args = array(
			'taxonomy'   => 'pattern-keyword',
			'type'       => 'ea_block_pattern',
			'orderby'    => 'name',
			'order'      => 'ASC',
			'hide_empty' => false,
			'fields'     => 'names',
		);

		$tags             = get_tags( $args );
		$args['taxonomy'] = 'pattern-category';
		$categories       = get_categories( $args );

		wp_send_json_success(
			array(
				'tags'       => $tags,
				'categories' => $categories,
			)
		);
	}

	/**
	 * Function to save the block pattern contents via ajax call.
	 */
	public function easl_save_pattern() {

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ea_block_nonce' ) ) {
			wp_send_json_error( esc_html__( 'Invalid Nonce data', 'ea-styles-library' ), 403 );
		}

		if ( empty( $_POST['title'] ) ) {
			wp_send_json_error( esc_html__( 'Title is missing', 'ea-styles-library' ), 400 );
		}

		if ( empty( $_POST['contents'] ) ) {
			wp_send_json_error( esc_html__( 'Block contents are missing', 'ea-styles-library' ), 400 );
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $_POST['title'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- wp_insert_post unslashes and sanitizes.
				'post_content' => $_POST['contents'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- wp_insert_post unslashes and sanitizes.
				'post_status'  => 'publish',
				'post_type'    => 'ea_block_pattern',
			)
		);

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Post could not be created.', 'ea-styles-library' ) . ' ' . $post_id->get_error_message(), 500 );
		}

		// Add categories to the ea_block_pattern cpt.
		if ( ! empty( $_POST['categories'] ) ) {
			$caregories = sanitize_text_field( wp_unslash( $_POST['categories'] ) );
			$caregories = explode( ',', $caregories );
			$term_ids   = array();

			foreach ( $caregories as $category ) {
				$term = get_term_by( 'name', $category, 'pattern-category' );

				if ( ! empty( $term->term_id ) ) {
					$term_ids[] = $term->term_id;
					continue;
				}

				$term = wp_insert_term( $category, 'pattern-category' );

				if ( ! empty( $term['term_id'] ) ) {
					$term_ids[] = $term['term_id'];
				}
			}

			wp_set_post_terms( $post_id, $term_ids, 'pattern-category' );
		}

		// Add post tags for block pattern.
		if ( ! empty( $_POST['tags'] ) ) {
			$tags = sanitize_text_field( wp_unslash( $_POST['tags'] ) );
			wp_set_post_terms( $post_id, $tags, 'pattern-keyword', true );
		}

		wp_send_json_success();
	}

	/**
	 * Function to upload the theme by ajax.
	 *
	 * @since 0.0.1
	 */
	public function easl_block_pattern_import() {

		// Check nonce to verify the authenticate upload file.
		check_ajax_referer( 'ea_block_nonce', 'ajax_nonce' );

		// Check if files are empty then return error message.
		if ( empty( $_FILES['file']['tmp_name'] ) ) {
			wp_send_json_error( esc_html__( 'File not found on the server', 'ea-styles-library' ), 500 );
		}

		$block_pattern = file_get_contents( $_FILES['file']['tmp_name'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- server generated temporary filename.
		unlink( $_FILES['file']['tmp_name'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- server generated temporary filename.

		$block_pattern = json_decode( $block_pattern );

		if ( empty( $block_pattern ) ) {
			wp_send_json_error( esc_html__( 'The imported block pattern seems to be empty', 'ea-styles-library' ), 400 );
		}

		$post_id = wp_insert_post(
			array(
				'post_title'   => $block_pattern->title,
				'post_content' => $block_pattern->content,
				'post_name'    => $block_pattern->name,
				'post_status'  => 'publish',
				'post_type'    => 'ea_block_pattern',
			)
		);

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( esc_html__( 'Pattern could not be imported.', 'ea-styles-library' ) . ' ' . $post_id->get_error_message(), 500 );
		}
		if ( ! empty( $post_id ) ) {
			if ( ! empty( $block_pattern->description ) ) {
				update_post_meta( $post_id, 'easl_description', sanitize_text_field( $block_pattern->description ) );
			}

			if ( ! empty( $block_pattern->viewport_width ) ) {
				update_post_meta( $post_id, 'easl_viewport_width', sanitize_text_field( $block_pattern->viewport_width ) );
			}

			// Add pattern categories.
			if ( ! empty( $block_pattern->categories ) ) {
				$caregories = array_map( 'sanitize_text_field', $block_pattern->categories );
				$term_ids   = array();
				foreach ( $caregories as $category ) {
					$term = get_term_by( 'name', $category, 'pattern-category' );

					if ( ! empty( $term->term_id ) ) {
						$term_ids[] = $term->term_id;
						continue;
					}

					$term = wp_insert_term( $category, 'pattern-category' );
					if ( ! empty( $term['term_id'] ) ) {
						$term_ids[] = $term['term_id'];
					}
				}

				wp_set_post_terms( $post_id, $term_ids, 'pattern-category' );
			}

			// Add pattern keywords.
			if ( ! empty( $block_pattern->keywords ) ) {
				$keywords = array_map( 'sanitize_text_field', $block_pattern->keywords );
				$keywords = implode( ',', $keywords );
				wp_set_post_terms( $post_id, $keywords, 'pattern-keyword', true );
			}

			wp_send_json_success( esc_html__( 'New Pattern successfully imported. The page will reload automatically.', 'ea-styles-library' ) );
		}
	}

	/**
	 * Function to ajax callback for download the pattern.
	 *
	 * @since 0.0.1
	 */
	public function easl_export_block_pattern() {

		check_ajax_referer( 'ea_block_nonce', 'ajax_nonce' );

		if ( empty( $_POST['post_id'] ) ) {
			wp_send_json_error( esc_html__( 'Post ID missing !', 'ea-styles-library' ) );
		}

		$post_id = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );

		$block_pattern = array();
		$post          = get_post( $post_id );

		if ( empty( $post ) ) {
			wp_send_json_error( esc_html__( 'Pattern not exist !', 'ea-styles-library' ) );
		}

		$block_pattern['title']          = $post->post_title;
		$block_pattern['content']        = $post->post_content;
		$block_pattern['name']           = 'ea-styles-library/' . $post->post_name;
		$block_pattern['description']    = esc_html( get_post_meta( $post->ID, 'easl_description', true ) );
		$block_pattern['viewport_width'] = esc_html( get_post_meta( $post->ID, 'easl_viewport_width', true ) );

		$terms      = wp_get_post_terms( $post->ID, array( 'pattern-category', 'pattern-keyword' ), array() );
		$categories = array();
		$keywords   = array();
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( 'pattern-keyword' === $term->taxonomy ) {
					$keywords[] = $term->slug;
				} elseif ( 'pattern-category' === $term->taxonomy ) {
					$categories[] = $term->slug;
				}
			}
		}

		$block_pattern['categories'] = $categories;
		$block_pattern['keywords']   = $keywords;

		/**
		 * Fires when download the block pattern.
		 *
		 * @version 0.0.1
		 * @param int   $post_id        Post ID.
		 * @param array $block_pattern  Block pattern options.
		 */
		do_action( 'easl_export_block_pattern', $post_id, $block_pattern );

		// Return the response after success.
		wp_send_json_success(
			array(
				'file_name' => $post->post_name,
				'contents'  => wp_json_encode( $block_pattern ),
			)
		);
	}

	/**
	 * Function to add the markups for pattern upload.
	 */
	public function add_import_form_templates() {

		if ( 'edit-ea_block_pattern' !== get_current_screen()->id ) {
			return;
		}
		?>
		<div id="easl-import-success" class="notice notice-success is-dismissible" style="display:none;"><p></p></div>
		<div id="easl-import-error" class="notice notice-error is-dismissible" style="display:none;"><p></p></div>

		<script type="text/template" id="easl-import-form">
			<div id="easl-pattern-library-import">
				<p> <?php esc_html_e( 'You can upload block pattern in .json format.', 'ea-styles-library' ); ?> </p>
				<form enctype="multipart/form-data" method="post" id="easl-upload-form" class="wp-upload-form">
					<label class="screen-reader-text" for="easl_block_pattern_json"> <?php esc_html_e( 'Upload Pattern', 'ea-styles-library' ); ?> </label>
					<input type="file" accept=".json" id="easl_block_pattern_json" name="easl_block_pattern_json" />
					<button id="easl-block-pattern-import" class="button" type="button"> <?php esc_html_e( 'Upload Pattern', 'ea-styles-library' ); ?> </button>
				</form>
			</div>
		</script>
		<?php
	}

	/**
	 * Adds the meta box for cpt ea_block_pattern.
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return void
	 */
	public function add_meta_box( $post ) {
		add_meta_box(
			'Pattern Details',
			__( 'Pattern Details', 'ea-style-library' ),
			array( $this, 'render_meta_box_content' ),
			'ea_block_pattern',
			'advanced',
			'high'
		);
	}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 *
	 * @return void
	 */
	public function render_meta_box_content( $post ) {

		wp_nonce_field( 'easl_custom_metabox', 'easl_metabox_nonce' );
		$easl_viewport_width = get_post_meta( $post->ID, 'easl_viewport_width', true );
		$easl_description    = get_post_meta( $post->ID, 'easl_description', true );
		?>

		<div class="easl-metabox-container">
			<?php
				$post = apply_filters( 'easl_block_pattern_cpt_metabox_before_contents', $post );
			?>
			<div class="easl-metabox-input-group">
				<div class="easl-metabox-inputs">
					<label for="easl-metabox-input-viewport-width"> <?php esc_html_e( 'Viewport width', 'ea-styles-library' ); ?> </label>
					<input type="text" class="easl-metabox-input" id="easl-metabox-input-viewport-width" name="easl-metabox-input-viewport-width" value="<?php echo $easl_viewport_width ? esc_attr( $easl_viewport_width ) : '750'; ?>"/>
				</div>
				<p class="easl-input-description"><?php esc_html_e( 'An integer specifying the intended width of the pattern to allow for a scaled preview of the pattern in the inserter.', 'ea-styles-library' ); ?></p>
			</div>

			<div class="easl-metabox-input-group">
				<div class="easl-metabox-inputs">
					<label for="easl-metabox-input-description"> <?php esc_html_e( 'Description', 'ea-styles-library' ); ?> </label>
					<textarea class="easl-metabox-input" id="easl-metabox-input-description" name="easl-metabox-input-description"><?php echo esc_html( $easl_description ); ?></textarea>
				</div>
				<p class="easl-input-description"><?php esc_html_e( 'A visually hidden text used to describe the pattern in the inserter. A description is optional but it is strongly encouraged when the title does not fully describe what the pattern does. The description will help users discover the pattern while searching.', 'ea-styles-library' ); ?></p>
			</div>

		</div>
		<?php
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_metabox( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		if ( empty( $_POST['easl_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['easl_metabox_nonce'], 'easl_custom_metabox' ) ) ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		}

		$easl_viewport_width = isset( $_POST['easl-metabox-input-viewport-width'] ) ? sanitize_text_field( wp_unslash( $_POST['easl-metabox-input-viewport-width'] ) ) : '';
		$easl_description    = isset( $_POST['easl-metabox-input-description'] ) ? sanitize_text_field( wp_unslash( $_POST['easl-metabox-input-description'] ) ) : '';

		update_post_meta( $post_id, 'easl_viewport_width', $easl_viewport_width );
		update_post_meta( $post_id, 'easl_description', $easl_description );
	}

	/**
	 * Function to register our new routes from the controller.
	 */
	public function register_routes() {
		$categories_controller = new PatternAndTypesRESTController();
		$categories_controller->register_routes();
	}

	/**
	 * Function to clear the transient on block styles plugin active.
	 *
	 * @param string $plugin Plugin name.
	 */
	public function clear_api_cache( $plugin ) {
		if ( ! empty( $plugin ) && 'ea-block-styles/ea-block-styles.php' === $plugin ) {
			delete_transient( 'easl_pattern_api_response' );
		}
	}

}
