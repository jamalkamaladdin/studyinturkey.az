<?php
/**
 * Beynəlxalq tələbə statistikası (admin meta).
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 || ! class_exists( 'SIT_University_Admission_Meta', false ) ) {
	return;
}

$total   = (string) get_post_meta( $university_id, SIT_University_Admission_Meta::META_INTL_TOTAL, true );
$foreign = (string) get_post_meta( $university_id, SIT_University_Admission_Meta::META_INTL_FOREIGN, true );
$accept  = (string) get_post_meta( $university_id, SIT_University_Admission_Meta::META_INTL_ACCEPT, true );

if ( '' === trim( $total . $foreign . $accept ) ) {
	return;
}
?>
<section class="scroll-mt-24" id="international" aria-labelledby="sit-intl-title">
	<h2 id="sit-intl-title" class="text-2xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Beynəlxalq tələbələr', 'studyinturkey' ); ?></h2>
	<p class="mt-2 text-sm text-slate-600 dark:text-slate-400"><?php esc_html_e( 'Qısa statistika və qəbul göstəriciləri.', 'studyinturkey' ); ?></p>
	<dl class="mt-6 grid gap-4 sm:grid-cols-3">
		<?php if ( '' !== trim( $total ) ) : ?>
			<div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
				<dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Tələbələr', 'studyinturkey' ); ?></dt>
				<dd class="mt-1 text-2xl font-bold text-slate-900 dark:text-white"><?php echo esc_html( $total ); ?></dd>
			</div>
		<?php endif; ?>
		<?php if ( '' !== trim( $foreign ) ) : ?>
			<div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
				<dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Xarici tələbələr', 'studyinturkey' ); ?></dt>
				<dd class="mt-1 text-2xl font-bold text-slate-900 dark:text-white"><?php echo esc_html( $foreign ); ?></dd>
			</div>
		<?php endif; ?>
		<?php if ( '' !== trim( $accept ) ) : ?>
			<div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/50">
				<dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Qəbul faizi', 'studyinturkey' ); ?></dt>
				<dd class="mt-1 text-2xl font-bold text-slate-900 dark:text-white"><?php echo esc_html( $accept ); ?></dd>
			</div>
		<?php endif; ?>
	</dl>
</section>
