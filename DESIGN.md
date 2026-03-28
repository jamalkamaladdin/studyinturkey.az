# DESIGN.md — Dizayn Sistemi

Bu fayl saytın vizual dizayn qaydalarını təsvir edir. Bütün AI agentlər və developerlər bu qaydaları izləməlidir.

## Dizayn Fəlsəfəsi

- **Minimalist və təmiz** — StudyLeo.com kimi sadə, light tonlarda
- **Rəngli backgroundlar YOXDUR** — ağ/açıq boz fon, rəng yalnız mətn, düymə və aksentlərdə
- **Çoxlu whitespace** — elementlər arasında geniş boşluq
- **Professional görünüş** — təhsil sektoru üçün etibarlı, ciddi

---

## Logo və Branding

| Asset | Fayl | İstifadə |
|---|---|---|
| Logo | `logo.svg` | Header, footer, about səhifəsi |
| Favicon | `favicon.png` | Brauzer tab ikonu |

---

## Rəng Sxemi

### Əsas Rənglər

| Rəng | HEX | İstifadə |
|---|---|---|
| **Primary (Yaşıl)** | `#11676a` | Headinglər, naviqasiya, əsas düymələr, linklər, ikonlar |
| **Secondary (Qırmızı)** | `#ff3131` | CTA düymələri, badges, xəbərdarlıqlar, aksentlər, qiymət vurğuları |

### Primary Tonları (Yaşıl)

```
primary-900: #0a3d3f    ← ən tünd (hover state, footer background)
primary-800: #0e5255    ← tünd (active state)
primary-700: #11676a    ← ƏSL RƏNG (headinglər, əsas elementlər)
primary-600: #1a7d80    ← orta (secondary düymələr)
primary-500: #2a9396    ← açıq (ikonlar, borderlər)
primary-400: #4aadaf    ← daha açıq (hover background)
primary-300: #7cc5c7    ← pastel (tag background)
primary-200: #b0dcdd    ← çox açıq (card hover, selected state)
primary-100: #d8eeef    ← ən açıq (section background, açıq fon)
primary-50:  #edf7f7    ← demək olar ağ (subtle background)
```

### Secondary Tonları (Qırmızı)

```
secondary-900: #991d1d  ← ən tünd (error state)
secondary-800: #cc2626  ← tünd (hover)
secondary-700: #ff3131  ← ƏSL RƏNG (CTA düymələr, aksentlər)
secondary-600: #ff5252  ← orta
secondary-500: #ff7070  ← açıq
secondary-400: #ff9494  ← pastel (notification badge)
secondary-300: #ffb8b8  ← çox açıq
secondary-200: #ffd6d6  ← ən açıq (error background)
secondary-100: #ffecec  ← demək olar ağ
```

### Neytral Rənglər

```
white:       #ffffff    ← əsas fon
gray-50:     #f9fafb    ← alternativ section fonu
gray-100:    #f3f4f6    ← card fonu, input fonu
gray-200:    #e5e7eb    ← borderlər, divider
gray-300:    #d1d5db    ← disabled state
gray-400:    #9ca3af    ← placeholder text
gray-500:    #6b7280    ← secondary text, meta text
gray-600:    #4b5563    ← body text
gray-700:    #374151    ← headinglər (kiçik)
gray-800:    #1f2937    ← əsas body text
gray-900:    #111827    ← ən tünd text
black:       #000000    ← istifadə etməyin, gray-900 istifadə edin
```

### Funksional Rənglər

```
success:     #059669    ← uğurlu əməliyyat, aktiv status
warning:     #d97706    ← xəbərdarlıq
error:       #ff3131    ← xəta (secondary ilə eyni)
info:        #11676a    ← məlumat (primary ilə eyni)
```

---

## Tipografiya

### Font Ailələri

```css
--font-heading: 'Inter', 'Segoe UI', sans-serif;
--font-body: 'Inter', 'Segoe UI', sans-serif;
--font-mono: 'JetBrains Mono', 'Consolas', monospace;
--font-arabic: 'Noto Sans Arabic', 'Inter', sans-serif;  /* fa, ar dillər üçün */
```

Google Fonts yükləmə:
```html
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
```

### Font Ölçüləri

| Ad | Ölçü | Çəki | İstifadə |
|---|---|---|---|
| `display-xl` | 48px / 3rem | 800 | Hero başlıq |
| `display` | 36px / 2.25rem | 700 | Səhifə başlığı |
| `h1` | 30px / 1.875rem | 700 | Section başlıqları |
| `h2` | 24px / 1.5rem | 600 | Alt başlıqlar |
| `h3` | 20px / 1.25rem | 600 | Kart başlıqları |
| `h4` | 18px / 1.125rem | 600 | Kiçik başlıqlar |
| `body-lg` | 18px / 1.125rem | 400 | Böyük body text |
| `body` | 16px / 1rem | 400 | Normal body text |
| `body-sm` | 14px / 0.875rem | 400 | Kiçik text, meta |
| `caption` | 12px / 0.75rem | 400 | Etiketlər, footnote |

