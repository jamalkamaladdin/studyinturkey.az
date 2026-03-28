<?php
/**
 * Template Name: SIT — Viza dəstəyi
 * Template Post Type: page
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$pid   = get_the_ID();
	$title = sit_theme_get_post_title( $pid );
	?>
	<main id="main-content" class="flex-1">
		<div class="border-b border-slate-200 bg-gradient-to-br from-slate-900 via-slate-800 to-brand-900 py-14 text-white lg:py-16">
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
				<p class="mt-6 text-sm font-medium uppercase tracking-wider text-brand-200"><?php esc_html_e( 'Təhsil yolunda', 'studyinturkey' ); ?></p>
				<h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl"><?php echo esc_html( $title ); ?></h1>
				<p class="mt-4 max-w-2xl text-slate-300"><?php esc_html_e( 'Türkiyə tələbə vizası və sənədlər haqqında ümumi məlumat bu səhifədə toplanır.', 'studyinturkey' ); ?></p>
			</div>
		</div>
		<div class="sit-container py-12 lg:py-16">
			<div class="mx-auto max-w-3xl">
				<div class="sit-entry-content text-slate-700 leading-relaxed">
					<?php echo sit_theme_get_post_content_filtered( $pid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="mt-10 rounded-2xl border border-brand-100 bg-brand-50/60 p-6">
					<p class="text-sm font-medium text-brand-900"><?php esc_html_e( 'Qeyd', 'studyinturkey' ); ?></p>
					<p class="mt-2 text-sm text-slate-700 leading-relaxed">
						<?php esc_html_e( 'Viza qaydaları dəyişə bilər. Rəsmi konsulluq və universitet təlimatlarını yoxlayın; dəqiq məsləhət üçün bizimlə əlaqə saxlayın.', 'studyinturkey' ); ?>
					</p>
					<?php
					$contact = get_pages(
						[
							'meta_key'   => '_wp_page_template',
							'meta_value' => 'page-templates/sit-contact.php',
							'number'     => 1,
						]
					);
					if ( ! empty( $contact[0] ) ) {
						$url = sit_theme_localize_url( get_permalink( $contact[0] ) );
						printf(
							'<a href="%s" class="mt-4 inline-flex font-semibold text-brand-800 hover:text-brand-700">%s →</a>',
							esc_url( $url ),
							esc_html__( 'Əlaqə səhifəsi', 'studyinturkey' )
						);
					}
					?>
				</div>
			</div>
		</div>
	</main>
	<?php
endwhile;

get_footer();
