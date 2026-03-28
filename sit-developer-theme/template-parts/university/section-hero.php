<?php
/**
 * Universitet hero — təmiz, cover fotosuz.
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 ) {
	return;
}

$title    = sit_theme_get_post_title( $university_id );
$rating   = (float) get_post_meta( $university_id, 'sit_rating', true );
$logo_id  = (int) get_post_meta( $university_id, 'sit_logo_id', true );
$students = (int) get_post_meta( $university_id, 'sit_student_count', true );
$founded  = (int) get_post_meta( $university_id, 'sit_founded_year', true );
$ranking  = (int) get_post_meta( $university_id, 'sit_global_ranking', true );
$tuition  = get_post_meta( $university_id, 'sit_tuition_fee_min', true );
$website  = (string) get_post_meta( $university_id, 'sit_official_website', true );
$cities   = get_the_terms( $university_id, 'city' );
$types    = get_the_terms( $university_id, 'university_type' );

$apply_url  = '';
$apply_page = get_page_by_path( 'muraciet' );
if ( $apply_page ) {
	$apply_url = sit_theme_localize_url( get_permalink( $apply_page ) );
} else {
	$apply_url = sit_theme_programs_archive_url();
}

$uni_base = sit_theme_localize_url( get_permalink( $university_id ) );
if ( function_exists( 'sit_theme_university_sub_url' ) ) {
	$dorms_sub = sit_theme_university_sub_url( $university_id, 'dormitories' );
} else {
	$dorms_sub = $uni_base ? $uni_base . '#dormitories' : '#dormitories';
}
?>
<section class="border-b border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900" aria-labelledby="sit-univ-hero-title">
	<div class="sit-container py-8 lg:py-10">

		<!-- Üst: Logo + Ad + Meta -->
		<div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:gap-5">
			<?php if ( $logo_id ) : ?>
				<div class="shrink-0 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-800">
					<?php echo wp_get_attachment_image( $logo_id, 'medium', false, [ 'class' => 'h-16 w-16 object-contain sm:h-20 sm:w-20' ] ); ?>
				</div>
			<?php endif; ?>
			<div class="min-w-0">
				<h1 id="sit-univ-hero-title" class="text-2xl font-bold text-slate-900 sm:text-3xl lg:text-4xl dark:text-white"><?php echo esc_html( $title ); ?></h1>
				<div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-sm text-slate-500 dark:text-slate-400">
					<?php if ( ! is_wp_error( $cities ) && is_array( $cities ) && $cities ) : ?>
						<span class="inline-flex items-center gap-1">
							<svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0Z"/></svg>
							<?php
							$names = array_map( function ( $t ) { return sit_theme_get_term_name( (int) $t->term_id, 'city' ); }, $cities );
							echo esc_html( implode( ', ', array_filter( $names ) ) );
							?>
						</span>
					<?php endif; ?>
					<?php if ( ! is_wp_error( $types ) && is_array( $types ) && $types ) : ?>
						<?php foreach ( $types as $t ) : ?>
							<span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300"><?php echo esc_html( sit_theme_get_term_name( (int) $t->term_id, 'university_type' ) ); ?></span>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if ( $rating > 0 ) : ?>
						<span class="inline-flex items-center gap-1 font-semibold text-amber-500">
							<svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
							<?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Stat kartları -->
		<?php
		$stats = [];
		if ( $students > 0 )                                      { $stats[] = [ __( 'Tələbələr', 'studyinturkey' ), number_format_i18n( $students ) ]; }
		if ( $founded > 0 )                                       { $stats[] = [ __( 'Təsis ili', 'studyinturkey' ), (string) $founded ]; }
		if ( $ranking > 0 )                                       { $stats[] = [ __( 'Reytinq', 'studyinturkey' ), '#' . number_format_i18n( $ranking ) ]; }
		if ( is_numeric( $tuition ) && (float) $tuition > 0 )     { $stats[] = [ __( 'İllik ödəniş (min.)', 'studyinturkey' ), '$' . number_format_i18n( (float) $tuition, 0 ) ]; }
		?>
		<?php if ( ! empty( $stats ) ) : ?>
			<div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
				<?php foreach ( $stats as $s ) : ?>
					<div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800">
						<p class="text-xs font-medium text-slate-500 dark:text-slate-400"><?php echo esc_html( $s[0] ); ?></p>
						<p class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white"><?php echo esc_html( $s[1] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Keçid düymələri -->
		<nav class="mt-6 flex flex-wrap items-center gap-2" aria-label="<?php esc_attr_e( 'Səhifə bölmələri', 'studyinturkey' ); ?>">
			<a href="<?php echo esc_url( $apply_url ); ?>" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-500">
				<?php esc_html_e( 'Müraciət et', 'studyinturkey' ); ?>
			</a>
			<a href="#programs" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"><?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></a>
			<a href="#admission-requirements" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"><?php esc_html_e( 'Qəbul', 'studyinturkey' ); ?></a>
			<a href="<?php echo esc_url( $dorms_sub ); ?>" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"><?php esc_html_e( 'Yataqxanalar', 'studyinturkey' ); ?></a>
			<a href="#campus" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"><?php esc_html_e( 'Kampus', 'studyinturkey' ); ?></a>
			<?php if ( '' !== $website ) : ?>
				<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
					<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
					<?php esc_html_e( 'Rəsmi sayt', 'studyinturkey' ); ?>
				</a>
			<?php endif; ?>
		</nav>

	</div>
</section>
