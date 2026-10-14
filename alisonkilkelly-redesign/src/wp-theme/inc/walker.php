<?php
/**
 * Renders the "Main menu" in the theme's markup: top-level links, and top-level items with
 * children as dropdown toggles (the item's own link is not used for a toggle).
 * Menu item "Description" fields show as small grey text under dropdown links.
 */

defined( 'ABSPATH' ) || exit;

class AK_Nav_Walker extends Walker_Nav_Menu {
	private $parent_id = 0;

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="nav__sub" id="sub-' . (int) $this->parent_id . '">';
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$custom  = array_filter( (array) get_post_meta( $item->ID, '_menu_item_classes', true ) );
		$current = ! empty( $item->current );
		$title   = apply_filters( 'the_title', $item->title, $item->ID );

		if ( 0 === $depth ) {
			$has_children = in_array( 'menu-item-has-children', (array) $item->classes, true );
			$classes      = $custom;
			if ( $has_children && ( ! empty( $item->current_item_ancestor ) || ! empty( $item->current_item_parent ) ) ) {
				$classes[] = 'nav__item--current';
			}
			$output .= '<li' . ( $classes ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : '' ) . '>';
			if ( $has_children ) {
				$this->parent_id = $item->ID;
				$output         .= '<button class="nav__toggle" type="button" aria-expanded="false" aria-controls="sub-' . (int) $item->ID . '">'
					. esc_html( $title ) . ak_icon_markup( 'chevron' ) . '</button>';
			} else {
				$output .= '<a class="nav__link" href="' . esc_url( $item->url ) . '"' . ( $current ? ' aria-current="page"' : '' ) . '>' . esc_html( $title ) . '</a>';
			}
			return;
		}

		$desc    = trim( (string) $item->description );
		$output .= '<li><a href="' . esc_url( $item->url ) . '"' . ( $current ? ' aria-current="page"' : '' ) . '>' . esc_html( $title )
			. ( $desc ? '<small>' . esc_html( $desc ) . '</small>' : '' ) . '</a>';
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

function ak_icon_markup( $name ) {
	$icons = ak_icons();
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}
