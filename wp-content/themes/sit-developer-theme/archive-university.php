<?php
/**
 * Universities Archive — Figma dizaynı: hero + city tabs + filters + card grid.
 */
defined( 'ABSPATH' ) || exit;
get_header();

global $wp_query;
$total       = (int) $wp_query->found_posts;
$total_pages = (int) $wp_query->max_num_pages;
$paged       = max( 1, (int) get_query_var( 'paged' ) );
$cur_city    = isset( $_GET['sit_city'] ) ? sanitize_title( wp_unslash( $_GET['sit_city'] ) ) : '';
$cur_type    = isset( $_GET['sit_type'] ) ? sanitize_title( wp_unslash( $_GET['sit_type'] ) ) : '';
$cities      = taxonomy_exists( 'city' ) ? get_terms( [ 'taxonomy' => 'city', 'hide_empty' => true ] ) : [];
if ( is_wp_error( $cities ) ) $cities = [];
$action = sit_theme_universities_archive_url();
?>
<main id="main-content" class="flex-1">
	<!-- Hero -->
	<div class="relative overflow-hidden bg-[#11676a]">
		<div class="absolute top-0 right-0 h-[600px] w-[600px] -translate-y-1/2 translate-x-1/3 rounded-full bg-brand-500/20 blur-[120px]"></div>
		<div class="absolute bottom-0 left-0 h-[400px] w-[400px] translate-y-1/2 rounded-full bg-[#ff3131]/[0.08] blur-[100px]"></div>
		<div class="sit-container relative pt-16 pb-28">
			<div class="max-w-2xl">
				<div class="mb-6 flex items-center gap-3">
					<div class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5">
						<?php echo sit_theme_icon_svg( 'Building', 'h-[18px] w-[18px] text-white/70' ); ?>
					</div>
					<span class="text-[13px] font-semibold uppercase tracking-[0.2em] text-white/40"><?php sit_esc_html_e( 'archive.univ.badge', 'Kəşf edin' ); ?></span>
				</div>
				<h1 class="mb-4 text-[42px] tracking-[-0.03em] text-white md:text-[56px]" style="line-height:1.1"><?php esc_html_e( 'Universitetlər', 'studyinturkey' ); ?></h1>
				<p class="max-w-lg text-[17px] leading-relaxed text-white/40"><?php esc_html_e( 'Türkiyədəki universitetləri şəhər və növə görə araşdırın, müqayisə edin.', 'studyinturkey' ); ?></p>
			</div>

			<!-- Search -->
			<form action="<?php echo esc_url( $action ); ?>" method="get" class="mt-10 max-w-3xl">
				<div class="flex gap-1.5 rounded-2xl border border-white/[0.08] bg-white/[0.06] p-1.5 backdrop-blur-xl">
					<div class="flex flex-1 items-center rounded-xl bg-white/[0.06] px-4">
						<svg class="mr-3 h-4 w-4 shrink-0 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
						<input type="text" name="sit_q" value="<?php echo esc_attr( $_GET['sit_q'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Universitet axtar...', 'studyinturkey' ); ?>" class="w-full border-none bg-transparent py-3 text-[14px] text-white outline-none placeholder:text-white/25" />
					</div>
					<button type="submit" class="shrink-0 rounded-xl bg-[#ff3131] px-6 text-[13px] font-semibold text-white transition-all hover:bg-[#e02020]"><?php sit_esc_html_e( 'btn.search', 'Axtar' ); ?></button>
				</div>
			</form>
		</div>
	</div>

	<!-- Content -->
	<div class="bg-[#f3f6f6]">
		<div class="sit-container pb-24">
			<!-- City tabs + sort -->
			<div class="sticky top-[67px] z-30 -mt-6 mb-8">
				<div class="flex flex-col items-center justify-between gap-3 rounded-2xl border border-gray-200/60 bg-white p-3 shadow-[0_4px_30px_rgba(0,0,0,0.06)] lg:flex-row">
					<div class="flex w-full items-center gap-2 overflow-x-auto pb-1 lg:w-auto lg:pb-0">
						<a href="<?php echo esc_url( $action ); ?>" class="whitespace-nowrap rounded-xl px-4 py-2 text-[13px] font-semibold transition-all <?php echo ! $cur_city ? 'bg-[#0d4f52] text-white' : 'text-gray-500 hover:bg-gray-50'; ?>"><?php esc_html_e( 'Hamısı', 'studyinturkey' ); ?></a>
						<?php foreach ( $cities as $ct ) : ?>
							<a href="<?php echo esc_url( add_query_arg( 'sit_city', $ct->slug, $action ) ); ?>" class="whitespace-nowrap rounded-xl px-4 py-2 text-[13px] font-semibold transition-all <?php echo $cur_city === $ct->slug ? 'bg-[#0d4f52] text-white' : 'text-gray-500 hover:bg-gray-50'; ?>">
								<?php echo esc_html( sit_theme_get_term_name( (int) $ct->term_id, 'city' ) ); ?>
							</a>
						<?php endforeach; ?>
					</div>
					<span class="text-[13px] font-medium text-gray-400"><?php echo esc_html( $total ); ?>+ <?php sit_esc_html_e( 'archive.results', 'nəticə' ); ?></span>
				</div>
			</div>

			<!-- Grid -->
			<?php if ( have_posts() ) : ?>
				<div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
					<?php while ( have_posts() ) : the_post();
						$pid      = get_the_ID();
						$title    = sit_theme_get_post_title( $pid );
						$link     = sit_theme_localize_url( get_permalink( $pid ) );
						$logo_id  = (int) get_post_meta( $pid, 'sit_logo_id', true );
						$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
						$rating   = (float) get_post_meta( $pid, 'sit_rating', true );
						$ct       = get_the_terms( $pid, 'city' );
						$tp       = get_the_terms( $pid, 'university_type' );
						$city_name = ( ! is_wp_error( $ct ) && is_array( $ct ) && $ct ) ? sit_theme_get_term_name( (int) $ct[0]->term_id, 'city' ) : '';
						$type_name = ( ! is_wp_error( $tp ) && is_array( $tp ) && $tp ) ? sit_theme_get_term_name( (int) $tp[0]->term_id, 'university_type' ) : '';
						$is_private = ( ! is_wp_error( $tp ) && is_array( $tp ) && $tp ) ? ( $tp[0]->slug === 'ozəl' || $tp[0]->slug === 'oz%c9%99l' ) : false;
						$prog_count = 0;
						$prog_q = new WP_Query(['post_type'=>'program','meta_key'=>'sit_university_id','meta_value'=>$pid,'posts_per_page'=>1,'fields'=>'ids','no_found_rows'=>false]);
						$prog_count = $prog_q->found_posts;
						wp_reset_postdata();
					?>
						<a href="<?php echo esc_url( $link ); ?>" class="group block overflow-hidden rounded-2xl border border-gray-200/60 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-gray-200/50">
							<div class="h-1.5 <?php echo $is_private ? 'bg-gradient-to-r from-[#ff3131] to-orange-400' : 'bg-gradient-to-r from-brand-600 to-teal-500'; ?>"></div>
							<div class="p-5">
								<div class="mb-5 flex items-start justify-between">
									<div class="h-16 w-16 overflow-hidden rounded-2xl border border-gray-100 bg-gray-50 p-1.5 transition-transform duration-300 group-hover:scale-105">
										<?php if ( $logo_url ) : ?>
											<img src="<?php echo esc_url( $logo_url ); ?>" alt="" class="h-full w-full rounded-xl object-contain" loading="lazy" />
										<?php else : ?>
											<div class="flex h-full w-full items-center justify-center rounded-xl bg-brand-50 text-lg font-bold text-brand-600"><?php echo esc_html( mb_substr( $title, 0, 1 ) ); ?></div>
										<?php endif; ?>
									</div>
									<?php if ( $type_name ) : ?>
										<span class="rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider <?php echo $is_private ? 'border-red-100 bg-red-50 text-[#ff3131]' : 'border-brand-600/15 bg-[#e6f2f2] text-brand-600'; ?>">
											<?php echo esc_html( $type_name ); ?>
										</span>
									<?php endif; ?>
								</div>
								<h3 class="mb-3 line-clamp-2 text-[17px] font-bold leading-snug text-[#0a1a1b] transition-colors group-hover:text-brand-600"><?php echo esc_html( $title ); ?></h3>
								<div class="mb-4 flex items-center gap-3">
									<?php if ( $city_name ) : ?>
										<span class="flex items-center gap-1 text-[12px] font-medium text-gray-400">
											<svg class="h-3 w-3 text-[#ff3131]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
											<?php echo esc_html( $city_name ); ?>
										</span>
									<?php endif; ?>
									<?php if ( $rating > 0 ) : ?>
										<span class="text-gray-300">&middot;</span>
										<span class="flex items-center gap-1 text-[12px] font-medium text-amber-500">★ <?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?></span>
									<?php endif; ?>
								</div>
								<div class="flex items-center justify-between border-t border-gray-100 pt-4">
									<span class="flex items-center gap-1.5 text-[12px] font-medium text-gray-500">
										<svg class="h-[13px] w-[13px] text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342"/></svg>
										<?php echo esc_html( $prog_count ); ?> <?php esc_html_e( 'proqram', 'studyinturkey' ); ?>
									</span>
									<div class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-100 bg-gray-50 text-gray-400 transition-all group-hover:border-brand-600 group-hover:bg-brand-600 group-hover:text-white">
										<svg class="h-[14px] w-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
									</div>
								</div>
							</div>
						</a>
					<?php endwhile; ?>
				</div>

				<!-- Pagination -->
				<?php if ( $total_pages > 1 ) : ?>
					<div class="mt-14 flex justify-center">
						<?php echo paginate_links(['total'=>$total_pages,'current'=>$paged,'prev_text'=>'&lt;','next_text'=>'&gt;','type'=>'list','add_args'=>sit_theme_university_archive_filter_params()]); ?>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<p class="rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-12 text-center text-gray-500"><?php esc_html_e( 'Universitet tapılmadı.', 'studyinturkey' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php get_footer();
