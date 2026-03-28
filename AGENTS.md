# AGENTS.md — AI Agent Qaydaları və Mərhələlər

Bu fayl bütün AI agentlər (Claude, Cursor, Copilot) üçün layihə qaydalarını və iş mərhələlərini müəyyən edir.

---

## Ümumi Qaydalar

### 1. Context Limiti
- Hər mərhələ **200K token context-dən aşağı** olmalıdır.
- Əgər mərhələ böyükdürsə, alt-mərhələlərə bölün.
- Bir mərhələdə yalnız **bir plugin/modul** üzərində işləyin.

### 2. Commit Qaydası
- Hər mərhələ tamamlananda **mütləq commit + push** edin.
- Commit mesajı formatı: `[FAZA.MƏRHƏLƏ] Qısa təsvir`
- Nümunə: `[2.1] Multilang plugin: DB cədvəlləri və aktivasiya`
- Push: `git push origin main`

### 3. Kod Qaydaları
- PHP kodu **WordPress Coding Standards**-a uyğun olmalıdır.
- Plugin prefix: `sit_` (StudyInTurkey)
- DB table prefix: `wp_sit_`
- Text domain: `studyinturkey`
- Minimum PHP versiyası: 8.1
- Bütün stringlər tərcümə funksiyaları ilə sarılmalıdır: `__()`, `_e()`, `esc_html__()`

### 4. Fayl Strukturu Qaydası
- Hər plugin öz qovluğundadır (repo root-da).
- Serverdə `wp-content/plugins/` altına kopyalanır.
- Theme `sit-developer-theme/` qovluğundadır, serverdə `wp-content/themes/` altına kopyalanır.
- **WordPress core fayllarına toxunmayın.**

### 5. Təhlükəsizlik
- Bütün inputlar sanitize olunmalıdır: `sanitize_text_field()`, `absint()`, `wp_kses_post()`
- Bütün outputlar escape olunmalıdır: `esc_html()`, `esc_attr()`, `esc_url()`
- Nonce yoxlaması: bütün form submit-lərdə `wp_nonce_field()` / `wp_verify_nonce()`
- Direct file access qoruması: hər PHP faylının əvvəlində `defined('ABSPATH') || exit;`
- SQL sorğularında `$wpdb->prepare()` istifadə edin.

### 6. Dil / Çoxdillilik
- Default dil: **Azərbaycan (az)**
- 6 dil: az, en, ru, fa, ar, kk
- Fars (fa) və Ərəb (ar) **RTL** dillərdir.
- URL strukturu: `/{lang_code}/səhifə-slug/` (məs: `/az/universitetler/`)
- Rəqəmsal data (qiymət, reytinq, tələbə sayı) tərcümə olunmur.
- Mətn data (başlıq, təsvir, slug) hər dil üçün ayrıca saxlanılır.

### 7. Performans
- Redis object cache istifadə edin (server-də quraşdırılıb).
- Ağır sorğularda transient cache istifadə edin.
- Admin AJAX əvəzinə REST API istifadə edin.
- Şəkilləri lazy load edin.

---

## Mərhələlər

Hər mərhələ aşağıdakı formatda icra olunur:
1. Kodu yazın
2. Test edin (əgər mümkünsə)
3. `git add . && git commit -m "[X.Y] Təsvir" && git push origin main`

---

### FAZA 1: Əsas İnfrastruktur

#### Mərhələ 1.1 — Layihə faylları ✅
- [x] README.md
- [x] AGENTS.md
- [x] CLAUDE.md
- [x] DESIGN.md (placeholder)
- [x] .gitignore
- [x] Plugin qovluq strukturları
- [x] Git init + ilk commit + push

---

### FAZA 2: Çoxdillilik Plugin (`sit-multilang`) — **TAMAMLANDI** (2.1–2.5)

> Növbəti iş: **FAZA 4** (`sit-developer-application`). `sit-multilang` üzərində dəyişiklik üçün aşağıdakı FAZA 2 “Handoff” bölməsini oxuyun.

