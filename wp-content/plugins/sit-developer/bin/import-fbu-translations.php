<?php
/**
 * Fenerbahçe Universiteti və onun proqramları üçün sit-multilang tərcümələri (en, ru, fa, ar, kk).
 * Əsas dil (az) wp_posts-da saxlanılır; digər dillər wp_sit_translations.
 *
 *   php8.3 wp-content/plugins/sit-developer/bin/import-fbu-translations.php
 *
 * @package StudyInTurkey
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( 'CLI only.' );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "wp-load.php tapılmadı.\n" );
	exit( 1 );
}

require $wp_load;

if ( ! class_exists( 'SIT_Translations', false ) ) {
	fwrite( STDERR, "sit-multilang aktiv deyil və ya SIT_Translations yoxdur.\n" );
	exit( 1 );
}

$univ_slug = 'fenerbahce-universitesi';
$univ      = get_posts(
	[
		'post_type'      => 'university',
		'post_status'    => 'any',
		'name'           => $univ_slug,
		'posts_per_page' => 1,
	]
);
if ( ! $univ ) {
	fwrite( STDERR, "Universitet tapılmadı: {$univ_slug}\n" );
	exit( 1 );
}
$univ_id = (int) $univ[0]->ID;

// İngilis başlıqlar — import-fenerbahce-university.php ilə eyni sıra (59).
$titles_en = [
	'Economics and Finance (English)',
	'English Language and Literature (English)',
	'Management Information Systems (English)',
	'Political Science and International Relations (English)',
	'Business Administration (English)',
	'Psychology (English)',
	'Psychology (Turkish)',
	'Computer Engineering (English)',
	'Software Engineering (English)',
	'Industrial Engineering (English)',
	'Electrical-Electronics Engineering (English)',
	'Interior Architecture and Environmental Design (English)',
	'Interior Architecture and Environmental Design (Turkish)',
	'Architecture (Turkish)',
	'New Media and Communication (Turkish)',
	'Public Relations and Advertising (Turkish)',
	'Radio, Television and Cinema (Turkish)',
	'Ergotherapy (Turkish)',
	'Nursing (Turkish)',
	'Nursing (English)',
	'Nutrition and Dietetics (Turkish)',
	'Midwifery (Turkish)',
	'Physiotherapy and Rehabilitation (English)',
	'Physiotherapy and Rehabilitation (Turkish)',
	'Speech and Language Therapy (Turkish)',
	'Pharmacy (English)',
	'Pharmacy (Turkish)',
	'Exercise and Sport Sciences (Turkish)',
	'Physical Education and Sports Teaching (Turkish)',
	'Sports Coaching (Turkish)',
	'Sports Management (Turkish)',
	'Anesthesia (Associate, Turkish)',
	'Dental Prosthetics Technology (Associate, Turkish)',
	'Dialysis (Associate, Turkish)',
	'First and Emergency Aid (Associate, Turkish)',
	'Medical Imaging Techniques (Associate, Turkish)',
	'Medical Laboratory Techniques (Associate, Turkish)',
	'Operating Room Services (Associate, Turkish)',
	'Oral and Dental Health (Associate, Turkish)',
	'Orthopedic Prosthetics and Orthotics (Associate, Turkish)',
	'Pathology Laboratory Techniques (Associate, Turkish)',
	'Pharmacy Services (Associate, Turkish)',
	'Physiotherapy (Associate, English)',
	'Physiotherapy (Associate, Turkish)',
	'Radiation Therapy (Associate, Turkish)',
	'MBA Business Administration (English, with thesis)',
	'MBA Business Administration (English, without thesis)',
	'MBA Business Administration (Turkish, with thesis)',
	'MBA Business Administration (Turkish, without thesis)',
	'Political Science and International Relations (English, with thesis)',
	'Political Science and International Relations (English, without thesis)',
	'Sports Sciences (Turkish, with thesis)',
	'Sports Sciences (Turkish, without thesis)',
	'Clinical Pharmacy (Turkish, without thesis)',
	'Internal Diseases Nursing (Turkish, with thesis)',
	'Organizational Behavior (Turkish, with thesis)',
	'Organizational Behavior (Turkish, without thesis)',
	'Business Administration (PhD, Turkish)',
	'Sports Sciences (PhD, Turkish)',
];

$i18n_prog = include dirname( __DIR__ ) . '/data/fbu-program-i18n-arrays.php';
if ( ! is_array( $i18n_prog ) || count( $i18n_prog['az'] ) !== count( $titles_en ) ) {
	fwrite( STDERR, "fbu-program-i18n-arrays.php uyğunsuzdur.\n" );
	exit( 1 );
}

$univ_i18n = [
	'en' => [
		'title'   => 'Fenerbahçe University',
		'slug'    => 'fenerbahce-university',
		'excerpt' => 'Private university in Istanbul — engineering, business, health sciences, English/Turkish programs.',
		'content' => '<p>Fenerbahçe University was established in 2016 in Istanbul, Türkiye. The campus is in Ataşehir (~100,000 m²). It offers bachelor, associate, master\'s and PhD programs in English and Turkish.</p><p>Official website: <a href="https://www.fbu.edu.tr/en">fbu.edu.tr</a></p>',
	],
	'ru' => [
		'title'   => 'Университет Фенербахче',
		'slug'    => 'universitet-fenerbahche',
		'excerpt' => 'Частный университет в Стамбуле — инженерия, бизнес, медицинские науки, программы на английском и турецком.',
		'content' => '<p>Университет Фенербахче основан в 2016 году в Стамбуле. Кампус в Аташехире (~100 000 м²). Бакалавриат, ассоциированная степень, магистратура и PhD на английском и турецком.</p><p>Сайт: <a href="https://www.fbu.edu.tr/en">fbu.edu.tr</a></p>',
	],
	'fa' => [
		'title'   => 'دانشگاه فنرباغچه',
		'slug'    => 'daneshgah-fenerbahce',
		'excerpt' => 'دانشگاه خصوصی در استانبول — مهندسی، مدیریت، علوم سلامت، برنامه‌های انگلیسی و ترکی.',
		'content' => '<p>دانشگاه فنرباغچه در سال ۲۰۱۶ در استانبول تأسیس شد. پردیس در آتاشهر (~۱۰۰٬۰۰۰ متر مربع). کارشناسی، کاردانی، کارشناسی ارشد و دکترا به انگلیسی و ترکی.</p><p>وب‌سایت: <a href="https://www.fbu.edu.tr/en">fbu.edu.tr</a></p>',
	],
	'ar' => [
		'title'   => 'جامعة فنربخشة',
		'slug'    => 'jamiat-fenerbahce',
		'excerpt' => 'جامعة خاصة في إسطنبول — هندسة، أعمال، علوم صحية، برامج بالإنجليزية والتركية.',
		'content' => '<p>تأسست جامعة فنربخشة عام ۲۰۱۶ في إسطنبول. الحرم في أتاشهير (~۱۰۰ ألف م²). بكالوريوس، دبلوم، ماجستير ودكتوراه بالإنجليزية والتركية.</p><p>الموقع: <a href="https://www.fbu.edu.tr/en">fbu.edu.tr</a></p>',
	],
	'kk' => [
		'title'   => 'Фенербахче университеті',
		'slug'    => 'fenerbahce-universiteti-kk',
		'excerpt' => 'Стамбұлдағы жеке университет — инженерия, бизнес, денсаулық ғылымдары, ағылшын/түрік бағдарламалары.',
		'content' => '<p>Фенербахче университеті 2016 жылы Стамбұлда құрылған. Кампус Аташехирде (~100 000 м²). Бакалавр, көмекші дәреже, магистратура және PhD ағылшын және түрік тілдерінде.</p><p>Сайт: <a href="https://www.fbu.edu.tr/en">fbu.edu.tr</a></p>',
	],
];

foreach ( $univ_i18n as $code => $fields ) {
	SIT_Translations::save_language_fields( $univ_id, SIT_Translations::OBJECT_POST, $code, $fields );
}
echo "Universitet tərcümələri: " . count( $univ_i18n ) . " dil.\n";

$n_prog = 0;
foreach ( $titles_en as $i => $en_title ) {
	$slug = sanitize_title( $en_title . '-fbu' );
	$posts = get_posts(
		[
			'post_type'      => 'program',
			'post_status'    => 'any',
			'name'           => $slug,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]
	);
	if ( ! $posts ) {
		fwrite( STDERR, "Proqram tapılmadı: {$slug}\n" );
		continue;
	}
	$pid = (int) $posts[0];

	$az_title   = $i18n_prog['az'][ $i ];
	$excerpt_az = 'FBU 2025–2026 payız tarifləri.';
	$body_az    = '<p>' . esc_html( $az_title ) . ' — Fenerbahçe Universiteti. İllik ödəniş USD (təqaüddən sonra).</p>';
	wp_update_post(
		[
			'ID'             => $pid,
			'post_title'     => $az_title,
			'post_excerpt'   => $excerpt_az,
			'post_content'   => $body_az,
		]
	);

	$body_en = '<p>' . esc_html( $en_title ) . ' — Fenerbahçe University (FBU). Annual fee in USD (after scholarship).</p>';
	$body_ru = '<p>' . esc_html( $i18n_prog['ru'][ $i ] ) . ' — Университет Фенербахче. Годовая оплата в USD (после стипендии).</p>';
	$body_fa = '<p>' . esc_html( $i18n_prog['fa'][ $i ] ) . ' — دانشگاه فنرباغچه. شهریه سالانه به دلار (پس از بورسیه).</p>';
	$body_ar = '<p>' . esc_html( $i18n_prog['ar'][ $i ] ) . ' — جامعة فنربخشة. الرسوم السنوية بالدولار (بعد المنحة).</p>';
	$body_kk = '<p>' . esc_html( $i18n_prog['kk'][ $i ] ) . ' — Фенербахче университеті. Жылдық төлем USD (гранттан кейін).</p>';

	SIT_Translations::save_language_fields(
		$pid,
		SIT_Translations::OBJECT_POST,
		'en',
		[
			'title'   => $en_title,
			'slug'    => $slug,
			'excerpt' => 'FBU 2025–2026 fall tuition reference (USD/year, after scholarship).',
			'content' => $body_en,
		]
	);
	SIT_Translations::save_language_fields(
		$pid,
		SIT_Translations::OBJECT_POST,
		'ru',
		[
			'title'   => $i18n_prog['ru'][ $i ],
			'slug'    => $slug,
			'excerpt' => 'Справочно: FBU осень 2025–2026, USD в год (после стипендии).',
			'content' => $body_ru,
		]
	);
	SIT_Translations::save_language_fields(
		$pid,
		SIT_Translations::OBJECT_POST,
		'fa',
		[
			'title'   => $i18n_prog['fa'][ $i ],
			'slug'    => $slug,
			'excerpt' => 'شهریه تقریبی FBU پاییز ۲۰۲۵–۲۰۲۶، دلار در سال (پس از بورسیه).',
			'content' => $body_fa,
		]
	);
	SIT_Translations::save_language_fields(
		$pid,
		SIT_Translations::OBJECT_POST,
		'ar',
		[
			'title'   => $i18n_prog['ar'][ $i ],
			'slug'    => $slug,
			'excerpt' => 'رسوم FBU خريف ۲۰۲۵–۲۰۲۶ تقريباً بالدولار سنوياً (بعد المنحة).',
			'content' => $body_ar,
		]
	);
	SIT_Translations::save_language_fields(
		$pid,
		SIT_Translations::OBJECT_POST,
		'kk',
		[
			'title'   => $i18n_prog['kk'][ $i ],
			'slug'    => $slug,
			'excerpt' => 'FBU 2025–2026 күз, жылдық USD (гранттан кейін).',
			'content' => $body_kk,
		]
	);
	++$n_prog;
}

echo "Proqramlar yeniləndi/tərcümə yazıldı: {$n_prog}.\n";
echo "Bitdi.\n";
