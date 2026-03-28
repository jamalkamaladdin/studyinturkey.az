<?php
/**
 * Universitet hero — modern dizayn.
 *
 * @var int $university_id get_template_part $args-dan extract.
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 ) {
	return;
}

$title    = sit_theme_get_post_title( $university_id );
$rating   = (float) get_post_meta( $university_id, 'sit_rating', true );
$cover    = (int) get_post_meta( $university_id, 'sit_cover_image_id', true );
$logo_id  = (int) get_post_meta( $university_id, 'sit_logo_id', true );
$students = (int) get_post_meta( $university_id, 'sit_student_count', true );
$founded  = (int) get_post_meta( $university_id, 'sit_founded_year', true );
$ranking  = (int) get_post_meta( $university_id, 'sit_global_ranking', true );
$tuition  = get_post_meta( $university_id, 'sit_tuition_fee_min', true );
$website  = (string) get_post_meta( $university_id, 'sit_official_website', true );
$cities   = get_the_terms( $university_id, 'city' );
$types    = get_the_terms( $university_id, 'university_type' );

/* Müraciət URL — əgər application səhifəsi varsa ora, yoxsa proqramlar arxivinə */
$apply_url = '';
$apply_page = get_page_by_path( 'muraciet' );
if ( $apply_page ) {
	$apply_url = sit_theme_localize_url( get_permalink( $apply_page ) );
} else {
	$apply_url = sit_theme_programs_archive_url();
}

$uni_base = sit_theme_localize_url( get_permalink( $university_id ) );
if ( function_exists( 'sit_theme_university_sub_url' ) ) {
	$dorms_sub = sit_theme_university_sub_url( $university_id, 'dormitories' );
	$admit_sub = sit_theme_university_sub_url( $university_id, 'admission' );
} else {
	$dorms_sub = $uni_base ? $uni_base . '#dormitories' : '#dormitories';
	$admit_sub = $uni_base ? $uni_base . '#admission-requirements' : '#admission-requirements';
}
?>
<section class="relative overflow-hidden bg-slate-900 text-white" aria-labelledby="sit-univ-hero-title">
	<!-- Cover -->
	<div class="absolute inset-0">
		<?php if ( $cover ) :
			echo wp_get_attachment_image( $cover, 'full', false, [ 'class' => 'h-full w-full object-cover opacity-30', 'alt' => '' ] );
		else : ?>
			<div class="h-full w-full bg-gradient-to-br from-brand-900 via-slate-900 to-teal-900" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/50 to-slate-900/30"></div>
	</div>

	<div class="sit-container relative py-12 sm:py-16 lg:py-20">
		<!-- Top: Logo + Title -->
		<div class="flex flex-col items-start gap-5 lg:flex-row lg:items-center lg:gap-6">
			<?php if ( $logo_id ) : ?>
				<div class="inline-flex shrink-0 rounded-2xl bg-white p-3 shadow-lg">
					<?php echo wp_get_attachment_image( $logo_id, 'medium', false, [ 'class' => 'h-16 w-auto max-w-[180px] object-contain sm:h-20' ] ); ?>
				</div>
			<?php endif; ?>
			<div class="min-w-0">
				<h1 id="sit-univ-hero-title" class="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl"><?php echo esc_html( $title ); ?></h1>
				<div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
					<?php if ( ! is_wp_error( $cities ) && is_array( $cities ) && $cities ) : ?>
						<span class="inline-flex items-center gap-1.5 text-slate-300">
							<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0Z"/></svg>
							<?php
							$names = array_map( function ( $t ) { return sit_theme_get_term_name( (int) $t->term_id, 'city' ); }, $cities );
							echo esc_html( implode( ', ', array_filter( $names ) ) );
							?>
						</span>
					<?php endif; ?>
					<?php if ( ! is_wp_error( $types ) && is_array( $types ) && $types ) : ?>
						<?php foreach ( $types as $t ) : ?>
							<span class="rounded-full bg-white/15 px-3 py-0.5 text-xs font-medium text-white"><?php echo esc_html( sit_theme_get_term_name( (int) $t->term_id, 'university_type' ) ); ?></span>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if ( $rating > 0 ) : ?>
						<span class="inline-flex items-center gap-1 text-amber-400 font-semibold">
							<svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
							<?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Stats grid -->
		<?php
		$stats = [];
		if ( $students > 0 ) {
			$stats[] = [ 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>', 'label' => __( 'Tələbələr', 'studyinturkey' ), 'value' => number_format_i18n( $students ) ];
		}
		if ( $founded > 0 ) {
			$stats[] = [ 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z"/></svg>', 'label' => __( 'Təsis', 'studyinturkey' ), 'value' => (string) $founded ];
		}
		if ( $ranking > 0 ) {
			$stats[] = [ 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-2.77.896m5.25-5.396v-.003c0-1.113-.285-2.16-.786-3.07"/></svg>', 'label' => __( 'Reytinq (qlobal)', 'studyinturkey' ), 'value' => '#' . number_format_i18n( $ranking ) ];
		}
		if ( is_numeric( $tuition ) && (float) $tuition > 0 ) {
			$stats[] = [ 'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>', 'label' => __( 'İlkin ödəniş (min.)', 'studyinturkey' ), 'value' => '$' . number_format_i18n( (float) $tuition, 0 ) ];
		}
		?>
		<?php if ( ! empty( $stats ) ) : ?>
			<div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:max-w-2xl">
				<?php foreach ( $stats as $s ) : ?>
					<div class="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-3 backdrop-blur-sm">
						<span class="text-brand-300"><?php echo $s['icon']; ?></span>
						<div>
							<p class="text-xs text-slate-400"><?php echo esc_html( $s['label'] ); ?></p>
							<p class="text-lg font-bold"><?php echo esc_html( $s['value'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Action buttons -->
		<div class="mt-8 flex flex-wrap items-center gap-3" aria-label="<?php esc_attr_e( 'Səhifə bölmələri', 'studyinturkey' ); ?>">
			<a href="<?php echo esc_url( $apply_url ); ?>" class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-brand-500/25 transition hover:bg-brand-400 hover:shadow-brand-400/30">
				<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
				<?php esc_html_e( 'Müraciət et', 'studyinturkey' ); ?>
			</a>
			<a href="#programs" class="rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold backdrop-blur-sm transition hover:bg-white/25"><?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></a>
			<a href="#admission-requirements" class="rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold backdrop-blur-sm transition hover:bg-white/25"><?php esc_html_e( 'Qəbul', 'studyinturkey' ); ?></a>
			<a href="<?php echo esc_url( $dorms_sub ); ?>" class="rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold backdrop-blur-sm transition hover:bg-white/25"><?php esc_html_e( 'Yataqxanalar', 'studyinturkey' ); ?></a>
			<a href="#campus" class="rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold backdrop-blur-sm transition hover:bg-white/25"><?php esc_html_e( 'Kampus', 'studyinturkey' ); ?></a>
			<?php if ( '' !== $website ) : ?>
				<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-semibold backdrop-blur-sm transition hover:bg-white/25">
					<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
					<?php esc_html_e( 'Rəsmi sayt', 'studyinturkey' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
