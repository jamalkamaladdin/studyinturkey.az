<?php
/**
 * Sitenin altbilgisi.
 */

defined( 'ABSPATH' ) || exit;

$year = (int) gmdate( 'Y' );
?>
<footer class="mt-auto border-t border-slate-200 bg-slate-50 pb-[env(safe-area-inset-bottom,0)] dark:border-slate-800 dark:bg-slate-900">
	<div class="sit-container py-12 lg:py-14">
		<div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
			<div class="lg:col-span-1">
				<p class="font-semibold text-slate-900 dark:text-white"><?php bloginfo( 'name' ); ?></p>
				<p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
					<?php echo esc_html( get_bloginfo( 'description', 'display' ) ?: __( 'Türkiyədə təhsil üçün universitet və proqram seçimi.', 'studyinturkey' ) ); ?>
				</p>
			</div>
			<div>
				<p class="text-sm font-semibold text-slate-900 dark:text-white"><?php esc_html_e( 'Keçidlər', 'studyinturkey' ); ?></p>
				<ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
					<li><a href="<?php echo esc_url( sit_theme_universities_archive_url() ); ?>"><?php esc_html_e( 'Universitetlər', 'studyinturkey' ); ?></a></li>
					<li><a href="<?php echo esc_url( sit_theme_programs_archive_url() ); ?>"><?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></a></li>
					<li><a href="<?php echo esc_url( sit_theme_localize_url( get_post_type_archive_link( 'post' ) ?: home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Bloq', 'studyinturkey' ); ?></a></li>
				</ul>
			</div>
			<div>
				<p class="text-sm font-semibold text-slate-900 dark:text-white"><?php esc_html_e( 'Əlaqə', 'studyinturkey' ); ?></p>
				<ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
					<?php if ( get_option( 'admin_email' ) ) : ?>
						<li>
							<a href="mailto:<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"><?php echo esc_html( get_option( 'admin_email' ) ); ?></a>
						</li>
					<?php endif; ?>
					<li><a href="<?php echo esc_url( sit_theme_localize_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Ana səhifə', 'studyinturkey' ); ?></a></li>
				</ul>
			</div>
			<div>
				<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
					<?php dynamic_sidebar( 'footer-1' ); ?>
				<?php else : ?>
					<p class="text-sm font-semibold text-slate-900 dark:text-white"><?php esc_html_e( 'Müraciət', 'studyinturkey' ); ?></p>
					<p class="mt-3 text-sm text-slate-600 dark:text-slate-400">
						<?php esc_html_e( 'Namizəd qeydiyyatı və kabinet üçün səhifələrdə application shortcode-larından istifadə edin.', 'studyinturkey' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<div class="mt-10 flex flex-col gap-2 border-t border-slate-200 pt-8 text-center text-xs text-slate-500 dark:border-slate-800 dark:text-slate-500 sm:flex-row sm:justify-between sm:text-start">
			<p>
				<?php
				printf(
					/* translators: %s: year */
					esc_html__( '© %s %s. Bütün hüquqlar qorunur.', 'studyinturkey' ),
					esc_html( (string) $year ),
					esc_html( get_bloginfo( 'name', 'display' ) )
				);
				?>
			</p>
			<div class="flex justify-center sm:ms-auto sm:justify-end">
				<?php
				wp_nav_menu(
					[
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'flex flex-wrap justify-center gap-x-4 gap-y-1 sm:justify-end',
						'fallback_cb'    => '__return_false',
						'depth'          => 1,
					]
				);
				?>
			</div>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
