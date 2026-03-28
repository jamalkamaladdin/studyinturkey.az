<?php
/**
 * CRUD for wp_sit_translations (posts and terms).
 */

defined( 'ABSPATH' ) || exit;

class SIT_Translations {

    public const OBJECT_POST = 'post';
    public const OBJECT_TERM = 'term';

    public const FIELD_TITLE   = 'title';
    public const FIELD_CONTENT = 'content';
    public const FIELD_EXCERPT = 'excerpt';
    public const FIELD_SLUG    = 'slug';

    /**
     * @return string[]
     */
    public static function get_post_field_names(): array {
        return [
            self::FIELD_TITLE,
            self::FIELD_CONTENT,
            self::FIELD_EXCERPT,
            self::FIELD_SLUG,
        ];
    }

    /**
     * @return string[]
     */
    public static function get_term_field_names(): array {
        return [
            self::FIELD_TITLE,
            self::FIELD_CONTENT,
            self::FIELD_SLUG,
        ];
    }

    /**
     * Get one translated field (non-default language rows only; default uses WP core).
     */
    public static function get_field( int $object_id, string $object_type, string $lang_code, string $field_name ): string {
        global $wpdb;

        $table = SIT_DB::translations_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $val = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT field_value FROM {$table}
                WHERE object_id = %d AND object_type = %s AND lang_code = %s AND field_name = %s
                LIMIT 1",
                $object_id,
                $object_type,
                $lang_code,
                $field_name
            )
        );

        return null !== $val ? (string) $val : '';
    }

    /**
     * All translation rows for an object as [ lang_code => [ field => value ] ].
     * Only languages that have at least one stored field appear; empty langs omitted.
     *
     * @return array<string, array<string, string>>
     */
    public static function get_map_for_object( int $object_id, string $object_type ): array {
        global $wpdb;

        $table = SIT_DB::translations_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT lang_code, field_name, field_value FROM {$table}
                WHERE object_id = %d AND object_type = %s",
                $object_id,
                $object_type
            )
        );

        $map = [];
        if ( ! $rows ) {
            return $map;
        }

        foreach ( $rows as $row ) {
            $lang = $row->lang_code;
            if ( ! isset( $map[ $lang ] ) ) {
                $map[ $lang ] = [];
            }
            $map[ $lang ][ $row->field_name ] = (string) $row->field_value;
        }

        return $map;
    }

    /**
     * Save multiple fields for one language. Empty strings delete the row.
     *
     * @param array<string, string> $fields field_name => value.
     */
    public static function save_language_fields( int $object_id, string $object_type, string $lang_code, array $fields ): void {
        global $wpdb;

        $table = SIT_DB::translations_table();

        foreach ( $fields as $field_name => $value ) {
            $value = is_string( $value ) ? $value : '';

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->delete(
                $table,
                [
                    'object_id'   => $object_id,
                    'object_type' => $object_type,
                    'lang_code'   => $lang_code,
                    'field_name'  => $field_name,
                ]
            );

            if ( '' === trim( $value ) ) {
                continue;
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->insert(
                $table,
                [
                    'object_id'   => $object_id,
                    'object_type' => $object_type,
                    'lang_code'   => $lang_code,
                    'field_name'  => $field_name,
                    'field_value' => $value,
                ]
            );
        }
    }

    /**
     * Remove all translation rows for an object (e.g. on delete).
     */
    public static function delete_for_object( int $object_id, string $object_type ): void {
        global $wpdb;

        $table = SIT_DB::translations_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->delete(
            $table,
            [
                'object_id'   => $object_id,
                'object_type' => $object_type,
            ],
            [ '%d', '%s' ]
        );
    }
}
