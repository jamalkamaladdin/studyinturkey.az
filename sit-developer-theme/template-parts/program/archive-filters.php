<?php
/**
 * Proqram arxivi — filtr formu.
 */

defined( 'ABSPATH' ) || exit;

$action = sit_theme_programs_archive_url();

$cur_degree = isset( $_GET['sit_degree'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['sit_degree'] ) ) : '';
$cur_lang   = isset( $_GET['sit_language'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['sit_language'] ) ) : '';
$cur_field  = isset( $_GET['sit_field'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['sit_field'] ) ) : '';
$cur_city   = isset( $_GET['sit_city'] ) ? sanitize_title( wp_unslash( (string) $_GET['sit_city'] ) ) : '';
$cur_pmin   = isset( $_GET['sit_price_min'] ) && is_numeric( $_GET['sit_price_min'] ) ? (string) (float) wp_unslash( $_GET['sit_price_min'] ) : '';
$cur_pmax   = isset( $_GET['sit_price_max'] ) && is_numeric( $_GET['sit_price_max'] ) ? (string) (float) wp_unslash( $_GET['sit_price_max'] ) : '';
$cur_sort   = isset( $_GET['sit_sort'] ) ? sanitize_key( wp_unslash( (string) $_GET['sit_sort'] ) ) : 'date_desc';
$cur_univ   = isset( $_GET['sit_university'] ) ? absint( wp_unslash( $_GET['sit_university'] ) ) : 0;

$degrees   = taxonomy_exists( 'degree_type' ) ? get_terms( [ 'taxonomy' => 'degree_type', 'hide_empty' => false ] ) : [];
$prog_langs = taxonomy_exists( 'program_language' ) ? get_terms( [ 'taxonomy' => 'program_language', 'hide_empty' => false ] ) : [];
$fields    = taxonomy_exists( 'field_of_study' ) ? get_terms( [ 'taxonomy' => 'field_of_study', 'hide_empty' => false ] ) : [];
$cities    = taxonomy_exists( 'city' ) ? get_terms( [ 'taxonomy' => 'city', 'hide_empty' => true ] ) : [];

if ( is_wp_error( $degrees ) ) {
	$degrees = [];
}
if ( is_wp_error( $prog_langs ) ) {
	$prog_langs = [];
}
if ( is_wp_error( $fields ) ) {
	$fields = [];
}
if ( is_wp_error( $cities ) ) {
	$cities = [];
}

