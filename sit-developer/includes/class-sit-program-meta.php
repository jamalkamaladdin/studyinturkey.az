<?php
/**
 * Proqram əlavə sahələri: ödəniş, müddət, universitet, təqaüd.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Program_Meta {

    public const NONCE_ACTION = 'sit_program_meta_save';

    public const NONCE_NAME = 'sit_program_meta_nonce';

    private const META_KEYS = [
        'sit_tuition_fee',
        'sit_duration',
        'sit_university_id',
        'sit_scholarship_available',
    ];

    public static function register(): void {
        foreach ( self::META_KEYS as $key ) {
            register_post_meta(
                SIT_Program_CPT::POST_TYPE,
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
        add_action( 'save_post_' . SIT_Program_CPT::POST_TYPE, [ __CLASS__, 'save_meta' ], 10, 2 );
    }

    private static function meta_schema_type( string $key ): string {
        if ( 'sit_university_id' === $key ) {
            return 'integer';
        }
        if ( 'sit_scholarship_available' === $key ) {
            return 'boolean';
        }
        if ( 'sit_tuition_fee' === $key ) {
            return 'number';
        }
        return 'string';
    }

    /**
     * @param mixed  $value Giriş dəyəri.
     * @param string $key   Meta açarı.
     * @return mixed
     */
    public static function sanitize_meta( $value, string $key, string $object_type = '', string $object_subtype = '' ) {
        switch ( $key ) {
            case 'sit_tuition_fee':
                $f = is_numeric( $value ) ? (float) $value : 0.0;
                return max( 0, round( $f, 2 ) );
            case 'sit_duration':
                return sanitize_text_field( (string) $value );
            case 'sit_university_id':
                $id = absint( $value );
                if ( ! $id ) {
                    return 0;
                }
                if ( SIT_University_CPT::POST_TYPE !== get_post_type( $id ) ) {
                    return 0;
                }
                return $id;
            case 'sit_scholarship_available':
                if ( true === $value || 1 === $value || '1' === $value || 'true' === $value ) {
                    return true;
                }
                return false;
            default:
                return $value;
        }
    }

    public static function auth_meta( bool $allowed, string $meta_key, int $post_id ): bool {
        return current_user_can( 'edit_post', $post_id );
    }

    public static function add_meta_boxes(): void {
        add_meta_box(
            'sit_program_details',
            __( 'Proqram məlumatları', 'studyinturkey' ),
            [ __CLASS__, 'render_meta_box' ],
            SIT_Program_CPT::POST_TYPE,
            'normal',
            'high'
        );
    }

    /**
     * @param WP_Post $post Post obyekti.
     */
    public static function render_meta_box( WP_Post $post ): void {
        wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

        $tuition    = get_post_meta( $post->ID, 'sit_tuition_fee', true );
        $duration   = get_post_meta( $post->ID, 'sit_duration', true );
        $univ_id    = (int) get_post_meta( $post->ID, 'sit_university_id', true );
        $scholarship = get_post_meta( $post->ID, 'sit_scholarship_available', true );
        $sch_checked = (bool) $scholarship;

        $tuition  = '' !== $tuition && null !== $tuition ? esc_attr( (string) $tuition ) : '';
        $duration = $duration ? esc_attr( (string) $duration ) : '';

        $universities = get_posts(
            [
                'post_type'      => SIT_University_CPT::POST_TYPE,
                'post_status'    => [ 'publish', 'draft', 'pending', 'private' ],
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
            ]
        );

        ?>
        <p>
            <label for="sit_university_id"><strong><?php esc_html_e( 'Universitet', 'studyinturkey' ); ?></strong></label><br />
            <select name="sit_university_id" id="sit_university_id" class="widefat">
                <option value=""><?php esc_html_e( '— Seçin —', 'studyinturkey' ); ?></option>
                <?php foreach ( $universities as $u ) : ?>
                    <option value="<?php echo esc_attr( (string) $u->ID ); ?>" <?php selected( $univ_id, $u->ID ); ?>>
                        <?php echo esc_html( get_the_title( $u ) ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="sit_tuition_fee"><strong><?php esc_html_e( 'İllik ödəniş (USD)', 'studyinturkey' ); ?></strong></label><br />
            <input type="number" step="0.01" min="0" class="small-text" id="sit_tuition_fee" name="sit_tuition_fee" value="<?php echo esc_attr( $tuition ); ?>" />
        </p>
        <p>
            <label for="sit_duration"><strong><?php esc_html_e( 'Müddət', 'studyinturkey' ); ?></strong></label><br />
            <input type="text" class="regular-text" id="sit_duration" name="sit_duration" value="<?php echo esc_attr( $duration ); ?>" placeholder="<?php esc_attr_e( 'Məs: 4 il, 2 semester', 'studyinturkey' ); ?>" />
        </p>
        <p>
            <label>
                <input type="checkbox" name="sit_scholarship_available" value="1" <?php checked( $sch_checked ); ?> />
                <?php esc_html_e( 'Təqaüd mümkündür', 'studyinturkey' ); ?>
            </label>
        </p>
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

        if ( array_key_exists( 'sit_tuition_fee', $_POST ) ) {
            $raw = wp_unslash( $_POST['sit_tuition_fee'] );
            if ( '' === $raw || null === $raw ) {
                delete_post_meta( $post_id, 'sit_tuition_fee' );
            } else {
                update_post_meta( $post_id, 'sit_tuition_fee', self::sanitize_meta( $raw, 'sit_tuition_fee' ) );
            }
        }

        if ( array_key_exists( 'sit_duration', $_POST ) ) {
            $raw = wp_unslash( $_POST['sit_duration'] );
            if ( '' === $raw || null === $raw ) {
                delete_post_meta( $post_id, 'sit_duration' );
            } else {
                update_post_meta( $post_id, 'sit_duration', self::sanitize_meta( $raw, 'sit_duration' ) );
            }
        }

        if ( array_key_exists( 'sit_university_id', $_POST ) ) {
            $raw = wp_unslash( $_POST['sit_university_id'] );
            if ( '' === $raw || '0' === (string) $raw ) {
                delete_post_meta( $post_id, 'sit_university_id' );
            } else {
                update_post_meta( $post_id, 'sit_university_id', self::sanitize_meta( $raw, 'sit_university_id' ) );
            }
        }

        $sch = isset( $_POST['sit_scholarship_available'] ) && '1' === (string) wp_unslash( $_POST['sit_scholarship_available'] );
        if ( $sch ) {
            update_post_meta( $post_id, 'sit_scholarship_available', true );
        } else {
            delete_post_meta( $post_id, 'sit_scholarship_available' );
        }
    }
}
