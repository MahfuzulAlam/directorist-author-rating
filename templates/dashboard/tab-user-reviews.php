<?php
/**
 * Dashboard User Reviews tab.
 *
 * @package DirectoristAuthorRating
 */

defined( 'ABSPATH' ) || exit;

$directorist_author_rating = ! empty( $user_id ) ? (float) get_user_meta( absint( $user_id ), 'directorist_rating', true ) : 0;
?>

<div class="directorist-author-rating-dashboard-reviews">
	<div class="directorist-author-rating-dashboard-reviews__header">
		<h3 class="directorist-author-rating-dashboard-reviews__title"><?php esc_html_e( 'User Reviews', 'directorist-author-rating' ); ?></h3>
		<div class="directorist-author-rating-dashboard-reviews__summary">
			<span class="directorist-author-rating-dashboard-reviews__average">
				<?php directorist_icon( 'fas fa-star' ); ?>
				<span><?php echo esc_html( number_format_i18n( $directorist_author_rating, 1 ) ); ?></span>
				<?php esc_html_e( 'Average Rating', 'directorist-author-rating' ); ?>
			</span>
			<span class="directorist-author-rating-dashboard-reviews__count">
				<?php
				printf(
					/* translators: %s: review count. */
					esc_html__( '%s total', 'directorist-author-rating' ),
					esc_html( number_format_i18n( count( (array) $reviews ) ) )
				);
				?>
			</span>
		</div>
	</div>

	<?php if ( ! empty( $reviews ) ) : ?>
		<div class="directorist-author-rating-review-list">
			<?php foreach ( $reviews as $review ) : ?>
				<article class="directorist-author-rating-review">
					<div class="directorist-author-rating-review__header">
						<div class="directorist-author-rating-review__rating">
							<?php directorist_icon( 'fas fa-star' ); ?>
							<span><?php echo esc_html( number_format_i18n( (float) $review['rating'], 1 ) ); ?></span>
						</div>

						<div class="directorist-author-rating-review__meta">
							<strong class="directorist-author-rating-review__author"><?php echo esc_html( $review['author_name'] ); ?></strong>
							<span class="directorist-author-rating-review__date"><?php echo esc_html( $review['date'] ); ?></span>
							<?php if ( '0' === (string) $review['status'] ) : ?>
								<span class="directorist-author-rating-review__status"><?php esc_html_e( 'Pending', 'directorist-author-rating' ); ?></span>
							<?php endif; ?>
						</div>
					</div>

					<div class="directorist-author-rating-review__content">
						<?php echo wp_kses_post( wpautop( $review['content'] ) ); ?>
					</div>

					<?php if ( ! empty( $review['listing_title'] ) && ! empty( $review['listing_url'] ) ) : ?>
						<a class="directorist-author-rating-review__listing" href="<?php echo esc_url( $review['listing_url'] ); ?>">
							<?php
							printf(
								/* translators: %s: listing title. */
								esc_html__( 'Listing: %s', 'directorist-author-rating' ),
								esc_html( $review['listing_title'] )
							);
							?>
						</a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="directorist-notfound"><?php esc_html_e( 'No reviews found.', 'directorist-author-rating' ); ?></div>
	<?php endif; ?>
</div>
