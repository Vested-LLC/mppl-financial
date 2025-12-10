<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Slider_Widget extends \Elementor\Widget_Base {

    use ElementorPro\Base\Base_Carousel_Trait;

    public function get_name() {
        return 'slider_widget';
    }

    public function get_title() {
        return __('Posts Slider', 'custom-elementor');
    }

    public function get_icon() {
        return 'eicon-slider-push';
    }

    // public function get_categories() {
    //     return ['general'];
    // }

    public function get_script_depends(): array {
		return [ 'swiper' ];
	}

    public function get_style_depends(): array {
		return [ 'swiper' ];
	}

    private function get_post_category_options() {
        $categories = get_categories();
        $options = [];

        if (!empty($categories)) {
            foreach($categories as $category) {
                $options[$category->slug] = $category->name;
            }
        }

        return $options;
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'custom-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Posts Per Page', 'custom-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
            ]
        );

        $this->add_control(
            'post_type',
            [
                'label' => __('Post Type', 'custom-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'post' => __('Post', 'custom-elementor'),
                    'page' => __('Page', 'custom-elementor'),
                    'custom_post' => __('Custom Post Type', 'custom-elementor'),
                ],
                'default' => 'post',
            ]
        );

        $this->add_control(
            'category',
            [
                'label' => __('Category', 'custom-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_post_category_options(),
                'multiple' => true,
                'label_block' => true,
            ]
        );

        $this->add_control(
            'slides_per_view',
            [
                'label' => __('Slides Per View', 'custom-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => __('Autoplay', 'custom-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'custom-elementor'),
                'label_off' => __('No', 'custom-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();
    }

    

    protected function render() {
        $settings = $this->get_settings_for_display();
        $post_type = $settings['post_type'];
        $category = $settings['category'];
        $posts_per_page = $settings['posts_per_page'];

        $query_args = [
            'post_type'      => $post_type,
            'posts_per_page' => $posts_per_page,
            'post_status'    => 'publish',
        ];

        if (!empty($category)) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => $category,
                ]
            ];
        }

        $query = new WP_Query($query_args);
        ?>
        <div class="custom-swiper-container swiper posts-swiper">
            <div class="swiper-wrapper">
                <?php
                if ($query->have_posts()) :
                    while ($query->have_posts()) :
                        $query->the_post();
                        ?>
                        <div class="swiper-slide">
                            <?php if ( has_post_thumbnail() ) {
                                $image_url = get_the_post_thumbnail_url(get_the_ID(), 'full'); // Get the image URL
                                $alt_text  = get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true); // Get the alt text
                                
                                echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($alt_text) . '" class="custom-class">';
                            } ?>
                            <div class="swiper-slide__bottom">
                                <div>
                                    <h3><?php the_title(); ?></h3>
                                    <p>
                                        <?php $excerpt = get_the_excerpt();
                                        $excerpt = substr( $excerpt , 0, 120); 
                                        echo $excerpt . '...'; ?>
                                    </p>
                                </div>
                                <a href="<?php the_permalink(); ?>">Read More</a>
                            </div>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<p>No posts found.</p>';
                endif;
                ?>
            </div>

            <!-- Swiper Pagination & Navigation -->
            <div class="swiper-pagination"></div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                new Swiper(".custom-swiper-container", {
                    slidesPerView: <?php echo esc_js($settings['slides_per_view']); ?>,
                    spaceBetween: 15,
                    padding: 20,
                    loop: false,
                    a11y: true,
                    effect: 'slide',
                    grabCursor: true,
                    keyboard: true,
                    speed: 600,
                    autoplay: <?php echo $settings['autoplay'] === 'no' ? '{ delay: 5000 }' : 'false'; ?>,
                    pagination: {
                        el: ".swiper-pagination",
                        type: 'bullets',
                        clickable: true,
                    },
                    breakpoints: {
                        // when window width is >= 640px
                        1440: {
                            slidesPerView: <?php echo esc_js($settings['slides_per_view']); ?>,
                            spaceBetween: 20,
                        },
                        1024: {
                            slidesPerView: 1,
                            spaceBetween: 0,
                        },
                        768: {
                            slidesPerView: 2.35,
                            spaceBetween: 20,
                        },
                        576: {
                            slidesPerView: 1.25,
                            spaceBetween: 20,
                        },
                        1: {
                            slidesPerView: 1,
                            spaceBetween: 0,
                        },
                    }
                });
                
            });
        </script>
        <?php
    }
}