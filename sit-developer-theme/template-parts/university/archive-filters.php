<?php
/**
 * Universitet arxivi — yan panel filtrləri.
 */

defined( 'ABSPATH' ) || exit;

if ( ! taxonomy_exists( 'city' ) && ! taxonomy_exists( 'university_type' ) ) {
	return;
}

$action = sit_theme_universities_archive_url();
$cur_city = isset( $_GET['sit_city'] ) ? sanitize_title( wp_unslash( (string) $_GET['sit_city'] ) ) : '';
$cur_type = isset( $_GET['sit_type'] ) ? sanitize_title( wp_unslash( (string) $_GET['sit_type'] ) ) : '';
$cur_q    = isset( $_GET['sit_q'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['sit_q'] ) ) : '';

$cities = taxonomy_exists( 'city' ) ? get_terms( [ 'taxonomy' => 'city', 'hide_empty' => true ] ) : [];
$types  = taxonomy_exists( 'university_type' ) ? get_terms( [ 'taxonomy' => 'university_type', 'hide_empty' => true ] ) : [];

if ( is_wp_error( $cities ) ) {
	$cities = [];
}
if ( is_wp_error( $types ) ) {
	$types = [];
}
?>
<aside class="lg:sticky lg:top-24 lg:self-start" aria-labelledby="sit-univ-filters-title">
	<div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800/80">
		<h2 id="sit-univ-filters-title" class="text-base font-semibold text-slate-900 dark:text-white">
			<?php esc_html_e( 'Filtrlər', 'studyinturkey' ); ?>
		</h2>
		<form class="mt-4 space-y-4" method="get" action="<?php echo esc_url( $action ); ?>">
			<div>
				<label for="sit_q" class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Axtarış', 'studyinturkey' ); ?></label>
				<input type="search" name="sit_q" id="sit_q" value="<?php echo esc_attr( $cur_q ); ?>" class="sit-form-input mt-1 w-full px-3 py-2 text-sm text-slate-800 dark:text-slate-100" placeholder="<?php esc_attr_e( 'Universitet adı…', 'studyinturkey' ); ?>">
			</div>
			<?php if ( ! empty( $cities ) ) : ?>
				<div>
					<label for="sit_city" class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Şəhər', 'studyinturkey' ); ?></label>
					<select name="sit_city" id="sit_city" class="sit-form-input mt-1 w-full px-3 py-2 text-sm text-slate-800 dark:text-slate-100">
						<option value=""><?php esc_html_e( 'Hamısı', 'studyinturkey' ); ?></option>
						<?php foreach ( $cities as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $cur_city, $term->slug ); ?>><?php echo esc_html( sit_theme_get_term_name( (int) $term->term_id, 'city' ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $types ) ) : ?>
				<div>
					<label for="sit_type" class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Növ', 'studyinturkey' ); ?></label>
					<select name="sit_type" id="sit_type" class="sit-form-input mt-1 w-full px-3 py-2 text-sm text-slate-800 dark:text-slate-100">
						<option value=""><?php esc_html_e( 'Hamısı', 'studyinturkey' ); ?></option>
						<?php foreach ( $types as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $cur_type, $term->slug ); ?>><?php echo esc_html( sit_theme_get_term_name( (int) $term->term_id, 'university_type' ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
			<div class="flex flex-col gap-2 sm:flex-row">
				<button type="submit" class="inline-flex min-h-[2.75rem] flex-1 touch-manipulation items-center justify-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 dark:hover:bg-brand-500">
					<?php esc_html_e( 'Tətbiq et', 'studyinturkey' ); ?>
				</button>
				<a href="<?php echo esc_url( $action ); ?>" class="inline-flex min-h-[2.75rem] flex-1 touch-manipulation items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
					<?php esc_html_e( 'Sıfırla', 'studyinturkey' ); ?>
				</a>
			</div>
		</form>
	</div>
</aside>
