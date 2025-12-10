<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Team_Content_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'team_content';
    }

    public function get_title() {
        return __('Team Content', 'text-domain');
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
        $post_id = !empty($this->get_settings('post_id')) ? $this->get_settings('post_id') : get_the_ID();

        if (have_rows('sections', $post_id)) {
            echo '<div class="team-sections">';
            while (have_rows('sections', $post_id)) {
                the_row();
                $section_header = get_sub_field('section_header');
                $section_content = get_sub_field('section_content');

                echo '<div class="team-section">';
                if ($section_header) {
                    echo '<h3>' . esc_html($section_header) . '</h3>';
                }
                if ($section_content) {
                    echo $section_content;
                }
                echo '</div>';
            }
            echo '</div>';
        } else {
        }
    }
}
