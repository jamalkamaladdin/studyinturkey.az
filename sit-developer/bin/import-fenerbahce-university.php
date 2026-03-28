<?php
/**
 * Fenerbahçe Universiteti + PDF (FBU Price List 2025-2026 FALL) proqramlarını idxal edir.
 *
 * İstifadə (WordPress kökündən, veb server istifadəçisi ilə):
 *   php8.3 wp-content/plugins/sit-developer/bin/import-fenerbahce-university.php
 *
 * Təkrar işlədiləndə eyni slug/title ilə mövcud yazılar yenilənir.
 *
 * @package StudyInTurkey
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( 'CLI only.' );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "wp-load.php tapılmadı: {$wp_load}\n" );
	exit( 1 );
}

require $wp_load;

if ( ! function_exists( 'wp_insert_post' ) ) {
	exit( 1 );
}

/**
 * @param string $taxonomy Taxonomy.
 * @param string $name    Term adı (sitenizdəki dil ilə eyni olmalıdır).
 */
function sit_fbu_term_id( string $taxonomy, string $name ): int {
	$t = get_term_by( 'name', $name, $taxonomy );
	if ( $t && ! is_wp_error( $t ) ) {
		return (int) $t->term_id;
	}
	$r = wp_insert_term( $name, $taxonomy );
	if ( is_wp_error( $r ) ) {
		fwrite( STDERR, "Term yaradılmadı ({$taxonomy} / {$name}): " . $r->get_error_message() . "\n" );
		return 0;
	}
	return (int) $r['term_id'];
}

/**
 * @param string $needle İngilis açar sözü.
 */
function sit_fbu_degree_id( string $needle ): int {
	$needle = strtolower( $needle );
	$terms  = get_terms(
		[
			'taxonomy'   => 'degree_type',
			'hide_empty' => false,
		]
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		return 0;
	}
	$map = [
		'associate' => [ 'associate', 'öncül', 'associate degree' ],
		'bachelor'  => [ 'bachelor', 'bakalavr', 'licenciatura' ],
		'master'    => [ 'master', 'magistr', 'magistratura' ],
		'phd'       => [ 'phd', 'doktor', 'doctorate', 'doktorantura' ],
	];
	$aliases = $map[ $needle ] ?? [ $needle ];
	foreach ( $terms as $t ) {
		$slug = strtolower( $t->slug );
		$name = strtolower( $t->name );
		foreach ( $aliases as $a ) {
			if ( str_contains( $name, $a ) || str_contains( $slug, $a ) || $slug === $a ) {
				return (int) $t->term_id;
			}
		}
	}
	return 0;
}

/**
 * @param string $en English / Turkish.
 */
function sit_fbu_lang_id( string $en ): int {
	$en = strtolower( $en );
	if ( str_contains( $en, 'turk' ) || 'tr' === $en ) {
		$id = sit_fbu_term_id( 'program_language', 'Turkish' );
		return $id ?: sit_fbu_term_id( 'program_language', 'Türk' );
	}
	$id = sit_fbu_term_id( 'program_language', 'English' );
	return $id ?: sit_fbu_term_id( 'program_language', 'İngilis' );
}

/**
 * @param string $key Field key.
 */
function sit_fbu_field_id( string $key ): int {
	$names = [
		'engineering'    => [ 'Engineering', 'Mühəndislik' ],
		'business'       => [ 'Business', 'Biznes' ],
		'medicine'       => [ 'Medicine', 'Tibb' ],
		'architecture'   => [ 'Architecture', 'Memarlıq' ],
		'communication'  => [ 'Communication', 'Kommunikasiya' ],
		'sport'          => [ 'Sport Sciences', 'İdman elmləri' ],
		'social'         => [ 'Social Sciences', 'Sosial elmlər' ],
		'humanities'     => [ 'Humanities', 'Humanitar elmlər' ],
	];
	$list = $names[ $key ] ?? [ $key ];
	foreach ( $list as $n ) {
		$id = sit_fbu_term_id( 'field_of_study', $n );
		if ( $id ) {
			return $id;
		}
	}
	return 0;
}

