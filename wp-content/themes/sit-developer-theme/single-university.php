<?php
/**
 * Single University — Figma dizaynı: cover hero + nav bar + sections.
 */
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$university_id = get_the_ID();
	$title    = sit_theme_get_post_title( $university_id );
	$logo_id  = (int) get_post_meta( $university_id, 'sit_logo_id', true );
	$cover_id = (int) get_post_meta( $university_id, 'sit_cover_image_id', true );
	$students = (int) get_post_meta( $university_id, 'sit_student_count', true );
	$founded  = (int) get_post_meta( $university_id, 'sit_founded_year', true );
	$tuition  = get_post_meta( $university_id, 'sit_tuition_fee_min', true );
	$rating   = (float) get_post_meta( $university_id, 'sit_rating', true );
	$cities   = get_the_terms( $university_id, 'city' );
	$types    = get_the_terms( $university_id, 'university_type' );

	$logo_url  = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
	$cover_url = $cover_id ? wp_get_attachment_image_url( $cover_id, 'full' ) : '';
	if ( ! $cover_url && has_post_thumbnail( $university_id ) ) {
		$cover_url = get_the_post_thumbnail_url( $university_id, 'full' );
	}

	$city_name = ( ! is_wp_error( $cities ) && is_array( $cities ) && $cities ) ? sit_theme_get_term_name( (int) $cities[0]->term_id, 'city' ) : '';
	$type_name = ( ! is_wp_error( $types ) && is_array( $types ) && $types ) ? sit_theme_get_term_name( (int) $types[0]->term_id, 'university_type' ) : '';
	$uni_url   = sit_theme_universities_archive_url();
