<?php
/**
 * 404 (Page Not Found) Template
 *
 * WHY: When someone visits a URL that doesn't exist, this page gives them
 * a friendly message and a search box to find what they're looking for.
 * Much better than a generic server error page.
 *
 * @package SocietyPress
 */

get_header();
?>

<div class="site-content">
    <div class="content-area-full">

        <div class="error-404">
            <h1>Page Not Found</h1>
            <p>Sorry, we couldn't find the page you were looking for. It may have been moved or no longer exists.</p>
            <p>Try searching for what you need:</p>
            <?php get_search_form(); ?>
        </div>

    </div>
</div>

<?php get_footer(); ?>
