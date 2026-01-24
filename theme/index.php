<?php get_header(); ?>
<main class="main-content">
    <h1>Welcome to SocietyPress</h1>
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <?php the_content(); ?>
    <?php endwhile; endif; ?>
    <section class="members-directory">
        <h2>Member Directory</h2>
        <?php echo do_shortcode('[societypress_directory]'); ?>
    </section>
</main>
<?php get_footer(); ?>
