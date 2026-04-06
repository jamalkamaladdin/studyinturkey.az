<?php
/**
 * Qəbul tələbləri — yalnız proqramı olan dərəcələr + admin məzmunu.
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 || ! class_exists( 'SIT_University_Admission_Meta', false ) ) {
	return;
}

$req       = SIT_University_Admission_Meta::get_requirements_decoded( $university_id );
$deg_slugs = sit_theme_filter_admission_degrees_for_display( $university_id, $req );
if ( [] === $deg_slugs ) {
	return;
}

$admission_url = sit_theme_university_sub_url( $university_id, 'admission' );
?>
<section class="scroll-mt-24" id="admission-requirements" aria-labelledby="sit-admreq-title">
	<div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
		<h2 id="sit-admreq-title" class="text-2xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Qəbul tələbləri', 'studyinturkey' ); ?></h2>
		<a href="<?php echo esc_url( $admission_url ); ?>" class="text-sm font-semibold text-brand-700 hover:text-brand-600 dark:text-brand-400"><?php esc_html_e( 'Tam səhifə', 'studyinturkey' ); ?> →</a>
	</div>
	<p class="mt-2 text-sm text-slate-600 dark:text-slate-400"><?php esc_html_e( 'Dərəcə növünə görə sənədlər və müraciət addımları.', 'studyinturkey' ); ?></p>
	<div class="mt-6">
		<?php get_template_part( 'template-parts/university/part', 'admission-degrees', [ 'university_id' => $university_id, 'show_all' => false ] ); ?>
	</div>
</section>
