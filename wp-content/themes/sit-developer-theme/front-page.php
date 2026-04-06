<?php
/**
 * Ana səhifə — Figma section sırası.
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="flex-1">
<?php
get_template_part( 'template-parts/home/hero' );
get_template_part( 'template-parts/home/why-turkey' );
get_template_part( 'template-parts/home/universities-slider' );
get_template_part( 'template-parts/home/program-offers' );
get_template_part( 'template-parts/home/steps' );
get_template_part( 'template-parts/home/reviews' );
get_template_part( 'template-parts/home/blog' );
get_template_part( 'template-parts/home/why-us' );
?>
</main>
<?php
get_footer();
