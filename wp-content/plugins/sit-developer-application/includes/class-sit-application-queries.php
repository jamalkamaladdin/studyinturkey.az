<?php
/**
 * Müraciət sorğuları (istifadəçi portalı üçün).
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Application_Queries {

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function get_applications_by_user_id( int $user_id ): array {
        global $wpdb;

        if ( $user_id <= 0 ) {
            return [];
        }

        $table = SIT_Application_Db::applications_table();

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC",
                $user_id
            ),
            ARRAY_A
        );
        // phpcs:enable

        return is_array( $rows ) ? $rows : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get_application_for_user( int $application_id, int $user_id ): ?array {
        global $wpdb;

        if ( $application_id <= 0 || $user_id <= 0 ) {
            return null;
        }

        $table = SIT_Application_Db::applications_table();

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE id = %d AND user_id = %d LIMIT 1",
                $application_id,
                $user_id
            ),
            ARRAY_A
        );
        // phpcs:enable

        return is_array( $row ) ? $row : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function get_documents_by_application_id( int $application_id ): array {
        global $wpdb;

        if ( $application_id <= 0 ) {
            return [];
        }

        $table = SIT_Application_Db::documents_table();

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, document_type, file_name, mime_type, file_size, created_at FROM {$table} WHERE application_id = %d ORDER BY id ASC",
                $application_id
            ),
            ARRAY_A
        );
        // phpcs:enable

        return is_array( $rows ) ? $rows : [];
    }

    /**
     * Admin üçün sənəd siyahısı (file_path daxil).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function get_documents_full_by_application_id( int $application_id ): array {
        global $wpdb;

        if ( $application_id <= 0 ) {
            return [];
        }

        $table = SIT_Application_Db::documents_table();

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE application_id = %d ORDER BY id ASC",
                $application_id
            ),
            ARRAY_A
        );
        // phpcs:enable

        return is_array( $rows ) ? $rows : [];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get_application_by_id( int $application_id ): ?array {
        global $wpdb;

        if ( $application_id <= 0 ) {
            return null;
        }

        $table = SIT_Application_Db::applications_table();

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE id = %d LIMIT 1",
                $application_id
            ),
            ARRAY_A
        );
        // phpcs:enable

        return is_array( $row ) ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function get_document_by_id( int $document_id ): ?array {
        global $wpdb;

        if ( $document_id <= 0 ) {
            return null;
        }

        $table = SIT_Application_Db::documents_table();

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE id = %d LIMIT 1",
                $document_id
            ),
            ARRAY_A
        );
        // phpcs:enable

        return is_array( $row ) ? $row : null;
    }

    /**
     * Admin siyahısı: səhifələmə və axtarış.
     *
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public static function get_applications_paginated( int $per_page, int $paged, string $search, string $orderby, string $order ): array {
        global $wpdb;

        $table   = SIT_Application_Db::applications_table();
        $allowed = [ 'id', 'created_at', 'status', 'applicant_name', 'applicant_email' ];
        if ( ! in_array( $orderby, $allowed, true ) ) {
            $orderby = 'created_at';
        }
        $order = strtoupper( $order ) === 'ASC' ? 'ASC' : 'DESC';

        $where_parts = [ '1=1' ];
        $params      = [];

        $search = trim( $search );
        if ( '' !== $search ) {
            $where_parts[] = '(applicant_name LIKE %s OR applicant_email LIKE %s OR applicant_phone LIKE %s OR id = %d)';
            $like          = '%' . $wpdb->esc_like( $search ) . '%';
            $params[]      = $like;
            $params[]      = $like;
            $params[]      = $like;
            $params[]      = ctype_digit( $search ) ? (int) $search : 0;
        }

        $where_sql = implode( ' AND ', $where_parts );

        // phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if ( ! empty( $params ) ) {
            $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
            $total     = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) );
        } else {
            $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}" );
        }

        $per_page = max( 1, $per_page );
        $paged    = max( 1, $paged );
        $offset   = ( $paged - 1 ) * $per_page;

        $data_params   = $params;
        $data_params[] = $per_page;
        $data_params[] = $offset;

        $sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";

        if ( ! empty( $params ) ) {
            $items = $wpdb->get_results( $wpdb->prepare( $sql, $data_params ), ARRAY_A );
        } else {
            $items = $wpdb->get_results( $wpdb->prepare( $sql, $per_page, $offset ), ARRAY_A );
        }
        // phpcs:enable

        return [
            'items' => is_array( $items ) ? $items : [],
            'total' => $total,
        ];
    }
}
