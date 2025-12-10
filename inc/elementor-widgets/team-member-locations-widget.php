<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Team_Member_Locations_Content_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'team_member_locations';
    }

    public function get_title() {
        return __('Team Member Locations', 'text-domain');
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
        $$post_id = get_the_ID();
        $locations = get_field('locations', $post_id);

         ?>

            <div class="elementor-widget-text-editor company-detail__managed-companies">
                <p><strong>Company Coverage</strong></p>
                <ul>
                    <?php foreach ($locations as $location) {
                        $location_title = get_the_title($location);
                    ?>
                        <li>
                            <p><?php echo $location_title ?></p>
                        </li>
                    <?php } ?>
                </ul>
                
            </div>
        <?php

        
    }
}
