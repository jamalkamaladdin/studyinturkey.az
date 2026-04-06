<?php
/**
 * Əlaqə formu işarələri.
 *
 * @var string $redirect_url Geri yönləndirmə (cari səhifə).
 */

defined( 'ABSPATH' ) || exit;

$redirect = ( isset( $redirect_url ) && is_string( $redirect_url ) && $redirect_url !== '' )
	? $redirect_url
	: sit_theme_localize_url( home_url( '/' ) );
?>
<form class="space-y-4" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="<?php echo esc_attr( SIT_THEME_CONTACT_ACTION ); ?>">
	<input type="hidden" name="sit_contact_redirect" value="<?php echo esc_url( $redirect ); ?>">
	<?php wp_nonce_field( 'sit_theme_contact', 'sit_theme_contact_nonce' ); ?>

	<p class="hidden" aria-hidden="true">
		<label for="sit_contact_website"><?php esc_html_e( 'Veb sayt', 'studyinturkey' ); ?></label>
		<input type="text" name="sit_contact_website" id="sit_contact_website" value="" tabindex="-1" autocomplete="off">
	</p>

	<div>
		<label for="sit_contact_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300"><?php esc_html_e( 'Ad, soyad', 'studyinturkey' ); ?> <span class="text-red-500">*</span></label>
		<input type="text" name="sit_contact_name" id="sit_contact_name" required minlength="2" class="sit-form-input mt-1 w-full px-3 py-2 text-sm shadow-sm" autocomplete="name">
	</div>
	<div>
		<label for="sit_contact_email" class="block text-sm font-medium text-slate-700 dark:text-slate-300"><?php esc_html_e( 'E-poçt', 'studyinturkey' ); ?> <span class="text-red-500">*</span></label>
		<input type="email" name="sit_contact_email" id="sit_contact_email" required class="sit-form-input mt-1 w-full px-3 py-2 text-sm shadow-sm" autocomplete="email">
	</div>
	<div>
		<label for="sit_contact_phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300"><?php esc_html_e( 'Telefon', 'studyinturkey' ); ?></label>
		<input type="tel" name="sit_contact_phone" id="sit_contact_phone" class="sit-form-input mt-1 w-full px-3 py-2 text-sm shadow-sm" autocomplete="tel">
	</div>
	<div>
		<label for="sit_contact_subject" class="block text-sm font-medium text-slate-700 dark:text-slate-300"><?php esc_html_e( 'Mövzu', 'studyinturkey' ); ?></label>
		<input type="text" name="sit_contact_subject" id="sit_contact_subject" class="sit-form-input mt-1 w-full px-3 py-2 text-sm shadow-sm">
	</div>
	<div>
		<label for="sit_contact_message" class="block text-sm font-medium text-slate-700 dark:text-slate-300"><?php esc_html_e( 'Mesaj', 'studyinturkey' ); ?> <span class="text-red-500">*</span></label>
		<textarea name="sit_contact_message" id="sit_contact_message" required rows="5" minlength="10" class="sit-form-input mt-1 w-full px-3 py-2 text-sm shadow-sm"></textarea>
	</div>
	<button type="submit" class="min-h-[2.75rem] w-full touch-manipulation rounded-xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 dark:hover:bg-brand-500 sm:w-auto">
		<?php esc_html_e( 'Göndər', 'studyinturkey' ); ?>
	</button>
</form>
