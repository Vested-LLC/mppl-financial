<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Location_Services_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'location_services';
    }

    public function get_title() {
        return __('Location Services', 'text-domain');
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

        if (have_rows('services_provided', $post_id)) {
            echo '<div class="location-sections">';
            while (have_rows('services_provided', $post_id)) {
                the_row();
                $service_header = get_sub_field('service_header');
                $service_description = get_sub_field('service_description');
                $service_button_text = get_sub_field('service_button_text');
                $service_button_url = get_sub_field('service_button_url');

                echo '<div class="location-section">';
                if ($service_header) {
                    echo '<h3>' . esc_html($service_header) . '</h3>';
                }
                if ($service_description) {
                    echo $service_description;
                }
                if ($service_button_text && $service_button_url) {
                    echo '<a class="location-section__button btn btn-secondary" href="' . esc_url($service_button_url) . '">' . esc_html($service_button_text) . '</a>';
                }
                echo '</div>';
            }
            echo '</div>';
        } else {
        }
    }
}