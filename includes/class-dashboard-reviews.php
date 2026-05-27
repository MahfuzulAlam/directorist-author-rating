<?php
/**
 * Dashboard reviews tab.
 *
 * @package DirectoristAuthorRating
 */

namespace DirectoristAuthorRating;

defined( 'ABSPATH' ) || exit;

/**
 * Adds a dashboard tab that lists reviews received by the current listing author.
 */
class Dashboard_Reviews {

	/**
	 * Author rating sync service.
	 *
	 * @var Author_Rating_Sync
	 */
	private $sync;

	/**
	 * Review list cache for the current request.
	 *
	 * @var array
	 */
	private $reviews_cache = array();

	/**
	 * Constructor.
	 *
	 * @param Author_Rating_Sync $sync Author rating sync service.
	 */
	public function __construct( Author_Rating_Sync $sync ) {
		$this->sync = $sync;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'directorist_dashboard_tabs', array( $this, 'add_user_reviews_tab' ) );
	}

	/**
	 * Add the User Reviews tab to the Directorist dashboard.
	 *
	 * @param array $tabs Dashboard tabs.
	 * @return array
	 */
	public function add_user_reviews_tab( $tabs ) {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return $tabs;
		}

		$reviews = $this->get_user_reviews( $user_id );

		$tabs['dashboard_user_reviews'] = array(
			'title'   => sprintf(
				/* translators: %s: review count. */
				esc_html__( 'User Reviews (%s)', 'directorist-author-rating' ),
				number_format_i18n( count( $reviews ) )
			),
			'content' => $this->get_template_contents(
				'dashboard/tab-user-reviews',
				array(
					'reviews' => $reviews,
					'user_id' => $user_id,
				)
			),
			'icon'    => 'las la-star',
		);

		return $tabs;
	}

	/**
	 * Get current user's received reviews from the extension-maintained user meta.
	 *
	 * @param int $user_id User ID.
	 * @return array
	 */
	private function get_user_reviews( $user_id ) {
		$user_id = absint( $user_id );

		if ( ! $user_id ) {
			return array();
		}

		if ( isset( $this->reviews_cache[ $user_id ] ) ) {
			return $this->reviews_cache[ $user_id ];
		}

		$this->sync->sync_author( $user_id );

		$review_ids = wp_parse_id_list( get_user_meta( $user_id, Author_Rating_Sync::META_REVIEWS, true ) );

		if ( empty( $review_ids ) ) {
			$this->reviews_cache[ $user_id ] = array();
			return array();
		}

		$review_ids = array_reverse( $review_ids );
		$comments   = get_comments(
			array(
				'comment__in' => $review_ids,
				'orderby'     => 'comment__in',
				'type'        => 'review',
				'status'      => 'all',
				'number'      => count( $review_ids ),
			)
		);

		$reviews = array();

		foreach ( $comments as $comment ) {
			if ( ! $this->is_visible_user_review( $comment, $user_id ) ) {
				continue;
			}

			$listing_id = absint( $comment->comment_post_ID );
			$rating     = (float) get_comment_meta( $comment->comment_ID, 'rating', true );

			$reviews[] = array(
				'id'            => absint( $comment->comment_ID ),
				'rating'        => $rating,
				'content'       => $comment->comment_content,
				'author_name'   => get_comment_author( $comment ),
				'date'          => get_comment_date( get_option( 'date_format' ), $comment ),
				'status'        => $comment->comment_approved,
				'listing_id'    => $listing_id,
				'listing_title' => get_the_title( $listing_id ),
				'listing_url'   => get_permalink( $listing_id ),
			);
		}

		$this->reviews_cache[ $user_id ] = $reviews;

		return $reviews;
	}

	/**
	 * Check whether a comment is one of the current user's received active reviews.
	 *
	 * @param mixed $comment Comment object.
	 * @param int   $user_id User ID.
	 * @return bool
	 */
	private function is_visible_user_review( $comment, $user_id ) {
		if ( ! $comment instanceof \WP_Comment ) {
			return false;
		}

		if ( 'review' !== $comment->comment_type || absint( $comment->comment_parent ) > 0 ) {
			return false;
		}

		if ( ! in_array( (string) $comment->comment_approved, array( '0', '1' ), true ) ) {
			return false;
		}

		return absint( get_post_field( 'post_author', $comment->comment_post_ID ) ) === absint( $user_id );
	}

	/**
	 * Render a plugin template and return its contents.
	 *
	 * @param string $template Template path relative to templates without .php.
	 * @param array  $args     Template arguments.
	 * @return string
	 */
	private function get_template_contents( $template, array $args = array() ) {
		$template = trim( str_replace( '\\', '/', $template ), '/' );
		$file     = DIRECTORIST_AUTHOR_RATING_DIR . 'templates/' . $template . '.php';

		if ( '' === $template || false !== strpos( $template, '..' ) || ! is_readable( $file ) ) {
			return '';
		}

		ob_start();
		extract( $args, EXTR_SKIP );
		include $file;

		return (string) ob_get_clean();
	}
}
