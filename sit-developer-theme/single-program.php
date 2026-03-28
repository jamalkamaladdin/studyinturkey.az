<?php
/**
 * Tək proqram səhifəsi — hero, universitet loqosu, mərkəzdə müraciət formu.
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
	$uid     = sit_theme_get_program_university_id( $post_id );

	$univ_title = '';
	$univ_link  = '';
	$logo_id    = 0;
	$logo_url   = '';
	if ( $uid > 0 ) {
		$univ_title = sit_theme_get_post_title( $uid );
		$univ_link  = sit_theme_localize_url( get_permalink( $uid ) );
		$logo_id    = (int) get_post_meta( $uid, 'sit_logo_id', true );
		if ( $logo_id ) {
			$logo_url = wp_get_attachment_image_url( $logo_id, 'medium' );
		}
	}

	$archive_prog = sit_theme_programs_archive_url();

	$deg_terms = get_the_terms( $post_id, 'degree_type' );
	$lang_terms = get_the_terms( $post_id, 'program_language' );
	$fmt_tax    = static function ( $terms, string $tax ): string {
		if ( ! is_array( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}
		$names = [];
		foreach ( $terms as $t ) {
			$names[] = sit_theme_get_term_name( (int) $t->term_id, $tax );
		}
		return $names ? implode( ', ', array_filter( $names ) ) : '';
	};
	$deg_line  = $fmt_tax( $deg_terms, 'degree_type' );
	$lang_line = $fmt_tax( $lang_terms, 'program_language' );
	$field_line = sit_theme_get_program_field_display_line( $post_id );

	$fee_num = ( is_numeric( $fee ) && (float) $fee > 0 ) ? (float) $fee : null;
	$fee_ref = ( $sch && $fee_num ) ? round( $fee_num * 2, -1 ) : null;
	if ( $fee_ref && $fee_ref <= $fee_num ) {
		$fee_ref = null;
	}
	?>
	<main id="main-content" class="flex-1">
		<div class="border-b border-slate-200 bg-gradient-to-b from-slate-50 to-white dark:border-slate-800 dark:from-slate-900 dark:to-slate-950">
			<div class="sit-container py-8 lg:py-10">
				<nav class="text-sm text-slate-500 dark:text-slate-400" aria-label="<?php esc_attr_e( 'Çörək qırıntısı', 'studyinturkey' ); ?>">
					<a href="<?php echo esc_url( sit_theme_localize_url( home_url( '/' ) ) ); ?>" class="hover:text-slate-800 dark:hover:text-white"><?php esc_html_e( 'Ana səhifə', 'studyinturkey' ); ?></a>
					<span class="mx-1.5" aria-hidden="true">/</span>
					<a href="<?php echo esc_url( $archive_prog ); ?>" class="hover:text-slate-800 dark:hover:text-white"><?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></a>
					<?php if ( $univ_link ) : ?>
						<span class="mx-1.5" aria-hidden="true">/</span>
						<a href="<?php echo esc_url( $univ_link ); ?>" class="hover:text-slate-800 dark:hover:text-white"><?php echo esc_html( $univ_title ); ?></a>
					<?php endif; ?>
				</nav>

				<div class="mt-8 flex flex-col items-center text-center">
					<?php if ( $logo_url && $univ_link ) : ?>
						<a href="<?php echo esc_url( $univ_link ); ?>" class="mb-5 inline-flex rounded-2xl border border-slate-200 bg-white p-4 shadow-sm ring-1 ring-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:ring-slate-700">
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="" class="h-16 w-auto max-h-20 object-contain sm:h-20" width="120" height="80" loading="eager" />
						</a>
					<?php elseif ( $logo_url ) : ?>
						<div class="mb-5 inline-flex rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="" class="h-16 w-auto max-h-20 object-contain sm:h-20" width="120" height="80" loading="eager" />
						</div>
					<?php endif; ?>

					<h1 class="max-w-4xl text-3xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-4xl"><?php echo esc_html( $title ); ?></h1>

					<?php if ( $univ_link ) : ?>
						<p class="mt-3 text-sm font-medium text-slate-600 dark:text-slate-400">
							<a href="<?php echo esc_url( $univ_link ); ?>" class="text-brand-700 hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300"><?php echo esc_html( $univ_title ); ?></a>
						</p>
					<?php endif; ?>

					<div class="mt-8 grid w-full max-w-3xl grid-cols-2 gap-3 sm:grid-cols-4">
						<div class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
							<p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Ödəniş', 'studyinturkey' ); ?></p>
							<p class="mt-1 text-base font-bold text-slate-900 dark:text-white">
								<?php if ( null !== $fee_num ) : ?>
									<?php if ( null !== $fee_ref ) : ?>
										<span class="text-emerald-600 dark:text-emerald-400"><?php echo esc_html( number_format_i18n( $fee_num, 0 ) ); ?> USD</span>
										<span class="block text-xs font-normal text-slate-400 line-through"><?php echo esc_html( number_format_i18n( $fee_ref, 0 ) ); ?> USD</span>
									<?php else : ?>
										<?php echo esc_html( number_format_i18n( $fee_num, 0 ) ); ?> USD
									<?php endif; ?>
								<?php else : ?>
									<?php echo esc_html( '—' ); ?>
								<?php endif; ?>
							</p>
						</div>
						<div class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
							<p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Müddət', 'studyinturkey' ); ?></p>
							<p class="mt-1 text-base font-bold text-slate-900 dark:text-white"><?php echo '' !== $dur ? esc_html( $dur ) : esc_html( '—' ); ?></p>
						</div>
						<div class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
							<p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Təqaüd', 'studyinturkey' ); ?></p>
							<p class="mt-1 text-base font-bold text-slate-900 dark:text-white"><?php echo $sch ? esc_html__( 'Mövcuddur', 'studyinturkey' ) : esc_html__( 'Yoxdur', 'studyinturkey' ); ?></p>
						</div>
						<div class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
							<p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Dil', 'studyinturkey' ); ?></p>
							<p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white"><?php echo '' !== $lang_line ? esc_html( $lang_line ) : esc_html( '—' ); ?></p>
						</div>
					</div>

					<dl class="mt-6 grid w-full max-w-xl gap-2 text-left text-sm sm:grid-cols-2">
						<?php if ( '' !== $deg_line ) : ?>
							<div class="flex justify-between gap-4 rounded-lg bg-slate-100/80 px-3 py-2 dark:bg-slate-800/80">
								<dt class="text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Dərəcə', 'studyinturkey' ); ?></dt>
								<dd class="font-semibold text-slate-900 dark:text-white"><?php echo esc_html( $deg_line ); ?></dd>
							</div>
						<?php endif; ?>
						<?php if ( '' !== $field_line ) : ?>
							<div class="flex justify-between gap-4 rounded-lg bg-slate-100/80 px-3 py-2 dark:bg-slate-800/80 sm:col-span-2">
								<dt class="text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Sahə', 'studyinturkey' ); ?></dt>
								<dd class="text-end font-semibold text-slate-900 dark:text-white"><?php echo esc_html( $field_line ); ?></dd>
							</div>
						<?php endif; ?>
					</dl>
				</div>
			</div>
		</div>

		<div class="sit-container py-10 lg:py-14">
			<div class="mx-auto max-w-2xl">
				<h2 class="mb-6 text-center text-xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Onlayn müraciət', 'studyinturkey' ); ?></h2>
				<p class="mb-8 text-center text-sm text-slate-600 dark:text-slate-400"><?php esc_html_e( 'Aşağıdakı formu doldurun. Dərəcənizə uyğun sənədlər avtomatik tələb olunacaq.', 'studyinturkey' ); ?></p>
				<?php if ( shortcode_exists( 'sit_application_form' ) ) : ?>
					<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:p-8">
						<?php echo do_shortcode( '[sit_application_form program_id="' . absint( $post_id ) . '"]' ); ?>
					</div>
				<?php else : ?>
					<p class="text-center text-sm text-slate-500"><?php esc_html_e( 'Müraciət formu üçün sit-developer-application plugin aktivləşdirin.', 'studyinturkey' ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( has_post_thumbnail() && ! post_password_required() ) : ?>
				<div class="mx-auto mt-12 max-w-3xl overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
					<?php the_post_thumbnail( 'large', [ 'class' => 'w-full object-cover' ] ); ?>
				</div>
			<?php endif; ?>

			<div class="sit-entry-content mx-auto mt-10 max-w-3xl text-slate-700 dark:text-slate-300">
				<?php echo sit_theme_get_post_content_filtered( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<p class="mx-auto mt-10 max-w-3xl text-center">
				<a href="<?php echo esc_url( $archive_prog ); ?>" class="font-semibold text-brand-700 hover:text-brand-600 dark:text-brand-400">← <?php esc_html_e( 'Proqramlar siyahısına qayıt', 'studyinturkey' ); ?></a>
			</p>
		</div>
	</main>
	<?php
endwhile;

get_footer();
