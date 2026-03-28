<?php
/**
 * Yataqxana, kampus, təqaüd, FAQ, rəy üçün universitet əlaqəsi və əlavə sahələr.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Extra_Meta {

    public const NONCE_NAME = 'sit_extra_meta_nonce';

    public static function register(): void {
        self::register_post_meta_all();

        add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );

        foreach ( SIT_Extra_Cpts::all() as $post_type ) {
            add_action( 'save_post_' . $post_type, [ __CLASS__, 'save_meta' ], 10, 2 );
        }
    }

    private static function register_post_meta_all(): void {
        $defs = [
            SIT_Extra_Cpts::DORMITORY   => [
                'sit_university_id' => 'integer',
                'sit_price'         => 'number',
                'sit_distance'      => 'string',
                'sit_facilities'    => 'string',
            ],
            SIT_Extra_Cpts::CAMPUS      => [
                'sit_university_id' => 'integer',
                'sit_address'       => 'string',
                'sit_latitude'      => 'number',
                'sit_longitude'     => 'number',
            ],
            SIT_Extra_Cpts::SCHOLARSHIP => [
                'sit_university_id' => 'integer',
                'sit_percentage'    => 'number',
                'sit_eligibility'   => 'string',
            ],
            SIT_Extra_Cpts::FAQ         => [
                'sit_university_id' => 'integer',
                'sit_sort_order'    => 'integer',
            ],
            SIT_Extra_Cpts::REVIEW      => [
                'sit_university_id'    => 'integer',
                'sit_rating'           => 'number',
                'sit_student_name'     => 'string',
                'sit_student_country'  => 'string',
            ],
        ];

        foreach ( $defs as $post_type => $keys ) {
            foreach ( $keys as $meta_key => $schema_type ) {
                register_post_meta(
                    $post_type,
                    $meta_key,
                    [
                        'type'              => $schema_type,
                        'single'            => true,
                        'show_in_rest'      => true,
                        'sanitize_callback' => [ __CLASS__, 'sanitize_meta' ],
                        'auth_callback'     => [ __CLASS__, 'auth_meta' ],
                    ]
                );
            }
        }
    }

    /**
     * @param mixed  $value Meta dəyəri.
     * @param string $key   Açar.
     * @return mixed
     */
    public static function sanitize_meta( $value, string $key, string $object_type = '', string $object_subtype = '' ) {
        switch ( $key ) {
            case 'sit_university_id':
                $id = absint( $value );
                if ( ! $id || SIT_University_CPT::POST_TYPE !== get_post_type( $id ) ) {
                    return 0;
                }
                return $id;
            case 'sit_price':
            case 'sit_percentage':
                $f = is_numeric( $value ) ? (float) $value : 0.0;
                if ( 'sit_percentage' === $key ) {
                    return min( 100, max( 0, round( $f, 2 ) ) );
                }
                return max( 0, round( $f, 2 ) );
            case 'sit_rating':
                $r = is_numeric( $value ) ? (float) $value : 0.0;
                return min( 5, max( 0, round( $r, 2 ) ) );
            case 'sit_distance':
                return sanitize_text_field( (string) $value );
            case 'sit_facilities':
            case 'sit_eligibility':
                return sanitize_textarea_field( (string) $value );
            case 'sit_address':
                return sanitize_textarea_field( (string) $value );
            case 'sit_latitude':
                $lat = is_numeric( $value ) ? (float) $value : 0.0;
                return min( 90, max( -90, round( $lat, 7 ) ) );
            case 'sit_longitude':
                $lng = is_numeric( $value ) ? (float) $value : 0.0;
                return min( 180, max( -180, round( $lng, 7 ) ) );
            case 'sit_sort_order':
                return absint( $value );
            case 'sit_student_name':
            case 'sit_student_country':
                return sanitize_text_field( (string) $value );
            default:
                return $value;
        }
    }

    public static function auth_meta( bool $allowed, string $meta_key, int $post_id ): bool {
        return current_user_can( 'edit_post', $post_id );
    }

    public static function add_meta_boxes(): void {
        $titles = [
            SIT_Extra_Cpts::DORMITORY   => __( 'Yataqxana məlumatları', 'studyinturkey' ),
            SIT_Extra_Cpts::CAMPUS      => __( 'Kampus məlumatları', 'studyinturkey' ),
            SIT_Extra_Cpts::SCHOLARSHIP => __( 'Təqaüd məlumatları', 'studyinturkey' ),
            SIT_Extra_Cpts::FAQ         => __( 'FAQ məlumatları', 'studyinturkey' ),
            SIT_Extra_Cpts::REVIEW      => __( 'Rəy məlumatları', 'studyinturkey' ),
        ];

        foreach ( SIT_Extra_Cpts::all() as $pt ) {
            add_meta_box(
                'sit_extra_meta_' . $pt,
                $titles[ $pt ] ?? __( 'Əlavə məlumat', 'studyinturkey' ),
                [ __CLASS__, 'render_meta_box' ],
                $pt,
                'normal',
                'high'
            );
        }
    }

    /**
     * @param WP_Post $post Post.
     */
    public static function render_meta_box( WP_Post $post ): void {
        wp_nonce_field( 'sit_extra_meta_save_' . $post->post_type, self::NONCE_NAME );

        switch ( $post->post_type ) {
            case SIT_Extra_Cpts::DORMITORY:
                self::render_dormitory( $post );
                break;
            case SIT_Extra_Cpts::CAMPUS:
                self::render_campus( $post );
                break;
            case SIT_Extra_Cpts::SCHOLARSHIP:
                self::render_scholarship( $post );
                break;
            case SIT_Extra_Cpts::FAQ:
                self::render_faq( $post );
                break;
            case SIT_Extra_Cpts::REVIEW:
                self::render_review( $post );
                break;
        }
    }

    /**
     * @param WP_Post $post Post.
     */
    private static function render_university_select( WP_Post $post ): void {
        $univ_id = (int) get_post_meta( $post->ID, 'sit_university_id', true );
        $posts   = get_posts(
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
            <label for="sit_extra_university_id"><strong><?php esc_html_e( 'Universitet', 'studyinturkey' ); ?></strong></label><br />
            <select name="sit_university_id" id="sit_extra_university_id" class="widefat">
                <option value=""><?php esc_html_e( '— Seçin —', 'studyinturkey' ); ?></option>
                <?php foreach ( $posts as $u ) : ?>
                    <option value="<?php echo esc_attr( (string) $u->ID ); ?>" <?php selected( $univ_id, $u->ID ); ?>>
                        <?php echo esc_html( get_the_title( $u ) ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    /**
     * @param WP_Post $post Post.
     */
    private static function render_dormitory( WP_Post $post ): void {
        self::render_university_select( $post );
        $price      = get_post_meta( $post->ID, 'sit_price', true );
        $distance   = get_post_meta( $post->ID, 'sit_distance', true );
        $facilities = get_post_meta( $post->ID, 'sit_facilities', true );
        $price      = ( '' !== $price && null !== $price && is_numeric( $price ) ) ? esc_attr( (string) $price ) : '';
        $distance   = $distance ? esc_attr( (string) $distance ) : '';
        ?>
        <p>
            <label for="sit_price"><strong><?php esc_html_e( 'Qiymət (USD / ay)', 'studyinturkey' ); ?></strong></label><br />
            <input type="number" step="0.01" min="0" class="small-text" id="sit_price" name="sit_price" value="<?php echo esc_attr( $price ); ?>" />
        </p>
        <p>
            <label for="sit_distance"><strong><?php esc_html_e( 'Məsafə', 'studyinturkey' ); ?></strong></label><br />
            <input type="text" class="regular-text" id="sit_distance" name="sit_distance" value="<?php echo esc_attr( $distance ); ?>" placeholder="<?php esc_attr_e( 'Məs: 500 m, 2 km', 'studyinturkey' ); ?>" />
        </p>
        <p>
            <label for="sit_facilities"><strong><?php esc_html_e( 'İmkanlar', 'studyinturkey' ); ?></strong></label><br />
            <textarea class="large-text" rows="4" id="sit_facilities" name="sit_facilities"><?php echo esc_textarea( (string) $facilities ); ?></textarea>
        </p>
        <?php
    }

    /**
     * @param WP_Post $post Post.
     */
    private static function render_campus( WP_Post $post ): void {
        self::render_university_select( $post );
        $address = (string) get_post_meta( $post->ID, 'sit_address', true );
        $lat     = get_post_meta( $post->ID, 'sit_latitude', true );
        $lng     = get_post_meta( $post->ID, 'sit_longitude', true );
        $lat_s   = ( '' !== $lat && null !== $lat && is_numeric( $lat ) ) ? esc_attr( (string) $lat ) : '';
        $lng_s   = ( '' !== $lng && null !== $lng && is_numeric( $lng ) ) ? esc_attr( (string) $lng ) : '';
        ?>
        <p>
            <label for="sit_address"><strong><?php esc_html_e( 'Ünvan', 'studyinturkey' ); ?></strong></label><br />
            <textarea class="large-text" rows="3" id="sit_address" name="sit_address"><?php echo esc_textarea( $address ); ?></textarea>
        </p>
        <p>
            <label for="sit_latitude"><strong><?php esc_html_e( 'Enlik (lat)', 'studyinturkey' ); ?></strong></label><br />
            <input type="text" inputmode="decimal" class="regular-text" id="sit_latitude" name="sit_latitude" value="<?php echo esc_attr( $lat_s ); ?>" />
        </p>
        <p>
            <label for="sit_longitude"><strong><?php esc_html_e( 'Uzunluq (lng)', 'studyinturkey' ); ?></strong></label><br />
            <input type="text" inputmode="decimal" class="regular-text" id="sit_longitude" name="sit_longitude" value="<?php echo esc_attr( $lng_s ); ?>" />
        </p>
        <?php
    }

    /**
     * @param WP_Post $post Post.
     */
    private static function render_scholarship( WP_Post $post ): void {
        self::render_university_select( $post );
        $pct  = get_post_meta( $post->ID, 'sit_percentage', true );
        $elig = (string) get_post_meta( $post->ID, 'sit_eligibility', true );
        $pct  = ( '' !== $pct && null !== $pct && is_numeric( $pct ) ) ? esc_attr( (string) $pct ) : '';
        ?>
        <p>
            <label for="sit_percentage"><strong><?php esc_html_e( 'Faiz (%)', 'studyinturkey' ); ?></strong></label><br />
            <input type="number" step="0.01" min="0" max="100" class="small-text" id="sit_percentage" name="sit_percentage" value="<?php echo esc_attr( $pct ); ?>" />
        </p>
        <p>
            <label for="sit_eligibility"><strong><?php esc_html_e( 'Uyğunluq şərtləri', 'studyinturkey' ); ?></strong></label><br />
            <textarea class="large-text" rows="5" id="sit_eligibility" name="sit_eligibility"><?php echo esc_textarea( $elig ); ?></textarea>
        </p>
        <?php
    }

    /**
     * @param WP_Post $post Post.
     */
    private static function render_faq( WP_Post $post ): void {
        self::render_university_select( $post );
        $order = (int) get_post_meta( $post->ID, 'sit_sort_order', true );
        ?>
        <p>
            <label for="sit_sort_order"><strong><?php esc_html_e( 'Sıra nömrəsi', 'studyinturkey' ); ?></strong></label><br />
            <input type="number" min="0" class="small-text" id="sit_sort_order" name="sit_sort_order" value="<?php echo esc_attr( (string) $order ); ?>" />
        </p>
        <?php
    }

    /**
     * @param WP_Post $post Post.
     */
    private static function render_review( WP_Post $post ): void {
        self::render_university_select( $post );
        $rating  = get_post_meta( $post->ID, 'sit_rating', true );
        $name    = (string) get_post_meta( $post->ID, 'sit_student_name', true );
        $country = (string) get_post_meta( $post->ID, 'sit_student_country', true );
        $rating  = ( '' !== $rating && null !== $rating && is_numeric( $rating ) ) ? esc_attr( (string) $rating ) : '';
        ?>
        <p>
            <label for="sit_rating_review"><strong><?php esc_html_e( 'Reytinq (0–5)', 'studyinturkey' ); ?></strong></label><br />
            <input type="number" step="0.01" min="0" max="5" class="small-text" id="sit_rating_review" name="sit_rating" value="<?php echo esc_attr( $rating ); ?>" />
        </p>
        <p>
            <label for="sit_student_name"><strong><?php esc_html_e( 'Tələbənin adı', 'studyinturkey' ); ?></strong></label><br />
            <input type="text" class="regular-text" id="sit_student_name" name="sit_student_name" value="<?php echo esc_attr( $name ); ?>" />
        </p>
        <p>
            <label for="sit_student_country"><strong><?php esc_html_e( 'Ölkə', 'studyinturkey' ); ?></strong></label><br />
            <input type="text" class="regular-text" id="sit_student_country" name="sit_student_country" value="<?php echo esc_attr( $country ); ?>" />
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
        if ( ! in_array( $post->post_type, SIT_Extra_Cpts::all(), true ) ) {
            return;
        }
        if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), 'sit_extra_meta_save_' . $post->post_type ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        switch ( $post->post_type ) {
            case SIT_Extra_Cpts::DORMITORY:
                self::persist_fields(
                    $post_id,
                    [
                        'sit_university_id',
                        'sit_price',
                        'sit_distance',
                        'sit_facilities',
                    ]
                );
                break;
            case SIT_Extra_Cpts::CAMPUS:
                self::persist_fields(
                    $post_id,
                    [
                        'sit_university_id',
                        'sit_address',
                        'sit_latitude',
                        'sit_longitude',
                    ]
                );
                break;
            case SIT_Extra_Cpts::SCHOLARSHIP:
                self::persist_fields(
                    $post_id,
                    [
                        'sit_university_id',
                        'sit_percentage',
                        'sit_eligibility',
                    ]
                );
                break;
            case SIT_Extra_Cpts::FAQ:
                self::persist_fields(
                    $post_id,
                    [
                        'sit_university_id',
                        'sit_sort_order',
                    ]
                );
                break;
            case SIT_Extra_Cpts::REVIEW:
                self::persist_fields(
                    $post_id,
                    [
                        'sit_university_id',
                        'sit_rating',
                        'sit_student_name',
                        'sit_student_country',
                    ]
                );
                break;
        }
    }

    /**
     * @param int      $post_id Post ID.
     * @param string[] $keys    Meta açarları.
     */
    private static function persist_fields( int $post_id, array $keys ): void {
        foreach ( $keys as $key ) {
            if ( ! array_key_exists( $key, $_POST ) ) {
                continue;
            }
            $raw = wp_unslash( $_POST[ $key ] );
            if ( '' === $raw || null === $raw ) {
                delete_post_meta( $post_id, $key );
                continue;
            }
            update_post_meta( $post_id, $key, self::sanitize_meta( $raw, $key, 'post', get_post_type( $post_id ) ) );
        }
    }
}
