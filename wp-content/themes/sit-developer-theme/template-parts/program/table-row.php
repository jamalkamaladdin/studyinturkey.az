<?php
/**
 * Proqram cədvəl sətri (SSR).
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID();
$title   = sit_theme_get_post_title( $post_id );
$link    = sit_theme_localize_url( get_permalink( $post_id ) );
$fee     = get_post_meta( $post_id, 'sit_tuition_fee', true );
$dur     = (string) get_post_meta( $post_id, 'sit_duration', true );
$sch     = (bool) get_post_meta( $post_id, 'sit_scholarship_available', true );
$uid     = sit_theme_get_program_university_id( $post_id );

$univ_title = '';
$univ_link  = '';
if ( $uid > 0 ) {
	$univ_title = sit_theme_get_post_title( $uid );
	$univ_link  = sit_theme_localize_url( get_permalink( $uid ) );
}

$deg   = get_the_terms( $post_id, 'degree_type' );
$langs = get_the_terms( $post_id, 'program_language' );
$flds  = get_the_terms( $post_id, 'field_of_study' );

$fmt_terms = static function ( $terms, string $tax ) {
	if ( ! is_array( $terms ) || is_wp_error( $terms ) ) {
		return '—';
	}
	$names = [];
	foreach ( $terms as $t ) {
		$names[] = sit_theme_get_term_name( (int) $t->term_id, $tax );
	}
	return $names ? implode( ', ', array_filter( $names ) ) : '—';
};
?>
<tr class="border-b border-slate-100 hover:bg-slate-50/80 dark:border-slate-800 dark:hover:bg-slate-800/50">
	<td class="px-3 py-3 font-medium text-slate-900 dark:text-slate-100">
		<a href="<?php echo esc_url( $link ); ?>" class="text-brand-700 hover:text-brand-600"><?php echo esc_html( $title ); ?></a>
	</td>
	<td class="hidden px-3 py-3 text-sm text-slate-600 dark:text-slate-400 lg:table-cell">
		<?php if ( $univ_link ) : ?>
			<a href="<?php echo esc_url( $univ_link ); ?>" class="hover:text-brand-700"><?php echo esc_html( $univ_title ); ?></a>
		<?php else : ?>
			—
		<?php endif; ?>
	</td>
	<td class="hidden px-3 py-3 text-sm text-slate-600 dark:text-slate-400 md:table-cell"><?php echo esc_html( $fmt_terms( $deg, 'degree_type' ) ); ?></td>
	<td class="hidden px-3 py-3 text-sm text-slate-600 dark:text-slate-400 xl:table-cell"><?php echo esc_html( $fmt_terms( $langs, 'program_language' ) ); ?></td>
	<td class="hidden px-3 py-3 text-sm text-slate-600 dark:text-slate-400 2xl:table-cell"><?php echo esc_html( $fmt_terms( $flds, 'field_of_study' ) ); ?></td>
	<td class="whitespace-nowrap px-3 py-3 text-sm text-slate-700 dark:text-slate-300">
		<?php echo ( is_numeric( $fee ) && (float) $fee > 0 ) ? esc_html( number_format_i18n( (float) $fee, 0 ) ) : '—'; ?>
	</td>
	<td class="hidden px-3 py-3 text-sm text-slate-600 dark:text-slate-400 sm:table-cell"><?php echo esc_html( '' !== $dur ? $dur : '—' ); ?></td>
	<td class="px-3 py-3 text-center text-sm">
		<?php if ( $sch ) : ?>
			<span class="inline-flex rounded-md bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-800 dark:bg-brand-950/80 dark:text-brand-200"><?php esc_html_e( 'Bəli', 'studyinturkey' ); ?></span>
		<?php else : ?>
			<span class="text-slate-400">—</span>
		<?php endif; ?>
	</td>
	<td class="px-3 py-3 text-end text-sm">
		<a href="<?php echo esc_url( $link ); ?>" class="font-semibold text-brand-700 hover:text-brand-600"><?php esc_html_e( 'Bax', 'studyinturkey' ); ?></a>
	</td>
</tr>