### Heading Rəngləri

- **H1, H2 (böyük başlıqlar):** `primary-700` (#11676a)
- **H3, H4 (kiçik başlıqlar):** `gray-800` (#1f2937)
- **Body text:** `gray-800` (#1f2937)
- **Secondary text:** `gray-500` (#6b7280)

---

## Spacing Sistemi

Tailwind CSS spacing scale istifadə olunur:

```
0:    0px
1:    4px
2:    8px
3:    12px
4:    16px
5:    20px
6:    24px
8:    32px
10:   40px
12:   48px
16:   64px
20:   80px
24:   96px
32:   128px
```

### Section Spacing

| Element | Desktop | Mobil |
|---|---|---|
| Section padding (yuxarı/aşağı) | 96px (py-24) | 48px (py-12) |
| Section arasındakı boşluq | 0 (section padding kifayətdir) | 0 |
| Container max-width | 1280px | 100% |
| Container padding (yan) | 32px (px-8) | 16px (px-4) |

---

## Komponent Dizaynı

### Düymələr

```
Primary Button:
  background: #11676a (primary-700)
  text: white
  padding: 12px 24px
  border-radius: 8px
  font-weight: 600
  hover: #0e5255 (primary-800)
  
Secondary Button (Outline):
  background: transparent
  border: 2px solid #11676a
  text: #11676a
  hover: background #edf7f7 (primary-50)

CTA Button (Qırmızı):
  background: #ff3131 (secondary-700)
  text: white
  hover: #cc2626 (secondary-800)

Ghost Button:
  background: transparent
  text: #11676a
  hover: background #edf7f7

Disabled:
  background: #e5e7eb (gray-200)
  text: #9ca3af (gray-400)
  cursor: not-allowed
```

### Kartlar (Universitet/Proqram)

```
Card:
  background: white
  border: 1px solid #e5e7eb (gray-200)
  border-radius: 12px
  padding: 24px
  box-shadow: 0 1px 3px rgba(0,0,0,0.05)
  hover: box-shadow 0 4px 12px rgba(0,0,0,0.1), border-color #b0dcdd (primary-200)
  transition: all 0.2s ease
```

### Input / Form Sahələri

```
Input:
  background: white
  border: 1px solid #e5e7eb (gray-200)
  border-radius: 8px
  padding: 12px 16px
  font-size: 16px
  focus: border-color #11676a, ring 2px #d8eeef (primary-100)
  placeholder: #9ca3af (gray-400)
  error: border-color #ff3131
```

### Badges / Tags

```
Default Badge:
  background: #edf7f7 (primary-50)
  text: #11676a (primary-700)
  padding: 4px 12px
  border-radius: 9999px (full)
  font-size: 12px

Accent Badge:
  background: #ffecec (secondary-100)
  text: #ff3131 (secondary-700)

Scholarship Badge:
  background: #ecfdf5
  text: #059669 (success)
```

### Naviqasiya (Header)

```
Header:
  background: white
  border-bottom: 1px solid #f3f4f6 (gray-100)
  height: 72px
  position: sticky, top: 0
  z-index: 50
  box-shadow: none (scroll-da 0 1px 3px rgba(0,0,0,0.05))

Nav Link:
  color: #4b5563 (gray-600)
  font-weight: 500
  hover: color #11676a (primary-700)
  active: color #11676a, font-weight 600

Language Switcher:
  border: 1px solid #e5e7eb
  border-radius: 8px
  padding: 8px 12px
```

### Footer

```
Footer:
  background: #0a3d3f (primary-900)
  text: white
  padding: 64px 0 32px

Footer Link:
  color: #b0dcdd (primary-200)
  hover: color white
```

---

## Layout

### Grid Sistemi

```
Desktop (≥1024px):
  Universitetlər: 3 sütun grid, gap 24px
  Proqramlar: cədvəl (table) görünüşü
  Blog: 3 sütun grid

Tablet (768px-1023px):
  Universitetlər: 2 sütun grid
  Proqramlar: cədvəl (horizontal scroll)
  Blog: 2 sütun grid

Mobil (<768px):
  Universitetlər: 1 sütun
  Proqramlar: kart görünüşü (cədvəl əvəzinə)
  Blog: 1 sütun
```

### Responsive Breakpoints

```css
sm:  640px    /* Kiçik mobil */
md:  768px    /* Böyük mobil / Kiçik tablet */
lg:  1024px   /* Tablet / Kiçik desktop */
xl:  1280px   /* Desktop */
2xl: 1536px   /* Böyük ekran */
```

---

## RTL Dizayn Qaydaları (Fars / Ərəb)

```css
[dir="rtl"] {
  text-align: right;
  direction: rtl;
}

/* Layout əksi */
[dir="rtl"] .flex-row { flex-direction: row-reverse; }
[dir="rtl"] .ml-auto { margin-left: 0; margin-right: auto; }
[dir="rtl"] .pl-4 { padding-left: 0; padding-right: 1rem; }

/* Font */
[dir="rtl"] { font-family: 'Noto Sans Arabic', 'Inter', sans-serif; }
```

RTL-də dəyişən elementlər:
- Naviqasiya: logo sağda, menyu solda
- Sidebar: sağ tərəfdə
- Kartlar: mətn sağa düzülmüş
- İkonlar: istiqamət əksinə
- Breadcrumb: sağdan sola

RTL-də dəyişMƏyən elementlər:
- Rəqəmlər (qiymət, reytinq)
- Telefon nömrələri
- URL-lər
- Logo

---

## İkon Sistemi

**Lucide Icons** (https://lucide.dev/) istifadə olunacaq — açıq mənbəli, yüngül, SVG əsaslı.

```html
<!-- CDN -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- İstifadə -->
<i data-lucide="search"></i>
<i data-lucide="map-pin"></i>
<i data-lucide="graduation-cap"></i>
```

Tez-tez istifadə olunan ikonlar:

| İkon | Ad | İstifadə |
|---|---|---|
| 🔍 | `search` | Axtarış |
| 📍 | `map-pin` | Şəhər/lokasiya |
| 🎓 | `graduation-cap` | Dərəcə/proqram |
| 🏛️ | `building-2` | Universitet |
| 💰 | `banknote` | Təhsil haqqı |
| ⭐ | `star` | Reytinq |
| 📞 | `phone` | Əlaqə |
| ✉️ | `mail` | Email |
| 🌐 | `globe` | Dil/beynəlxalq |
| 📄 | `file-text` | Sənəd/müraciət |
| ✅ | `check-circle` | Uğurlu/təsdiqlənmiş |
| ❌ | `x-circle` | Xəta/rədd |
| 🔗 | `external-link` | Xarici link |
| 📅 | `calendar` | Tarix |
| 👤 | `user` | İstifadəçi/profil |
| 🏠 | `home` | Ana səhifə |

---

## Şəkil Qaydaları

| Tip | Ölçü | Format | İstifadə |
|---|---|---|---|
| Universitet logo | 200x200px | PNG/SVG | Kart, detail səhifə |
| Universitet cover | 1200x400px | JPEG/WebP | Detail səhifə hero |
| Universitet kart şəkli | 400x250px | JPEG/WebP | List səhifə kartı |
| Blog cover | 800x450px | JPEG/WebP | Blog list və detail |
| Hero background | 1920x800px | JPEG/WebP | Ana səhifə hero |
| Yataqxana şəkli | 600x400px | JPEG/WebP | Yataqxana kartı |

Bütün şəkillər **lazy load** olunmalıdır (`loading="lazy"`).
WebP formatı dəstəklənməlidir (`<picture>` tag ilə fallback).

---

## Tailwind CSS Konfiqurasiyası

```javascript
// tailwind.config.js
module.exports = {
  content: ['./**/*.php'],
  theme: {
    extend: {
      colors: {
        primary: {
          50:  '#edf7f7',
          100: '#d8eeef',
          200: '#b0dcdd',
          300: '#7cc5c7',
          400: '#4aadaf',
          500: '#2a9396',
          600: '#1a7d80',
          700: '#11676a',
          800: '#0e5255',
          900: '#0a3d3f',
        },
        secondary: {
          100: '#ffecec',
          200: '#ffd6d6',
          300: '#ffb8b8',
          400: '#ff9494',
          500: '#ff7070',
          600: '#ff5252',
          700: '#ff3131',
          800: '#cc2626',
          900: '#991d1d',
        },
      },
      fontFamily: {
        heading: ['Inter', 'Segoe UI', 'sans-serif'],
        body: ['Inter', 'Segoe UI', 'sans-serif'],
        arabic: ['Noto Sans Arabic', 'Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
```

---

## Referans

- **Dizayn ilhamı:** https://studyleo.com/ (minimalist, light, professional)
- **Fərqlər:** Bizim rəng sxemimiz yaşıl/qırmızı, StudyLeo mavi/ağ
- **Əsas prinsip:** Rəngli backgroundlar yoxdur, ağ fon üzərində təmiz tipografiya və minimal aksentlər
