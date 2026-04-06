<?php
/**
 * Universitet — bütün yataqxanalar (alt səhifə).
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$university_id = get_the_ID();
	$title           = sit_theme_get_post_title( $university_id );
	$univ_link       = get_permalink( $university_id );
	$archive_univ    = sit_theme_universities_archive_url();
	?>
	<main id="main-content" class="flex-1 py-10 lg:py-14">
		<div class="sit-container">
			<nav class="text-sm text-slate-500 dark:text-slate-400" aria-label="<?php esc_attr_e( 'Çörək qırıntısı', 'studyinturkey' ); ?>">
				<a href="<?php echo esc_url( sit_theme_localize_url( home_url( '/' ) ) ); ?>" class="hover:text-slate-800 dark:hover:text-slate-200"><?php esc_html_e( 'Ana səhifə', 'studyinturkey' ); ?></a>
				<span class="mx-1.5" aria-hidden="true">/</span>
				<a href="<?php echo esc_url( $archive_univ ); ?>" class="hover:text-slate-800 dark:hover:text-slate-200"><?php esc_html_e( 'Universitetlər', 'studyinturkey' ); ?></a>
				<span class="mx-1.5" aria-hidden="true">/</span>
				<a href="<?php echo esc_url( $univ_link ); ?>" class="hover:text-slate-800 dark:hover:text-slate-200"><?php echo esc_html( $title ); ?></a>
				<span class="mx-1.5" aria-hidden="true">/</span>
				<span class="text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Yataqxanalar', 'studyinturkey' ); ?></span>
			</nav>

			<header class="mt-8 border-b border-slate-200 pb-8 dark:border-slate-800">
				<h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl"><?php esc_html_e( 'Yaxın yataqxanalar', 'studyinturkey' ); ?></h1>
				<p class="mt-2 max-w-2xl text-slate-600 dark:text-slate-400"><?php echo esc_html( $title ); ?></p>
			</header>

			<div class="mt-10">
				<?php
				if ( ! post_type_exists( 'dormitory' ) ) {
					echo '<p class="text-slate-500">' . esc_html__( 'Yataqxana tipi mövcud deyil.', 'studyinturkey' ) . '</p>';
				} else {
					$q = sit_theme_query_posts_by_university( 'dormitory', $university_id );
					if ( ! $q->have_posts() ) {
						echo '<p class="text-slate-500">' . esc_html__( 'Hələ yataqxana əlavə edilməyib.', 'studyinturkey' ) . '</p>';
					} else {
						?>
						<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
							<?php
							while ( $q->have_posts() ) :
								$q->the_post();
								get_template_part( 'template-parts/university/part', 'dormitory-card', [ 'dormitory_id' => get_the_ID() ] );
							endwhile;
							wp_reset_postdata();
							?>
						</div>
						<?php
					}
				}
				?>
			</div>

			<p class="mt-10">
				<a href="<?php echo esc_url( $univ_link ); ?>" class="font-semibold text-brand-700 hover:text-brand-600 dark:text-brand-400">← <?php esc_html_e( 'Universitetə qayıt', 'studyinturkey' ); ?></a>
			</p>
		</div>
	</main>
	<?php
endwhile;

get_footer();