#### Mərhələ 2.1 — DB cədvəlləri və plugin aktivasiyası
- [x] Plugin əsas faylı: `sit-multilang/sit-multilang.php`
- [x] Aktivasiya hook: `wp_sit_translations`, `wp_sit_languages`, `wp_sit_strings` cədvəlləri yaratmaq
- [x] Default dilləri insert etmək (az, en, ru, fa, ar, kk)
- [x] Deaktivasiya hook
- [x] Uninstall hook
- **Commit:** `[2.1] Multilang plugin: DB cədvəlləri və aktivasiya`

#### Mərhələ 2.2 — Admin UI (dil idarəetməsi)
- [x] Admin menyu: "Dillər" səhifəsi
- [x] Dil əlavə/redaktə/silmə formu
- [x] Dil siyahısı cədvəli (WP_List_Table)
- [x] Dil aktivasiya/deaktivasiya toggle
- **Commit:** `[2.2] Multilang plugin: Admin dil idarəetmə UI`

#### Mərhələ 2.3 — Tərcümə UI (post/term edit ekranlarında)
- [x] Post edit ekranında dil tab-ları (az, en, ru, fa, ar, kk)
- [x] Hər tab-da: başlıq, təsvir, slug sahələri
- [x] Tərcümə save/update mexanizmi
- [x] Taxonomy term edit ekranında dil tab-ları
- **Commit:** `[2.3] Multilang plugin: Tərcümə UI tab sistemi`

#### Mərhələ 2.4 — URL routing
- [x] Rewrite rules: `/{lang}/` prefix
- [x] `init` hook-da dili URL-dən müəyyən etmək
- [x] `$current_lang` global dəyişən
- [x] Permalink filter: postların URL-lərinə dil prefix əlavə etmək
- [x] Hreflang meta tag-ları
- [x] 301 redirect: `/` → `/az/` (default dilə)
- **Commit:** `[2.4] Multilang plugin: URL routing və rewrite rules`

#### Mərhələ 2.5 — Language switcher və UI strings
- [x] Language switcher widget/shortcode
- [x] `sit__()` helper funksiyası (UI string tərcüməsi)
- [x] Admin: UI strings idarəetmə səhifəsi
- [x] Əsas UI stringlərin default tərcümələri
- **Commit:** `[2.5] Multilang plugin: Language switcher və UI strings`

#### FAZA 2 — Növbəti agentlər üçün (vacib detallar)

Bu bölmə **yalnız `sit-multilang`** üzərində davam edən və ya inteqrasiya yazan agentlər üçündür.

**Versiya və yükləmə sırası**  
- Plugin versiyası `SIT_MULTILANG_VERSION` (`sit-multilang.php`). `check_db_version()` köhnə versiyada `SIT_Activator::activate( true )` çağırır — **icazəsiz istifadəçi** kontekstində də DB/migration işləsin deyə.  
- `require` sırası: `class-sit-db` → `languages` → `translations` → `rewrite` → `strings` → `activator` → `sit-multilang-functions` → `class-sit-language-switcher`.

**Verilənlər bazası**  
- `wp_sit_languages` — dillər (kod, locale, rtl/ltr, default, aktiv).  
- `wp_sit_translations` — post/term sahə tərcümələri: `object_type` = `post` | `term`; sahələr: `title`, `content`, `excerpt`, `slug` (term-də excerpt yoxdur).  
- `wp_sit_strings` — UI sətirləri: `string_key` + `lang_code` unikaldır; `context` admin qrupu üçündür.  
- **Əsas dil** məzmunu: post üçün `wp_posts` (və term üçün `wp_terms` / taxonomy), qalan dillər cədvəldə.