?>
<main id="main-content" class="flex-1">
	<!-- Cover Hero -->
	<div class="relative flex h-[380px] w-full items-end md:h-[480px]">
		<?php if ( $cover_url ) : ?>
			<img src="<?php echo esc_url( $cover_url ); ?>" alt="" class="absolute inset-0 h-full w-full object-cover" />
		<?php endif; ?>
		<div class="absolute inset-0 bg-gradient-to-t from-[#0d4f52] via-[#0d4f52]/70 to-[#0d4f52]/30"></div>

		<!-- Back button -->
		<div class="absolute top-5 left-4 z-20 sm:left-6">
			<a href="<?php echo esc_url( $uni_url ); ?>" class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-[13px] font-semibold text-white backdrop-blur-md transition hover:bg-white/20">
				<svg class="h-[15px] w-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
				<?php esc_html_e( 'Universitetlər', 'studyinturkey' ); ?>
			</a>
		</div>

		<div class="sit-container relative z-10 pb-12 lg:pb-20">
			<div class="flex flex-col items-center gap-6 text-center md:flex-row md:items-end md:text-left">
				<?php if ( $logo_url ) : ?>
					<div class="h-28 w-28 rounded-2xl bg-white p-2.5 shadow-2xl ring-2 ring-white/10 md:h-36 md:w-36 md:translate-y-6">
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="" class="h-full w-full rounded-xl object-contain" />
					</div>
				<?php endif; ?>
				<div class="flex-1">
					<div class="mb-3 flex flex-wrap justify-center gap-2 md:justify-start">
						<?php if ( $type_name ) : ?>
							<span class="rounded-lg bg-brand-600 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.1em] text-white"><?php echo esc_html( $type_name ); ?></span>
						<?php endif; ?>
						<?php if ( $city_name ) : ?>
							<span class="rounded-lg border border-brand-600/20 bg-brand-600/20 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.1em] text-[#e6f2f2] backdrop-blur-sm"><?php echo esc_html( $city_name ); ?></span>
						<?php endif; ?>
					</div>
					<h1 class="mb-2 text-[32px] tracking-[-0.02em] text-white md:text-[48px]" style="line-height:1.1"><?php echo esc_html( $title ); ?></h1>
					<?php if ( $city_name ) : ?>
						<p class="flex items-center justify-center gap-2 text-[15px] font-medium text-blue-200/80 md:justify-start">
							<svg class="h-[17px] w-[17px] text-[#ff3131]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
							<?php echo esc_html( $city_name ); ?>, Türkiyə
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Content -->
	<div class="bg-[#f3f6f6] pb-24">
		<div class="sit-container space-y-16">
			<!-- Nav bar -->
			<div class="relative -mt-6 z-20">
				<div class="flex flex-col items-center justify-between gap-3 rounded-2xl border border-gray-200/60 bg-white p-2 shadow-[0_4px_20px_rgba(0,0,0,0.04)] lg:flex-row">
					<div class="flex w-full flex-wrap gap-1.5 rounded-xl bg-gray-50 p-1 lg:w-auto">
						<a href="#about" class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-white px-5 py-2.5 text-[13px] font-semibold text-brand-600 shadow-sm transition lg:flex-none">📋 <?php esc_html_e( 'Haqqında', 'studyinturkey' ); ?></a>
						<a href="#programs" class="flex flex-1 items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-[13px] font-semibold text-gray-500 transition hover:bg-white/60 hover:text-brand-600 lg:flex-none">📚 <?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?></a>
						<a href="#admission-requirements" class="flex flex-1 items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-[13px] font-semibold text-gray-500 transition hover:bg-white/60 hover:text-brand-600 lg:flex-none">📝 <?php esc_html_e( 'Qəbul', 'studyinturkey' ); ?></a>
					</div>
					<button type="button" data-sit-consult-open class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#ff3131] px-7 py-3 text-[14px] font-semibold text-white shadow-sm shadow-red-500/20 transition-all hover:bg-[#e02020] lg:w-auto">📞 <?php esc_html_e( 'Konsultasiya al', 'studyinturkey' ); ?></button>
				</div>
			</div>

			<!-- Stats -->
			<?php
			$stats = [];
			if ( $students > 0 ) $stats[] = ['<svg class="h-[22px] w-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>', __('Tələbələr','studyinturkey'), number_format_i18n($students), 'text-brand-600','bg-[#e6f2f2]','border-brand-600/15'];
			if ( $founded > 0 ) $stats[] = ['<svg class="h-[22px] w-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>', __('Təsis ili','studyinturkey'), (string)$founded, 'text-[#ff3131]','bg-red-50','border-red-100'];
			if ( is_numeric($tuition) && (float)$tuition > 0 ) $stats[] = ['<svg class="h-[22px] w-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', __('İllik ödəniş (min.)','studyinturkey'), '$'.number_format_i18n((float)$tuition,0), 'text-brand-600','bg-[#e6f2f2]','border-brand-600/15'];
			if ( ! empty($stats) ) : ?>
				<div class="grid grid-cols-1 gap-4 md:grid-cols-3">
					<?php foreach ( $stats as $s ) : ?>
						<div class="group flex items-center gap-4 rounded-2xl border <?php echo esc_attr($s[5]); ?> bg-white p-6 transition-all hover:shadow-md">
							<div class="flex h-13 w-13 items-center justify-center rounded-xl p-3 <?php echo esc_attr($s[4].' '.$s[3]); ?> transition-transform group-hover:scale-105"><?php echo $s[0]; ?></div>
							<div>
								<div class="text-[10px] font-semibold uppercase tracking-[0.1em] text-slate-400"><?php echo esc_html($s[1]); ?></div>
								<div class="text-[28px] font-extrabold tracking-tight text-[#0a1a1b]"><?php echo esc_html($s[2]); ?></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Existing sections -->
			<?php
			get_template_part( 'template-parts/university/section', 'why-choose', [ 'university_id' => $university_id ] );
			get_template_part( 'template-parts/university/section', 'about', [ 'university_id' => $university_id ] );
			get_template_part( 'template-parts/university/section', 'programs', [ 'university_id' => $university_id ] );
			get_template_part( 'template-parts/university/section', 'admission-requirements', [ 'university_id' => $university_id ] );
			get_template_part( 'template-parts/university/section', 'dormitories', [ 'university_id' => $university_id ] );
			get_template_part( 'template-parts/university/section', 'campus', [ 'university_id' => $university_id ] );
			get_template_part( 'template-parts/university/section', 'faq', [ 'university_id' => $university_id ] );
			get_template_part( 'template-parts/university/section', 'reviews', [ 'university_id' => $university_id ] );
			?>
		</div>
	</div>
