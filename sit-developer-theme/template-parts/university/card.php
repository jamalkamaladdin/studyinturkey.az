<?php
/**
 * Universitet kartı (arxiv döngüsü üçün).
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
?>
<article <?php post_class( 'group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-brand-200 hover:shadow-md' ); ?>>
	<a href="<?php echo esc_url( $link ); ?>" class="relative block aspect-[16/9] bg-slate-100">
		<?php
		$cover_id = (int) get_post_meta( $post_id, 'sit_cover_image_id', true );
		if ( $cover_id ) {
			echo wp_get_attachment_image( $cover_id, 'medium_large', false, [ 'class' => 'h-full w-full object-cover transition group-hover:scale-[1.02]' ] );
		} elseif ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, 'medium_large', [ 'class' => 'h-full w-full object-cover transition group-hover:scale-[1.02]' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( $logo_id ) {
			echo wp_get_attachment_image( $logo_id, 'medium', false, [ 'class' => 'h-full w-full object-contain p-8 bg-white' ] );
		} else {
			echo '<span class="flex h-full items-center justify-center text-4xl font-bold text-brand-200" aria-hidden="true">' . esc_html( function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 ) ) . '</span>';
		}
		?>
		<?php if ( $rating > 0 ) : ?>
			<span class="absolute end-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-xs font-semibold text-amber-600 shadow-sm dark:bg-slate-900/95 dark:text-amber-400" aria-label="<?php esc_attr_e( 'Reytinq', 'studyinturkey' ); ?>">
				<?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?> ★
			</span>
		<?php endif; ?>
	</a>
	<div class="flex flex-1 flex-col p-5">
		<div class="flex items-start gap-3">
			<?php if ( $logo_id && ( $cover_id || has_post_thumbnail( $post_id ) ) ) : ?>
				<div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg border border-slate-100 bg-white p-1">
					<?php echo wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'h-full w-full object-contain' ] ); ?>
				</div>
			<?php endif; ?>
			<div class="min-w-0 flex-1">
				<h2 class="text-lg font-semibold text-slate-900 leading-snug">
					<a href="<?php echo esc_url( $link ); ?>" class="text-inherit hover:text-brand-700"><?php echo esc_html( $title ); ?></a>
				</h2>
				<?php if ( ! is_wp_error( $cities ) && is_array( $cities ) && $cities ) : ?>
					<p class="mt-1 text-xs text-slate-500">
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
			</div>
		</div>
		<?php if ( ! is_wp_error( $types ) && is_array( $types ) && $types ) : ?>
			<div class="mt-3 flex flex-wrap gap-1.5">
				<?php foreach ( $types as $t ) : ?>
					<span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"><?php echo esc_html( sit_theme_get_term_name( (int) $t->term_id, 'university_type' ) ); ?></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<?php if ( '' !== $excerpt ) : ?>
			<p class="mt-3 line-clamp-3 flex-1 text-sm text-slate-600"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>
		<div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-4 text-sm">
			<?php if ( is_numeric( $tuition ) && (float) $tuition > 0 ) : ?>
				<span class="font-medium text-slate-700">
					<?php
					printf(
						/* translators: %s: formatted tuition minimum */
						esc_html__( 'Ən azı %s', 'studyinturkey' ),
						esc_html( number_format_i18n( (float) $tuition, 0 ) )
					);
					?>
				</span>
			<?php else : ?>
				<span></span>
			<?php endif; ?>
			<a href="<?php echo esc_url( $link ); ?>" class="font-semibold text-brand-700 hover:text-brand-600"><?php esc_html_e( 'Ətraflı', 'studyinturkey' ); ?> →</a>
		</div>
	</div>
</article>
