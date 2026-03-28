<?php
/**
 * Proqramlar arxivi — kart şəbəkəsi, filtrlər, REST ilə AJAX yeniləmə (StudyLeo üslubu).
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
			<h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl"><?php esc_html_e( 'Universitet proqramları', 'studyinturkey' ); ?></h1>
			<p class="mt-2 max-w-2xl text-base text-slate-600 dark:text-slate-400"><?php esc_html_e( 'Bütün proqramları kəşf edin, ödəniş və dil üzrə müqayisə edin və uyğun olanı seçin.', 'studyinturkey' ); ?></p>
			<div class="mt-4 flex flex-wrap items-baseline gap-2">
				<span class="text-3xl font-black tabular-nums text-brand-600 dark:text-brand-400" data-sit-prog-count><?php echo esc_html( (string) max( 0, $total ) ); ?></span>
				<span class="text-sm font-medium text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Filtrlər', 'studyinturkey' ); ?></span>
			</div>
			<p class="mt-2 text-sm text-slate-500 dark:text-slate-400" data-sit-prog-summary>
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

		<div class="flex flex-col gap-8 lg:flex-row lg:items-start">
			<?php get_template_part( 'template-parts/program/archive-filters' ); ?>

			<div class="min-w-0 flex-1">
				<div class="relative" data-sit-prog-table-wrap>
					<div class="pointer-events-none absolute inset-0 z-10 hidden items-center justify-center rounded-2xl bg-white/70 dark:bg-slate-900/70" data-sit-prog-loading aria-hidden="true">
						<span class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow dark:bg-slate-800 dark:text-slate-200"><?php esc_html_e( 'Yüklənir…', 'studyinturkey' ); ?></span>
					</div>
					<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
						<table class="w-full min-w-[720px] border-collapse text-left text-sm table-fixed">
							<colgroup>
								<col class="w-[22%]" />
								<col class="w-[24%]" />
								<col class="w-[14%]" />
								<col class="w-[14%]" />
								<col class="w-[12%]" />
								<col class="w-[14%]" />
							</colgroup>
							<thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-400">
								<tr>
									<th class="px-4 py-3"><?php esc_html_e( 'Universitet', 'studyinturkey' ); ?></th>
									<th class="px-4 py-3"><?php esc_html_e( 'Proqram', 'studyinturkey' ); ?></th>
									<th class="px-4 py-3"><?php esc_html_e( 'Dərəcə', 'studyinturkey' ); ?></th>
									<th class="px-4 py-3"><?php esc_html_e( 'Ödəniş', 'studyinturkey' ); ?></th>
									<th class="px-4 py-3"><?php esc_html_e( 'Dillər', 'studyinturkey' ); ?></th>
									<th class="px-4 py-3 text-end"><?php esc_html_e( 'Müraciət', 'studyinturkey' ); ?></th>
								</tr>
							</thead>
							<tbody data-sit-prog-list>
								<?php if ( have_posts() ) : ?>
									<?php
									while ( have_posts() ) :
										the_post();
										get_template_part( 'template-parts/program/archive-program-row' );
									endwhile;
									?>
								<?php else : ?>
									<tr>
										<td colspan="6" class="px-6 py-12 text-center text-slate-600 dark:text-slate-400">
											<?php esc_html_e( 'Filtrlərə uyğun proqram tapılmadı.', 'studyinturkey' ); ?>
										</td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
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
