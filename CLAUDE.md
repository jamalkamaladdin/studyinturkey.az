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

Bu cədvəllər `sit-developer-application` plugin tərəfindən yaradılır:

```sql
wp_sit_applications          -- Müraciətlər
wp_sit_application_documents -- Yüklənmiş sənədlər
```

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
