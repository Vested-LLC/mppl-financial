<?php

/**
 * The template for displaying single posts.
 *
 * @package HelloElementor
 */
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

get_header();

while (have_posts()) :
    the_post();
?>

<main id="content" <?php post_class('site-main'); ?>>
    <div class="page-content">
        <div class="entry-content">
            <div class="entry-content__left">
                <!-- Featured Image -->
                 <div class="entry-content__left__featured-image">
                    <?php the_post_thumbnail('full', array('class' => 'entry-content__left__featured-image__image')); ?>
                 </div>
                 <!-- Share -->
                  <?php echo do_shortcode('[elementor-template id="5927"]'); ?>
            </div>
            <div class="entry-content__right">
                <div class="entry-content__right__title">
                    <?php the_title(); ?>
                </div>
                <div class="entry-content__right__date">
                    Published <?php the_date('F j, Y'); ?>
                </div>
                <div class="entry-content__right__content">
                    <?php the_content(); ?>
                    <?php if (get_field('disclaimer')): ?>
                        <div class="entry-content__right__disclaimer">
                            <?php the_field('disclaimer'); ?>
                        </div>
                    <?php endif; ?>
                    <!-- Back to Insights Button -->
                    <a href="<?php echo home_url('/insights'); ?>" class="back-to-insights-button">Back to Insights</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
endwhile;

get_footer();