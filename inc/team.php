<form id="filters" class="two-col">
    <div class="filter-group dropdown" data-filter="Employees">
        <p>Team</p>
        <button aria-label="Filter by team">All</button>
        <ul id="employees-filter" class="filter">
            <li>
                <button data-employees="" class="js-active">All</button>
            </li>
            <?php
            $teams = get_terms( array(
                'taxonomy'   => 'team',
                'hide_empty' => false,
            ) );
            
            foreach ($teams as $team) {
                echo '<li>';
                    echo '<button data-employees="' . esc_attr($team->slug) . '">' . esc_html($team->name) . '</button>';
                echo '</li>';
            }
            ?>
        </ul>
    </div>

    <div class="filter-group dropdown" data-filter="Location">
        <p>Location</p>
        <button aria-label="Filter team member locations">All</button>
        <ul id="location-filter" class="filter">
            <li>
                <button data-location="" class="js-active">All</button>
            </li>
            <?php
                $locations = new WP_Query(array(
                    'post_type'      => 'location',
                    'posts_per_page' => -1, // Fetch all locations
                    'orderby'        => 'title',
                    'order'          => 'ASC'
                ));

                if ($locations->have_posts()) :
                    while ($locations->have_posts()) : $locations->the_post();
                        echo '<li>';
                            echo '<button data-location="' . esc_attr(get_post_field('post_name', get_the_ID())) . '">' . esc_html(get_the_title()) . '</button>';
                        echo '</li>';
                    endwhile;
                    wp_reset_postdata();
                else :
                    echo '<p>No locations found.</p>';
                endif;
            ?>
        </ul>
    </div>

</form>

<div id="team-container" class="dynamic-container">
    <!-- Posts will be loaded here dynamically -->
</div>

<button class="load-more" data-total-pages>View More</button>