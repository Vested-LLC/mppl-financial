<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementorChild
 */

/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
function hello_elementor_child_enqueue_scripts() {
    $cache_bust_css = filemtime( get_stylesheet_directory() . '/style.css' );
    $cache_bust_sass = filemtime( get_stylesheet_directory() . '/dist/css/main.css' );
    $cache_bust_js = filemtime( get_stylesheet_directory() . '/dist/js/script.js' );

    
    wp_enqueue_style( 'hello-elementor-child-style-sass', get_stylesheet_directory_uri() . '/dist/css/main.css', array(), $cache_bust_sass );
    wp_enqueue_style('hello-elementor-child-style', get_stylesheet_directory_uri() .'/style.css', array(), $cache_bust_css );

    wp_enqueue_script('script', get_stylesheet_directory_uri() . '/dist/js/script.js', array('jquery'), $cache_bust_js);

    wp_localize_script(
        'script',
        'ajaxurl',
        ['url' => admin_url('admin-ajax.php')]
    );
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts' );

add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

// Include Elementor custom widgets
require_once get_stylesheet_directory() . '/inc/elementor-widgets/register-widgets.php';

function prevent_location_redirect($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // Check if we are on the Team Members archive page and filtering by location
    if (isset($_GET['location']) && is_post_type_archive('team')) {
        $location_slug = sanitize_text_field($_GET['location']); // Get the query parameter safely

        // Prevent redirect by explicitly setting the query to fetch only team members
        $query->set('post_type', 'team');
        $query->set('tax_query', array(
            array(
                'taxonomy' => 'location',
                'field'    => 'slug',
                'terms'    => $location_slug,
            ),
        ));
    }
}
add_action('pre_get_posts', 'prevent_location_redirect');


add_action('elementor/query/team_by_location', function($query) {
    if (!is_singular('location')) {
        return;
    }

    $current_location_slug = get_post_field('post_name', get_the_ID()); // Get the current Location post slug

    $query->set('tax_query', array(
        array(
            'taxonomy' => 'location',
            'field'    => 'slug',
            'terms'    => $current_location_slug,
        ),
    ));
});

function social_icons_shortcode() {
    ob_start();

    // Define the social media platforms and their corresponding custom field keys
    $social_media = [
        'facebook' => 'social_media_facebook_link',
        'x' => 'social_media_x_link',
        'linkedin' => 'social_media_linkedin_link',
        'instagram' => 'social_media_instagram_link',
    ];

    echo '<ul class="social-icons">';

    foreach ($social_media as $platform => $field_key) {
        $link = get_field($field_key);
        if ($link) {
            echo '<li class="social-icons__icon">';
                echo '<a href="' . esc_url($link) . '" target="_blank" title="' . $platform . '" class="' . $platform . '"></a>';
            echo '</li>';
        }
    }

    echo '</ul>';

    return ob_get_clean();
}

add_shortcode('social_icons', 'social_icons_shortcode');

function fetch_filtered_posts() {
    if (!isset($_POST['action']) || $_POST['action'] !== 'fetch_filtered_posts') {
        wp_send_json_error(['message' => 'Invalid action'], 400);
    }

    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $topic = isset($_POST['topic']) ? sanitize_text_field($_POST['topic']) : '';

    // Query posts
    $args = [
        'post_type' => 'post',
        'posts_per_page' => 12,
        'paged' => $paged,  
        'category_name'  => $category,
        'post_status'  => 'publish',
    ];

    if ($topic) {
        $args['tax_query'][] = [
            'taxonomy' => 'topic',
            'field' => 'slug',
            'terms' => $topic,
        ];
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            echo '<article class="elementor-invisible" data-page="' . esc_attr($paged) . '">';
            if (has_post_thumbnail($post->ID)) {
                echo '<div class="post-image">';
                $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail');
                echo '<img src="' . $image[0] . '" alt="' . get_the_title() . '" />';
                echo '</div>';
            }
            echo '<div class="post-meta">';
                echo '<div class="post-meta__content">';
                    echo '<h2><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>'; ?>
                    <p>
                        <?php $excerpt = get_the_excerpt();
                        $excerpt = substr( $excerpt , 0, 120); 
                        echo $excerpt . '...'; ?>
                    </p>
                <?php echo '</div>';
                echo '<div class="post-meta__cta">';
                    echo '<a title="' . get_the_title() . '" href="' . get_permalink() . '">Read More</a>';
                echo '</div>';
            echo '</div>';
            echo '</article>';
        }

        echo '<div class="pagination" data-total-pages="' .  $query->max_num_pages . '" aria-hidden="true"></div>';
        

    } else {
        echo '<div id="no-posts">';
            echo '<p>No insights found</p>';
            echo '<button>Reset Filters</button>';
        echo '</div>';
    }

    wp_reset_postdata();
    wp_die();
}

