<?php
/**
 * Fallback template: blog index, single posts, testimonials, search results, archives.
 */
get_header(); ?>
<section class="page-hero">
  <div class="wrap">
    <?php if ( is_singular() ) : ?>
      <h1><?php single_post_title(); ?></h1>
    <?php elseif ( is_search() ) : ?>
      <h1><?php printf( esc_html__( 'Search results for “%s”', 'ak-counseling' ), esc_html( get_search_query() ) ); ?></h1>
    <?php else : ?>
      <h1><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ?: get_bloginfo( 'name' ) ); ?></h1>
    <?php endif; ?>
  </div>
</section>
<section class="section section--white">
  <div class="wrap wrap--narrow prose">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
      <article <?php post_class( 'mb-0' ); ?>>
        <?php if ( ! is_singular() ) : ?><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php endif; ?>
        <?php is_singular() ? the_content() : the_excerpt(); ?>
      </article>
    <?php endwhile; the_posts_pagination(); else : ?>
      <p><?php esc_html_e( 'Nothing found.', 'ak-counseling' ); ?></p>
    <?php endif; ?>
  </div>
</section>
<?php get_footer();
