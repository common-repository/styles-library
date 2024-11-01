<?php
/**
 * This is file contain the block patterns.
 *
 * @since 0.0.1
 *
 * @package ea-styles-library
 */

// Disable the direct access to this class.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap easl-container">

	<!-- Page title -->
	<h1 class="wp-heading-inline">
		<span> <?php esc_html_e( 'Block Pattern Library', 'ea-styles-library' ); ?></span>

		<!-- New upload button -->
		<a href="javascript:void(0)" id="easl-upload-new-pattern">
			<?php esc_html_e( 'Upload New', 'ea-styles-library' ); ?>
		</a>
	</h1>

	<!-- This is upload section -->
	<div id="easl-pattern-library-import" class="easl-block-pattern-import-container hide" >
		<p> <?php esc_html_e( 'If you have a block pattern in a .json format, you can upload here.', 'ea-styles-library' ); ?> </p>
		<form method="post" enctype="multipart/form-data" id="easl-upload-form" class="wp-upload-form">
			<label class="screen-reader-text" for="easl_block_pattern_json"> <?php esc_html_e( 'Upload Pattern', 'ea-styles-library' ); ?> </label>
			<input type="file" accept=".json" id="easl_block_pattern_json" name="easl_block_pattern_json" />
			<button id="easl-block-pattern-import" class="button" type="button"> <?php esc_html_e( 'Upload Pattern', 'ea-styles-library' ); ?> </button>
		</form>
	</div>

	<div class="block-pattern-library-container">
		<ul class="easl-block-pattern-grids">
		<?php
		// WP_Block_Pattern_Categories_Registry.
		if ( class_exists( 'WP_Block_Patterns_Registry' ) ) {
			// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- included from a namespaced method.
			$args = array(
				'taxonomy' => 'pattern-category',
				'type'     => 'ea_block_pattern',
				'orderby'  => 'name',
				'order'    => 'ASC',
			);

			$categories  = get_categories( $args );
			$pattern_cat = array();

			if ( ! empty( $categories ) ) {
				foreach ( $categories as $category ) {
					$pattern_cat[] = $category->slug;
				}
			}

			$all_pattern = WP_Block_Patterns_Registry::get_instance()->get_all_registered();

			if ( is_array( $all_pattern ) ) {
				foreach ( $all_pattern as $pattern ) {

					if ( ! empty( $pattern['categories'] ) && ! count( array_intersect( $pattern_cat, $pattern['categories'] ) ) ) {
						continue;
					}

					printf(
						'<li class="easl-block-pattern-grid-item">
							<div class="pattern-list-view">
								<figure class="wp-block-post-featured-image">
									<img class="attachment-post-thumbnail wp-post-image placeholder" src="%s" />
								</figure>
								<div class="pattern-list-preview">
									<div class="entry-content wp-block-post-content">
										%s
									</div>
								</div>
							</div>
							<h2 class="wp-block-post-title">%s</h2>
						</li>',
						esc_url( EASL_PLUGIN_URL . '/assets/images/placeholder.png' ),
						wp_kses_post( $pattern['content'] ),
						esc_html( $pattern['title'] ),
					);
				}
			}
		}
		// phpcs:enable.
		?>
		</ul>
	</div>
</div>