**URL routing (2.4)** — klassik `rewrite_rules_array` prefiksi **istifadə olunmur**.  
- `init` **-1**: `$GLOBALS['sit_original_request_uri']` saxlanılır (dil keçidi eyni səhifənin başqa dil URL-i üçün).  
- `init` **0**: `early_set_current_lang` — xam `REQUEST_URI`-dən ilk seqment dildirsə qlobal `$sit_current_lang` / `$current_lang`.  
- `do_parse_request`: etibarlı dil prefiksi çıxarılıb `$_SERVER['REQUEST_URI']` yenilənir; dil olmayan ictimai path → **301** default dil prefiksi ilə.  
- **Bypass** (routing tətbiq olunmur): `is_admin`, `wp_doing_ajax`, cron, `REST_REQUEST`, `XMLRPC_REQUEST`, `WP_CLI`, filter `sit_multilang_bypass_routing`.  
- **Pretty permalinks** gözlənilir; deploy sonrası bəzən **Parametrlər → Daimi keçidlər → Yadda saxla** lazım ola bilər.

**Publik API (theme / digər plugin)**  
- `sit_get_current_lang()` — cari dil kodu; admində (ajax olmayanda) əsas dil.  
- `SIT_CURRENT_LANG` — `init:1`-də `sit_get_current_lang()` ilə təyin (sabit yenidən təyin olunmur).  
- `sit_get_translation( $id, $object_type, $lang, $field, $fallback )` — post/term tərcüməsi.  
- `sit__( $key, $default, $context )`, `sit_e()`, `sit_esc_html_e()` — `wp_sit_strings`; filter `sit__`.  
- `sit_get_page_url_in_language( $lang )` — cari səhifənin digər dil URL-i (`SIT_Rewrite::get_localized_url_for_lang`).  
- `SIT_Rewrite::localize_url( $url, $lang )` — istənilən daxili URL-ə dil prefiksi.

**Admin menyu**  
- Üst səviyyə: **Dillər** (`sit-languages`).  
- Alt səhifə: **UI sətirləri** (`sit-ui-strings`).

**Frontend**  
- Shortcode: `[sit_language_switcher]` (atributlar: `type`, `show_flags`, `show_names`, `class`).  
- Widget: **SIT: Dil keçidi**.  
- CSS: `assets/css/sit-switcher.css`.

**Post/term tərcümə UI**  
- Filter: `sit_multilang_supported_post_types`, `sit_multilang_supported_taxonomies` — hansı CPT/taxonomiyalarda meta box göstərilir.

**Uninstall**  
- `uninstall.php` bütün `wp_sit_*` cədvəllərini və `sit_*` option-larını silir.

---

### FAZA 3: Universitet & Proqram Plugin (`sit-developer`) — **TAMAMLANDI** (3.1–3.4)

> Növbəti iş: **FAZA 4** (`sit-developer-application`). Bu pluginə toxunacaq agentlər üçün aşağıdakı “Handoff” bölməsini oxuyun.

#### Mərhələ 3.1 — University CPT
- [x] Plugin əsas faylı: `sit-developer/sit-developer.php`
- [x] CPT: `university` (admin UI, meta boxes)
- [x] Meta fields: tuition_fee_min, student_count, founded_year, global_ranking, rating, website_url, logo, cover_image
- [x] Taxonomy: `city` (İstanbul, Ankara, İzmir...)
- [x] Taxonomy: `university_type` (Dövlət, Özəl)
- [x] Multilang inteqrasiyası (başlıq, təsvir, slug tərcümələri)
- **Commit:** `[3.1] Developer plugin: University CPT və taxonomies`

#### Mərhələ 3.2 — Program CPT
- [x] CPT: `program`
- [x] Meta fields: tuition_fee, duration, university_id (əlaqə), scholarship_available
- [x] Taxonomy: `degree_type` (Associate, Bachelor, Master, PhD)
- [x] Taxonomy: `program_language` (English, Turkish, Arabic...)
- [x] Taxonomy: `field_of_study` (Medicine, Engineering, Business...)
- [x] Multilang inteqrasiyası
- **Commit:** `[3.2] Developer plugin: Program CPT və taxonomies`

