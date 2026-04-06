<?php
/**
 * Tələbə rəyləri (review CPT) — uni logosu ilə.
 */

defined( 'ABSPATH' ) || exit;

if ( ! post_type_exists( 'review' ) ) {
	return;
}

$q = new WP_Query(
	[
		'post_type'      => 'review',
		'post_status'    => 'publish',
		'posts_per_page' => 6,
		'orderby'        => 'date',
		'order'          => 'DESC',
	]
);

if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="bg-slate-900 py-14 text-white lg:py-16" aria-labelledby="sit-reviews-title">
	<div class="sit-container">
		<h2 id="sit-reviews-title" class="text-2xl font-bold sm:text-3xl">
			<?php esc_html_e( 'Tələbə rəyləri', 'studyinturkey' ); ?>
		</h2>
		<p class="mt-2 max-w-xl text-slate-300">
			<?php esc_html_e( 'Real təcrübələr — seçiminizi asanlaşdırır.', 'studyinturkey' ); ?>
		</p>
		<div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
			<?php
			while ( $q->have_posts() ) :
				$q->the_post();
				$pid     = get_the_ID();
				$title   = sit_theme_get_post_title( $pid );
				$rating  = (float) get_post_meta( $pid, 'sit_rating', true );
				$name    = (string) get_post_meta( $pid, 'sit_student_name', true );
				$ctry    = (string) get_post_meta( $pid, 'sit_student_country', true );
				$excerpt = sit_theme_get_post_excerpt( $pid );

				// University logo
				$univ_id      = (int) get_post_meta( $pid, 'sit_university_id', true );
				$univ_name    = '';
				$univ_logo_url = '';
				if ( $univ_id > 0 ) {
					$univ_name = sit_theme_get_post_title( $univ_id );
					$lid       = (int) get_post_meta( $univ_id, 'sit_logo_id', true );
					if ( $lid ) {
						$univ_logo_url = wp_get_attachment_image_url( $lid, 'thumbnail' );
					}
				}
				?>
				<blockquote class="relative rounded-2xl border border-slate-700 bg-slate-800/50 p-6">
					<?php if ( $univ_logo_url ) : ?>
						<div class="absolute end-4 top-4 h-8 w-8 overflow-hidden rounded-full border border-slate-600 bg-white p-0.5" title="<?php echo esc_attr( $univ_name ); ?>">
							<img src="<?php echo esc_url( $univ_logo_url ); ?>" alt="<?php echo esc_attr( $univ_name ); ?>" class="h-full w-full rounded-full object-contain" loading="lazy" width="28" height="28" />
						</div>
					<?php endif; ?>
					<?php if ( $rating > 0 ) : ?>
						<p class="text-sm font-medium text-brand-300" aria-label="<?php esc_attr_e( 'Reytinq', 'studyinturkey' ); ?>">
							<?php echo esc_html( str_repeat( '★', (int) round( $rating ) ) . str_repeat( '☆', max( 0, 5 - (int) round( $rating ) ) ) ); ?>
						</p>
					<?php endif; ?>
					<p class="mt-3 text-slate-200 leading-relaxed"><?php echo esc_html( $excerpt ); ?></p>
					<footer class="mt-4 text-sm text-slate-400">
						<cite class="not-italic font-semibold text-white"><?php echo esc_html( $name ?: $title ); ?></cite>
						<?php if ( $ctry ) : ?>
							<span class="text-slate-500"> — <?php echo esc_html( $ctry ); ?></span>
						<?php endif; ?>
					</footer>
				</blockquote>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
