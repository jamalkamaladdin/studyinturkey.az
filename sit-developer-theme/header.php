<?php
/**
 * Sitenin başlığı.
 */

defined( 'ABSPATH' ) || exit;

$account = sit_theme_account_urls();
$login   = sit_theme_localize_url( $account['login'] );
$reg     = sit_theme_localize_url( $account['register'] );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'min-h-screen min-h-[100dvh] flex flex-col font-sans' ); ?>>
<?php wp_body_open(); ?>
<a class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[100] focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:shadow dark:focus:bg-slate-900 dark:focus:text-white" href="#main-content"><?php esc_html_e( 'Məzmuna keç', 'studyinturkey' ); ?></a>

<header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 pt-[env(safe-area-inset-top,0)] backdrop-blur supports-[backdrop-filter]:bg-white/80 dark:border-slate-700/80 dark:bg-slate-950/95 dark:supports-[backdrop-filter]:bg-slate-950/80">
	<div class="sit-container flex h-16 items-center justify-between gap-4 lg:h-[4.25rem]">
		<div class="flex min-w-0 items-center gap-3">
			<button type="button" class="inline-flex h-11 min-w-[2.75rem] items-center justify-center rounded-lg border border-slate-200 text-slate-700 touch-manipulation lg:hidden dark:border-slate-600 dark:text-slate-200" aria-expanded="false" aria-controls="sit-primary-nav" data-sit-nav-toggle>
				<span class="sr-only"><?php esc_html_e( 'Menyunu aç', 'studyinturkey' ); ?></span>
				<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
			</button>
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				$home = sit_theme_localize_url( home_url( '/' ) );
				?>
				<a href="<?php echo esc_url( $home ); ?>" class="flex items-center gap-2 truncate font-semibold text-slate-900 dark:text-white">
					<span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-600 text-sm font-bold text-white">SİT</span>
					<span class="hidden sm:inline"><?php bloginfo( 'name' ); ?></span>
				</a>
				<?php
			}
			?>
		</div>

		<nav id="sit-primary-nav" class="sit-primary-nav hidden lg:flex lg:flex-1 lg:justify-center" aria-label="<?php esc_attr_e( 'Əsas naviqasiya', 'studyinturkey' ); ?>">
			<?php
			wp_nav_menu(
				[
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'flex flex-col gap-1 text-sm font-medium text-slate-700 sm:flex-row sm:items-center sm:gap-6 lg:flex-row dark:text-slate-200',
					'fallback_cb'    => 'sit_theme_primary_menu_fallback',
					'depth'          => 2,
				]
			);
			?>
		</nav>

		<div class="flex shrink-0 items-center gap-2 sm:gap-3">
			<button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-600 touch-manipulation hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800" data-sit-dark-toggle aria-label="<?php esc_attr_e( 'İşıqlı və ya qaranlıq görünüş', 'studyinturkey' ); ?>">
				<span class="dark:hidden" aria-hidden="true">
					<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
				</span>
				<span class="hidden dark:inline" aria-hidden="true">
					<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
				</span>
			</button>
			<?php if ( shortcode_exists( 'sit_language_switcher' ) ) : ?>
				<div class="sit-header-lang hidden max-w-[10rem] sm:block">
					<?php echo do_shortcode( '[sit_language_switcher type="dropdown" show_flags="1" show_names="1"]' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( is_user_logged_in() ) : ?>
				<a class="hidden rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 sm:inline-block dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800" href="<?php echo esc_url( sit_theme_localize_url( $account['portal'] ) ); ?>">
					<?php esc_html_e( 'Kabinet', 'studyinturkey' ); ?>
				</a>
			<?php else : ?>
				<a class="hidden rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 sm:inline-block dark:text-slate-300 dark:hover:text-white" href="<?php echo esc_url( $login ); ?>"><?php esc_html_e( 'Giriş', 'studyinturkey' ); ?></a>
				<a class="rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 dark:bg-brand-600 dark:hover:bg-brand-500" href="<?php echo esc_url( $reg ); ?>"><?php esc_html_e( 'Qeydiyyat', 'studyinturkey' ); ?></a>
			<?php endif; ?>
		</div>
	</div>

	<div class="sit-container hidden border-t border-slate-100 pb-3 dark:border-slate-800 lg:hidden" data-sit-nav-panel>
		<?php
		wp_nav_menu(
			[
				'theme_location'     => 'primary',
				'container'          => false,
				'menu_class'         => 'flex flex-col gap-1 pt-3 text-sm font-medium text-slate-700 dark:text-slate-200',
				'fallback_cb'        => 'sit_theme_primary_menu_fallback',
				'depth'              => 2,
				'sit_strip_item_ids' => true,
			]
		);
		if ( shortcode_exists( 'sit_language_switcher' ) ) {
			echo '<div class="sit-header-lang mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">';
			echo do_shortcode( '[sit_language_switcher type="list" show_flags="1" show_names="1"]' );
			echo '</div>';
		}
		?>
	</div>
</header>
