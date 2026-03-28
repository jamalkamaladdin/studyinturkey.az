<?php
/**
 * Universitetlər arxivi — kartlar və filtr paneli.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="flex-1 py-10 lg:py-14">
	<div class="sit-container">
		<div class="flex flex-col gap-10 lg:flex-row lg:items-start">
			<?php get_template_part( 'template-parts/university/archive-filters' ); ?>
			<div class="min-w-0 flex-1">
				<header class="mb-8 border-b border-slate-200 pb-6">
					<h1 class="text-3xl font-bold text-slate-900"><?php the_archive_title(); ?></h1>
					<?php the_archive_description( '<div class="mt-2 max-w-2xl text-slate-600">', '</div>' ); ?>
					<?php
					global $wp_query;
					$total = (int) $wp_query->found_posts;
					if ( $total > 0 ) {
						printf(
							'<p class="mt-3 text-sm text-slate-500">%s</p>',
							esc_html(
								sprintf(
									/* translators: %d: result count */
									_n( '%d universitet tapıldı.', '%d universitet tapıldı.', $total, 'studyinturkey' ),
									$total
								)
							)
						);
					}
					?>
				</header>

				<?php if ( have_posts() ) : ?>
					<div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-2">
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
					<p class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-slate-600">
						<?php esc_html_e( 'Seçilmiş filtrlərə uyğun universitet tapılmadı.', 'studyinturkey' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
