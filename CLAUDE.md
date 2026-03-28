# CLAUDE.md — StudyInTurkey.az Layihə Konteksti

Bu fayl AI agentlərə layihənin texniki detallarını verir.

## Server

- **Provider:** Contabo VPS
- **IP:** 84.46.255.38
- **User:** root
- **OS:** Ubuntu 24.04.4 LTS

SSH əmri (plink):
```bash
plink -ssh root@84.46.255.38 -pw "<SERVER_PASS>" -batch -hostkey "SHA256:PEqSkaPvnhj3+P8BV0g+u5eQZWjBTqlLdk/42XOYdKU" '<command>'
```
> **Qeyd:** Server parolu və digər gizli məlumatlar ana `C:\Users\Jamal Ali\CLAUDE.md` faylındadır.

## Server Stack

| Komponent | Versiya | Qeyd |
|---|---|---|
| Nginx | 1.24 | Web server |
| PHP | 8.3 + FPM | Socket: `/run/php/php8.3-fpm.sock` |
| MySQL | 8.0 | Root pass: bax ana CLAUDE.md |
| Redis | - | Object cache üçün |
| Certbot | - | SSL (Let's Encrypt) |
| WP-CLI | - | `wp` command |

## WordPress Quraşdırması

| Məlumat | Dəyər |
|---|---|
| Domain | `studyinturkey.az` |
| WP Path | `/var/www/studyinturkey.az/public/` |
| WP Admin URL | `studyinturkey.az/wp-admin` |
| Admin User | `camal` |
| Admin Pass | bax ana CLAUDE.md |
| DB Name | `studyinturkey_az` |
| DB User | `studyinturkey_az` |
| DB Password | bax ana CLAUDE.md |
| DB Host | `localhost` |
| Table Prefix | `wp_` |

## GitHub

| Məlumat | Dəyər |
|---|---|
| Account | `jamalkamaladdin` |
| Repo | `studyinturkey.az` |
| Token | `<GH_TOKEN>` (bax: ana CLAUDE.md və ya .env) |
| Clone URL | `https://<GH_TOKEN>@github.com/jamalkamaladdin/studyinturkey.az.git` |

## Git Əmrləri

```bash
# İlk dəfə clone
git clone https://<GH_TOKEN>@github.com/jamalkamaladdin/studyinturkey.az.git

# Commit və push
git add .
git commit -m "[X.Y] Təsvir"
git push origin main
```

## Plugin Deploy (serverdə)

Pluginlər repo-dan serverdəki WordPress-ə kopyalanır:

```bash
# Repo-nu serverdə yeniləmək
cd /tmp/sit-repo && git pull

# Pluginləri kopyalamaq
cp -r /tmp/sit-repo/sit-multilang/ /var/www/studyinturkey.az/public/wp-content/plugins/sit-multilang/
cp -r /tmp/sit-repo/sit-developer/ /var/www/studyinturkey.az/public/wp-content/plugins/sit-developer/
cp -r /tmp/sit-repo/sit-developer-application/ /var/www/studyinturkey.az/public/wp-content/plugins/sit-developer-application/

# Theme-i kopyalamaq
cp -r /tmp/sit-repo/sit-developer-theme/ /var/www/studyinturkey.az/public/wp-content/themes/sit-developer-theme/

# İcazələr
chown -R www-data:www-data /var/www/studyinturkey.az/public/wp-content/plugins/sit-*
chown -R www-data:www-data /var/www/studyinturkey.az/public/wp-content/themes/sit-developer-theme/
```

## Custom DB Cədvəlləri

Bu cədvəllər `sit-multilang` plugin tərəfindən yaradılır:

```sql
wp_sit_translations   -- Obyekt tərcümələri (post/term başlıq, təsvir, slug)
wp_sit_languages      -- Dil siyahısı və parametrləri
wp_sit_strings        -- UI string tərcümələri (düymələr, menyu, statik mətnlər)
```

### sit-multilang (FAZA 2 — hazırdır)

**Qovluq:** `sit-multilang/`. **Versiya:** `SIT_MULTILANG_VERSION` (`sit-multilang.php`).

**URL:** Dil prefiksi `do_parse_request` içində `REQUEST_URI`-dən çıxarılır (bütün `rewrite_rules` kopyalanmır). Kökdən dil olmadan giriş → 301 `/{default}/`. Orijinal URI `init:-1`-də `sit_original_request_uri` qlobalında saxlanır (language switcher).

**API:** `sit_get_current_lang()`, `sit_get_translation()`, `sit__()` / `sit_e()`, `sit_get_page_url_in_language()`, `SIT_Rewrite::localize_url()`. Əsas dil post/term üçün WP core sahələri; digər dillər `wp_sit_translations`.

**Admin:** `sit-languages`, alt menyu `sit-ui-strings`. Shortcode `[sit_language_switcher]`, widget **SIT: Dil keçidi**.

**İnteqrasiya:** `sit-developer` CPT-ləri `show_ui` ilə gəldiyi üçün tərcümə UI adətən avtomatikdir. Yalnız xüsusi CPT/tax gizlədilib tərcümə lazımdırsa `sit_multilang_supported_post_types` / `sit_multilang_supported_taxonomies` filterləri; frontda routing-dən çıxmaq üçün `sit_multilang_bypass_routing`.

### sit-developer (FAZA 3 — hazırdır)

**Qovluq:** `sit-developer/`. **Versiya:** `SIT_DEVELOPER_VERSION` (`sit-developer.php`).

**CPT:** `university`, `program`, `dormitory`, `campus`, `scholarship`, `faq`, `review`. Əlavə beş CPT admin-də **Universitetlər** alt menyusundadır.

**Taxonomiyalar (yalnız university/program):** `city`, `university_type` (universitet); `degree_type`, `program_language`, `field_of_study` (proqram).

**REST:** `sit/v1` — `programs`, `programs/{id}`, `universities`, `universities/{id}`; filtr, səhifələmə, `lang`, transient keş (`sit_cache_*`, `sit_rest_cache_ver`). Ətraflı cədvəl və meta açarları üçün repo-dakı `AGENTS.md` FAZA 3 “Handoff” bölməsinə baxın.

**Əsas fayllar:** `class-sit-university-cpt.php`, `class-sit-university-meta.php`, `class-sit-program-cpt.php`, `class-sit-program-meta.php`, `class-sit-extra-cpts.php`, `class-sit-extra-meta.php`, `class-sit-rest-api.php`, `class-sit-developer.php`, `class-sit-developer-activator.php`.

Bu cədvəllər `sit-developer-application` plugin tərəfindən yaradılır:

```sql
wp_sit_applications          -- Müraciətlər
wp_sit_application_documents -- Yüklənmiş sənədlər
```

### sit-developer-application (FAZA 4 — hazırdır)

**Qovluq:** `sit-developer-application/`. **Versiya:** `SIT_APPLICATION_VERSION` (`sit-developer-application.php`).

**Qısa funksionallıq:** shortcode müraciət formu (pasport/transkript/şəkil), namizəd qeydiyyat/giriş/portal (`?sit_my_app=id`), admin **Müraciətlər** menyusu (siyahı, detal, status, sənəd yükləmə), e-poçt bildirişləri, parametrlərdə WhatsApp (`wa.me`) linki.

**Shortcode-lar:** `[sit_application_form]`, `[sit_auth_register]`, `[sit_auth_login]`, `[sit_applicant_portal]`.

**Ətraflı:** cədvəl sahələri, hook/filterlər, option-lar, təhlükəsizlik və theme inteqrasiyası üçün repo-dakı **`AGENTS.md` FAZA 4 “Handoff”** bölməsi.

## Naming Conventions

| Nə | Format | Nümunə |
|---|---|---|
| Plugin prefix | `sit_` | `sit_get_translation()` |
| DB table prefix | `wp_sit_` | `wp_sit_translations` |
| Text domain | `studyinturkey` | `__('Apply Now', 'studyinturkey')` |
| CPT names | `university`, `program`, `dormitory`, `campus`, `scholarship`, `faq`, `review` | |
| Taxonomy names | `city`, `university_type`, `degree_type`, `program_language`, `field_of_study` | |
| REST namespace | `sit/v1` | `/wp-json/sit/v1/programs` |
| Option prefix | `sit_` | `sit_default_language` |
| Transient prefix | `sit_cache_` | `sit_cache_programs_filter_xyz` |

## Dillər

| Kod | Dil | İstiqamət | Default |
|---|---|---|---|
| `az` | Azərbaycan | LTR | **Bəli** |
| `en` | İngilis | LTR | Xeyr |
| `ru` | Rus | LTR | Xeyr |
| `fa` | Fars | RTL | Xeyr |
| `ar` | Ərəb | RTL | Xeyr |
| `kk` | Qazax | LTR | Xeyr |

## Referans

- **Referans sayt:** https://studyleo.com/
- **Funksionallıq:** Universitet axtarışı, proqram filtrasiyası, onlayn müraciət, çoxdillilik, bloq, FAQ, rəylər
- **Miqyas:** 5-6 universitet, ~300-400 proqram, 6 dil
