<?php
/**
 * AK Counseling theme.
 *
 * - Contact details shown in the header, footer and mobile bar: Appearance > Customize > AK Counseling contact details.
 * - One-click page + menu setup: Appearance > AK Counseling Setup (inc/setup.php).
 */

defined( 'ABSPATH' ) || exit;

define( 'AK_THEME_VERSION', '1.0.0' );

require_once __DIR__ . '/inc/icons.php';
require_once __DIR__ . '/inc/walker.php';
require_once __DIR__ . '/inc/setup.php';

/** Defaults for every Customizer field (values from the existing site). */
function ak_defaults() {
	return array(
		'brand_name'    => 'AK Counseling, Inc.',
		'brand_tag'     => 'Couples Therapy & Marriage Counseling · Madison, WI',
		'owner'         => 'Alison Kilkelly, LCSW',
		'footer_blurb'  => 'Private, trustworthy, effective therapy for couples and adults.',
		'phone_display' => '608.567.4648',
		'phone_tel'     => '+16085674648',
		'email'         => 'AKCounselingMadison@gmail.com',
		'topbar_note'   => 'In-person in Madison & via Zoom',
		'address_1'     => '313 Price Place, Suite 205',
		'address_2'     => 'Madison, WI 53705',
		'map_url'       => 'https://maps.app.goo.gl/ryx2a1LV6d1sYjUs8',
		'hours'         => "In-person & virtual visits\nMonday – Saturday",
		'portal_url'    => 'https://akcounselingmadison.clientsecure.me/sign-in',
		'facebook'      => 'https://facebook.com/akcounseling',
		'linkedin'      => 'https://www.linkedin.com/in/alison-kilkelly-lcsw-5a45574',
		'instagram'     => 'https://instagram.com/akcounselingmadison',
		'psych_today'   => 'https://www.psychologytoday.com/us/therapists/alison-kilkelly-madison-wi/195607',
		'good_therapy'  => 'https://www.goodtherapy.org/therapists/profile/alison-kilkelly-20140716',
	);
}

function ak_opt( $key ) {
	$defaults = ak_defaults();
	return get_theme_mod( 'ak_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

function ak_asset( $path ) {
	return get_template_directory_uri() . '/assets/' . ltrim( $path, '/' );
}

function ak_icon( $name ) {
	$icons = ak_icons();
	if ( isset( $icons[ $name ] ) ) {
		echo $icons[ $name ]; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG markup shipped with the theme.
	}
}

function ak_social_links() {
	foreach ( array( 'facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram' ) as $key => $label ) {
		$url = ak_opt( $key );
		if ( $url ) {
			printf( '<a href="%s" aria-label="%s">', esc_url( $url ), esc_attr( $label ) );
			ak_icon( $key );
			echo '</a>';
		}
	}
}

/** True for pages whose content is a designed layout (full-width sections) rather than plain text. */
function ak_has_designed_layout( $post ) {
	return $post && ( false !== strpos( $post->post_content, 'class="page-hero' ) || false !== strpos( $post->post_content, 'class="hero' ) );
}

/** Rewrites the root-relative links/asset paths in the bundled page designs for this site. */
function ak_localize_html( $html ) {
	$html = str_replace( array( 'src="/assets/', 'href="/assets/' ), array( 'src="' . ak_asset( '' ), 'href="' . ak_asset( '' ) ), $html );
	return preg_replace_callback(
		'#\b(href|src)="/(?!/)([^"]*)"#',
		function ( $m ) {
			return $m[1] . '="' . esc_url( home_url( '/' . $m[2] ) ) . '"';
		},
		$html
	);
}

/** Menu shown until a menu is assigned to the "Main menu" location. */
function ak_fallback_nav() {
	$file = __DIR__ . '/content/nav.html';
	if ( file_exists( $file ) ) {
		echo ak_localize_html( file_get_contents( $file ) ); // phpcs:ignore -- markup shipped with the theme.
	}
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	register_nav_menus( array( 'primary' => __( 'Main menu', 'ak-counseling' ) ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'ak-styles', ak_asset( 'css/styles.css' ), array(), AK_THEME_VERSION );
	wp_enqueue_script( 'ak-main', ak_asset( 'js/main.js' ), array(), AK_THEME_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	// This theme doesn't use block-library front-end styles for its own layout; keep them for any blocks added later.
} );

add_action( 'wp_head', function () {
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( ak_asset( 'fonts/fraunces-latin-full-normal.woff2' ) ) );
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( ak_asset( 'fonts/figtree-latin-wght-normal.woff2' ) ) );
	if ( ! has_site_icon() ) {
		printf( '<link rel="icon" type="image/png" sizes="32x32" href="%s">' . "\n", esc_url( ak_asset( 'img/favicon-32.png' ) ) );
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( ak_asset( 'img/apple-touch-icon.png' ) ) );
	}
}, 5 );

