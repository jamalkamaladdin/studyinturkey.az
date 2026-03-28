<?php
/**
 * Bu universitetə bağlı rəylər.
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 || ! post_type_exists( 'review' ) ) {
	return;
}

$q = sit_theme_query_posts_by_university( 'review', $university_id );
if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="scroll-mt-24" id="reviews" aria-labelledby="sit-univ-reviews-title">
	<h2 id="sit-univ-reviews-title" class="text-2xl font-bold text-slate-900"><?php esc_html_e( 'Tələbə rəyləri', 'studyinturkey' ); ?></h2>
	<ul class="mt-6 grid gap-4 md:grid-cols-2">
		<?php
		while ( $q->have_posts() ) :
			$q->the_post();
			$pid     = get_the_ID();
			$rating  = (float) get_post_meta( $pid, 'sit_rating', true );
			$name    = (string) get_post_meta( $pid, 'sit_student_name', true );
			$country = (string) get_post_meta( $pid, 'sit_student_country', true );
			$text    = sit_theme_get_post_excerpt( $pid );
			if ( '' === $text ) {
				$text = wp_trim_words( wp_strip_all_tags( sit_theme_get_post_content_filtered( $pid ) ), 40, '…' );
			}
			?>
			<li class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5">
				<?php if ( $rating > 0 ) : ?>
					<p class="text-sm text-amber-600" aria-label="<?php esc_attr_e( 'Reytinq', 'studyinturkey' ); ?>">
						<?php echo esc_html( str_repeat( '★', (int) round( $rating ) ) . str_repeat( '☆', max( 0, 5 - (int) round( $rating ) ) ) ); ?>
					</p>
				<?php endif; ?>
				<p class="mt-2 text-sm text-slate-700 leading-relaxed"><?php echo esc_html( $text ); ?></p>
				<p class="mt-3 text-xs font-medium text-slate-500">
					<?php echo esc_html( $name ); ?>
					<?php if ( $country ) : ?>
						<span class="text-slate-400"> — <?php echo esc_html( $country ); ?></span>
					<?php endif; ?>
				</p>
			</li>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ul>
</section>
