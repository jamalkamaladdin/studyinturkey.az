<?php
/**
 * FAQ.
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
	<h2 id="sit-faq-title" class="text-2xl font-bold text-slate-900"><?php esc_html_e( 'Tez-tez verilən suallar', 'studyinturkey' ); ?></h2>
	<dl class="mt-6 space-y-3">
		<?php
		while ( $q->have_posts() ) :
			$q->the_post();
			$pid   = get_the_ID();
			$qtext = sit_theme_get_post_title( $pid );
			$ans   = sit_theme_get_post_content_filtered( $pid );
			?>
			<div class="rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
				<dt class="font-semibold text-slate-900"><?php echo esc_html( $qtext ); ?></dt>
				<dd class="sit-entry-content mt-2 text-sm text-slate-600">
					<?php echo $ans; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</dd>
			</div>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</dl>
</section>