/** Customizer: contact details used in the header, footer and mobile bar. */
add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'ak_contact', array(
		'title'       => __( 'AK Counseling contact details', 'ak-counseling' ),
		'description' => __( 'Shown in the top bar, footer and mobile contact bar. Text inside page content is edited on each page.', 'ak-counseling' ),
		'priority'    => 30,
	) );
	$fields = array(
		'brand_name'    => array( 'Business name (header)', 'text' ),
		'brand_tag'     => array( 'Tagline under the name', 'text' ),
		'owner'         => array( 'Name in footer', 'text' ),
		'footer_blurb'  => array( 'Footer sentence', 'text' ),
		'phone_display' => array( 'Phone, as shown (e.g. 608.567.4648)', 'text' ),
		'phone_tel'     => array( 'Phone, for tap-to-call (e.g. +16085674648)', 'text' ),
		'email'         => array( 'Email', 'email' ),
		'topbar_note'   => array( 'Short note in the top bar', 'text' ),
		'address_1'     => array( 'Address line 1', 'text' ),
		'address_2'     => array( 'Address line 2', 'text' ),
		'map_url'       => array( 'Google Maps link', 'url' ),
		'hours'         => array( 'Hours (footer)', 'textarea' ),
		'portal_url'    => array( 'Client portal link', 'url' ),
		'facebook'      => array( 'Facebook link', 'url' ),
		'linkedin'      => array( 'LinkedIn link', 'url' ),
		'instagram'     => array( 'Instagram link', 'url' ),
		'psych_today'   => array( 'Psychology Today profile', 'url' ),
		'good_therapy'  => array( 'GoodTherapy profile', 'url' ),
	);
	$defaults = ak_defaults();
	foreach ( $fields as $key => $f ) {
		$sanitize = 'url' === $f[1] ? 'esc_url_raw' : ( 'email' === $f[1] ? 'sanitize_email' : ( 'textarea' === $f[1] ? 'sanitize_textarea_field' : 'sanitize_text_field' ) );
		$wp_customize->add_setting( 'ak_' . $key, array( 'default' => $defaults[ $key ], 'sanitize_callback' => $sanitize ) );
		$wp_customize->add_control( 'ak_' . $key, array( 'label' => $f[0], 'section' => 'ak_contact', 'type' => $f[1] ) );
	}
} );

/** Keep the sticky header below the WordPress admin bar when logged in. */
add_action( 'wp_head', function () {
	if ( is_admin_bar_showing() ) {
		echo '<style>.admin-bar .site-header{top:32px}@media (max-width:782px){.admin-bar .site-header{top:46px}}@media (max-width:600px){.admin-bar .site-header{top:0}}</style>' . "\n";
	}
}, 20 );

/** Point people to the setup screen right after activation. */
add_action( 'after_switch_theme', function () {
	set_transient( 'ak_show_setup_notice', 1, DAY_IN_SECONDS );
} );
add_action( 'admin_notices', function () {
	if ( get_transient( 'ak_show_setup_notice' ) && current_user_can( 'manage_options' ) && ( ! isset( $_GET['page'] ) || 'ak-setup' !== $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		printf(
			'<div class="notice notice-info is-dismissible"><p><strong>AK Counseling theme is active.</strong> Next step: <a href="%s">open AK Counseling Setup</a> to apply the new page designs and menu.</p></div>',
			esc_url( admin_url( 'themes.php?page=ak-setup' ) )
		);
	}
} );
