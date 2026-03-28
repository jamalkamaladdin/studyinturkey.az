<?php
/**
 * Niyə biz?
 */

defined( 'ABSPATH' ) || exit;

$items = [
	[
		'title' => __( 'Çoxdilli məzmun', 'studyinturkey' ),
		'desc'  => __( 'Azərbaycan, ingilis, rus və digər dillərdə eyni keyfiyyətli təqdimat.', 'studyinturkey' ),
	],
	[
		'title' => __( 'Filtr və REST API', 'studyinturkey' ),
		'desc'  => __( 'Proqramları sürətli süzün; məlumatlar strukturlaşdırılıb.', 'studyinturkey' ),
	],
	[
		'title' => __( 'Onlayn müraciət', 'studyinturkey' ),
		'desc'  => __( 'Sənəd yükləmə və namizəd kabineti bir platformada.', 'studyinturkey' ),
	],
	[
		'title' => __( 'Şəffaf məlumat', 'studyinturkey' ),
		'desc'  => __( 'Ödəniş aralığı, kampus və rəylər kimi real göstəricilər.', 'studyinturkey' ),
	],
];
?>
<section class="border-t border-slate-100 bg-white py-14 dark:border-slate-800 dark:bg-slate-950 lg:py-16" aria-labelledby="sit-why-title">
	<div class="sit-container">
		<h2 id="sit-why-title" class="text-center text-2xl font-bold text-slate-900 dark:text-white sm:text-3xl">
			<?php esc_html_e( 'Niyə StudyInTurkey?', 'studyinturkey' ); ?>
		</h2>
		<p class="mx-auto mt-3 max-w-2xl text-center text-slate-600 dark:text-slate-400">
			<?php esc_html_e( 'Namizədlər və universitetlər üçün sadə, təhlükəsiz proses.', 'studyinturkey' ); ?>
		</p>
		<div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
			<?php foreach ( $items as $item ) : ?>
				<div class="rounded-2xl border border-slate-200 bg-gradient-to-b from-white to-slate-50/80 p-6 dark:border-slate-700 dark:from-slate-900 dark:to-slate-800/80">
					<h3 class="text-lg font-semibold text-slate-900 dark:text-white"><?php echo esc_html( $item['title'] ); ?></h3>
					<p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400"><?php echo esc_html( $item['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
