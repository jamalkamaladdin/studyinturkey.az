<?php
/**
 * Why Choose Us — Bento grid, Figma dizaynı.
 */
defined( 'ABSPATH' ) || exit;

$cards = [];
for ( $i = 1; $i <= 5; $i++ ) {
	$t = get_theme_mod( "sit_wu_{$i}_title", '' );
	if ( ! $t ) continue;
	$cards[] = [
		'title' => $t,
		'desc'  => get_theme_mod( "sit_wu_{$i}_desc", '' ),
		'icon'  => get_theme_mod( "sit_wu_{$i}_icon", 'Award' ),
		'style' => get_theme_mod( "sit_wu_{$i}_style", 'normal' ),
	];
}
if ( empty( $cards ) ) return;
?>
<section class="border-t border-slate-200 bg-gradient-to-b from-white to-slate-100 py-20 lg:py-24 dark:border-slate-800 dark:from-slate-950 dark:to-slate-900">
	<div class="sit-container">
		<div class="mb-16 text-center">
			<h2 class="mb-6 text-4xl font-extrabold text-slate-800 md:text-5xl dark:text-white"><?php sit_esc_html_e( 'why_us.heading', 'Niyə Biz?' ); ?></h2>
			<p class="mx-auto max-w-2xl text-lg text-slate-600 dark:text-slate-400"><?php sit_esc_html_e( 'why_us.desc', 'Təhsil yolunuzu rahat və əlçatan etmək üçün nəzərdə tutulmuş misilsiz dəstək və eksklüziv imkanlar.' ); ?></p>
		</div>

		<div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
			<?php foreach ( $cards as $idx => $c ) :
				$is_large  = $c['style'] === 'large';
				$is_dark   = $c['style'] === 'dark';
				$is_accent = $c['style'] === 'accent';

				if ( $is_large ) :
			?>
				<div class="group relative col-span-1 overflow-hidden rounded-[2rem] bg-brand-700 p-8 text-white md:p-10 lg:col-span-2">
					<div class="relative z-10 flex h-full flex-col justify-center">
						<div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-md">
							<?php echo sit_theme_icon_svg( $c['icon'], 'h-8 w-8 text-yellow-400' ); // phpcs:ignore ?>
						</div>
						<h3 class="mb-4 text-3xl font-bold"><?php echo esc_html( $c['title'] ); ?></h3>
						<p class="max-w-lg text-lg leading-relaxed text-blue-100"><?php echo esc_html( $c['desc'] ); ?></p>
					</div>
					<div class="absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-blue-500 opacity-30 blur-3xl transition-opacity duration-700 group-hover:opacity-50"></div>
				</div>
			<?php elseif ( $is_dark ) : ?>
				<div class="group relative overflow-hidden rounded-[2rem] bg-[#1E2532] p-8 text-white shadow-lg transition-all hover:shadow-2xl">
					<div class="relative z-10">
						<div class="mb-6 text-emerald-400"><?php echo sit_theme_icon_svg( $c['icon'], 'h-10 w-10' ); // phpcs:ignore ?></div>
						<h3 class="mb-3 text-2xl font-bold"><?php echo esc_html( $c['title'] ); ?></h3>
						<p class="leading-relaxed text-slate-400"><?php echo esc_html( $c['desc'] ); ?></p>
					</div>
				</div>
			<?php elseif ( $is_accent ) : ?>
				<div class="group relative overflow-hidden rounded-[2rem] border border-red-100 bg-red-50 p-8 shadow-sm transition-all hover:shadow-xl dark:border-red-900/50 dark:bg-red-950/30">
					<div class="relative z-10">
						<div class="mb-6 text-red-600 dark:text-red-400"><?php echo sit_theme_icon_svg( $c['icon'], 'h-10 w-10' ); // phpcs:ignore ?></div>
						<h3 class="mb-3 text-2xl font-bold text-red-900 dark:text-red-300"><?php echo esc_html( $c['title'] ); ?></h3>
						<p class="leading-relaxed text-red-800/80 dark:text-red-400/80"><?php echo esc_html( $c['desc'] ); ?></p>
					</div>
				</div>
			<?php else : ?>
				<div class="flex flex-col justify-center rounded-[2rem] border border-slate-200 bg-white p-8 shadow-sm transition-all hover:shadow-xl dark:border-slate-700 dark:bg-slate-900">
					<div class="mb-6 text-blue-600 dark:text-blue-400"><?php echo sit_theme_icon_svg( $c['icon'], 'h-10 w-10' ); // phpcs:ignore ?></div>
					<h3 class="mb-3 text-2xl font-bold text-slate-900 dark:text-white"><?php echo esc_html( $c['title'] ); ?></h3>
					<p class="leading-relaxed text-slate-500 dark:text-slate-400"><?php echo esc_html( $c['desc'] ); ?></p>
				</div>
			<?php endif; endforeach; ?>
		</div>
	</div>
</section>
