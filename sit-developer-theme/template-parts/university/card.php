<?php
/**
 * Universitet kartı — StudyLeo tipli (loqo + mətn + CTA).
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_the_ID();
$title   = sit_theme_get_post_title( $post_id );
$link    = sit_theme_localize_url( get_permalink( $post_id ) );
$excerpt = sit_theme_get_post_excerpt( $post_id );
$rating  = (float) get_post_meta( $post_id, 'sit_rating', true );
$tuition = get_post_meta( $post_id, 'sit_tuition_fee_min', true );
$logo_id = (int) get_post_meta( $post_id, 'sit_logo_id', true );
$cities  = get_the_terms( $post_id, 'city' );
$types   = get_the_terms( $post_id, 'university_type' );

$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
?>
<article <?php post_class( 'flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-brand-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-900' ); ?>>
	<div class="flex gap-4 p-4 sm:p-5">
		<?php if ( $logo_url ) : ?>
			<a href="<?php echo esc_url( $link ); ?>" class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border border-slate-100 bg-white p-1 dark:border-slate-700 sm:h-20 sm:w-20">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="" class="h-full w-full object-contain" loading="lazy" width="80" height="80" />
			</a>
		<?php else : ?>
			<div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-xl font-bold text-brand-600 dark:bg-brand-950/80 dark:text-brand-300 sm:h-20 sm:w-20" aria-hidden="true">
				<?php echo esc_html( function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 ) ); ?>
			</div>
		<?php endif; ?>
		<div class="min-w-0 flex-1">
			<h2 class="text-lg font-semibold leading-snug text-slate-900 dark:text-white sm:text-xl">
				<a href="<?php echo esc_url( $link ); ?>" class="text-inherit hover:text-brand-600 dark:hover:text-brand-400"><?php echo esc_html( $title ); ?></a>
			</h2>
			<?php if ( ! is_wp_error( $cities ) && is_array( $cities ) && $cities ) : ?>
				<p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
					<?php
					$names = array_map(
						function ( $t ) {
							return sit_theme_get_term_name( (int) $t->term_id, 'city' );
						},
						$cities
					);
					echo esc_html( implode( ', ', array_filter( $names ) ) );
					?>
				</p>
			<?php endif; ?>
			<?php if ( ! is_wp_error( $types ) && is_array( $types ) && $types ) : ?>
				<div class="mt-2 flex flex-wrap gap-1.5">
					<?php foreach ( $types as $t ) : ?>
						<span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"><?php echo esc_html( sit_theme_get_term_name( (int) $t->term_id, 'university_type' ) ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( $rating > 0 ) : ?>
				<p class="mt-2 text-sm font-medium text-amber-600 dark:text-amber-400"><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?> ★</p>
			<?php endif; ?>
		</div>
	</div>
	<?php if ( '' !== $excerpt ) : ?>
		<p class="line-clamp-2 border-t border-slate-100 px-4 pb-3 pt-2 text-sm text-slate-600 dark:border-slate-800 dark:text-slate-400 sm:px-5"><?php echo esc_html( $excerpt ); ?></p>
	<?php endif; ?>
	<div class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-4 dark:border-slate-800 sm:px-5">
		<?php if ( is_numeric( $tuition ) && (float) $tuition > 0 ) : ?>
			<div class="text-sm font-semibold text-slate-900 dark:text-white">
				<span class="text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Ən azı', 'studyinturkey' ); ?></span>
				<span class="ms-1 text-brand-600 dark:text-brand-400"><?php echo esc_html( number_format_i18n( (float) $tuition, 0 ) ); ?>$</span>
				<span class="text-xs font-normal text-slate-500"><?php esc_html_e( '/ il', 'studyinturkey' ); ?></span>
			</div>
		<?php else : ?>
			<span></span>
		<?php endif; ?>
		<a href="<?php echo esc_url( $link ); ?>" class="inline-flex min-h-[2.5rem] items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 dark:hover:bg-brand-500"><?php esc_html_e( 'Ətraflı', 'studyinturkey' ); ?></a>
	</div>
</article>
