<?php
/**
 * Activation tasks.
 *
 * @package DirectoristAuthorRating
 */

namespace DirectoristAuthorRating;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin activation.
 */
class Activator {

	/**
	 * Rebuild meta for authors who already have approved reviews.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( ! self::is_directorist_active() ) {
			deactivate_plugins( plugin_basename( DIRECTORIST_AUTHOR_RATING_FILE ) );

			wp_die(
				esc_html__( 'Directorist Author Rating requires the Directorist plugin to be installed and active.', 'directorist-author-rating' ),
				esc_html__( 'Plugin dependency missing', 'directorist-author-rating' ),
				array(
					'back_link' => true,
				)
			);
		}

		$sync = new Author_Rating_Sync();
		$sync->sync_all_reviewed_authors();
	}

	/**
	 * Check whether Directorist is active.
	 *
	 * @return bool
	 */
	private static function is_directorist_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( 'directorist/directorist-base.php' ) || is_plugin_active_for_network( 'directorist/directorist-base.php' );
	}
}
