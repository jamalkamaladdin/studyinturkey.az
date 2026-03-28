<?php
/**
 * Qəbul və ümumi təsvir (post məzmunu).
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = isset( $university_id ) ? absint( $university_id ) : 0;
if ( $university_id < 1 ) {
	return;
}

$html    = sit_theme_get_post_content_filtered( $university_id );
$has_txt = '' !== trim( wp_strip_all_tags( $html ) );
?>
<section class="scroll-mt-24" id="admission" aria-labelledby="sit-admission-title">
	<h2 id="sit-admission-title" class="text-2xl font-bold text-slate-900"><?php esc_html_e( 'Qəbul və təqdimat', 'studyinturkey' ); ?></h2>
	<?php if ( $has_txt ) : ?>
		<div class="sit-entry-content mt-4 text-slate-700 leading-relaxed">
			<?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content filters ?>
		</div>
	<?php else : ?>
		<p class="mt-4 text-sm text-slate-500"><?php esc_html_e( 'Bu bölmə üçün məzmun admin panelindən əlavə edilə bilər.', 'studyinturkey' ); ?></p>
	<?php endif; ?>
</section>
