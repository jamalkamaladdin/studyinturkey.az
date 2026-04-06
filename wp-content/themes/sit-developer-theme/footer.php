<?php
/**
 * Footer — Figma v2: CTA strip, #0a1a1b, lang codes.
 */
defined( 'ABSPATH' ) || exit;
$year    = (int) gmdate( 'Y' );
$socials = sit_theme_get_social_links();
$phone   = get_theme_mod( 'sit_footer_phone', '' );
$contact = sit_theme_localize_url( home_url( '/elaqe/' ) );
$lang_codes = [ 'AZ', 'EN', 'RU', 'FA', 'AR', 'KK' ];
?>
<footer class="mt-auto bg-[#0d4f52] text-white">
	<!-- CTA Strip -->
	<div class="border-b border-white/10 bg-[#0a3d3f]">
		<div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 py-12 sm:px-6 md:flex-row">
			<div>
				<h3 class="mb-1 text-2xl font-extrabold" style="line-height:1.3"><?php esc_html_e( 'Türkiyədə təhsilə başlamağa hazırsınız?', 'studyinturkey' ); ?></h3>
				<p class="text-[15px] text-white/70"><?php esc_html_e( 'Təhsil mütəxəssislərimizdən pulsuz məsləhət alın.', 'studyinturkey' ); ?></p>
			</div>
			<a href="<?php echo esc_url( $contact ); ?>" class="flex items-center gap-2 whitespace-nowrap rounded-xl bg-[#ff3131] px-8 py-3.5 text-[15px] font-bold text-white shadow-lg shadow-red-900/30 transition-all hover:bg-[#e02020]">
				<?php esc_html_e( 'Başlayın', 'studyinturkey' ); ?>
				<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
			</a>
		</div>
	</div>

	<div class="sit-container px-4 pt-14 pb-8 sm:px-6">
		<!-- Top: Logo + Nav -->
		<div class="mb-12 flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-center">
			<div class="flex items-center gap-3">
				<?php if ( has_custom_logo() ) : ?>
					<div class="[&_img]:h-9 [&_img]:w-auto [&_img]:brightness-0 [&_img]:invert [&_img]:opacity-90"><?php the_custom_logo(); ?></div>
				<?php else : ?>
					<span class="text-xl font-bold"><?php bloginfo( 'name' ); ?></span>
				<?php endif; ?>
			</div>
			<nav>
				<?php wp_nav_menu(['theme_location'=>'footer','container'=>false,'menu_class'=>'flex flex-wrap gap-x-8 gap-y-3','fallback_cb'=>'__return_false','depth'=>1]); ?>
			</nav>
		</div>

		<!-- Middle: desc + phone -->
		<div class="mb-12 flex flex-col items-start justify-between gap-8 md:flex-row md:items-end">
			<p class="max-w-sm text-[14px] leading-relaxed text-white/60">
				<?php echo esc_html( get_bloginfo( 'description', 'display' ) ?: __( 'Türkiyədə təhsil üçün universitet və proqram seçimi.', 'studyinturkey' ) ); ?>
			</p>
			<div class="flex flex-col items-start gap-3 md:items-end">
				<?php if ( $phone ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>" class="text-2xl font-bold tracking-tight transition hover:text-brand-500 lg:text-3xl"><?php echo esc_html( $phone ); ?></a>
				<?php endif; ?>
				<?php if ( $socials ) : ?>
					<div class="flex gap-2">
						<?php foreach ( $socials as $p => $url ) : ?>
							<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-white/10 bg-white/5 p-2.5 transition hover:bg-white/10" aria-label="<?php echo esc_attr( ucfirst( $p ) ); ?>">
								<?php echo sit_theme_social_icon_svg( $p ); // phpcs:ignore ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Languages -->
		<div class="mb-8 border-b border-white/20 pb-8">
			<div class="flex flex-wrap justify-center gap-x-1 gap-y-2 text-[12px] font-semibold tracking-[0.15em] text-white/60 md:justify-end">
				<?php foreach ( $lang_codes as $idx => $code ) : ?>
					<span class="px-0.5 transition hover:text-white"><?php echo esc_html( $code ); ?></span>
					<?php if ( $idx < count( $lang_codes ) - 1 ) : ?><span class="px-0.5 text-white/30">/</span><?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- Bottom -->
		<div class="flex flex-col items-center justify-between gap-4 text-[13px] text-white/60 md:flex-row">
			<p>&copy; <?php echo esc_html( $year . ' ' . get_bloginfo( 'name', 'display' ) ); ?>. <?php esc_html_e( 'Bütün hüquqlar qorunur.', 'studyinturkey' ); ?></p>
			<div class="flex gap-6">
				<a href="#" class="transition hover:text-white"><?php esc_html_e( 'Gizlilik Siyasəti', 'studyinturkey' ); ?></a>
				<a href="#" class="transition hover:text-white"><?php esc_html_e( 'İstifadə Şərtləri', 'studyinturkey' ); ?></a>
			</div>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
