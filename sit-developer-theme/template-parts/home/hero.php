<?php
/**
 * Hero bölməsi.
 */

defined( 'ABSPATH' ) || exit;

$univ_url = sit_theme_universities_archive_url();
$prog_url = sit_theme_programs_archive_url();
?>
<section class="relative overflow-hidden bg-gradient-to-br from-brand-900 via-brand-700 to-teal-600 text-white" aria-labelledby="sit-hero-heading">
	<div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, #fff 0%, transparent 45%), radial-gradient(circle at 80% 60%, #ccfbf1 0%, transparent 40%);"></div>
	<div class="sit-container relative py-16 sm:py-20 lg:py-28">
		<div class="max-w-3xl">
			<p class="text-sm font-medium uppercase tracking-wider text-brand-100">
				<?php esc_html_e( 'Türkiyədə təhsil', 'studyinturkey' ); ?>
			</p>
			<h1 id="sit-hero-heading" class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl lg:text-5xl">
				<?php esc_html_e( 'Universitet və proqram seçiminizi bir yerdə edin', 'studyinturkey' ); ?>
			</h1>
			<p class="mt-5 text-lg text-brand-50 leading-relaxed sm:text-xl">
				<?php esc_html_e( 'Filtrasiya, müraciət və çoxdilli məzmun ilə namizədlərə aydın yol xəritəsi təqdim edirik.', 'studyinturkey' ); ?>
			</p>
			<div class="mt-8 flex flex-wrap gap-3">
				<a class="inline-flex min-h-[2.75rem] touch-manipulation items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-semibold text-brand-800 shadow-lg hover:bg-brand-50 dark:bg-slate-100 dark:text-brand-900 dark:hover:bg-white" href="<?php echo esc_url( $prog_url ); ?>">
					<?php esc_html_e( 'Proqramlara bax', 'studyinturkey' ); ?>
				</a>
				<a class="inline-flex min-h-[2.75rem] touch-manipulation items-center justify-center rounded-xl border border-white/40 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur hover:bg-white/20" href="<?php echo esc_url( $univ_url ); ?>">
					<?php esc_html_e( 'Universitetlər', 'studyinturkey' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>
