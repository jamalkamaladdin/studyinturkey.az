<?php
/**
 * Template Name: SIT — Haqqımızda
 * Template Post Type: page
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pid   = get_the_ID();
	$title = sit_theme_get_post_title( $pid );
	?>
	<main id="main-content" class="flex-1">
		<div class="border-b border-slate-200 bg-gradient-to-br from-brand-900 via-brand-800 to-teal-900 py-14 text-white lg:py-16">
			<div class="sit-container">
				<?php
				get_template_part(
					'template-parts/page/breadcrumbs',
					null,
					[
						'items' => [
							[
								'label' => __( 'Ana səhifə', 'studyinturkey' ),
								'url'   => sit_theme_localize_url( home_url( '/' ) ),
							],
							[
								'label' => $title,
								'url'   => '',
							],
						],
					]
				);
				?>
				<h1 class="mt-6 text-3xl font-bold tracking-tight sm:text-4xl"><?php echo esc_html( $title ); ?></h1>
				<?php
				$hero_excerpt = sit_theme_get_post_excerpt( $pid );
				if ( '' !== $hero_excerpt ) :
					?>
					<p class="mt-3 max-w-2xl text-lg text-brand-100"><?php echo esc_html( $hero_excerpt ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<div class="sit-container py-12 lg:py-16">
			<div class="mx-auto max-w-3xl">
				<div class="sit-entry-content text-slate-700 leading-relaxed">
					<?php echo sit_theme_get_post_content_filtered( $pid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>
	</main>
	<?php
endwhile;

get_footer();
