<?php
/**
 * Ana səhifə şablonu (statik və ya son yazılar seçimindən asılı olmayaraq).
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="flex-1">
<?php
get_template_part( 'template-parts/home/hero' );
get_template_part( 'template-parts/home/universities-slider' );
get_template_part( 'template-parts/home/steps' );
get_template_part( 'template-parts/home/why-us' );
get_template_part( 'template-parts/home/reviews' );
get_template_part( 'template-parts/home/blog' );
?>
</main>
<?php
get_footer();
