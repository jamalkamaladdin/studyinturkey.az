<?php
/**
 * REST API: sit/v1 — proqram və universitet siyahıları (filtr, səhifələmə, keş).
 */

defined( 'ABSPATH' ) || exit;

final class SIT_REST_API {

    private const CACHE_PREFIX = 'sit_cache_prog';

    private const CACHE_PREFIX_UNIV = 'sit_cache_univ';

    public static function register(): void {
        add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
        add_action( 'save_post', [ __CLASS__, 'maybe_bump_on_save' ], 99, 2 );
        add_action( 'set_object_terms', [ __CLASS__, 'maybe_bump_on_terms' ], 10, 6 );
    }

    public static function bump_cache(): void {
        update_option( 'sit_rest_cache_ver', (int) get_option( 'sit_rest_cache_ver', 1 ) + 1 );
    }

    /**
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post.
     */
    public static function maybe_bump_on_save( int $post_id, WP_Post $post ): void {
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        if ( in_array( $post->post_type, [ SIT_Program_CPT::POST_TYPE, SIT_University_CPT::POST_TYPE ], true ) ) {
            self::bump_cache();
        }
    }

    /**
     * @param int    $object_id  Obyekt ID.
     * @param mixed  $terms      Termlər.
     * @param array  $tt_ids     Term taxonomy ID-ləri.
     * @param string $taxonomy   Taxonomiya.
     * @param bool   $append     Əlavə rejimi.
     * @param array  $old_tt_ids Köhnə tt_id-lər.
     */
    public static function maybe_bump_on_terms( $object_id, $terms, $tt_ids, string $taxonomy, $append, $old_tt_ids ): void {
        $tracked = [
            SIT_University_CPT::TAX_CITY,
            SIT_University_CPT::TAX_TYPE,
            SIT_Program_CPT::TAX_DEGREE,
            SIT_Program_CPT::TAX_LANG,
            SIT_Program_CPT::TAX_FIELD,
        ];
        if ( in_array( $taxonomy, $tracked, true ) ) {
            self::bump_cache();
        }
    }

