<?php
/**
 * Why Choose Turkey? — 3 kart, Figma dizaynı, Customizer.
 */
defined( 'ABSPATH' ) || exit;

$cards = [];
for ( $i = 1; $i <= 3; $i++ ) {
	$t = get_theme_mod( "sit_wt_{$i}_title", '' );
	if ( ! $t ) continue;
	$cards[] = [
		'title' => $t,
		'desc'  => get_theme_mod( "sit_wt_{$i}_desc", '' ),
		'icon'  => get_theme_mod( "sit_wt_{$i}_icon", 'BookOpen' ),
		'color' => get_theme_mod( "sit_wt_{$i}_color", 'blue' ),
		'image' => get_theme_mod( "sit_wt_{$i}_image", '' ),
	];
}
if ( empty( $cards ) ) return;

$color_map = [
	'blue'    => ['bg-blue-50','text-blue-600'],
	'red'     => ['bg-red-50','text-red-600'],
	'emerald' => ['bg-emerald-50','text-emerald-600'],
	'amber'   => ['bg-amber-50','text-amber-600'],
	'purple'  => ['bg-purple-50','text-purple-600'],
	'slate'   => ['bg-slate-100','text-slate-600'],
];
?>
<section class="bg-white py-20 lg:py-24 dark:bg-slate-950">
	<div class="sit-container">
		<div class="mx-auto mb-16 max-w-3xl text-center">
			<h2 class="mb-4 text-3xl font-bold text-slate-800 md:text-4xl dark:text-white"><?php esc_html_e( 'Why Choose Turkey?', 'studyinturkey' ); ?></h2>
			<p class="text-lg text-slate-600 dark:text-slate-400"><?php esc_html_e( 'Turkey has become one of the most popular destinations for international students, offering high-quality education at affordable prices.', 'studyinturkey' ); ?></p>
		</div>
		<div class="grid gap-10 md:grid-cols-3">
			<?php foreach ( $cards as $c ) :
				$cm = $color_map[ $c['color'] ] ?? $color_map['blue'];
			?>
				<div class="group rounded-2xl border border-slate-100 bg-white p-8 transition hover:border-slate-200 hover:shadow-xl dark:border-slate-700 dark:bg-slate-900">
					<?php if ( $c['image'] ) : ?>
						<img src="<?php echo esc_url( $c['image'] ); ?>" alt="" class="mb-6 h-16 w-16 rounded-2xl object-contain transition-transform duration-300 group-hover:scale-110" />
					<?php else : ?>
						<div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl <?php echo esc_attr( $cm[0] . ' ' . $cm[1] ); ?> transition-transform duration-300 group-hover:scale-110">
							<?php echo sit_theme_icon_svg( $c['icon'], 'h-8 w-8' ); // phpcs:ignore ?>
						</div>
					<?php endif; ?>
					<h3 class="mb-3 text-xl font-bold text-slate-900 dark:text-white"><?php echo esc_html( $c['title'] ); ?></h3>
					<p class="leading-relaxed text-slate-600 dark:text-slate-400"><?php echo esc_html( $c['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
