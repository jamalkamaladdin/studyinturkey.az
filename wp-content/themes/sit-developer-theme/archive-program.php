<?php
/**
 * Programs Archive — Figma dizaynı: gradient hero + degree tabs + row cards + filters.
 */
defined( 'ABSPATH' ) || exit;
get_header();

global $wp_query;
$paged       = max( 1, (int) get_query_var( 'paged' ) );
$total       = (int) $wp_query->found_posts;
$total_pages = (int) $wp_query->max_num_pages;

$cur_degree = isset( $_GET['sit_degree'] ) ? sanitize_text_field( wp_unslash( $_GET['sit_degree'] ) ) : '';
$cur_lang   = isset( $_GET['sit_language'] ) ? sanitize_text_field( wp_unslash( $_GET['sit_language'] ) ) : '';
$cur_field  = isset( $_GET['sit_field'] ) ? sanitize_text_field( wp_unslash( $_GET['sit_field'] ) ) : '';
$cur_pmin   = isset( $_GET['sit_price_min'] ) && is_numeric( $_GET['sit_price_min'] ) ? (string)(float) $_GET['sit_price_min'] : '';
$cur_pmax   = isset( $_GET['sit_price_max'] ) && is_numeric( $_GET['sit_price_max'] ) ? (string)(float) $_GET['sit_price_max'] : '';
$cur_sort   = isset( $_GET['sit_sort'] ) ? sanitize_key( wp_unslash( $_GET['sit_sort'] ) ) : '';
$cur_univ   = isset( $_GET['sit_university'] ) ? absint( $_GET['sit_university'] ) : 0;

$degrees    = taxonomy_exists( 'degree_type' ) ? get_terms( [ 'taxonomy' => 'degree_type', 'hide_empty' => false ] ) : [];
$prog_langs = taxonomy_exists( 'program_language' ) ? get_terms( [ 'taxonomy' => 'program_language', 'hide_empty' => false ] ) : [];
$fields     = taxonomy_exists( 'field_of_study' ) ? get_terms( [ 'taxonomy' => 'field_of_study', 'hide_empty' => false ] ) : [];
if ( is_wp_error( $degrees ) ) $degrees = [];
if ( is_wp_error( $prog_langs ) ) $prog_langs = [];
if ( is_wp_error( $fields ) ) $fields = [];

$action = sit_theme_programs_archive_url();

