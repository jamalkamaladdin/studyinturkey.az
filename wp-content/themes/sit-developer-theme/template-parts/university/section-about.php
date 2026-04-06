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
			<div class="relative overflow-hidden rounded-2xl" data-sit-about-slider>
				<div class="flex transition-transform duration-300" data-sit-about-track>
					<?php foreach ( $gallery_ids as $aid ) :
						$img_url = wp_get_attachment_image_url( $aid, 'large' );
						if ( ! $img_url ) { continue; }
						?>
						<img src="<?php echo esc_url( $img_url ); ?>" alt="" class="h-72 w-full shrink-0 object-cover lg:h-80" loading="lazy" />
					<?php endforeach; ?>
				</div>
				<?php if ( count( $gallery_ids ) > 1 ) : ?>
					<button type="button" class="absolute start-2 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow-md hover:bg-white" data-sit-about-prev aria-label="<?php esc_attr_e( 'Əvvəlki', 'studyinturkey' ); ?>">
						<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
					</button>
					<button type="button" class="absolute end-2 top-1/2 z-10 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow-md hover:bg-white" data-sit-about-next aria-label="<?php esc_attr_e( 'Növbəti', 'studyinturkey' ); ?>">
						<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
					</button>
					<div class="absolute bottom-3 start-0 end-0 flex justify-center gap-1.5" data-sit-about-dots>
						<?php for ( $di = 0; $di < count( $gallery_ids ); $di++ ) : ?>
							<button type="button" class="h-2 w-2 rounded-full <?php echo 0 === $di ? 'bg-white' : 'bg-white/50'; ?>" data-sit-about-dot="<?php echo $di; ?>"></button>
						<?php endfor; ?>
					</div>
				<?php endif; ?>
			</div>
			<script>
			(function(){
				var s = document.querySelector('[data-sit-about-slider]');
				if (!s) return;
				var track = s.querySelector('[data-sit-about-track]');
				var imgs = track.querySelectorAll('img');
				var dots = s.querySelectorAll('[data-sit-about-dot]');
				var cur = 0, total = imgs.length;
				if (total < 2) return;
				function go(i) {
					cur = (i + total) % total;
					track.style.transform = 'translateX(-' + (cur * 100) + '%)';
					dots.forEach(function(d, j) { d.className = d.className.replace(/bg-white(\/50)?/g, '') + (j === cur ? ' bg-white' : ' bg-white/50'); });
				}
				var prev = s.querySelector('[data-sit-about-prev]');
				var next = s.querySelector('[data-sit-about-next]');
				if (prev) prev.addEventListener('click', function() { go(cur - 1); });
				if (next) next.addEventListener('click', function() { go(cur + 1); });
				dots.forEach(function(d) { d.addEventListener('click', function() { go(parseInt(d.getAttribute('data-sit-about-dot'), 10)); }); });
			})();
			</script>
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
