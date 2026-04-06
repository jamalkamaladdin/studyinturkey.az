<?php
/**
 * Bu universitetə bağlı proqramlar (5 limit + "hamısını gör" linki).
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 || ! post_type_exists( 'program' ) ) {
	return;
}

$limit = 5;
$q = new WP_Query( [
	'post_type'      => 'program',
	'post_status'    => 'publish',
	'posts_per_page' => $limit + 1,
	'meta_query'     => [
		[
			'key'   => 'sit_university_id',
			'value' => $university_id,
			'type'  => 'NUMERIC',
		],
	],
	'orderby'  => 'title',
	'order'    => 'ASC',
	'no_found_rows' => true,
] );

if ( ! $q->have_posts() ) {
	return;
}

$has_more   = $q->post_count > $limit;
$show_count = min( $q->post_count, $limit );

$programs_url = add_query_arg( 'sit_university', $university_id, sit_theme_programs_archive_url() );
?>
<section class="scroll-mt-24" id="programs" aria-labelledby="sit-univ-programs-title">
	<div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
		<h2 id="sit-univ-programs-title" class="text-2xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></h2>
		<a href="<?php echo esc_url( $programs_url ); ?>" class="text-sm font-semibold text-brand-700 hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300"><?php esc_html_e( 'Bütün proqramları gör', 'studyinturkey' ); ?> &rarr;</a>
	</div>

	<div class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
		<table class="w-full min-w-[540px] border-collapse text-left text-sm">
			<thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-400">
				<tr>
					<th class="px-4 py-3"><?php esc_html_e( 'Proqram', 'studyinturkey' ); ?></th>
					<th class="px-4 py-3"><?php esc_html_e( 'Dərəcə', 'studyinturkey' ); ?></th>
					<th class="px-4 py-3"><?php esc_html_e( 'Ödəniş', 'studyinturkey' ); ?></th>
					<th class="px-4 py-3"><?php esc_html_e( 'Dil', 'studyinturkey' ); ?></th>
					<th class="px-4 py-3 text-end"></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$i = 0;
				while ( $q->have_posts() && $i < $limit ) :
					$q->the_post();
					$i++;
					$pid      = get_the_ID();
					$p_title  = sit_theme_get_post_title( $pid );
					$link     = sit_theme_localize_url( get_permalink( $pid ) );
					$fee      = get_post_meta( $pid, 'sit_tuition_fee', true );
					$dur      = (string) get_post_meta( $pid, 'sit_duration', true );
					$deg      = get_the_terms( $pid, 'degree_type' );
					$langs    = get_the_terms( $pid, 'program_language' );

					$deg_s  = '';
					if ( is_array( $deg ) && ! is_wp_error( $deg ) ) {
						$names = [];
						foreach ( $deg as $t ) { $names[] = sit_theme_get_term_name( (int) $t->term_id, 'degree_type' ); }
						$deg_s = implode( ', ', array_filter( $names ) );
					}
					$lang_s = '';
					if ( is_array( $langs ) && ! is_wp_error( $langs ) ) {
						$names = [];
						foreach ( $langs as $t ) { $names[] = sit_theme_get_term_name( (int) $t->term_id, 'program_language' ); }
						$lang_s = implode( ', ', array_filter( $names ) );
					}

					$fee_num = ( is_numeric( $fee ) && (float) $fee > 0 ) ? (float) $fee : null;
					?>
					<tr class="border-b border-slate-200 bg-white transition-colors hover:bg-slate-50/80 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800/60">
						<td class="px-4 py-3.5 align-top">
							<a href="<?php echo esc_url( $link ); ?>" class="text-sm font-semibold text-slate-900 hover:text-brand-600 dark:text-white dark:hover:text-brand-400"><?php echo esc_html( $p_title ); ?></a>
							<?php if ( '' !== $dur ) : ?>
								<span class="mt-0.5 block text-xs text-slate-500"><?php echo esc_html( $dur ); ?></span>
							<?php endif; ?>
						</td>
						<td class="px-4 py-3.5 align-top text-sm text-slate-700 dark:text-slate-300">
							<?php echo '' !== $deg_s ? esc_html( $deg_s ) : '<span class="text-slate-400">—</span>'; ?>
						</td>
						<td class="px-4 py-3.5 align-top text-sm font-semibold whitespace-nowrap">
							<?php if ( null !== $fee_num ) : ?>
								<span class="text-slate-900 dark:text-white"><?php echo esc_html( number_format_i18n( $fee_num, 0 ) ); ?>$</span>
								<span class="text-xs font-normal text-slate-500"><?php esc_html_e( '/ il', 'studyinturkey' ); ?></span>
							<?php else : ?>
								<span class="text-slate-400">—</span>
							<?php endif; ?>
						</td>
						<td class="px-4 py-3.5 align-top text-sm text-slate-700 dark:text-slate-300">
							<?php echo '' !== $lang_s ? esc_html( $lang_s ) : '<span class="text-slate-400">—</span>'; ?>
						</td>
						<td class="px-4 py-3.5 align-top text-end">
							<a href="<?php echo esc_url( $link ); ?>" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm whitespace-nowrap hover:bg-brand-700 dark:hover:bg-brand-500"><?php esc_html_e( 'Müraciət et', 'studyinturkey' ); ?></a>
						</td>
					</tr>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</tbody>
		</table>
	</div>

	<?php if ( $has_more ) : ?>
		<div class="mt-4 text-center">
			<a href="<?php echo esc_url( $programs_url ); ?>" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
				<?php esc_html_e( 'Bütün proqramları gör', 'studyinturkey' ); ?> &rarr;
			</a>
		</div>
	<?php endif; ?>
</section>
