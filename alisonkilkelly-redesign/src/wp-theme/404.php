<?php get_header(); ?>
<section class="page-hero">
  <div class="wrap wrap--narrow">
    <span class="eyebrow"><?php esc_html_e( 'Page not found', 'ak-counseling' ); ?></span>
    <h1><?php esc_html_e( 'That page has moved or no longer exists.', 'ak-counseling' ); ?></h1>
    <div class="btn-row">
      <a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to the homepage', 'ak-counseling' ); ?></a>
      <a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/contact-location/' ) ); ?>"><?php esc_html_e( 'Contact Alison', 'ak-counseling' ); ?></a>
    </div>
  </div>
</section>
<?php get_footer();
