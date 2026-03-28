<?php
/**
 * Universitet: "Niyə bizi seçməli?" və "Haqqında" genişləndirilmiş sahələr.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_University_About_Meta {

    /* ── Why Choose Us ── */
    public const META_WHY_VIDEO   = 'sit_why_video_url';
    public const META_WHY_TEXT    = 'sit_why_text';
    public const META_WHY_BULLETS = 'sit_why_bullets';

    /* ── About accordion ── */
    public const META_ABOUT_DESC     = 'sit_about_description';
    public const META_ABOUT_MISSION  = 'sit_about_mission';
    public const META_ABOUT_STUDENT  = 'sit_about_student_life';
    public const META_ABOUT_GALLERY  = 'sit_about_gallery_ids';

    private const NONCE_ACTION = 'sit_univ_about_meta_save';
    private const NONCE_NAME   = 'sit_univ_about_meta_nonce';

    private const TEXT_KEYS = [
        self::META_WHY_VIDEO,
        self::META_WHY_TEXT,
        self::META_WHY_BULLETS,
        self::META_ABOUT_DESC,
        self::META_ABOUT_MISSION,
        self::META_ABOUT_STUDENT,
        self::META_ABOUT_GALLERY,
    ];

    public static function register(): void {
        foreach ( self::TEXT_KEYS as $key ) {
            register_post_meta(
                SIT_University_CPT::POST_TYPE,
                $key,
                [
                    'type'              => 'string',
                    'single'            => true,
                    'show_in_rest'      => true,
                    'sanitize_callback' => 'wp_kses_post',
                    'auth_callback'     => static fn( $a, $k, $id ) => current_user_can( 'edit_post', $id ),
                ]
            );
        }

        add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
        add_action( 'save_post_' . SIT_University_CPT::POST_TYPE, [ __CLASS__, 'save_meta' ], 10, 2 );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
    }

    public static function add_meta_boxes(): void {
        add_meta_box(
            'sit_university_why_choose',
            __( 'Niyə bizi seçməli? + Haqqında', 'studyinturkey' ),
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

        $vid     = (string) get_post_meta( $post->ID, self::META_WHY_VIDEO, true );
        $txt     = (string) get_post_meta( $post->ID, self::META_WHY_TEXT, true );
        $bullets = (string) get_post_meta( $post->ID, self::META_WHY_BULLETS, true );
        $desc    = (string) get_post_meta( $post->ID, self::META_ABOUT_DESC, true );
        $mission = (string) get_post_meta( $post->ID, self::META_ABOUT_MISSION, true );
        $student = (string) get_post_meta( $post->ID, self::META_ABOUT_STUDENT, true );
        $gallery = (string) get_post_meta( $post->ID, self::META_ABOUT_GALLERY, true );
        ?>
        <h3><?php esc_html_e( '🎯 Niyə bizi seçməli?', 'studyinturkey' ); ?></h3>
        <p>
            <label for="sit_why_video_url"><strong><?php esc_html_e( 'Video URL (YouTube/Vimeo)', 'studyinturkey' ); ?></strong></label><br />
            <input type="url" class="large-text" id="sit_why_video_url" name="sit_why_video_url" value="<?php echo esc_attr( $vid ); ?>" placeholder="https://youtube.com/watch?v=..." />
        </p>
        <p>
            <label for="sit_why_text"><strong><?php esc_html_e( 'Əsas mətn', 'studyinturkey' ); ?></strong></label><br />
            <textarea class="large-text" rows="3" id="sit_why_text" name="sit_why_text"><?php echo esc_textarea( $txt ); ?></textarea>
        </p>
        <p>
            <label for="sit_why_bullets"><strong><?php esc_html_e( 'Əsas xüsusiyyətlər (hər sətirdə bir)', 'studyinturkey' ); ?></strong></label><br />
            <textarea class="large-text" rows="6" id="sit_why_bullets" name="sit_why_bullets" placeholder="<?php esc_attr_e( "Beynəlxalq akkreditasiya\nGeniş kampus\n50+ proqram", 'studyinturkey' ); ?>"><?php echo esc_textarea( $bullets ); ?></textarea>
        </p>

        <hr style="margin:1.5em 0;" />

        <h3><?php esc_html_e( '📖 Universitet haqqında (akkordeon)', 'studyinturkey' ); ?></h3>
        <p>
            <label for="sit_about_description"><strong><?php esc_html_e( 'Haqqında (əsas təsvir, HTML icazəli)', 'studyinturkey' ); ?></strong></label><br />
            <textarea class="large-text" rows="5" id="sit_about_description" name="sit_about_description"><?php echo esc_textarea( $desc ); ?></textarea>
        </p>
        <p>
            <label for="sit_about_mission"><strong><?php esc_html_e( 'Missiya (HTML icazəli)', 'studyinturkey' ); ?></strong></label><br />
            <textarea class="large-text" rows="4" id="sit_about_mission" name="sit_about_mission"><?php echo esc_textarea( $mission ); ?></textarea>
        </p>
        <p>
            <label for="sit_about_student_life"><strong><?php esc_html_e( 'Tələbə həyatı (HTML icazəli)', 'studyinturkey' ); ?></strong></label><br />
            <textarea class="large-text" rows="4" id="sit_about_student_life" name="sit_about_student_life"><?php echo esc_textarea( $student ); ?></textarea>
        </p>

        <div class="sit-about-gallery-field" style="margin:1em 0;">
            <label><strong><?php esc_html_e( 'Foto qalereyası (ID-lər, vergüllə ayrılmış)', 'studyinturkey' ); ?></strong></label><br />
            <input type="text" class="large-text sit-about-gallery-input" name="sit_about_gallery_ids" value="<?php echo esc_attr( $gallery ); ?>" placeholder="123,456,789" />
            <button type="button" class="button sit-about-gallery-select"><?php esc_html_e( 'Şəkillər seç', 'studyinturkey' ); ?></button>
            <div class="sit-about-gallery-preview" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
                <?php
                if ( '' !== $gallery ) {
                    $ids = array_filter( array_map( 'absint', explode( ',', $gallery ) ) );
                    foreach ( $ids as $aid ) {
                        $url = wp_get_attachment_image_url( $aid, 'thumbnail' );
                        if ( $url ) {
                            echo '<img src="' . esc_url( $url ) . '" style="width:80px;height:80px;object-fit:cover;border-radius:6px;" />';
                        }
                    }
                }
                ?>
            </div>
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
            'sit_why_video_url'      => 'esc_url_raw',
            'sit_why_text'           => 'wp_kses_post',
            'sit_why_bullets'        => 'sanitize_textarea_field',
            'sit_about_description'  => 'wp_kses_post',
            'sit_about_mission'      => 'wp_kses_post',
            'sit_about_student_life' => 'wp_kses_post',
            'sit_about_gallery_ids'  => 'sanitize_text_field',
        ];

        foreach ( $fields as $key => $sanitizer ) {
            if ( ! array_key_exists( $key, $_POST ) ) {
                continue;
            }
            $raw = wp_unslash( $_POST[ $key ] );
            if ( '' === $raw || null === $raw ) {
                delete_post_meta( $post_id, $key );
                continue;
            }
            $val = $sanitizer( $raw );
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

        $js = <<<'JS'
(function($){
    $(function(){
        $('.sit-about-gallery-select').on('click', function(){
            var $btn = $(this);
            var $input = $btn.siblings('.sit-about-gallery-input');
            var $preview = $btn.siblings('.sit-about-gallery-preview');
            var frame = wp.media({
                title: 'Şəkillər seçin',
                button: { text: 'Seç' },
                multiple: true,
                library: { type: 'image' }
            });
            frame.on('select', function(){
                var attachments = frame.state().get('selection').toJSON();
                var ids = attachments.map(function(a){ return a.id; });
                $input.val(ids.join(','));
                $preview.html(attachments.map(function(a){
                    var url = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
                    return '<img src="'+url+'" style="width:80px;height:80px;object-fit:cover;border-radius:6px;" />';
                }).join(''));
            });
            frame.open();
        });
    });
})(jQuery);
JS;
        wp_add_inline_script( 'sit-university-meta-admin', $js );
    }
}
