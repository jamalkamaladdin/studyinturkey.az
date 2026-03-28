<?php
/**
 * Program CPT və degree_type / program_language / field_of_study taxonomiyaları.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Program_CPT {

    public const POST_TYPE = 'program';

    public const TAX_DEGREE = 'degree_type';

    public const TAX_LANG = 'program_language';

    public const TAX_FIELD = 'field_of_study';

    public static function register(): void {
        self::register_post_type();
        self::register_taxonomies();
    }

    private static function register_post_type(): void {
        $labels = [
            'name'                  => _x( 'Proqramlar', 'post type general name', 'studyinturkey' ),
            'singular_name'         => _x( 'Proqram', 'post type singular name', 'studyinturkey' ),
            'menu_name'             => _x( 'Proqramlar', 'admin menu', 'studyinturkey' ),
            'add_new'               => _x( 'Yeni əlavə et', 'program', 'studyinturkey' ),
            'add_new_item'          => __( 'Yeni proqram', 'studyinturkey' ),
            'edit_item'             => __( 'Proqramı redaktə et', 'studyinturkey' ),
            'new_item'              => __( 'Yeni proqram', 'studyinturkey' ),
            'view_item'             => __( 'Proqramı görüntülə', 'studyinturkey' ),
            'search_items'          => __( 'Proqram axtar', 'studyinturkey' ),
            'not_found'             => __( 'Proqram tapılmadı', 'studyinturkey' ),
            'not_found_in_trash'    => __( 'Zibil qutusunda yoxdur', 'studyinturkey' ),
            'all_items'             => __( 'Bütün proqramlar', 'studyinturkey' ),
            'archives'              => __( 'Proqram arxivi', 'studyinturkey' ),
            'attributes'            => __( 'Proqram atributları', 'studyinturkey' ),
            'insert_into_item'      => __( 'Proqrama əlavə et', 'studyinturkey' ),
            'uploaded_to_this_item' => __( 'Bu proqrama yüklənib', 'studyinturkey' ),
            'filter_items_list'     => __( 'Proqram siyahısını süz', 'studyinturkey' ),
            'items_list_navigation' => __( 'Proqram siyahısı naviqasiyası', 'studyinturkey' ),
            'items_list'            => __( 'Proqram siyahısı', 'studyinturkey' ),
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
            'rest_base'           => 'programs',
            'menu_position'       => 22,
            'menu_icon'           => 'dashicons-welcome-learn-more',
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'supports'            => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
            'has_archive'         => true,
            'rewrite'             => [
                'slug'       => 'proqramlar',
                'with_front' => false,
            ],
            'query_var'           => true,
        ];

        register_post_type( self::POST_TYPE, $args );
    }

    private static function register_taxonomies(): void {
        $degree_labels = [
            'name'              => _x( 'Dərəcə növləri', 'taxonomy general name', 'studyinturkey' ),
            'singular_name'     => _x( 'Dərəcə növü', 'taxonomy singular name', 'studyinturkey' ),
            'search_items'      => __( 'Dərəcə axtar', 'studyinturkey' ),
            'all_items'         => __( 'Bütün dərəcələr', 'studyinturkey' ),
            'parent_item'       => __( 'Üst dərəcə', 'studyinturkey' ),
            'parent_item_colon' => __( 'Üst dərəcə:', 'studyinturkey' ),
            'edit_item'         => __( 'Dərəcəni redaktə et', 'studyinturkey' ),
            'update_item'       => __( 'Dərəcəni yenilə', 'studyinturkey' ),
            'add_new_item'      => __( 'Yeni dərəcə', 'studyinturkey' ),
            'new_item_name'     => __( 'Yeni dərəcə adı', 'studyinturkey' ),
            'menu_name'         => __( 'Dərəcə', 'studyinturkey' ),
        ];

        register_taxonomy(
            self::TAX_DEGREE,
            [ self::POST_TYPE ],
            [
                'labels'            => $degree_labels,
                'hierarchical'      => true,
                'public'            => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_nav_menus' => true,
                'show_in_rest'      => true,
                'rewrite'           => [
                    'slug'         => 'derece-novu',
                    'with_front'   => false,
                    'hierarchical' => true,
                ],
            ]
        );

        $lang_labels = [
            'name'          => _x( 'Proqram dilləri', 'taxonomy general name', 'studyinturkey' ),
            'singular_name' => _x( 'Proqram dili', 'taxonomy singular name', 'studyinturkey' ),
            'search_items'  => __( 'Dil axtar', 'studyinturkey' ),
            'all_items'     => __( 'Bütün dillər', 'studyinturkey' ),
            'edit_item'     => __( 'Dili redaktə et', 'studyinturkey' ),
            'update_item'   => __( 'Dili yenilə', 'studyinturkey' ),
            'add_new_item'  => __( 'Yeni dil', 'studyinturkey' ),
            'new_item_name' => __( 'Yeni dil adı', 'studyinturkey' ),
            'menu_name'     => __( 'Proqram dili', 'studyinturkey' ),
        ];

        register_taxonomy(
            self::TAX_LANG,
            [ self::POST_TYPE ],
            [
                'labels'            => $lang_labels,
                'hierarchical'      => true,
                'public'            => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_nav_menus' => true,
                'show_in_rest'      => true,
                'rewrite'           => [
                    'slug'       => 'proqram-dili',
                    'with_front' => false,
                ],
            ]
        );

        $field_labels = [
            'name'              => _x( 'İxtisas sahələri', 'taxonomy general name', 'studyinturkey' ),
            'singular_name'     => _x( 'İxtisas sahəsi', 'taxonomy singular name', 'studyinturkey' ),
            'search_items'      => __( 'Sahə axtar', 'studyinturkey' ),
            'all_items'         => __( 'Bütün sahələr', 'studyinturkey' ),
            'parent_item'       => __( 'Üst sahə', 'studyinturkey' ),
            'parent_item_colon' => __( 'Üst sahə:', 'studyinturkey' ),
            'edit_item'         => __( 'Sahəni redaktə et', 'studyinturkey' ),
            'update_item'       => __( 'Sahəni yenilə', 'studyinturkey' ),
            'add_new_item'      => __( 'Yeni sahə', 'studyinturkey' ),
            'new_item_name'     => __( 'Yeni sahə adı', 'studyinturkey' ),
            'menu_name'         => __( 'İxtisas sahəsi', 'studyinturkey' ),
        ];

        register_taxonomy(
            self::TAX_FIELD,
            [ self::POST_TYPE ],
            [
                'labels'            => $field_labels,
                'hierarchical'      => true,
                'public'            => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_nav_menus' => true,
                'show_in_rest'      => true,
                'rewrite'           => [
                    'slug'         => 'ixtisas-sahesi',
                    'with_front'   => false,
                    'hierarchical' => true,
                ],
            ]
        );
    }
}
