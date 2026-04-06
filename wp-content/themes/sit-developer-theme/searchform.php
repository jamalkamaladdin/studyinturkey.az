<?php
/**
 * Axtarış formu.
 */

defined( 'ABSPATH' ) || exit;

$unique_id = wp_unique_id( 'sit-search-' );
?>
<form role="search" method="get" class="flex w-full gap-2" action="<?php echo esc_url( sit_theme_localize_url( home_url( '/' ) ) ); ?>">
	<label for="<?php echo esc_attr( $unique_id ); ?>" class="sr-only"><?php echo esc_html_x( 'Axtarış', 'label', 'studyinturkey' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $unique_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" class="sit-form-input min-w-0 flex-1 rounded-xl px-4 py-2.5 text-sm shadow-sm" placeholder="<?php esc_attr_e( 'Axtar…', 'studyinturkey' ); ?>">
	<button type="submit" class="min-h-[2.75rem] shrink-0 touch-manipulation rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 dark:hover:bg-brand-500"><?php esc_html_e( 'Axtar', 'studyinturkey' ); ?></button>
</form>
