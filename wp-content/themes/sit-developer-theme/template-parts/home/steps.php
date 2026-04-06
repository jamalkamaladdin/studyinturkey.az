<?php
/**
 * Necə işləyir? — Figma stilinə uyğunlaşdırılmış kartlar.
 */
defined( 'ABSPATH' ) || exit;

$steps = [];
for ( $i = 1; $i <= 6; $i++ ) {
	$t = get_theme_mod( "sit_step_{$i}_title", '' );
	if ( '' !== $t ) $steps[] = [ 'title' => $t, 'desc' => get_theme_mod( "sit_step_{$i}_desc", '' ), 'num' => $i ];
}
if ( empty( $steps ) ) return;

$colors = [
	['bg-blue-50 dark:bg-blue-950/40','text-blue-600 dark:text-blue-400','border-blue-100 dark:border-blue-900/50'],
	['bg-red-50 dark:bg-red-950/40','text-red-600 dark:text-red-400','border-red-100 dark:border-red-900/50'],
	['bg-emerald-50 dark:bg-emerald-950/40','text-emerald-600 dark:text-emerald-400','border-emerald-100 dark:border-emerald-900/50'],
	['bg-amber-50 dark:bg-amber-950/40','text-amber-600 dark:text-amber-400','border-amber-100 dark:border-amber-900/50'],
	['bg-purple-50 dark:bg-purple-950/40','text-purple-600 dark:text-purple-400','border-purple-100 dark:border-purple-900/50'],
	['bg-slate-100 dark:bg-slate-800/40','text-slate-600 dark:text-slate-400','border-slate-200 dark:border-slate-700'],
];
$cols = min( count( $steps ), 4 );
?>
<section class="bg-slate-50 py-20 lg:py-24 dark:bg-slate-900">
	<div class="sit-container">
		<div class="mx-auto mb-16 max-w-3xl text-center">
			<h2 class="mb-4 text-3xl font-bold text-slate-800 md:text-4xl dark:text-white"><?php esc_html_e( 'Necə işləyir?', 'studyinturkey' ); ?></h2>
			<p class="text-lg text-slate-600 dark:text-slate-400"><?php esc_html_e( 'Qısa addımlarla Türkiyədə təhsilə yolunuzu planlayın.', 'studyinturkey' ); ?></p>
		</div>
		<div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-<?php echo esc_attr( $cols ); ?>">
			<?php foreach ( $steps as $idx => $step ) :
				$c = $colors[ $idx % count($colors) ];
			?>
				<div class="group rounded-2xl border <?php echo esc_attr( $c[2] ); ?> bg-white p-8 transition hover:shadow-xl dark:bg-slate-800/80">
					<div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl <?php echo esc_attr( $c[0] ); ?> transition-transform duration-300 group-hover:scale-110">
						<span class="text-2xl font-extrabold <?php echo esc_attr( $c[1] ); ?>"><?php echo esc_html( (string) $step['num'] ); ?></span>
					</div>
					<h3 class="mb-3 text-xl font-bold text-slate-900 dark:text-white"><?php echo esc_html( $step['title'] ); ?></h3>
					<?php if ( $step['desc'] ) : ?>
						<p class="leading-relaxed text-slate-600 dark:text-slate-400"><?php echo esc_html( $step['desc'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
