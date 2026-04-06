<?php
/**
 * Admin müraciət siyahısı (WP_List_Table).
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table', false ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

final class SIT_Application_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct(
            [
                'singular' => 'sit_application',
                'plural'   => 'sit_applications',
                'ajax'     => false,
            ]
        );
    }

    public function get_columns(): array {
        return [
            'id'              => __( '№', 'studyinturkey' ),
            'applicant_name'  => __( 'Namizəd', 'studyinturkey' ),
            'applicant_email' => __( 'E-poçt', 'studyinturkey' ),
            'program_id'      => __( 'Proqram', 'studyinturkey' ),
            'status'          => __( 'Status', 'studyinturkey' ),
            'created_at'      => __( 'Tarix', 'studyinturkey' ),
            'actions'         => '',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function get_sortable_columns(): array {
        return [
            'id'         => [ 'id', true ],
            'created_at' => [ 'created_at', true ],
            'status'     => [ 'status', false ],
            'applicant_name' => [ 'applicant_name', false ],
        ];
    }

    protected function column_default( $item, $column_name ) {
        if ( ! is_array( $item ) ) {
            return '';
        }

        switch ( $column_name ) {
            case 'id':
                return isset( $item['id'] ) ? (string) (int) $item['id'] : '';
            case 'applicant_name':
                return isset( $item['applicant_name'] ) ? esc_html( (string) $item['applicant_name'] ) : '';
            case 'applicant_email':
                return isset( $item['applicant_email'] ) ? '<a href="mailto:' . esc_attr( (string) $item['applicant_email'] ) . '">' . esc_html( (string) $item['applicant_email'] ) . '</a>' : '';
            case 'program_id':
                $pid = isset( $item['program_id'] ) ? (int) $item['program_id'] : 0;
                if ( ! $pid ) {
                    return '—';
                }
                $title = get_the_title( $pid );
                $link  = get_edit_post_link( $pid );
                if ( $link ) {
                    return '<a href="' . esc_url( $link ) . '">' . esc_html( $title ? $title : (string) $pid ) . '</a>';
                }
                return esc_html( $title ? $title : (string) $pid );
            case 'status':
                $s = isset( $item['status'] ) ? (string) $item['status'] : '';
                return esc_html( SIT_Application_Account::status_label( $s ) );
            case 'created_at':
                $d = isset( $item['created_at'] ) ? (string) $item['created_at'] : '';
                $fmt = $d ? mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $d, true ) : '';
                return esc_html( is_string( $fmt ) && '' !== $fmt ? $fmt : ( $d ? $d : '—' ) );
            case 'actions':
                $id = isset( $item['id'] ) ? (int) $item['id'] : 0;
                $url = add_query_arg(
                    [
                        'page'       => 'sit-applications',
                        'action'     => 'view',
                        'sit_app_id' => $id,
                    ],
                    admin_url( 'admin.php' )
                );
                return '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Bax', 'studyinturkey' ) . '</a>';
            default:
                return '';
        }
    }

    protected function no_items(): void {
        esc_html_e( 'Müraciət tapılmadı.', 'studyinturkey' );
    }

    public function prepare_items(): void {
        $per_page = $this->get_items_per_page( 'sit_applications_per_page', 20 );
        $paged    = $this->get_pagenum();

        $search = '';
        if ( isset( $_REQUEST['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            $search = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
        }

        $orderby = 'created_at';
        $order   = 'DESC';
        if ( isset( $_REQUEST['orderby'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            $ob = sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
            if ( in_array( $ob, [ 'id', 'created_at', 'status', 'applicant_name', 'applicant_email' ], true ) ) {
                $orderby = $ob;
            }
        }
        if ( isset( $_REQUEST['order'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            $order = 'asc' === strtolower( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) ? 'ASC' : 'DESC'; // phpcs:ignore WordPress.Security.NonceVerification
        }

        $result = SIT_Application_Queries::get_applications_paginated( $per_page, $paged, $search, $orderby, $order );

        $this->items = $result['items'];

        $this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];

        $this->set_pagination_args(
            [
                'total_items' => $result['total'],
                'per_page'    => $per_page,
                'total_pages' => (int) ceil( max( 1, $result['total'] ) / $per_page ),
            ]
        );
    }
}