function sit_fbu_map_field( string $title ): string {
	if ( preg_match( '/\b(engineering|software|computer|electrical|industrial)\b/i', $title ) ) {
		return 'engineering';
	}
	if ( preg_match( '/\b(mba|business|economics|finance|management information|organizational behavior)\b/i', $title ) ) {
		return 'business';
	}
	if ( preg_match( '/\b(pharmacy|nursing|physiotherapy|midwifery|nutrition|speech|ergotherapy|clinical pharmacy|internal diseases nursing|anesthesia|dental|dialysis|emergency aid|medical imaging|laboratory|operating room|oral and dental|radiation|pathology|pharmacy services|orthopedic|dietetics)\b/i', $title ) ) {
		return 'medicine';
	}
	if ( preg_match( '/\b(architecture|interior architecture)\b/i', $title ) ) {
		return 'architecture';
	}
	if ( preg_match( '/\b(new media|public relations|radio|television|cinema|advertising)\b/i', $title ) ) {
		return 'communication';
	}
	if ( preg_match( '/\b(sport|coaching|exercise|physical education)\b/i', $title ) ) {
		return 'sport';
	}
	if ( preg_match( '/\b(psychology|political science|international relations)\b/i', $title ) ) {
		return 'social';
	}
	if ( preg_match( '/\benglish language and literature\b/i', $title ) ) {
		return 'humanities';
	}
	return 'business';
}

