<?php
/**
 * Proqram arxivi — StudyLeo tipli kart (SSR).
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID();
$title   = sit_theme_get_post_title( $post_id );
$link    = sit_theme_localize_url( get_permalink( $post_id ) );
$fee     = get_post_meta( $post_id, 'sit_tuition_fee', true );
$dur     = (string) get_post_meta( $post_id, 'sit_duration', true );
$sch     = (bool) get_post_meta( $post_id, 'sit_scholarship_available', true );
$uid     = (int) get_post_meta( $post_id, 'sit_university_id', true );

$univ_title = '';
$univ_link  = '';
$logo_url   = '';
if ( $uid > 0 ) {
	$univ_title = sit_theme_get_post_title( $uid );
	$univ_link  = sit_theme_localize_url( get_permalink( $uid ) );
	$lid        = (int) get_post_meta( $uid, 'sit_logo_id', true );
	if ( $lid ) {
		$logo_url = wp_get_attachment_image_url( $lid, 'medium' );
	}
}

$deg   = get_the_terms( $post_id, 'degree_type' );
$langs = get_the_terms( $post_id, 'program_language' );
$flds  = get_the_terms( $post_id, 'field_of_study' );

$fmt_terms = static function ( $terms, string $tax ) {
	if ( ! is_array( $terms ) || is_wp_error( $terms ) ) {
		return '';
	}
	$names = [];
	foreach ( $terms as $t ) {
		$names[] = sit_theme_get_term_name( (int) $t->term_id, $tax );
	}
	return $names ? implode( ', ', array_filter( $names ) ) : '';
};

$deg_s   = $fmt_terms( $deg, 'degree_type' );
$lang_s  = $fmt_terms( $langs, 'program_language' );
$field_s = $fmt_terms( $flds, 'field_of_study' );

$fee_num = ( is_numeric( $fee ) && (float) $fee > 0 ) ? (float) $fee : null;
$fee_ref = ( $sch && $fee_num ) ? round( $fee_num * 2, -1 ) : null;
if ( $fee_ref && $fee_ref <= $fee_num ) {
	$fee_ref = null;
}
?>
<article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
	<div class="flex gap-3 border-b border-slate-100 p-4 dark:border-slate-800">
		<?php if ( $logo_url ) : ?>
			<a href="<?php echo esc_url( $univ_link ? $univ_link : $link ); ?>" class="h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-slate-100 bg-white p-1 dark:border-slate-700">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="" class="h-full w-full object-contain" loading="lazy" width="56" height="56" />
			</a>
		<?php else : ?>
			<div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-lg font-bold text-brand-600 dark:bg-brand-950/80 dark:text-brand-300" aria-hidden="true">
				<?php echo esc_html( $univ_title ? ( function_exists( 'mb_substr' ) ? mb_substr( $univ_title, 0, 1 ) : substr( $univ_title, 0, 1 ) ) : '★' ); ?>
			</div>
		<?php endif; ?>
		<div class="min-w-0 flex-1">
			<?php if ( $univ_link ) : ?>
				<a href="<?php echo esc_url( $univ_link ); ?>" class="text-sm font-semibold text-slate-900 hover:text-brand-700 dark:text-white dark:hover:text-brand-300"><?php echo esc_html( $univ_title ); ?></a>
			<?php else : ?>
				<span class="text-sm font-semibold text-slate-500">—</span>
			<?php endif; ?>
			<?php if ( '' !== $field_s ) : ?>
				<p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"><?php echo esc_html( $field_s ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<div class="flex flex-1 flex-col gap-2 px-4 py-3">
		<p class="text-[0.65rem] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500"><?php esc_html_e( 'Proqram', 'studyinturkey' ); ?></p>
		<h2 class="text-base font-semibold leading-snug text-slate-900 dark:text-white">
			<a href="<?php echo esc_url( $link ); ?>" class="text-inherit hover:text-brand-600 dark:hover:text-brand-400"><?php echo esc_html( $title ); ?></a>
		</h2>
		<dl class="mt-1 grid gap-1.5 text-xs text-slate-600 dark:text-slate-400">
			<?php if ( '' !== $deg_s ) : ?>
				<div class="flex gap-2">
					<dt class="shrink-0 font-medium text-slate-500 dark:text-slate-500"><?php esc_html_e( 'Dərəcə', 'studyinturkey' ); ?></dt>
					<dd><?php echo esc_html( $dur ? sprintf( '%s (%s)', $deg_s, $dur ) : $deg_s ); ?></dd>
				</div>
			<?php endif; ?>
			<?php if ( '' !== $lang_s ) : ?>
				<div class="flex gap-2">
					<dt class="shrink-0 font-medium text-slate-500 dark:text-slate-500"><?php esc_html_e( 'Dillər', 'studyinturkey' ); ?></dt>
					<dd><?php echo esc_html( $lang_s ); ?></dd>
				</div>
			<?php endif; ?>
		</dl>
	</div>
	<div class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-3 dark:border-slate-800">
		<div class="text-sm font-semibold text-slate-900 dark:text-white">
			<?php if ( null !== $fee_num ) : ?>
				<?php if ( null !== $fee_ref ) : ?>
					<span class="text-brand-600 dark:text-brand-400"><?php echo esc_html( number_format_i18n( $fee_num, 0 ) ); ?>$</span>
					<span class="ms-1 text-slate-400 line-through dark:text-slate-500"><?php echo esc_html( number_format_i18n( $fee_ref, 0 ) ); ?>$</span>
				<?php else : ?>
					<span><?php echo esc_html( number_format_i18n( $fee_num, 0 ) ); ?>$</span>
				<?php endif; ?>
				<span class="ms-1 text-xs font-normal text-slate-500"><?php esc_html_e( '/ il', 'studyinturkey' ); ?></span>
			<?php else : ?>
				<span class="text-slate-400">—</span>
			<?php endif; ?>
		</div>
		<a href="<?php echo esc_url( $link ); ?>" class="inline-flex min-h-[2.5rem] items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-700 dark:hover:bg-brand-500"><?php esc_html_e( 'Müraciət et', 'studyinturkey' ); ?></a>
	</div>
</article>
