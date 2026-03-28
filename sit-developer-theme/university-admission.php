<?php
/**
 * Universitet — qəbul tələbləri (tam səhifə).
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$university_id = get_the_ID();
	$title           = sit_theme_get_post_title( $university_id );
	$univ_link       = get_permalink( $university_id );
	$archive_univ    = sit_theme_universities_archive_url();
	$programs_url    = sit_theme_programs_archive_url();
	?>
	<main id="main-content" class="flex-1 py-10 lg:py-14">
		<div class="sit-container max-w-4xl">
			<nav class="text-sm text-slate-500 dark:text-slate-400" aria-label="<?php esc_attr_e( 'Çörək qırıntısı', 'studyinturkey' ); ?>">
				<a href="<?php echo esc_url( sit_theme_localize_url( home_url( '/' ) ) ); ?>" class="hover:text-slate-800 dark:hover:text-slate-200"><?php esc_html_e( 'Ana səhifə', 'studyinturkey' ); ?></a>
				<span class="mx-1.5" aria-hidden="true">/</span>
				<a href="<?php echo esc_url( $archive_univ ); ?>" class="hover:text-slate-800 dark:hover:text-slate-200"><?php esc_html_e( 'Universitetlər', 'studyinturkey' ); ?></a>
				<span class="mx-1.5" aria-hidden="true">/</span>
				<a href="<?php echo esc_url( $univ_link ); ?>" class="hover:text-slate-800 dark:hover:text-slate-200"><?php echo esc_html( $title ); ?></a>
				<span class="mx-1.5" aria-hidden="true">/</span>
				<span class="text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Qəbul tələbləri', 'studyinturkey' ); ?></span>
			</nav>

			<header class="mt-8 border-b border-slate-200 pb-8 dark:border-slate-800">
				<h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl"><?php esc_html_e( 'Qəbul tələbləri', 'studyinturkey' ); ?></h1>
				<p class="mt-3 text-slate-600 dark:text-slate-400"><?php esc_html_e( 'Dərəcə növünə görə sənədlər və addımlar. Proqram seçmək və müraciət üçün aşağıdakı keçiddən istifadə edin.', 'studyinturkey' ); ?></p>
				<p class="mt-4">
					<a href="<?php echo esc_url( $programs_url ); ?>" class="inline-flex min-h-[2.75rem] items-center justify-center rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700"><?php esc_html_e( 'Proqramları kəşf et', 'studyinturkey' ); ?></a>
				</p>
			</header>

			<div class="mt-10">
				<?php
				$req_show = class_exists( 'SIT_University_Admission_Meta', false )
					? SIT_University_Admission_Meta::get_requirements_decoded( $university_id )
					: [];
				$deg_show = sit_theme_filter_admission_degrees_for_display( $university_id, $req_show );
				if ( [] === $deg_show ) {
					echo '<p class="text-slate-600 dark:text-slate-400">' . esc_html__( 'Bu universitet üçün qəbul tələbləri hələ admin panelində doldurulmayıb və ya uyğun proqram dərəcəsi üçün məzmun yoxdur.', 'studyinturkey' ) . '</p>';
				} else {
					get_template_part( 'template-parts/university/part', 'admission-degrees', [ 'university_id' => $university_id, 'show_all' => true ] );
				}
				?>
			</div>

			<p class="mt-12">
				<a href="<?php echo esc_url( $univ_link ); ?>" class="font-semibold text-brand-700 hover:text-brand-600 dark:text-brand-400">← <?php esc_html_e( 'Universitetə qayıt', 'studyinturkey' ); ?></a>
			</p>
		</div>
	</main>
	<?php
endwhile;

get_footer();
