<?php
/**
 * Universitet — tək kampus (alt səhifə).
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$university_id = get_the_ID();
	$title           = sit_theme_get_post_title( $university_id );
	$univ_link       = get_permalink( $university_id );
	$archive_univ    = sit_theme_universities_archive_url();
	$campus_slug = (string) get_query_var( 'sit_campus_slug' );
	$campus      = sit_theme_resolve_university_campus( $university_id, $campus_slug );
	// template_redirect artıq yanlış slug üçün 404 verir.
	if ( ! $campus instanceof WP_Post ) {
		get_footer();
		exit;
	}

	$cname  = sit_theme_get_post_title( $campus->ID );
	$addr   = (string) get_post_meta( $campus->ID, 'sit_address', true );
	$lat    = (float) get_post_meta( $campus->ID, 'sit_latitude', true );
	$lng    = (float) get_post_meta( $campus->ID, 'sit_longitude', true );
	$map    = ( 0.0 !== $lat || 0.0 !== $lng )
		? 'https://www.google.com/maps?q=' . rawurlencode( (string) $lat . ',' . (string) $lng )
		: '';
	$content = sit_theme_get_post_content_filtered( $campus->ID );
	$programs_url = sit_theme_programs_archive_url();
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
				<span class="text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Kampus', 'studyinturkey' ); ?></span>
				<span class="mx-1.5" aria-hidden="true">/</span>
				<span class="text-slate-800 dark:text-slate-200"><?php echo esc_html( $cname ); ?></span>
			</nav>

			<header class="mt-8 border-b border-slate-200 pb-8 dark:border-slate-800">
				<h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl"><?php echo esc_html( $cname ); ?></h1>
				<p class="mt-2 text-slate-600 dark:text-slate-400"><?php echo esc_html( $title ); ?></p>
				<?php if ( '' !== $addr ) : ?>
					<p class="mt-4 whitespace-pre-line text-sm text-slate-700 dark:text-slate-300"><?php echo esc_html( $addr ); ?></p>
				<?php endif; ?>
				<div class="mt-6 flex flex-wrap gap-3">
					<?php if ( $map ) : ?>
						<a href="<?php echo esc_url( $map ); ?>" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm hover:border-brand-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white" rel="noopener noreferrer" target="_blank"><?php esc_html_e( 'Xəritədə aç', 'studyinturkey' ); ?></a>
					<?php endif; ?>
					<a href="<?php echo esc_url( $programs_url ); ?>" class="inline-flex items-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700"><?php esc_html_e( 'Proqramları kəşf et', 'studyinturkey' ); ?></a>
				</div>
			</header>

			<?php if ( '' !== trim( wp_strip_all_tags( $content ) ) ) : ?>
				<div class="sit-entry-content mt-10 text-slate-700 leading-relaxed dark:text-slate-300">
					<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>

			<section class="mt-12 rounded-2xl border border-slate-200 bg-slate-50 p-6 dark:border-slate-700 dark:bg-slate-800/40">
				<h2 class="text-lg font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Yaxın yataqxanalar', 'studyinturkey' ); ?></h2>
				<p class="mt-2 text-sm text-slate-600 dark:text-slate-400"><?php esc_html_e( 'Bu universitetə bağlı bütün yataqxanaları siyahıdan görün.', 'studyinturkey' ); ?></p>
				<a href="<?php echo esc_url( sit_theme_university_sub_url( $university_id, 'dormitories' ) ); ?>" class="mt-4 inline-flex font-semibold text-brand-700 hover:text-brand-600 dark:text-brand-400"><?php esc_html_e( 'Yataqxanalar səhifəsi', 'studyinturkey' ); ?> →</a>
			</section>

			<p class="mt-10">
				<a href="<?php echo esc_url( $univ_link ); ?>" class="font-semibold text-brand-700 hover:text-brand-600 dark:text-brand-400">← <?php esc_html_e( 'Universitetə qayıt', 'studyinturkey' ); ?></a>
			</p>
		</div>
	</main>
	<?php
endwhile;

get_footer();
