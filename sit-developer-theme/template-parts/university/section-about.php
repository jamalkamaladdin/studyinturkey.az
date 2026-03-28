<?php
/**
 * Universitet haqqında — foto qalereyası + akkordeon (Haqqında, Missiya, Tələbə həyatı).
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 ) {
	return;
}

$has_about_meta = class_exists( 'SIT_University_About_Meta', false );

$desc    = $has_about_meta ? (string) get_post_meta( $university_id, SIT_University_About_Meta::META_ABOUT_DESC, true ) : '';
$mission = $has_about_meta ? (string) get_post_meta( $university_id, SIT_University_About_Meta::META_ABOUT_MISSION, true ) : '';
$student = $has_about_meta ? (string) get_post_meta( $university_id, SIT_University_About_Meta::META_ABOUT_STUDENT, true ) : '';
$gallery = $has_about_meta ? (string) get_post_meta( $university_id, SIT_University_About_Meta::META_ABOUT_GALLERY, true ) : '';

/* Fallback: WP content as description if meta is empty */
if ( '' === trim( wp_strip_all_tags( $desc ) ) ) {
	$desc = sit_theme_get_post_content_filtered( $university_id );
}

$has_desc    = '' !== trim( wp_strip_all_tags( $desc ) );
$has_mission = '' !== trim( wp_strip_all_tags( $mission ) );
$has_student = '' !== trim( wp_strip_all_tags( $student ) );

if ( ! $has_desc && ! $has_mission && ! $has_student ) {
	return;
}

$gallery_ids = [];
if ( '' !== $gallery ) {
	$gallery_ids = array_filter( array_map( 'absint', explode( ',', $gallery ) ) );
}

$accordion = [];
if ( $has_desc ) {
	$accordion[] = [
		'id'    => 'about-desc',
		'title' => __( 'Haqqında', 'studyinturkey' ),
		'html'  => $desc,
		'open'  => true,
	];
}
if ( $has_mission ) {
	$accordion[] = [
		'id'    => 'about-mission',
		'title' => __( 'Missiya', 'studyinturkey' ),
		'html'  => $mission,
		'open'  => false,
	];
}
if ( $has_student ) {
	$accordion[] = [
		'id'    => 'about-student',
		'title' => __( 'Tələbə həyatı', 'studyinturkey' ),
		'html'  => $student,
		'open'  => false,
	];
}
?>
<section class="scroll-mt-24" id="about" aria-labelledby="sit-about-title">
	<h2 id="sit-about-title" class="text-2xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Universitet haqqında', 'studyinturkey' ); ?></h2>

	<div class="mt-6 grid gap-8 <?php echo ! empty( $gallery_ids ) ? 'lg:grid-cols-2 lg:items-start' : ''; ?>">
		<?php if ( ! empty( $gallery_ids ) ) : ?>
			<div class="sit-uni-slider flex snap-x snap-mandatory gap-3 overflow-x-auto rounded-2xl pb-2" style="scroll-padding:0;">
				<?php foreach ( $gallery_ids as $aid ) :
					$img_url = wp_get_attachment_image_url( $aid, 'large' );
					if ( ! $img_url ) { continue; }
					?>
					<img src="<?php echo esc_url( $img_url ); ?>" alt="" class="h-64 w-auto max-w-[80%] shrink-0 snap-start rounded-xl object-cover lg:h-72" loading="lazy" />
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="space-y-3 <?php echo ! empty( $gallery_ids ) ? '' : 'lg:col-span-2'; ?>">
			<?php foreach ( $accordion as $item ) : ?>
				<details class="group rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900" <?php echo $item['open'] ? 'open' : ''; ?>>
					<summary class="flex cursor-pointer items-center justify-between px-5 py-4 text-base font-semibold text-slate-900 dark:text-white">
						<?php echo esc_html( $item['title'] ); ?>
						<svg class="h-5 w-5 shrink-0 text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
					</summary>
					<div class="sit-entry-content border-t border-slate-200 px-5 py-4 text-slate-700 leading-relaxed dark:border-slate-700 dark:text-slate-300">
						<?php echo wp_kses_post( $item['html'] ); ?>
					</div>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
