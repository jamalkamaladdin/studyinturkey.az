<?php
/**
 * Son bloq yazıları.
 */

defined( 'ABSPATH' ) || exit;

$q = new WP_Query(
	[
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'ignore_sticky_posts' => true,
	]
);

if ( ! $q->have_posts() ) {
	return;
}

$blog_url = get_post_type_archive_link( 'post' );
$blog_url = $blog_url ? sit_theme_localize_url( $blog_url ) : sit_theme_localize_url( home_url( '/' ) );
?>
<section class="border-t border-slate-100 bg-white py-14 dark:border-slate-800 dark:bg-slate-950 lg:py-16" aria-labelledby="sit-blog-title">
	<div class="sit-container">
		<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
			<div>
				<h2 id="sit-blog-title" class="text-2xl font-bold text-slate-900 dark:text-white sm:text-3xl">
					<?php esc_html_e( 'Bloq', 'studyinturkey' ); ?>
				</h2>
				<p class="mt-2 max-w-xl text-slate-600 dark:text-slate-400">
					<?php esc_html_e( 'Xəbərlər, məsləhətlər və qəbul təqvimi.', 'studyinturkey' ); ?>
				</p>
			</div>
			<a class="shrink-0 text-sm font-semibold text-brand-700 hover:text-brand-600" href="<?php echo esc_url( $blog_url ); ?>">
				<?php esc_html_e( 'Bütün yazılar', 'studyinturkey' ); ?> →
			</a>
		</div>
		<div class="mt-10 grid gap-8 md:grid-cols-3">
			<?php
			while ( $q->have_posts() ) :
				$q->the_post();
				$pid   = get_the_ID();
				$title = sit_theme_get_post_title( $pid );
				$link  = sit_theme_localize_url( get_permalink( $pid ) );
				?>
				<article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/50 shadow-sm transition hover:border-brand-200 hover:bg-white hover:shadow-md dark:border-slate-700 dark:bg-slate-800/50 dark:hover:border-brand-600 dark:hover:bg-slate-800">
					<a href="<?php echo esc_url( $link ); ?>" class="block aspect-[16/10] bg-slate-200 dark:bg-slate-700">
						<?php
						if ( has_post_thumbnail( $pid ) ) {
							the_post_thumbnail(
								'medium_large',
								[
									'class' => 'h-full w-full object-cover',
									'alt'   => esc_attr( $title ),
								]
							);
						}
						?>
					</a>
					<div class="flex flex-1 flex-col p-5">
						<time class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-500" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
							<?php echo esc_html( get_the_date() ); ?>
						</time>
						<h3 class="mt-2 text-lg font-semibold text-slate-900 dark:text-white">
							<a href="<?php echo esc_url( $link ); ?>" class="text-inherit hover:text-brand-700 dark:hover:text-brand-400"><?php echo esc_html( $title ); ?></a>
						</h3>
						<p class="mt-2 line-clamp-3 flex-1 text-sm text-slate-600 dark:text-slate-400">
							<?php echo esc_html( sit_theme_get_post_excerpt( $pid ) ); ?>
						</p>
						<a class="mt-4 inline-flex text-sm font-semibold text-brand-700" href="<?php echo esc_url( $link ); ?>">
							<?php esc_html_e( 'Oxu', 'studyinturkey' ); ?> →
						</a>
					</div>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