$accent_styles = ['from-brand-600 to-teal-600','from-brand-700 to-teal-500','from-brand-600 to-emerald-600','from-brand-700 to-cyan-600','from-brand-600 to-teal-700'];
?>
<main id="main-content" class="flex-1">
	<!-- Hero -->
	<div class="relative overflow-hidden bg-gradient-to-br from-brand-600 via-brand-700 to-[#0d4f52]">
		<div class="absolute left-1/2 top-1/2 h-[800px] w-[800px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-white/[0.03]"></div>
		<div class="absolute top-0 right-0 h-96 w-96 rounded-full bg-[#ff3131]/10 blur-[100px]"></div>
		<div class="sit-container relative pt-16 pb-28">
			<div class="max-w-2xl">
				<div class="mb-6 flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 w-fit">
					<svg class="h-[13px] w-[13px] text-amber-300" fill="currentColor" viewBox="0 0 24 24"><path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z"/></svg>
					<span class="text-[12px] font-semibold text-white/60"><?php echo esc_html( $total ); ?>+ <?php esc_html_e( 'proqram mövcuddur', 'studyinturkey' ); ?></span>
				</div>
				<h1 class="mb-4 text-[42px] tracking-[-0.03em] text-white md:text-[56px]" style="line-height:1.1"><?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></h1>
				<p class="max-w-lg text-[17px] leading-relaxed text-white/40"><?php esc_html_e( 'İngilis və türk dillərində proqramları kəşf edin, eksklüziv təqaüdlərdən faydalanın.', 'studyinturkey' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Content -->
	<div class="bg-[#f3f6f6]">
		<div class="sit-container pb-24">
			<!-- Degree tabs + search -->
			<div class="sticky top-[67px] z-30 -mt-6 mb-8">
				<form action="<?php echo esc_url( $action ); ?>" method="get" class="flex flex-col items-center justify-between gap-3 rounded-2xl border border-gray-200/60 bg-white p-3 shadow-[0_4px_30px_rgba(0,0,0,0.06)] lg:flex-row">
					<div class="flex w-full items-center gap-1.5 overflow-x-auto pb-1 lg:w-auto lg:pb-0">
						<a href="<?php echo esc_url( $action ); ?>" class="whitespace-nowrap rounded-xl px-4 py-2 text-[13px] font-semibold transition-all <?php echo ! $cur_degree ? 'bg-[#0d4f52] text-white' : 'text-gray-500 hover:bg-gray-50'; ?>"><?php esc_html_e( 'Hamısı', 'studyinturkey' ); ?></a>
						<?php foreach ( $degrees as $d ) : ?>
							<a href="<?php echo esc_url( add_query_arg( 'sit_degree', $d->slug, $action ) ); ?>" class="whitespace-nowrap rounded-xl px-4 py-2 text-[13px] font-semibold transition-all <?php echo $cur_degree === $d->slug ? 'bg-[#0d4f52] text-white' : 'text-gray-500 hover:bg-gray-50'; ?>">
								<?php echo esc_html( sit_theme_get_term_name( (int) $d->term_id, 'degree_type' ) ); ?>
							</a>
						<?php endforeach; ?>
					</div>
					<div class="flex w-full items-center gap-2 lg:w-auto">
						<div class="relative flex-1 lg:w-64">
							<svg class="absolute left-3 top-1/2 h-[14px] w-[14px] -translate-y-1/2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
							<input type="text" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Proqram axtar...', 'studyinturkey' ); ?>" class="w-full rounded-xl border border-gray-100 bg-gray-50 py-2 pl-9 pr-3 text-[13px] outline-none transition focus:ring-2 focus:ring-brand-600/20" />
						</div>
					</div>
				</form>
			</div>

			<div class="flex gap-8">
				<!-- Sidebar filters -->
				<aside class="hidden w-[260px] shrink-0 lg:block">
					<form action="<?php echo esc_url( $action ); ?>" method="get" class="sticky top-[140px] rounded-2xl border border-gray-200/60 bg-white p-5">
						<div class="mb-5 flex items-center gap-2 border-b border-gray-100 pb-4">
							<svg class="h-[15px] w-[15px] text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
							<span class="text-[14px] font-bold text-[#0a1a1b]"><?php esc_html_e( 'Filtrlər', 'studyinturkey' ); ?></span>
						</div>
						<?php if ( $fields ) : ?>
						<div class="mb-6">
							<h3 class="mb-3 text-[12px] font-semibold uppercase tracking-[0.1em] text-[#0a1a1b]"><?php esc_html_e( 'İxtisas sahəsi', 'studyinturkey' ); ?></h3>
							<select name="sit_field" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5 text-[13px] font-medium text-gray-600 outline-none">
								<option value=""><?php esc_html_e( 'Hamısı', 'studyinturkey' ); ?></option>
								<?php foreach ( $fields as $f ) : ?><option value="<?php echo esc_attr( $f->slug ); ?>" <?php selected( $cur_field, $f->slug ); ?>><?php echo esc_html( sit_theme_get_term_name( (int) $f->term_id, 'field_of_study' ) ); ?></option><?php endforeach; ?>
							</select>
						</div>
						<?php endif; ?>
						<?php if ( $prog_langs ) : ?>
						<div class="mb-6">
							<h3 class="mb-3 text-[12px] font-semibold uppercase tracking-[0.1em] text-[#0a1a1b]"><?php esc_html_e( 'Tədris dili', 'studyinturkey' ); ?></h3>
							<select name="sit_language" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5 text-[13px] font-medium text-gray-600 outline-none">
								<option value=""><?php esc_html_e( 'Hamısı', 'studyinturkey' ); ?></option>
								<?php foreach ( $prog_langs as $pl ) : ?><option value="<?php echo esc_attr( $pl->slug ); ?>" <?php selected( $cur_lang, $pl->slug ); ?>><?php echo esc_html( sit_theme_get_term_name( (int) $pl->term_id, 'program_language' ) ); ?></option><?php endforeach; ?>
							</select>
						</div>
						<?php endif; ?>
						<div class="mb-6">
							<h3 class="mb-3 text-[12px] font-semibold uppercase tracking-[0.1em] text-[#0a1a1b]"><?php esc_html_e( 'Qiymət aralığı', 'studyinturkey' ); ?></h3>
							<div class="flex gap-2">
								<input type="number" name="sit_price_min" value="<?php echo esc_attr( $cur_pmin ); ?>" placeholder="<?php esc_attr_e( 'Min', 'studyinturkey' ); ?>" class="w-1/2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-[12px] text-gray-600 outline-none" />
								<input type="number" name="sit_price_max" value="<?php echo esc_attr( $cur_pmax ); ?>" placeholder="<?php esc_attr_e( 'Maks', 'studyinturkey' ); ?>" class="w-1/2 rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-[12px] text-gray-600 outline-none" />
							</div>
						</div>
						<div class="mb-6">
							<h3 class="mb-3 text-[12px] font-semibold uppercase tracking-[0.1em] text-[#0a1a1b]"><?php esc_html_e( 'Sıralama', 'studyinturkey' ); ?></h3>
							<select name="sit_sort" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-3 py-2.5 text-[13px] font-medium text-gray-600 outline-none">
								<option value="date_desc" <?php selected( $cur_sort, 'date_desc' ); ?>><?php esc_html_e( 'Ən yeni', 'studyinturkey' ); ?></option>
								<option value="price_asc" <?php selected( $cur_sort, 'price_asc' ); ?>><?php esc_html_e( 'Qiymət: ucuzdan bahaya', 'studyinturkey' ); ?></option>
								<option value="price_desc" <?php selected( $cur_sort, 'price_desc' ); ?>><?php esc_html_e( 'Qiymət: bahadan ucuza', 'studyinturkey' ); ?></option>
								<option value="title_asc" <?php selected( $cur_sort, 'title_asc' ); ?>><?php esc_html_e( 'Ad (A-Z)', 'studyinturkey' ); ?></option>
							</select>
						</div>
						<button type="submit" class="w-full rounded-xl bg-[#0d4f52] py-2.5 text-[13px] font-semibold text-white transition-all hover:bg-brand-600"><?php esc_html_e( 'Tətbiq et', 'studyinturkey' ); ?></button>
					</form>
				</aside>

				<!-- Program list -->
				<div class="min-w-0 flex-1">
					<?php if ( have_posts() ) : ?>
						<div class="flex flex-col gap-3">
							<?php $idx = 0; while ( have_posts() ) : the_post();
								$pid   = get_the_ID();
								$title = sit_theme_get_post_title( $pid );
								$link  = sit_theme_localize_url( get_permalink( $pid ) );
								$fee   = (float) get_post_meta( $pid, 'sit_tuition_fee', true );
								$dur   = get_post_meta( $pid, 'sit_duration', true );
								$sch   = (bool) get_post_meta( $pid, 'sit_scholarship_available', true );
								$uid   = (int) get_post_meta( $pid, 'sit_university_id', true );
								$ref   = $sch && $fee > 0 ? round( $fee * 2, -1 ) : null;
								$deg   = get_the_terms( $pid, 'degree_type' );
								$plang = get_the_terms( $pid, 'program_language' );
								$uni_name = ''; $logo_url = '';
								if ( $uid ) {
									$uni_name = sit_theme_get_post_title( $uid );
									$lid = (int) get_post_meta( $uid, 'sit_logo_id', true );
									if ( $lid ) $logo_url = wp_get_attachment_image_url( $lid, 'thumbnail' );
								}
								$deg_name = ( ! is_wp_error( $deg ) && is_array( $deg ) && $deg ) ? sit_theme_get_term_name( (int) $deg[0]->term_id, 'degree_type' ) : '';
								$lang_name = ( ! is_wp_error( $plang ) && is_array( $plang ) && $plang ) ? sit_theme_get_term_name( (int) $plang[0]->term_id, 'program_language' ) : '';
								$ac = $accent_styles[ $idx % 5 ];
								$idx++;
							?>
								<a href="<?php echo esc_url( $link ); ?>" class="group block overflow-hidden rounded-2xl border border-gray-200/60 bg-white transition-all duration-300 hover:shadow-xl hover:shadow-gray-200/50">
									<div class="flex flex-col md:flex-row">
										<div class="h-1.5 w-full bg-gradient-to-b <?php echo esc_attr( $ac ); ?> md:h-auto md:w-1.5 shrink-0"></div>
										<div class="flex flex-1 flex-col items-center gap-4 p-5 md:flex-row">
											<!-- Logo -->
											<div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-gray-100 bg-gray-50 p-1.5 transition-transform group-hover:scale-105">
												<?php if ( $logo_url ) : ?>
													<img src="<?php echo esc_url( $logo_url ); ?>" alt="" class="h-full w-full rounded-lg object-contain" loading="lazy" />
												<?php else : ?>
													<div class="flex h-full w-full items-center justify-center rounded-lg bg-brand-50 font-bold text-brand-600"><?php echo esc_html( mb_substr( $uni_name ?: '?', 0, 1 ) ); ?></div>
												<?php endif; ?>
											</div>
											<!-- Info -->
											<div class="min-w-0 flex-1 text-center md:text-start">
												<div class="mb-1 flex flex-wrap items-center justify-center gap-2 md:justify-start">
													<h3 class="text-[16px] font-bold text-[#0a1a1b] transition-colors group-hover:text-brand-600"><?php echo esc_html( $title ); ?></h3>
													</div>
												<p class="mb-2 flex items-center justify-center gap-1.5 text-[13px] font-medium text-gray-400 md:justify-start">
													<svg class="h-[13px] w-[13px] text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18"/></svg>
													<?php echo esc_html( $uni_name ); ?>
												</p>
												<div class="flex flex-wrap items-center justify-center gap-2 md:justify-start">
													<?php if ( $deg_name ) : ?>
														<span class="flex items-center gap-1 rounded-lg border border-gray-100 bg-gray-50 px-2.5 py-1 text-[11px] font-semibold text-gray-500">
															<svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347"/></svg>
															<?php echo esc_html( $deg_name ); ?>
														</span>
													<?php endif; ?>
													<?php if ( $dur ) : ?>
														<span class="flex items-center gap-1 rounded-lg border border-gray-100 bg-gray-50 px-2.5 py-1 text-[11px] font-semibold text-gray-500">
															<svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
															<?php echo esc_html( $dur ); ?>
														</span>
													<?php endif; ?>
												</div>
											</div>
											<!-- Price -->
											<div class="flex w-full shrink-0 flex-col items-center border-t border-gray-50 pt-3 md:w-auto md:items-end md:border-t-0 md:pt-0">
												<div class="mb-2 flex items-end gap-2">
													<?php if ( $ref && $ref > $fee ) : ?>
														<span class="text-[13px] text-gray-300 line-through">$<?php echo esc_html( number_format_i18n( $ref, 0 ) ); ?></span>
													<?php endif; ?>
													<?php if ( $fee > 0 ) : ?>
														<span class="text-[22px] font-extrabold tracking-tight text-[#0a1a1b]">$<?php echo esc_html( number_format_i18n( $fee, 0 ) ); ?></span>
													<?php endif; ?>
												</div>
												<div class="flex items-center gap-1.5 rounded-xl bg-brand-600 px-5 py-2 text-[13px] font-semibold text-white transition-all group-hover:bg-brand-700">
													<?php esc_html_e( 'Ətraflı', 'studyinturkey' ); ?>
													<svg class="h-[14px] w-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
												</div>
											</div>
										</div>
									</div>
								</a>
							<?php endwhile; ?>
						</div>

						<?php if ( $total_pages > 1 ) : ?>
							<div class="mt-14 flex justify-center">
								<?php echo paginate_links(['total'=>$total_pages,'current'=>$paged,'prev_text'=>'&lt;','next_text'=>'&gt;','type'=>'list','add_args'=>sit_theme_program_archive_filter_params()]); ?>
							</div>
						<?php endif; ?>
					<?php else : ?>
						<p class="rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-12 text-center text-gray-500"><?php esc_html_e( 'Proqram tapılmadı.', 'studyinturkey' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</main>
<?php get_footer();
