<?php
/**
 * Bu universitetə bağlı tələbə rəyləri (StudyLeo kartları).
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

$univ_title = sit_theme_get_post_title( $university_id );
?>
<section class="scroll-mt-24 border-t border-slate-200 pt-14 dark:border-slate-700" id="reviews" aria-labelledby="sit-univ-reviews-title">
	<div class="text-center">
		<h2 id="sit-univ-reviews-title" class="text-3xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Tələbə rəyləri', 'studyinturkey' ); ?></h2>
		<p class="mt-2 text-sm text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Tələbələrimizin real təcrübələri', 'studyinturkey' ); ?></p>
	</div>
	<div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
		<?php
		while ( $q->have_posts() ) :
			$q->the_post();
			$pid     = get_the_ID();
			$rating  = (float) get_post_meta( $pid, 'sit_rating', true );
			$name    = (string) get_post_meta( $pid, 'sit_student_name', true );
			$country = (string) get_post_meta( $pid, 'sit_student_country', true );
			$date    = get_the_date( 'M j, Y' );
			$text    = sit_theme_get_post_excerpt( $pid );
			if ( '' === $text ) {
				$text = wp_trim_words( wp_strip_all_tags( sit_theme_get_post_content_filtered( $pid ) ), 50, '…' );
			}
			if ( '' === $name ) {
				$name = sit_theme_get_post_title( $pid );
			}
			?>
			<article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
				<h3 class="text-lg font-bold text-slate-900 dark:text-white"><?php echo esc_html( $name ); ?></h3>
				<p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400"><?php echo esc_html( $univ_title ); ?></p>
				<?php if ( $rating > 0 ) : ?>
					<p class="mt-2 text-amber-500" aria-label="<?php printf( esc_attr__( 'Reytinq: %s/5', 'studyinturkey' ), number_format( $rating, 1 ) ); ?>">
						<?php echo esc_html( str_repeat( '★', (int) round( $rating ) ) . str_repeat( '☆', max( 0, 5 - (int) round( $rating ) ) ) ); ?>
					</p>
				<?php endif; ?>
				<p class="mt-3 flex-1 text-sm text-slate-600 leading-relaxed dark:text-slate-400"><?php echo esc_html( $text ); ?></p>
				<p class="mt-4 text-xs text-slate-400 dark:text-slate-500"><?php echo esc_html( $date ); ?></p>
			</article>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</section>
