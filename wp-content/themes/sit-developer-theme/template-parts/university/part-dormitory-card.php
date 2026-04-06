<?php
/**
 * Yataqxana kartı (siyahı və alt səhifə).
 */

defined( 'ABSPATH' ) || exit;

$pid = isset( $args['dormitory_id'] ) ? absint( $args['dormitory_id'] ) : ( isset( $dormitory_id ) ? absint( $dormitory_id ) : 0 );
if ( $pid < 1 ) {
	return;
}

$name       = sit_theme_get_post_title( $pid );
$link       = sit_theme_localize_url( get_permalink( $pid ) );
$price      = get_post_meta( $pid, 'sit_price', true );
$price_max  = get_post_meta( $pid, 'sit_price_max', true );
$distance   = (string) get_post_meta( $pid, 'sit_distance', true );
$facilities = (string) get_post_meta( $pid, 'sit_facilities', true );
$rooms      = (string) get_post_meta( $pid, 'sit_dorm_room_types', true );
$capacity   = (int) get_post_meta( $pid, 'sit_dorm_capacity', true );
$gender     = (string) get_post_meta( $pid, 'sit_dorm_gender', true );
$loc        = (string) get_post_meta( $pid, 'sit_dorm_location_type', true );
$contact    = (string) get_post_meta( $pid, 'sit_dorm_contact_url', true );

$gender_l = [
	'female' => __( 'Qadın', 'studyinturkey' ),
	'male'   => __( 'Kişi', 'studyinturkey' ),
	'mixed'  => __( 'Qarışıq', 'studyinturkey' ),
];
$loc_l = [
	'off_campus' => __( 'Kampusdan kənar', 'studyinturkey' ),
	'on_campus'  => __( 'Kampusda', 'studyinturkey' ),
];
?>
<article class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
	<div class="border-b border-slate-100 p-5 dark:border-slate-800">
		<h3 class="text-lg font-semibold text-slate-900 dark:text-white">
			<a href="<?php echo esc_url( $link ); ?>" class="hover:text-brand-600 dark:hover:text-brand-400"><?php echo esc_html( $name ); ?></a>
		</h3>
		<?php if ( '' !== $distance ) : ?>
			<p class="mt-2 text-sm text-slate-600 dark:text-slate-400"><?php echo esc_html( $distance ); ?></p>
		<?php endif; ?>
	</div>
	<div class="flex flex-1 flex-col gap-3 p-5 text-sm text-slate-600 dark:text-slate-400">
		<?php if ( is_numeric( $price ) && (float) $price > 0 ) : ?>
			<p>
				<span class="font-medium text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Qiymət:', 'studyinturkey' ); ?></span>
				<?php
				$pmin = number_format_i18n( (float) $price, 0 );
				if ( is_numeric( $price_max ) && (float) $price_max > 0 && (float) $price_max !== (float) $price ) {
					$pmax = number_format_i18n( (float) $price_max, 0 );
					echo esc_html( $pmin . '$ – ' . $pmax . '$' );
				} else {
					echo esc_html( $pmin . '$' );
				}
				?>
			</p>
		<?php endif; ?>
		<?php if ( '' !== trim( $rooms ) ) : ?>
			<p>
				<span class="font-medium text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Otaqlar:', 'studyinturkey' ); ?></span>
				<?php echo esc_html( str_replace( [ "\r\n", "\n" ], ', ', $rooms ) ); ?>
			</p>
		<?php endif; ?>
		<?php if ( $capacity > 0 ) : ?>
			<p>
				<span class="font-medium text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Tutum:', 'studyinturkey' ); ?></span>
				<?php echo esc_html( (string) $capacity ); ?>
			</p>
		<?php endif; ?>
		<?php if ( isset( $gender_l[ $gender ] ) ) : ?>
			<p>
				<span class="font-medium text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Cinsiyyət:', 'studyinturkey' ); ?></span>
				<?php echo esc_html( $gender_l[ $gender ] ); ?>
			</p>
		<?php endif; ?>
		<?php if ( isset( $loc_l[ $loc ] ) ) : ?>
			<p>
				<span class="font-medium text-slate-800 dark:text-slate-200"><?php esc_html_e( 'Növ:', 'studyinturkey' ); ?></span>
				<?php echo esc_html( $loc_l[ $loc ] ); ?>
			</p>
		<?php endif; ?>
		<?php if ( '' !== $facilities ) : ?>
			<p class="leading-relaxed"><?php echo esc_html( wp_strip_all_tags( $facilities ) ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( '' !== $contact ) : ?>
		<div class="mt-auto border-t border-slate-100 p-4 dark:border-slate-800">
			<a href="<?php echo esc_url( $contact ); ?>" class="text-sm font-semibold text-brand-700 hover:text-brand-600 dark:text-brand-400" rel="noopener noreferrer" target="_blank"><?php esc_html_e( 'Əlaqə', 'studyinturkey' ); ?></a>
		</div>
	<?php endif; ?>
</article>