add_action('wp_ajax_fetch_filtered_posts', 'fetch_filtered_posts');
add_action('wp_ajax_nopriv_fetch_filtered_posts', 'fetch_filtered_posts');

function fetch_filtered_team_members() {
    if (!isset($_POST['action']) || $_POST['action'] !== 'fetch_filtered_team_members') {
        wp_send_json_error(['message' => 'Invalid action'], 400);
    }

    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $employees = isset($_POST['employees']) ? sanitize_text_field($_POST['employees']) : '';
    $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';

    // Query posts
    $args = [
        'post_type' => 'team',
        'orderby'   => 'title',
        'order'     => 'ASC',
        'posts_per_page' => 12,
        'paged'     => $paged,
        'post_status' => 'publish',
        'tax_query' => [],
        'meta_query' => [],
    ];
    
    if (!empty($employees) || !empty($location)) {
        $args['tax_query'] = [
            'relation' => 'AND', // Ensure both conditions must be met
        ];
    
        if (!empty($employees)) {
            $args['tax_query'][] = [
                'taxonomy' => 'team',
                'field'    => 'slug',
                'terms'    => $employees,
            ];
        }
    
        if (!empty($location)) {
            $args['tax_query'][] = [
                'taxonomy' => 'location',
                'field'    => 'slug',
                'terms'    => $location,
            ];
        }
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            
            echo '<div class="team-member elementor-invisible" data-page="' . esc_attr($paged) . '">';

                if (has_post_thumbnail($post->ID)) {
                    echo '<div class="team-member__image">';
                    $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail');
                    echo '<a href="' . get_permalink() . '" aria-label="Go to ' . get_the_title() . 's bio"><img src="' . $image[0] . '" alt="' . get_the_title() . '" /></a>';
                    echo '</div>';
                }
                echo '<div class="team-member__info">';
                    echo '<h2 class="team-member__info-name"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
                    echo '<p class="team-member__info-position">' . get_field('title') . '</p>';
                echo '</div>';
            echo '</div>';
        }

        echo '<div class="pagination" data-total-pages="' .  $query->max_num_pages . '" aria-hidden="true"></div>';
    
    } else {
        echo '<div id="no-posts">';
            echo '<p>No team members found</p>';
            echo '<button>Reset Filters</button>';
        echo '</div>';
    }

    wp_reset_postdata();
    wp_die();
}

add_action('wp_ajax_fetch_filtered_team_members', 'fetch_filtered_team_members');
add_action('wp_ajax_nopriv_fetch_filtered_team_members', 'fetch_filtered_team_members');

// Shortcodes
function insights() {
    ob_start();
    include( (file_exists(get_stylesheet_directory().'/inc/insights.php') ? get_stylesheet_directory() : get_template_directory()) . '/inc/insights.php' );
    return ob_get_clean();    
}
add_shortcode('insights', 'insights');

function team() {
    ob_start();
    include( (file_exists(get_stylesheet_directory().'/inc/team.php')    ? get_stylesheet_directory() : get_template_directory()) . '/inc/team.php' );
    return ob_get_clean();    
}
add_shortcode('team', 'team');

function nav() {
    ob_start();
    include( (file_exists(get_stylesheet_directory().'/inc/nav.php')    ? get_stylesheet_directory() : get_template_directory()) . '/inc/nav.php' );
    return ob_get_clean();    
}
add_shortcode('nav', 'nav');

function current_year() {
    $year = date('Y');
    return $year;
}

add_shortcode('current_year', 'current_year');

/**
* Filters the buttons.
* Replaces the form's <input> buttons with <button> while maintaining attributes from original <input>.
*
* @param string $button Contains the <input> tag to be filtered.
* @param array  $form    Contains all the properties of the current form.
*
* @return string The filtered button.
*/
add_filter( 'gform_submit_button', 'input_to_button', 10, 2 );
function input_to_button( $button, $form ) {
    $fragment = WP_HTML_Processor::create_fragment( $button );
    $fragment->next_token();
 
    $attributes = array( 'id', 'type', 'class', 'onclick' );
    $new_attributes = array();
    foreach ( $attributes as $attribute ) {
        $value = $fragment->get_attribute( $attribute );
        if ( ! empty( $value ) ) {
            $new_attributes[] = sprintf( '%s="%s"', $attribute, esc_attr( $value ) );
        }
    }
 
    return sprintf( '<button %s>%s</button>', implode( ' ', $new_attributes ), esc_html( $fragment->get_attribute( 'value' ) ) );
}