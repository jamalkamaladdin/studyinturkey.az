<?php
/**
 * Admin: post və term redaktə ekranlarında dil tab-ları və tərcümə saxlama.
 */

defined( 'ABSPATH' ) || exit;

class SIT_Admin_Translations {

    private static array $post_save_done = [];

    public static function init(): void {
        add_action( 'add_meta_boxes', [ __CLASS__, 'register_post_meta_box' ], 10, 2 );
        add_action( 'save_post', [ __CLASS__, 'save_post_translations' ], 25, 2 );
        add_action( 'before_delete_post', [ __CLASS__, 'cleanup_post_translations' ] );

        foreach ( self::get_taxonomies() as $taxonomy ) {
            add_action( "{$taxonomy}_edit_form_fields", [ __CLASS__, 'render_term_fields' ], 10, 2 );
        }

        add_action( 'created_term', [ __CLASS__, 'save_term_translations' ], 10, 3 );
        add_action( 'edited_term', [ __CLASS__, 'save_term_translations' ], 10, 3 );
        add_action( 'delete_term', [ __CLASS__, 'cleanup_term_translations' ], 10, 4 );

        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    /**
     * @return string[]
     */
    public static function get_post_types(): array {
        $types = get_post_types( [ 'show_ui' => true ], 'names' );
        unset( $types['attachment'] );
        return apply_filters( 'sit_multilang_supported_post_types', array_values( $types ) );
    }

    /**
     * @return string[]
     */
    public static function get_taxonomies(): array {
        $tax = get_taxonomies( [ 'show_ui' => true ], 'names' );
        return apply_filters( 'sit_multilang_supported_taxonomies', array_values( $tax ) );
    }

    public static function enqueue_assets( string $hook ): void {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen ) {
            return;
        }

        $load = false;
        if ( 'post' === $screen->base && in_array( $screen->post_type, self::get_post_types(), true ) ) {
            $load = true;
        }
        if ( 'term' === $screen->base && in_array( $screen->taxonomy, self::get_taxonomies(), true ) ) {
            $load = true;
        }

        if ( ! $load ) {
            return;
        }

        wp_enqueue_style(
            'sit-ml-translations-admin',
            SIT_MULTILANG_URL . 'admin/css/sit-translations-admin.css',
            [],
            SIT_MULTILANG_VERSION
        );
        wp_enqueue_script(
            'sit-ml-translations-admin',
            SIT_MULTILANG_URL . 'admin/js/sit-translations-admin.js',
            [ 'jquery' ],
            SIT_MULTILANG_VERSION,
            true
        );
    }

    public static function register_post_meta_box( string $post_type, $post ): void {
        if ( ! $post instanceof WP_Post ) {
            return;
        }
        if ( ! in_array( $post_type, self::get_post_types(), true ) ) {
            return;
        }

        add_meta_box(
            'sit_ml_post_translations',
            esc_html__( 'Dil üzrə tərcümələr', 'studyinturkey' ),
            [ __CLASS__, 'render_post_meta_box' ],
            $post_type,
            'normal',
            'high'
        );
    }

