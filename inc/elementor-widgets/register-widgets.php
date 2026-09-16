<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function register_custom_elementor_widgets($widgets_manager) {
    require_once get_stylesheet_directory() . '/inc/elementor-widgets/team-content-widget.php';
    require_once get_stylesheet_directory() . '/inc/elementor-widgets/team-member-locations-widget.php';
    require_once get_stylesheet_directory() . '/inc/elementor-widgets/location-content-widget.php';
    require_once get_stylesheet_directory() . '/inc/elementor-widgets/location-services-widget.php';
    require_once get_stylesheet_directory() . '/inc/elementor-widgets/location-why-choose-widget.php';
    require_once get_stylesheet_directory() . '/inc/elementor-widgets/slider-widget.php';
    require_once get_stylesheet_directory() . '/inc/elementor-widgets/accordion-widget.php';
    require_once get_stylesheet_directory() . '/inc/elementor-widgets/awards-slider-widget.php';

    $widgets_manager->register(new \Team_Content_Widget());
    $widgets_manager->register(new \Team_Member_Locations_Content_Widget());
    $widgets_manager->register(new \Location_Content_Widget());
    $widgets_manager->register(new \Location_Services_Widget());
    $widgets_manager->register(new \Location_Why_Choose_Widget());
    $widgets_manager->register(new \Slider_Widget());
    $widgets_manager->register(new \Accordion_Widget());
    $widgets_manager->register(new \Awards_Slider_Widget());
}

add_action('elementor/widgets/register', 'register_custom_elementor_widgets');
