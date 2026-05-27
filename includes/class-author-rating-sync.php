<?php
/**
 * Author rating sync service.
 *
 * @package DirectoristAuthorRating
 */

namespace DirectoristAuthorRating;

defined( 'ABSPATH' ) || exit;

/**
 * Rebuilds Directorist author rating user meta from active listing reviews.
 */
class Author_Rating_Sync {

	const META_RATING       = 'directorist_rating';
	const META_REVIEW_COUNT = 'directorist_review_count';
	const META_REVIEWS      = 'directorist_reviews';

	/**
	 * Sync user meta for the listing author attached to a review comment.
	 *
	 * @param int $comment_id Comment ID.
	 * @return void
	 */
	public function sync_by_comment_id( $comment_id ) {
		$comment = get_comment( absint( $comment_id ) );

		if ( ! $comment instanceof \WP_Comment ) {
			return;
		}

		$this->sync_by_comment( $comment );
	}

	/**
	 * Sync user meta for the listing author attached to a review comment.
	 *
	 * @param \WP_Comment $comment Comment object.
	 * @return void
	 */
	public function sync_by_comment( \WP_Comment $comment ) {
		if ( ! $this->is_listing_post( absint( $comment->comment_post_ID ) ) ) {
			return;
		}

		$author_id = $this->get_listing_author_id( absint( $comment->comment_post_ID ) );

		if ( $author_id ) {
			$this->sync_author( $author_id );
		}
	}

	/**
	 * Sync all listing authors with active reviews.
	 *
	 * @return void
	 */
	public function sync_all_reviewed_authors() {
		global $wpdb;

		$listing_post_type = $this->get_listing_post_type();

		$author_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT p.post_author
				FROM {$wpdb->comments} c
				INNER JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID
				WHERE p.post_type = %s
				AND c.comment_parent = 0
				AND c.comment_type = %s
				AND c.comment_approved IN ( %s, %s )",
				$listing_post_type,
				'review',
				'0',
				'1'
			)
		);

		foreach ( array_map( 'absint', (array) $author_ids ) as $author_id ) {
			if ( $author_id ) {
				$this->sync_author( $author_id );
			}
		}
	}

	/**
	 * Recalculate and save all requested rating meta for one listing author.
	 *
	 * @param int $author_id User ID.
	 * @return void
	 */
	public function sync_author( $author_id ) {
		$author_id = absint( $author_id );

		if ( ! $author_id ) {
			return;
		}

		$reviews      = $this->get_active_author_reviews( $author_id );
		$review_ids   = array();
		$total_rating = 0;
		$rating_count = 0;

		foreach ( $reviews as $review ) {
			$review_id = isset( $review->comment_ID ) ? absint( $review->comment_ID ) : 0;
			$rating    = isset( $review->rating ) ? (float) $review->rating : 0;

			if ( $review_id ) {
				$review_ids[] = $review_id;
			}

			if ( $rating > 0 ) {
				$total_rating += $rating;
				$rating_count++;
			}
		}

		$average_rating = $rating_count ? number_format( $total_rating / $rating_count, 2, '.', '' ) : 0;

		update_user_meta( $author_id, self::META_REVIEWS, $review_ids );
		update_user_meta( $author_id, self::META_REVIEW_COUNT, count( $review_ids ) );
		update_user_meta( $author_id, self::META_RATING, $average_rating );
	}

	/**
	 * Get active top-level Directorist review comments for all listings by an author.
	 *
	 * @param int $author_id User ID.
	 * @return array
	 */
	private function get_active_author_reviews( $author_id ) {
		global $wpdb;

		$listing_post_type = $this->get_listing_post_type();

		$reviews = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT c.comment_ID, cm.meta_value AS rating
				FROM {$wpdb->comments} c
				INNER JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID
				LEFT JOIN {$wpdb->commentmeta} cm
					ON cm.comment_id = c.comment_ID
					AND cm.meta_key = %s
				WHERE p.post_author = %d
				AND p.post_type = %s
				AND c.comment_parent = 0
				AND c.comment_type = %s
				AND c.comment_approved IN ( %s, %s )
				ORDER BY c.comment_ID ASC",
				'rating',
				absint( $author_id ),
				$listing_post_type,
				'review',
				'0',
				'1'
			)
		);

		return is_array( $reviews ) ? $reviews : array();
	}

	/**
	 * Get listing author ID.
	 *
	 * @param int $listing_id Listing post ID.
	 * @return int
	 */
	private function get_listing_author_id( $listing_id ) {
		if ( ! $this->is_listing_post( $listing_id ) ) {
			return 0;
		}

		return absint( get_post_field( 'post_author', $listing_id ) );
	}

	/**
	 * Check whether a post ID belongs to the Directorist listing post type.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	private function is_listing_post( $post_id ) {
		return $post_id && $this->get_listing_post_type() === get_post_type( $post_id );
	}

	/**
	 * Check whether Directorist core is loaded.
	 *
	 * @return bool
	 */
	public function is_directorist_available() {
		return defined( 'ATBDP_POST_TYPE' ) || post_type_exists( 'at_biz_dir' );
	}

	/**
	 * Get the Directorist listing post type.
	 *
	 * @return string
	 */
	private function get_listing_post_type() {
		return defined( 'ATBDP_POST_TYPE' ) ? ATBDP_POST_TYPE : 'at_biz_dir';
	}
}