#### Mərhələ 3.3 — Filtrasiya sistemi (REST API)
- [x] REST endpoint: `/wp-json/sit/v1/programs` (filter parametrləri ilə)
- [x] REST endpoint: `/wp-json/sit/v1/universities`
- [x] Filter parametrləri: degree, language, city, field, price_min, price_max, sort
- [x] Pagination dəstəyi
- [x] Cache layer (transient/Redis)
- **Commit:** `[3.3] Developer plugin: REST API filtrasiya sistemi`

#### Mərhələ 3.4 — Əlavə data tipləri
- [x] CPT: `dormitory` (meta: university_id, price, distance, facilities)
- [x] CPT: `campus` (meta: university_id, address, lat/lng)
- [x] CPT: `scholarship` (meta: university_id, percentage, eligibility)
- [x] CPT: `faq` (meta: university_id, sort_order)
- [x] CPT: `review` (meta: university_id, rating, student_name, student_country)
- **Commit:** `[3.4] Developer plugin: Dormitory, Campus, Scholarship, FAQ, Review CPT-ləri`

#### FAZA 3 — Növbəti agentlər üçün (vacib detallar)

Bu bölmə **`sit-developer`** üzərində davam edən, REST/theme ilə inteqrasiya yazan və ya yeni CPT/meta əlavə edən agentlər üçündür.

**Versiya və fayl yükləmə sırası**  
- Plugin versiyası `SIT_DEVELOPER_VERSION` (`sit-developer.php`).  
- `require` sırası: `class-sit-developer-activator` → `class-sit-university-cpt` → `class-sit-university-meta` → `class-sit-program-cpt` → `class-sit-program-meta` → `class-sit-extra-cpts` → `class-sit-extra-meta` → `class-sit-rest-api` → `class-sit-developer`.  
- `SIT_Developer::__construct`: əvvəlcə `SIT_REST_API::register()`; sonra `init:0` textdomain; `init:5` — `SIT_University_CPT`, `SIT_Program_CPT`, `SIT_Extra_Cpts` (bu ardıcıllıqla universitet CPT-si əvvəl qeydiyyatdan keçir ki, əlavə CPT-lərin alt menyusu işləsin); `init:6` — üç meta sinfi.

**Siniflər və CPT sabitləri**  
- `SIT_University_CPT`: `POST_TYPE` = `university`, `TAX_CITY` = `city`, `TAX_TYPE` = `university_type`.  
- `SIT_Program_CPT`: `POST_TYPE` = `program`, `TAX_DEGREE` = `degree_type`, `TAX_LANG` = `program_language`, `TAX_FIELD` = `field_of_study`.  
- `SIT_Extra_Cpts`: `DORMITORY`, `CAMPUS`, `SCHOLARSHIP`, `FAQ`, `REVIEW` + `all()` bütün siyahını qaytarır. Bu beş CPT admin-də **`Universitetlər` alt menyusunda** (`show_in_menu` → `edit.php?post_type=university`).

**Post meta açarları** (`register_post_meta` + `show_in_rest` — əsasən `wp_postmeta`)  
- **Universitet:** `sit_tuition_fee_min`, `sit_student_count`, `sit_founded_year`, `sit_global_ranking`, `sit_rating`, `sit_website_url`, `sit_logo_id`, `sit_cover_image_id`.  
- **Proqram:** `sit_tuition_fee`, `sit_duration`, `sit_university_id`, `sit_scholarship_available` (boolean).  
- **Yataqxana:** `sit_university_id`, `sit_price`, `sit_distance`, `sit_facilities`.  
- **Kampus:** `sit_university_id`, `sit_address`, `sit_latitude`, `sit_longitude`.  
- **Təqaüd:** `sit_university_id`, `sit_percentage`, `sit_eligibility`.  
- **FAQ:** `sit_university_id`, `sit_sort_order`.  
- **Rəy:** `sit_university_id`, `sit_rating`, `sit_student_name`, `sit_student_country`.  

