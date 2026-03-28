<?php
/**
 * SIT Developer Theme — əsas funksiyalar.
 */

defined( 'ABSPATH' ) || exit;

define( 'SIT_THEME_VERSION', '1.1.0' );
define( 'SIT_THEME_DIR', get_template_directory() );
define( 'SIT_THEME_URI', get_template_directory_uri() );

/**
 * Qaranlıq rejim: FOUC-un qarşısını almaq üçün html.dark sinifini tez təyin edir.
 */
function sit_theme_dark_mode_boot_script(): void {
	?>
<script>
(function(){try{var k='sit-theme-pref';var v=localStorage.getItem(k);var d=v==='dark'||(v!=='light'&&window.matchMedia('(prefers-color-scheme: dark)').matches);if(d)document.documentElement.classList.add('dark');}catch(e){}})();
</script>
	<?php
}
add_action( 'wp_head', 'sit_theme_dark_mode_boot_script', -99 );

require_once SIT_THEME_DIR . '/inc/setup.php';
require_once SIT_THEME_DIR . '/inc/template-functions.php';
require_once SIT_THEME_DIR . '/inc/query-filters.php';
require_once SIT_THEME_DIR . '/inc/contact-form.php';
require_once SIT_THEME_DIR . '/inc/seo-sitemap.php';
require_once SIT_THEME_DIR . '/inc/seo.php';
require_once SIT_THEME_DIR . '/inc/performance.php';

/**
 * Frontend aktivlər.
 */
function sit_theme_assets(): void {
	$css_path = SIT_THEME_DIR . '/assets/css/app.css';
	$ver      = file_exists( $css_path ) ? (string) filemtime( $css_path ) : SIT_THEME_VERSION;

	wp_enqueue_style(
		'sit-theme-app',
		SIT_THEME_URI . '/assets/css/app.css',
		[],
		$ver
	);

	wp_enqueue_style(
		'sit-theme-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
		[],
		null
	);

	wp_enqueue_script(
		'sit-theme',
		SIT_THEME_URI . '/assets/js/theme.js',
		[],
		file_exists( SIT_THEME_DIR . '/assets/js/theme.js' ) ? (string) filemtime( SIT_THEME_DIR . '/assets/js/theme.js' ) : SIT_THEME_VERSION,
		[
			'in_footer' => true,
			'strategy'  => 'defer',
		]
	);
}
add_action( 'wp_enqueue_scripts', 'sit_theme_assets' );

/**
 * İkinci əsas menyu nümunəsində təkrarlanan HTML id-lərinin qarşısını alır.
 *
 * @param string    $menu_id Element id.
 * @param WP_Post   $item    Menyu elementi.
 * @param stdClass  $args    wp_nav_menu arqumentləri.
 */
function sit_theme_nav_menu_item_id( string $menu_id, $item, $args, $depth = 0 ): string {
	unset( $depth );
	if ( is_object( $args ) && ! empty( $args->sit_strip_item_ids ) ) {
		return '';
	}
	return $menu_id;
}
add_filter( 'nav_menu_item_id', 'sit_theme_nav_menu_item_id', 10, 4 );

/**
 * Proqram arxivi: REST ilə AJAX (sit/v1/programs).
 */
function sit_theme_program_archive_assets(): void {
	if ( ! is_post_type_archive( 'program' ) ) {
		return;
	}

	$path = SIT_THEME_DIR . '/assets/js/programs-archive.js';
	$uri  = SIT_THEME_URI . '/assets/js/programs-archive.js';
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : SIT_THEME_VERSION;

	wp_enqueue_script( 'sit-programs-archive', $uri, [], $ver, true );

	$lang = function_exists( 'sit_get_current_lang' ) ? sit_get_current_lang() : '';

	wp_localize_script(
		'sit-programs-archive',
		'sitProgramsArchive',
		[
			'restUrl'    => esc_url_raw( rest_url( 'sit/v1/programs' ) ),
			'archiveUrl' => esc_url_raw( sit_theme_programs_archive_url() ),
			'lang'       => sanitize_key( $lang ),
			'locale'     => get_locale(),
			'perPage'    => 12,
			'strings'    => [
				'summary'        => __( '%d proqram tapıldı.', 'studyinturkey' ),
				'none'           => __( 'Nəticə yoxdur.', 'studyinturkey' ),
				'empty'          => __( 'Filtrlərə uyğun proqram tapılmadı.', 'studyinturkey' ),
				'error'          => __( 'Məlumat yüklənərkən xəta baş verdi. Səhifəni yeniləyin.', 'studyinturkey' ),
				'prev'           => __( 'Əvvəlki', 'studyinturkey' ),
				'next'           => __( 'Növbəti', 'studyinturkey' ),
				'pageOf'         => __( 'Səhifə %1$s / %2$s', 'studyinturkey' ),
				'view'           => __( 'Bax', 'studyinturkey' ),
				'scholarshipYes' => __( 'Bəli', 'studyinturkey' ),
			],
		]
	);
}
add_action( 'wp_enqueue_scripts', 'sit_theme_program_archive_assets', 20 );

/**
 * Tək proqramda müraciət shortcode üçün plugin stilləri (məzmunda shortcode olmayanda).
 */
function sit_theme_single_program_assets(): void {
	if ( ! is_singular( 'program' ) ) {
		return;
	}
	if ( ! defined( 'SIT_APPLICATION_URL' ) || ! defined( 'SIT_APPLICATION_VERSION' ) ) {
		return;
	}
	wp_enqueue_style(
		'sit-application-form',
		SIT_APPLICATION_URL . 'assets/css/sit-application-form.css',
		[],
		SIT_APPLICATION_VERSION
	);
	wp_enqueue_style(
		'sit-application-account',
		SIT_APPLICATION_URL . 'assets/css/sit-application-account.css',
		[],
		SIT_APPLICATION_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'sit_theme_single_program_assets', 15 );
