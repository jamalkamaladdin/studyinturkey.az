<?php
/**
 * Hero — Figma v2: dark bg, gradient blobs, badge, search.
 */
defined( 'ABSPATH' ) || exit;

$bg_type  = get_theme_mod( 'sit_hero_bg_type', 'gradient' );
$bg_image = get_theme_mod( 'sit_hero_bg_image', '' );
$bg_video = get_theme_mod( 'sit_hero_bg_video', '' );
$overlay  = (int) get_theme_mod( 'sit_hero_overlay_opacity', 60 );
$h1       = get_theme_mod( 'sit_hero_heading', __( 'Potensialınızı Açın', 'studyinturkey' ) );
$h1_2     = get_theme_mod( 'sit_hero_heading2', __( 'Türkiyədə Oxuyun', 'studyinturkey' ) );
$desc     = get_theme_mod( 'sit_hero_description', __( 'Ambisiyalı tələbələrə Türkiyənin ən yaxşı universitetlərindən qəbul almağa kömək edirik. Dünya səviyyəli təhsil və sonsuz imkanlar.', 'studyinturkey' ) );
$s_ph     = get_theme_mod( 'sit_hero_search_placeholder', __( 'Nə oxumaq istəyirsiniz?', 'studyinturkey' ) );
$prog_url = sit_theme_programs_archive_url();

$stats = [];
for ( $i = 1; $i <= 3; $i++ ) {
	$n = get_theme_mod( "sit_hero_stat{$i}_num", '' );
	$t = get_theme_mod( "sit_hero_stat{$i}_text", '' );
	if ( $n ) $stats[] = [ 'num' => $n, 'text' => $t ];
}
?>
<section class="relative flex min-h-[92vh] items-center overflow-hidden bg-[#11676a]">
	<!-- Background -->
	<?php if ( 'video' === $bg_type && $bg_video ) : ?>
		<video class="absolute inset-0 h-full w-full object-cover opacity-20" autoplay muted loop playsinline preload="auto">
			<source src="<?php echo esc_url( $bg_video ); ?>" type="video/mp4">
		</video>
	<?php elseif ( 'image' === $bg_type && $bg_image ) : ?>
		<img src="<?php echo esc_url( $bg_image ); ?>" alt="" class="absolute inset-0 h-full w-full object-cover opacity-20" loading="eager" />
	<?php endif; ?>
	<div class="absolute inset-0 bg-gradient-to-b from-[#11676a]/70 via-transparent to-[#11676a]"></div>
	<div class="absolute top-0 right-0 h-[600px] w-[600px] -translate-y-1/3 rounded-full bg-brand-600/15 blur-[120px]"></div>
	<div class="absolute bottom-0 left-0 h-[400px] w-[400px] translate-y-1/3 rounded-full bg-[#ff3131]/[0.08] blur-[100px]"></div>

	<div class="relative z-10 sit-container py-32">
		<div class="mx-auto max-w-3xl text-center">
			<!-- Badge -->
			<div class="mb-8 inline-flex items-center gap-2 rounded-full border border-white/[0.08] bg-white/[0.06] px-4 py-1.5 text-[13px] font-medium text-white/70 backdrop-blur-md">
				<span class="h-1.5 w-1.5 animate-pulse rounded-full bg-brand-600"></span>
				<?php esc_html_e( '2026 Payız semestri üçün qəbul açıqdır', 'studyinturkey' ); ?>
			</div>

			<h1 class="mb-6 text-[40px] font-extrabold tracking-[-0.02em] text-white md:text-[56px] lg:text-[64px]" style="line-height:1.1">
				<?php echo esc_html( $h1 ); ?>
				<span class="bg-gradient-to-r from-[#ff3131] to-[#ff6b6b] bg-clip-text text-transparent"> <?php echo esc_html( $h1_2 ); ?></span>
			</h1>
			<p class="mx-auto mb-10 max-w-2xl text-[17px] font-normal leading-relaxed text-gray-300/90 md:text-[19px]">
				<?php echo esc_html( $desc ); ?>
			</p>

			<!-- Search -->
			<form action="<?php echo esc_url( $prog_url ); ?>" method="get" class="mx-auto flex max-w-4xl flex-col gap-1.5 rounded-2xl bg-white p-1.5 shadow-2xl shadow-black/20 md:flex-row">
				<div class="flex flex-1 items-center rounded-xl border border-transparent bg-gray-50 px-4 py-3.5 transition focus-within:border-brand-600/30 focus-within:bg-white">
					<svg class="mr-3 h-[18px] w-[18px] shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
					<input type="text" name="s" placeholder="<?php echo esc_attr( $s_ph ); ?>" class="w-full border-none bg-transparent text-[14px] text-gray-700 outline-none placeholder:text-gray-400" />
				</div>
				<div class="flex flex-1 items-center rounded-xl border border-transparent bg-gray-50 px-4 py-3.5 transition focus-within:border-brand-600/30 focus-within:bg-white">
					<svg class="mr-3 h-[18px] w-[18px] shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
					<select name="sit_degree" class="w-full cursor-pointer appearance-none border-none bg-transparent text-[14px] text-gray-700 outline-none">
						<option value=""><?php esc_html_e( 'Təhsil dərəcəsi', 'studyinturkey' ); ?></option>
						<option value="bachelor"><?php esc_html_e( 'Bakalavr', 'studyinturkey' ); ?></option>
						<option value="master"><?php esc_html_e( 'Magistr', 'studyinturkey' ); ?></option>
						<option value="phd"><?php esc_html_e( 'Doktorantura', 'studyinturkey' ); ?></option>
					</select>
				</div>
				<button type="submit" class="flex items-center justify-center whitespace-nowrap rounded-xl bg-[#ff3131] px-8 py-3.5 text-[14px] font-semibold text-white shadow-sm transition-all hover:bg-[#e02020]">
					<?php esc_html_e( 'Axtar', 'studyinturkey' ); ?>
				</button>
			</form>

			<!-- Stats -->
			<?php if ( $stats ) : ?>
				<div class="mt-10 flex flex-wrap items-center justify-center gap-3 md:gap-6">
					<?php foreach ( $stats as $st ) : ?>
						<span class="flex items-center gap-2 rounded-full border border-white/[0.08] bg-white/[0.06] px-4 py-2 text-[13px] font-medium text-white/70 backdrop-blur-sm">
							<span class="text-[#ff3131]"><?php echo esc_html( $st['num'] ); ?></span>
							<?php echo esc_html( $st['text'] ); ?>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-[#f3f6f6] to-transparent"></div>
</section>
