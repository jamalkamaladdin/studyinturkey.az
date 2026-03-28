<?php
/**
 * Kampuslar.
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 || ! post_type_exists( 'campus' ) ) {
	return;
}

$q = sit_theme_query_posts_by_university( 'campus', $university_id );
if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="scroll-mt-24" id="campus" aria-labelledby="sit-campus-title">
	<h2 id="sit-campus-title" class="text-2xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Kampuslar', 'studyinturkey' ); ?></h2>
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
			<li class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
				<h3 class="font-semibold text-slate-900 dark:text-white">
					<a href="<?php echo esc_url( sit_theme_university_sub_url( $university_id, 'campus', (string) get_post()->post_name ) ); ?>" class="hover:text-brand-600 dark:hover:text-brand-400"><?php echo esc_html( $name ); ?></a>
				</h3>
				<?php if ( '' !== $addr ) : ?>
					<p class="mt-2 text-sm text-slate-600 whitespace-pre-line dark:text-slate-400"><?php echo esc_html( $addr ); ?></p>
				<?php endif; ?>
				<div class="mt-3 flex flex-wrap gap-3">
					<?php if ( $map ) : ?>
						<a href="<?php echo esc_url( $map ); ?>" class="text-sm font-semibold text-brand-700 dark:text-brand-400" rel="noopener noreferrer" target="_blank"><?php esc_html_e( 'Xəritədə aç', 'studyinturkey' ); ?></a>
					<?php endif; ?>
					<a href="<?php echo esc_url( sit_theme_university_sub_url( $university_id, 'campus', (string) get_post()->post_name ) ); ?>" class="text-sm font-semibold text-slate-600 hover:text-brand-700 dark:text-slate-400 dark:hover:text-brand-400"><?php esc_html_e( 'Ətraflı', 'studyinturkey' ); ?> →</a>
				</div>
			</li>
			<?php
		endwhile;
		wp_reset_postdata();
		?>
	</ul>
</section>
