<?php
/**
 * Template Name: SIT — Əlaqə
 * Template Post Type: page
 */
defined( 'ABSPATH' ) || exit;
get_header();
$phone   = get_theme_mod( 'sit_footer_phone', '+90 501 012 77 88' );
$address = get_theme_mod( 'sit_footer_address', '' );
$email   = get_option( 'admin_email' );
?>
<main id="main-content" class="flex-1">
	<div class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-[#11676a]">
		<div class="absolute bottom-0 left-1/4 h-[500px] w-[500px] rounded-full bg-white/[0.04] blur-[80px]"></div>
		<div class="relative mx-auto max-w-7xl px-4 pt-16 pb-28 sm:px-6">
			<div class="max-w-2xl">
				<div class="mb-6 flex items-center gap-3">
					<div class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10">
						<svg class="h-[18px] w-[18px] text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
					</div>
					<span class="text-[13px] font-semibold uppercase tracking-[0.2em] text-white/40"><?php esc_html_e( 'Əlaqə', 'studyinturkey' ); ?></span>
				</div>
				<h1 class="mb-4 text-[42px] tracking-[-0.03em] text-white md:text-[56px]" style="line-height:1.1"><?php the_title(); ?></h1>
				<p class="max-w-lg text-[17px] leading-relaxed text-white/40"><?php esc_html_e( 'Türkiyədə təhsil haqqında suallarınız var? Komandamız hər addımda sizə kömək etməyə hazırdır.', 'studyinturkey' ); ?></p>
			</div>
		</div>
	</div>

	<div class="bg-[#f3f6f6]">
		<div class="mx-auto max-w-7xl px-4 pb-24 -mt-12 sm:px-6">
			<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
				<div class="space-y-3 lg:col-span-1">
					<?php
					$items = [
						['<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>',__('Ofisimiz','studyinturkey'),[$address?:'Baku, Azerbaijan'],'from-brand-700 to-teal-600'],
						['<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>',__('Telefon','studyinturkey'),[$phone],'from-[#ff3131] to-rose-600'],
						['<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>',__('Email','studyinturkey'),[$email],'from-brand-700 to-teal-600'],
						['<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',__('İş saatları','studyinturkey'),[__('B.e-Cümə: 09:00-18:00','studyinturkey'),__('Şənbə: 10:00-14:00','studyinturkey')],'from-[#ff3131] to-rose-600'],
					];
					foreach ($items as $c) : ?>
						<div class="flex items-start gap-4 rounded-2xl border border-gray-200/60 bg-white p-5 transition-all hover:shadow-md">
							<div class="shrink-0 rounded-xl bg-gradient-to-br <?php echo esc_attr($c[3]); ?> p-2.5 text-white shadow-sm"><?php echo $c[0]; // phpcs:ignore ?></div>
							<div><h3 class="mb-1 text-[14px] font-bold text-[#0a1a1b]"><?php echo esc_html($c[1]); ?></h3><?php foreach ($c[2] as $l) : ?><p class="text-[13px] font-medium text-gray-500"><?php echo esc_html($l); ?></p><?php endforeach; ?></div>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="rounded-2xl border border-gray-200/60 bg-white p-7 shadow-sm md:p-10 lg:col-span-2">
					<h2 class="mb-2 text-[24px] text-[#0a1a1b]"><?php esc_html_e( 'Bizə Mesaj Göndərin', 'studyinturkey' ); ?></h2>
					<p class="mb-7 text-[14px] text-gray-400"><?php esc_html_e( '24 saat ərzində sizinlə əlaqə saxlayacağıq.', 'studyinturkey' ); ?></p>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="space-y-4">
						<input type="hidden" name="action" value="sit_theme_contact" />
						<?php wp_nonce_field( 'sit_theme_contact_nonce', '_wpnonce_contact' ); ?>
						<input type="hidden" name="sit_contact_website" value="" />
						<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
							<div><label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.1em] text-gray-400"><?php esc_html_e('Ad','studyinturkey'); ?></label><input type="text" name="sit_contact_name" required class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-[14px] transition focus:border-brand-600/40 focus:outline-none focus:ring-2 focus:ring-brand-600/20" /></div>
							<div><label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.1em] text-gray-400"><?php esc_html_e('Email','studyinturkey'); ?></label><input type="email" name="sit_contact_email" required class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-[14px] transition focus:border-brand-600/40 focus:outline-none focus:ring-2 focus:ring-brand-600/20" /></div>
						</div>
						<div><label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.1em] text-gray-400"><?php esc_html_e('Telefon','studyinturkey'); ?></label><input type="tel" name="sit_contact_phone" class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-[14px] transition focus:border-brand-600/40 focus:outline-none focus:ring-2 focus:ring-brand-600/20" /></div>
						<div><label class="mb-1.5 block text-[11px] font-bold uppercase tracking-[0.1em] text-gray-400"><?php esc_html_e('Mesaj','studyinturkey'); ?></label><textarea name="sit_contact_message" rows="5" required class="w-full resize-none rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-[14px] transition focus:border-brand-600/40 focus:outline-none focus:ring-2 focus:ring-brand-600/20"></textarea></div>
						<button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#ff3131] px-8 py-3.5 text-[14px] font-bold text-white shadow-sm shadow-red-500/20 transition-all hover:bg-[#e02020] md:w-auto"><?php esc_html_e('Mesaj Göndər','studyinturkey'); ?> <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg></button>
					</form>
				</div>
			</div>
		</div>
	</div>
</main>
<?php get_footer();
