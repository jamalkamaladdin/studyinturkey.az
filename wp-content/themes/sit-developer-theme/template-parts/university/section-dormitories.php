<?php
/**
 * Yataqxanalar (qısa siyahı + alt səhifə keçidi).
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 || ! post_type_exists( 'dormitory' ) ) {
	return;
}

$q = sit_theme_query_posts_by_university( 'dormitory', $university_id );
if ( ! $q->have_posts() ) {
	return;
}

$dorms_url   = sit_theme_university_sub_url( $university_id, 'dormitories' );
$total_dorms = (int) $q->found_posts;
?>
<section class="scroll-mt-24" id="dormitories" aria-labelledby="sit-dorm-title">
	<div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
		<h2 id="sit-dorm-title" class="text-2xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Yataqxanalar', 'studyinturkey' ); ?></h2>
		<a href="<?php echo esc_url( $dorms_url ); ?>" class="text-sm font-semibold text-brand-700 hover:text-brand-600 dark:text-brand-400"><?php esc_html_e( 'Bütün yataqxanalar', 'studyinturkey' ); ?> →</a>
	</div>
	<div class="mt-6 grid gap-4 sm:grid-cols-2">
		<?php
		$n = 0;
		while ( $q->have_posts() && $n < 4 ) :
			$q->the_post();
			get_template_part( 'template-parts/university/part', 'dormitory-card', [ 'dormitory_id' => get_the_ID() ] );
			++$n;
		endwhile;
		wp_reset_postdata();
		?>
	</div>
	<?php if ( $total_dorms > 4 ) : ?>
		<p class="mt-6">
			<a href="<?php echo esc_url( $dorms_url ); ?>" class="font-semibold text-brand-700 hover:text-brand-600 dark:text-brand-400"><?php esc_html_e( 'Hamısına bax', 'studyinturkey' ); ?> →</a>
		</p>
	<?php endif; ?>
</section>
