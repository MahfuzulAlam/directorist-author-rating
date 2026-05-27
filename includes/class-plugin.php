<?php
/**
 * Main plugin class.
 *
 * @package DirectoristAuthorRating
 */

namespace DirectoristAuthorRating;

defined( 'ABSPATH' ) || exit;

/**
 * Boots the author rating extension.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Author rating sync service.
	 *
	 * @var Author_Rating_Sync
	 */
	private $sync;

	/**
	 * Get the plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public function init() {

		load_plugin_textdomain( 'directorist-author-rating', false, dirname( plugin_basename( DIRECTORIST_AUTHOR_RATING_FILE ) ) . '/languages' );

		if ( ! $this->is_directorist_loaded() ) {
			add_action( 'admin_notices', array( $this, 'maybe_show_dependency_notice' ) );
			return;
		}

		$this->sync = new Author_Rating_Sync();

		add_action( 'comment_post', array( $this, 'sync_after_comment_post' ), 20, 3 );
		add_action( 'edit_comment', array( $this, 'sync_after_comment_edit' ), 20, 2 );
		add_action( 'directorist_review_rating_updated', array( $this, 'sync_after_rating_update' ), 20, 2 );
		add_action( 'directorist_review_updated', array( $this, 'sync_after_review_update' ), 20, 2 );
		add_action( 'transition_comment_status', array( $this, 'sync_after_status_transition' ), 20, 3 );
		add_action( 'deleted_comment', array( $this, 'sync_after_comment_delete' ), 20, 2 );
		add_action( 'admin_notices', array( $this, 'maybe_show_dependency_notice' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		$this->register_template_loader();
		( new Dashboard_Reviews( $this->sync ) )->register();
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			'directorist-author-rating',
			DIRECTORIST_AUTHOR_RATING_URL . 'assets/css/frontend.css',
			array(),
			DIRECTORIST_AUTHOR_RATING_VERSION
		);
	}

	/**
	 * Register plugin template overrides.
	 *
	 * @return void
	 */
	private function register_template_loader() {
		Directorist_Author_Rating_Template_Loader::register();
	}

	/**
	 * Sync author meta after a review is submitted.
	 *
	 * @param int        $comment_id       Comment ID.
	 * @param int|string $comment_approved Comment approval status.
	 * @param array      $comment_data     Comment data.
	 * @return void
	 */
	public function sync_after_comment_post( $comment_id, $comment_approved, $comment_data ) {
		unset( $comment_approved, $comment_data );

		$this->sync->sync_by_comment_id( absint( $comment_id ) );
	}

	/**
	 * Sync author meta after a review is edited in wp-admin.
	 *
	 * @param int   $comment_id   Comment ID.
	 * @param array $comment_data Comment data.
	 * @return void
	 */
	public function sync_after_comment_edit( $comment_id, $comment_data ) {
		unset( $comment_data );

		$this->sync->sync_by_comment_id( absint( $comment_id ) );
	}

	/**
	 * Sync author meta after a review rating is created or changed.
	 *
	 * @param float|int|string $rating       Review rating.
	 * @param array            $comment_data Comment data.
	 * @return void
	 */
	public function sync_after_rating_update( $rating, $comment_data ) {
		unset( $rating );

		$comment_id = isset( $comment_data['comment_ID'] ) ? absint( $comment_data['comment_ID'] ) : 0;

		if ( ! $comment_id ) {
			return;
		}

		$this->sync->sync_by_comment_id( $comment_id );
	}

	/**
	 * Sync author meta after Directorist updates a review.
	 *
	 * @param int   $comment_id   Comment ID.
	 * @param array $comment_data Comment data.
	 * @return void
	 */
	public function sync_after_review_update( $comment_id, $comment_data ) {
		unset( $comment_data );

		$this->sync->sync_by_comment_id( absint( $comment_id ) );
	}

	/**
	 * Sync author meta when a review is approved, unapproved, trashed, or spammed.
	 *
	 * @param string     $new_status New comment status.
	 * @param string     $old_status Old comment status.
	 * @param \WP_Comment $comment    Comment object.
	 * @return void
	 */
	public function sync_after_status_transition( $new_status, $old_status, $comment ) {
		unset( $new_status, $old_status );

		if ( ! $comment instanceof \WP_Comment ) {
			return;
		}

		$this->sync->sync_by_comment( $comment );
	}

	/**
	 * Sync author meta after a review is permanently deleted.
	 *
	 * @param int         $comment_id Comment ID.
	 * @param \WP_Comment $comment    Deleted comment object.
	 * @return void
	 */
	public function sync_after_comment_delete( $comment_id, $comment = null ) {
		unset( $comment_id );

		if ( ! $comment instanceof \WP_Comment ) {
			return;
		}

		$this->sync->sync_by_comment( $comment );
	}

	/**
	 * Show an admin notice when Directorist is not available.
	 *
	 * @return void
	 */
	public function maybe_show_dependency_notice() {
		if ( $this->is_directorist_loaded() ) {
			return;
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html__( 'Directorist Author Rating requires the Directorist plugin to be active.', 'directorist-author-rating' )
		);
	}

	/**
	 * Check whether Directorist has loaded.
	 *
	 * @return bool
	 */
	private function is_directorist_loaded() {
		return class_exists( 'Directorist_Base' ) || defined( 'ATBDP_POST_TYPE' ) || function_exists( 'directorist_is_listing_post_type' );
	}
}
