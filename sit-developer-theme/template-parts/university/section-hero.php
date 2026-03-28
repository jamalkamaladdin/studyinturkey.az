<?php
/**
 * Universitet örtük və başlıq.
 *
 * @var int $university_id get_template_part $args-dan extract.
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 ) {
	return;
}

$title   = sit_theme_get_post_title( $university_id );
$rating  = (float) get_post_meta( $university_id, 'sit_rating', true );
$cover   = (int) get_post_meta( $university_id, 'sit_cover_image_id', true );
$logo_id = (int) get_post_meta( $university_id, 'sit_logo_id', true );
$students = (int) get_post_meta( $university_id, 'sit_student_count', true );
$founded  = (int) get_post_meta( $university_id, 'sit_founded_year', true );
$ranking  = (int) get_post_meta( $university_id, 'sit_global_ranking', true );
$tuition  = get_post_meta( $university_id, 'sit_tuition_fee_min', true );
$cities   = get_the_terms( $university_id, 'city' );
$types    = get_the_terms( $university_id, 'university_type' );

$uni_base = sit_theme_localize_url( get_permalink( $university_id ) );
if ( function_exists( 'sit_theme_university_sub_url' ) ) {
	$dorms_sub  = sit_theme_university_sub_url( $university_id, 'dormitories' );
	$admit_sub  = sit_theme_university_sub_url( $university_id, 'admission' );
} else {
	$dorms_sub = $uni_base ? $uni_base . '#dormitories' : '#dormitories';
	$admit_sub = $uni_base ? $uni_base . '#admission-requirements' : '#admission-requirements';
}
$programs_a  = sit_theme_programs_archive_url();
?>
<section class="relative border-b border-slate-200 bg-slate-900 text-white" aria-labelledby="sit-univ-hero-title">
	<div class="absolute inset-0 overflow-hidden">
		<?php
		if ( $cover ) {
			echo wp_get_attachment_image(
				$cover,
				'full',
				false,
				[
					'class' => 'h-full w-full object-cover opacity-40',
					'alt'   => '',
				]
			);
		} else {
			echo '<div class="h-full w-full bg-gradient-to-br from-brand-900 via-slate-900 to-teal-900 opacity-90" aria-hidden="true"></div>';
		}
		?>
		<div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent"></div>
	</div>
	<div class="sit-container relative py-14 sm:py-16 lg:py-20">
		<div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
			<div class="max-w-3xl">
				<?php if ( $logo_id ) : ?>
					<div class="mb-4 inline-flex rounded-2xl bg-white p-3 shadow-lg ring-1 ring-white/20">
						<?php echo wp_get_attachment_image( $logo_id, 'medium', false, [ 'class' => 'h-14 w-auto max-w-[200px] object-contain sm:h-16' ] ); ?>
					</div>
				<?php endif; ?>
				<h1 id="sit-univ-hero-title" class="text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl"><?php echo esc_html( $title ); ?></h1>
				<div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-200">
					<?php if ( ! is_wp_error( $cities ) && is_array( $cities ) && $cities ) : ?>
						<span>
							<?php
							$names = array_map(
								function ( $t ) {
									return sit_theme_get_term_name( (int) $t->term_id, 'city' );
								},
								$cities
							);
							echo esc_html( implode( ', ', array_filter( $names ) ) );
							?>
						</span>
					<?php endif; ?>
					<?php if ( ! is_wp_error( $types ) && is_array( $types ) && $types ) : ?>
						<?php foreach ( $types as $t ) : ?>
							<span class="rounded-full bg-white/10 px-2.5 py-0.5 text-xs font-medium"><?php echo esc_html( sit_theme_get_term_name( (int) $t->term_id, 'university_type' ) ); ?></span>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<?php if ( $rating > 0 ) : ?>
					<p class="mt-4 text-lg font-semibold text-amber-300" aria-label="<?php esc_attr_e( 'Reytinq', 'studyinturkey' ); ?>">
						<?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?> / 5 ★
					</p>
				<?php endif; ?>
				<nav class="mt-6 flex flex-wrap gap-2 text-sm" aria-label="<?php esc_attr_e( 'Səhifə bölmələri', 'studyinturkey' ); ?>">
					<a href="#programs" class="rounded-full border border-white/30 bg-white/20 px-3.5 py-1.5 font-medium backdrop-blur-sm hover:bg-white/30"><?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></a>
					<a href="#admission-requirements" class="rounded-full border border-white/30 bg-white/20 px-3.5 py-1.5 font-medium backdrop-blur-sm hover:bg-white/30"><?php esc_html_e( 'Qəbul', 'studyinturkey' ); ?></a>
					<a href="<?php echo esc_url( $dorms_sub ); ?>" class="rounded-full border border-white/30 bg-white/20 px-3.5 py-1.5 font-medium backdrop-blur-sm hover:bg-white/30"><?php esc_html_e( 'Yataqxanalar', 'studyinturkey' ); ?></a>
					<a href="#campus" class="rounded-full border border-white/30 bg-white/20 px-3.5 py-1.5 font-medium backdrop-blur-sm hover:bg-white/30"><?php esc_html_e( 'Kampus', 'studyinturkey' ); ?></a>
					<a href="<?php echo esc_url( $programs_a ); ?>" class="rounded-full bg-brand-500 px-4 py-2 font-semibold text-white shadow-lg ring-1 ring-brand-400/50 hover:bg-brand-400"><?php esc_html_e( 'Müraciət et', 'studyinturkey' ); ?></a>
					<a href="<?php echo esc_url( $admit_sub ); ?>" class="rounded-full border border-white/30 bg-white/20 px-3.5 py-1.5 font-medium backdrop-blur-sm hover:bg-white/30"><?php esc_html_e( 'Qəbul tələbləri (tam)', 'studyinturkey' ); ?></a>
				</nav>
			</div>
			<dl class="grid grid-cols-2 gap-4 rounded-2xl bg-white/10 p-4 text-sm backdrop-blur sm:grid-cols-4 lg:max-w-xl lg:grid-cols-2">
				<?php if ( $students > 0 ) : ?>
					<div>
						<dt class="text-slate-400"><?php esc_html_e( 'Tələbələr', 'studyinturkey' ); ?></dt>
						<dd class="mt-0.5 font-semibold"><?php echo esc_html( number_format_i18n( $students ) ); ?></dd>
					</div>
				<?php endif; ?>
				<?php if ( $founded > 0 ) : ?>
					<div>
						<dt class="text-slate-400"><?php esc_html_e( 'Təsis', 'studyinturkey' ); ?></dt>
						<dd class="mt-0.5 font-semibold"><?php echo esc_html( (string) $founded ); ?></dd>
					</div>
				<?php endif; ?>
				<?php if ( $ranking > 0 ) : ?>
					<div>
						<dt class="text-slate-400"><?php esc_html_e( 'Reytinq (qlobal)', 'studyinturkey' ); ?></dt>
						<dd class="mt-0.5 font-semibold">#<?php echo esc_html( number_format_i18n( $ranking ) ); ?></dd>
					</div>
				<?php endif; ?>
				<?php if ( is_numeric( $tuition ) && (float) $tuition > 0 ) : ?>
					<div>
						<dt class="text-slate-400"><?php esc_html_e( 'İlkin ödəniş (min.)', 'studyinturkey' ); ?></dt>
						<dd class="mt-0.5 font-semibold"><?php echo esc_html( number_format_i18n( (float) $tuition, 0 ) ); ?></dd>
					</div>
				<?php endif; ?>
			</dl>
		</div>
	</div>
</section>
