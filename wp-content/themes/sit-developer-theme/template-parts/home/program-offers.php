<?php
/**
 * Today's Program Offers — random proqramlar, Figma kart dizaynı.
 */
defined( 'ABSPATH' ) || exit;

if ( ! post_type_exists( 'program' ) ) return;

$q = new WP_Query([
	'post_type'      => 'program',
	'post_status'    => 'publish',
	'posts_per_page' => 5,
	'orderby'        => 'rand',
	'meta_query'     => [['key'=>'sit_tuition_fee','value'=>0,'compare'=>'>','type'=>'NUMERIC']],
]);

if ( ! $q->have_posts() ) return;

$prog_url = sit_theme_programs_archive_url();
$tags     = [
	sit__( 'offer.tag.special_price', 'Xüsusi Qiymət' ),
	sit__( 'offer.tag.limited_seats', 'Məhdud Yer' ),
	sit__( 'offer.tag.early_bird', 'Erkən Qeydiyyat' ),
	sit__( 'offer.tag.best_value', 'Ən Sərfəli' ),
	sit__( 'offer.tag.popular', 'Məşhur' ),
];
$tag_styles = [
	'bg-blue-50 text-blue-600',
	'bg-orange-50 text-orange-600',
	'bg-slate-100 text-slate-800',
	'bg-emerald-50 text-emerald-600',
	'bg-red-50 text-red-600',
];
$idx = 0;
?>
<section class="border-t border-slate-100 bg-white py-20 lg:py-24 dark:border-slate-800 dark:bg-slate-950">
	<div class="mx-auto max-w-4xl px-4 sm:px-6">
		<div class="mb-12 text-center">
			<span class="mb-4 inline-block rounded-full bg-red-50 px-3 py-1 text-sm font-bold uppercase tracking-wider text-red-600 dark:bg-red-900/30 dark:text-red-400"><?php sit_esc_html_e( 'offers.badge', 'Məhdud Müddət' ); ?></span>
			<h2 class="text-3xl font-bold text-slate-800 md:text-4xl dark:text-white"><?php sit_esc_html_e( 'offers.heading', 'Günün Proqram Təklifləri' ); ?></h2>
		</div>

		<div class="flex flex-col gap-4">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$pid  = get_the_ID();
				$fee  = (float) get_post_meta( $pid, 'sit_tuition_fee', true );
				$dur  = get_post_meta( $pid, 'sit_duration', true );
				$sch  = (bool) get_post_meta( $pid, 'sit_scholarship_available', true );
				$uid  = (int) get_post_meta( $pid, 'sit_university_id', true );
				$ref  = $sch ? round( $fee * 2, -1 ) : null;
				$title = sit_theme_get_post_title( $pid );
				$link  = sit_theme_localize_url( get_permalink( $pid ) );

				$uni_name = ''; $logo_url = '';
				if ( $uid ) {
					$uni_name = sit_theme_get_post_title( $uid );
					$lid = (int) get_post_meta( $uid, 'sit_logo_id', true );
					if ( $lid ) $logo_url = wp_get_attachment_image_url( $lid, 'thumbnail' );
				}
				$tag = $tags[ $idx % count($tags) ];
				$tag_cls = $tag_styles[ $idx % count($tag_styles) ];
				$idx++;
			?>
				<div class="group flex flex-col items-center gap-6 rounded-2xl border border-slate-100 bg-white p-5 transition-all hover:border-slate-300 hover:shadow-xl md:flex-row dark:border-slate-700 dark:bg-slate-900 dark:hover:border-slate-500">
					<!-- Logo -->
					<div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-100 bg-slate-50 p-2 dark:border-slate-700 dark:bg-slate-800">
						<?php if ( $logo_url ) : ?>
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="" class="h-full w-full rounded-xl object-contain" loading="lazy" />
						<?php else : ?>
							<span class="text-2xl font-bold text-brand-600"><?php echo esc_html( mb_substr( $uni_name ?: '?', 0, 1 ) ); ?></span>
						<?php endif; ?>
					</div>

					<!-- Details -->
					<div class="min-w-0 flex-1 text-center md:text-start">
						<div class="mb-2 flex flex-wrap items-center justify-center gap-3 md:justify-start">
							<h3 class="text-xl font-bold text-slate-900 transition-colors group-hover:text-brand-700 dark:text-white"><?php echo esc_html( $title ); ?></h3>
							<span class="whitespace-nowrap rounded-md px-2.5 py-1 text-xs font-bold <?php echo esc_attr( $tag_cls ); ?>"><?php echo esc_html( $tag ); ?></span>
						</div>
						<p class="flex items-center justify-center gap-1.5 font-medium text-slate-500 md:justify-start dark:text-slate-400">
							<svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
							<?php echo esc_html( $uni_name ); ?>
						</p>
					</div>

					<!-- Price & CTA -->
					<div class="flex w-full shrink-0 flex-col items-center md:w-auto md:items-end">
						<div class="mb-2 flex items-end gap-3">
							<?php if ( $ref && $ref > $fee ) : ?>
								<span class="mb-1 text-sm text-slate-400 line-through">$<?php echo esc_html( number_format_i18n( $ref, 0 ) ); ?></span>
							<?php endif; ?>
							<span class="text-2xl font-extrabold text-slate-900 dark:text-white">
								$<?php echo esc_html( number_format_i18n( $fee, 0 ) ); ?><span class="text-sm font-medium text-slate-500">/<?php esc_html_e( 'il', 'studyinturkey' ); ?></span>
							</span>
						</div>
						<a href="<?php echo esc_url( $link ); ?>" class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand-700 px-6 py-2.5 font-semibold text-white transition-colors hover:bg-brand-800 md:w-auto">
							<?php sit_esc_html_e( 'btn.apply_now', 'İndi Müraciət Et' ); ?>
							<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
						</a>
					</div>
				</div>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<div class="mt-10 text-center">
			<a href="<?php echo esc_url( $prog_url ); ?>" class="inline-flex items-center gap-2 font-bold text-brand-700 hover:underline dark:text-brand-400">
				<?php sit_esc_html_e( 'offers.view_all', 'Bütün Təkliflərə Bax' ); ?>
				<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
			</a>
		</div>
	</div>
</section>
