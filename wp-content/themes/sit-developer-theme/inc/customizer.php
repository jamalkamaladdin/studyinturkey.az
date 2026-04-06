<?php
/**
 * Theme Customizer — full Figma design support.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', function ( WP_Customize_Manager $wp_customize ): void {

	$wp_customize->add_panel( 'sit_homepage', [
		'title'    => __( 'Ana Səhifə', 'studyinturkey' ),
		'priority' => 30,
	] );

	// ═══ HERO ═══
	$wp_customize->add_section( 'sit_hero', [
		'title' => __( 'Hero bölməsi', 'studyinturkey' ),
		'panel' => 'sit_homepage',
	] );

	$hero = [
		['sit_hero_bg_type',    'select', 'gradient', __('Arxa fon növü','studyinturkey'), ['gradient'=>'Gradient','image'=>'Şəkil','video'=>'Video']],
		['sit_hero_overlay_opacity', 'number', '60', __('Overlay qaranlıq (%)','studyinturkey')],
		['sit_hero_heading',    'text', 'Unlock Your Potential', __('Başlıq (sətir 1)','studyinturkey')],
		['sit_hero_heading2',   'text', 'Study in Turkey', __('Başlıq (sətir 2, rəngli)','studyinturkey')],
		['sit_hero_description','textarea', 'We help ambitious students secure admissions at top-ranked Turkish universities.', __('Təsvir','studyinturkey')],
		['sit_hero_stat1_num',  'text', '100%', __('Statistika 1 — Rəqəm','studyinturkey')],
		['sit_hero_stat1_text', 'text', 'Admission Rate', __('Statistika 1 — Mətn','studyinturkey')],
		['sit_hero_stat2_num',  'text', '5000+', __('Statistika 2 — Rəqəm','studyinturkey')],
		['sit_hero_stat2_text', 'text', 'Students', __('Statistika 2 — Mətn','studyinturkey')],
		['sit_hero_stat3_num',  'text', '150+', __('Statistika 3 — Rəqəm','studyinturkey')],
		['sit_hero_stat3_text', 'text', 'Universities', __('Statistika 3 — Mətn','studyinturkey')],
		['sit_hero_search_placeholder', 'text', 'What do you want to study?', __('Axtarış placeholder','studyinturkey')],
	];
	foreach ($hero as $h) {
		$san = $h[1]==='textarea' ? 'sanitize_textarea_field' : ($h[1]==='number' ? 'absint' : 'sanitize_text_field');
		$wp_customize->add_setting($h[0], ['default'=>$h[2], 'sanitize_callback'=>$san]);
		$ctrl = ['section'=>'sit_hero','label'=>$h[3],'type'=>$h[1]];
		if ($h[1]==='select') $ctrl['choices'] = $h[4];
		if ($h[1]==='number') $ctrl['input_attrs'] = ['min'=>0,'max'=>100,'step'=>5];
		$wp_customize->add_control($h[0], $ctrl);
	}
	$wp_customize->add_setting('sit_hero_bg_image', ['default'=>'','sanitize_callback'=>'esc_url_raw']);
	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize,'sit_hero_bg_image',['section'=>'sit_hero','label'=>__('Arxa fon şəkli','studyinturkey')]));
	$wp_customize->add_setting('sit_hero_bg_video', ['default'=>'','sanitize_callback'=>'esc_url_raw']);
	$wp_customize->add_control(new WP_Customize_Upload_Control($wp_customize,'sit_hero_bg_video',['section'=>'sit_hero','label'=>__('Arxa fon video (mp4)','studyinturkey'),'mime_type'=>'video']));

	// ═══ WHY TURKEY (3 kart) ═══
	$wp_customize->add_section('sit_why_turkey', ['title'=>__('Why Choose Turkey?','studyinturkey'),'panel'=>'sit_homepage']);
	$wt_def = [
		1 => ['Quality Education','Degrees recognized globally. Modern campuses with cutting-edge laboratories.','BookOpen','blue'],
		2 => ['Cultural Bridge','A unique blend of Eastern and Western cultures, rich history.','MapPin','red'],
		3 => ['Affordable Living','Compared to Europe and the US, significantly lower tuition and living costs.','Briefcase','emerald'],
	];
	for ($i=1;$i<=3;$i++) {
		foreach ([
			["sit_wt_{$i}_title",'text',$wt_def[$i][0],"Kart $i — Başlıq"],
			["sit_wt_{$i}_desc",'textarea',$wt_def[$i][1],"Kart $i — Təsvir"],
			["sit_wt_{$i}_icon",'text',$wt_def[$i][2],"Kart $i — İkon (BookOpen/MapPin/Briefcase/Award/Users/Building/Star/GraduationCap)"],
			["sit_wt_{$i}_color",'select',$wt_def[$i][3],"Kart $i — Rəng"],
		] as $f) {
			$wp_customize->add_setting($f[0],['default'=>$f[2],'sanitize_callback'=>'sanitize_text_field']);
			$ctrl = ['section'=>'sit_why_turkey','label'=>$f[3],'type'=>$f[1]];
			if ($f[1]==='select') $ctrl['choices'] = ['blue'=>'Mavi','red'=>'Qırmızı','emerald'=>'Yaşıl','amber'=>'Sarı','purple'=>'Bənövşəyi','slate'=>'Boz'];
			$wp_customize->add_control($f[0],$ctrl);
		}
		$wp_customize->add_setting("sit_wt_{$i}_image",['default'=>'','sanitize_callback'=>'esc_url_raw']);
		$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize,"sit_wt_{$i}_image",['section'=>'sit_why_turkey','label'=>"Kart $i — Şəkil (ikona əvəz)"]));
	}

	// ═══ STEPS ═══
	$wp_customize->add_section('sit_steps', ['title'=>__('Necə işləyir?','studyinturkey'),'panel'=>'sit_homepage']);
	$sd = [1=>['Proqram seçin','Dərəcə, dil və şəhər üzrə filtrləyin.'],2=>['Sənədləri hazırlayın','Pasport, transkript və şəkil yükləyin.'],3=>['Müraciət edin','Formu doldurun, statusu kabinetdə izləyin.'],4=>['Qəbul dəstəyi','Komandamız əlaqə saxlayır və yönləndirir.']];
	for ($i=1;$i<=6;$i++) {
		$wp_customize->add_setting("sit_step_{$i}_title",['default'=>$sd[$i][0]??'','sanitize_callback'=>'sanitize_text_field']);
		$wp_customize->add_control("sit_step_{$i}_title",['section'=>'sit_steps','label'=>"Addım $i — Başlıq",'type'=>'text']);
		$wp_customize->add_setting("sit_step_{$i}_desc",['default'=>$sd[$i][1]??'','sanitize_callback'=>'sanitize_textarea_field']);
		$wp_customize->add_control("sit_step_{$i}_desc",['section'=>'sit_steps','label'=>"Addım $i — Təsvir",'type'=>'textarea']);
	}

	// ═══ WHY CHOOSE US (Bento — 5 kart) ═══
	$wp_customize->add_section('sit_why_us', ['title'=>__('Why Choose Us (Bento)','studyinturkey'),'panel'=>'sit_homepage']);
	$wu_def = [
		1 => ['Scholarships up to 100%','We represent over 150 prestigious universities, offering exclusive scholarships.','Award','large'],
		2 => ['Free Application','Complete the free application form by uploading your documents.','Briefcase','normal'],
		3 => ['Cheapest Fees','Through our exclusive agreements, we guarantee the lowest tuition fees.','MapPin','normal'],
		4 => ['150+ Universities','Proudly partnered with over 150 top-tier universities across Turkey.','Building','accent'],
		5 => ['100% Acceptance','With our vast network, we guarantee acceptance into your chosen department.','Users','dark'],
	];
	for ($i=1;$i<=5;$i++) {
		foreach ([
			["sit_wu_{$i}_title",'text',$wu_def[$i][0],"Kart $i — Başlıq"],
			["sit_wu_{$i}_desc",'textarea',$wu_def[$i][1],"Kart $i — Təsvir"],
			["sit_wu_{$i}_icon",'text',$wu_def[$i][2],"Kart $i — İkon"],
			["sit_wu_{$i}_style",'select',$wu_def[$i][3],"Kart $i — Stil"],
		] as $f) {
			$wp_customize->add_setting($f[0],['default'=>$f[2],'sanitize_callback'=>'sanitize_text_field']);
			$ctrl = ['section'=>'sit_why_us','label'=>$f[3],'type'=>$f[1]];
			if ($f[1]==='select') $ctrl['choices'] = ['large'=>'Böyük (mavi)','normal'=>'Adi (ağ)','accent'=>'Aksentli (qırmızı fon)','dark'=>'Tünd'];
			$wp_customize->add_control($f[0],$ctrl);
		}
	}

	// ═══ FOOTER — Social ═══
	$wp_customize->add_section('sit_footer_social', ['title'=>__('Footer & Sosial','studyinturkey'),'priority'=>120]);
	foreach (['facebook'=>'Facebook','instagram'=>'Instagram','twitter'=>'X (Twitter)','youtube'=>'YouTube','linkedin'=>'LinkedIn','telegram'=>'Telegram','tiktok'=>'TikTok','whatsapp'=>'WhatsApp'] as $k=>$l) {
		$wp_customize->add_setting("sit_social_{$k}",['default'=>'','sanitize_callback'=>'esc_url_raw']);
		$wp_customize->add_control("sit_social_{$k}",['section'=>'sit_footer_social','label'=>$l.' URL','type'=>'url']);
	}
	$wp_customize->add_setting('sit_footer_phone',['default'=>'','sanitize_callback'=>'sanitize_text_field']);
	$wp_customize->add_control('sit_footer_phone',['section'=>'sit_footer_social','label'=>__('Telefon','studyinturkey'),'type'=>'text']);
	$wp_customize->add_setting('sit_footer_address',['default'=>'','sanitize_callback'=>'sanitize_textarea_field']);
	$wp_customize->add_control('sit_footer_address',['section'=>'sit_footer_social','label'=>__('Ünvan','studyinturkey'),'type'=>'textarea']);
});

// ═══ HELPERS ═══

function sit_theme_social_icon_svg(string $p): string {
	$i = [
		'facebook'=>'<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
		'instagram'=>'<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',
		'twitter'=>'<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
		'youtube'=>'<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
		'linkedin'=>'<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
		'telegram'=>'<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
		'tiktok'=>'<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>',
		'whatsapp'=>'<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
	];
	return $i[$p] ?? '';
}

function sit_theme_get_social_links(): array {
	$out = [];
	foreach (['facebook','instagram','twitter','youtube','linkedin','telegram','tiktok','whatsapp'] as $p) {
		$u = get_theme_mod("sit_social_{$p}",'');
		if ($u) $out[$p] = $u;
	}
	return $out;
}

/**
 * SVG icon helper for Lucide-style icons.
 */
function sit_theme_icon_svg(string $name, string $class = 'h-8 w-8'): string {
	$icons = [
		'BookOpen'   => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>',
		'MapPin'     => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>',
		'Briefcase'  => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>',
		'Award'      => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0016.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.003 6.003 0 01-2.77.907m-5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172"/></svg>',
		'Users'      => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>',
		'Building'   => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>',
		'Search'     => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>',
		'Star'       => '<svg class="'.$class.'" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
	];
	return $icons[$name] ?? '<span class="'.$class.'">●</span>';
}
