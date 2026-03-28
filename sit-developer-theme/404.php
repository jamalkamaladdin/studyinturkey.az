<?php
/**
 * 404 səhifəsi.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="flex-1 py-16 lg:py-24">
	<div class="sit-container text-center">
		<p class="text-8xl font-black text-brand-100 dark:text-brand-900/40 sm:text-9xl" aria-hidden="true">404</p>
		<h1 class="mt-4 text-2xl font-bold text-slate-900 dark:text-white sm:text-3xl"><?php esc_html_e( 'Səhifə tapılmadı', 'studyinturkey' ); ?></h1>
		<p class="mx-auto mt-3 max-w-md text-slate-600 dark:text-slate-400"><?php esc_html_e( 'Ünvanı yoxlayın və ya axtarışdan istifadə edin.', 'studyinturkey' ); ?></p>
		<div class="mx-auto mt-8 max-w-md">
			<?php get_search_form(); ?>
		</div>
		<div class="mt-10 flex flex-wrap justify-center gap-3 text-sm font-medium">
			<a href="<?php echo esc_url( sit_theme_localize_url( home_url( '/' ) ) ); ?>" class="min-h-[2.75rem] touch-manipulation rounded-xl bg-brand-600 px-5 py-2.5 text-white shadow-sm hover:bg-brand-700 dark:hover:bg-brand-500"><?php esc_html_e( 'Ana səhifə', 'studyinturkey' ); ?></a>
			<a href="<?php echo esc_url( sit_theme_universities_archive_url() ); ?>" class="min-h-[2.75rem] touch-manipulation rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"><?php esc_html_e( 'Universitetlər', 'studyinturkey' ); ?></a>
			<a href="<?php echo esc_url( sit_theme_programs_archive_url() ); ?>" class="min-h-[2.75rem] touch-manipulation rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"><?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></a>
		</div>
	</div>
</main>
<?php
get_footer();
