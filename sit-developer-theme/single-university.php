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
			<div class="space-y-14">
				<?php
				get_template_part( 'template-parts/university/section', 'why-choose', [ 'university_id' => $university_id ] );
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
		</div>
	</main>
	<?php
endwhile;

get_footer();
