<?php
/**
 * Universitet hero — təmiz, cover fotosuz.
 */

defined( 'ABSPATH' ) || exit;

$university_id = sit_theme_resolve_university_id( isset( $university_id ) ? $university_id : null );
if ( $university_id < 1 ) {
	return;
}

$title    = sit_theme_get_post_title( $university_id );
$rating   = (float) get_post_meta( $university_id, 'sit_rating', true );
$logo_id  = (int) get_post_meta( $university_id, 'sit_logo_id', true );
$students = (int) get_post_meta( $university_id, 'sit_student_count', true );
$founded  = (int) get_post_meta( $university_id, 'sit_founded_year', true );
$ranking  = (int) get_post_meta( $university_id, 'sit_global_ranking', true );
$tuition  = get_post_meta( $university_id, 'sit_tuition_fee_min', true );
$website  = (string) get_post_meta( $university_id, 'sit_official_website', true );
$cities   = get_the_terms( $university_id, 'city' );
$types    = get_the_terms( $university_id, 'university_type' );

$apply_url  = '';
$apply_page = get_page_by_path( 'muraciet' );
if ( $apply_page ) {
	$apply_url = sit_theme_localize_url( get_permalink( $apply_page ) );
} else {
	$apply_url = sit_theme_programs_archive_url();
}

$uni_base = sit_theme_localize_url( get_permalink( $university_id ) );
if ( function_exists( 'sit_theme_university_sub_url' ) ) {
	$dorms_sub = sit_theme_university_sub_url( $university_id, 'dormitories' );
} else {
	$dorms_sub = $uni_base ? $uni_base . '#dormitories' : '#dormitories';
}
?>
<section class="border-b border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900" aria-labelledby="sit-univ-hero-title">
	<div class="sit-container py-8 lg:py-10">

		<!-- Üst: Logo + Ad + Meta -->
		<div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:gap-5">
			<?php if ( $logo_id ) : ?>
				<div class="shrink-0 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-800">
					<?php echo wp_get_attachment_image( $logo_id, 'medium', false, [ 'class' => 'h-16 w-16 object-contain sm:h-20 sm:w-20' ] ); ?>
				</div>
			<?php endif; ?>
			<div class="min-w-0">
				<h1 id="sit-univ-hero-title" class="text-2xl font-bold text-slate-900 sm:text-3xl lg:text-4xl dark:text-white"><?php echo esc_html( $title ); ?></h1>
				<div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-sm text-slate-500 dark:text-slate-400">
					<?php if ( ! is_wp_error( $cities ) && is_array( $cities ) && $cities ) : ?>
						<span class="inline-flex items-center gap-1">
							<svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0Z"/></svg>
							<?php
							$names = array_map( function ( $t ) { return sit_theme_get_term_name( (int) $t->term_id, 'city' ); }, $cities );
							echo esc_html( implode( ', ', array_filter( $names ) ) );
							?>
						</span>
					<?php endif; ?>
					<?php if ( ! is_wp_error( $types ) && is_array( $types ) && $types ) : ?>
						<?php foreach ( $types as $t ) : ?>
							<span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300"><?php echo esc_html( sit_theme_get_term_name( (int) $t->term_id, 'university_type' ) ); ?></span>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if ( $rating > 0 ) : ?>
						<span class="inline-flex items-center gap-1 font-semibold text-amber-500">
							<svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
							<?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Stat kartları -->
		<?php
		$stats = [];
		if ( $students > 0 )                                      { $stats[] = [ __( 'Tələbələr', 'studyinturkey' ), number_format_i18n( $students ) ]; }
		if ( $founded > 0 )                                       { $stats[] = [ __( 'Təsis ili', 'studyinturkey' ), (string) $founded ]; }
		if ( $ranking > 0 )                                       { $stats[] = [ __( 'Reytinq', 'studyinturkey' ), '#' . number_format_i18n( $ranking ) ]; }
		if ( is_numeric( $tuition ) && (float) $tuition > 0 )     { $stats[] = [ __( 'İllik ödəniş (min.)', 'studyinturkey' ), '$' . number_format_i18n( (float) $tuition, 0 ) ]; }
		?>
		<?php if ( ! empty( $stats ) ) : ?>
			<div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
				<?php foreach ( $stats as $s ) : ?>
					<div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800">
						<p class="text-xs font-medium text-slate-500 dark:text-slate-400"><?php echo esc_html( $s[0] ); ?></p>
						<p class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white"><?php echo esc_html( $s[1] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- Keçid düymələri -->
		<nav class="mt-6 flex flex-wrap items-center gap-2" aria-label="<?php esc_attr_e( 'Səhifə bölmələri', 'studyinturkey' ); ?>">
			<a href="#programs" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-500">
				<?php esc_html_e( 'Proqramlar', 'studyinturkey' ); ?>
			</a>
			<button type="button" data-sit-consult-open class="inline-flex items-center gap-1.5 rounded-lg border-2 border-brand-600 bg-white px-5 py-2 text-sm font-bold text-brand-700 shadow-sm transition hover:bg-brand-50 dark:border-brand-400 dark:bg-slate-800 dark:text-brand-300 dark:hover:bg-slate-700">
				<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
				<?php esc_html_e( 'Konsultasiya al', 'studyinturkey' ); ?>
			</button>
			<a href="#admission-requirements" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"><?php esc_html_e( 'Qəbul', 'studyinturkey' ); ?></a>
			<a href="<?php echo esc_url( $dorms_sub ); ?>" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"><?php esc_html_e( 'Yataqxanalar', 'studyinturkey' ); ?></a>
			<a href="#campus" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"><?php esc_html_e( 'Kampus', 'studyinturkey' ); ?></a>
			<?php if ( '' !== $website ) : ?>
				<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
					<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
					<?php esc_html_e( 'Rəsmi sayt', 'studyinturkey' ); ?>
				</a>
			<?php endif; ?>
		</nav>

	</div>
