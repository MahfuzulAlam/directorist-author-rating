<?php
/**
 * @author  wpWax
 * @since   6.6
 * @version 7.7.0
 */

use \Directorist\Helper;

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<section class="directorist-author-profile-area directorist-author-profile">

    <?php do_action( 'directorist_before_author_profile_section' ); ?>

    <div class="directorist-card directorist-author-profile__wrap">

        <div class="directorist-author-avatar directorist-author-profile__avatar">

            <?php echo wp_kses_post( $author->avatar_html() ); ?>

            <div class="directorist-author-avatar__info directorist-author-profile__avatar__info">
                <h2 class="directorist-author-name directorist-author-profile__avatar__info__name"><?php echo esc_html( $author->display_name() ); ?></h2>
                <p><?php echo esc_html( $author->member_since_text() ); ?></p>
            </div>

        </div>

        <ul class="directorist-author-meta-list directorist-author-profile__meta-list">

            <?php if ( $author->review_enabled() ) : ?>
                <?php
                $directorist_author_id           = absint( $author->get_id() );
                $directorist_author_rating       = (float) get_user_meta( $directorist_author_id, 'directorist_rating', true );
                $directorist_author_review_count = absint( get_user_meta( $directorist_author_id, 'directorist_review_count', true ) );
                $directorist_author_review_text  = sprintf(
                    _nx( '%s Review', '%s Reviews', $directorist_author_review_count, 'author review count', 'directorist-author-rating' ),
                    number_format_i18n( $directorist_author_review_count )
                );
                ?>
                <li class="directorist-author-meta-list__item directorist-info-meta directorist-author-profile__meta-list__item directorist-author-rating-meta">
                    <?php directorist_icon( 'fas fa-star' ); ?>
                    <span class="directorist-review-count">
                        <span class="directorist-author-rating-value"><?php echo esc_html( number_format_i18n( $directorist_author_rating, 1 ) ); ?></span>
                        <span class="directorist-author-rating-count"><?php echo esc_html( $directorist_author_review_text ); ?></span>
                    </span>
                </li>

            <?php endif; ?>

            <li class="directorist-author-meta-list__item directorist-info-meta directorist-author-profile__meta-list__item">
                <?php directorist_icon( 'fas fa-list-ol' ); ?>
                <span class="directorist-listing-count">
                    <?php echo wp_kses_post( $author->listing_count_html() ); ?>
                </span>
            </li>

        </ul>

    </div>

</section>
