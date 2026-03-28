<?php
/**
 * Statik səhifə.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="flex-1 py-12 lg:py-16">
	<div class="sit-container">
		<?php
		while ( have_posts() ) :
			the_post();
			$pid   = get_the_ID();
			$title = sit_theme_get_post_title( $pid );
			?>
			<article <?php post_class(); ?>>
				<header class="mb-8">
					<h1 class="text-3xl font-bold text-slate-900"><?php echo esc_html( $title ); ?></h1>
				</header>
				<div class="sit-entry-content text-slate-700 leading-relaxed">
					<?php
					if ( post_password_required() ) {
						echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						echo sit_theme_get_post_content_filtered( $pid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</div>
</main>
<?php
get_footer();
