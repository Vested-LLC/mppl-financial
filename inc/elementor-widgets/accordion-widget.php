<?php
/**
 * Accordion Widget
 *
 * Displays an accordion/FAQ widget.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Accordion_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'accordion';
    }

    public function get_title() {
        return __( 'FAQ Accordion', 'text-domain' );
    }

    public function get_icon() {
        return 'eicon-caret-down';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_keywords() {
        return [ 'accordion', 'faq', 'toggle', 'collapse' ];
    }

    protected function register_controls() {

        $this->start_controls_section( 'content_section', [
            'label' => __( 'Accordion', 'text-domain' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'faqs_notice', [
            'type'            => \Elementor\Controls_Manager::RAW_HTML,
            'raw'             => __( 'Items are pulled from the "FAQs" ACF repeater field on the current page.', 'text-domain' ),
            'content_classes' => 'elementor-descriptor',
        ] );

        $this->add_control( 'open_first_item', [
            'label'        => __( 'Open First Item', 'text-domain' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => __( 'Yes', 'text-domain' ),
            'label_off'    => __( 'No', 'text-domain' ),
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->end_controls_section();
    }

    protected function render() {
        $open_first = $this->get_settings_for_display( 'open_first_item' ) === 'yes';
        $widget_id  = 'accordion-' . $this->get_id();

        $post_id = get_queried_object_id();
        if ( empty( $post_id ) ) {
            $post_id = get_the_ID();
        }

        if ( ! have_rows( 'faqs', $post_id ) ) return;
        ?>
        <div class="accordion" id="<?php echo esc_attr( $widget_id ); ?>">
            <?php $index = 0; ?>
            <?php while ( have_rows( 'faqs', $post_id ) ) : the_row();
                $faq_question = get_sub_field( 'faq_question' );
                $faq_answer   = get_sub_field( 'faq_answer' );

                if ( ! $faq_question ) {
                    $index++;
                    continue;
                }

                $item_id  = $widget_id . '-' . $index;
                $btn_id   = $item_id . '-btn';
                $panel_id = $item_id . '-panel';
                $is_open  = $open_first && $index === 0;
            ?>
            <div class="accordion__item">
                <h3 class="accordion__header">
                    <button
                        class="accordion__button"
                        id="<?php echo esc_attr( $btn_id ); ?>"
                        type="button"
                        aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
                        aria-controls="<?php echo esc_attr( $panel_id ); ?>"
                    >
                        <span class="accordion__title"><?php echo esc_html( $faq_question ); ?></span>
                        <span class="accordion__icon" aria-hidden="true">
                            <svg class="accordion__icon-chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            <svg class="accordion__icon-close" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </span>
                    </button>
                </h3>
                <div
                    class="accordion__panel<?php echo $is_open ? ' is-open' : ''; ?>"
                    id="<?php echo esc_attr( $panel_id ); ?>"
                    role="region"
                    aria-labelledby="<?php echo esc_attr( $btn_id ); ?>"
                    <?php echo ! $is_open ? 'hidden' : ''; ?>
                >
                    <div class="accordion__content">
                        <?php echo wp_kses_post( $faq_answer ); ?>
                    </div>
                </div>
            </div>
            <?php $index++; ?>
            <?php endwhile; ?>
        </div>

        <script>
        (function() {
            var acc = document.getElementById('<?php echo esc_js( $widget_id ); ?>');
            if ( ! acc ) return;

            function setOpen( btn, panel, open ) {
                btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
                panel.classList.toggle( 'is-open', open );

                if ( open ) {
                    panel.removeAttribute( 'hidden' );
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                } else {
                    panel.style.maxHeight = '0';
                    panel.addEventListener( 'transitionend', function handler() {
                        if ( btn.getAttribute( 'aria-expanded' ) === 'false' ) {
                            panel.setAttribute( 'hidden', '' );
                        }
                        panel.removeEventListener( 'transitionend', handler );
                    } );
                }
            }

            acc.querySelectorAll( '.accordion__button' ).forEach( function( btn ) {
                var panel = document.getElementById( btn.getAttribute( 'aria-controls' ) );
                if ( ! panel ) return;

                // Set initial max-height for open items
                if ( btn.getAttribute( 'aria-expanded' ) === 'true' ) {
                    panel.style.maxHeight = panel.scrollHeight + 'px';
                }

                btn.addEventListener( 'click', function() {
                    var isOpen = btn.getAttribute( 'aria-expanded' ) === 'true';

                    // Close any other open item first
                    acc.querySelectorAll( '.accordion__button[aria-expanded="true"]' ).forEach( function( openBtn ) {
                        if ( openBtn !== btn ) {
                            var openPanel = document.getElementById( openBtn.getAttribute( 'aria-controls' ) );
                            if ( openPanel ) setOpen( openBtn, openPanel, false );
                        }
                    } );

                    setOpen( btn, panel, ! isOpen );
                } );
            } );
        })();
        </script>
        <?php
    }
}