    public static function register_routes(): void {
        register_rest_route(
            'sit/v1',
            '/programs',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ __CLASS__, 'get_programs' ],
                'permission_callback' => '__return_true',
                'args'                => self::collection_args(),
            ]
        );

        register_rest_route(
            'sit/v1',
            '/programs/(?P<id>\d+)',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ __CLASS__, 'get_program_single' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'id' => [
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ],
                    'lang' => [
                        'sanitize_callback' => 'sanitize_key',
                    ],
                ],
            ]
        );

        register_rest_route(
            'sit/v1',
            '/universities',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ __CLASS__, 'get_universities' ],
                'permission_callback' => '__return_true',
                'args'                => self::university_collection_args(),
            ]
        );

        register_rest_route(
            'sit/v1',
            '/universities/(?P<id>\d+)',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ __CLASS__, 'get_university_single' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'id' => [
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ],
                    'lang' => [
                        'sanitize_callback' => 'sanitize_key',
                    ],
                ],
            ]
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function collection_args(): array {
        return array_merge(
            self::pagination_and_lang_args(),
            [
                'degree'    => [ 'sanitize_callback' => [ __CLASS__, 'sanitize_slug_csv' ] ],
                'language'  => [ 'sanitize_callback' => [ __CLASS__, 'sanitize_slug_csv' ] ],
                'field'     => [ 'sanitize_callback' => [ __CLASS__, 'sanitize_slug_csv' ] ],
                'city'      => [ 'sanitize_callback' => [ __CLASS__, 'sanitize_slug_csv' ] ],
                'price_min' => [
                    'sanitize_callback' => static function ( $v ) {
                        return is_numeric( $v ) ? (float) $v : null;
                    },
                ],
                'price_max' => [
                    'sanitize_callback' => static function ( $v ) {
                        return is_numeric( $v ) ? (float) $v : null;
                    },
                ],
                'sort' => [
                    'default'           => 'date_desc',
                    'sanitize_callback' => 'sanitize_key',
                ],
                'university' => [
                    'sanitize_callback' => 'absint',
                ],
            ]
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function university_collection_args(): array {
        return array_merge(
            self::pagination_and_lang_args(),
            [
                'city'      => [ 'sanitize_callback' => [ __CLASS__, 'sanitize_slug_csv' ] ],
                'type'      => [ 'sanitize_callback' => [ __CLASS__, 'sanitize_slug_csv' ] ],
                'price_min' => [
                    'sanitize_callback' => static function ( $v ) {
                        return is_numeric( $v ) ? (float) $v : null;
                    },
                ],
                'price_max' => [
                    'sanitize_callback' => static function ( $v ) {
                        return is_numeric( $v ) ? (float) $v : null;
                    },
                ],
                'sort' => [
                    'default'           => 'date_desc',
                    'sanitize_callback' => 'sanitize_key',
                ],
            ]
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function pagination_and_lang_args(): array {
        return [
            'page' => [
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ],
            'per_page' => [
                'default'           => 12,
                'sanitize_callback' => static function ( $v ) {
                    $n = absint( $v );
                    return min( 100, max( 1, $n ?: 12 ) );
                },
            ],
            'lang' => [
                'sanitize_callback' => 'sanitize_key',
            ],
        ];
    }

    /**
     * @param mixed $value REST param.
     */
    public static function sanitize_slug_csv( $value ): string {
        if ( is_array( $value ) ) {
            $value = implode( ',', $value );
        }
        return sanitize_text_field( (string) $value );
    }

    /**
     * @param WP_REST_Request $request Sorğu.
     */
    public static function get_programs( WP_REST_Request $request ) {
        $params = [
            'degree'     => (string) $request->get_param( 'degree' ),
            'language'   => (string) $request->get_param( 'language' ),
            'field'      => (string) $request->get_param( 'field' ),
            'city'       => (string) $request->get_param( 'city' ),
            'price_min'  => $request->get_param( 'price_min' ),
            'price_max'  => $request->get_param( 'price_max' ),
            'sort'       => (string) $request->get_param( 'sort' ),
            'university' => absint( $request->get_param( 'university' ) ),
            'page'       => absint( $request->get_param( 'page' ) ) ?: 1,
            'per_page'   => min( 100, max( 1, (int) $request->get_param( 'per_page' ) ?: 12 ) ),
            'lang'       => (string) $request->get_param( 'lang' ),
        ];

        $lang = self::resolve_lang( $params['lang'] );
        $params['lang'] = $lang;

        $cache_ver = (int) get_option( 'sit_rest_cache_ver', 1 );
        $ttl       = (int) apply_filters( 'sit_rest_programs_cache_ttl', 5 * MINUTE_IN_SECONDS );
        $cache_key = self::CACHE_PREFIX . '_' . $cache_ver . '_' . md5( wp_json_encode( $params ) );

        if ( $ttl > 0 ) {
            $cached = get_transient( $cache_key );
            if ( false !== $cached && is_array( $cached ) ) {
                return new WP_REST_Response( $cached['body'], 200, $cached['headers'] ?? [] );
            }
        }

        $query_args = self::build_program_query_args( $params );
        $q          = new WP_Query( $query_args );

        $items = [];
        foreach ( $q->posts as $post ) {
            if ( $post instanceof WP_Post ) {
                $items[] = self::format_program( $post, $lang, false );
            }
        }

        $total       = (int) $q->found_posts;
        $per_page    = (int) $params['per_page'];
        $total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 0;

        $body = [
            'items'        => $items,
            'total'        => $total,
            'total_pages'  => $total_pages,
            'page'         => (int) $params['page'],
            'per_page'     => $per_page,
            'lang'         => $lang,
        ];

        $headers = [
            'X-WP-Total'      => (string) $total,
            'X-WP-TotalPages' => (string) $total_pages,
        ];

        if ( $ttl > 0 ) {
            set_transient( $cache_key, compact( 'body', 'headers' ), $ttl );
        }

        return new WP_REST_Response( $body, 200, $headers );
    }

    /**
     * @param array<string, mixed> $params Parametrlər.
     * @return array<string, mixed>
     */
    private static function build_program_query_args( array $params ): array {
        $page     = max( 1, (int) $params['page'] );
        $per_page = (int) $params['per_page'];

        $args = [
            'post_type'              => SIT_Program_CPT::POST_TYPE,
            'post_status'            => 'publish',
            'posts_per_page'         => $per_page,
            'paged'                  => $page,
            'no_found_rows'          => false,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true,
        ];

        $tax_query = [];
        $degree    = self::csv_to_slugs( $params['degree'] );
        if ( ! empty( $degree ) ) {
            $tax_query[] = [
                'taxonomy' => SIT_Program_CPT::TAX_DEGREE,
                'field'    => 'slug',
                'terms'    => $degree,
                'operator' => 'IN',
            ];
        }
        $language = self::csv_to_slugs( $params['language'] );
        if ( ! empty( $language ) ) {
            $tax_query[] = [
                'taxonomy' => SIT_Program_CPT::TAX_LANG,
                'field'    => 'slug',
                'terms'    => $language,
                'operator' => 'IN',
            ];
        }
        $field = self::csv_to_slugs( $params['field'] );
        if ( ! empty( $field ) ) {
            $tax_query[] = [
                'taxonomy' => SIT_Program_CPT::TAX_FIELD,
                'field'    => 'slug',
                'terms'    => $field,
                'operator' => 'IN',
            ];
        }

        if ( count( $tax_query ) > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query;
        }

        $meta_query = [];

        if ( ! empty( $params['university'] ) ) {
            $meta_query[] = [
                'key'   => 'sit_university_id',
                'value' => (int) $params['university'],
            ];
        }

        $cities = self::csv_to_slugs( $params['city'] );
        if ( ! empty( $cities ) ) {
            $univ_ids = get_posts(
                [
                    'post_type'              => SIT_University_CPT::POST_TYPE,
                    'post_status'            => 'publish',
                    'posts_per_page'         => -1,
                    'fields'                 => 'ids',
                    'no_found_rows'          => true,
                    'update_post_meta_cache' => false,
                    'update_post_term_cache' => false,
                    'tax_query'              => [
                        [
                            'taxonomy' => SIT_University_CPT::TAX_CITY,
                            'field'    => 'slug',
                            'terms'    => $cities,
                            'operator' => 'IN',
                        ],
                    ],
                ]
            );
            if ( empty( $univ_ids ) ) {
                $args['post__in'] = [ 0 ];
            } else {
                $meta_query[] = [
                    'key'     => 'sit_university_id',
                    'value'   => array_map( 'absint', $univ_ids ),
                    'compare' => 'IN',
                    'type'    => 'NUMERIC',
                ];
            }
        }

        $pmin = isset( $params['price_min'] ) && is_numeric( $params['price_min'] ) ? (float) $params['price_min'] : null;
        $pmax = isset( $params['price_max'] ) && is_numeric( $params['price_max'] ) ? (float) $params['price_max'] : null;

        if ( null !== $pmin && null !== $pmax ) {
            $meta_query[] = [
                'key'     => 'sit_tuition_fee',
                'value'   => [ $pmin, $pmax ],
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC',
            ];
        } elseif ( null !== $pmin ) {
            $meta_query[] = [
                'key'     => 'sit_tuition_fee',
                'value'   => $pmin,
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ];
        } elseif ( null !== $pmax ) {
            $meta_query[] = [
                'key'     => 'sit_tuition_fee',
                'value'   => $pmax,
                'compare' => '<=',
                'type'    => 'NUMERIC',
            ];
        }

        if ( count( $meta_query ) > 1 ) {
            $meta_query['relation'] = 'AND';
        }
        if ( ! empty( $meta_query ) ) {
            $args['meta_query'] = $meta_query;
        }

        $sort = sanitize_key( (string) $params['sort'] );
        if ( '' === $sort ) {
            $sort = 'date_desc';
        }

        if ( 'price_asc' === $sort || 'price_desc' === $sort ) {
            $args['meta_key'] = 'sit_tuition_fee';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'price_asc' === $sort ? 'ASC' : 'DESC';
        } elseif ( 'title_asc' === $sort ) {
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
        } elseif ( 'title_desc' === $sort ) {
            $args['orderby'] = 'title';
            $args['order']   = 'DESC';
        } elseif ( 'date_asc' === $sort ) {
            $args['orderby'] = 'date';
            $args['order']   = 'ASC';
        } else {
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
        }

        return $args;
    }

    /**
     * @param WP_REST_Request $request Sorğu.
     */
    public static function get_program_single( WP_REST_Request $request ) {
        $id   = absint( $request->get_param( 'id' ) );
        $lang = self::resolve_lang( (string) $request->get_param( 'lang' ) );
        $post = get_post( $id );
        if ( ! $post instanceof WP_Post || SIT_Program_CPT::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
            return new WP_Error( 'sit_not_found', __( 'Proqram tapılmadı.', 'studyinturkey' ), [ 'status' => 404 ] );
        }
        return new WP_REST_Response( self::format_program( $post, $lang, true ), 200 );
    }

    /**
     * @param WP_REST_Request $request Sorğu.
     */
    public static function get_universities( WP_REST_Request $request ) {
        $params = [
            'city'      => (string) $request->get_param( 'city' ),
            'type'      => (string) $request->get_param( 'type' ),
            'price_min' => $request->get_param( 'price_min' ),
            'price_max' => $request->get_param( 'price_max' ),
            'sort'      => (string) $request->get_param( 'sort' ),
            'page'      => absint( $request->get_param( 'page' ) ) ?: 1,
            'per_page'  => min( 100, max( 1, (int) $request->get_param( 'per_page' ) ?: 12 ) ),
            'lang'      => (string) $request->get_param( 'lang' ),
        ];

        $lang = self::resolve_lang( $params['lang'] );
        $params['lang'] = $lang;

        $cache_ver = (int) get_option( 'sit_rest_cache_ver', 1 );
        $ttl       = (int) apply_filters( 'sit_rest_universities_cache_ttl', 5 * MINUTE_IN_SECONDS );
        $cache_key = self::CACHE_PREFIX_UNIV . '_' . $cache_ver . '_' . md5( wp_json_encode( $params ) );

        if ( $ttl > 0 ) {
            $cached = get_transient( $cache_key );
            if ( false !== $cached && is_array( $cached ) ) {
                return new WP_REST_Response( $cached['body'], 200, $cached['headers'] ?? [] );
            }
        }

        $args = [
            'post_type'              => SIT_University_CPT::POST_TYPE,
            'post_status'            => 'publish',
            'posts_per_page'         => $params['per_page'],
            'paged'                  => max( 1, (int) $params['page'] ),
            'no_found_rows'          => false,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => true,
        ];

        $tax_query = [];
        $cities    = self::csv_to_slugs( $params['city'] );
        if ( ! empty( $cities ) ) {
            $tax_query[] = [
                'taxonomy' => SIT_University_CPT::TAX_CITY,
                'field'    => 'slug',
                'terms'    => $cities,
                'operator' => 'IN',
            ];
        }
        $types = self::csv_to_slugs( $params['type'] );
        if ( ! empty( $types ) ) {
            $tax_query[] = [
                'taxonomy' => SIT_University_CPT::TAX_TYPE,
                'field'    => 'slug',
                'terms'    => $types,
                'operator' => 'IN',
            ];
        }
        if ( count( $tax_query ) > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query;
        }

        $meta_query = [];
        $pmin       = isset( $params['price_min'] ) && is_numeric( $params['price_min'] ) ? (float) $params['price_min'] : null;
        $pmax       = isset( $params['price_max'] ) && is_numeric( $params['price_max'] ) ? (float) $params['price_max'] : null;

        if ( null !== $pmin && null !== $pmax ) {
            $meta_query[] = [
                'key'     => 'sit_tuition_fee_min',
                'value'   => [ $pmin, $pmax ],
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC',
            ];
        } elseif ( null !== $pmin ) {
            $meta_query[] = [
                'key'     => 'sit_tuition_fee_min',
                'value'   => $pmin,
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ];
        } elseif ( null !== $pmax ) {
            $meta_query[] = [
                'key'     => 'sit_tuition_fee_min',
                'value'   => $pmax,
                'compare' => '<=',
                'type'    => 'NUMERIC',
            ];
        }

        if ( ! empty( $meta_query ) ) {
            $args['meta_query'] = $meta_query;
        }

        $sort = sanitize_key( (string) $params['sort'] );
        if ( '' === $sort ) {
            $sort = 'date_desc';
        }

        if ( 'price_asc' === $sort || 'price_desc' === $sort ) {
            $args['meta_key'] = 'sit_tuition_fee_min';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'price_asc' === $sort ? 'ASC' : 'DESC';
        } elseif ( 'title_asc' === $sort ) {
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
        } elseif ( 'title_desc' === $sort ) {
            $args['orderby'] = 'title';
            $args['order']   = 'DESC';
        } elseif ( 'date_asc' === $sort ) {
            $args['orderby'] = 'date';
            $args['order']   = 'ASC';
        } else {
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
        }

        $q = new WP_Query( $args );

        $items = [];
        foreach ( $q->posts as $post ) {
            if ( $post instanceof WP_Post ) {
                $items[] = self::format_university( $post, $lang, false );
            }
        }

        $total       = (int) $q->found_posts;
        $per_page    = (int) $params['per_page'];
        $total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 0;

        $body = [
            'items'       => $items,
            'total'       => $total,
            'total_pages' => $total_pages,
            'page'        => (int) $params['page'],
            'per_page'    => $per_page,
            'lang'        => $lang,
        ];

        $headers = [
            'X-WP-Total'      => (string) $total,
            'X-WP-TotalPages' => (string) $total_pages,
        ];

        if ( $ttl > 0 ) {
            set_transient( $cache_key, compact( 'body', 'headers' ), $ttl );
        }

        return new WP_REST_Response( $body, 200, $headers );
    }

    /**
     * @param WP_REST_Request $request Sorğu.
     */
    public static function get_university_single( WP_REST_Request $request ) {
        $id   = absint( $request->get_param( 'id' ) );
        $lang = self::resolve_lang( (string) $request->get_param( 'lang' ) );
        $post = get_post( $id );
        if ( ! $post instanceof WP_Post || SIT_University_CPT::POST_TYPE !== $post->post_type || 'publish' !== $post->post_status ) {
            return new WP_Error( 'sit_not_found', __( 'Universitet tapılmadı.', 'studyinturkey' ), [ 'status' => 404 ] );
        }
        return new WP_REST_Response( self::format_university( $post, $lang, true ), 200 );
    }

    /**
     * @param string $csv Vergül ilə ayrılmış slug-lar.
     * @return string[]
     */
    private static function csv_to_slugs( string $csv ): array {
        if ( '' === trim( $csv ) ) {
            return [];
        }
        $parts = array_map( 'trim', explode( ',', $csv ) );
        $out   = [];
        foreach ( $parts as $p ) {
            $s = sanitize_title( $p );
            if ( '' !== $s ) {
                $out[] = $s;
            }
        }
        return array_values( array_unique( $out ) );
    }

    private static function resolve_lang( string $lang ): string {
        $default = 'az';
        if ( class_exists( 'SIT_Languages', false ) ) {
            $default = SIT_Languages::get_default_language_code() ?: 'az';
        }
        $l = sanitize_key( $lang );
        if ( '' === $l ) {
            return $default;
        }
        if ( class_exists( 'SIT_Languages', false ) && SIT_Languages::is_valid_code( $l ) ) {
            return $l;
        }
        return $default;
    }

    /**
     * @param WP_Post $post    Proqram.
     * @param string  $lang    Dil kodu.
     * @param bool    $content Tam məzmun.
     * @return array<string, mixed>
     */
    private static function format_program( WP_Post $post, string $lang, bool $content ): array {
        $id = (int) $post->ID;

        $title   = get_the_title( $post );
        $excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
        $slug    = $post->post_name;

        if ( function_exists( 'sit_get_translation' ) && class_exists( 'SIT_Translations', false ) ) {
            $title = sit_get_translation( $id, SIT_Translations::OBJECT_POST, $lang, SIT_Translations::FIELD_TITLE, $title );
            $excerpt = sit_get_translation( $id, SIT_Translations::OBJECT_POST, $lang, SIT_Translations::FIELD_EXCERPT, $excerpt );
            $slug_t = sit_get_translation( $id, SIT_Translations::OBJECT_POST, $lang, SIT_Translations::FIELD_SLUG, $slug );
            if ( '' !== $slug_t ) {
                $slug = $slug_t;
            }
        }

        $link = get_permalink( $post );
        if ( class_exists( 'SIT_Rewrite', false ) ) {
            $link = SIT_Rewrite::localize_url( $link, $lang );
        }

        $fee_raw = get_post_meta( $id, 'sit_tuition_fee', true );
        $fee     = ( '' !== $fee_raw && null !== $fee_raw && is_numeric( $fee_raw ) ) ? (float) $fee_raw : null;

        $row = [
            'id'                    => $id,
            'title'                 => $title,
            'slug'                  => $slug,
            'excerpt'               => wp_strip_all_tags( $excerpt ),
            'link'                  => $link,
            'featured_media_id'     => (int) get_post_thumbnail_id( $post ),
            'tuition_fee'           => $fee,
            'duration'              => (string) get_post_meta( $id, 'sit_duration', true ),
            'university_id'         => (int) get_post_meta( $id, 'sit_university_id', true ),
            'scholarship_available' => (bool) get_post_meta( $id, 'sit_scholarship_available', true ),
            'degree_type'           => self::format_terms_for_post( $id, SIT_Program_CPT::TAX_DEGREE, $lang ),
            'program_language'      => self::format_terms_for_post( $id, SIT_Program_CPT::TAX_LANG, $lang ),
            'field_of_study'        => self::format_terms_for_post( $id, SIT_Program_CPT::TAX_FIELD, $lang ),
        ];

        if ( $content ) {
            $body = $post->post_content;
            if ( function_exists( 'sit_get_translation' ) && class_exists( 'SIT_Translations', false ) ) {
                $body = sit_get_translation( $id, SIT_Translations::OBJECT_POST, $lang, SIT_Translations::FIELD_CONTENT, $body );
            }
            $row['content'] = apply_filters( 'the_content', $body );
        }

        return $row;
    }

    /**
     * @param WP_Post $post    Universitet.
     * @param string  $lang    Dil kodu.
     * @param bool    $content Tam məzmun.
     * @return array<string, mixed>
     */
    private static function format_university( WP_Post $post, string $lang, bool $content ): array {
        $id = (int) $post->ID;

        $title   = get_the_title( $post );
        $excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
        $slug    = $post->post_name;

        if ( function_exists( 'sit_get_translation' ) && class_exists( 'SIT_Translations', false ) ) {
            $title = sit_get_translation( $id, SIT_Translations::OBJECT_POST, $lang, SIT_Translations::FIELD_TITLE, $title );
            $excerpt = sit_get_translation( $id, SIT_Translations::OBJECT_POST, $lang, SIT_Translations::FIELD_EXCERPT, $excerpt );
            $slug_t = sit_get_translation( $id, SIT_Translations::OBJECT_POST, $lang, SIT_Translations::FIELD_SLUG, $slug );
            if ( '' !== $slug_t ) {
                $slug = $slug_t;
            }
        }

        $link = get_permalink( $post );
        if ( class_exists( 'SIT_Rewrite', false ) ) {
            $link = SIT_Rewrite::localize_url( $link, $lang );
        }

        $fee_min_raw = get_post_meta( $id, 'sit_tuition_fee_min', true );
        $fee_min     = ( '' !== $fee_min_raw && null !== $fee_min_raw && is_numeric( $fee_min_raw ) ) ? (float) $fee_min_raw : null;

        $row = [
            'id'                => $id,
            'title'             => $title,
            'slug'              => $slug,
            'excerpt'           => wp_strip_all_tags( $excerpt ),
            'link'              => $link,
            'featured_media_id' => (int) get_post_thumbnail_id( $post ),
            'tuition_fee_min'   => $fee_min,
            'student_count'     => (int) get_post_meta( $id, 'sit_student_count', true ),
            'founded_year'      => (int) get_post_meta( $id, 'sit_founded_year', true ),
            'global_ranking'    => (int) get_post_meta( $id, 'sit_global_ranking', true ),
            'rating'            => self::nullable_float( get_post_meta( $id, 'sit_rating', true ) ),
            'website_url'       => (string) get_post_meta( $id, 'sit_website_url', true ),
            'logo_id'           => (int) get_post_meta( $id, 'sit_logo_id', true ),
            'cover_image_id'    => (int) get_post_meta( $id, 'sit_cover_image_id', true ),
            'city'              => self::format_terms_for_post( $id, SIT_University_CPT::TAX_CITY, $lang ),
            'university_type'   => self::format_terms_for_post( $id, SIT_University_CPT::TAX_TYPE, $lang ),
        ];

        if ( $content ) {
            $body = $post->post_content;
            if ( function_exists( 'sit_get_translation' ) && class_exists( 'SIT_Translations', false ) ) {
                $body = sit_get_translation( $id, SIT_Translations::OBJECT_POST, $lang, SIT_Translations::FIELD_CONTENT, $body );
            }
            $row['content'] = apply_filters( 'the_content', $body );
        }

        return $row;
    }

    /**
     * @param mixed $v Meta dəyəri.
     */
    private static function nullable_float( $v ): ?float {
        if ( '' === $v || null === $v || ! is_numeric( $v ) ) {
            return null;
        }
        return round( (float) $v, 2 );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function format_terms_for_post( int $post_id, string $taxonomy, string $lang ): array {
        $terms = get_the_terms( $post_id, $taxonomy );
        if ( ! is_array( $terms ) || is_wp_error( $terms ) ) {
            return [];
        }
        $out = [];
        foreach ( $terms as $term ) {
            if ( ! $term instanceof WP_Term ) {
                continue;
            }
            $out[] = self::format_term( $term, $lang );
        }
        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private static function format_term( WP_Term $term, string $lang ): array {
        $name = $term->name;
        if ( function_exists( 'sit_get_translation' ) && class_exists( 'SIT_Translations', false ) ) {
            $name = sit_get_translation( (int) $term->term_id, SIT_Translations::OBJECT_TERM, $lang, SIT_Translations::FIELD_TITLE, $name );
        }
        return [
            'id'   => (int) $term->term_id,
            'slug' => $term->slug,
            'name' => $name,
        ];
    }
}
