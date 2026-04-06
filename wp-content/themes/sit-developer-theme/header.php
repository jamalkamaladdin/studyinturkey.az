<?php
/**
 * Header — Figma v2: top bar + nav + lang dropdown + brand colors.
 */
defined( 'ABSPATH' ) || exit;
$account = sit_theme_account_urls();
$home    = sit_theme_localize_url( home_url( '/' ) );
$phone   = get_theme_mod( 'sit_footer_phone', '+90 501 012 77 88' );
$socials = sit_theme_get_social_links();
$insta   = $socials['instagram'] ?? '';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'min-h-screen min-h-[100dvh] flex flex-col font-sans' ); ?>>
<?php wp_body_open(); ?>
<a class="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[100] focus:rounded-md focus:bg-white focus:px-3 focus:py-2 focus:shadow" href="#main-content"><?php esc_html_e( 'Məzmuna keç', 'studyinturkey' ); ?></a>

<!-- Top Bar -->
<div class="hidden bg-[#11676a] text-white/80 text-xs lg:block">
	<div class="sit-container flex h-9 items-center justify-between">
		<div class="flex items-center gap-5">
			<?php if ( $phone ) : ?>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>" class="flex items-center gap-1.5 transition hover:text-white">
					<svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
					<?php echo esc_html( $phone ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $insta ) : ?>
				<span class="h-3 w-px bg-white/20"></span>
				<a href="<?php echo esc_url( $insta ); ?>" target="_blank" rel="noopener" class="flex items-center gap-1.5 transition hover:text-white">
					<svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
					@studyinturkey
				</a>
			<?php endif; ?>
		</div>
		<span class="text-white/50"><?php esc_html_e( 'Mon-Fri 09:00-18:00', 'studyinturkey' ); ?></span>
	</div>
</div>

