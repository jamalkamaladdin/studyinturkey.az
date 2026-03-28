<?php
/**
 * Tək yazı (bloq və digər post tipləri üçün əsas şablon).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="flex-1 py-12 lg:py-16">
	<div class="sit-container max-w-3xl">
		<?php
		while ( have_posts() ) :
			the_post();
			$pid   = get_the_ID();
			$title = sit_theme_get_post_title( $pid );
			?>
			<article <?php post_class(); ?>>
				<header class="mb-8">
					<?php if ( is_singular( 'post' ) ) : ?>
						<?php
						$bc_home = sit_theme_localize_url( home_url( '/' ) );
						$bc_blog = sit_theme_blog_index_url();
						$bc_items = [
							[
								'label' => __( 'Ana səhifə', 'studyinturkey' ),
								'url'   => $bc_home,
							],
						];
						if ( untrailingslashit( $bc_blog ) !== untrailingslashit( $bc_home ) ) {
							$bc_items[] = [
								'label' => __( 'Bloq', 'studyinturkey' ),
								'url'   => $bc_blog,
							];
						}
						$bc_items[] = [
							'label' => $title,
							'url'   => '',
						];
						get_template_part(
							'template-parts/page/breadcrumbs',
							null,
							[ 'items' => $bc_items ]
						);
						?>
						<time class="mt-4 block text-sm text-slate-500" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
							<?php echo esc_html( get_the_date() ); ?>
						</time>
						<?php
						$cats = get_the_category( $pid );
						if ( ! empty( $cats ) ) :
							?>
							<p class="mt-2 text-sm text-brand-700">
								<?php
								echo esc_html(
									implode(
										', ',
										array_map(
											static function ( $c ) {
												return $c->name;
											},
											$cats
										)
									)
								);
								?>
							</p>
						<?php endif; ?>
					<?php endif; ?>
					<h1 class="mt-2 text-3xl font-bold text-slate-900 sm:text-4xl"><?php echo esc_html( $title ); ?></h1>
				</header>
				<?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
					<div class="mb-8 overflow-hidden rounded-2xl border border-slate-100">
						<?php the_post_thumbnail( 'large', [ 'class' => 'w-full object-cover' ] ); ?>
					</div>
				<?php endif; ?>
				<div class="sit-entry-content text-slate-700 leading-relaxed">
					<?php
					if ( post_password_required() ) {
						echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						echo sit_theme_get_post_content_filtered( $pid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				</div>
				<?php if ( is_singular( 'post' ) ) : ?>
					<nav class="mt-10 flex justify-between border-t border-slate-200 pt-8 text-sm">
						<?php
						$prev = get_previous_post_link( '%link', '← %title' );
						$next = get_next_post_link( '%link', '%title →' );
						if ( $prev ) {
							echo '<div>' . $prev . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						if ( $next ) {
							echo '<div class="ms-auto">' . $next . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
					</nav>
				<?php endif; ?>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
