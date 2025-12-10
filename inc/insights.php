<form id="filters" class="two-col">
    <div class="filter-group dropdown" data-filter="Category">
        <p>Category</p>
        <button aria-label="Filter post categories">Category</button>
        <ul id="category-filter" class="filter">
            <li>
                <button data-category="" class="js-active">All Categories</button>
            </li>
            <?php
            $categories = get_categories();
            foreach ($categories as $category) {
                echo '<li>';
                    echo '<button data-category="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</button>';
                echo '</li>';
            }
            ?>
        </ul>
    </div>

    <div class="filter-group dropdown" data-filter="Topic">
        <p>Topic</p>
        <button aria-label="Filter post topics">Topic</button>
        <ul id="topic-filter" class="filter">
            <li>
                <button data-topic="" class="js-active">All Topics</button>
            </li>
            <?php

            $topics = get_terms( array(
                'taxonomy'   => 'topic',
                'hide_empty' => false,
            ) );
            
            foreach ($topics as $topic) {
                echo '<li>';
                    echo '<button data-topic="' . esc_attr($topic->slug) . '">' . esc_html($topic->name) . '</button>';
                echo '</li>';
            }
            ?>
        </ul>
    </div>

</form>

<div id="posts-container" class="dynamic-container">
    <!-- Posts will be loaded here dynamically -->
</div>

<button class="load-more" data-total-pages>View More</button>