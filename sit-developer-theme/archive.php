<?php
/**
 * Bloq arxivi (kateqoriya, teq, tarix, müəllif).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="flex-1 py-12 lg:py-16">
	<div class="sit-container">
		<header class="mb-10 border-b border-slate-200 pb-8">
			<h1 class="text-3xl font-bold text-slate-900"><?php the_archive_title(); ?></h1>
			<?php the_archive_description( '<div class="mt-3 max-w-2xl text-slate-600">', '</div>' ); ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
				<?php
				while ( have_posts() ) :
					the_post();
					$pid   = get_the_ID();
					$title = sit_theme_get_post_title( $pid );
					$link  = sit_theme_localize_url( get_permalink( $pid ) );
					$cats  = get_the_category( $pid );
					?>
					<article <?php post_class( 'group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-brand-200 hover:shadow-md' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php echo esc_url( $link ); ?>" class="block aspect-[16/10] overflow-hidden bg-slate-100">
								<?php the_post_thumbnail( 'medium_large', [ 'class' => 'h-full w-full object-cover transition group-hover:scale-[1.02]' ] ); ?>
							</a>
						<?php endif; ?>
						<div class="flex flex-1 flex-col p-5">
							<?php if ( ! empty( $cats ) ) : ?>
								<p class="text-xs font-medium uppercase tracking-wide text-brand-600">
									<?php echo esc_html( $cats[0]->name ); ?>
								</p>
							<?php endif; ?>
							<h2 class="mt-1 text-lg font-semibold text-slate-900 leading-snug">
								<a href="<?php echo esc_url( $link ); ?>" class="text-inherit hover:text-brand-700"><?php echo esc_html( $title ); ?></a>
							</h2>
							<time class="mt-2 text-xs text-slate-500" datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $pid ) ); ?>"><?php echo esc_html( get_the_date( '', $pid ) ); ?></time>
							<p class="mt-3 line-clamp-3 flex-1 text-sm text-slate-600"><?php echo esc_html( sit_theme_get_post_excerpt( $pid ) ); ?></p>
							<a href="<?php echo esc_url( $link ); ?>" class="mt-4 inline-flex text-sm font-semibold text-brand-700"><?php esc_html_e( 'Oxu', 'studyinturkey' ); ?> →</a>
						</div>
					</article>
					<?php
				endwhile;
				?>
			</div>
			<div class="mt-10 flex justify-center"><?php the_posts_pagination( [ 'mid_size' => 2, 'prev_text' => '← ' . __( 'Əvvəlki', 'studyinturkey' ), 'next_text' => __( 'Növbəti', 'studyinturkey' ) . ' →' ] ); ?></div>
		<?php else : ?>
			<p class="text-slate-600"><?php esc_html_e( 'Bu arxivdə yazı yoxdur.', 'studyinturkey' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
