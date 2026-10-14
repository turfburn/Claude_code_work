<?php
/**
 * Appearance > AK Counseling Setup
 *
 * Applies the bundled page designs (content/*.html) to the matching pages, matched by web
 * address, and can build the main menu. Each page update is a normal WordPress edit, so the
 * previous version stays in the page's Revisions.
 */

defined( 'ABSPATH' ) || exit;

function ak_setup_pages() {
	$file = get_template_directory() . '/content/pages.json';
	return file_exists( $file ) ? json_decode( file_get_contents( $file ), true ) : array();
}

/** Finds the existing WordPress page for a design (front page by Reading settings, others by slug). */
function ak_find_page( $p ) {
	if ( ! empty( $p['front'] ) ) {
		$front = (int) get_option( 'page_on_front' );
		if ( $front && 'page' === get_option( 'show_on_front' ) ) {
			return get_post( $front );
		}
		foreach ( (array) $p['front_slugs'] as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				return $page;
			}
		}
		return null;
	}
	return get_page_by_path( $p['slug'] );
}

add_action( 'admin_menu', function () {
	add_theme_page( __( 'AK Counseling Setup', 'ak-counseling' ), __( 'AK Counseling Setup', 'ak-counseling' ), 'manage_options', 'ak-setup', 'ak_setup_screen' );
} );