**Qeyd:** `review` CPT-də `sit_rating` universitetdəki `sit_rating` ilə eyni **meta açarıdır**, lakin fərqli `post_type` üçün qeydiyyat olunub — qarışıqlıq yaratmır.

**Rewrite slug-ları (qısa)**  
- CPT arxiv/tək: `universitetler`, `proqramlar`, `yataqxana`, `kampus`, `teqaud`, `faq`, `rey` (əlavə CPT-lərdə `has_archive` = false).  
- Taxonomiya: `seher`, `universitet-novu`, `derece-novu`, `proqram-dili`, `ixtisas-sahesi`.

**Aktivasiya** (`SIT_Developer_Activator`)  
- `SIT_University_CPT::register()`, `SIT_Program_CPT::register()`, `SIT_Extra_Cpts::register()`, sonra default terminlər (şəhər, universitet növü, dərəcə, proqram dili, ixtisas), `flush_rewrite_rules()`, `sit_developer_version` option.

**Çoxdillilik (`sit-multilang`)**  
- Bütün bu CPT və taxonomiyalar `show_ui` = true olduğu üçün tərcümə tab-ları **əlavə filter tələb etmədən** göstərilir (multilang default olaraq `show_ui` olan bütün post/tax tiplərini götürür). Yalnız UI-dan gizlədilmiş yeni CPT əlavə etsəniz və tərcümə istəsəniz `sit_multilang_supported_post_types` / `sit_multilang_supported_taxonomies` filterlərindən istifadə edin.

**REST API (`SIT_REST_API`, namespace `sit/v1`)**  
- Siyahı: `GET /wp-json/sit/v1/programs`, `GET /wp-json/sit/v1/universities` — sorğu parametrləri, cavab strukturu və `lang` davranışı üçün mövcud kodu `class-sit-rest-api.php`-də baxın.  
- Tək yazı: `GET .../programs/{id}`, `GET .../universities/{id}`.  
- **Keş:** transient açarları `sit_cache_prog_*`, `sit_cache_univ_*` + `sit_rest_cache_ver` (versiya dəyişəndə bütün siyahı keşləri etibarını itirir). Defolt TTL filterləri: `sit_rest_programs_cache_ttl`, `sit_rest_universities_cache_ttl` (defolt ~5 dəq).  
- **Keş sıfırlama:** `program`, `university` və `SIT_Extra_Cpts::all()` üzrə `save_post`; həmçinin `city`, `university_type`, `degree_type`, `program_language`, `field_of_study` üçün `set_object_terms`.

**Admin aktivlər**  
- Universitet loqo/örtük: `assets/js/sit-university-meta-admin.js` (yalnız `university` post ekranında yüklənir).

**Deploy / yeniləmə**  
- CPT əlavə və ya rewrite dəyişikliyindən sonra production-da **Parametrlər → Daimi keçidlər → Yadda saxla** tövsiyə olunur.

---

### FAZA 4: Müraciət Sistemi Plugin (`sit-developer-application`)

#### Mərhələ 4.1 — Müraciət formu və fayl yükləmə
- [ ] Plugin əsas faylı
- [ ] Custom DB table: `wp_sit_applications`
- [ ] Custom DB table: `wp_sit_application_documents`
- [ ] Frontend müraciət formu (shortcode/block)
- [ ] Fayl yükləmə (pasport, transkript, şəkil)
- [ ] Form validation
- **Commit:** `[4.1] Application plugin: Müraciət formu və fayl yükləmə`

#### Mərhələ 4.2 — İstifadəçi dashboard
- [ ] İstifadəçi qeydiyyatı (telefon, email, ad)
- [ ] Login/Register səhifələri
- [ ] Dashboard: müraciət siyahısı, status
- [ ] Müraciət detalları səhifəsi
- **Commit:** `[4.2] Application plugin: İstifadəçi dashboard`

