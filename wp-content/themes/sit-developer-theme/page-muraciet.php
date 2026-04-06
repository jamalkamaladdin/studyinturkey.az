<?php
/**
 * Template Name: Müraciət səhifəsi
 * Slug: muraciet
 *
 * Proqram seçimli müraciət formu — single-program.php stilində.
 */

defined( 'ABSPATH' ) || exit;

get_header();

$archive_prog = sit_theme_programs_archive_url();
?>
<main id="main-content" class="flex-1">
	<!-- Hero -->
	<div class="border-b border-slate-200 bg-gradient-to-b from-slate-50 to-white dark:border-slate-800 dark:from-slate-900 dark:to-slate-950">
		<div class="sit-container py-10 lg:py-14">
			<div class="mx-auto max-w-3xl text-center">
				<div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 dark:bg-brand-900/30">
					<svg class="h-8 w-8 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
				</div>
				<h1 class="text-3xl font-bold text-slate-900 sm:text-4xl dark:text-white"><?php esc_html_e( 'Onlayn müraciət', 'studyinturkey' ); ?></h1>
				<p class="mt-3 text-slate-600 dark:text-slate-400"><?php esc_html_e( 'Aşağıdakı formu doldurun. Dərəcənizə uyğun sənədlər avtomatik tələb olunacaq.', 'studyinturkey' ); ?></p>
				<p class="mt-1.5 text-sm text-slate-500 dark:text-slate-500"><?php esc_html_e( 'Ulduzla işarələnmiş sahələr məcburidir.', 'studyinturkey' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Form -->
	<div class="sit-container py-10 lg:py-14">
		<?php if ( shortcode_exists( 'sit_application_form' ) ) : ?>
			<?php echo do_shortcode( '[sit_application_form]' ); ?>
		<?php else : ?>
			<p class="text-center text-sm text-slate-500"><?php esc_html_e( 'Müraciət formu üçün sit-developer-application plugin aktivləşdirin.', 'studyinturkey' ); ?></p>
		<?php endif; ?>

		<p class="mt-10 text-center">
			<a href="<?php echo esc_url( $archive_prog ); ?>" class="font-semibold text-brand-700 hover:text-brand-600 dark:text-brand-400">← <?php esc_html_e( 'Proqramlar siyahısına qayıt', 'studyinturkey' ); ?></a>
		</p>
	</div>
</main>
<?php
get_footer();
