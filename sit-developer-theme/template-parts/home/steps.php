<?php
/**
 * Addımlar (proses).
 */

defined( 'ABSPATH' ) || exit;

$steps = [
	[
		'title' => __( 'Proqram seçin', 'studyinturkey' ),
		'desc'  => __( 'Dərəcə, dil və şəhər üzrə filtrləyin.', 'studyinturkey' ),
		'icon'  => '1',
	],
	[
		'title' => __( 'Sənədləri hazırlayın', 'studyinturkey' ),
		'desc'  => __( 'Pasport, transkript və şəkil yükləyin.', 'studyinturkey' ),
		'icon'  => '2',
	],
	[
		'title' => __( 'Müraciət edin', 'studyinturkey' ),
		'desc'  => __( 'Formu doldurun, statusu kabinetdə izləyin.', 'studyinturkey' ),
		'icon'  => '3',
	],
	[
		'title' => __( 'Qəbul dəstəyi', 'studyinturkey' ),
		'desc'  => __( 'Komandamız əlaqə saxlayır və yönləndirir.', 'studyinturkey' ),
		'icon'  => '4',
	],
];
?>
<section class="bg-slate-50 py-14 dark:bg-slate-900 lg:py-16" aria-labelledby="sit-steps-title">
	<div class="sit-container">
		<h2 id="sit-steps-title" class="text-center text-2xl font-bold text-slate-900 dark:text-white sm:text-3xl">
			<?php esc_html_e( 'Necə işləyir?', 'studyinturkey' ); ?>
		</h2>
		<p class="mx-auto mt-3 max-w-2xl text-center text-slate-600 dark:text-slate-400">
			<?php esc_html_e( 'Qısa addımlarla Türkiyədə təhsilə yolunuzu planlayın.', 'studyinturkey' ); ?>
		</p>
		<ol class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
			<?php foreach ( $steps as $i => $step ) : ?>
				<li class="relative rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800/80">
					<span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-800 dark:bg-brand-900/60 dark:text-brand-200" aria-hidden="true"><?php echo esc_html( $step['icon'] ); ?></span>
					<h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-white"><?php echo esc_html( $step['title'] ); ?></h3>
					<p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400"><?php echo esc_html( $step['desc'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
