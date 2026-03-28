<?php
/**
 * Dərəcə üzrə qəbul tələbləri (StudyLeo tipli).
 *
 * @var int  $university_id
 * @var bool $show_all      Əsas səhifədə hamısını göstər; false olanda qısa + keçid.
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
$show_all      = isset( $show_all ) ? (bool) $show_all : true;

if ( $university_id < 1 || ! class_exists( 'SIT_University_Admission_Meta', false ) ) {
	return;
}

$req        = SIT_University_Admission_Meta::get_requirements_decoded( $university_id );
$deg_slugs  = sit_theme_filter_admission_degrees_for_display( $university_id, $req );
$admission_url = sit_theme_university_sub_url( $university_id, 'admission' );

if ( [] === $deg_slugs ) {
	return;
}

$limit = $show_all ? 999 : 2;
$slice = array_slice( $deg_slugs, 0, $limit );
?>
<div class="space-y-10">
	<?php foreach ( $slice as $slug ) : ?>
		<?php
		$blk = isset( $req[ $slug ] ) && is_array( $req[ $slug ] ) ? $req[ $slug ] : [];
		$term = get_term_by( 'slug', $slug, 'degree_type' );
		$name = ( $term instanceof WP_Term && ! is_wp_error( $term ) )
			? sit_theme_get_term_name( (int) $term->term_id, 'degree_type' )
			: $slug;
		$steps = isset( $blk['steps'] ) && is_array( $blk['steps'] ) ? $blk['steps'] : [];
		$docs  = isset( $blk['documents'] ) && is_array( $blk['documents'] ) ? $blk['documents'] : [];
		?>
		<article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
			<h3 class="text-lg font-bold text-slate-900 dark:text-white"><?php echo esc_html( $name ); ?></h3>
			<?php if ( [] !== $steps ) : ?>
				<ol class="mt-4 list-decimal space-y-3 ps-5 text-sm text-slate-700 dark:text-slate-300">
					<?php foreach ( $steps as $step_html ) : ?>
						<?php if ( is_string( $step_html ) && '' !== trim( wp_strip_all_tags( $step_html ) ) ) : ?>
							<li class="leading-relaxed"><?php echo wp_kses_post( $step_html ); ?></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
			<?php if ( [] !== $docs ) : ?>
				<ul class="mt-4 list-disc space-y-1.5 ps-5 text-sm text-slate-700 dark:text-slate-300">
					<?php foreach ( $docs as $line ) : ?>
						<?php if ( is_string( $line ) && '' !== trim( $line ) ) : ?>
							<li><?php echo esc_html( $line ); ?></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<?php
			$it = isset( $blk['intake_title'] ) ? (string) $blk['intake_title'] : '';
			$is = isset( $blk['intake_start'] ) ? (string) $blk['intake_start'] : '';
			$id = isset( $blk['intake_deadline'] ) ? (string) $blk['intake_deadline'] : '';
			if ( '' !== trim( $it . $is . $id ) ) :
				?>
				<div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm dark:bg-slate-800/80">
					<?php if ( '' !== trim( $it ) ) : ?>
						<p class="font-semibold text-slate-900 dark:text-white"><?php echo esc_html( $it ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== trim( $is ) ) : ?>
						<p class="mt-1 text-slate-600 dark:text-slate-400">
							<?php esc_html_e( 'Başlanğıc:', 'studyinturkey' ); ?>
							<?php echo esc_html( $is ); ?>
						</p>
					<?php endif; ?>
					<?php if ( '' !== trim( $id ) ) : ?>
						<p class="mt-0.5 text-slate-600 dark:text-slate-400">
							<?php esc_html_e( 'Son tarix:', 'studyinturkey' ); ?>
							<?php echo esc_html( $id ); ?>
						</p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>
</div>
<?php if ( ! $show_all && count( $deg_slugs ) > $limit ) : ?>
	<p class="mt-6">
		<a href="<?php echo esc_url( $admission_url ); ?>" class="inline-flex font-semibold text-brand-700 hover:text-brand-600 dark:text-brand-400">
			<?php esc_html_e( 'Bütün qəbul tələbləri', 'studyinturkey' ); ?> →
		</a>
	</p>
<?php endif; ?>
