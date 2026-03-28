<?php
/**
 * Tək proqram səhifəsi.
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();
	$title   = sit_theme_get_post_title( $post_id );
	$fee     = get_post_meta( $post_id, 'sit_tuition_fee', true );
	$dur     = (string) get_post_meta( $post_id, 'sit_duration', true );
	$sch     = (bool) get_post_meta( $post_id, 'sit_scholarship_available', true );
	$uid     = (int) get_post_meta( $post_id, 'sit_university_id', true );

	$univ_title = '';
	$univ_link  = '';
	if ( $uid > 0 ) {
		$univ_title = sit_theme_get_post_title( $uid );
		$univ_link  = sit_theme_localize_url( get_permalink( $uid ) );
	}

	$archive_prog = sit_theme_programs_archive_url();
	?>
	<main id="main-content" class="flex-1">
		<div class="border-b border-slate-200 bg-gradient-to-b from-slate-50 to-white">
			<div class="sit-container py-10 lg:py-12">
				<nav class="text-sm text-slate-500" aria-label="<?php esc_attr_e( 'Çörək qırıntısı', 'studyinturkey' ); ?>">
					<a href="<?php echo esc_url( sit_theme_localize_url( home_url( '/' ) ) ); ?>" class="hover:text-slate-800"><?php esc_html_e( 'Ana səhifə', 'studyinturkey' ); ?></a>
					<span class="mx-1.5" aria-hidden="true">/</span>
					<a href="<?php echo esc_url( $archive_prog ); ?>" class="hover:text-slate-800"><?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></a>
					<?php if ( $univ_link ) : ?>
						<span class="mx-1.5" aria-hidden="true">/</span>
						<a href="<?php echo esc_url( $univ_link ); ?>" class="hover:text-slate-800"><?php echo esc_html( $univ_title ); ?></a>
					<?php endif; ?>
				</nav>
				<div class="mt-6 flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
					<div class="max-w-3xl">
						<h1 class="text-3xl font-bold text-slate-900 sm:text-4xl"><?php echo esc_html( $title ); ?></h1>
						<dl class="mt-6 flex flex-wrap gap-x-6 gap-y-3 text-sm">
							<?php if ( is_numeric( $fee ) && (float) $fee > 0 ) : ?>
								<div>
									<dt class="text-slate-500"><?php esc_html_e( 'Ödəniş', 'studyinturkey' ); ?></dt>
									<dd class="font-semibold text-slate-900"><?php echo esc_html( number_format_i18n( (float) $fee, 0 ) ); ?></dd>
								</div>
							<?php endif; ?>
							<?php if ( '' !== $dur ) : ?>
								<div>
									<dt class="text-slate-500"><?php esc_html_e( 'Müddət', 'studyinturkey' ); ?></dt>
									<dd class="font-semibold text-slate-900"><?php echo esc_html( $dur ); ?></dd>
								</div>
							<?php endif; ?>
							<div>
								<dt class="text-slate-500"><?php esc_html_e( 'Təqaüd', 'studyinturkey' ); ?></dt>
								<dd class="font-semibold text-slate-900"><?php echo $sch ? esc_html__( 'Mövcuddur', 'studyinturkey' ) : esc_html__( 'Yoxdur', 'studyinturkey' ); ?></dd>
							</div>
						</dl>
						<?php
						$tax_display = static function ( int $pid, string $tax, string $label ) {
							$terms = get_the_terms( $pid, $tax );
							if ( ! is_array( $terms ) || is_wp_error( $terms ) || ! $terms ) {
								return;
							}
							$names = [];
							foreach ( $terms as $t ) {
								$names[] = sit_theme_get_term_name( (int) $t->term_id, $tax );
							}
							if ( ! $names ) {
								return;
							}
							echo '<p class="mt-4 text-sm text-slate-600"><span class="font-medium text-slate-700">' . esc_html( $label ) . ':</span> ' . esc_html( implode( ', ', $names ) ) . '</p>';
						};
						$tax_display( $post_id, 'degree_type', __( 'Dərəcə', 'studyinturkey' ) );
						$tax_display( $post_id, 'program_language', __( 'Dil', 'studyinturkey' ) );
						$tax_display( $post_id, 'field_of_study', __( 'Sahə', 'studyinturkey' ) );
						?>
					</div>
					<div class="w-full max-w-sm shrink-0 space-y-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
						<?php if ( $univ_link ) : ?>
							<a href="<?php echo esc_url( $univ_link ); ?>" class="block rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-800 hover:border-brand-300 hover:bg-brand-50">
								<?php
								printf(
									/* translators: %s: university name */
									esc_html__( 'Universitet: %s', 'studyinturkey' ),
									esc_html( $univ_title )
								);
								?>
							</a>
						<?php endif; ?>
						<?php if ( shortcode_exists( 'sit_application_form' ) ) : ?>
							<div class="sit-program-apply rounded-xl border border-brand-100 bg-brand-50/50 p-4 text-sm">
								<?php echo do_shortcode( '[sit_application_form program_id="' . absint( $post_id ) . '"]' ); ?>
							</div>
						<?php else : ?>
							<p class="text-xs text-slate-500"><?php esc_html_e( 'Müraciət formu üçün sit-developer-application plugin aktivləşdirin.', 'studyinturkey' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<div class="sit-container py-10 lg:py-14">
			<?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
				<div class="mb-10 overflow-hidden rounded-2xl border border-slate-200">
					<?php the_post_thumbnail( 'large', [ 'class' => 'w-full object-cover' ] ); ?>
				</div>
			<?php endif; ?>
			<div class="sit-entry-content mx-auto max-w-3xl text-slate-700 leading-relaxed">
				<?php echo sit_theme_get_post_content_filtered( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<p class="mx-auto mt-10 max-w-3xl">
				<a href="<?php echo esc_url( $archive_prog ); ?>" class="font-semibold text-brand-700 hover:text-brand-600">← <?php esc_html_e( 'Proqramlar siyahısına qayıt', 'studyinturkey' ); ?></a>
			</p>
		</div>
	</main>
	<?php
endwhile;

get_footer();