// Qiymətlər: PDF-dəki "AFTER SCHOLARSHIP (USD)" illik (xarici tələbə cədvəli).
$programs = [
	// Bakalavr (4 il, əsas ENG/TR).
	[ 'title' => 'Economics and Finance (English)', 'fee' => 3780, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'English Language and Literature (English)', 'fee' => 3780, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Management Information Systems (English)', 'fee' => 3780, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Political Science and International Relations (English)', 'fee' => 3780, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Business Administration (English)', 'fee' => 3780, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Psychology (English)', 'fee' => 3780, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Psychology (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Computer Engineering (English)', 'fee' => 3780, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Software Engineering (English)', 'fee' => 3780, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Industrial Engineering (English)', 'fee' => 3780, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Electrical-Electronics Engineering (English)', 'fee' => 3780, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Interior Architecture and Environmental Design (English)', 'fee' => 3780, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Interior Architecture and Environmental Design (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Architecture (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'New Media and Communication (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Public Relations and Advertising (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Radio, Television and Cinema (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Ergotherapy (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Nursing (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Nursing (English)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Nutrition and Dietetics (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Midwifery (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Physiotherapy and Rehabilitation (English)', 'fee' => 3780, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Physiotherapy and Rehabilitation (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Speech and Language Therapy (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Pharmacy (English)', 'fee' => 7200, 'dur' => '5 il', 'deg' => 'bachelor', 'lang' => 'en' ],
	[ 'title' => 'Pharmacy (Turkish)', 'fee' => 5850, 'dur' => '5 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Exercise and Sport Sciences (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Physical Education and Sports Teaching (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Sports Coaching (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	[ 'title' => 'Sports Management (Turkish)', 'fee' => 2835, 'dur' => '4 il', 'deg' => 'bachelor', 'lang' => 'tr' ],
	// Öncül (2 il).
	[ 'title' => 'Anesthesia (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	[ 'title' => 'Dental Prosthetics Technology (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	[ 'title' => 'Dialysis (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	[ 'title' => 'First and Emergency Aid (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	[ 'title' => 'Medical Imaging Techniques (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	[ 'title' => 'Medical Laboratory Techniques (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	[ 'title' => 'Operating Room Services (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	[ 'title' => 'Oral and Dental Health (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	[ 'title' => 'Orthopedic Prosthetics and Orthotics (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	[ 'title' => 'Pathology Laboratory Techniques (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	[ 'title' => 'Pharmacy Services (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	[ 'title' => 'Physiotherapy (Associate, English)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'en' ],
	[ 'title' => 'Physiotherapy (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	[ 'title' => 'Radiation Therapy (Associate, Turkish)', 'fee' => 1800, 'dur' => '2 il', 'deg' => 'associate', 'lang' => 'tr' ],
	// Magistratura / doktorantura.
	[ 'title' => 'MBA Business Administration (English, with thesis)', 'fee' => 2970, 'dur' => '2 il', 'deg' => 'master', 'lang' => 'en' ],
	[ 'title' => 'MBA Business Administration (English, without thesis)', 'fee' => 2025, 'dur' => '1,5 il', 'deg' => 'master', 'lang' => 'en' ],
	[ 'title' => 'MBA Business Administration (Turkish, with thesis)', 'fee' => 2970, 'dur' => '2 il', 'deg' => 'master', 'lang' => 'tr' ],
	[ 'title' => 'MBA Business Administration (Turkish, without thesis)', 'fee' => 2025, 'dur' => '1,5 il', 'deg' => 'master', 'lang' => 'tr' ],
	[ 'title' => 'Political Science and International Relations (English, with thesis)', 'fee' => 2970, 'dur' => '2 il', 'deg' => 'master', 'lang' => 'en' ],
	[ 'title' => 'Political Science and International Relations (English, without thesis)', 'fee' => 2025, 'dur' => '1,5 il', 'deg' => 'master', 'lang' => 'en' ],
	[ 'title' => 'Sports Sciences (Turkish, with thesis)', 'fee' => 2970, 'dur' => '2 il', 'deg' => 'master', 'lang' => 'tr' ],
	[ 'title' => 'Sports Sciences (Turkish, without thesis)', 'fee' => 2025, 'dur' => '1,5 il', 'deg' => 'master', 'lang' => 'tr' ],
	[ 'title' => 'Clinical Pharmacy (Turkish, without thesis)', 'fee' => 2025, 'dur' => '1,5 il', 'deg' => 'master', 'lang' => 'tr' ],
	[ 'title' => 'Internal Diseases Nursing (Turkish, with thesis)', 'fee' => 2970, 'dur' => '2 il', 'deg' => 'master', 'lang' => 'tr' ],
	[ 'title' => 'Organizational Behavior (Turkish, with thesis)', 'fee' => 2970, 'dur' => '2 il', 'deg' => 'master', 'lang' => 'tr' ],
	[ 'title' => 'Organizational Behavior (Turkish, without thesis)', 'fee' => 2025, 'dur' => '1,5 il', 'deg' => 'master', 'lang' => 'tr' ],
	[ 'title' => 'Business Administration (PhD, Turkish)', 'fee' => 6750, 'dur' => '4 il', 'deg' => 'phd', 'lang' => 'tr' ],
	[ 'title' => 'Sports Sciences (PhD, Turkish)', 'fee' => 6750, 'dur' => '4 il', 'deg' => 'phd', 'lang' => 'tr' ],
];

$univ_slug = 'fenerbahce-universitesi';
$existing_u = get_posts(
	[
		'post_type'      => 'university',
		'post_status'    => 'any',
		'name'           => $univ_slug,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	]
);

$univ_body = '<p>' . esc_html__( 'Fenerbahçe Universiteti 2016-cı ildə İstanbulda təsis olunmuş özəl ali təhsil müəssisəsidir. Fenerbahçe İdman Klubunun təsisçiliyi ilə yaradılmışdır. Kampus Ataşehir rayonunda, təxminən 100 000 m² sahədə yerləşir.', 'studyinturkey' ) . '</p>'
	. '<p>' . esc_html__( 'Bakalavr, öncül (vocational), magistratura və doktorantura proqramları təklif olunur; tədris ingilis və türk dillərində aparılır. Rəsmi sayt: ', 'studyinturkey' ) . '<a href="' . esc_url( 'https://www.fbu.edu.tr/en' ) . '">fbu.edu.tr</a></p>'
	. '<p><em>' . esc_html__( 'Qiymətlər FBU 2025–2026 payız tariflərinə uyğun olaraq təqaüddən sonrakı illik ödəniş (USD) kimi göstərilir.', 'studyinturkey' ) . '</em></p>';

$univ_data = [
	'post_type'    => 'university',
	'post_status'  => 'publish',
	'post_title'   => 'Fenerbahçe Universiteti',
	'post_name'    => $univ_slug,
	'post_content' => $univ_body,
	'post_excerpt' => __( 'İstanbulda özəl universitet — mühəndislik, biznes, sağlamlıq elmləri və daha çox proqramlar (ingilis/türk).', 'studyinturkey' ),
];

if ( $existing_u ) {
	$univ_id = (int) $existing_u[0];
	$univ_data['ID'] = $univ_id;
	wp_update_post( wp_slash( $univ_data ), true );
	echo "Universitet yeniləndi: ID {$univ_id}\n";
} else {
	$univ_id = wp_insert_post( wp_slash( $univ_data ), true );
	if ( is_wp_error( $univ_id ) ) {
		fwrite( STDERR, $univ_id->get_error_message() . "\n" );
		exit( 1 );
	}
	echo "Universitet yaradıldı: ID {$univ_id}\n";
}

update_post_meta( $univ_id, 'sit_tuition_fee_min', 1800 );
update_post_meta( $univ_id, 'sit_student_count', 6000 );
update_post_meta( $univ_id, 'sit_founded_year', 2016 );
update_post_meta( $univ_id, 'sit_global_ranking', 0 );
update_post_meta( $univ_id, 'sit_rating', 4.5 );
update_post_meta( $univ_id, 'sit_website_url', 'https://www.fbu.edu.tr/en' );

$city_id = sit_fbu_term_id( 'city', 'İstanbul' );
$type_id = sit_fbu_term_id( 'university_type', 'Özəl' );
if ( $city_id ) {
	wp_set_object_terms( $univ_id, [ $city_id ], 'city', false );
}
if ( $type_id ) {
	wp_set_object_terms( $univ_id, [ $type_id ], 'university_type', false );
}

$created = 0;
$updated = 0;

foreach ( $programs as $row ) {
	$slug = sanitize_title( $row['title'] . '-fbu' );
	$deg_id = sit_fbu_degree_id( $row['deg'] );
	$lang_id = sit_fbu_lang_id( $row['lang'] );
	$field_key = sit_fbu_map_field( $row['title'] );
	$field_id  = sit_fbu_field_id( $field_key );

	$found = get_posts(
		[
			'post_type'      => 'program',
			'post_status'    => 'any',
			'name'           => $slug,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		]
	);

	$desc = '<p>' . sprintf(
		/* translators: 1: program title */
		esc_html__( '%1$s — Fenerbahçe Universiteti (FBU). Qiymət: təqaüddən sonra illik USD.', 'studyinturkey' ),
		esc_html( $row['title'] )
	) . '</p>';

	$pdata = [
		'post_type'    => 'program',
		'post_status'  => 'publish',
		'post_title'   => $row['title'],
		'post_name'    => $slug,
		'post_content' => $desc,
		'post_excerpt' => __( 'FBU 2025–2026 payız tarifləri.', 'studyinturkey' ),
	];

	if ( $found ) {
		$pid = (int) $found[0];
		$pdata['ID'] = $pid;
		wp_update_post( wp_slash( $pdata ), true );
		++$updated;
	} else {
		$pid = wp_insert_post( wp_slash( $pdata ), true );
		if ( is_wp_error( $pid ) ) {
			fwrite( STDERR, $pid->get_error_message() . "\n" );
			continue;
		}
		++$created;
	}

	update_post_meta( $pid, 'sit_university_id', $univ_id );
	update_post_meta( $pid, 'sit_tuition_fee', (float) $row['fee'] );
	update_post_meta( $pid, 'sit_duration', $row['dur'] );
	update_post_meta( $pid, 'sit_scholarship_available', true );

	$tax = [];
	if ( $deg_id ) {
		$tax['degree_type'] = [ $deg_id ];
	}
	if ( $lang_id ) {
		$tax['program_language'] = [ $lang_id ];
	}
	if ( $field_id ) {
		$tax['field_of_study'] = [ $field_id ];
	}
	foreach ( $tax as $tx => $ids ) {
		wp_set_object_terms( $pid, $ids, $tx, false );
	}
}

echo "Proqramlar: {$created} yaradıldı, {$updated} yeniləndi.\n";
echo "Bitdi.\n";