</section>

<!-- Konsultasiya popup -->
<div id="sit-consult-modal" style="display:none;" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="sit-consult-title">
	<div class="relative mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl sm:p-8 dark:bg-slate-900">
		<button type="button" data-sit-consult-close class="absolute end-4 top-4 flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200" aria-label="Bağla">
			<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
		</button>

		<h2 id="sit-consult-title" class="text-xl font-bold text-slate-900 dark:text-white"><?php esc_html_e( 'Konsultasiya al', 'studyinturkey' ); ?></h2>
		<p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400"><?php esc_html_e( 'Məlumatlarınızı yazın, komandamız sizinlə əlaqə saxlayacaq.', 'studyinturkey' ); ?></p>

		<form id="sit-consult-form" class="mt-5 space-y-4" novalidate>
			<input type="hidden" name="action" value="sit_consultation_request" />
			<input type="hidden" name="sit_consult_university" value="<?php echo esc_attr( $title ); ?>" />
			<?php wp_nonce_field( 'sit_consultation_request', 'sit_consult_nonce', false ); ?>

			<div>
				<label for="sit_consult_name" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300"><?php esc_html_e( 'Ad, soyad', 'studyinturkey' ); ?> <span class="text-red-500">*</span></label>
				<input type="text" id="sit_consult_name" name="sit_consult_name" required maxlength="120" autocomplete="name"
					class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
			</div>

			<div>
				<label for="sit_consult_phone" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300"><?php esc_html_e( 'Telefon', 'studyinturkey' ); ?> <span class="text-red-500">*</span></label>
				<div class="flex gap-2">
					<select id="sit_consult_phone_code" name="sit_consult_phone_code" class="w-32 shrink-0 rounded-lg border border-slate-300 bg-white px-2 py-2.5 text-sm text-slate-700 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
						<?php sit_theme_phone_code_options(); ?>
					</select>
					<input type="tel" id="sit_consult_phone" name="sit_consult_phone" required maxlength="20" autocomplete="tel" placeholder="50 123 45 67"
						class="w-full min-w-0 rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
				</div>
			</div>

			<div>
				<label for="sit_consult_email" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300"><?php esc_html_e( 'E-poçt', 'studyinturkey' ); ?> <span class="text-red-500">*</span></label>
				<input type="email" id="sit_consult_email" name="sit_consult_email" required maxlength="191" autocomplete="email"
					class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
			</div>

			<button type="submit" class="w-full rounded-lg bg-brand-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-brand-500 disabled:opacity-50">
				<?php esc_html_e( 'Göndər', 'studyinturkey' ); ?>
			</button>

			<div id="sit-consult-result" style="display:none;" class="rounded-lg p-3 text-center text-sm font-medium"></div>
		</form>
	</div>
</div>
<script>
(function(){
	var modal=document.getElementById('sit-consult-modal');
	if(!modal)return;
	function open(){modal.style.display='flex';document.body.style.overflow='hidden';}
	function close(){modal.style.display='none';document.body.style.overflow='';}
	document.querySelectorAll('[data-sit-consult-open]').forEach(function(b){b.addEventListener('click',open);});
	document.querySelectorAll('[data-sit-consult-close]').forEach(function(b){b.addEventListener('click',close);});
	modal.addEventListener('click',function(e){if(e.target===modal)close();});
	document.addEventListener('keydown',function(e){if(e.key==='Escape'&&modal.style.display!=='none')close();});

	var form=document.getElementById('sit-consult-form');
	var result=document.getElementById('sit-consult-result');
	if(!form)return;
	form.addEventListener('submit',function(e){
		e.preventDefault();
		var btn=form.querySelector('button[type="submit"]');
		btn.disabled=true;
		result.style.display='none';
		var fd=new FormData(form);
		fd.set('sit_consult_phone',fd.get('sit_consult_phone_code')+' '+fd.get('sit_consult_phone'));
		fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',{method:'POST',body:fd,credentials:'same-origin'})
		.then(function(r){return r.json();})
		.then(function(d){
			result.style.display='block';
			if(d.success){
				result.className='rounded-lg bg-emerald-50 p-3 text-center text-sm font-medium text-emerald-700';
				result.textContent=d.data||'OK';
				form.reset();
			}else{
				result.className='rounded-lg bg-red-50 p-3 text-center text-sm font-medium text-red-700';
				result.textContent=d.data||'Error';
			}
			btn.disabled=false;
		})
		.catch(function(){
			result.style.display='block';
			result.className='rounded-lg bg-red-50 p-3 text-center text-sm font-medium text-red-700';
			result.textContent='Xəta baş verdi.';
			btn.disabled=false;
		});
	});
})();
</script>
