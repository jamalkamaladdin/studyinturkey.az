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

### FAZA 2: Çoxdillilik Plugin (`sit-multilang`)

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
- [ ] Post edit ekranında dil tab-ları (az, en, ru, fa, ar, kk)
- [ ] Hər tab-da: başlıq, təsvir, slug sahələri
- [ ] Tərcümə save/update mexanizmi
- [ ] Taxonomy term edit ekranında dil tab-ları
- **Commit:** `[2.3] Multilang plugin: Tərcümə UI tab sistemi`

#### Mərhələ 2.4 — URL routing
- [ ] Rewrite rules: `/{lang}/` prefix
- [ ] `init` hook-da dili URL-dən müəyyən etmək
- [ ] `$current_lang` global dəyişən
- [ ] Permalink filter: postların URL-lərinə dil prefix əlavə etmək
- [ ] Hreflang meta tag-ları
- [ ] 301 redirect: `/` → `/az/` (default dilə)
- **Commit:** `[2.4] Multilang plugin: URL routing və rewrite rules`

#### Mərhələ 2.5 — Language switcher və UI strings
- [ ] Language switcher widget/shortcode
- [ ] `sit__()` helper funksiyası (UI string tərcüməsi)
- [ ] Admin: UI strings idarəetmə səhifəsi
- [ ] Əsas UI stringlərin default tərcümələri
- **Commit:** `[2.5] Multilang plugin: Language switcher və UI strings`

---

### FAZA 3: Universitet & Proqram Plugin (`sit-developer`)

#### Mərhələ 3.1 — University CPT
- [ ] Plugin əsas faylı: `sit-developer/sit-developer.php`
- [ ] CPT: `university` (admin UI, meta boxes)
- [ ] Meta fields: tuition_fee_min, student_count, founded_year, global_ranking, rating, website_url, logo, cover_image
- [ ] Taxonomy: `city` (İstanbul, Ankara, İzmir...)
- [ ] Taxonomy: `university_type` (Dövlət, Özəl)
- [ ] Multilang inteqrasiyası (başlıq, təsvir, slug tərcümələri)
- **Commit:** `[3.1] Developer plugin: University CPT və taxonomies`

#### Mərhələ 3.2 — Program CPT
- [ ] CPT: `program`
- [ ] Meta fields: tuition_fee, duration, university_id (əlaqə), scholarship_available
- [ ] Taxonomy: `degree_type` (Associate, Bachelor, Master, PhD)
- [ ] Taxonomy: `program_language` (English, Turkish, Arabic...)
- [ ] Taxonomy: `field_of_study` (Medicine, Engineering, Business...)
- [ ] Multilang inteqrasiyası
- **Commit:** `[3.2] Developer plugin: Program CPT və taxonomies`

#### Mərhələ 3.3 — Filtrasiya sistemi (REST API)
- [ ] REST endpoint: `/wp-json/sit/v1/programs` (filter parametrləri ilə)
- [ ] REST endpoint: `/wp-json/sit/v1/universities`
- [ ] Filter parametrləri: degree, language, city, field, price_min, price_max, sort
- [ ] Pagination dəstəyi
- [ ] Cache layer (transient/Redis)
- **Commit:** `[3.3] Developer plugin: REST API filtrasiya sistemi`

#### Mərhələ 3.4 — Əlavə data tipləri
- [ ] CPT: `dormitory` (meta: university_id, price, distance, facilities)
- [ ] CPT: `campus` (meta: university_id, address, lat/lng)
- [ ] CPT: `scholarship` (meta: university_id, percentage, eligibility)
- [ ] CPT: `faq` (meta: university_id, sort_order)
- [ ] CPT: `review` (meta: university_id, rating, student_name, student_country)
- **Commit:** `[3.4] Developer plugin: Dormitory, Campus, Scholarship, FAQ, Review CPT-ləri`

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
