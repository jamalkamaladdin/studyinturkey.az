<?php
/**
 * Fenerbahce Universiteti (ID: 6) ucun numune melumatlar: yataqxana, kampus, FAQ, reyler, qebul.
 *
 * Istifade:
 *   php8.3 /usr/local/bin/wp --path=/var/www/studyinturkey.az eval-file /tmp/sit-repo/tools/seed-fbu-data.php --allow-root
 *
 * @package StudyInTurkey
 */

defined( 'ABSPATH' ) || exit;

$university_id = 6;

// Universitetin movcudlugunu yoxla.
$univ_post = get_post( $university_id );
if ( ! $univ_post || 'university' !== $univ_post->post_type ) {
    echo "XETA: Universitet (ID={$university_id}) tapilmadi ve ya post_type uygun deyil.\n";
    exit( 1 );
}
echo "Universitet tapildi: {$univ_post->post_title} (ID: {$university_id})\n\n";

/**
 * Basliga gore movcud postu yoxlayir, yoxdursa yaradir.
 * Dublikatlarin qarsisini alir.
 */
function sit_seed_upsert( string $post_type, string $title, array $post_data, array $meta = [] ): int {
    $existing = get_posts( [
        'post_type'      => $post_type,
        'post_status'    => 'any',
        'title'          => $title,
        'posts_per_page' => 1,
    ] );

    if ( $existing ) {
        $pid = (int) $existing[0]->ID;
        echo "  [movcud] {$post_type}: \"{$title}\" (ID: {$pid})\n";
    } else {
        $pid = wp_insert_post( array_merge( [
            'post_type'   => $post_type,
            'post_title'  => $title,
            'post_status' => 'publish',
        ], $post_data ) );

        if ( is_wp_error( $pid ) ) {
            echo "  [XETA] {$post_type}: \"{$title}\" — {$pid->get_error_message()}\n";
            return 0;
        }
        echo "  [yaradildi] {$post_type}: \"{$title}\" (ID: {$pid})\n";
    }

    foreach ( $meta as $key => $value ) {
        update_post_meta( $pid, $key, $value );
    }

    return $pid;
}

// ──────────────────────────────────────────────
// 1. Yataqxanalar (dormitory)
// ──────────────────────────────────────────────
echo "=== Yataqxanalar ===\n";

sit_seed_upsert(
    'dormitory',
    'Ataşehir Akademik Yurt (Qadın)',
    [
        'post_content' => '<p>Ataşehir Akademik Yurt qadın tələbələr üçün nəzərdə tutulmuşdur. Yataqxana kampusa yaxın məsafədə yerləşir və müasir şəraitə malikdir. Wi-Fi, yemekxana, çamaşırxana və 7/24 təhlükəsizlik xidməti təqdim olunur.</p>',
        'post_excerpt' => 'Qadın tələbələr üçün yataqxana, kampusa 1.2km məsafədə.',
    ],
    [
        'sit_university_id' => $university_id,
        'sit_price'         => '250',
        'sit_distance'      => '1.2km',
        'sit_facilities'    => 'Wi-Fi, Yemekxana, Çamaşırxana, 7/24 Təhlükəsizlik',
    ]
);

sit_seed_upsert(
    'dormitory',
    'Turkuaz Erkek Yurdu',
    [
        'post_content' => '<p>Turkuaz Erkek Yurdu kişi tələbələr üçün nəzərdə tutulmuşdur. Yataqxana rahat yaşayış şəraiti, Wi-Fi, yemekxana, oyun otağı və televiziya otağı ilə təchiz olunmuşdur.</p>',
        'post_excerpt' => 'Kişi tələbələr üçün yataqxana, kampusa 2.5km məsafədə.',
    ],
    [
        'sit_university_id' => $university_id,
        'sit_price'         => '200',
        'sit_distance'      => '2.5km',
        'sit_facilities'    => 'Wi-Fi, Yemekxana, Oyun otağı, Televiziya otağı',
    ]
);

// ──────────────────────────────────────────────
// 2. Kampus (campus)
// ──────────────────────────────────────────────
echo "\n=== Kampus ===\n";

sit_seed_upsert(
    'campus',
    'Fenerbahçe Ana Kampüsü',
    [
        'post_content' => '<p>Fenerbahçe Universitetinin əsas kampusu İstanbulun Kadıköy rayonunda yerləşir. Kampus müasir tədris binaları, laboratoriyalar, kitabxana, idman kompleksi və yaşıl ərazilərlə təchiz olunmuşdur.</p>',
        'post_excerpt' => 'Fenerbahçe Universitetinin əsas kampusu, Kadıköy, İstanbul.',
    ],
    [
        'sit_university_id' => $university_id,
        'sit_address'       => 'Ataşehir, İstanbul, Türkiyə. Fener Kalamış Cd. No:36, 34726 Kadıköy',
        'sit_latitude'      => '40.9643',
        'sit_longitude'     => '29.0475',
    ]
);

// ──────────────────────────────────────────────
// 3. FAQ (faq)
// ──────────────────────────────────────────────
echo "\n=== FAQ ===\n";

