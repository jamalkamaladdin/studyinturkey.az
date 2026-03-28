<?php
/**
 * Əsas şablon (arxiv, axtarış və s.).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="flex-1 py-12 lg:py-16">
	<div class="sit-container">
		<?php if ( have_posts() ) : ?>
			<header class="mb-10">
				<h1 class="text-3xl font-bold text-slate-900"><?php the_archive_title(); ?></h1>
				<?php the_archive_description( '<div class="mt-3 max-w-2xl text-slate-600">', '</div>' ); ?>
			</header>
			<div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
				<?php
				while ( have_posts() ) :
					the_post();
					$pid   = get_the_ID();
					$title = sit_theme_get_post_title( $pid );
					$link  = sit_theme_localize_url( get_permalink( $pid ) );
					?>
					<article <?php post_class( 'rounded-2xl border border-slate-200 bg-white p-6 shadow-sm' ); ?>>
						<h2 class="text-lg font-semibold text-slate-900">
							<a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
						</h2>
						<p class="mt-2 text-sm text-slate-600"><?php echo esc_html( sit_theme_get_post_excerpt( $pid ) ); ?></p>
					</article>
					<?php
				endwhile;
				?>
			</div>
			<div class="mt-10">
				<?php the_posts_pagination( [ 'mid_size' => 2 ] ); ?>
			</div>
		<?php else : ?>
			<p class="text-slate-600"><?php esc_html_e( 'Heç bir məzmun tapılmadı.', 'studyinturkey' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
