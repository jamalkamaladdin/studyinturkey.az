<?php
/**
 * University CPT və city / university_type taxonomiyaları.
 *
 * sit-multilang: show_ui=true olduğu üçün tərcümə meta box-ları avtomatik göstərilir.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_University_CPT {

    public const POST_TYPE = 'university';

    public const TAX_CITY = 'city';

    public const TAX_TYPE = 'university_type';

    public static function register(): void {
        self::register_post_type();
        self::register_taxonomies();
    }

    private static function register_post_type(): void {
        $labels = [
            'name'                  => _x( 'Universitetlər', 'post type general name', 'studyinturkey' ),
            'singular_name'         => _x( 'Universitet', 'post type singular name', 'studyinturkey' ),
            'menu_name'             => _x( 'Universitetlər', 'admin menu', 'studyinturkey' ),
            'add_new'               => _x( 'Yeni əlavə et', 'university', 'studyinturkey' ),
            'add_new_item'          => __( 'Yeni universitet', 'studyinturkey' ),
            'edit_item'             => __( 'Universiteti redaktə et', 'studyinturkey' ),
            'new_item'              => __( 'Yeni universitet', 'studyinturkey' ),
            'view_item'             => __( 'Universiteti görüntülə', 'studyinturkey' ),
            'search_items'          => __( 'Universitet axtar', 'studyinturkey' ),
            'not_found'             => __( 'Universitet tapılmadı', 'studyinturkey' ),
            'not_found_in_trash'    => __( 'Zibil qutusunda yoxdur', 'studyinturkey' ),
            'all_items'             => __( 'Bütün universitetlər', 'studyinturkey' ),
            'archives'              => __( 'Universitet arxivi', 'studyinturkey' ),
            'attributes'            => __( 'Universitet atributları', 'studyinturkey' ),
            'insert_into_item'      => __( 'Universitetə əlavə et', 'studyinturkey' ),
            'uploaded_to_this_item' => __( 'Bu universitetə yüklənib', 'studyinturkey' ),
            'filter_items_list'     => __( 'Universitet siyahısını süz', 'studyinturkey' ),
            'items_list_navigation' => __( 'Universitet siyahısı naviqasiyası', 'studyinturkey' ),
            'items_list'            => __( 'Universitet siyahısı', 'studyinturkey' ),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'show_in_rest'        => true,
            'rest_base'           => 'universities',
            'menu_position'       => 21,
            'menu_icon'           => 'dashicons-building',
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'supports'            => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
            'has_archive'         => true,
            'rewrite'             => [
                'slug'       => 'universitetler',
                'with_front' => false,
            ],
            'query_var'           => true,
        ];

        register_post_type( self::POST_TYPE, $args );
    }

    private static function register_taxonomies(): void {
        $city_labels = [
            'name'              => _x( 'Şəhərlər', 'taxonomy general name', 'studyinturkey' ),
            'singular_name'     => _x( 'Şəhər', 'taxonomy singular name', 'studyinturkey' ),
            'search_items'      => __( 'Şəhər axtar', 'studyinturkey' ),
            'all_items'       => __( 'Bütün şəhərlər', 'studyinturkey' ),
            'parent_item'     => __( 'Üst şəhər', 'studyinturkey' ),
            'parent_item_colon' => __( 'Üst şəhər:', 'studyinturkey' ),
            'edit_item'       => __( 'Şəhəri redaktə et', 'studyinturkey' ),
            'update_item'     => __( 'Şəhəri yenilə', 'studyinturkey' ),
            'add_new_item'    => __( 'Yeni şəhər', 'studyinturkey' ),
            'new_item_name'   => __( 'Yeni şəhər adı', 'studyinturkey' ),
            'menu_name'       => __( 'Şəhərlər', 'studyinturkey' ),
        ];

        register_taxonomy(
            self::TAX_CITY,
            [ self::POST_TYPE ],
            [
                'labels'            => $city_labels,
                'hierarchical'      => true,
                'public'            => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_nav_menus' => true,
                'show_in_rest'      => true,
                'rewrite'           => [
                    'slug'         => 'seher',
                    'with_front'   => false,
                    'hierarchical' => true,
                ],
            ]
        );

        $type_labels = [
            'name'          => _x( 'Universitet növləri', 'taxonomy general name', 'studyinturkey' ),
            'singular_name' => _x( 'Universitet növü', 'taxonomy singular name', 'studyinturkey' ),
            'search_items'  => __( 'Növ axtar', 'studyinturkey' ),
            'all_items'     => __( 'Bütün növlər', 'studyinturkey' ),
            'edit_item'     => __( 'Növü redaktə et', 'studyinturkey' ),
            'update_item'   => __( 'Növü yenilə', 'studyinturkey' ),
            'add_new_item'  => __( 'Yeni növ', 'studyinturkey' ),
            'new_item_name' => __( 'Yeni növ adı', 'studyinturkey' ),
            'menu_name'     => __( 'Növlər', 'studyinturkey' ),
        ];

        register_taxonomy(
            self::TAX_TYPE,
            [ self::POST_TYPE ],
            [
                'labels'            => $type_labels,
                'hierarchical'      => true,
                'public'            => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_nav_menus' => true,
                'show_in_rest'      => true,
                'rewrite'           => [
                    'slug'       => 'universitet-novu',
                    'with_front' => false,
                ],
            ]
        );
    }
}
