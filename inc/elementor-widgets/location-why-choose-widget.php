<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Location_Why_Choose_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'location_why_choose';
    }

    public function get_title() {
        return __('Location - Why Choose MPPL?', 'text-domain');
    }

    public function get_icon() {
        return 'eicon-post-list';
    }
    
    public function get_categories() {
        return ['general'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'text-domain'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $post_id = $this->get_settings('post_id');
        if (empty($post_id)) {
            $post_id = get_queried_object_id();
        }
        if (empty($post_id)) {
            $post_id = get_the_ID();
        }

        if (have_rows('why_choose_items', $post_id)) {
            echo '<div class="location-why">';
            while (have_rows('why_choose_items', $post_id)) {
                the_row();
                $why_choose_header = get_sub_field('why_choose_heading');
                $why_choose_description = get_sub_field('why_choose_descriptor');

                echo '<div class="location-why-item">';
                if ($why_choose_header) {
                    echo '<h3>' . esc_html($why_choose_header) . '</h3>';
                }
                if ($why_choose_description) {
                    echo $why_choose_description;
                }
                echo '</div>';
            }
            echo '</div>';
        } else {
        }
    }
}