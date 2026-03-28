<?php
/**
 * Universitet haqqında (əsas məzmun / redaktor).
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 ) {
	return;
}

$html    = sit_theme_get_post_content_filtered( $university_id );
$has_txt = '' !== trim( wp_strip_all_tags( $html ) );
if ( ! $has_txt ) {
	return;
}
?>
<section class="scroll-mt-24" id="about" aria-labelledby="sit-about-title">
	<h2 id="sit-about-title" class="text-2xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Universitet haqqında', 'studyinturkey' ); ?></h2>
	<div class="sit-entry-content mt-4 text-slate-700 leading-relaxed dark:text-slate-300">
		<?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content filters ?>
	</div>
</section>
