<?php
/**
 * WP_List_Table for languages admin screen.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class SIT_Languages_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct(
            [
                'singular' => 'language',
                'plural'   => 'languages',
                'ajax'     => false,
            ]
        );
    }

    public function get_columns(): array {
        return [
            'code'        => esc_html__( 'Kod', 'studyinturkey' ),
            'name'        => esc_html__( 'Ad (EN)', 'studyinturkey' ),
            'native_name' => esc_html__( 'Yerli ad', 'studyinturkey' ),
            'locale'      => esc_html__( 'Locale', 'studyinturkey' ),
            'direction'   => esc_html__( 'İstiqamət', 'studyinturkey' ),
            'sort_order'  => esc_html__( 'Sıra', 'studyinturkey' ),
            'is_active'   => esc_html__( 'Aktiv', 'studyinturkey' ),
            'is_default'  => esc_html__( 'Default', 'studyinturkey' ),
        ];
    }

    protected function get_sortable_columns(): array {
        return [
            'code'       => [ 'code', true ],
            'name'       => [ 'name', false ],
            'sort_order' => [ 'sort_order', true ],
        ];
    }

    protected function column_default( $item, $column_name ) {
        return isset( $item->$column_name ) ? esc_html( (string) $item->$column_name ) : '';
    }

    protected function column_code( $item ): string {
        $base = admin_url( 'admin.php?page=sit-languages' );
        $edit = add_query_arg(
            [
                'action'  => 'edit',
                'lang_id' => (int) $item->id,
            ],
            $base
        );

        $actions = [
            'edit' => sprintf(
                '<a href="%s">%s</a>',
                esc_url( $edit ),
                esc_html__( 'Redaktə', 'studyinturkey' )
            ),
        ];

        $delete_url = wp_nonce_url(
            add_query_arg(
                [
                    'action'  => 'delete',
                    'lang_id' => (int) $item->id,
                ],
                $base
            ),
            'sit_languages_admin',
            '_sit_nonce'
        );
        $actions['delete'] = sprintf(
            '<a href="%s" class="sit-lang-delete" onclick="return confirm(\'%s\');">%s</a>',
            esc_url( $delete_url ),
            esc_js( __( 'Bu dili silmək istədiyinizə əminsiniz?', 'studyinturkey' ) ),
            esc_html__( 'Sil', 'studyinturkey' )
        );

        $title = sprintf( '<strong><a href="%s">%s</a></strong>', esc_url( $edit ), esc_html( $item->code ) );

        return $title . $this->row_actions( $actions );
    }

    protected function column_direction( $item ): string {
        $dir = ( 'rtl' === $item->direction ) ? 'RTL' : 'LTR';
        return esc_html( $dir );
    }

    protected function column_is_active( $item ): string {
        $base   = admin_url( 'admin.php?page=sit-languages' );
        $active = (int) $item->is_active === 1;

        if ( $active ) {
            $url = wp_nonce_url(
                add_query_arg(
                    [
                        'action'  => 'deactivate',
                        'lang_id' => (int) $item->id,
                    ],
                    $base
                ),
                'sit_languages_admin',
                '_sit_nonce'
            );
            return sprintf(
                '<a href="%s" class="button button-small">%s</a>',
                esc_url( $url ),
                esc_html__( 'Deaktiv et', 'studyinturkey' )
            );
        }

        $url = wp_nonce_url(
            add_query_arg(
                [
                    'action'  => 'activate',
                    'lang_id' => (int) $item->id,
                ],
                $base
            ),
            'sit_languages_admin',
            '_sit_nonce'
        );
        return sprintf(
            '<a href="%s" class="button button-small button-primary">%s</a>',
            esc_url( $url ),
            esc_html__( 'Aktiv et', 'studyinturkey' )
        );
    }

    protected function column_is_default( $item ): string {
        if ( (int) $item->is_default === 1 ) {
            return '<span class="dashicons dashicons-yes-alt" style="color:#059669;" aria-hidden="true"></span> '
                . esc_html__( 'Bəli', 'studyinturkey' );
        }
        $base = admin_url( 'admin.php?page=sit-languages' );
        $url  = wp_nonce_url(
            add_query_arg(
                [
                    'action'  => 'set_default',
                    'lang_id' => (int) $item->id,
                ],
                $base
            ),
            'sit_languages_admin',
            '_sit_nonce'
        );
        return sprintf(
            '<a href="%s" class="button-link">%s</a>',
            esc_url( $url ),
            esc_html__( 'Default et', 'studyinturkey' )
        );
    }

    public function prepare_items(): void {
        $per_page     = 20;
        $current_page = $this->get_pagenum();

        $all = SIT_Languages::get_all_languages();

        $orderby = isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'sort_order';
        $order   = isset( $_GET['order'] ) && 'desc' === strtolower( sanitize_text_field( wp_unslash( $_GET['order'] ) ) ) ? 'DESC' : 'ASC';

        $allowed = [ 'code', 'name', 'native_name', 'sort_order', 'locale' ];
        if ( ! in_array( $orderby, $allowed, true ) ) {
            $orderby = 'sort_order';
        }

        usort(
            $all,
            static function ( $a, $b ) use ( $orderby, $order ) {
                $va = $a->$orderby ?? '';
                $vb = $b->$orderby ?? '';
                if ( is_numeric( $va ) && is_numeric( $vb ) ) {
                    $cmp = (int) $va <=> (int) $vb;
                } else {
                    $cmp = strcasecmp( (string) $va, (string) $vb );
                }
                return 'DESC' === $order ? -$cmp : $cmp;
            }
        );

        $total = count( $all );
        $all   = array_slice( $all, ( $current_page - 1 ) * $per_page, $per_page );

        $this->set_pagination_args(
            [
                'total_items' => $total,
                'per_page'    => $per_page,
            ]
        );

        $this->items = $all;

        $this->_column_headers = [
            $this->get_columns(),
            [],
            $this->get_sortable_columns(),
            $this->get_primary_column_name(),
        ];
    }

    public function no_items(): void {
        esc_html_e( 'Heç bir dil tapılmadı.', 'studyinturkey' );
    }
}
