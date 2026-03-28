<?php
/**
 * Kampuslar.
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = isset( $university_id ) ? absint( $university_id ) : 0;
if ( $university_id < 1 || ! post_type_exists( 'campus' ) ) {
	return;
}

$q = sit_theme_query_posts_by_university( 'campus', $university_id );
if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="scroll-mt-24" id="campus" aria-labelledby="sit-campus-title">
	<h2 id="sit-campus-title" class="text-2xl font-bold text-slate-900"><?php esc_html_e( 'Kampuslar', 'studyinturkey' ); ?></h2>
	<ul class="mt-6 space-y-4">
		<?php
		while ( $q->have_posts() ) :
			$q->the_post();
			$pid   = get_the_ID();
			$name  = sit_theme_get_post_title( $pid );
			$addr  = (string) get_post_meta( $pid, 'sit_address', true );
			$lat   = (float) get_post_meta( $pid, 'sit_latitude', true );
			$lng   = (float) get_post_meta( $pid, 'sit_longitude', true );
			$map   = ( 0.0 !== $lat || 0.0 !== $lng )
				? 'https://www.google.com/maps?q=' . rawurlencode( (string) $lat . ',' . (string) $lng )
				: '';
			?>
			<li class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
				<h3 class="font-semibold text-slate-900"><?php echo esc_html( $name ); ?></h3>
				<?php if ( '' !== $addr ) : ?>
					<p class="mt-2 text-sm text-slate-600 whitespace-pre-line"><?php echo esc_html( $addr ); ?></p>
				<?php endif; ?>
				<?php if ( $map ) : ?>
					<a href="<?php echo esc_url( $map ); ?>" class="mt-3 inline-flex text-sm font-semibold text-brand-700" rel="noopener noreferrer" target="_blank"><?php esc_html_e( 'Xəritədə aç', 'studyinturkey' ); ?></a>
				<?php endif; ?>
			</li>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ul>
</section>
