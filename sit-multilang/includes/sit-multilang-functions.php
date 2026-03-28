<?php
/**
 * Public API helpers for other plugins and themes.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cari sorğu dili (URL prefiksi). Admin və bypass kontekstdə əsas dil.
 */
function sit_get_current_lang(): string {
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        $code = SIT_Languages::get_default_language_code();
        return $code ?: 'az';
    }
    if ( is_admin() && ! wp_doing_ajax() ) {
        $code = SIT_Languages::get_default_language_code();
        return $code ?: 'az';
    }
    if ( ! empty( $GLOBALS['sit_current_lang'] ) && is_string( $GLOBALS['sit_current_lang'] ) ) {
        $g = sanitize_key( $GLOBALS['sit_current_lang'] );
        if ( SIT_Languages::is_valid_code( $g ) ) {
            return $g;
        }
    }
    $code = SIT_Languages::get_default_language_code();
    return $code ?: 'az';
}

/**
 * Get translated field for a post or term.
 * Default dil üçün dəyər WordPress obyektindən oxunur; digər dillər üçün wp_sit_translations.
 *
 * @param int    $object_id   Post ID və ya term_id (term üçün taxonomy ilə uyğun ID).
 * @param string $object_type SIT_Translations::OBJECT_POST və ya OBJECT_TERM.
 * @param string $lang_code   Dil kodu (məs. az, en).
 * @param string $field_name  title|content|excerpt|slug (term üçün excerpt yoxdur).
 * @param string $fallback    Boş olduqda qaytarılacaq dəyər.
 */
function sit_get_translation( int $object_id, string $object_type, string $lang_code, string $field_name, string $fallback = '' ): string {
    $default = SIT_Languages::get_default_language_code();
    if ( $lang_code === $default ) {
        if ( SIT_Translations::OBJECT_POST === $object_type ) {
            $post = get_post( $object_id );
            if ( ! $post ) {
                return $fallback;
            }
            switch ( $field_name ) {
                case SIT_Translations::FIELD_TITLE:
                    return '' !== $post->post_title ? $post->post_title : $fallback;
                case SIT_Translations::FIELD_CONTENT:
                    return '' !== $post->post_content ? $post->post_content : $fallback;
                case SIT_Translations::FIELD_EXCERPT:
                    return '' !== $post->post_excerpt ? $post->post_excerpt : $fallback;
                case SIT_Translations::FIELD_SLUG:
                    return '' !== $post->post_name ? $post->post_name : $fallback;
                default:
                    return $fallback;
            }
        }
        if ( SIT_Translations::OBJECT_TERM === $object_type ) {
            $term = get_term( $object_id );
            if ( is_wp_error( $term ) || ! $term instanceof WP_Term ) {
                return $fallback;
            }
            switch ( $field_name ) {
                case SIT_Translations::FIELD_TITLE:
                    return '' !== $term->name ? $term->name : $fallback;
                case SIT_Translations::FIELD_CONTENT:
                    return '' !== $term->description ? $term->description : $fallback;
                case SIT_Translations::FIELD_SLUG:
                    return '' !== $term->slug ? $term->slug : $fallback;
                default:
                    return $fallback;
            }
        }
    }

    $value = SIT_Translations::get_field( $object_id, $object_type, $lang_code, $field_name );
    return '' !== $value ? $value : $fallback;
}
