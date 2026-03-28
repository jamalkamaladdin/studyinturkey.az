<?php
/**
 * Təqaüdlər.
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = isset( $university_id ) ? absint( $university_id ) : 0;
if ( $university_id < 1 || ! post_type_exists( 'scholarship' ) ) {
	return;
}

$q = sit_theme_query_posts_by_university( 'scholarship', $university_id );
if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="scroll-mt-24" id="scholarships" aria-labelledby="sit-scholarship-title">
	<h2 id="sit-scholarship-title" class="text-2xl font-bold text-slate-900"><?php esc_html_e( 'Təqaüdlər', 'studyinturkey' ); ?></h2>
	<ul class="mt-6 space-y-4">
		<?php
		while ( $q->have_posts() ) :
			$q->the_post();
			$pid    = get_the_ID();
			$name   = sit_theme_get_post_title( $pid );
			$pct    = get_post_meta( $pid, 'sit_percentage', true );
			$elig   = (string) get_post_meta( $pid, 'sit_eligibility', true );
			?>
			<li class="rounded-2xl border border-slate-200 bg-gradient-to-br from-brand-50/80 to-white p-5">
				<div class="flex flex-wrap items-baseline justify-between gap-2">
					<h3 class="font-semibold text-slate-900"><?php echo esc_html( $name ); ?></h3>
					<?php if ( is_numeric( $pct ) && (float) $pct > 0 ) : ?>
						<span class="text-lg font-bold text-brand-800"><?php echo esc_html( number_format_i18n( (float) $pct, 0 ) ); ?>%</span>
					<?php endif; ?>
				</div>
				<?php if ( '' !== $elig ) : ?>
					<p class="mt-2 text-sm text-slate-600 leading-relaxed"><?php echo esc_html( wp_strip_all_tags( $elig ) ); ?></p>
				<?php endif; ?>
			</li>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ul>
</section>
