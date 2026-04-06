<?php
/**
 * Yataqxana, kampus, təqaüd, FAQ, rəy CPT-ləri (universitet alt menyusu).
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Extra_Cpts {

    public const DORMITORY = 'dormitory';

    public const CAMPUS = 'campus';

    public const SCHOLARSHIP = 'scholarship';

    public const FAQ = 'faq';

    public const REVIEW = 'review';

    /**
     * @return string[]
     */
    public static function all(): array {
        return [
            self::DORMITORY,
            self::CAMPUS,
            self::SCHOLARSHIP,
            self::FAQ,
            self::REVIEW,
        ];
    }

    public static function register(): void {
        $parent = 'edit.php?post_type=' . SIT_University_CPT::POST_TYPE;

        self::register_dormitory( $parent );
        self::register_campus( $parent );
        self::register_scholarship( $parent );
        self::register_faq( $parent );
        self::register_review( $parent );
    }

    private static function register_dormitory( string $parent ): void {
        $labels = [
            'name'               => _x( 'Yataqxanalar', 'post type general name', 'studyinturkey' ),
            'singular_name'      => _x( 'Yataqxana', 'post type singular name', 'studyinturkey' ),
            'menu_name'          => _x( 'Yataqxanalar', 'admin menu', 'studyinturkey' ),
            'add_new'            => __( 'Yeni yataqxana', 'studyinturkey' ),
            'add_new_item'       => __( 'Yeni yataqxana', 'studyinturkey' ),
            'edit_item'          => __( 'Yataqxananı redaktə et', 'studyinturkey' ),
            'new_item'           => __( 'Yeni yataqxana', 'studyinturkey' ),
            'view_item'          => __( 'Yataqxananı görüntülə', 'studyinturkey' ),
            'search_items'       => __( 'Yataqxana axtar', 'studyinturkey' ),
            'not_found'          => __( 'Tapılmadı', 'studyinturkey' ),
            'not_found_in_trash' => __( 'Zibil qutusunda yoxdur', 'studyinturkey' ),
            'all_items'          => __( 'Bütün yataqxanalar', 'studyinturkey' ),
        ];

        register_post_type(
            self::DORMITORY,
            [
                'labels'             => $labels,
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => $parent,
                'show_in_rest'       => true,
                'rest_base'          => 'dormitories',
                'menu_icon'          => 'dashicons-admin-home',
                'capability_type'    => 'post',
                'map_meta_cap'       => true,
                'hierarchical'       => false,
                'supports'           => [ 'title', 'editor', 'revisions' ],
                'has_archive'        => false,
                'rewrite'            => [
                    'slug'       => 'yataqxana',
                    'with_front' => false,
                ],
            ]
        );
    }

    private static function register_campus( string $parent ): void {
        $labels = [
            'name'               => _x( 'Kampuslar', 'post type general name', 'studyinturkey' ),
            'singular_name'      => _x( 'Kampus', 'post type singular name', 'studyinturkey' ),
            'menu_name'          => _x( 'Kampuslar', 'admin menu', 'studyinturkey' ),
            'add_new'            => __( 'Yeni kampus', 'studyinturkey' ),
            'add_new_item'       => __( 'Yeni kampus', 'studyinturkey' ),
            'edit_item'          => __( 'Kampusu redaktə et', 'studyinturkey' ),
            'new_item'           => __( 'Yeni kampus', 'studyinturkey' ),
            'view_item'          => __( 'Kampusu görüntülə', 'studyinturkey' ),
            'search_items'       => __( 'Kampus axtar', 'studyinturkey' ),
            'not_found'          => __( 'Tapılmadı', 'studyinturkey' ),
            'not_found_in_trash' => __( 'Zibil qutusunda yoxdur', 'studyinturkey' ),
            'all_items'          => __( 'Bütün kampuslar', 'studyinturkey' ),
        ];

        register_post_type(
            self::CAMPUS,
            [
                'labels'             => $labels,
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => $parent,
                'show_in_rest'       => true,
                'rest_base'          => 'campuses',
                'menu_icon'          => 'dashicons-location-alt',
                'capability_type'    => 'post',
                'map_meta_cap'       => true,
                'hierarchical'       => false,
                'supports'           => [ 'title', 'editor', 'revisions' ],
                'has_archive'        => false,
                'rewrite'            => [
                    'slug'       => 'kampus',
                    'with_front' => false,
                ],
            ]
        );
    }

    private static function register_scholarship( string $parent ): void {
        $labels = [
            'name'               => _x( 'Təqaüdlər', 'post type general name', 'studyinturkey' ),
            'singular_name'      => _x( 'Təqaüd', 'post type singular name', 'studyinturkey' ),
            'menu_name'          => _x( 'Təqaüdlər', 'admin menu', 'studyinturkey' ),
            'add_new'            => __( 'Yeni təqaüd', 'studyinturkey' ),
            'add_new_item'       => __( 'Yeni təqaüd', 'studyinturkey' ),
            'edit_item'          => __( 'Təqaüdü redaktə et', 'studyinturkey' ),
            'new_item'           => __( 'Yeni təqaüd', 'studyinturkey' ),
            'view_item'          => __( 'Təqaüdü görüntülə', 'studyinturkey' ),
            'search_items'       => __( 'Təqaüd axtar', 'studyinturkey' ),
            'not_found'          => __( 'Tapılmadı', 'studyinturkey' ),
            'not_found_in_trash' => __( 'Zibil qutusunda yoxdur', 'studyinturkey' ),
            'all_items'          => __( 'Bütün təqaüdlər', 'studyinturkey' ),
        ];

        register_post_type(
            self::SCHOLARSHIP,
            [
                'labels'             => $labels,
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => $parent,
                'show_in_rest'       => true,
                'rest_base'          => 'scholarships',
                'menu_icon'          => 'dashicons-awards',
                'capability_type'    => 'post',
                'map_meta_cap'       => true,
                'hierarchical'       => false,
                'supports'           => [ 'title', 'editor', 'revisions' ],
                'has_archive'        => false,
                'rewrite'            => [
                    'slug'       => 'teqaud',
                    'with_front' => false,
                ],
            ]
        );
    }

    private static function register_faq( string $parent ): void {
        $labels = [
            'name'               => _x( 'FAQ', 'post type general name', 'studyinturkey' ),
            'singular_name'      => _x( 'FAQ', 'post type singular name', 'studyinturkey' ),
            'menu_name'          => _x( 'FAQ', 'admin menu', 'studyinturkey' ),
            'add_new'            => __( 'Yeni sual', 'studyinturkey' ),
            'add_new_item'       => __( 'Yeni sual', 'studyinturkey' ),
            'edit_item'          => __( 'Sualı redaktə et', 'studyinturkey' ),
            'new_item'           => __( 'Yeni sual', 'studyinturkey' ),
            'view_item'          => __( 'Sualı görüntülə', 'studyinturkey' ),
            'search_items'       => __( 'Sual axtar', 'studyinturkey' ),
            'not_found'          => __( 'Tapılmadı', 'studyinturkey' ),
            'not_found_in_trash' => __( 'Zibil qutusunda yoxdur', 'studyinturkey' ),
            'all_items'          => __( 'Bütün suallar', 'studyinturkey' ),
        ];

        register_post_type(
            self::FAQ,
            [
                'labels'             => $labels,
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => $parent,
                'show_in_rest'       => true,
                'rest_base'          => 'faq',
                'menu_icon'          => 'dashicons-editor-help',
                'capability_type'    => 'post',
                'map_meta_cap'       => true,
                'hierarchical'       => false,
                'supports'           => [ 'title', 'editor', 'revisions' ],
                'has_archive'        => false,
                'rewrite'            => [
                    'slug'       => 'faq',
                    'with_front' => false,
                ],
            ]
        );
    }

    private static function register_review( string $parent ): void {
        $labels = [
            'name'               => _x( 'Rəylər', 'post type general name', 'studyinturkey' ),
            'singular_name'      => _x( 'Rəy', 'post type singular name', 'studyinturkey' ),
            'menu_name'          => _x( 'Rəylər', 'admin menu', 'studyinturkey' ),
            'add_new'            => __( 'Yeni rəy', 'studyinturkey' ),
            'add_new_item'       => __( 'Yeni rəy', 'studyinturkey' ),
            'edit_item'          => __( 'Rəyi redaktə et', 'studyinturkey' ),
            'new_item'           => __( 'Yeni rəy', 'studyinturkey' ),
            'view_item'          => __( 'Rəyi görüntülə', 'studyinturkey' ),
            'search_items'       => __( 'Rəy axtar', 'studyinturkey' ),
            'not_found'          => __( 'Tapılmadı', 'studyinturkey' ),
            'not_found_in_trash' => __( 'Zibil qutusunda yoxdur', 'studyinturkey' ),
            'all_items'          => __( 'Bütün rəylər', 'studyinturkey' ),
        ];

        register_post_type(
            self::REVIEW,
            [
                'labels'             => $labels,
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => $parent,
                'show_in_rest'       => true,
                'rest_base'          => 'reviews',
                'menu_icon'          => 'dashicons-star-filled',
                'capability_type'    => 'post',
                'map_meta_cap'       => true,
                'hierarchical'       => false,
                'supports'           => [ 'title', 'editor', 'revisions' ],
                'has_archive'        => false,
                'rewrite'            => [
                    'slug'       => 'rey',
                    'with_front' => false,
                ],
            ]
        );
    }
}