<!-- Main Header -->
<header class="sticky top-0 z-50 w-full border-b border-transparent bg-white transition-all duration-500 admin-bar:top-8 dark:bg-slate-950" id="sit-header">
	<div class="sit-container flex h-16 items-center justify-between lg:h-[68px]">
		<!-- Logo -->
		<?php if ( has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php else : ?>
			<a href="<?php echo esc_url( $home ); ?>" class="z-50 shrink-0 text-[22px] font-extrabold tracking-tight text-[#0a1a1b]" style="line-height:1.1">StudyIn<span class="text-[#ff3131]">Turkey</span></a>
		<?php endif; ?>

		<!-- Desktop Nav -->
		<nav class="hidden items-center gap-1 lg:flex" aria-label="<?php esc_attr_e( 'Əsas naviqasiya', 'studyinturkey' ); ?>">
			<?php
			wp_nav_menu([
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'sit-nav-menu flex items-center gap-1',
				'fallback_cb'    => 'sit_theme_primary_menu_fallback',
				'depth'          => 2,
			]);
			?>
		</nav>

		<!-- Desktop Actions -->
		<div class="hidden items-center gap-2 lg:flex">
			<!-- Language -->
			<?php if ( function_exists( 'sit_get_current_lang' ) && class_exists( 'SIT_Languages' ) ) :
				$sit_langs    = SIT_Languages::get_active_languages();
				$sit_cur_lang = sit_get_current_lang();
				$sit_cur_flag = '🌐'; $sit_cur_name = 'Language';
				foreach ( $sit_langs as $sl ) { if ( $sl->code === $sit_cur_lang ) { $sit_cur_flag = $sl->flag ?: '🌐'; $sit_cur_name = $sl->native_name; break; } }
			?>
				<div class="relative" data-sit-lang-wrap>
					<button type="button" class="flex items-center gap-2 rounded-lg px-3 py-2 text-[13px] font-semibold text-[#3d4f5f] transition-all hover:bg-gray-50" data-sit-lang-toggle>
						<svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
						<span class="text-base leading-none"><?php echo esc_html( $sit_cur_flag ); ?></span>
						<span class="hidden xl:inline"><?php echo esc_html( $sit_cur_name ); ?></span>
						<svg class="h-3 w-3 text-gray-400 transition-transform duration-200" data-sit-lang-chevron fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
					</button>
					<div class="absolute end-0 top-full z-50 mt-2 hidden w-60 overflow-hidden rounded-2xl border border-gray-100 bg-white p-1.5 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.15)]" data-sit-lang-dropdown>
						<div class="max-h-56 overflow-y-auto py-1">
							<?php foreach ( $sit_langs as $sl ) :
								$sl_url = sit_get_page_url_in_language( $sl->code );
								$is_cur = $sl->code === $sit_cur_lang;
							?>
								<a href="<?php echo esc_url( $sl_url ); ?>" class="flex items-center justify-between rounded-xl px-3 py-2 text-[13px] transition-all <?php echo $is_cur ? 'bg-[#e6f2f2] text-brand-700' : 'text-[#3d4f5f] hover:bg-gray-50'; ?>">
									<span class="flex items-center gap-2.5">
										<span class="text-base leading-none"><?php echo esc_html( $sl->flag ?: '🌐' ); ?></span>
										<span class="font-medium"><?php echo esc_html( $sl->native_name ); ?></span>
									</span>
									<?php if ( $is_cur ) : ?>
										<svg class="h-3.5 w-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
									<?php endif; ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- Dark mode -->
			<button type="button" class="rounded-lg p-2 text-[#3d4f5f] transition hover:bg-gray-50" data-sit-dark-toggle aria-label="<?php esc_attr_e( 'İşıqlı / qaranlıq', 'studyinturkey' ); ?>">
				<span class="dark:hidden"><svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg></span>
				<span class="hidden dark:inline"><svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg></span>
			</button>

			<!-- Search -->
			<button type="button" class="rounded-lg p-2 text-[#3d4f5f] transition hover:bg-gray-50" data-sit-search-toggle aria-label="<?php esc_attr_e( 'Axtar', 'studyinturkey' ); ?>">
				<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
			</button>
		</div>

		<!-- Mobile: search + hamburger -->
		<div class="flex items-center gap-1 lg:hidden">
			<button type="button" class="rounded-lg p-2 text-[#0a1a1b]" data-sit-search-toggle><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></button>
			<button type="button" class="rounded-xl p-2 text-[#0a1a1b] transition hover:bg-gray-50" data-sit-nav-toggle aria-expanded="false">
				<svg class="h-6 w-6" data-sit-nav-icon-open fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
				<svg class="hidden h-6 w-6" data-sit-nav-icon-close fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
			</button>
		</div>
	</div>
</header>

<!-- Mobile drawer -->
<div class="fixed inset-0 z-40 hidden lg:hidden" data-sit-nav-panel>
	<div class="absolute inset-0 bg-[#11676a]/50 backdrop-blur-sm" data-sit-nav-backdrop></div>
	<div class="absolute top-0 right-0 flex h-full w-[85%] max-w-sm flex-col bg-white shadow-[-8px_0_30px_rgba(0,0,0,0.1)]" data-sit-nav-drawer>
		<div class="flex-1 overflow-y-auto px-6 pt-20 pb-8">
			<?php wp_nav_menu(['theme_location'=>'primary','container'=>false,'menu_class'=>'sit-nav-menu-mobile flex flex-col gap-1 mb-8','fallback_cb'=>'sit_theme_primary_menu_fallback','depth'=>2,'sit_strip_item_ids'=>true]); ?>
			<?php if ( function_exists( 'sit_get_current_lang' ) && class_exists( 'SIT_Languages' ) ) : ?>
				<div class="border-t border-gray-100 pt-6">
					<h3 class="mb-3 px-1 text-[11px] font-bold uppercase tracking-[0.1em] text-gray-400"><?php esc_html_e( 'Dil', 'studyinturkey' ); ?></h3>
					<div class="space-y-0.5 rounded-xl border border-gray-100 bg-gray-50 p-1.5">
						<?php foreach ( SIT_Languages::get_active_languages() as $sl ) : $is_cur = $sl->code === sit_get_current_lang(); ?>
							<a href="<?php echo esc_url( sit_get_page_url_in_language( $sl->code ) ); ?>" class="flex items-center justify-between rounded-lg px-3 py-2.5 text-[13px] transition <?php echo $is_cur ? 'bg-white text-brand-700 shadow-sm' : 'text-[#3d4f5f] hover:bg-white'; ?>">
								<span class="flex items-center gap-2.5"><span class="text-base leading-none"><?php echo esc_html( $sl->flag ?: '🌐' ); ?></span><span class="font-medium"><?php echo esc_html( $sl->native_name ); ?></span></span>
								<?php if ( $is_cur ) : ?><svg class="h-3.5 w-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><?php endif; ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Search overlay -->
<div class="fixed inset-0 z-[60] hidden" data-sit-search-overlay aria-modal="true" role="dialog">
	<div class="absolute inset-0 bg-[#11676a]/80 backdrop-blur-sm" data-sit-search-backdrop></div>
	<div class="relative mx-auto flex h-full max-w-2xl flex-col px-4 pt-[10vh]">
		<div class="rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
			<div class="flex items-center gap-3 border-b border-gray-200 px-4">
				<svg class="h-5 w-5 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
				<input type="search" class="h-14 flex-1 border-0 bg-transparent text-base text-gray-900 outline-none placeholder:text-gray-400 dark:text-white" placeholder="<?php esc_attr_e( 'Universitet, proqram və ya bloq axtar...', 'studyinturkey' ); ?>" data-sit-search-input />
				<button type="button" class="flex h-7 items-center rounded border border-gray-200 px-1.5 text-[10px] font-medium text-gray-400" data-sit-search-close>ESC</button>
			</div>
			<div class="max-h-[60vh] overflow-y-auto p-4" data-sit-search-results>
				<p class="py-8 text-center text-sm text-gray-400"><?php esc_html_e( 'Axtarış sözü yazın...', 'studyinturkey' ); ?></p>
			</div>
		</div>
	</div>
</div>