function ak_setup_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$pages   = ak_setup_pages();
	$results = array();

	if ( isset( $_POST['ak_restore_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['ak_restore_nonce'] ), 'ak_restore' ) ) {
		$results = ak_restore_pages( $pages );
	} elseif ( isset( $_POST['ak_setup_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['ak_setup_nonce'] ), 'ak_setup' ) ) {
		$chosen  = isset( $_POST['ak_pages'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['ak_pages'] ) ) : array();
		$results = ak_apply_pages( $pages, $chosen );
		if ( ! empty( $_POST['ak_menu'] ) ) {
			$results[] = ak_build_menu( $pages );
		}
		delete_transient( 'ak_show_setup_notice' );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'AK Counseling Setup', 'ak-counseling' ); ?></h1>

		<?php if ( $results ) : ?>
			<div class="notice notice-success"><p><strong><?php esc_html_e( 'Done.', 'ak-counseling' ); ?></strong></p><ul style="list-style:disc;padding-left:1.5em">
				<?php foreach ( $results as $r ) : ?><li><?php echo wp_kses_post( $r ); ?></li><?php endforeach; ?>
			</ul><p><a class="button button-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'View the site', 'ak-counseling' ); ?></a></p></div>
		<?php endif; ?>

		<?php if ( ! current_user_can( 'unfiltered_html' ) ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Your account cannot save full HTML (common on WordPress multisite). WordPress would strip the videos, map and icons from the page designs. Ask your host or site administrator to run this step.', 'ak-counseling' ); ?></p></div>
		<?php endif; ?>

		<p style="max-width:720px"><?php esc_html_e( 'Tick the pages to update with the new design. Each page is matched by its web address. Its current text is backed up first (and added to the page’s Revisions), so the Undo button below can put it back. Pages that don’t exist yet are created.', 'ak-counseling' ); ?></p>

		<form method="post">
			<?php wp_nonce_field( 'ak_setup', 'ak_setup_nonce' ); ?>
			<table class="widefat striped" style="max-width:860px">
				<thead><tr><th style="width:2em"></th><th><?php esc_html_e( 'Page', 'ak-counseling' ); ?></th><th><?php esc_html_e( 'Web address', 'ak-counseling' ); ?></th><th><?php esc_html_e( 'Status', 'ak-counseling' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $pages as $p ) :
					$page    = ak_find_page( $p );
					$applied = $page && ak_has_designed_layout( $page );
					$url     = $p['front'] ? home_url( '/' ) : home_url( '/' . $p['slug'] . '/' );
					?>
					<tr>
						<td><input type="checkbox" name="ak_pages[]" value="<?php echo esc_attr( $p['key'] ); ?>" <?php checked( ! empty( $p['default_on'] ) && ! $applied ); ?>></td>
						<td><strong><?php echo esc_html( $p['title'] ); ?></strong><?php if ( ! empty( $p['note'] ) ) : ?><br><span class="description"><?php echo esc_html( $p['note'] ); ?></span><?php endif; ?></td>
						<td><code><?php echo esc_html( wp_parse_url( $url, PHP_URL_PATH ) ); ?></code></td>
						<td><?php echo $page ? ( $applied ? esc_html__( 'New design applied', 'ak-counseling' ) : esc_html__( 'Found — current design', 'ak-counseling' ) ) : esc_html__( 'Not found — will be created', 'ak-counseling' ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p><label><input type="checkbox" name="ak_menu" value="1" <?php checked( ! has_nav_menu( 'primary' ) ); ?>> <?php esc_html_e( 'Create the main menu (“Main Menu – AK design”) and assign it to the header. Your existing menus are not changed or deleted.', 'ak-counseling' ); ?></label></p>
			<?php submit_button( __( 'Apply new design', 'ak-counseling' ) ); ?>
		</form>

		<?php if ( ak_pages_with_backup( $pages ) ) : ?>
		<hr>
		<h2><?php esc_html_e( 'Undo', 'ak-counseling' ); ?></h2>
		<p style="max-width:720px"><?php esc_html_e( 'Puts back the text each page had before the new design was applied. Use this only if you are switching back to your previous theme.', 'ak-counseling' ); ?></p>
		<form method="post" onsubmit="return confirm('<?php echo esc_js( __( 'Restore the previous text on all redesigned pages?', 'ak-counseling' ) ); ?>');">
			<?php wp_nonce_field( 'ak_restore', 'ak_restore_nonce' ); ?>
			<?php submit_button( __( 'Restore previous page text', 'ak-counseling' ), 'secondary' ); ?>
		</form>
		<?php endif; ?>
	</div>
	<?php
}

function ak_apply_pages( $pages, $chosen ) {
	$out = array();
	foreach ( $pages as $p ) {
		if ( ! in_array( $p['key'], $chosen, true ) ) {
			continue;
		}
		$file = get_template_directory() . '/content/' . $p['file'];
		if ( ! file_exists( $file ) ) {
			continue;
		}
		$html    = ak_localize_html( file_get_contents( $file ) );
		$content = "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->";
		$page    = ak_find_page( $p );

		if ( $page ) {
			// Keep the current text recoverable no matter how revisions are configured:
			// 1) a theme backup restorable from this screen, 2) a revision of the current state.
			if ( ! ak_has_designed_layout( $page ) ) {
				update_post_meta( $page->ID, '_ak_previous_content', wp_slash( $page->post_content ) );
			}
			if ( wp_revisions_enabled( $page ) ) {
				$revs   = wp_get_post_revisions( $page->ID, array( 'posts_per_page' => 1 ) );
				$latest = $revs ? reset( $revs ) : null;
				if ( ! $latest || $latest->post_content !== $page->post_content ) {
					_wp_put_post_revision( $page );
				}
			}
			$res = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $content ) ), true );
			$msg = sprintf( '%s — updated (previous text backed up)', esc_html( $p['title'] ) );
		} else {
			$res = wp_insert_post( wp_slash( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $p['title'],
				'post_name'    => $p['front'] ? 'home' : $p['slug'],
				'post_content' => $content,
			) ), true );
			$msg = sprintf( '%s — created', esc_html( $p['title'] ) );
		}
		if ( is_wp_error( $res ) ) {
			$out[] = sprintf( '%s — <strong>failed:</strong> %s', esc_html( $p['title'] ), esc_html( $res->get_error_message() ) );
			continue;
		}
		if ( $p['front'] && ( 'page' !== get_option( 'show_on_front' ) || ! get_option( 'page_on_front' ) ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', (int) $res );
			$msg .= '; set as the homepage';
		}
		$out[] = $msg;
	}
	return $out;
}

function ak_build_menu( $pages ) {
	$name = 'Main Menu – AK design';
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		// Re-running setup rebuilds this theme's own menu from scratch.
		foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
			wp_delete_post( $item->ID, true );
		}
		$menu_id = $menu->term_id;
	} else {
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			return 'Menu — <strong>failed:</strong> ' . esc_html( $menu_id->get_error_message() );
		}
	}

	$by_key = array();
	foreach ( $pages as $p ) {
		$by_key[ $p['key'] ] = $p;
	}
	$page_item = function ( $key, $label, $parent = 0, $desc = '' ) use ( $menu_id, $by_key ) {
		$page = isset( $by_key[ $key ] ) ? ak_find_page( $by_key[ $key ] ) : null;
		$args = array( 'menu-item-title' => $label, 'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent, 'menu-item-description' => $desc );
		if ( $page ) {
			$args += array( 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $page->ID );
		} else {
			$args += array( 'menu-item-type' => 'custom', 'menu-item-url' => home_url( '/' . $by_key[ $key ]['slug'] . '/' ) );
		}
		return wp_update_nav_menu_item( $menu_id, 0, $args );
	};
	$custom_item = function ( $label, $url, $parent = 0, $classes = '' ) use ( $menu_id ) {
		return wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title' => $label, 'menu-item-url' => $url, 'menu-item-type' => 'custom',
			'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent, 'menu-item-classes' => $classes,
		) );
	};

	$custom_item( 'Home', home_url( '/' ), 0, 'nav__home-mobile' );
	$services = $custom_item( 'Services', '#' );
	$page_item( 'couplestherapy-marriagecounseling', 'Marriage & Couples Counseling', $services, 'Dating, engaged, married, co-parenting' );
	$page_item( 'therapy-coaching', 'Therapy & Coaching', $services, 'Individual therapy blended with coaching' );
	$page_item( 'pre-marital-counseling', 'Pre-Marital Counseling', $services, 'The 12 most vital topics before “I do”' );
	$page_item( 'infidelity', 'Infidelity Recovery', $services, 'Heal after an affair, together or alone' );
	$page_item( 'about', 'About Alison' );
	$page_item( 'what-to-expect', 'What to Expect' );
	$clients = $custom_item( 'What Clients Say', '#' );
	$page_item( 'what-clients-say', 'All client stories', $clients );
	$base = home_url( '/what-clients-say/' );
	foreach ( array(
		'exceptional-marital-therapy' => 'Exceptional marital therapy',
		'depression-anxiety-parenting' => 'Depression, Anxiety, Parenting',
		'not-hopelessly-broken'        => 'NOT hopelessly broken after all!',
		'abusive-significant-other'    => 'Mentally, emotionally, physically abusive significant other',
		'alison-saved-my-life'         => 'Alison saved my life…',
		'diminished-anxiety'           => 'Diminished anxiety dramatically improved my outlook on life!',
	) as $anchor => $label ) {
		$custom_item( $label, $base . '#' . $anchor, $clients );
	}
	$page_item( 'rates', 'Rates' );
	$page_item( 'resources', 'Resources' );
	$page_item( 'contact-location', 'Contact' );

	$locations            = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	return sprintf( 'Main menu — %s and set as the header menu (edit it under Appearance &gt; Menus)', $menu ? 'rebuilt' : 'created' );
}

function ak_pages_with_backup( $pages ) {
	$found = array();
	foreach ( $pages as $p ) {
		$page = ak_find_page( $p );
		if ( $page && metadata_exists( 'post', $page->ID, '_ak_previous_content' ) ) {
			$found[] = array( $p, $page );
		}
	}
	return $found;
}

function ak_restore_pages( $pages ) {
	$out = array();
	foreach ( ak_pages_with_backup( $pages ) as list( $p, $page ) ) {
		$old = get_post_meta( $page->ID, '_ak_previous_content', true );
		$res = wp_update_post( wp_slash( array( 'ID' => $page->ID, 'post_content' => $old ) ), true );
		if ( is_wp_error( $res ) ) {
			$out[] = sprintf( '%s — <strong>failed:</strong> %s', esc_html( $p['title'] ), esc_html( $res->get_error_message() ) );
			continue;
		}
		delete_post_meta( $page->ID, '_ak_previous_content' );
		$out[] = sprintf( '%s — previous text restored', esc_html( $p['title'] ) );
	}
	return $out;
}
