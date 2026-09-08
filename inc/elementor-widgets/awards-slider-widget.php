<?php
/**
 * Awards Slider Widget
 *
 * Displays a Swiper carousel of award badges with an optional disclaimer.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Awards_Slider_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'awards_slider';
    }

    public function get_title() {
        return __( 'Awards Slider', 'text-domain' );
    }

    public function get_icon() {
        return 'eicon-slider-push';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_keywords() {
        return [ 'awards', 'slider', 'carousel', 'swiper', 'badges' ];
    }

    public function get_script_depends(): array {
        return [ 'swiper' ];
    }

    public function get_style_depends(): array {
        return [ 'swiper' ];
    }

    protected function register_controls() {

        $this->start_controls_section( 'content_section', [
            'label' => __( 'Awards', 'text-domain' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control( 'award_image', [
            'label'   => __( 'Award Image', 'text-domain' ),
            'type'    => \Elementor\Controls_Manager::MEDIA,
            'default' => [ 'url' => '' ],
        ] );

        $repeater->add_control( 'award_name', [
            'label'       => __( 'Award Name', 'text-domain' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'label_block' => true,
            'default'     => '',
        ] );

        $repeater->add_control( 'award_link', [
            'label'       => __( 'Link', 'text-domain' ),
            'type'        => \Elementor\Controls_Manager::URL,
            'label_block' => true,
            'placeholder' => __( 'https://', 'text-domain' ),
            'default'     => [ 'url' => '' ],
        ] );

        $this->add_control( 'awards', [
            'label'       => __( 'Slides', 'text-domain' ),
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'title_field' => '{{{ award_name }}}',
            'default'     => [],
        ] );

        $this->end_controls_section();

        $this->start_controls_section( 'disclaimer_section', [
            'label' => __( 'Disclaimer', 'text-domain' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'disclaimer', [
            'label'   => __( 'Disclaimer', 'text-domain' ),
            'type'    => \Elementor\Controls_Manager::WYSIWYG,
            'default' => '',
        ] );

        $this->end_controls_section();
    }

    /**
     * Read the repeater into a flat list, skipping rows with no image and no name.
     *
     * @param array $settings Widget settings from get_settings_for_display().
     * @return array List of [ 'image_id' => int, 'image_url' => string, 'name' => string, 'link' => array ].
     */
    protected function get_award_items( $settings ) {
        $items = [];
        $rows  = ! empty( $settings['awards'] ) ? $settings['awards'] : [];

        foreach ( $rows as $row ) {
            $image_id  = ! empty( $row['award_image']['id'] ) ? (int) $row['award_image']['id'] : 0;
            $image_url = ! empty( $row['award_image']['url'] ) ? $row['award_image']['url'] : '';
            $name      = ! empty( $row['award_name'] ) ? $row['award_name'] : '';
            $link      = ! empty( $row['award_link']['url'] ) ? $row['award_link'] : [];

            if ( ! $image_id && ! $image_url && ! $name ) {
                continue;
            }

            $items[] = [
                'image_id'  => $image_id,
                'image_url' => $image_url,
                'name'      => $name,
                'link'      => $link,
            ];
        }

        return $items;
    }

    protected function render() {
        $settings   = $this->get_settings_for_display();
        $widget_id  = 'awards-slider-' . $this->get_id();
        $swiper_id  = $widget_id . '-swiper';
        $items      = $this->get_award_items( $settings );
        $disclaimer = ! empty( $settings['disclaimer'] ) ? $settings['disclaimer'] : '';

        if ( empty( $items ) ) {
            return;
        }
        ?>
        <div class="awards-slider" id="<?php echo esc_attr( $widget_id ); ?>">
            <div class="awards-slider__swiper swiper" id="<?php echo esc_attr( $swiper_id ); ?>">
                <div class="swiper-wrapper">
                    <?php foreach ( $items as $index => $item ) :
                        $has_link = ! empty( $item['link'] );
                        $card_tag = $has_link ? 'a' : 'div';
                        $card_key = 'card_' . $index;

                        $this->add_render_attribute( $card_key, 'class', 'awards-slider__card' );

                        if ( $has_link ) {
                            $this->add_render_attribute( $card_key, 'class', 'awards-slider__card--link' );
                            $this->add_link_attributes( $card_key, $item['link'] );
                        }
                    ?>
                    <div class="awards-slider__slide swiper-slide">
                        <<?php echo $card_tag; ?> <?php echo $this->get_render_attribute_string( $card_key ); ?>>
                            <?php if ( $item['image_id'] || $item['image_url'] ) : ?>
                            <div class="awards-slider__figure">
                                <?php if ( $item['image_id'] ) : ?>
                                    <?php echo wp_get_attachment_image( $item['image_id'], 'large', false, [ 'class' => 'awards-slider__image' ] ); ?>
                                <?php else : ?>
                                    <img class="awards-slider__image" src="<?php echo esc_url( $item['image_url'] ); ?>" alt="">
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php if ( $item['name'] ) : ?>
                                <p class="awards-slider__name"><?php echo esc_html( $item['name'] ); ?></p>
                            <?php endif; ?>
                        </<?php echo $card_tag; ?>>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="awards-slider__pagination swiper-pagination"></div>

            <?php if ( $disclaimer ) : ?>
            <div class="awards-slider__disclaimer">
                <?php echo wp_kses_post( $disclaimer ); ?>
            </div>
            <?php endif; ?>
        </div>

        <script>
        (() => {
            const init = () => {
                const wrap = document.getElementById('<?php echo esc_js( $widget_id ); ?>');
                const el = document.getElementById('<?php echo esc_js( $swiper_id ); ?>');
                if (!wrap || !el) { return; }
                if (el.swiper || typeof Swiper === 'undefined') { return; }

                const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                new Swiper(el, {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    speed: reduceMotion ? 0 : 600,
                    loop: false,
                    grabCursor: true,
                    watchOverflow: true,
                    watchSlidesProgress: true,
                    centerInsufficientSlides: true,
                    keyboard: {
                        enabled: true,
                        onlyInViewport: true
                    },
                    a11y: {
                        enabled: true,
                        containerMessage: '<?php echo esc_js( __( 'Awards', 'text-domain' ) ); ?>',
                        containerRoleDescriptionMessage: 'carousel',
                        itemRoleDescriptionMessage: 'slide',
                        paginationBulletMessage: '<?php echo esc_js( __( 'Go to slide {{index}}', 'text-domain' ) ); ?>'
                    },
                    pagination: {
                        el: wrap.querySelector('.awards-slider__pagination'),
                        type: 'bullets',
                        clickable: true
                    },
                    breakpoints: {
                        1: { slidesPerView: 1 },
                        576: { slidesPerView: 2 },
                        1025: { slidesPerView: 3 },
                        1200: { slidesPerView: 4 }
                    }
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
        </script>
        <?php
    }
}
