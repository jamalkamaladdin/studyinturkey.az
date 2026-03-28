<?php
/**
 * Yataqxanalar.
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = isset( $university_id ) ? absint( $university_id ) : 0;
if ( $university_id < 1 || ! post_type_exists( 'dormitory' ) ) {
	return;
}

$q = sit_theme_query_posts_by_university( 'dormitory', $university_id );
if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="scroll-mt-24" id="dormitories" aria-labelledby="sit-dorm-title">
	<h2 id="sit-dorm-title" class="text-2xl font-bold text-slate-900"><?php esc_html_e( 'Yataqxanalar', 'studyinturkey' ); ?></h2>
	<ul class="mt-6 grid gap-4 sm:grid-cols-2">
		<?php
		while ( $q->have_posts() ) :
			$q->the_post();
			$pid        = get_the_ID();
			$name       = sit_theme_get_post_title( $pid );
			$price      = get_post_meta( $pid, 'sit_price', true );
			$distance   = (string) get_post_meta( $pid, 'sit_distance', true );
			$facilities = (string) get_post_meta( $pid, 'sit_facilities', true );
			?>
			<li class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5">
				<h3 class="font-semibold text-slate-900"><?php echo esc_html( $name ); ?></h3>
				<?php if ( is_numeric( $price ) && (float) $price > 0 ) : ?>
					<p class="mt-2 text-sm text-slate-600">
						<?php
						printf(
							/* translators: %s: price */
							esc_html__( 'Qiymət: %s', 'studyinturkey' ),
							esc_html( number_format_i18n( (float) $price, 0 ) )
						);
						?>
					</p>
				<?php endif; ?>
				<?php if ( '' !== $distance ) : ?>
					<p class="mt-1 text-sm text-slate-600"><?php echo esc_html( $distance ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $facilities ) : ?>
					<p class="mt-2 text-sm text-slate-600 leading-relaxed"><?php echo esc_html( wp_strip_all_tags( $facilities ) ); ?></p>
				<?php endif; ?>
			</li>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ul>
</section>
