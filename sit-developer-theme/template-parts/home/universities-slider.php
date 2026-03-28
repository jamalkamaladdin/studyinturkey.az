<?php
/**
 * Universitetlər horizontal slayder.
 */

defined( 'ABSPATH' ) || exit;

if ( ! post_type_exists( 'university' ) ) {
	return;
}

$q = new WP_Query(
	[
		'post_type'      => 'university',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
	]
);

if ( ! $q->have_posts() ) {
	return;
}

$archive = sit_theme_universities_archive_url();
?>
<section class="border-b border-slate-100 bg-white py-14 dark:border-slate-800 dark:bg-slate-950 lg:py-16" aria-labelledby="sit-uni-slider-title">
	<div class="sit-container">
		<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
			<div>
				<h2 id="sit-uni-slider-title" class="text-2xl font-bold text-slate-900 dark:text-white sm:text-3xl">
					<?php esc_html_e( 'Universitetlər', 'studyinturkey' ); ?>
				</h2>
				<p class="mt-2 max-w-xl text-slate-600 dark:text-slate-400">
					<?php esc_html_e( 'Əməkdaşlıq etdiyimiz təhsil müəssisələri ilə tanış olun.', 'studyinturkey' ); ?>
				</p>
			</div>
			<a class="shrink-0 text-sm font-semibold text-brand-700 hover:text-brand-600" href="<?php echo esc_url( $archive ); ?>">
				<?php esc_html_e( 'Hamısına bax', 'studyinturkey' ); ?> →
			</a>
		</div>

		<div class="sit-uni-slider mt-8 flex gap-4 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0">
			<?php
			while ( $q->have_posts() ) :
				$q->the_post();
				$post_id = get_the_ID();
				$title   = sit_theme_get_post_title( $post_id );
				$link    = sit_theme_localize_url( get_permalink( $post_id ) );
				$logo_id = (int) get_post_meta( $post_id, 'sit_logo_id', true );
				$logo    = $logo_id ? wp_get_attachment_image( $logo_id, 'medium', false, [ 'class' => 'max-h-14 w-auto object-contain' ] ) : '';
				?>
				<a href="<?php echo esc_url( $link ); ?>" class="flex min-w-[220px] max-w-[260px] flex-col rounded-2xl border border-slate-200 bg-slate-50/80 p-5 shadow-sm transition hover:border-brand-200 hover:bg-white hover:shadow-md dark:border-slate-700 dark:bg-slate-800/80 dark:hover:border-brand-600 dark:hover:bg-slate-800">
					<div class="flex h-16 items-center justify-center rounded-xl bg-white p-2 ring-1 ring-slate-100 dark:bg-slate-900 dark:ring-slate-700">
						<?php
						if ( $logo ) {
							echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attachment image.
						} elseif ( has_post_thumbnail( $post_id ) ) {
							the_post_thumbnail( 'medium', [ 'class' => 'max-h-14 w-auto object-contain' ] );
						} else {
							$initial = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 );
							echo '<span class="text-2xl font-bold text-brand-600">' . esc_html( $initial ) . '</span>';
						}
						?>
					</div>
					<span class="mt-4 line-clamp-2 text-center text-sm font-semibold text-slate-900 dark:text-white"><?php echo esc_html( $title ); ?></span>
				</a>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