</main>
<?php
// Konsultasiya modal
add_action( 'wp_footer', function () use ( $title ) {
	if ( did_action( 'sit_consult_modal_rendered' ) ) return;
	do_action( 'sit_consult_modal_rendered' );
	$ajax_url = admin_url( 'admin-ajax.php' );
?>
<div id="sit-consult-modal" class="fixed inset-0 z-[99999] hidden items-center justify-center bg-black/60 backdrop-blur-sm" role="dialog" aria-modal="true">
	<div class="relative mx-4 w-full max-w-md rounded-2xl border border-gray-200/60 bg-white p-6 shadow-2xl">
		<button type="button" data-sit-consult-close class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100">✕</button>
		<h2 class="text-xl font-bold text-[#0a1a1b]"><?php esc_html_e( 'Konsultasiya al', 'studyinturkey' ); ?></h2>
		<p class="mt-1 text-[13px] text-gray-500"><?php esc_html_e( 'Məlumatlarınızı yazın, komandamız əlaqə saxlayacaq.', 'studyinturkey' ); ?></p>
		<form id="sit-consult-form" class="mt-5 space-y-4" novalidate>
			<input type="hidden" name="action" value="sit_consultation_request"/>
			<input type="hidden" name="sit_consult_university" value="<?php echo esc_attr( $title ); ?>"/>
			<?php wp_nonce_field( 'sit_consultation_request', 'sit_consult_nonce', false ); ?>
			<div>
				<label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.1em] text-gray-400"><?php esc_html_e( 'Ad, soyad', 'studyinturkey' ); ?> *</label>
				<input type="text" name="sit_consult_name" required class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-[14px] outline-none transition focus:border-brand-600/40 focus:ring-2 focus:ring-brand-600/20"/>
			</div>
			<div>
				<label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.1em] text-gray-400"><?php esc_html_e( 'Telefon', 'studyinturkey' ); ?> *</label>
				<div class="flex gap-2">
					<select name="sit_consult_phone_code" class="w-28 shrink-0 rounded-xl border border-gray-100 bg-gray-50 px-2 py-3 text-[13px] outline-none"><?php sit_theme_phone_code_options(); ?></select>
					<input type="tel" name="sit_consult_phone" required placeholder="50 123 45 67" class="min-w-0 flex-1 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-[14px] outline-none transition focus:border-brand-600/40 focus:ring-2 focus:ring-brand-600/20"/>
				</div>
			</div>
			<div>
				<label class="mb-1 block text-[11px] font-bold uppercase tracking-[0.1em] text-gray-400"><?php esc_html_e( 'E-poçt', 'studyinturkey' ); ?> *</label>
				<input type="email" name="sit_consult_email" required class="w-full rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 text-[14px] outline-none transition focus:border-brand-600/40 focus:ring-2 focus:ring-brand-600/20"/>
			</div>
			<button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#ff3131] px-6 py-3.5 text-[14px] font-bold text-white shadow-sm shadow-red-500/20 transition hover:bg-[#e02020]"><?php esc_html_e( 'Göndər', 'studyinturkey' ); ?></button>
			<div id="sit-consult-result" class="hidden rounded-xl p-3 text-center text-[14px] font-medium"></div>
		</form>
	</div>
</div>
<script>
(function(){
	var m=document.getElementById('sit-consult-modal');if(!m)return;
	function show(){m.classList.remove('hidden');m.classList.add('flex');document.body.style.overflow='hidden';}
	function hide(){m.classList.add('hidden');m.classList.remove('flex');document.body.style.overflow='';}
	document.querySelectorAll('[data-sit-consult-open]').forEach(function(b){b.addEventListener('click',function(e){e.preventDefault();show();});});
	document.querySelectorAll('[data-sit-consult-close]').forEach(function(b){b.addEventListener('click',hide);});
	m.addEventListener('click',function(e){if(e.target===m)hide();});
	document.addEventListener('keydown',function(e){if(e.key==='Escape'&&!m.classList.contains('hidden'))hide();});
	var f=document.getElementById('sit-consult-form'),r=document.getElementById('sit-consult-result');
	if(!f)return;
	f.addEventListener('submit',function(e){
		e.preventDefault();var btn=f.querySelector('button[type="submit"]');btn.disabled=true;r.classList.add('hidden');
		var fd=new FormData(f);fd.set('sit_consult_phone',fd.get('sit_consult_phone_code')+' '+fd.get('sit_consult_phone'));
		fetch('<?php echo esc_url( $ajax_url ); ?>',{method:'POST',body:fd,credentials:'same-origin'})
		.then(function(x){return x.json();}).then(function(d){
			r.classList.remove('hidden');
			if(d.success){r.className='rounded-xl p-3 text-center text-[14px] font-medium bg-emerald-50 text-emerald-700';r.textContent=d.data||'OK';f.reset();}
			else{r.className='rounded-xl p-3 text-center text-[14px] font-medium bg-red-50 text-red-700';r.textContent=d.data||'Xəta';}
			btn.disabled=false;
		}).catch(function(){r.classList.remove('hidden');r.className='rounded-xl p-3 text-center text-[14px] font-medium bg-red-50 text-red-700';r.textContent='Xəta baş verdi.';btn.disabled=false;});
	});
})();
</script>
<?php
} );

endwhile;
get_footer();
