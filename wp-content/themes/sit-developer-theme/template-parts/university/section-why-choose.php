<?php
/**
 * "Niyə bizi seçməli?" — video + maddələr.
 *
 * @var int $university_id
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 || ! class_exists( 'SIT_University_About_Meta', false ) ) {
	return;
}

$video   = (string) get_post_meta( $university_id, SIT_University_About_Meta::META_WHY_VIDEO, true );
$text    = (string) get_post_meta( $university_id, SIT_University_About_Meta::META_WHY_TEXT, true );
$bullets = (string) get_post_meta( $university_id, SIT_University_About_Meta::META_WHY_BULLETS, true );

if ( '' === trim( $text . $bullets . $video ) ) {
	return;
}

$bullet_list = [];
if ( '' !== trim( $bullets ) ) {
	$bullet_list = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $bullets ) ) );
}

$embed_url = '';
if ( '' !== $video ) {
	if ( preg_match( '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $video, $m ) ) {
		$embed_url = 'https://www.youtube.com/embed/' . $m[1];
	} elseif ( preg_match( '/vimeo\.com\/(\d+)/', $video, $m ) ) {
		$embed_url = 'https://player.vimeo.com/video/' . $m[1];
	}
}
?>
<section class="scroll-mt-24" id="why-choose" aria-labelledby="sit-why-title">
	<h2 id="sit-why-title" class="text-2xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Niyə bizi seçməli?', 'studyinturkey' ); ?></h2>

	<div class="mt-6 grid gap-8 lg:grid-cols-2 lg:items-start">
		<?php if ( '' !== $embed_url ) : ?>
			<div class="aspect-video w-full overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 shadow-sm dark:border-slate-700">
				<iframe src="<?php echo esc_url( $embed_url ); ?>" class="h-full w-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
			</div>
		<?php endif; ?>

		<div class="<?php echo '' !== $embed_url ? '' : 'lg:col-span-2'; ?>">
			<?php if ( '' !== trim( $text ) ) : ?>
				<p class="text-slate-700 leading-relaxed dark:text-slate-300"><?php echo wp_kses_post( $text ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $bullet_list ) ) : ?>
				<ul class="mt-4 grid gap-3 sm:grid-cols-2">
					<?php foreach ( $bullet_list as $bullet ) : ?>
						<li class="flex items-start gap-2.5 text-slate-700 dark:text-slate-300">
							<svg class="mt-1 h-5 w-5 shrink-0 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
							<span><?php echo esc_html( $bullet ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</section>
