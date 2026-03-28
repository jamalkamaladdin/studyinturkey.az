<?php
/**
 * Template Name: SIT — Əlaqə
 * Template Post Type: page
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pid   = get_the_ID();
	$title = sit_theme_get_post_title( $pid );

	$contact_status = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( (string) $_GET['contact'] ) ) : '';
	?>
	<main id="main-content" class="flex-1">
		<div class="border-b border-slate-200 bg-gradient-to-br from-brand-700 to-teal-800 py-12 text-white lg:py-14">
			<div class="sit-container">
				<?php
				get_template_part(
					'template-parts/page/breadcrumbs',
					null,
					[
						'items' => [
							[
								'label' => __( 'Ana səhifə', 'studyinturkey' ),
								'url'   => sit_theme_localize_url( home_url( '/' ) ),
							],
							[
								'label' => $title,
								'url'   => '',
							],
						],
					]
				);
				?>
				<h1 class="mt-6 text-3xl font-bold sm:text-4xl"><?php echo esc_html( $title ); ?></h1>
			</div>
		</div>

		<div class="sit-container py-12 lg:py-16">
			<?php if ( 'sent' === $contact_status ) : ?>
				<div class="mb-8 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900" role="status">
					<?php esc_html_e( 'Mesajınız göndərildi. Tezliklə sizinlə əlaqə saxlayacağıq.', 'studyinturkey' ); ?>
				</div>
			<?php elseif ( 'error' === $contact_status ) : ?>
				<div class="mb-8 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
					<?php esc_html_e( 'Göndərmə alınmadı. Məlumatları yoxlayın və yenidən cəhd edin.', 'studyinturkey' ); ?>
				</div>
			<?php endif; ?>

			<div class="grid gap-12 lg:grid-cols-2 lg:gap-14">
				<div>
					<h2 class="text-lg font-semibold text-slate-900"><?php esc_html_e( 'Bizə yazın', 'studyinturkey' ); ?></h2>
					<p class="mt-2 text-sm text-slate-600"><?php esc_html_e( 'Ulduzla işarələnmiş sahələr məcburidir.', 'studyinturkey' ); ?></p>
					<div class="mt-6">
						<?php
						get_template_part(
							'template-parts/page/contact-form',
							null,
							[ 'redirect_url' => sit_theme_localize_url( get_permalink( $pid ) ) ]
						);
						?>
					</div>
				</div>
				<div class="space-y-6">
					<div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-6">
						<h2 class="text-lg font-semibold text-slate-900"><?php esc_html_e( 'Əlaqə məlumatları', 'studyinturkey' ); ?></h2>
						<?php if ( get_option( 'admin_email' ) ) : ?>
							<p class="mt-3 text-sm text-slate-600">
								<span class="font-medium text-slate-800"><?php esc_html_e( 'E-poçt', 'studyinturkey' ); ?>:</span>
								<a href="mailto:<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" class="ms-1"><?php echo esc_html( get_option( 'admin_email' ) ); ?></a>
							</p>
						<?php endif; ?>
						<div class="sit-entry-content mt-4 text-sm text-slate-600 leading-relaxed">
							<?php echo sit_theme_get_post_content_filtered( $pid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>

					<div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
						<?php
						$map_html = apply_filters(
							'sit_theme_contact_map_iframe',
							'<iframe title="' . esc_attr__( 'Xəritə', 'studyinturkey' ) . '" src="https://www.openstreetmap.org/export/embed.html?bbox=28.94%2C41.00%2C29.05%2C41.06&amp;layer=mapnik&amp;marker=41.03,28.99" class="h-72 w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>'
						);
						echo $map_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filter returns HTML.
						?>
						<p class="border-t border-slate-100 bg-white px-4 py-2 text-center text-xs text-slate-500">
							<a href="https://www.openstreetmap.org/?mlat=41.03&amp;mlon=28.99#map=12/41.03/28.99" class="text-brand-700" rel="noopener noreferrer" target="_blank"><?php esc_html_e( 'Böyük xəritədə aç', 'studyinturkey' ); ?></a>
						</p>
					</div>
				</div>
			</div>
		</div>
	</main>
	<?php
endwhile;

get_footer();
