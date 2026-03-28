<?php
/**
 * Əlaqə formu (admin-post, wp_mail).
 */

defined( 'ABSPATH' ) || exit;

const SIT_THEME_CONTACT_ACTION = 'sit_theme_contact';

/**
 * Form emalı.
 */
function sit_theme_handle_contact_form(): void {
	if ( ! isset( $_POST['sit_theme_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sit_theme_contact_nonce'] ) ), 'sit_theme_contact' ) ) {
		$ref = wp_get_referer();
		wp_safe_redirect( add_query_arg( 'contact', 'error', $ref ? $ref : home_url( '/' ) ) );
		exit;
	}

	// Honeypot.
	if ( ! empty( $_POST['sit_contact_website'] ) ) {
		sit_theme_contact_redirect( 'sent' );
	}

	$name    = isset( $_POST['sit_contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sit_contact_name'] ) ) : '';
	$email   = isset( $_POST['sit_contact_email'] ) ? sanitize_email( wp_unslash( $_POST['sit_contact_email'] ) ) : '';
	$phone   = isset( $_POST['sit_contact_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['sit_contact_phone'] ) ) : '';
	$subject = isset( $_POST['sit_contact_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['sit_contact_subject'] ) ) : '';
	$message = isset( $_POST['sit_contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sit_contact_message'] ) ) : '';

	if ( strlen( $name ) < 2 || ! is_email( $email ) || strlen( $message ) < 10 ) {
		sit_theme_contact_redirect( 'error' );
	}

	$to      = apply_filters( 'sit_theme_contact_mail_to', get_option( 'admin_email' ) );
	$subj    = apply_filters(
		'sit_theme_contact_mail_subject',
		sprintf(
			/* translators: %s: site name */
			__( '[%s] Əlaqə formu', 'studyinturkey' ),
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
		) . ( $subject ? ' — ' . $subject : '' ),
		$name,
		$email
	);
	$body    = sprintf(
		/* translators: 1: name, 2: email, 3: phone, 4: subject, 5: message */
		__( "Ad: %1\$s\nE-poçt: %2\$s\nTelefon: %3\$s\nMövzu: %4\$s\n\nMesaj:\n%5\$s", 'studyinturkey' ),
		$name,
		$email,
		$phone ? $phone : '—',
		$subject ? $subject : '—',
		$message
	);
	$headers = apply_filters(
		'sit_theme_contact_mail_headers',
		[ 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' ],
		$email,
		$name
	);

	$sent = wp_mail( $to, $subj, $body, $headers );

	sit_theme_contact_redirect( $sent ? 'sent' : 'error' );
}

/**
 * @param string $status sent|error.
 */
function sit_theme_contact_redirect( string $status ): void {
	$redirect = isset( $_POST['sit_contact_redirect'] ) ? esc_url_raw( wp_unslash( (string) $_POST['sit_contact_redirect'] ) ) : '';
	$redirect = $redirect ? wp_validate_redirect( $redirect, home_url( '/' ) ) : home_url( '/' );
	$redirect = remove_query_arg( [ 'contact' ], $redirect );
	wp_safe_redirect( add_query_arg( 'contact', $status, $redirect ) );
	exit;
}

add_action( 'admin_post_nopriv_' . SIT_THEME_CONTACT_ACTION, 'sit_theme_handle_contact_form' );
add_action( 'admin_post_' . SIT_THEME_CONTACT_ACTION, 'sit_theme_handle_contact_form' );

/* ── Konsultasiya popup (AJAX) ── */
function sit_theme_handle_consultation_ajax(): void {
	if ( ! isset( $_POST['sit_consult_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sit_consult_nonce'] ) ), 'sit_consultation_request' ) ) {
		wp_send_json_error( 'Təhlükəsizlik xətası.' );
	}

	$name  = isset( $_POST['sit_consult_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sit_consult_name'] ) ) : '';
	$phone = isset( $_POST['sit_consult_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['sit_consult_phone'] ) ) : '';
	$email = isset( $_POST['sit_consult_email'] ) ? sanitize_email( wp_unslash( $_POST['sit_consult_email'] ) ) : '';
	$univ  = isset( $_POST['sit_consult_university'] ) ? sanitize_text_field( wp_unslash( $_POST['sit_consult_university'] ) ) : '';

	if ( mb_strlen( $name ) < 2 || ! is_email( $email ) || mb_strlen( $phone ) < 5 ) {
		wp_send_json_error( 'Bütün sahələri düzgün doldurun.' );
	}

	$to   = get_option( 'admin_email' );
	$subj = sprintf( '[%s] Konsultasiya sorğusu — %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $univ );
	$body = sprintf(
		"Ad: %s\nTelefon: %s\nE-poçt: %s\nUniversitet: %s",
		$name,
		$phone,
		$email,
		$univ ? $univ : '—'
	);
	$headers = [ 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' ];

	wp_mail( $to, $subj, $body, $headers );

	wp_send_json_success( 'Sorğunuz qəbul edildi. Tezliklə sizinlə əlaqə saxlanılacaq.' );
}
add_action( 'wp_ajax_sit_consultation_request', 'sit_theme_handle_consultation_ajax' );
add_action( 'wp_ajax_nopriv_sit_consultation_request', 'sit_theme_handle_consultation_ajax' );