$universities = get_posts(
	[
		'post_type'              => 'university',
		'post_status'            => 'publish',
		'posts_per_page'         => 400,
		'orderby'                => 'title',
		'order'                  => 'ASC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
	]
);
?>
<aside class="lg:sticky lg:top-24 lg:self-start" aria-labelledby="sit-prog-filters-title">
	<div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800/80">
		<h2 id="sit-prog-filters-title" class="text-base font-semibold text-slate-900 dark:text-white"><?php esc_html_e( 'Filtrlər', 'studyinturkey' ); ?></h2>
		<form id="sit-prog-filters" class="mt-4 space-y-4" method="get" action="<?php echo esc_url( $action ); ?>" data-sit-prog-form>
			<?php if ( ! empty( $universities ) ) : ?>
				<div>
					<label for="sit_university" class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Universitet', 'studyinturkey' ); ?></label>
					<select name="sit_university" id="sit_university" class="sit-form-input mt-1 w-full px-3 py-2 text-sm text-slate-800 dark:text-slate-100">
						<option value=""><?php esc_html_e( 'Hamısı', 'studyinturkey' ); ?></option>
						<?php foreach ( $universities as $u ) : ?>
							<option value="<?php echo esc_attr( (string) $u->ID ); ?>" <?php selected( $cur_univ, (int) $u->ID ); ?>><?php echo esc_html( sit_theme_get_post_title( (int) $u->ID ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
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
			<?php if ( ! empty( $degrees ) ) : ?>
				<div>
					<label for="sit_degree" class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Dərəcə', 'studyinturkey' ); ?></label>
					<select name="sit_degree" id="sit_degree" class="sit-form-input mt-1 w-full px-3 py-2 text-sm text-slate-800 dark:text-slate-100">
						<option value=""><?php esc_html_e( 'Hamısı', 'studyinturkey' ); ?></option>
						<?php foreach ( $degrees as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $cur_degree, $term->slug ); ?>><?php echo esc_html( sit_theme_get_term_name( (int) $term->term_id, 'degree_type' ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $prog_langs ) ) : ?>
				<div>
					<label for="sit_language" class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Proqram dili', 'studyinturkey' ); ?></label>
					<select name="sit_language" id="sit_language" class="sit-form-input mt-1 w-full px-3 py-2 text-sm text-slate-800 dark:text-slate-100">
						<option value=""><?php esc_html_e( 'Hamısı', 'studyinturkey' ); ?></option>
						<?php foreach ( $prog_langs as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $cur_lang, $term->slug ); ?>><?php echo esc_html( sit_theme_get_term_name( (int) $term->term_id, 'program_language' ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $fields ) ) : ?>
				<div>
					<label for="sit_field" class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'İxtisas sahəsi', 'studyinturkey' ); ?></label>
					<select name="sit_field" id="sit_field" class="sit-form-input mt-1 w-full px-3 py-2 text-sm text-slate-800 dark:text-slate-100">
						<option value=""><?php esc_html_e( 'Hamısı', 'studyinturkey' ); ?></option>
						<?php foreach ( $fields as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $cur_field, $term->slug ); ?>><?php echo esc_html( sit_theme_get_term_name( (int) $term->term_id, 'field_of_study' ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
			<div class="grid grid-cols-2 gap-2">
				<div>
					<label for="sit_price_min" class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Qiymət min', 'studyinturkey' ); ?></label>
					<input type="number" min="0" step="1" name="sit_price_min" id="sit_price_min" value="<?php echo esc_attr( $cur_pmin ); ?>" class="sit-form-input mt-1 w-full px-3 py-2 text-sm text-slate-800 dark:text-slate-100">
				</div>
				<div>
					<label for="sit_price_max" class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Qiymət max', 'studyinturkey' ); ?></label>
					<input type="number" min="0" step="1" name="sit_price_max" id="sit_price_max" value="<?php echo esc_attr( $cur_pmax ); ?>" class="sit-form-input mt-1 w-full px-3 py-2 text-sm text-slate-800 dark:text-slate-100">
				</div>
			</div>
			<div>
				<label for="sit_sort" class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Sıralama', 'studyinturkey' ); ?></label>
				<select name="sit_sort" id="sit_sort" class="sit-form-input mt-1 w-full px-3 py-2 text-sm text-slate-800 dark:text-slate-100">
					<option value="date_desc" <?php selected( $cur_sort, 'date_desc' ); ?>><?php esc_html_e( 'Ən yeni', 'studyinturkey' ); ?></option>
					<option value="date_asc" <?php selected( $cur_sort, 'date_asc' ); ?>><?php esc_html_e( 'Ən köhnə', 'studyinturkey' ); ?></option>
					<option value="price_asc" <?php selected( $cur_sort, 'price_asc' ); ?>><?php esc_html_e( 'Qiymət: aşağıdan yuxarı', 'studyinturkey' ); ?></option>
					<option value="price_desc" <?php selected( $cur_sort, 'price_desc' ); ?>><?php esc_html_e( 'Qiymət: yuxarıdan aşağı', 'studyinturkey' ); ?></option>
					<option value="title_asc" <?php selected( $cur_sort, 'title_asc' ); ?>><?php esc_html_e( 'Ad (A–Z)', 'studyinturkey' ); ?></option>
					<option value="title_desc" <?php selected( $cur_sort, 'title_desc' ); ?>><?php esc_html_e( 'Ad (Z–A)', 'studyinturkey' ); ?></option>
				</select>
			</div>
			<div class="flex flex-col gap-2 sm:flex-row">
				<button type="submit" class="inline-flex min-h-[2.75rem] flex-1 touch-manipulation items-center justify-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 dark:hover:bg-brand-500"><?php esc_html_e( 'Tətbiq et', 'studyinturkey' ); ?></button>
				<a href="<?php echo esc_url( $action ); ?>" class="inline-flex min-h-[2.75rem] flex-1 touch-manipulation items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"><?php esc_html_e( 'Sıfırla', 'studyinturkey' ); ?></a>
			</div>
		</form>
	</div>
</aside>
