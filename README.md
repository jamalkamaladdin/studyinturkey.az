# StudyInTurkey.az

Türkiyədə təhsil almaq istəyən beynəlxalq tələbələr üçün universitet axtarış və müraciət platforması.

## Layihə Haqqında

StudyInTurkey.az, tələbələri Türkiyədəki universitetlərə qəbul prosesində dəstəkləyən WordPress əsaslı platformadır. Sayt 5-6 universitet və ~300-400 proqramı əhatə edir, 6 dildə xidmət göstərir.

## Texnologiya Stack

| Komponent | Texnologiya |
|---|---|
| CMS | WordPress 6.x |
| Server | Nginx 1.24 + PHP 8.3-FPM |
| Database | MySQL 8.0 |
| Cache | Redis |
| SSL | Let's Encrypt (Certbot) |
| OS | Ubuntu 24.04 LTS |
| DNS | Cloudflare |

## Dillər

| Dil | Kod | İstiqamət | Default |
|---|---|---|---|
| Azərbaycan | `az` | LTR | **Bəli** |
| İngilis | `en` | LTR | Xeyr |
| Rus | `ru` | LTR | Xeyr |
| Fars | `fa` | RTL | Xeyr |
| Ərəb | `ar` | RTL | Xeyr |
| Qazax | `kk` | LTR | Xeyr |

## Repo Strukturu

```
studyinturkey.az/
├── README.md                 # Bu fayl
├── AGENTS.md                 # AI agentlər üçün qaydalar və mərhələlər
├── CLAUDE.md                 # Layihəyə xas texniki məlumatlar
├── DESIGN.md                 # Dizayn sistemi (rənglər, fontlar, layout)
├── sit-multilang/            # Plugin: Çoxdillilik sistemi
├── sit-developer/            # Plugin: Universitet & Proqram idarəetməsi
├── sit-developer-application/# Plugin: Müraciət sistemi
├── sit-developer-theme/      # Theme: Custom WordPress theme
└── .gitignore
```

## Plugin Arxitekturası

Hər plugin ayrıca qovluqdur. Serverdə `wp-content/plugins/` qovluğuna kopyalanır.

| Plugin | Məqsəd |
|---|---|
| `sit-multilang` | Pluginsiz çoxdillilik sistemi (6 dil, RTL dəstəyi) |
| `sit-developer` | Universitet, Proqram, Yataqxana, Təqaüd, FAQ CPT-ləri |
| `sit-developer-application` | Müraciət formu, istifadəçi dashboard, status tracking |

| Theme | Məqsəd |
|---|---|
| `sit-developer-theme` | Custom WordPress theme (Tailwind CSS, responsive, RTL) |

## Deploy Prosesi

```bash
# Serverdə repo-nu clone etmək
cd /var/www/studyinturkey.az/public/wp-content/plugins/
git clone https://<token>@github.com/jamalkamaladdin/studyinturkey.az.git /tmp/sit-repo

# Pluginləri kopyalamaq
cp -r /tmp/sit-repo/sit-multilang/ ./sit-multilang/
cp -r /tmp/sit-repo/sit-developer/ ./sit-developer/
cp -r /tmp/sit-repo/sit-developer-application/ ./sit-developer-application/

# Theme-i kopyalamaq
cp -r /tmp/sit-repo/sit-developer-theme/ ../themes/sit-developer-theme/

# İcazələr
chown -R www-data:www-data ./sit-multilang/ ./sit-developer/ ./sit-developer-application/ ../themes/sit-developer-theme/
```

## Əlaqə

- **Domain:** studyinturkey.az
- **Server:** 84.46.255.38 (Contabo VPS)
