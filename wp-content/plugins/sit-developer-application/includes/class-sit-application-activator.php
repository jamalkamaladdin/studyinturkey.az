<?php
/**
 * Aktivasiya / deaktivasiya.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Application_Activator {

    public static function activate( bool $network_wide = false ): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }

        SIT_Application_Db::create_tables();
        update_option( 'sit_application_db_version', SIT_APPLICATION_VERSION );

        add_option( 'sit_application_notify_admin_new', '1', '', 'no' );
        add_option( 'sit_application_notify_applicant_status', '1', '', 'no' );
        add_option( 'sit_application_notify_extra_emails', '', '', 'no' );
        add_option( 'sit_application_whatsapp_number', '', '', 'no' );
    }

    public static function deactivate( bool $network_wide = false ): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
    }
}
