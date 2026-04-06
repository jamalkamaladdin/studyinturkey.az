<?php
/**
 * Axtarış nəticələri.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="flex-1 py-12 lg:py-16">
	<div class="sit-container">
		<header class="mb-10 border-b border-slate-200 pb-8">
			<h1 class="text-3xl font-bold text-slate-900">
				<?php
				printf(
					/* translators: %s: search query */
					esc_html__( 'Axtarış: %s', 'studyinturkey' ),
					esc_html( get_search_query() )
				);
				?>
			</h1>
		</header>
		<?php if ( have_posts() ) : ?>
			<ul class="space-y-4">
				<?php
				while ( have_posts() ) :
					the_post();
					$pid   = get_the_ID();
					$title = sit_theme_get_post_title( $pid );
					$link  = sit_theme_localize_url( get_permalink( $pid ) );
					?>
					<li class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
						<a href="<?php echo esc_url( $link ); ?>" class="text-lg font-semibold text-brand-700 hover:text-brand-600"><?php echo esc_html( $title ); ?></a>
						<?php
						$pto = get_post_type_object( get_post_type() );
						$ptl = $pto && isset( $pto->labels->singular_name ) ? $pto->labels->singular_name : '';
						?>
						<p class="mt-1 text-xs uppercase tracking-wide text-slate-500"><?php echo esc_html( $ptl ); ?></p>
						<p class="mt-2 line-clamp-2 text-sm text-slate-600"><?php echo esc_html( sit_theme_get_post_excerpt( $pid ) ); ?></p>
					</li>
					<?php
				endwhile;
				?>
			</ul>
			<div class="mt-10 flex justify-center"><?php the_posts_pagination( [ 'mid_size' => 2, 'prev_text' => '← ' . __( 'Əvvəlki', 'studyinturkey' ), 'next_text' => __( 'Növbəti', 'studyinturkey' ) . ' →' ] ); ?></div>
		<?php else : ?>
			<p class="text-slate-600"><?php esc_html_e( 'Heç bir nəticə tapılmadı.', 'studyinturkey' ); ?></p>
			<div class="mx-auto mt-6 max-w-md"><?php get_search_form(); ?></div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
