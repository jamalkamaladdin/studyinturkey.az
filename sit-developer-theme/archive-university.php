<?php
/**
 * Universitetlər arxivi — StudyLeo tipli başlıq + kart şəbəkəsi.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="flex-1 py-10 lg:py-14">
	<div class="sit-container">
		<div class="flex flex-col gap-10 lg:flex-row lg:items-start">
			<?php get_template_part( 'template-parts/university/archive-filters' ); ?>
			<div class="min-w-0 flex-1">
				<header class="mb-8 border-b border-slate-200 pb-6 dark:border-slate-800">
					<h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl"><?php esc_html_e( 'Universitetlər', 'studyinturkey' ); ?></h1>
					<p class="mt-2 max-w-2xl text-base text-slate-600 dark:text-slate-400"><?php esc_html_e( 'Özəl və dövlət universitetlərini şəhər və növə görə müqayisə edin.', 'studyinturkey' ); ?></p>
					<?php
					global $wp_query;
					$total = (int) $wp_query->found_posts;
					if ( $total > 0 ) {
						?>
						<div class="mt-4 flex flex-wrap items-baseline gap-2">
							<span class="text-3xl font-black tabular-nums text-brand-600 dark:text-brand-400"><?php echo esc_html( (string) $total ); ?></span>
							<span class="text-sm font-medium text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Filtrlər', 'studyinturkey' ); ?></span>
						</div>
						<p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
							<?php
							printf(
								/* translators: %d: result count */
								esc_html( _n( '%d universitet tapıldı.', '%d universitet tapıldı.', $total, 'studyinturkey' ) ),
								$total
							);
							?>
						</p>
						<?php
					} else {
						the_archive_description( '<div class="mt-2 max-w-2xl text-slate-600 dark:text-slate-400">', '</div>' );
					}
					?>
				</header>

				<?php if ( have_posts() ) : ?>
					<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-2">
						<?php
						while ( have_posts() ) :
							the_post();
							get_template_part( 'template-parts/university/card' );
						endwhile;
						?>
					</div>
					<div class="mt-10">
						<?php
						the_posts_pagination(
							[
								'mid_size'  => 2,
								'add_args'  => sit_theme_university_archive_filter_params(),
								'prev_text' => '← ' . __( 'Əvvəlki', 'studyinturkey' ),
								'next_text' => __( 'Növbəti', 'studyinturkey' ) . ' →',
							]
						);
						?>
					</div>
				<?php else : ?>
					<p class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center text-slate-600 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400">
						<?php esc_html_e( 'Seçilmiş filtrlərə uyğun universitet tapılmadı.', 'studyinturkey' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
