<?php
/**
 * Tema dəstəyi və menyular.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tema konfiqurasiyası.
 */
function sit_theme_setup(): void {
	load_theme_textdomain( 'studyinturkey', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		[
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		]
	);
	add_theme_support( 'custom-logo', [
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	] );

	register_nav_menus(
		[
			'primary' => __( 'Əsas menyu', 'studyinturkey' ),
			'footer'  => __( 'Altbilgi menyusu', 'studyinturkey' ),
		]
	);
}
add_action( 'after_setup_theme', 'sit_theme_setup' );

/**
 * Widget areas (lazım olsa).
 */
function sit_theme_widgets_init(): void {
	register_sidebar(
		[
			'name'          => __( 'Altbilgi 1', 'studyinturkey' ),
			'id'            => 'footer-1',
			'description'   => __( 'Altbilgi sütunu.', 'studyinturkey' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s mb-4">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="font-semibold text-slate-900 mb-2">',
			'after_title'   => '</h3>',
		]
	);
}
add_action( 'widgets_init', 'sit_theme_widgets_init' );
