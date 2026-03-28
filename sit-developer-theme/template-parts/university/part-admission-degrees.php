<?php
/**
 * Dərəcə üzrə qəbul tələbləri (StudyLeo tipli kart görünüşü).
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

$icons = [
	'bachelor'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15v-3.75m0 0-2.25.75L12 16.5l7.5-4.5-2.25-.75" />',
	'master'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21" />',
	'phd'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />',
	'associate' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />',
];
?>
<div class="grid gap-6 sm:grid-cols-2">
	<?php foreach ( $slice as $slug ) : ?>
		<?php
		$blk = isset( $req[ $slug ] ) && is_array( $req[ $slug ] ) ? $req[ $slug ] : [];
		$term = get_term_by( 'slug', $slug, 'degree_type' );
		$name = ( $term instanceof WP_Term && ! is_wp_error( $term ) )
			? sit_theme_get_term_name( (int) $term->term_id, 'degree_type' )
			: $slug;
		$steps = isset( $blk['steps'] ) && is_array( $blk['steps'] ) ? $blk['steps'] : [];
		$docs  = isset( $blk['documents'] ) && is_array( $blk['documents'] ) ? $blk['documents'] : [];
		$icon  = isset( $icons[ $slug ] ) ? $icons[ $slug ] : $icons['bachelor'];
		?>
		<article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
			<div class="flex items-center gap-3">
				<div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-950/60">
					<svg class="h-5 w-5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></svg>
				</div>
				<h3 class="text-lg font-bold text-slate-900 dark:text-white"><?php echo esc_html( $name ); ?></h3>
			</div>

			<?php if ( [] !== $steps ) : ?>
				<div class="mt-4 space-y-2.5">
					<?php foreach ( $steps as $idx => $step_html ) : ?>
						<?php if ( is_string( $step_html ) && '' !== trim( wp_strip_all_tags( $step_html ) ) ) : ?>
							<div class="flex gap-3 text-sm">
								<span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white"><?php echo (int) $idx + 1; ?></span>
								<div class="text-slate-700 leading-relaxed dark:text-slate-300"><?php echo wp_kses_post( $step_html ); ?></div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( [] !== $docs ) : ?>
				<div class="mt-4">
					<p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Tələb olunan sənədlər', 'studyinturkey' ); ?></p>
					<ul class="mt-2 space-y-1.5 text-sm text-slate-700 dark:text-slate-300">
						<?php foreach ( $docs as $line ) : ?>
							<?php if ( is_string( $line ) && '' !== trim( $line ) ) : ?>
								<li class="flex items-start gap-2">
									<svg class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
									<?php echo esc_html( $line ); ?>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php
			$it = isset( $blk['intake_title'] ) ? (string) $blk['intake_title'] : '';
			$is = isset( $blk['intake_start'] ) ? (string) $blk['intake_start'] : '';
			$id = isset( $blk['intake_deadline'] ) ? (string) $blk['intake_deadline'] : '';
			if ( '' !== trim( $it . $is . $id ) ) :
				?>
				<div class="mt-4 rounded-xl bg-brand-50/50 px-4 py-3 text-sm dark:bg-brand-950/30">
					<?php if ( '' !== trim( $it ) ) : ?>
						<p class="font-semibold text-brand-800 dark:text-brand-300"><?php echo esc_html( $it ); ?></p>
					<?php endif; ?>
					<div class="mt-1 flex flex-wrap gap-x-4 gap-y-0.5 text-slate-600 dark:text-slate-400">
						<?php if ( '' !== trim( $is ) ) : ?>
							<span><?php esc_html_e( 'Başlanğıc:', 'studyinturkey' ); ?> <?php echo esc_html( $is ); ?></span>
						<?php endif; ?>
						<?php if ( '' !== trim( $id ) ) : ?>
							<span><?php esc_html_e( 'Son tarix:', 'studyinturkey' ); ?> <?php echo esc_html( $id ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>
</div>
<?php if ( ! $show_all && count( $deg_slugs ) > $limit ) : ?>
	<p class="mt-6 text-center">
		<a href="<?php echo esc_url( $admission_url ); ?>" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
			<?php esc_html_e( 'Bütün qəbul tələbləri', 'studyinturkey' ); ?> &rarr;
		</a>
	</p>
<?php endif; ?>