    public static function render_post_meta_box( WP_Post $post ): void {
        if ( ! current_user_can( 'edit_post', $post->ID ) ) {
            return;
        }

        $languages = SIT_Languages::get_active_languages();
        if ( empty( $languages ) ) {
            echo '<p>' . esc_html__( 'Heç bir aktiv dil yoxdur.', 'studyinturkey' ) . '</p>';
            return;
        }

        $default_code = SIT_Languages::get_default_language_code();
        $map          = $post->ID ? SIT_Translations::get_map_for_object( (int) $post->ID, SIT_Translations::OBJECT_POST ) : [];

        wp_nonce_field( 'sit_post_translations', 'sit_post_translations_nonce' );

        if ( ! $post->ID ) {
            echo '<p class="sit-ml-notice">' . esc_html__( 'Əvvəlcə qaralamanı saxlayın; sonra digər dillər üçün tərcümə əlavə edə bilərsiniz.', 'studyinturkey' ) . '</p>';
        }

        ?>
        <div id="sit-ml-post-translations" class="sit-ml-translations" data-default-lang="<?php echo esc_attr( $default_code ); ?>">
            <div class="sit-ml-tabs nav-tab-wrapper">
                <?php foreach ( $languages as $lang ) : ?>
                    <a href="#" class="nav-tab" data-sit-lang="<?php echo esc_attr( $lang->code ); ?>" role="tab">
                        <?php echo esc_html( $lang->native_name ); ?>
                        <?php if ( $lang->code === $default_code ) : ?>
                            <span class="description">(<?php esc_html_e( 'əsas', 'studyinturkey' ); ?>)</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php foreach ( $languages as $lang ) : ?>
                <div class="sit-ml-panel" data-sit-lang="<?php echo esc_attr( $lang->code ); ?>" role="tabpanel">
                    <?php if ( $lang->code === $default_code ) : ?>
                        <div class="sit-ml-notice">
                            <?php esc_html_e( 'Əsas dil üçün başlıq, mətn və qısa təsvir yuxarıdakı redaktor sahələrində saxlanılır.', 'studyinturkey' ); ?>
                        </div>
                        <?php if ( $post->ID ) : ?>
                            <div class="sit-ml-preview sit-ml-field">
                                <label><?php esc_html_e( 'Başlıq (baxış)', 'studyinturkey' ); ?></label>
                                <input type="text" readonly value="<?php echo esc_attr( get_the_title( $post ) ); ?>" class="large-text" />
                            </div>
                            <div class="sit-ml-preview sit-ml-field">
                                <label><?php esc_html_e( 'Qısa təsvir (baxış)', 'studyinturkey' ); ?></label>
                                <textarea readonly class="large-text" rows="3"><?php echo esc_textarea( $post->post_excerpt ); ?></textarea>
                            </div>
                            <div class="sit-ml-preview sit-ml-field">
                                <label><?php esc_html_e( 'Slug (baxış)', 'studyinturkey' ); ?></label>
                                <input type="text" readonly value="<?php echo esc_attr( $post->post_name ); ?>" class="large-text" />
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <?php
                        $p = $map[ $lang->code ] ?? [];
                        $t = $p[ SIT_Translations::FIELD_TITLE ] ?? '';
                        $c = $p[ SIT_Translations::FIELD_CONTENT ] ?? '';
                        $e = $p[ SIT_Translations::FIELD_EXCERPT ] ?? '';
                        $s = $p[ SIT_Translations::FIELD_SLUG ] ?? '';
                        $pfx = 'sit_post_translations[' . $lang->code . ']';
                        ?>
                        <div class="sit-ml-field">
                            <label for="sit-ml-<?php echo esc_attr( $lang->code ); ?>-title"><?php esc_html_e( 'Başlıq', 'studyinturkey' ); ?></label>
                            <input type="text" class="large-text" id="sit-ml-<?php echo esc_attr( $lang->code ); ?>-title"
                                name="<?php echo esc_attr( $pfx ); ?>[title]" value="<?php echo esc_attr( $t ); ?>" />
                        </div>
                        <div class="sit-ml-field">
                            <label for="sit-ml-<?php echo esc_attr( $lang->code ); ?>-excerpt"><?php esc_html_e( 'Qısa təsvir', 'studyinturkey' ); ?></label>
                            <textarea class="large-text" rows="3" id="sit-ml-<?php echo esc_attr( $lang->code ); ?>-excerpt"
                                name="<?php echo esc_attr( $pfx ); ?>[excerpt]"><?php echo esc_textarea( $e ); ?></textarea>
                        </div>
                        <div class="sit-ml-field">
                            <label for="sit-ml-<?php echo esc_attr( $lang->code ); ?>-content"><?php esc_html_e( 'Məzmun', 'studyinturkey' ); ?></label>
                            <textarea class="large-text sit-ml-content" rows="10" id="sit-ml-<?php echo esc_attr( $lang->code ); ?>-content"
                                name="<?php echo esc_attr( $pfx ); ?>[content]"><?php echo esc_textarea( $c ); ?></textarea>
                        </div>
                        <div class="sit-ml-field">
                            <label for="sit-ml-<?php echo esc_attr( $lang->code ); ?>-slug"><?php esc_html_e( 'Slug', 'studyinturkey' ); ?></label>
                            <input type="text" class="large-text" id="sit-ml-<?php echo esc_attr( $lang->code ); ?>-slug"
                                name="<?php echo esc_attr( $pfx ); ?>[slug]" value="<?php echo esc_attr( $s ); ?>" />
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    public static function save_post_translations( int $post_id, $post ): void {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        if ( ! isset( $_POST['sit_post_translations_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sit_post_translations_nonce'] ) ), 'sit_post_translations' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        if ( ! $post instanceof WP_Post ) {
            $loaded = get_post( $post_id );
            if ( ! $loaded instanceof WP_Post ) {
                return;
            }
            $post = $loaded;
        }
        if ( ! in_array( $post->post_type, self::get_post_types(), true ) ) {
            return;
        }
        if ( isset( self::$post_save_done[ $post_id ] ) ) {
            return;
        }

        $default_code = SIT_Languages::get_default_language_code();
        $raw          = isset( $_POST['sit_post_translations'] ) ? wp_unslash( $_POST['sit_post_translations'] ) : [];
        if ( ! is_array( $raw ) ) {
            $raw = [];
        }

        foreach ( SIT_Languages::get_active_languages() as $lang ) {
            if ( $lang->code === $default_code ) {
                continue;
            }
            if ( ! isset( $raw[ $lang->code ] ) || ! is_array( $raw[ $lang->code ] ) ) {
                continue;
            }
            $chunk = $raw[ $lang->code ];
            $title = isset( $chunk['title'] ) ? sanitize_text_field( $chunk['title'] ) : '';
            $excerpt = isset( $chunk['excerpt'] ) ? sanitize_textarea_field( $chunk['excerpt'] ) : '';
            $content = isset( $chunk['content'] ) ? wp_kses_post( $chunk['content'] ) : '';
            $slug    = isset( $chunk['slug'] ) ? sanitize_title( $chunk['slug'] ) : '';

            SIT_Translations::save_language_fields(
                $post_id,
                SIT_Translations::OBJECT_POST,
                $lang->code,
                [
                    SIT_Translations::FIELD_TITLE   => $title,
                    SIT_Translations::FIELD_EXCERPT => $excerpt,
                    SIT_Translations::FIELD_CONTENT => $content,
                    SIT_Translations::FIELD_SLUG    => $slug,
                ]
            );
        }

        self::$post_save_done[ $post_id ] = true;
    }

    public static function cleanup_post_translations( int $post_id ): void {
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        SIT_Translations::delete_for_object( $post_id, SIT_Translations::OBJECT_POST );
    }

    /**
     * @param WP_Term|object $term     Term object.
     * @param string         $taxonomy Taxonomy name.
     */
    public static function render_term_fields( $term, string $taxonomy ): void {
        if ( ! in_array( $taxonomy, self::get_taxonomies(), true ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_term', $term->term_id ) ) {
            return;
        }

        $languages    = SIT_Languages::get_active_languages();
        $default_code = SIT_Languages::get_default_language_code();
        $map          = SIT_Translations::get_map_for_object( (int) $term->term_id, SIT_Translations::OBJECT_TERM );

        wp_nonce_field( 'sit_term_translations', 'sit_term_translations_nonce' );
        ?>
        <tr class="form-field sit-ml-term-translations-wrap">
            <th scope="row" valign="top">
                <label><?php esc_html_e( 'Dil üzrə tərcümələr', 'studyinturkey' ); ?></label>
            </th>
            <td>
                <div id="sit-ml-term-translations" class="sit-ml-translations" data-default-lang="<?php echo esc_attr( $default_code ); ?>">
                    <div class="sit-ml-tabs nav-tab-wrapper">
                        <?php foreach ( $languages as $lang ) : ?>
                            <a href="#" class="nav-tab" data-sit-lang="<?php echo esc_attr( $lang->code ); ?>" role="tab">
                                <?php echo esc_html( $lang->native_name ); ?>
                                <?php if ( $lang->code === $default_code ) : ?>
                                    <span class="description">(<?php esc_html_e( 'əsas', 'studyinturkey' ); ?>)</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php foreach ( $languages as $lang ) : ?>
                        <div class="sit-ml-panel" data-sit-lang="<?php echo esc_attr( $lang->code ); ?>" role="tabpanel">
                            <?php if ( $lang->code === $default_code ) : ?>
                                <div class="sit-ml-notice">
                                    <?php esc_html_e( 'Əsas dil üçün ad, təsvir və slug bu səhifədəki standart sahələrdə saxlanılır.', 'studyinturkey' ); ?>
                                </div>
                                <div class="sit-ml-preview sit-ml-field">
                                    <label><?php esc_html_e( 'Ad (baxış)', 'studyinturkey' ); ?></label>
                                    <input type="text" readonly value="<?php echo esc_attr( $term->name ); ?>" class="large-text" />
                                </div>
                                <div class="sit-ml-preview sit-ml-field">
                                    <label><?php esc_html_e( 'Təsvir (baxış)', 'studyinturkey' ); ?></label>
                                    <textarea readonly class="large-text" rows="3"><?php echo esc_textarea( $term->description ); ?></textarea>
                                </div>
                                <div class="sit-ml-preview sit-ml-field">
                                    <label><?php esc_html_e( 'Slug (baxış)', 'studyinturkey' ); ?></label>
                                    <input type="text" readonly value="<?php echo esc_attr( $term->slug ); ?>" class="large-text" />
                                </div>
                            <?php else : ?>
                                <?php
                                $p = $map[ $lang->code ] ?? [];
                                $t = $p[ SIT_Translations::FIELD_TITLE ] ?? '';
                                $c = $p[ SIT_Translations::FIELD_CONTENT ] ?? '';
                                $s = $p[ SIT_Translations::FIELD_SLUG ] ?? '';
                                $pfx = 'sit_term_translations[' . $lang->code . ']';
                                ?>
                                <div class="sit-ml-field">
                                    <label for="sit-ml-term-<?php echo esc_attr( $lang->code ); ?>-name"><?php esc_html_e( 'Ad', 'studyinturkey' ); ?></label>
                                    <input type="text" class="large-text" id="sit-ml-term-<?php echo esc_attr( $lang->code ); ?>-name"
                                        name="<?php echo esc_attr( $pfx ); ?>[title]" value="<?php echo esc_attr( $t ); ?>" />
                                </div>
                                <div class="sit-ml-field">
                                    <label for="sit-ml-term-<?php echo esc_attr( $lang->code ); ?>-desc"><?php esc_html_e( 'Təsvir', 'studyinturkey' ); ?></label>
                                    <textarea class="large-text" rows="4" id="sit-ml-term-<?php echo esc_attr( $lang->code ); ?>-desc"
                                        name="<?php echo esc_attr( $pfx ); ?>[content]"><?php echo esc_textarea( $c ); ?></textarea>
                                </div>
                                <div class="sit-ml-field">
                                    <label for="sit-ml-term-<?php echo esc_attr( $lang->code ); ?>-slug"><?php esc_html_e( 'Slug', 'studyinturkey' ); ?></label>
                                    <input type="text" class="large-text" id="sit-ml-term-<?php echo esc_attr( $lang->code ); ?>-slug"
                                        name="<?php echo esc_attr( $pfx ); ?>[slug]" value="<?php echo esc_attr( $s ); ?>" />
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
        <?php
    }

    public static function save_term_translations( int $term_id, int $tt_id, string $taxonomy ): void {
        if ( ! in_array( $taxonomy, self::get_taxonomies(), true ) ) {
            return;
        }
        if ( ! isset( $_POST['sit_term_translations_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sit_term_translations_nonce'] ) ), 'sit_term_translations' ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_term', $term_id ) ) {
            return;
        }

        $default_code = SIT_Languages::get_default_language_code();
        $raw          = isset( $_POST['sit_term_translations'] ) ? wp_unslash( $_POST['sit_term_translations'] ) : [];
        if ( ! is_array( $raw ) ) {
            $raw = [];
        }

        foreach ( SIT_Languages::get_active_languages() as $lang ) {
            if ( $lang->code === $default_code ) {
                continue;
            }
            if ( ! isset( $raw[ $lang->code ] ) || ! is_array( $raw[ $lang->code ] ) ) {
                continue;
            }
            $chunk = $raw[ $lang->code ];
            $title = isset( $chunk['title'] ) ? sanitize_text_field( $chunk['title'] ) : '';
            $content = isset( $chunk['content'] ) ? wp_kses_post( $chunk['content'] ) : '';
            $slug    = isset( $chunk['slug'] ) ? sanitize_title( $chunk['slug'] ) : '';

            SIT_Translations::save_language_fields(
                $term_id,
                SIT_Translations::OBJECT_TERM,
                $lang->code,
                [
                    SIT_Translations::FIELD_TITLE   => $title,
                    SIT_Translations::FIELD_CONTENT => $content,
                    SIT_Translations::FIELD_SLUG    => $slug,
                ]
            );
        }
    }

    /**
     * @param int    $term_id         Term ID.
     * @param int    $tt_id           Term taxonomy ID.
     * @param string $taxonomy        Taxonomy slug.
     * @param mixed  $deleted_term    Deleted term object (WP 4.8+).
     */
    public static function cleanup_term_translations( int $term_id, int $tt_id, string $taxonomy, $deleted_term = null ): void {
        SIT_Translations::delete_for_object( $term_id, SIT_Translations::OBJECT_TERM );
    }
}
