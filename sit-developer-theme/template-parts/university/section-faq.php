<?php
/**
 * FAQ — accordion format.
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 || ! post_type_exists( 'faq' ) ) {
	return;
}

$q = sit_theme_query_posts_by_university( 'faq', $university_id );
if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="scroll-mt-24" id="faq" aria-labelledby="sit-faq-title">
	<h2 id="sit-faq-title" class="text-2xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Tez-tez verilən suallar', 'studyinturkey' ); ?></h2>
	<div class="mt-6 space-y-3">
		<?php
		$first = true;
		while ( $q->have_posts() ) :
			$q->the_post();
			$pid   = get_the_ID();
			$qtext = sit_theme_get_post_title( $pid );
			$ans   = sit_theme_get_post_content_filtered( $pid );
			?>
			<details class="group rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900" <?php echo $first ? 'open' : ''; ?>>
				<summary class="flex cursor-pointer items-center justify-between px-5 py-4 text-base font-semibold text-slate-900 dark:text-white">
					<?php echo esc_html( $qtext ); ?>
					<svg class="h-5 w-5 shrink-0 text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
				</summary>
				<div class="sit-entry-content border-t border-slate-200 px-5 py-4 text-sm text-slate-600 leading-relaxed dark:border-slate-700 dark:text-slate-400">
					<?php echo $ans; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</details>
			<?php
			$first = false;
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
