<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0a434a">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'ak-counseling' ); ?></a>

<div class="topbar">
  <div class="wrap">
    <div class="topbar__contact">
      <a href="tel:<?php echo esc_attr( ak_opt( 'phone_tel' ) ); ?>"><?php ak_icon( 'phone' ); ?><?php printf( esc_html__( 'Call or text %s', 'ak-counseling' ), esc_html( ak_opt( 'phone_display' ) ) ); ?></a>
      <a href="mailto:<?php echo esc_attr( ak_opt( 'email' ) ); ?>?subject=Website%20Inquiry"><?php ak_icon( 'mail' ); ?><?php echo esc_html( ak_opt( 'email' ) ); ?></a>
      <span class="topbar__note"><?php echo esc_html( ak_opt( 'topbar_note' ) ); ?></span>
    </div>
    <div class="topbar__social">
      <?php if ( ak_opt( 'portal_url' ) ) : ?><a class="topbar__portal" href="<?php echo esc_url( ak_opt( 'portal_url' ) ); ?>"><?php esc_html_e( 'Client portal', 'ak-counseling' ); ?></a><?php endif; ?>
      <?php ak_social_links(); ?>
    </div>
  </div>
</div>

<header class="site-header">
  <div class="wrap">
    <a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) . ' home' ); ?>">
      <img src="<?php echo esc_url( ak_asset( 'img/ak-logo.png' ) ); ?>" width="48" height="48" alt="">
      <span class="brand__text"><span class="brand__name"><?php echo esc_html( ak_opt( 'brand_name' ) ); ?></span><span class="brand__tag"><?php echo esc_html( ak_opt( 'brand_tag' ) ); ?></span></span>
    </a>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="site-nav"><?php ak_icon( 'menu' ); ?><?php esc_html_e( 'Menu', 'ak-counseling' ); ?></button>
    <nav class="nav" id="site-nav" aria-label="<?php esc_attr_e( 'Main', 'ak-counseling' ); ?>">
      <button class="menu-btn nav-close" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'ak-counseling' ); ?>"><?php ak_icon( 'close' ); ?></button>
      <ul class="nav__list">
        <?php
        if ( has_nav_menu( 'primary' ) ) {
        	wp_nav_menu( array(
        		'theme_location' => 'primary',
        		'container'      => false,
        		'items_wrap'     => '%3$s',
        		'depth'          => 2,
        		'walker'         => new AK_Nav_Walker(),
        	) );
        } else {
        	ak_fallback_nav();
        }
        ?>
      </ul>
      <a class="btn btn--primary nav__cta" href="<?php echo esc_url( home_url( '/get-started/' ) ); ?>"><?php esc_html_e( 'Get Started', 'ak-counseling' ); ?></a>
    </nav>
    <div class="nav-backdrop"></div>
  </div>
</header>

<main id="main">
