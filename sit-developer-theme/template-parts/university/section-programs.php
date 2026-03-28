<?php
/**
 * Bu universitetə bağlı proqramlar.
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 || ! post_type_exists( 'program' ) ) {
	return;
}

$q = sit_theme_query_posts_by_university( 'program', $university_id );
if ( ! $q->have_posts() ) {
	return;
}

$programs_url = sit_theme_programs_archive_url();
?>
<section class="scroll-mt-24" id="programs" aria-labelledby="sit-univ-programs-title">
	<div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
		<h2 id="sit-univ-programs-title" class="text-2xl font-bold text-slate-900"><?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></h2>
		<a href="<?php echo esc_url( $programs_url ); ?>" class="text-sm font-semibold text-brand-700 hover:text-brand-600"><?php esc_html_e( 'Bütün proqramlar', 'studyinturkey' ); ?> →</a>
	</div>
	<ul class="mt-6 divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white shadow-sm">
		<?php
		while ( $q->have_posts() ) :
			$q->the_post();
			$pid         = get_the_ID();
			$p_title     = sit_theme_get_post_title( $pid );
			$link        = sit_theme_localize_url( get_permalink( $pid ) );
			$fee         = get_post_meta( $pid, 'sit_tuition_fee', true );
			$duration    = (string) get_post_meta( $pid, 'sit_duration', true );
			$scholarship = (bool) get_post_meta( $pid, 'sit_scholarship_available', true );
			?>
			<li>
				<a href="<?php echo esc_url( $link ); ?>" class="flex flex-col gap-2 p-4 transition hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
					<div class="min-w-0">
						<span class="font-semibold text-slate-900"><?php echo esc_html( $p_title ); ?></span>
						<?php if ( '' !== $duration ) : ?>
							<span class="mt-0.5 block text-sm text-slate-500"><?php echo esc_html( $duration ); ?></span>
						<?php endif; ?>
					</div>
					<div class="flex flex-wrap items-center gap-2 sm:justify-end">
						<?php if ( $scholarship ) : ?>
							<span class="rounded-md bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-800"><?php esc_html_e( 'Təqaüd', 'studyinturkey' ); ?></span>
						<?php endif; ?>
						<?php if ( is_numeric( $fee ) && (float) $fee > 0 ) : ?>
							<span class="text-sm font-medium text-slate-700"><?php echo esc_html( number_format_i18n( (float) $fee, 0 ) ); ?></span>
						<?php endif; ?>
					</div>
				</a>
			</li>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ul>
</section>