$faq_items = [
    [
        'q' => 'Fenerbahçe Universitetinə necə müraciət etmək olar?',
        'a' => '<p>Fenerbahçe Universitetinə müraciət onlayn formalar vasitəsilə həyata keçirilir. Əvvəlcə universitetin rəsmi saytında qeydiyyatdan keçin, tələb olunan sənədləri yükləyin və müraciət formasını doldurun. Müraciət prosesi haqqında ətraflı məlumat üçün qəbul səhifəsinə baxa bilərsiniz.</p>',
    ],
    [
        'q' => 'Hansı dildə təhsil verilir?',
        'a' => '<p>Fenerbahçe Universitetində proqramlar həm ingilis, həm də türk dilində təqdim olunur. Bir çox mühəndislik, biznes və sosial elm proqramları tamamilə ingilis dilindədir. Türk dilində olan proqramlar da mövcuddur. Hər proqramın tədris dili proqram təsvirində qeyd edilmişdir.</p>',
    ],
    [
        'q' => 'Təqaüd imkanları varmı?',
        'a' => '<p>Bəli, Fenerbahçe Universiteti beynəlxalq tələbələr üçün akademik nailiyyətlərə əsaslanan təqaüd proqramları təklif edir. Təqaüdlər qismən və ya tam ödənişli ola bilər. Ətraflı məlumat üçün universitetin təqaüd səhifəsinə müraciət edin.</p>',
    ],
    [
        'q' => 'Yataqxana xidməti varmı?',
        'a' => '<p>Bəli, Fenerbahçe Universiteti tərəfdaş yataqxanalar vasitəsilə tələbələrə yaşayış xidməti təklif edir. Kişi və qadın tələbələr üçün ayrı yataqxanalar mövcuddur. Yataqxanalar Wi-Fi, yemekxana və digər müasir imkanlarla təchiz olunmuşdur.</p>',
    ],
    [
        'q' => 'Qəbul müddətləri nə vaxtdır?',
        'a' => '<p>Fenerbahçe Universiteti ildə iki dəfə — payız və yaz semestrləri üçün qəbul elan edir. Payız semestri üçün müraciətlər adətən iyul-avqust aylarında, yaz semestri üçün isə yanvar-fevral aylarında qəbul edilir. Dəqiq tarixlər hər il universitetin rəsmi saytında elan olunur.</p>',
    ],
];

$n = 0;
foreach ( $faq_items as $item ) {
    ++$n;
    sit_seed_upsert(
        'faq',
        $item['q'],
        [
            'post_content' => $item['a'],
            'post_excerpt' => wp_strip_all_tags( $item['q'] ),
        ],
        [
            'sit_university_id' => $university_id,
            'sit_sort_order'    => $n,
        ]
    );
}

// ──────────────────────────────────────────────
// 4. Rəylər (review)
// ──────────────────────────────────────────────
echo "\n=== Reylər ===\n";

$reviews = [
    [
        'name'    => 'Əli Həsənov',
        'country' => 'Azərbaycan',
        'rating'  => '5.0',
        'title'   => 'Əli Həsənov — Fenerbahçe Universiteti rəyi',
        'content' => '<p>Fenerbahçe Universitetində təhsil almaq mənim üçün əla təcrübə oldu. Müəllimlər çox peşəkardır və tədris keyfiyyəti yüksəkdir. Beynəlxalq mühit və kampus həyatı hər tələbə üçün unudulmaz xatirələr yaradır. Universitetin İstanbuldakı mövqeyi də böyük üstünlükdür.</p>',
    ],
    [
        'name'    => 'Maria Petrova',
        'country' => 'Rusiya',
        'rating'  => '4.5',
        'title'   => 'Maria Petrova — Fenerbahçe Universiteti rəyi',
        'content' => '<p>Kampus həyatı çox maraqlı və dinamikdir. Tələbə klubları, idman tədbirləri və sosial fəaliyyətlər çoxdur. Kampus müasir infrastruktura malikdir — kitabxana, laboratoriyalar və istirahət zonaları mükəmməldir. İstanbulda yaşamaq və oxumaq əla kombinasiyadır.</p>',
    ],
    [
        'name'    => 'Ahmed Al-Sayed',
        'country' => 'Misir',
        'rating'  => '4.8',
        'title'   => 'Ahmed Al-Sayed — Fenerbahçe Universiteti rəyi',
        'content' => '<p>Beynəlxalq tələbə olaraq burada çox rahat hiss edirəm. Universitet müxtəlif ölkələrdən gələn tələbələrlə zəngin mədəni mühit təqdim edir. İngilis dilində tədris proqramları keyfiyyətlidir və akademik dəstək xidmətləri hər zaman mövcuddur.</p>',
    ],
];

foreach ( $reviews as $rev ) {
    sit_seed_upsert(
        'review',
        $rev['title'],
        [
            'post_content' => $rev['content'],
            'post_excerpt' => mb_substr( wp_strip_all_tags( $rev['content'] ), 0, 160 ),
        ],
        [
            'sit_university_id'   => $university_id,
            'sit_rating'          => $rev['rating'],
            'sit_student_name'    => $rev['name'],
            'sit_student_country' => $rev['country'],
        ]
    );
}

