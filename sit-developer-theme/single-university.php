<?php
/**
 * Tək universitet səhifəsi.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$university_id = get_the_ID();
	?>
	<main id="main-content" class="flex-1">
		<?php
		get_template_part( 'template-parts/university/section', 'hero', [ 'university_id' => $university_id ] );
		?>
		<div class="sit-container py-10 lg:py-14">
			<div class="grid gap-12 lg:grid-cols-[minmax(0,1fr)_minmax(0,18rem)] lg:gap-14">
				<div class="min-w-0 space-y-14">
					<?php
					get_template_part( 'template-parts/university/section', 'about', [ 'university_id' => $university_id ] );
					get_template_part( 'template-parts/university/section', 'programs', [ 'university_id' => $university_id ] );
					get_template_part( 'template-parts/university/section', 'admission-requirements', [ 'university_id' => $university_id ] );
					get_template_part( 'template-parts/university/section', 'dormitories', [ 'university_id' => $university_id ] );
					get_template_part( 'template-parts/university/section', 'campus', [ 'university_id' => $university_id ] );
					get_template_part( 'template-parts/university/section', 'international', [ 'university_id' => $university_id ] );
					get_template_part( 'template-parts/university/section', 'scholarships', [ 'university_id' => $university_id ] );
					get_template_part( 'template-parts/university/section', 'faq', [ 'university_id' => $university_id ] );
					get_template_part( 'template-parts/university/section', 'reviews', [ 'university_id' => $university_id ] );
					?>
				</div>
				<aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
					<?php get_template_part( 'template-parts/university/section', 'sidebar', [ 'university_id' => $university_id ] ); ?>
				</aside>
			</div>
		</div>
	</main>
	<?php
endwhile;

get_footer();
