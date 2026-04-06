<?php
/**
 * WP_List_Table: UI string açarları siyahısı.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class SIT_UI_Strings_List_Table extends WP_List_Table {

    private string $search = '';

    public function __construct() {
        parent::__construct(
            [
                'singular' => 'ui_string',
                'plural'   => 'ui_strings',
                'ajax'     => false,
            ]
        );
    }

    public function set_search( string $s ): void {
        $this->search = $s;
    }

    public function get_columns(): array {
        return [
            'string_key' => esc_html__( 'Açar', 'studyinturkey' ),
            'context'    => esc_html__( 'Kontekst', 'studyinturkey' ),
            'preview'    => esc_html__( 'Önizləmə (əsas dil)', 'studyinturkey' ),
        ];
    }

    protected function column_string_key( $item ): string {
        $url  = admin_url( 'admin.php?page=sit-ui-strings&action=edit&key=' . rawurlencode( $item->string_key ) );
        $del  = wp_nonce_url(
            admin_url( 'admin.php?page=sit-ui-strings&action=delete&key=' . rawurlencode( $item->string_key ) ),
            'sit_ui_string_delete_' . $item->string_key,
            '_sit_ui_nonce'
        );
        $title = sprintf( '<strong><a href="%s">%s</a></strong>', esc_url( $url ), esc_html( $item->string_key ) );
        $actions = [
            'edit'   => sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'Redaktə', 'studyinturkey' ) ),
            'delete' => sprintf(
                '<a href="%s" class="submitdelete" onclick="return confirm(\'%s\');">%s</a>',
                esc_url( $del ),
                esc_js( __( 'Silmək istədiyinizə əminsiniz?', 'studyinturkey' ) ),
                esc_html__( 'Sil', 'studyinturkey' )
            ),
        ];
        return $title . $this->row_actions( $actions );
    }

    protected function column_default( $item, $column_name ) {
        if ( 'context' === $column_name ) {
            return esc_html( $item->context );
        }
        if ( 'preview' === $column_name ) {
            $def = SIT_Languages::get_default_language_code() ?: 'az';
            $v   = SIT_Strings::get_value( $item->string_key, $def );
            return esc_html( wp_trim_words( $v, 12, '…' ) );
        }
        return '';
    }

    public function prepare_items(): void {
        $per_page     = 20;
        $current_page = $this->get_pagenum();

        $total = SIT_Strings::count_distinct_keys( $this->search );
        $rows  = SIT_Strings::list_keys_paginated( $current_page, $per_page, $this->search );

        $this->set_pagination_args(
            [
                'total_items' => $total,
                'per_page'    => $per_page,
            ]
        );

        $this->items = $rows;

        $this->_column_headers = [
            $this->get_columns(),
            [],
            [],
            $this->get_primary_column_name(),
        ];
    }

    public function no_items(): void {
        esc_html_e( 'Heç bir UI sətri yoxdur.', 'studyinturkey' );
    }
}