// ──────────────────────────────────────────────
// 5. Qəbul tələbləri (admission requirements JSON)
// ──────────────────────────────────────────────
echo "\n=== Qəbul tələbləri ===\n";

$admission = [
    'associate' => [
        'steps' => [
            '<strong>Addım 1 — Müraciət:</strong> Onlayn müraciət formasını doldurun və tələb olunan sənədləri yükləyin.',
            '<strong>Addım 2 — Qiymətləndirmə:</strong> Sənədləriniz qəbul komissiyası tərəfindən nəzərdən keçirilir.',
            '<strong>Addım 3 — Qeydiyyat:</strong> Qəbul məktubu aldıqdan sonra qeydiyyat prosedurunu tamamlayın.',
        ],
        'documents' => [
            'Orta təhsil diplomu',
            'Transkript',
            'Pasport surəti',
            'Pasport fotosu',
        ],
        'intake_title'    => 'Payız 2026',
        'intake_start'    => '1 İyul 2026',
        'intake_deadline' => '15 Avqust 2026',
    ],
    'bachelor' => [
        'steps' => [
            '<strong>Addım 1 — Müraciət:</strong> Onlayn müraciət formasını doldurun və tələb olunan sənədləri yükləyin.',
            '<strong>Addım 2 — Qiymətləndirmə:</strong> Sənədləriniz qəbul komissiyası tərəfindən nəzərdən keçirilir və müsahibə təyin oluna bilər.',
            '<strong>Addım 3 — Qeydiyyat:</strong> Qəbul məktubu aldıqdan sonra qeydiyyat prosedurunu tamamlayın.',
        ],
        'documents' => [
            'Orta təhsil diplomu',
            'Transkript',
            'Pasport surəti',
            'Pasport fotosu',
            'Dil sertifikatı (varsa)',
        ],
        'intake_title'    => 'Payız 2026',
        'intake_start'    => '1 İyul 2026',
        'intake_deadline' => '15 Avqust 2026',
    ],
    'master' => [
        'steps' => [
            '<strong>Addım 1 — Müraciət:</strong> Onlayn müraciət formasını doldurun və tələb olunan sənədləri yükləyin.',
            '<strong>Addım 2 — Qiymətləndirmə:</strong> Sənədləriniz nəzərdən keçirilir, müsahibə və ya yazılı imtahan təyin oluna bilər.',
            '<strong>Addım 3 — Qeydiyyat:</strong> Qəbul məktubu aldıqdan sonra qeydiyyat prosedurunu tamamlayın.',
        ],
        'documents' => [
            'Bakalavr diplomu',
            'Transkript',
            'CV',
            'Motivasiya məktubu',
            '2 Tövsiyə məktubu',
            'Dil sertifikatı',
        ],
        'intake_title'    => 'Payız 2026',
        'intake_start'    => '1 İyul 2026',
        'intake_deadline' => '15 Avqust 2026',
    ],
    'phd' => [
        'steps' => [
            '<strong>Addım 1 — Müraciət:</strong> Onlayn müraciət formasını doldurun və tələb olunan sənədləri yükləyin.',
            '<strong>Addım 2 — Qiymətləndirmə:</strong> Sənədləriniz nəzərdən keçirilir, müsahibə və tədqiqat planı təqdimatı tələb olunur.',
            '<strong>Addım 3 — Qeydiyyat:</strong> Qəbul məktubu aldıqdan sonra qeydiyyat prosedurunu tamamlayın.',
        ],
        'documents' => [
            'Magistr diplomu',
            'Transkript',
            'Tədqiqat planı',
            'CV',
            '2 Tövsiyə məktubu',
            'Dil sertifikatı',
        ],
        'intake_title'    => 'Payız 2026',
        'intake_start'    => '1 İyul 2026',
        'intake_deadline' => '15 Avqust 2026',
    ],
];

$json = wp_json_encode( $admission, JSON_UNESCAPED_UNICODE );
update_post_meta( $university_id, 'sit_admission_requirements', $json );
echo "  sit_admission_requirements yenilendi (university ID: {$university_id})\n";

// ──────────────────────────────────────────────
// 6. Beynəlxalq statistikalar (university meta)
// ──────────────────────────────────────────────
echo "\n=== Beynəlxalq statistikalar ===\n";

update_post_meta( $university_id, 'sit_intl_students_total',  '8000+' );
update_post_meta( $university_id, 'sit_intl_foreign_students', '2348+' );
update_post_meta( $university_id, 'sit_intl_accept_rate',      '99%' );

echo "  sit_intl_students_total: 8000+\n";
echo "  sit_intl_foreign_students: 2348+\n";
echo "  sit_intl_accept_rate: 99%\n";

echo "\n=== Bitdi ===\n";
echo "FBU (ID: {$university_id}) ucun numune melumatlar uğurla yaradildi/yenilendi.\n";
