<?php
/**
 * Proqramlar arxivi — cədvəl, filtrlər, REST ilə AJAX yeniləmə.
 */

defined( 'ABSPATH' ) || exit;

get_header();

global $wp_query;
$paged       = max( 1, (int) get_query_var( 'paged' ) );
$total       = (int) $wp_query->found_posts;
$total_pages = (int) $wp_query->max_num_pages;
?>
<main id="main-content" class="flex-1 py-10 lg:py-14" data-sit-prog-root>
	<div class="sit-container">
		<header class="mb-8 border-b border-slate-200 pb-6 dark:border-slate-800">
			<h1 class="text-3xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></h1>
			<p class="mt-2 max-w-2xl text-slate-600 dark:text-slate-400"><?php esc_html_e( 'Dərəcə, dil, şəhər və qiymət üzrə süzün; nəticələr REST API ilə yenilənir.', 'studyinturkey' ); ?></p>
			<p class="mt-3 text-sm text-slate-500 dark:text-slate-400" data-sit-prog-summary>
				<?php
				if ( $total > 0 ) {
					printf(
						/* translators: %d: program count */
						esc_html( _n( '%d proqram tapıldı.', '%d proqram tapıldı.', $total, 'studyinturkey' ) ),
						(int) $total
					);
				} else {
					esc_html_e( 'Nəticə yoxdur.', 'studyinturkey' );
				}
				?>
			</p>
		</header>

		<div class="flex flex-col gap-10 lg:flex-row lg:items-start">
			<?php get_template_part( 'template-parts/program/archive-filters' ); ?>

			<div class="min-w-0 flex-1">
				<div class="relative overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900" data-sit-prog-table-wrap>
					<div class="pointer-events-none absolute inset-0 z-10 hidden items-center justify-center bg-white/60 dark:bg-slate-900/70" data-sit-prog-loading aria-hidden="true">
						<span class="text-sm font-medium text-slate-600 dark:text-slate-300"><?php esc_html_e( 'Yüklənir…', 'studyinturkey' ); ?></span>
					</div>
					<table class="w-full min-w-[640px] border-collapse text-start text-sm">
						<thead>
							<tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-400">
								<th class="px-3 py-3"><?php esc_html_e( 'Proqram', 'studyinturkey' ); ?></th>
								<th class="hidden px-3 py-3 lg:table-cell"><?php esc_html_e( 'Universitet', 'studyinturkey' ); ?></th>
								<th class="hidden px-3 py-3 md:table-cell"><?php esc_html_e( 'Dərəcə', 'studyinturkey' ); ?></th>
								<th class="hidden px-3 py-3 xl:table-cell"><?php esc_html_e( 'Dil', 'studyinturkey' ); ?></th>
								<th class="hidden px-3 py-3 2xl:table-cell"><?php esc_html_e( 'Sahə', 'studyinturkey' ); ?></th>
								<th class="px-3 py-3"><?php esc_html_e( 'Ödəniş', 'studyinturkey' ); ?></th>
								<th class="hidden px-3 py-3 sm:table-cell"><?php esc_html_e( 'Müddət', 'studyinturkey' ); ?></th>
								<th class="px-3 py-3 text-center"><?php esc_html_e( 'Təqaüd', 'studyinturkey' ); ?></th>
								<th class="px-3 py-3 text-end"><?php esc_html_e( 'Əməl.', 'studyinturkey' ); ?></th>
							</tr>
						</thead>
						<tbody data-sit-prog-tbody>
							<?php if ( have_posts() ) : ?>
								<?php
								while ( have_posts() ) :
									the_post();
									get_template_part( 'template-parts/program/table-row' );
								endwhile;
								?>
							<?php else : ?>
								<tr>
									<td colspan="9" class="px-3 py-10 text-center text-slate-600"><?php esc_html_e( 'Filtrlərə uyğun proqram tapılmadı.', 'studyinturkey' ); ?></td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>

				<?php if ( $total_pages > 1 ) : ?>
					<nav class="mt-8 flex justify-center" data-sit-prog-pagination aria-label="<?php esc_attr_e( 'Səhifələmə', 'studyinturkey' ); ?>">
						<?php
						echo paginate_links(
							[
								'total'     => $total_pages,
								'current'   => $paged,
								'prev_text' => '← ' . __( 'Əvvəlki', 'studyinturkey' ),
								'next_text' => __( 'Növbəti', 'studyinturkey' ) . ' →',
								'type'      => 'list',
								'add_args'  => sit_theme_program_archive_filter_params(),
							]
						);
						?>
					</nav>
				<?php else : ?>
					<nav class="mt-8 hidden justify-center" data-sit-prog-pagination aria-label="<?php esc_attr_e( 'Səhifələmə', 'studyinturkey' ); ?>"></nav>
				<?php endif; ?>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
