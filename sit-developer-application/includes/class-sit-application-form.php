<?php
/**
 * Shortcode və forma çıxışı.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Application_Form {

    public static function register(): void {
        add_shortcode( 'sit_application_form', [ __CLASS__, 'render_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    public static function enqueue_assets(): void {
        global $post;
        if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'sit_application_form' ) ) {
            return;
        }

        wp_enqueue_style(
            'sit-application-form',
            SIT_APPLICATION_URL . 'assets/css/sit-application-form.css',
            [],
            SIT_APPLICATION_VERSION
        );
    }

    /**
     * Shortcode: [sit_application_form program_id="123"]
     *
     * @param array<string, string> $atts Shortcode attributes.
     */
    public static function render_shortcode( $atts ): string {
        $atts = shortcode_atts(
            [
                'program_id' => '',
            ],
            $atts,
            'sit_application_form'
        );

        $preselect = absint( $atts['program_id'] );

        wp_enqueue_style(
            'sit-application-form',
            SIT_APPLICATION_URL . 'assets/css/sit-application-form.css',
            [],
            SIT_APPLICATION_VERSION
        );

        ob_start();

        $flash   = SIT_Application_Handler::consume_form_flash();
        $errors  = $flash['errors'];
        $old     = $flash['old'];
        $success = SIT_Application_Handler::has_success_flag();

        if ( is_user_logged_in() ) {
            $u = wp_get_current_user();
            if ( empty( $old['sit_app_name'] ) ) {
                $old['sit_app_name'] = $u->display_name;
            }
            if ( empty( $old['sit_app_email'] ) ) {
                $old['sit_app_email'] = $u->user_email;
            }
            if ( empty( $old['sit_app_phone'] ) ) {
                $phone_meta = get_user_meta( $u->ID, SIT_Application_Account::META_PHONE, true );
                if ( is_string( $phone_meta ) && '' !== $phone_meta ) {
                    $old['sit_app_phone'] = $phone_meta;
                }
            }
        }

        $programs = get_posts(
            [
                'post_type'      => 'program',
                'post_status'    => 'publish',
                'posts_per_page' => 500,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'no_found_rows'  => true,
            ]
        );

        $action_url = esc_url( admin_url( 'admin-post.php' ) );
        ?>
        <div class="sit-app-form-wrapper">
            <?php if ( $success ) : ?>
                <div class="sit-app-form-notice sit-app-form-notice--success" role="status">
                    <?php esc_html_e( 'Müraciətiniz qəbul edildi. Tezliklə sizinlə əlaqə saxlanılacaq.', 'studyinturkey' ); ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $errors ) ) : ?>
                <div class="sit-app-form-notice sit-app-form-notice--error" role="alert">
                    <ul class="sit-app-form-error-list">
                        <?php foreach ( $errors as $err ) : ?>
                            <li><?php echo esc_html( $err ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ( empty( $programs ) ) : ?>
                <p class="sit-app-form-empty"><?php esc_html_e( 'Hazırda açıq proqram yoxdur.', 'studyinturkey' ); ?></p>
            <?php else : ?>
                <form class="sit-app-form" method="post" action="<?php echo esc_url( $action_url ); ?>" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="action" value="<?php echo esc_attr( SIT_Application_Handler::NONCE_ACTION ); ?>" />

                    <?php wp_nonce_field( SIT_Application_Handler::NONCE_ACTION, SIT_Application_Handler::NONCE_NAME, true, false ); ?>

                    <p class="sit-app-form-field">
                        <label for="sit_app_name"><?php esc_html_e( 'Tam ad', 'studyinturkey' ); ?> <span class="required">*</span></label>
                        <input type="text" id="sit_app_name" name="sit_app_name" required maxlength="191"
                            value="<?php echo isset( $old['sit_app_name'] ) ? esc_attr( (string) $old['sit_app_name'] ) : ''; ?>" />
                    </p>

                    <p class="sit-app-form-field">
                        <label for="sit_app_email"><?php esc_html_e( 'E-poçt', 'studyinturkey' ); ?> <span class="required">*</span></label>
                        <input type="email" id="sit_app_email" name="sit_app_email" required maxlength="191"
                            value="<?php echo isset( $old['sit_app_email'] ) ? esc_attr( sanitize_email( (string) $old['sit_app_email'] ) ) : ''; ?>" />
                    </p>

                    <p class="sit-app-form-field">
                        <label for="sit_app_phone"><?php esc_html_e( 'Telefon', 'studyinturkey' ); ?> <span class="required">*</span></label>
                        <input type="tel" id="sit_app_phone" name="sit_app_phone" required maxlength="50"
                            value="<?php echo isset( $old['sit_app_phone'] ) ? esc_attr( (string) $old['sit_app_phone'] ) : ''; ?>" />
                    </p>

                    <p class="sit-app-form-field">
                        <label for="sit_app_program_id"><?php esc_html_e( 'Proqram', 'studyinturkey' ); ?> <span class="required">*</span></label>
                        <select id="sit_app_program_id" name="sit_app_program_id" required>
                            <option value=""><?php esc_html_e( 'Seçin…', 'studyinturkey' ); ?></option>
                            <?php
                            $current_program = 0;
                            if ( ! empty( $old['sit_app_program_id'] ) ) {
                                $current_program = absint( $old['sit_app_program_id'] );
                            } elseif ( $preselect > 0 ) {
                                $current_program = $preselect;
                            }
                            ?>
                            <?php foreach ( $programs as $prog ) : ?>
                                <?php $pid = (int) $prog->ID; ?>
                                <option value="<?php echo esc_attr( (string) $pid ); ?>" <?php selected( $current_program, $pid ); ?>>
                                    <?php echo esc_html( get_the_title( $prog ) ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <p class="sit-app-form-field">
                        <label for="sit_app_message"><?php esc_html_e( 'Əlavə qeyd (istəyə bağlı)', 'studyinturkey' ); ?></label>
                        <textarea id="sit_app_message" name="sit_app_message" rows="4" maxlength="5000"><?php echo isset( $old['sit_app_message'] ) ? esc_textarea( (string) $old['sit_app_message'] ) : ''; ?></textarea>
                    </p>

                    <fieldset class="sit-app-form-files">
                        <legend><?php esc_html_e( 'Sənədlər', 'studyinturkey' ); ?></legend>
                        <p class="sit-app-form-hint"><?php esc_html_e( 'Pasport və şəkil: PDF, JPG, PNG və ya WEBP (şəkil üçün). Transkript: PDF, Word və ya şəkil.', 'studyinturkey' ); ?></p>

                        <p class="sit-app-form-field">
                            <label for="sit_app_passport"><?php esc_html_e( 'Pasport', 'studyinturkey' ); ?> <span class="required">*</span></label>
                            <input type="file" id="sit_app_passport" name="sit_app_passport" required accept=".pdf,.jpg,.jpeg,.png,.webp,image/*,application/pdf" />
                        </p>

                        <p class="sit-app-form-field">
                            <label for="sit_app_transcript"><?php esc_html_e( 'Transkript', 'studyinturkey' ); ?> <span class="required">*</span></label>
                            <input type="file" id="sit_app_transcript" name="sit_app_transcript" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*" />
                        </p>

                        <p class="sit-app-form-field">
                            <label for="sit_app_photo"><?php esc_html_e( 'Şəkil', 'studyinturkey' ); ?> <span class="required">*</span></label>
                            <input type="file" id="sit_app_photo" name="sit_app_photo" required accept=".jpg,.jpeg,.png,.webp,image/*" />
                        </p>
                    </fieldset>

                    <p class="sit-app-form-submit">
                        <button type="submit" class="sit-app-form-button"><?php esc_html_e( 'Müraciət göndər', 'studyinturkey' ); ?></button>
                    </p>
                </form>
            <?php endif; ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