#### Mərhələ 4.3 — Admin idarəetmə və bildirişlər
- [ ] Admin: müraciət siyahısı (WP_List_Table)
- [ ] Admin: müraciət detalları və status dəyişmə
- [ ] Email bildirişlər (yeni müraciət, status dəyişikliyi)
- [ ] WhatsApp inteqrasiyası (link)
- **Commit:** `[4.3] Application plugin: Admin idarəetmə və email bildirişlər`

---

### FAZA 5: Theme (`sit-developer-theme`)

#### Mərhələ 5.1 — Theme skeleton və ana səhifə
- [ ] Theme əsas faylları: style.css, functions.php, index.php
- [ ] Tailwind CSS inteqrasiyası
- [ ] Header (naviqasiya, language switcher, login/register)
- [ ] Footer (əlaqə, linklər, copyright)
- [ ] Ana səhifə: hero, universitetlər slider, addımlar, rəylər, bloqlar, "Why Choose Us"
- **Commit:** `[5.1] Theme: Skeleton, header, footer, ana səhifə`

#### Mərhələ 5.2 — Universitetlər səhifələri
- [ ] Universitetlər list səhifəsi (kartlar + filter sidebar)
- [ ] Tək universitet səhifəsi (reytinq, proqramlar, qəbul tələbləri, yataqxanalar, kampuslar, rəylər, FAQ)
- **Commit:** `[5.2] Theme: Universitetlər list və detail səhifələri`

#### Mərhələ 5.3 — Proqramlar səhifəsi
- [ ] Proqramlar list səhifəsi (cədvəl + filterlər)
- [ ] AJAX filtrasiya (REST API ilə)
- [ ] Tək proqram səhifəsi
- **Commit:** `[5.3] Theme: Proqramlar list və detail səhifələri`

#### Mərhələ 5.4 — Digər səhifələr
- [ ] Bloq list və tək bloq səhifəsi
- [ ] Haqqımızda səhifəsi
- [ ] Əlaqə səhifəsi (form + xəritə)
- [ ] Viza dəstəyi səhifəsi
- [ ] 404 səhifəsi
- **Commit:** `[5.4] Theme: Bloq, Haqqımızda, Əlaqə, Viza, 404`

#### Mərhələ 5.5 — RTL və responsive
- [ ] RTL stylesheet (fars, ərəb)
- [ ] Mobil responsive (bütün səhifələr)
- [ ] Dark/light mode (optional)
- **Commit:** `[5.5] Theme: RTL dəstəyi və mobil responsive`

---

### FAZA 6: SEO və Optimallaşdırma

#### Mərhələ 6.1 — SEO
- [ ] Meta tags (title, description, og:tags)
- [ ] XML Sitemap (hər dil üçün)
- [ ] Hreflang tags
- [ ] Schema.org structured data (University, Course, Review)
- [ ] Canonical URLs
- [ ] Robots.txt
- **Commit:** `[6.1] SEO: Meta tags, sitemap, structured data`

#### Mərhələ 6.2 — Performans
- [ ] Redis object cache konfiqurasiyası
- [ ] Page cache (transient əsaslı)
- [ ] Şəkil lazy loading
- [ ] CSS/JS minification
- [ ] Database query optimization
- **Commit:** `[6.2] Performans: Cache, lazy load, minification`

---

## Referans Sayt

**StudyLeo.com** — funksionallıq və UI baxımından referans olaraq istifadə olunur.
- 78 universitet, 6412 proqram, 256 bloq
- Filtrasiya sistemi, müraciət sistemi, çoxdillilik
- Tələbə rəyləri, FAQ, yataqxana, kampus məlumatları

Bizim sayt daha kiçik miqyasda olacaq (5-6 universitet, ~300-400 proqram) amma eyni funksionallıqla.
