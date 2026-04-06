<?php
/**
 * Universitet əlavə sahələri (meta) və media seçiciləri.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_University_Meta {

    public const NONCE_ACTION = 'sit_university_meta_save';

    public const NONCE_NAME = 'sit_university_meta_nonce';

    private const META_KEYS = [
        'sit_tuition_fee_min',
        'sit_student_count',
        'sit_founded_year',
        'sit_global_ranking',
        'sit_rating',
        'sit_website_url',
        'sit_logo_id',
        'sit_cover_image_id',
    ];

    public static function register(): void {
        foreach ( self::META_KEYS as $key ) {
            register_post_meta(
                SIT_University_CPT::POST_TYPE,
                $key,
                [
                    'type'              => self::meta_schema_type( $key ),
                    'single'            => true,
                    'show_in_rest'      => true,
                    'sanitize_callback' => [ __CLASS__, 'sanitize_meta' ],
                    'auth_callback'   => [ __CLASS__, 'auth_meta' ],
                ]
            );
        }

        add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
        add_action( 'save_post_' . SIT_University_CPT::POST_TYPE, [ __CLASS__, 'save_meta' ], 10, 2 );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
    }

    /**
     * @param string $key Meta açarı.
     */
    private static function meta_schema_type( string $key ): string {
        if ( in_array( $key, [ 'sit_logo_id', 'sit_cover_image_id', 'sit_student_count', 'sit_founded_year', 'sit_global_ranking' ], true ) ) {
            return 'integer';
        }
        if ( in_array( $key, [ 'sit_tuition_fee_min', 'sit_rating' ], true ) ) {
            return 'number';
        }
        return 'string';
    }

    /**
     * register_post_meta üçün (və save zamanı) təmizləmə.
     *
     * @param mixed  $value Giriş dəyəri.
     * @param string $key   Meta açarı.
     * @return mixed
     */
    public static function sanitize_meta( $value, string $key, string $object_type = '', string $object_subtype = '' ) {
        switch ( $key ) {
            case 'sit_tuition_fee_min':
                $f = is_numeric( $value ) ? (float) $value : 0.0;
                return max( 0, round( $f, 2 ) );
            case 'sit_student_count':
            case 'sit_founded_year':
            case 'sit_global_ranking':
                return absint( $value );
            case 'sit_rating':
                $r = is_numeric( $value ) ? (float) $value : 0.0;
                return min( 5, max( 0, round( $r, 2 ) ) );
            case 'sit_website_url':
                return esc_url_raw( (string) $value );
            case 'sit_logo_id':
            case 'sit_cover_image_id':
                $id = absint( $value );
                if ( $id && 'attachment' !== get_post_type( $id ) ) {
                    return 0;
                }
                return $id;
            default:
                return $value;
        }
    }

    public static function auth_meta( bool $allowed, string $meta_key, int $post_id ): bool {
        return current_user_can( 'edit_post', $post_id );
    }

    public static function add_meta_boxes(): void {
        add_meta_box(
            'sit_university_details',
            __( 'Universitet məlumatları', 'studyinturkey' ),
            [ __CLASS__, 'render_meta_box' ],
            SIT_University_CPT::POST_TYPE,
            'normal',
            'high'
        );
    }

    /**
     * @param WP_Post $post Post obyekti.
     */
    public static function render_meta_box( WP_Post $post ): void {
        wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

        $tuition   = get_post_meta( $post->ID, 'sit_tuition_fee_min', true );
        $students  = get_post_meta( $post->ID, 'sit_student_count', true );
        $founded   = get_post_meta( $post->ID, 'sit_founded_year', true );
        $ranking   = get_post_meta( $post->ID, 'sit_global_ranking', true );
        $rating    = get_post_meta( $post->ID, 'sit_rating', true );
        $website   = get_post_meta( $post->ID, 'sit_website_url', true );
        $logo_id   = (int) get_post_meta( $post->ID, 'sit_logo_id', true );
        $cover_id  = (int) get_post_meta( $post->ID, 'sit_cover_image_id', true );

        $tuition  = '' !== $tuition && null !== $tuition ? esc_attr( (string) $tuition ) : '';
        $students = $students ? (string) absint( $students ) : '';
        $founded  = $founded ? (string) absint( $founded ) : '';
        $ranking  = $ranking ? (string) absint( $ranking ) : '';
        $rating   = '' !== $rating && null !== $rating ? esc_attr( (string) $rating ) : '';
        $website  = $website ? esc_attr( (string) $website ) : '';

        ?>
        <div class="sit-univ-meta-grid">
            <p>
                <label for="sit_tuition_fee_min"><strong><?php esc_html_e( 'Minimal illik ödəniş (USD)', 'studyinturkey' ); ?></strong></label><br />
                <input type="number" step="0.01" min="0" class="small-text" id="sit_tuition_fee_min" name="sit_tuition_fee_min" value="<?php echo esc_attr( $tuition ); ?>" />
            </p>
            <p>
                <label for="sit_student_count"><strong><?php esc_html_e( 'Tələbə sayı', 'studyinturkey' ); ?></strong></label><br />
                <input type="number" min="0" class="small-text" id="sit_student_count" name="sit_student_count" value="<?php echo esc_attr( $students ); ?>" />
            </p>
            <p>
                <label for="sit_founded_year"><strong><?php esc_html_e( 'Təsis ili', 'studyinturkey' ); ?></strong></label><br />
                <input type="number" min="1000" max="2100" class="small-text" id="sit_founded_year" name="sit_founded_year" value="<?php echo esc_attr( $founded ); ?>" />
            </p>
            <p>
                <label for="sit_global_ranking"><strong><?php esc_html_e( 'Qlobal reytinq (yer)', 'studyinturkey' ); ?></strong></label><br />
                <input type="number" min="0" class="small-text" id="sit_global_ranking" name="sit_global_ranking" value="<?php echo esc_attr( $ranking ); ?>" />
            </p>
            <p>
                <label for="sit_rating"><strong><?php esc_html_e( 'Reytinq (0–5)', 'studyinturkey' ); ?></strong></label><br />
                <input type="number" step="0.01" min="0" max="5" class="small-text" id="sit_rating" name="sit_rating" value="<?php echo esc_attr( $rating ); ?>" />
            </p>
            <p>
                <label for="sit_website_url"><strong><?php esc_html_e( 'Vebsayt URL', 'studyinturkey' ); ?></strong></label><br />
                <input type="url" class="large-text" id="sit_website_url" name="sit_website_url" value="<?php echo esc_attr( $website ); ?>" placeholder="https://" />
            </p>
        </div>

        <?php
        self::render_media_field(
            'sit_logo_id',
            __( 'Loqo', 'studyinturkey' ),
            $logo_id
        );
        self::render_media_field(
            'sit_cover_image_id',
            __( 'Örtük şəkli', 'studyinturkey' ),
            $cover_id
        );
    }

    /**
     * @param string $name    Input adı.
     * @param string $label   Etiket.
     * @param int    $att_id  Attachment ID.
     */
    private static function render_media_field( string $name, string $label, int $att_id ): void {
        $preview = '';
        if ( $att_id ) {
            $url = wp_get_attachment_image_url( $att_id, 'medium' );
            if ( $url ) {
                $preview = '<img src="' . esc_url( $url ) . '" alt="" style="max-width:160px;height:auto;display:block;margin-top:8px;" />';
            }
        }
        ?>
        <div class="sit-univ-media-field" data-field="<?php echo esc_attr( $name ); ?>" style="margin:1em 0;">
            <label><strong><?php echo esc_html( $label ); ?></strong></label>
            <div class="sit-univ-media-preview" style="margin:8px 0;"><?php echo $preview; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $att_id ); ?>" class="sit-univ-media-input" />
            <button type="button" class="button sit-univ-media-select"><?php esc_html_e( 'Şəkil seç', 'studyinturkey' ); ?></button>
            <button type="button" class="button sit-univ-media-clear"><?php esc_html_e( 'Təmizlə', 'studyinturkey' ); ?></button>
        </div>
        <?php
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

        $fields = [
            'sit_tuition_fee_min',
            'sit_student_count',
            'sit_founded_year',
            'sit_global_ranking',
            'sit_rating',
            'sit_website_url',
            'sit_logo_id',
            'sit_cover_image_id',
        ];

        foreach ( $fields as $key ) {
            if ( ! array_key_exists( $key, $_POST ) ) {
                continue;
            }

            $raw = wp_unslash( $_POST[ $key ] );
            if ( '' === $raw || null === $raw ) {
                delete_post_meta( $post_id, $key );
                continue;
            }

            $val = self::sanitize_meta( $raw, $key );
            update_post_meta( $post_id, $key, $val );
        }
    }

    public static function enqueue_admin_assets( string $hook ): void {
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
            return;
        }
        $screen = get_current_screen();
        if ( ! $screen || SIT_University_CPT::POST_TYPE !== $screen->post_type ) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_script(
            'sit-university-meta-admin',
            SIT_DEVELOPER_URL . 'assets/js/sit-university-meta-admin.js',
            [ 'jquery' ],
            SIT_DEVELOPER_VERSION,
            true
        );

        wp_localize_script(
            'sit-university-meta-admin',
            'sitUniversityMeta',
            [
                'frameTitle' => __( 'Şəkil seçin', 'studyinturkey' ),
                'frameButton' => __( 'Seç', 'studyinturkey' ),
            ]
        );
    }
}
