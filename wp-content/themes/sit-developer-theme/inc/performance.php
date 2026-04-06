<?php
/**
 * Performans ipucları (skript strategiyası, preconnect).
 *
 * Redis obyekt keşi üçün serverdə wp-content/object-cache.php drop-in (məs. Redis) quraşdırılmalıdır —
 * bu fayl yalnız temanın yüngül optimallaşdırmalarını ehtiva edir.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Google Fonts üçün preconnect (FOIT azaltmaq üçün font URL-də display=swap artıq var).
 */
function sit_theme_performance_resource_hints(): void {
	if ( is_admin() ) {
		return;
	}
	echo '<link rel="preconnect" href="https://fonts.googleapis.com" />' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />' . "\n";
}
add_action( 'wp_head', 'sit_theme_performance_resource_hints', 2 );

/**
 * Əsas şəkil üçün lazy loading (məzmun üçün WP core defolt aktivdir).
 */
function sit_theme_performance_lazy_featured_images( array $attr, WP_Post $attachment, string|array $size ): array {
	if ( ! is_admin() && ! empty( $attr['class'] ) && is_string( $attr['class'] ) && str_contains( $attr['class'], 'wp-post-image' ) ) {
		$attr['loading']  = 'lazy';
		$attr['decoding'] = 'async';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'sit_theme_performance_lazy_featured_images', 10, 3 );
