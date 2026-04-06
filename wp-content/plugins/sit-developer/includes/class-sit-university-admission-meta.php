<?php
/**
 * Universitet: dərəcə üzrə qəbul tələbləri (JSON) və beynəlxalq statistikalar.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_University_Admission_Meta {

    public const META_REQUIREMENTS = 'sit_admission_requirements';

    public const META_INTL_TOTAL = 'sit_intl_students_total';

    public const META_INTL_FOREIGN = 'sit_intl_foreign_students';

    public const META_INTL_ACCEPT = 'sit_intl_accept_rate';

    private const NONCE_ACTION = 'sit_univ_admission_meta_save';

    private const NONCE_NAME = 'sit_univ_admission_meta_nonce';

    public static function register(): void {
        register_post_meta(
            SIT_University_CPT::POST_TYPE,
            self::META_REQUIREMENTS,
            [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => false,
                'sanitize_callback' => [ __CLASS__, 'sanitize_requirements_json' ],
                'auth_callback'     => [ __CLASS__, 'auth_meta' ],
            ]
        );
        foreach ( [ self::META_INTL_TOTAL, self::META_INTL_FOREIGN, self::META_INTL_ACCEPT ] as $key ) {
            register_post_meta(
                SIT_University_CPT::POST_TYPE,
                $key,
                [
                    'type'              => 'string',
                    'single'            => true,
                    'show_in_rest'      => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'auth_callback'     => [ __CLASS__, 'auth_meta' ],
                ]
            );
        }

        add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
        add_action( 'save_post_' . SIT_University_CPT::POST_TYPE, [ __CLASS__, 'save_meta' ], 10, 2 );
    }

    /**
     * @param mixed $value Giriş.
     */
    public static function sanitize_requirements_json( $value ): string {
        if ( ! is_string( $value ) ) {
            return '';
        }
        $value = trim( $value );
        if ( '' === $value ) {
            return '';
        }
        $decoded = json_decode( $value, true );
        if ( ! is_array( $decoded ) ) {
            return '';
        }
        $clean = self::sanitize_requirements_array( $decoded );
        return wp_json_encode( $clean );
    }

    /**
     * @param array<string, mixed> $data Giriş.
     * @return array<string, array<string, mixed>>
     */
    private static function sanitize_requirements_array( array $data ): array {
        $out = [];
        foreach ( $data as $slug => $block ) {
            if ( ! is_string( $slug ) || ! is_array( $block ) ) {
                continue;
            }
            $slug = sanitize_title( $slug );
            if ( '' === $slug ) {
                continue;
            }
            $steps = [];
            if ( isset( $block['steps'] ) && is_array( $block['steps'] ) ) {
                foreach ( $block['steps'] as $s ) {
                    if ( is_string( $s ) && '' !== trim( $s ) ) {
                        $steps[] = wp_kses_post( $s );
                    }
                }
            }
            $docs = [];
            if ( isset( $block['documents'] ) && is_array( $block['documents'] ) ) {
                foreach ( $block['documents'] as $d ) {
                    if ( is_string( $d ) && '' !== trim( $d ) ) {
                        $docs[] = sanitize_text_field( $d );
                    }
                }
            }
            $out[ $slug ] = [
                'steps'           => $steps,
                'documents'       => $docs,
                'intake_title'    => isset( $block['intake_title'] ) ? sanitize_text_field( (string) $block['intake_title'] ) : '',
                'intake_start'    => isset( $block['intake_start'] ) ? sanitize_text_field( (string) $block['intake_start'] ) : '',
                'intake_deadline' => isset( $block['intake_deadline'] ) ? sanitize_text_field( (string) $block['intake_deadline'] ) : '',
            ];
        }
        return $out;
    }

    public static function auth_meta( bool $allowed, string $meta_key, int $post_id ): bool {
        return current_user_can( 'edit_post', $post_id );
    }

    public static function add_meta_boxes(): void {
        add_meta_box(
            'sit_univ_admission_requirements',
            __( 'Qəbul tələbləri (dərəcə üzrə)', 'studyinturkey' ),
            [ __CLASS__, 'render_meta_box' ],
            SIT_University_CPT::POST_TYPE,
            'normal',
            'default'
        );
    }

    /**
     * @param WP_Post $post Post.
     */
    public static function render_meta_box( WP_Post $post ): void {
        wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

        $terms = get_terms(
            [
                'taxonomy'   => SIT_Program_CPT::TAX_DEGREE,
                'hide_empty' => false,
            ]
        );
        if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
            $terms = [];
        }

        $raw = get_post_meta( $post->ID, self::META_REQUIREMENTS, true );
        $data = [];
        if ( is_string( $raw ) && '' !== $raw ) {
            $decoded = json_decode( $raw, true );
            if ( is_array( $decoded ) ) {
                $data = $decoded;
            }
        }

        $intl_total   = (string) get_post_meta( $post->ID, self::META_INTL_TOTAL, true );
        $intl_foreign = (string) get_post_meta( $post->ID, self::META_INTL_FOREIGN, true );
        $intl_accept  = (string) get_post_meta( $post->ID, self::META_INTL_ACCEPT, true );

        ?>
        <p class="description"><?php esc_html_e( 'Hər dərəcə üçün addımlar, sənədlər və qəbul pəncərəsi. Yalnız bu universitetdə həmin dərəcəli proqram varsa, saytda göstərilir.', 'studyinturkey' ); ?></p>
        <fieldset style="margin:1em 0;padding:12px;border:1px solid #c3c4c7;">
            <legend><strong><?php esc_html_e( 'Beynəlxalq tələbə (statistika, mətn)', 'studyinturkey' ); ?></strong></legend>
            <p>
                <label><?php esc_html_e( 'Ümumi tələbələr (məs: 8000+)', 'studyinturkey' ); ?></label><br />
                <input type="text" class="regular-text" name="sit_intl_students_total" value="<?php echo esc_attr( $intl_total ); ?>" />
            </p>
            <p>
                <label><?php esc_html_e( 'Xarici tələbələr (məs: 2348+)', 'studyinturkey' ); ?></label><br />
                <input type="text" class="regular-text" name="sit_intl_foreign_students" value="<?php echo esc_attr( $intl_foreign ); ?>" />
            </p>
            <p>
                <label><?php esc_html_e( 'Qəbul faizi (məs: 99%)', 'studyinturkey' ); ?></label><br />
                <input type="text" class="regular-text" name="sit_intl_accept_rate" value="<?php echo esc_attr( $intl_accept ); ?>" />
            </p>
        </fieldset>
        <?php
        foreach ( $terms as $term ) {
            if ( ! $term instanceof WP_Term ) {
                continue;
            }
            $slug = $term->slug;
            $blk  = isset( $data[ $slug ] ) && is_array( $data[ $slug ] ) ? $data[ $slug ] : [];
            $steps = isset( $blk['steps'] ) && is_array( $blk['steps'] ) ? $blk['steps'] : [ '', '', '' ];
            while ( count( $steps ) < 3 ) {
                $steps[] = '';
            }
            $docs_lines = '';
            if ( isset( $blk['documents'] ) && is_array( $blk['documents'] ) ) {
                $docs_lines = implode( "\n", array_map( 'strval', $blk['documents'] ) );
            }
            $it = isset( $blk['intake_title'] ) ? (string) $blk['intake_title'] : '';
            $is = isset( $blk['intake_start'] ) ? (string) $blk['intake_start'] : '';
            $id = isset( $blk['intake_deadline'] ) ? (string) $blk['intake_deadline'] : '';
            ?>
            <details style="margin:12px 0;padding:8px;border:1px solid #dcdcde;">
                <summary><strong><?php echo esc_html( $term->name ); ?> <code><?php echo esc_html( $slug ); ?></code></strong></summary>
                <?php for ( $i = 0; $i < 3; $i++ ) : ?>
                    <p>
                        <label><?php printf( /* translators: %d step number */ esc_html__( 'Addım %d (HTML icazəli)', 'studyinturkey' ), $i + 1 ); ?></label><br />
                        <textarea class="large-text" rows="3" name="sit_adm_step[<?php echo esc_attr( $slug ); ?>][]"><?php echo esc_textarea( isset( $steps[ $i ] ) ? (string) $steps[ $i ] : '' ); ?></textarea>
                    </p>
                <?php endfor; ?>
                <p>
                    <label><?php esc_html_e( 'Sənədlər (hər sətirdə bir)', 'studyinturkey' ); ?></label><br />
                    <textarea class="large-text" rows="5" name="sit_adm_docs[<?php echo esc_attr( $slug ); ?>]"><?php echo esc_textarea( $docs_lines ); ?></textarea>
                </p>
                <p>
                    <label><?php esc_html_e( 'Qəbul pəncərəsi başlığı (məs: Fall 2026)', 'studyinturkey' ); ?></label><br />
                    <input type="text" class="regular-text" name="sit_adm_intake_title[<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( $it ); ?>" />
                </p>
                <p>
                    <label><?php esc_html_e( 'Başlanğıc tarixi (mətn)', 'studyinturkey' ); ?></label><br />
                    <input type="text" class="regular-text" name="sit_adm_intake_start[<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( $is ); ?>" placeholder="Jul 7, 2026" />
                </p>
                <p>
                    <label><?php esc_html_e( 'Son müraciət (mətn)', 'studyinturkey' ); ?></label><br />
                    <input type="text" class="regular-text" name="sit_adm_intake_deadline[<?php echo esc_attr( $slug ); ?>]" value="<?php echo esc_attr( $id ); ?>" placeholder="Aug 15, 2026" />
                </p>
            </details>
            <?php
        }
    }

    /**
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post.
     */
    public static function save_meta( int $post_id, WP_Post $post ): void {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        foreach ( [ self::META_INTL_TOTAL => 'sit_intl_students_total', self::META_INTL_FOREIGN => 'sit_intl_foreign_students', self::META_INTL_ACCEPT => 'sit_intl_accept_rate' ] as $meta => $field ) {
            if ( ! isset( $_POST[ $field ] ) ) {
                continue;
            }
            $v = sanitize_text_field( wp_unslash( (string) $_POST[ $field ] ) );
            if ( '' === $v ) {
                delete_post_meta( $post_id, $meta );
            } else {
                update_post_meta( $post_id, $meta, $v );
            }
        }

        $built = [];
        if ( isset( $_POST['sit_adm_step'] ) && is_array( $_POST['sit_adm_step'] ) ) {
            foreach ( wp_unslash( $_POST['sit_adm_step'] ) as $slug => $steps_in ) {
                $slug = sanitize_title( (string) $slug );
                if ( '' === $slug || ! is_array( $steps_in ) ) {
                    continue;
                }
                $steps = [];
                foreach ( $steps_in as $s ) {
                    $steps[] = is_string( $s ) ? wp_kses_post( $s ) : '';
                }
                if ( ! isset( $built[ $slug ] ) ) {
                    $built[ $slug ] = [
                        'steps'           => [],
                        'documents'       => [],
                        'intake_title'    => '',
                        'intake_start'    => '',
                        'intake_deadline' => '',
                    ];
                }
                $built[ $slug ]['steps'] = array_values(
                    array_filter(
                        $steps,
                        static function ( $x ) {
                            return is_string( $x ) && '' !== trim( wp_strip_all_tags( $x ) );
                        }
                    )
                );
            }
        }

        if ( isset( $_POST['sit_adm_docs'] ) && is_array( $_POST['sit_adm_docs'] ) ) {
            foreach ( wp_unslash( $_POST['sit_adm_docs'] ) as $slug => $text ) {
                $slug = sanitize_title( (string) $slug );
                if ( '' === $slug || ! is_string( $text ) ) {
                    continue;
                }
                $lines = array_filter(
                    array_map( 'trim', preg_split( '/\r\n|\r|\n/', $text ) ),
                    static function ( $l ) {
                        return '' !== $l;
                    }
                );
                $docs = array_map( 'sanitize_text_field', $lines );
                if ( ! isset( $built[ $slug ] ) ) {
                    $built[ $slug ] = [
                        'steps'           => [],
                        'documents'       => [],
                        'intake_title'    => '',
                        'intake_start'    => '',
                        'intake_deadline' => '',
                    ];
                }
                $built[ $slug ]['documents'] = $docs;
            }
        }

        $intake_fields = [
            'sit_adm_intake_title'    => 'intake_title',
            'sit_adm_intake_start'    => 'intake_start',
            'sit_adm_intake_deadline' => 'intake_deadline',
        ];
        foreach ( $intake_fields as $post_field => $key ) {
            if ( ! isset( $_POST[ $post_field ] ) || ! is_array( $_POST[ $post_field ] ) ) {
                continue;
            }
            foreach ( wp_unslash( $_POST[ $post_field ] ) as $slug => $val ) {
                $slug = sanitize_title( (string) $slug );
                if ( '' === $slug ) {
                    continue;
                }
                if ( ! isset( $built[ $slug ] ) ) {
                    $built[ $slug ] = [
                        'steps'           => [],
                        'documents'       => [],
                        'intake_title'    => '',
                        'intake_start'    => '',
                        'intake_deadline' => '',
                    ];
                }
                $built[ $slug ][ $key ] = sanitize_text_field( (string) $val );
            }
        }

        $built = self::prune_empty_degree_blocks( $built );

        if ( [] === $built ) {
            delete_post_meta( $post_id, self::META_REQUIREMENTS );
            return;
        }

        $json = wp_json_encode( self::sanitize_requirements_array( $built ) );
        update_post_meta( $post_id, self::META_REQUIREMENTS, $json );
    }

    /**
     * Dekodlanmış qəbul massivi (tema üçün).
     *
     * @param int $university_id Universitet ID.
     * @return array<string, array{steps:string[],documents:string[],intake_title:string,intake_start:string,intake_deadline:string}>
     */
    /**
     * @param array<string, array<string, mixed>> $built Giriş.
     * @return array<string, array<string, mixed>>
     */
    private static function prune_empty_degree_blocks( array $built ): array {
        $out = [];
        foreach ( $built as $slug => $blk ) {
            if ( ! is_array( $blk ) ) {
                continue;
            }
            $steps = isset( $blk['steps'] ) && is_array( $blk['steps'] ) ? $blk['steps'] : [];
            $docs  = isset( $blk['documents'] ) && is_array( $blk['documents'] ) ? $blk['documents'] : [];
            $has_step = false;
            foreach ( $steps as $s ) {
                if ( is_string( $s ) && '' !== trim( wp_strip_all_tags( $s ) ) ) {
                    $has_step = true;
                    break;
                }
            }
            $has_doc = [] !== array_filter( $docs, static fn( $d ) => is_string( $d ) && '' !== trim( $d ) );
            $it      = isset( $blk['intake_title'] ) ? trim( (string) $blk['intake_title'] ) : '';
            $is      = isset( $blk['intake_start'] ) ? trim( (string) $blk['intake_start'] ) : '';
            $id      = isset( $blk['intake_deadline'] ) ? trim( (string) $blk['intake_deadline'] ) : '';
            if ( $has_step || $has_doc || '' !== $it || '' !== $is || '' !== $id ) {
                $out[ $slug ] = $blk;
            }
        }
        return $out;
    }

    public static function get_requirements_decoded( int $university_id ): array {
        if ( $university_id < 1 ) {
            return [];
        }
        $raw = get_post_meta( $university_id, self::META_REQUIREMENTS, true );
        if ( ! is_string( $raw ) || '' === $raw ) {
            return [];
        }
        $decoded = json_decode( $raw, true );
        return is_array( $decoded ) ? $decoded : [];
    }
}
