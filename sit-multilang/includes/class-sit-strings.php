<?php
/**
 * wp_sit_strings — UI string tərcümələri.
 */

defined( 'ABSPATH' ) || exit;

class SIT_Strings {

    /**
     * Tərcümə dəyəri (boş sətir = tapılmayıb).
     */
    public static function get_value( string $string_key, string $lang_code ): string {
        global $wpdb;

        $table = SIT_DB::strings_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $val = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT string_value FROM {$table} WHERE string_key = %s AND lang_code = %s LIMIT 1",
                $string_key,
                $lang_code
            )
        );

        return null !== $val ? (string) $val : '';
    }

    /**
     * @return array<string, string> lang_code => value
     */
    public static function get_all_values_for_key( string $string_key ): array {
        global $wpdb;

        $table = SIT_DB::strings_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT lang_code, string_value, context FROM {$table} WHERE string_key = %s",
                $string_key
            )
        );

        $out = [];
        if ( $rows ) {
            foreach ( $rows as $row ) {
                $out[ $row->lang_code ] = (string) $row->string_value;
            }
        }
        return $out;
    }

    /**
     * Context birinci sətirdən götürülür (eyni key üçün eyni context saxlanılır).
     */
    public static function get_context_for_key( string $string_key ): string {
        global $wpdb;

        $table = SIT_DB::strings_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $ctx = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT context FROM {$table} WHERE string_key = %s LIMIT 1",
                $string_key
            )
        );

        return $ctx ? (string) $ctx : 'general';
    }

    /**
     * @param array<string, string> $values lang_code => string_value
     */
    public static function save_key( string $string_key, string $context, array $values ): void {
        global $wpdb;

        $table  = SIT_DB::strings_table();
        $string_key = self::sanitize_string_key( $string_key );
        if ( '' === $string_key ) {
            return;
        }

        $context = sanitize_key( $context );
        if ( '' === $context ) {
            $context = 'general';
        }

        foreach ( $values as $lang_code => $raw ) {
            $lang_code = sanitize_key( (string) $lang_code );
            if ( ! SIT_Languages::is_valid_code( $lang_code ) ) {
                continue;
            }
            $text = is_string( $raw ) ? wp_kses_post( $raw ) : '';

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->delete(
                $table,
                [
                    'string_key' => $string_key,
                    'lang_code'  => $lang_code,
                ]
            );

            if ( '' === trim( $text ) ) {
                continue;
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $wpdb->insert(
                $table,
                [
                    'string_key'   => $string_key,
                    'context'      => $context,
                    'lang_code'    => $lang_code,
                    'string_value' => $text,
                ]
            );
        }
    }

    public static function delete_key( string $string_key ): void {
        global $wpdb;

        $table = SIT_DB::strings_table();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->delete( $table, [ 'string_key' => self::sanitize_string_key( $string_key ) ] );
    }

    public static function sanitize_string_key( string $key ): string {
        $key = strtolower( trim( $key ) );
        $key = preg_replace( '/[^a-z0-9._-]/', '', $key );
        return substr( (string) $key, 0, 255 );
    }

    /**
     * Siyahı üçün: fərqli string_key sətirləri.
     *
     * @return array<int, object{string_key: string, context: string}>
     */
    public static function list_keys_paginated( int $page, int $per_page, string $search = '' ): array {
        global $wpdb;

        $table = SIT_DB::strings_table();
        $page  = max( 1, $page );
        $off   = ( $page - 1 ) * $per_page;

        $where = '';
        if ( $search !== '' ) {
            $like  = '%' . $wpdb->esc_like( $search ) . '%';
            $where = $wpdb->prepare( ' WHERE string_key LIKE %s ', $like );
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        return $wpdb->get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL
            "SELECT string_key, MAX(context) AS context FROM {$table} {$where} GROUP BY string_key ORDER BY string_key ASC LIMIT " . (int) $per_page . ' OFFSET ' . (int) $off
        );
    }

    public static function count_distinct_keys( string $search = '' ): int {
        global $wpdb;

        $table = SIT_DB::strings_table();
        $where = '';
        if ( $search !== '' ) {
            $like  = '%' . $wpdb->esc_like( $search ) . '%';
            $where = $wpdb->prepare( ' WHERE string_key LIKE %s ', $like );
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
        return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT string_key) FROM {$table} {$where}" );
    }

    /**
     * Aktivasiyada: default tərcümələr (yalnız boş olanlar üçün insert).
     */
    public static function seed_defaults(): void {
        global $wpdb;

        $table = SIT_DB::strings_table();
        $defs  = self::get_default_strings();

        foreach ( $defs as $key => $row ) {
            $context = isset( $row['context'] ) ? sanitize_key( $row['context'] ) : 'general';
            $langs   = isset( $row['langs'] ) && is_array( $row['langs'] ) ? $row['langs'] : [];

            foreach ( $langs as $code => $text ) {
                $code = sanitize_key( (string) $code );
                if ( ! SIT_Languages::is_valid_code( $code ) ) {
                    continue;
                }
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $exists = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT id FROM {$table} WHERE string_key = %s AND lang_code = %s LIMIT 1",
                        $key,
                        $code
                    )
                );
                if ( $exists ) {
                    continue;
                }
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                $wpdb->insert(
                    $table,
                    [
                        'string_key'   => $key,
                        'context'      => $context,
                        'lang_code'    => $code,
                        'string_value' => (string) $text,
                    ]
                );
            }
        }
    }

    /**
     * @return array<string, array{context: string, langs: array<string, string>}>
     */
    private static function get_default_strings(): array {
        return [
            'nav.home'         => [
                'context' => 'nav',
                'langs'   => [
                    'az' => 'Ana səhifə',
                    'en' => 'Home',
                    'ru' => 'Главная',
                    'fa' => 'خانه',
                    'ar' => 'الرئيسية',
                    'kk' => 'Басты бет',
                ],
            ],
            'nav.universities' => [
                'context' => 'nav',
                'langs'   => [
                    'az' => 'Universitetlər',
                    'en' => 'Universities',
                    'ru' => 'Университеты',
                    'fa' => 'دانشگاه‌ها',
                    'ar' => 'الجامعات',
                    'kk' => 'Университеттер',
                ],
            ],
            'nav.programs'     => [
                'context' => 'nav',
                'langs'   => [
                    'az' => 'Proqramlar',
                    'en' => 'Programs',
                    'ru' => 'Программы',
                    'fa' => 'برنامه‌ها',
                    'ar' => 'البرامج',
                    'kk' => 'Бағдарламалар',
                ],
            ],
            'nav.blog'         => [
                'context' => 'nav',
                'langs'   => [
                    'az' => 'Bloq',
                    'en' => 'Blog',
                    'ru' => 'Блог',
                    'fa' => 'وبلاگ',
                    'ar' => 'المدونة',
                    'kk' => 'Блог',
                ],
            ],
            'nav.contact'      => [
                'context' => 'nav',
                'langs'   => [
                    'az' => 'Əlaqə',
                    'en' => 'Contact',
                    'ru' => 'Контакты',
                    'fa' => 'تماس',
                    'ar' => 'اتصل بنا',
                    'kk' => 'Байланыс',
                ],
            ],
            'nav.about'        => [
                'context' => 'nav',
                'langs'   => [
                    'az' => 'Haqqımızda',
                    'en' => 'About',
                    'ru' => 'О нас',
                    'fa' => 'درباره ما',
                    'ar' => 'من نحن',
                    'kk' => 'Біз туралы',
                ],
            ],
            'btn.apply_now'    => [
                'context' => 'buttons',
                'langs'   => [
                    'az' => 'Müraciət et',
                    'en' => 'Apply now',
                    'ru' => 'Подать заявку',
                    'fa' => 'درخواست دهید',
                    'ar' => 'قدّم الآن',
                    'kk' => 'Өтініш беру',
                ],
            ],
            'btn.read_more'    => [
                'context' => 'buttons',
                'langs'   => [
                    'az' => 'Ətraflı',
                    'en' => 'Read more',
                    'ru' => 'Подробнее',
                    'fa' => 'بیشتر بخوانید',
                    'ar' => 'اقرأ المزيد',
                    'kk' => 'Толығырақ',
                ],
            ],
            'btn.search'       => [
                'context' => 'buttons',
                'langs'   => [
                    'az' => 'Axtar',
                    'en' => 'Search',
                    'ru' => 'Поиск',
                    'fa' => 'جستجو',
                    'ar' => 'بحث',
                    'kk' => 'Іздеу',
                ],
            ],
            'btn.submit'       => [
                'context' => 'buttons',
                'langs'   => [
                    'az' => 'Göndər',
                    'en' => 'Submit',
                    'ru' => 'Отправить',
                    'fa' => 'ارسال',
                    'ar' => 'إرسال',
                    'kk' => 'Жіберу',
                ],
            ],
            'common.loading'   => [
                'context' => 'common',
                'langs'   => [
                    'az' => 'Yüklənir…',
                    'en' => 'Loading…',
                    'ru' => 'Загрузка…',
                    'fa' => 'در حال بارگذاری…',
                    'ar' => 'جاري التحميل…',
                    'kk' => 'Жүктелуде…',
                ],
            ],
            'common.no_results' => [
                'context' => 'common',
                'langs'   => [
                    'az' => 'Nəticə tapılmadı',
                    'en' => 'No results found',
                    'ru' => 'Ничего не найдено',
                    'fa' => 'نتیجه‌ای یافت نشد',
                    'ar' => 'لا توجد نتائج',
                    'kk' => 'Нәтиже жоқ',
                ],
            ],
            'footer.tagline'   => [
                'context' => 'footer',
                'langs'   => [
                    'az' => 'Türkiyədə təhsil üçün etibarlı tərəfdaşınız',
                    'en' => 'Your trusted partner for studying in Turkey',
                    'ru' => 'Ваш надёжный партнёр для учёбы в Турции',
                    'fa' => 'شریک مطمئن شما برای تحصیل در ترکیه',
                    'ar' => 'شريكك الموثوق للدراسة في تركيا',
                    'kk' => 'Түркияда оқу үшін сенімді серіктесіңіз',
                ],
            ],
            'form.name'        => [
                'context' => 'forms',
                'langs'   => [
                    'az' => 'Ad',
                    'en' => 'Name',
                    'ru' => 'Имя',
                    'fa' => 'نام',
                    'ar' => 'الاسم',
                    'kk' => 'Аты',
                ],
            ],
            'form.email'       => [
                'context' => 'forms',
                'langs'   => [
                    'az' => 'E-poçt',
                    'en' => 'Email',
                    'ru' => 'Эл. почта',
                    'fa' => 'ایمیل',
                    'ar' => 'البريد الإلكتروني',
                    'kk' => 'Электрондық пошта',
                ],
            ],
            'form.phone'       => [
                'context' => 'forms',
                'langs'   => [
                    'az' => 'Telefon',
                    'en' => 'Phone',
                    'ru' => 'Телефон',
                    'fa' => 'تلفن',
                    'ar' => 'الهاتف',
                    'kk' => 'Телефон',
                ],
            ],
            /* ── Gettext-compatible: key = Azerbaijani text from esc_html_e() calls ── */
            'Müraciət et'      => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Müraciət et', 'en' => 'Apply now', 'ru' => 'Подать заявку', 'fa' => 'درخواست دهید', 'ar' => 'قدّم الآن', 'kk' => 'Өтініш беру' ],
            ],
            'Müraciət'         => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Müraciət', 'en' => 'Application', 'ru' => 'Заявка', 'fa' => 'درخواست', 'ar' => 'طلب', 'kk' => 'Өтініш' ],
            ],
            'Universitet'      => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Universitet', 'en' => 'University', 'ru' => 'Университет', 'fa' => 'دانشگاه', 'ar' => 'الجامعة', 'kk' => 'Университет' ],
            ],
            'Proqram'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Proqram', 'en' => 'Program', 'ru' => 'Программа', 'fa' => 'برنامه', 'ar' => 'البرنامج', 'kk' => 'Бағдарлама' ],
            ],
            'Proqramlar'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Proqramlar', 'en' => 'Programs', 'ru' => 'Программы', 'fa' => 'برنامه‌ها', 'ar' => 'البرامج', 'kk' => 'Бағдарламалар' ],
            ],
            'Dərəcə'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Dərəcə', 'en' => 'Degree', 'ru' => 'Степень', 'fa' => 'مدرک', 'ar' => 'الدرجة', 'kk' => 'Дәреже' ],
            ],
            'Ödəniş'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Ödəniş', 'en' => 'Tuition', 'ru' => 'Оплата', 'fa' => 'شهریه', 'ar' => 'الرسوم', 'kk' => 'Төлем' ],
            ],
            'Dillər'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Dillər', 'en' => 'Languages', 'ru' => 'Языки', 'fa' => 'زبان‌ها', 'ar' => 'اللغات', 'kk' => 'Тілдер' ],
            ],
            'Filtrlər'         => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Filtrlər', 'en' => 'Filters', 'ru' => 'Фильтры', 'fa' => 'فیلترها', 'ar' => 'المرشحات', 'kk' => 'Сүзгілер' ],
            ],
            'Hamısı'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Hamısı', 'en' => 'All', 'ru' => 'Все', 'fa' => 'همه', 'ar' => 'الكل', 'kk' => 'Барлығы' ],
            ],
            'Tətbiq et'        => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Tətbiq et', 'en' => 'Apply', 'ru' => 'Применить', 'fa' => 'اعمال', 'ar' => 'تطبيق', 'kk' => 'Қолдану' ],
            ],
            'Sıfırla'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Sıfırla', 'en' => 'Reset', 'ru' => 'Сбросить', 'fa' => 'بازنشانی', 'ar' => 'إعادة تعيين', 'kk' => 'Қалпына келтіру' ],
            ],
            'Şəhər'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Şəhər', 'en' => 'City', 'ru' => 'Город', 'fa' => 'شهر', 'ar' => 'المدينة', 'kk' => 'Қала' ],
            ],
            'Proqram dili'     => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Proqram dili', 'en' => 'Language', 'ru' => 'Язык', 'fa' => 'زبان', 'ar' => 'اللغة', 'kk' => 'Тіл' ],
            ],
            'İxtisas sahəsi'   => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'İxtisas sahəsi', 'en' => 'Field of study', 'ru' => 'Направление', 'fa' => 'رشته تحصیلی', 'ar' => 'مجال الدراسة', 'kk' => 'Мамандық саласы' ],
            ],
            'Qiymət min'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qiymət min', 'en' => 'Price min', 'ru' => 'Цена мин', 'fa' => 'حداقل قیمت', 'ar' => 'السعر الأدنى', 'kk' => 'Баға мин' ],
            ],
            'Qiymət max'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qiymət max', 'en' => 'Price max', 'ru' => 'Цена макс', 'fa' => 'حداکثر قیمت', 'ar' => 'السعر الأقصى', 'kk' => 'Баға макс' ],
            ],
            'Sıralama'         => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Sıralama', 'en' => 'Sort', 'ru' => 'Сортировка', 'fa' => 'مرتب‌سازی', 'ar' => 'الترتيب', 'kk' => 'Сұрыптау' ],
            ],
            'Ən yeni'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Ən yeni', 'en' => 'Newest', 'ru' => 'Новейшие', 'fa' => 'جدیدترین', 'ar' => 'الأحدث', 'kk' => 'Ең жаңа' ],
            ],
            'Ən köhnə'         => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Ən köhnə', 'en' => 'Oldest', 'ru' => 'Старейшие', 'fa' => 'قدیمی‌ترین', 'ar' => 'الأقدم', 'kk' => 'Ең ескі' ],
            ],
            'Yüklənir…'        => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Yüklənir…', 'en' => 'Loading…', 'ru' => 'Загрузка…', 'fa' => 'در حال بارگذاری…', 'ar' => 'جاري التحميل…', 'kk' => 'Жүктелуде…' ],
            ],
            '/ il'             => [
                'context' => 'gettext',
                'langs'   => [ 'az' => '/ il', 'en' => '/ year', 'ru' => '/ год', 'fa' => '/ سال', 'ar' => '/ سنة', 'kk' => '/ жыл' ],
            ],
            'Əvvəlki'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Əvvəlki', 'en' => 'Previous', 'ru' => 'Предыдущая', 'fa' => 'قبلی', 'ar' => 'السابق', 'kk' => 'Алдыңғы' ],
            ],
            'Növbəti'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Növbəti', 'en' => 'Next', 'ru' => 'Следующая', 'fa' => 'بعدی', 'ar' => 'التالي', 'kk' => 'Келесі' ],
            ],
            'Universitet proqramları' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Universitet proqramları', 'en' => 'University programs', 'ru' => 'Программы университетов', 'fa' => 'برنامه‌های دانشگاه', 'ar' => 'برامج الجامعة', 'kk' => 'Университет бағдарламалары' ],
            ],
            'Niyə bizi seçməli?' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Niyə bizi seçməli?', 'en' => 'Why choose us?', 'ru' => 'Почему выбирают нас?', 'fa' => 'چرا ما را انتخاب کنید?', 'ar' => 'لماذا تختارنا؟', 'kk' => 'Неге бізді таңдау керек?' ],
            ],
            'Universitet haqqında' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Universitet haqqında', 'en' => 'About the university', 'ru' => 'О университете', 'fa' => 'درباره دانشگاه', 'ar' => 'عن الجامعة', 'kk' => 'Университет туралы' ],
            ],
            'Haqqında'         => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Haqqında', 'en' => 'About', 'ru' => 'О нас', 'fa' => 'درباره', 'ar' => 'حول', 'kk' => 'Туралы' ],
            ],
            'Missiya'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Missiya', 'en' => 'Mission', 'ru' => 'Миссия', 'fa' => 'ماموریت', 'ar' => 'المهمة', 'kk' => 'Миссия' ],
            ],
            'Tələbə həyatı'    => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Tələbə həyatı', 'en' => 'Student life', 'ru' => 'Студенческая жизнь', 'fa' => 'زندگی دانشجویی', 'ar' => 'الحياة الطلابية', 'kk' => 'Студенттік өмір' ],
            ],
            'Bütün proqramları gör' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bütün proqramları gör', 'en' => 'See all programs', 'ru' => 'Все программы', 'fa' => 'مشاهده همه برنامه‌ها', 'ar' => 'عرض جميع البرامج', 'kk' => 'Барлық бағдарламаларды көру' ],
            ],
            'Bütün proqramlar' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bütün proqramlar', 'en' => 'All programs', 'ru' => 'Все программы', 'fa' => 'تمام برنامه‌ها', 'ar' => 'جميع البرامج', 'kk' => 'Барлық бағдарламалар' ],
            ],
            'Dil'              => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Dil', 'en' => 'Language', 'ru' => 'Язык', 'fa' => 'زبان', 'ar' => 'اللغة', 'kk' => 'Тіл' ],
            ],
            'Qəbul tələbləri'  => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qəbul tələbləri', 'en' => 'Admission requirements', 'ru' => 'Требования к поступлению', 'fa' => 'شرایط پذیرش', 'ar' => 'متطلبات القبول', 'kk' => 'Қабылдау талаптары' ],
            ],
            'Tələb olunan sənədlər' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Tələb olunan sənədlər', 'en' => 'Required documents', 'ru' => 'Необходимые документы', 'fa' => 'مدارک مورد نیاز', 'ar' => 'المستندات المطلوبة', 'kk' => 'Қажетті құжаттар' ],
            ],
            'Başlanğıc:'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Başlanğıc:', 'en' => 'Start:', 'ru' => 'Начало:', 'fa' => 'شروع:', 'ar' => 'البداية:', 'kk' => 'Басталуы:' ],
            ],
            'Son tarix:'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Son tarix:', 'en' => 'Deadline:', 'ru' => 'Дедлайн:', 'fa' => 'مهلت:', 'ar' => 'الموعد النهائي:', 'kk' => 'Мерзімі:' ],
            ],
            'Bütün qəbul tələbləri' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bütün qəbul tələbləri', 'en' => 'All admission requirements', 'ru' => 'Все требования к поступлению', 'fa' => 'تمام شرایط پذیرش', 'ar' => 'جميع متطلبات القبول', 'kk' => 'Барлық қабылдау талаптары' ],
            ],
            'Yataqxanalar'     => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Yataqxanalar', 'en' => 'Dormitories', 'ru' => 'Общежития', 'fa' => 'خوابگاه‌ها', 'ar' => 'المساكن الجامعية', 'kk' => 'Жатақханалар' ],
            ],
            'Kampuslar'        => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Kampuslar', 'en' => 'Campuses', 'ru' => 'Кампусы', 'fa' => 'دانشکده‌ها', 'ar' => 'الحرم الجامعي', 'kk' => 'Кампустар' ],
            ],
            'Beynəlxalq tələbələr' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Beynəlxalq tələbələr', 'en' => 'International students', 'ru' => 'Иностранные студенты', 'fa' => 'دانشجویان بین‌المللی', 'ar' => 'الطلاب الدوليون', 'kk' => 'Халықаралық студенттер' ],
            ],
            'Tələbələr'        => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Tələbələr', 'en' => 'Students', 'ru' => 'Студенты', 'fa' => 'دانشجویان', 'ar' => 'الطلاب', 'kk' => 'Студенттер' ],
            ],
            'Xarici tələbələr' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Xarici tələbələr', 'en' => 'Foreign students', 'ru' => 'Иностранные студенты', 'fa' => 'دانشجویان خارجی', 'ar' => 'الطلاب الأجانب', 'kk' => 'Шетелдік студенттер' ],
            ],
            'Qəbul faizi'      => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qəbul faizi', 'en' => 'Acceptance rate', 'ru' => 'Процент приёма', 'fa' => 'نرخ پذیرش', 'ar' => 'نسبة القبول', 'kk' => 'Қабылдау пайызы' ],
            ],
            'Təqaüdlər'        => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Təqaüdlər', 'en' => 'Scholarships', 'ru' => 'Стипендии', 'fa' => 'بورسیه‌ها', 'ar' => 'المنح الدراسية', 'kk' => 'Стипендиялар' ],
            ],
            'Təqaüd'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Təqaüd', 'en' => 'Scholarship', 'ru' => 'Стипендия', 'fa' => 'بورسیه', 'ar' => 'منحة دراسية', 'kk' => 'Стипендия' ],
            ],
            'Xəritədə aç'     => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Xəritədə aç', 'en' => 'Open on map', 'ru' => 'Открыть на карте', 'fa' => 'باز کردن در نقشه', 'ar' => 'افتح في الخريطة', 'kk' => 'Картадан ашу' ],
            ],
            'Ətraflı'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Ətraflı', 'en' => 'Details', 'ru' => 'Подробнее', 'fa' => 'جزئیات', 'ar' => 'تفاصيل', 'kk' => 'Толығырақ' ],
            ],
            /* ── Application form ── */
            'Əlaqə və şəxsiyyət' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Əlaqə və şəxsiyyət', 'en' => 'Contact & Identity', 'ru' => 'Контакт и личность', 'fa' => 'تماس و هویت', 'ar' => 'الاتصال والهوية', 'kk' => 'Байланыс пен жеке мәліметтер' ],
            ],
            'Tam ad (pasportdakı kimi)' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Tam ad (pasportdakı kimi)', 'en' => 'Full name (as in passport)', 'ru' => 'Полное имя (как в паспорте)', 'fa' => 'نام کامل (مطابق گذرنامه)', 'ar' => 'الاسم الكامل (كما في جواز السفر)', 'kk' => 'Толық аты-жөні (паспорт бойынша)' ],
            ],
            'E-poçt'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'E-poçt', 'en' => 'Email', 'ru' => 'Эл. почта', 'fa' => 'ایمیل', 'ar' => 'البريد الإلكتروني', 'kk' => 'Электрондық пошта' ],
            ],
            'Telefon (WhatsApp mümkünsə)' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Telefon (WhatsApp mümkünsə)', 'en' => 'Phone (WhatsApp if possible)', 'ru' => 'Телефон (WhatsApp если возможно)', 'fa' => 'تلفن (واتساپ اگر ممکن است)', 'ar' => 'الهاتف (واتساب إذا أمكن)', 'kk' => 'Телефон (мүмкін болса WhatsApp)' ],
            ],
            'Doğum tarixi'     => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Doğum tarixi', 'en' => 'Date of birth', 'ru' => 'Дата рождения', 'fa' => 'تاریخ تولد', 'ar' => 'تاريخ الميلاد', 'kk' => 'Туған күні' ],
            ],
            'Vətəndaşlıq'     => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Vətəndaşlıq', 'en' => 'Citizenship', 'ru' => 'Гражданство', 'fa' => 'تابعیت', 'ar' => 'الجنسية', 'kk' => 'Азаматтығы' ],
            ],
            'Təhsil və proqram' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Təhsil və proqram', 'en' => 'Education & Program', 'ru' => 'Образование и программа', 'fa' => 'تحصیلات و برنامه', 'ar' => 'التعليم والبرنامج', 'kk' => 'Білім және бағдарлама' ],
            ],
            'Dərəcə səviyyəsi' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Dərəcə səviyyəsi', 'en' => 'Degree level', 'ru' => 'Уровень степени', 'fa' => 'سطح مدرک', 'ar' => 'مستوى الدرجة', 'kk' => 'Дәреже деңгейі' ],
            ],
            'Proqram seçin'    => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Proqram seçin', 'en' => 'Select a program', 'ru' => 'Выберите программу', 'fa' => 'یک برنامه انتخاب کنید', 'ar' => 'اختر برنامجًا', 'kk' => 'Бағдарлама таңдаңыз' ],
            ],
            'Sənədlər'         => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Sənədlər', 'en' => 'Documents', 'ru' => 'Документы', 'fa' => 'مدارک', 'ar' => 'المستندات', 'kk' => 'Құжаттар' ],
            ],
            'Pasport kopyası'  => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Pasport kopyası', 'en' => 'Passport copy', 'ru' => 'Копия паспорта', 'fa' => 'کپی گذرنامه', 'ar' => 'نسخة جواز السفر', 'kk' => 'Паспорт көшірмесі' ],
            ],
            'Transkript'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Transkript', 'en' => 'Transcript', 'ru' => 'Транскрипт', 'fa' => 'کارنامه', 'ar' => 'كشف الدرجات', 'kk' => 'Транскрипт' ],
            ],
            'Şəkil (3x4)'     => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Şəkil (3x4)', 'en' => 'Photo (3x4)', 'ru' => 'Фото (3x4)', 'fa' => 'عکس (3x4)', 'ar' => 'صورة (3x4)', 'kk' => 'Сурет (3x4)' ],
            ],
            'Əlavə mesaj (istəyə bağlı)' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Əlavə mesaj (istəyə bağlı)', 'en' => 'Additional message (optional)', 'ru' => 'Дополнительное сообщение (необязательно)', 'fa' => 'پیام اضافی (اختیاری)', 'ar' => 'رسالة إضافية (اختيارية)', 'kk' => 'Қосымша хабарлама (міндетті емес)' ],
            ],
            'Göndər'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Göndər', 'en' => 'Submit', 'ru' => 'Отправить', 'fa' => 'ارسال', 'ar' => 'إرسال', 'kk' => 'Жіберу' ],
            ],
            'Müraciəti göndər' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Müraciəti göndər', 'en' => 'Submit application', 'ru' => 'Отправить заявку', 'fa' => 'ارسال درخواست', 'ar' => 'إرسال الطلب', 'kk' => 'Өтінішті жіберу' ],
            ],
            'Rəsmi vebsayt'   => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Rəsmi vebsayt', 'en' => 'Official website', 'ru' => 'Официальный сайт', 'fa' => 'وب‌سایت رسمی', 'ar' => 'الموقع الرسمي', 'kk' => 'Ресми веб-сайт' ],
            ],
            'Nəticə yoxdur.'   => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Nəticə yoxdur.', 'en' => 'No results.', 'ru' => 'Нет результатов.', 'fa' => 'نتیجه‌ای نیست.', 'ar' => 'لا توجد نتائج.', 'kk' => 'Нәтиже жоқ.' ],
            ],
            'Filtrlərə uyğun proqram tapılmadı.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Filtrlərə uyğun proqram tapılmadı.', 'en' => 'No programs found matching the filters.', 'ru' => 'Программы по фильтрам не найдены.', 'fa' => 'برنامه‌ای مطابق فیلترها یافت نشد.', 'ar' => 'لم يتم العثور على برامج تطابق المرشحات.', 'kk' => 'Сүзгілерге сәйкес бағдарлама табылмады.' ],
            ],
            'Qiymət: aşağıdan yuxarı' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qiymət: aşağıdan yuxarı', 'en' => 'Price: low to high', 'ru' => 'Цена: по возрастанию', 'fa' => 'قیمت: کم به زیاد', 'ar' => 'السعر: من الأقل إلى الأعلى', 'kk' => 'Баға: төменнен жоғарыға' ],
            ],
            'Qiymət: yuxarıdan aşağı' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qiymət: yuxarıdan aşağı', 'en' => 'Price: high to low', 'ru' => 'Цена: по убыванию', 'fa' => 'قیمت: زیاد به کم', 'ar' => 'السعر: من الأعلى إلى الأقل', 'kk' => 'Баға: жоғарыдан төменге' ],
            ],
            'Ad (A–Z)'         => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Ad (A–Z)', 'en' => 'Name (A–Z)', 'ru' => 'Название (А–Я)', 'fa' => 'نام (الف–ی)', 'ar' => 'الاسم (أ–ي)', 'kk' => 'Аты (А–Я)' ],
            ],
            'Ad (Z–A)'         => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Ad (Z–A)', 'en' => 'Name (Z–A)', 'ru' => 'Название (Я–А)', 'fa' => 'نام (ی–الف)', 'ar' => 'الاسم (ي–أ)', 'kk' => 'Аты (Я–А)' ],
            ],
            /* ── Header & Navigation ── */
            'Məzmuna keç'      => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Məzmuna keç', 'en' => 'Skip to content', 'ru' => 'Перейти к содержимому', 'fa' => 'رفتن به محتوا', 'ar' => 'انتقل إلى المحتوى', 'kk' => 'Мазмұнға өту' ],
            ],
            'Menyunu aç'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Menyunu aç', 'en' => 'Open menu', 'ru' => 'Открыть меню', 'fa' => 'باز کردن منو', 'ar' => 'فتح القائمة', 'kk' => 'Мәзірді ашу' ],
            ],
            'Əsas naviqasiya'  => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Əsas naviqasiya', 'en' => 'Main navigation', 'ru' => 'Главная навигация', 'fa' => 'ناوبری اصلی', 'ar' => 'التنقل الرئيسي', 'kk' => 'Негізгі навигация' ],
            ],
            'İşıqlı və ya qaranlıq görünüş' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'İşıqlı və ya qaranlıq görünüş', 'en' => 'Light or dark mode', 'ru' => 'Светлая или тёмная тема', 'fa' => 'حالت روشن یا تاریک', 'ar' => 'الوضع الفاتح أو الداكن', 'kk' => 'Жарық немесе қараңғы режим' ],
            ],
            'Kabinet'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Kabinet', 'en' => 'Dashboard', 'ru' => 'Кабинет', 'fa' => 'پنل کاربری', 'ar' => 'لوحة التحكم', 'kk' => 'Кабинет' ],
            ],
            'Giriş'            => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Giriş', 'en' => 'Login', 'ru' => 'Вход', 'fa' => 'ورود', 'ar' => 'تسجيل الدخول', 'kk' => 'Кіру' ],
            ],
            'Qeydiyyat'        => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qeydiyyat', 'en' => 'Register', 'ru' => 'Регистрация', 'fa' => 'ثبت‌نام', 'ar' => 'التسجيل', 'kk' => 'Тіркелу' ],
            ],
            'Əsas menyu'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Əsas menyu', 'en' => 'Main menu', 'ru' => 'Главное меню', 'fa' => 'منوی اصلی', 'ar' => 'القائمة الرئيسية', 'kk' => 'Басты мәзір' ],
            ],
            'Çörək qırıntısı'  => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Çörək qırıntısı', 'en' => 'Breadcrumb', 'ru' => 'Хлебные крошки', 'fa' => 'مسیر ناوبری', 'ar' => 'مسار التنقل', 'kk' => 'Навигация жолы' ],
            ],
            'Ana səhifə'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Ana səhifə', 'en' => 'Home', 'ru' => 'Главная', 'fa' => 'خانه', 'ar' => 'الرئيسية', 'kk' => 'Басты бет' ],
            ],
            /* ── Footer ── */
            '© %s %s. Bütün hüquqlar qorunur.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => '© %s %s. Bütün hüquqlar qorunur.', 'en' => '© %s %s. All rights reserved.', 'ru' => '© %s %s. Все права защищены.', 'fa' => '© %s %s. تمامی حقوق محفوظ است.', 'ar' => '© %s %s. جميع الحقوق محفوظة.', 'kk' => '© %s %s. Барлық құқықтар қорғалған.' ],
            ],
            /* ── Homepage ── */
            'Türkiyədə təhsil' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Türkiyədə təhsil', 'en' => 'Study in Turkey', 'ru' => 'Обучение в Турции', 'fa' => 'تحصیل در ترکیه', 'ar' => 'الدراسة في تركيا', 'kk' => 'Түркияда оқу' ],
            ],
            'Türkiyədə təhsil üçün universitet və proqram seçimi.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Türkiyədə təhsil üçün universitet və proqram seçimi.', 'en' => 'Choose the right university and program for studying in Turkey.', 'ru' => 'Выбор университета и программы для обучения в Турции.', 'fa' => 'انتخاب دانشگاه و برنامه برای تحصیل در ترکیه.', 'ar' => 'اختيار الجامعة والبرنامج للدراسة في تركيا.', 'kk' => 'Түркияда оқу үшін университет пен бағдарлама таңдау.' ],
            ],
            'Proqramları kəşf et' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Proqramları kəşf et', 'en' => 'Explore programs', 'ru' => 'Изучить программы', 'fa' => 'کاوش برنامه‌ها', 'ar' => 'استكشاف البرامج', 'kk' => 'Бағдарламаларды зерттеу' ],
            ],
            'Universitetlər'   => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Universitetlər', 'en' => 'Universities', 'ru' => 'Университеты', 'fa' => 'دانشگاه‌ها', 'ar' => 'الجامعات', 'kk' => 'Университеттер' ],
            ],
            'Niyə StudyInTurkey?' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Niyə StudyInTurkey?', 'en' => 'Why StudyInTurkey?', 'ru' => 'Почему StudyInTurkey?', 'fa' => 'چرا StudyInTurkey?', 'ar' => 'لماذا StudyInTurkey؟', 'kk' => 'Неге StudyInTurkey?' ],
            ],
            'Şəffaf məlumat'   => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Şəffaf məlumat', 'en' => 'Transparent information', 'ru' => 'Прозрачная информация', 'fa' => 'اطلاعات شفاف', 'ar' => 'معلومات شفافة', 'kk' => 'Мөлдір ақпарат' ],
            ],
            'Ödəniş aralığı, kampus və rəylər kimi real göstəricilər.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Ödəniş aralığı, kampus və rəylər kimi real göstəricilər.', 'en' => 'Real indicators like tuition range, campus and reviews.', 'ru' => 'Реальные показатели: стоимость, кампус и отзывы.', 'fa' => 'شاخص‌های واقعی مانند محدوده شهریه، دانشکده و نظرات.', 'ar' => 'مؤشرات حقيقية مثل نطاق الرسوم والحرم الجامعي والمراجعات.', 'kk' => 'Төлем диапазоны, кампус және пікірлер сияқты нақты көрсеткіштер.' ],
            ],
            'Filtr və REST API' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Filtr və REST API', 'en' => 'Filter & REST API', 'ru' => 'Фильтр и REST API', 'fa' => 'فیلتر و REST API', 'ar' => 'التصفية و REST API', 'kk' => 'Сүзгі және REST API' ],
            ],
            'Proqramları sürətli süzün; məlumatlar strukturlaşdırılıb.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Proqramları sürətli süzün; məlumatlar strukturlaşdırılıb.', 'en' => 'Quickly filter programs; data is structured.', 'ru' => 'Быстро фильтруйте программы; данные структурированы.', 'fa' => 'برنامه‌ها را سریع فیلتر کنید؛ داده‌ها ساختاریافته هستند.', 'ar' => 'قم بتصفية البرامج بسرعة؛ البيانات منظمة.', 'kk' => 'Бағдарламаларды жылдам сүзіңіз; деректер құрылымдалған.' ],
            ],
            'Onlayn müraciət'  => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Onlayn müraciət', 'en' => 'Online application', 'ru' => 'Онлайн-заявка', 'fa' => 'درخواست آنلاین', 'ar' => 'التقديم عبر الإنترنت', 'kk' => 'Онлайн өтініш' ],
            ],
            'Sənəd yükləmə və namizəd kabineti bir platformada.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Sənəd yükləmə və namizəd kabineti bir platformada.', 'en' => 'Document upload and applicant dashboard in one platform.', 'ru' => 'Загрузка документов и кабинет кандидата на одной платформе.', 'fa' => 'بارگذاری مدارک و پنل متقاضی در یک پلتفرم.', 'ar' => 'رفع المستندات ولوحة تحكم المتقدم في منصة واحدة.', 'kk' => 'Құжат жүктеу және үміткер кабинеті бір платформада.' ],
            ],
            'Çoxdilli məzmun'  => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Çoxdilli məzmun', 'en' => 'Multilingual content', 'ru' => 'Многоязычный контент', 'fa' => 'محتوای چندزبانه', 'ar' => 'محتوى متعدد اللغات', 'kk' => 'Көптілді мазмұн' ],
            ],
            'Azərbaycan, ingilis, rus və digər dillərdə eyni keyfiyyətli təqdimat.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Azərbaycan, ingilis, rus və digər dillərdə eyni keyfiyyətli təqdimat.', 'en' => 'Same quality presentation in Azerbaijani, English, Russian and other languages.', 'ru' => 'Одинаково качественная подача на азербайджанском, английском, русском и других языках.', 'fa' => 'ارائه با کیفیت یکسان به زبان‌های آذربایجانی، انگلیسی، روسی و زبان‌های دیگر.', 'ar' => 'عرض بنفس الجودة بالأذربيجانية والإنجليزية والروسية ولغات أخرى.', 'kk' => 'Әзербайжан, ағылшын, орыс және басқа тілдерде бірдей сапалы ұсыну.' ],
            ],
            'Qəbul dəstəyi'    => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qəbul dəstəyi', 'en' => 'Admission support', 'ru' => 'Поддержка при поступлении', 'fa' => 'پشتیبانی پذیرش', 'ar' => 'دعم القبول', 'kk' => 'Қабылдау қолдауы' ],
            ],
            'Komandamız əlaqə saxlayır və yönləndirir.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Komandamız əlaqə saxlayır və yönləndirir.', 'en' => 'Our team contacts and guides you.', 'ru' => 'Наша команда связывается с вами и направляет.', 'fa' => 'تیم ما با شما تماس می‌گیرد و راهنمایی می‌کند.', 'ar' => 'فريقنا يتواصل معك ويوجهك.', 'kk' => 'Біздің команда сізбен хабарласып, бағыт береді.' ],
            ],
            'Necə işləyir?'    => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Necə işləyir?', 'en' => 'How does it work?', 'ru' => 'Как это работает?', 'fa' => 'چگونه کار می‌کند؟', 'ar' => 'كيف يعمل؟', 'kk' => 'Қалай жұмыс істейді?' ],
            ],
            'Qısa addımlarla Türkiyədə təhsilə yolunuzu planlayın.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qısa addımlarla Türkiyədə təhsilə yolunuzu planlayın.', 'en' => 'Plan your education in Turkey in a few simple steps.', 'ru' => 'Спланируйте обучение в Турции за несколько шагов.', 'fa' => 'با چند قدم ساده مسیر تحصیل در ترکیه را برنامه‌ریزی کنید.', 'ar' => 'خطط لدراستك في تركيا بخطوات بسيطة.', 'kk' => 'Бірнеше қарапайым қадаммен Түркиядағы білім жолыңызды жоспарлаңыз.' ],
            ],
            'Proqramlara bax'  => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Proqramlara bax', 'en' => 'View programs', 'ru' => 'Смотреть программы', 'fa' => 'مشاهده برنامه‌ها', 'ar' => 'عرض البرامج', 'kk' => 'Бағдарламаларды көру' ],
            ],
            'Sənədləri hazırlayın' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Sənədləri hazırlayın', 'en' => 'Prepare documents', 'ru' => 'Подготовьте документы', 'fa' => 'مدارک را آماده کنید', 'ar' => 'جهّز المستندات', 'kk' => 'Құжаттарды дайындаңыз' ],
            ],
            'Pasport, transkript və şəkil yükləyin.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Pasport, transkript və şəkil yükləyin.', 'en' => 'Upload passport, transcript and photo.', 'ru' => 'Загрузите паспорт, транскрипт и фото.', 'fa' => 'گذرنامه، کارنامه و عکس را بارگذاری کنید.', 'ar' => 'ارفع جواز السفر وكشف الدرجات والصورة.', 'kk' => 'Паспорт, транскрипт және суретті жүктеңіз.' ],
            ],
            'Müraciət edin'    => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Müraciət edin', 'en' => 'Apply', 'ru' => 'Подайте заявку', 'fa' => 'درخواست دهید', 'ar' => 'قدّم طلبك', 'kk' => 'Өтініш беріңіз' ],
            ],
            'Formu doldurun, statusu kabinetdə izləyin.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Formu doldurun, statusu kabinetdə izləyin.', 'en' => 'Fill in the form, track status in your dashboard.', 'ru' => 'Заполните форму, отслеживайте статус в кабинете.', 'fa' => 'فرم را پر کنید، وضعیت را در پنل دنبال کنید.', 'ar' => 'املأ النموذج، تابع الحالة في لوحة التحكم.', 'kk' => 'Форманы толтырыңыз, мәртебені кабинетте бақылаңыз.' ],
            ],
            'Filtrasiya, müraciət və çoxdilli məzmun ilə namizədlərə aydın yol xəritəsi təqdim edirik.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Filtrasiya, müraciət və çoxdilli məzmun ilə namizədlərə aydın yol xəritəsi təqdim edirik.', 'en' => 'We provide candidates with a clear roadmap through filtering, application and multilingual content.', 'ru' => 'Мы предоставляем кандидатам чёткую дорожную карту с фильтрацией, заявками и многоязычным контентом.', 'fa' => 'با فیلتر، درخواست و محتوای چندزبانه نقشه راه روشنی به متقاضیان ارائه می‌دهیم.', 'ar' => 'نقدم للمرشحين خارطة طريق واضحة من خلال التصفية والتقديم والمحتوى متعدد اللغات.', 'kk' => 'Біз үміткерлерге сүзгі, өтініш және көптілді мазмұн арқылы анық жол картасын ұсынамыз.' ],
            ],
            'Namizədlər və universitetlər üçün sadə, təhlükəsiz proses.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Namizədlər və universitetlər üçün sadə, təhlükəsiz proses.', 'en' => 'Simple, secure process for candidates and universities.', 'ru' => 'Простой и безопасный процесс для кандидатов и университетов.', 'fa' => 'فرایند ساده و امن برای متقاضیان و دانشگاه‌ها.', 'ar' => 'عملية بسيطة وآمنة للمرشحين والجامعات.', 'kk' => 'Үміткерлер мен университеттер үшін қарапайым, қауіпсіз процесс.' ],
            ],
            'Namizəd qeydiyyatı və kabinet üçün səhifələrdə application shortcode-larından istifadə edin.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Namizəd qeydiyyatı və kabinet üçün səhifələrdə application shortcode-larından istifadə edin.', 'en' => 'Use application shortcodes on pages for candidate registration and dashboard.', 'ru' => 'Используйте шорткоды приложения на страницах для регистрации кандидатов и кабинета.', 'fa' => 'از شورت‌کدهای درخواست در صفحات برای ثبت‌نام متقاضی و پنل استفاده کنید.', 'ar' => 'استخدم أكواد التطبيق القصيرة في الصفحات لتسجيل المرشحين ولوحة التحكم.', 'kk' => 'Үміткерді тіркеу және кабинет үшін беттерде қолданба шорт-кодтарын пайдаланыңыз.' ],
            ],
            'Müraciət formu üçün sit-developer-application plugin aktivləşdirin.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Müraciət formu üçün sit-developer-application plugin aktivləşdirin.', 'en' => 'Activate sit-developer-application plugin for the application form.', 'ru' => 'Активируйте плагин sit-developer-application для формы заявки.', 'fa' => 'افزونه sit-developer-application را برای فرم درخواست فعال کنید.', 'ar' => 'قم بتفعيل إضافة sit-developer-application لنموذج التقديم.', 'kk' => 'Өтініш формасы үшін sit-developer-application плагинін іске қосыңыз.' ],
            ],
            /* ── University page ── */
            'Reytinq'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Reytinq', 'en' => 'Rating', 'ru' => 'Рейтинг', 'fa' => 'رتبه‌بندی', 'ar' => 'التصنيف', 'kk' => 'Рейтинг' ],
            ],
            'Səhifə bölmələri' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Səhifə bölmələri', 'en' => 'Page sections', 'ru' => 'Разделы страницы', 'fa' => 'بخش‌های صفحه', 'ar' => 'أقسام الصفحة', 'kk' => 'Бет бөлімдері' ],
            ],
            'Qəbul'            => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qəbul', 'en' => 'Admission', 'ru' => 'Приём', 'fa' => 'پذیرش', 'ar' => 'القبول', 'kk' => 'Қабылдау' ],
            ],
            'Kampus'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Kampus', 'en' => 'Campus', 'ru' => 'Кампус', 'fa' => 'دانشکده', 'ar' => 'الحرم الجامعي', 'kk' => 'Кампус' ],
            ],
            'Qəbul tələbləri (tam)' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qəbul tələbləri (tam)', 'en' => 'Admission requirements (full)', 'ru' => 'Требования к поступлению (полные)', 'fa' => 'شرایط پذیرش (کامل)', 'ar' => 'متطلبات القبول (كاملة)', 'kk' => 'Қабылдау талаптары (толық)' ],
            ],
            'Təsis'            => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Təsis', 'en' => 'Founded', 'ru' => 'Основан', 'fa' => 'تأسیس', 'ar' => 'التأسيس', 'kk' => 'Құрылған' ],
            ],
            'Reytinq (qlobal)' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Reytinq (qlobal)', 'en' => 'Ranking (global)', 'ru' => 'Рейтинг (мировой)', 'fa' => 'رتبه‌بندی (جهانی)', 'ar' => 'التصنيف (عالمي)', 'kk' => 'Рейтинг (жаһандық)' ],
            ],
            'İlkin ödəniş (min.)' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'İlkin ödəniş (min.)', 'en' => 'Tuition (min.)', 'ru' => 'Оплата (мин.)', 'fa' => 'شهریه (حداقل)', 'ar' => 'الرسوم (الحد الأدنى)', 'kk' => 'Төлем (мин.)' ],
            ],
            'Universitetə qayıt' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Universitetə qayıt', 'en' => 'Back to university', 'ru' => 'Назад к университету', 'fa' => 'بازگشت به دانشگاه', 'ar' => 'العودة إلى الجامعة', 'kk' => 'Университетке оралу' ],
            ],
            'Rəsmi sayt'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Rəsmi sayt', 'en' => 'Official site', 'ru' => 'Официальный сайт', 'fa' => 'سایت رسمی', 'ar' => 'الموقع الرسمي', 'kk' => 'Ресми сайт' ],
            ],
            'Veb sayt'         => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Veb sayt', 'en' => 'Website', 'ru' => 'Веб-сайт', 'fa' => 'وب‌سایت', 'ar' => 'الموقع الإلكتروني', 'kk' => 'Веб-сайт' ],
            ],
            'Qısa statistika və qəbul göstəriciləri.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qısa statistika və qəbul göstəriciləri.', 'en' => 'Brief statistics and admission indicators.', 'ru' => 'Краткая статистика и показатели приёма.', 'fa' => 'آمار مختصر و شاخص‌های پذیرش.', 'ar' => 'إحصائيات موجزة ومؤشرات القبول.', 'kk' => 'Қысқаша статистика және қабылдау көрсеткіштері.' ],
            ],
            /* ── Reviews ── */
            'Tələbə rəyləri'   => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Tələbə rəyləri', 'en' => 'Student reviews', 'ru' => 'Отзывы студентов', 'fa' => 'نظرات دانشجویان', 'ar' => 'آراء الطلاب', 'kk' => 'Студент пікірлері' ],
            ],
            'Tələbələrimizin real təcrübələri' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Tələbələrimizin real təcrübələri', 'en' => 'Real experiences of our students', 'ru' => 'Реальный опыт наших студентов', 'fa' => 'تجربیات واقعی دانشجویان ما', 'ar' => 'التجارب الحقيقية لطلابنا', 'kk' => 'Студенттеріміздің нақты тәжірибелері' ],
            ],
            'Reytinq: %s/5'    => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Reytinq: %s/5', 'en' => 'Rating: %s/5', 'ru' => 'Рейтинг: %s/5', 'fa' => 'امتیاز: %s/5', 'ar' => 'التقييم: %s/5', 'kk' => 'Рейтинг: %s/5' ],
            ],
            'Rəylər'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Rəylər', 'en' => 'Reviews', 'ru' => 'Отзывы', 'fa' => 'نظرات', 'ar' => 'المراجعات', 'kk' => 'Пікірлер' ],
            ],
            'Real təcrübələr — seçiminizi asanlaşdırır.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Real təcrübələr — seçiminizi asanlaşdırır.', 'en' => 'Real experiences — making your choice easier.', 'ru' => 'Реальный опыт — облегчает ваш выбор.', 'fa' => 'تجربیات واقعی — انتخاب شما را آسان‌تر می‌کند.', 'ar' => 'تجارب حقيقية — تسهّل اختيارك.', 'kk' => 'Нақты тәжірибелер — таңдауыңызды жеңілдетеді.' ],
            ],
            /* ── FAQ ── */
            'Tez-tez verilən suallar' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Tez-tez verilən suallar', 'en' => 'Frequently asked questions', 'ru' => 'Часто задаваемые вопросы', 'fa' => 'سوالات متداول', 'ar' => 'الأسئلة الشائعة', 'kk' => 'Жиі қойылатын сұрақтар' ],
            ],
            'FAQ'              => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'FAQ', 'en' => 'FAQ', 'ru' => 'FAQ', 'fa' => 'FAQ', 'ar' => 'FAQ', 'kk' => 'FAQ' ],
            ],
            /* ── Dormitories ── */
            'Yaxın yataqxanalar' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Yaxın yataqxanalar', 'en' => 'Nearby dormitories', 'ru' => 'Ближайшие общежития', 'fa' => 'خوابگاه‌های نزدیک', 'ar' => 'المساكن القريبة', 'kk' => 'Жақын жатақханалар' ],
            ],
            'Bütün yataqxanalar' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bütün yataqxanalar', 'en' => 'All dormitories', 'ru' => 'Все общежития', 'fa' => 'تمام خوابگاه‌ها', 'ar' => 'جميع المساكن', 'kk' => 'Барлық жатақханалар' ],
            ],
            'Bu universitetə bağlı bütün yataqxanaları siyahıdan görün.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bu universitetə bağlı bütün yataqxanaları siyahıdan görün.', 'en' => 'See all dormitories associated with this university.', 'ru' => 'Все общежития, связанные с этим университетом.', 'fa' => 'تمام خوابگاه‌های مرتبط با این دانشگاه را ببینید.', 'ar' => 'عرض جميع المساكن المرتبطة بهذه الجامعة.', 'kk' => 'Осы университетке байланысты барлық жатақханаларды көріңіз.' ],
            ],
            'Hələ yataqxana əlavə edilməyib.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Hələ yataqxana əlavə edilməyib.', 'en' => 'No dormitories added yet.', 'ru' => 'Общежития пока не добавлены.', 'fa' => 'هنوز خوابگاهی اضافه نشده.', 'ar' => 'لم تتم إضافة مساكن بعد.', 'kk' => 'Жатақхана әлі қосылмаған.' ],
            ],
            'Yataqxana tipi mövcud deyil.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Yataqxana tipi mövcud deyil.', 'en' => 'Dormitory type not available.', 'ru' => 'Тип общежития недоступен.', 'fa' => 'نوع خوابگاه موجود نیست.', 'ar' => 'نوع المسكن غير متوفر.', 'kk' => 'Жатақхана түрі қол жетімді емес.' ],
            ],
            'Kampusda'         => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Kampusda', 'en' => 'On campus', 'ru' => 'На кампусе', 'fa' => 'در دانشکده', 'ar' => 'في الحرم الجامعي', 'kk' => 'Кампуста' ],
            ],
            'Kampusdan kənar'  => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Kampusdan kənar', 'en' => 'Off campus', 'ru' => 'За пределами кампуса', 'fa' => 'خارج از دانشکده', 'ar' => 'خارج الحرم الجامعي', 'kk' => 'Кампустан тыс' ],
            ],
            'Kişi'             => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Kişi', 'en' => 'Male', 'ru' => 'Мужской', 'fa' => 'مرد', 'ar' => 'ذكر', 'kk' => 'Ер' ],
            ],
            'Qadın'            => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qadın', 'en' => 'Female', 'ru' => 'Женский', 'fa' => 'زن', 'ar' => 'أنثى', 'kk' => 'Әйел' ],
            ],
            'Qarışıq'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qarışıq', 'en' => 'Mixed', 'ru' => 'Смешанный', 'fa' => 'مختلط', 'ar' => 'مختلط', 'kk' => 'Аралас' ],
            ],
            'Otaqlar:'         => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Otaqlar:', 'en' => 'Rooms:', 'ru' => 'Комнаты:', 'fa' => 'اتاق‌ها:', 'ar' => 'الغرف:', 'kk' => 'Бөлмелер:' ],
            ],
            'Tutum:'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Tutum:', 'en' => 'Capacity:', 'ru' => 'Вместимость:', 'fa' => 'ظرفیت:', 'ar' => 'السعة:', 'kk' => 'Сыйымдылық:' ],
            ],
            'Cinsiyyət:'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Cinsiyyət:', 'en' => 'Gender:', 'ru' => 'Пол:', 'fa' => 'جنسیت:', 'ar' => 'الجنس:', 'kk' => 'Жынысы:' ],
            ],
            'Qiymət:'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qiymət:', 'en' => 'Price:', 'ru' => 'Цена:', 'fa' => 'قیمت:', 'ar' => 'السعر:', 'kk' => 'Бағасы:' ],
            ],
            'Mövcuddur'        => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Mövcuddur', 'en' => 'Available', 'ru' => 'Доступно', 'fa' => 'موجود', 'ar' => 'متاح', 'kk' => 'Қол жетімді' ],
            ],
            'Yoxdur'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Yoxdur', 'en' => 'Not available', 'ru' => 'Недоступно', 'fa' => 'موجود نیست', 'ar' => 'غير متاح', 'kk' => 'Қол жетімді емес' ],
            ],
            'Xəritə'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Xəritə', 'en' => 'Map', 'ru' => 'Карта', 'fa' => 'نقشه', 'ar' => 'الخريطة', 'kk' => 'Карта' ],
            ],
            'Böyük xəritədə aç' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Böyük xəritədə aç', 'en' => 'Open in large map', 'ru' => 'Открыть на большой карте', 'fa' => 'باز کردن در نقشه بزرگ', 'ar' => 'فتح في الخريطة الكبيرة', 'kk' => 'Үлкен картада ашу' ],
            ],
            'Yataqxanalar (siyahı)' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Yataqxanalar (siyahı)', 'en' => 'Dormitories (list)', 'ru' => 'Общежития (список)', 'fa' => 'خوابگاه‌ها (فهرست)', 'ar' => 'المساكن (قائمة)', 'kk' => 'Жатақханалар (тізім)' ],
            ],
            'Yataqxanalar səhifəsi' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Yataqxanalar səhifəsi', 'en' => 'Dormitories page', 'ru' => 'Страница общежитий', 'fa' => 'صفحه خوابگاه‌ها', 'ar' => 'صفحة المساكن', 'kk' => 'Жатақханалар беті' ],
            ],
            /* ── Admission ── */
            'Dərəcə növünə görə sənədlər və addımlar. Proqram seçmək və müraciət üçün aşağıdakı keçiddən istifadə edin.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Dərəcə növünə görə sənədlər və addımlar. Proqram seçmək və müraciət üçün aşağıdakı keçiddən istifadə edin.', 'en' => 'Documents and steps by degree type. Use the link below to choose a program and apply.', 'ru' => 'Документы и этапы по типу степени. Используйте ссылку ниже для выбора программы и подачи заявки.', 'fa' => 'مدارک و مراحل بر اساس نوع مدرک. از لینک زیر برای انتخاب برنامه و درخواست استفاده کنید.', 'ar' => 'المستندات والخطوات حسب نوع الدرجة. استخدم الرابط أدناه لاختيار البرنامج والتقديم.', 'kk' => 'Дәреже түрі бойынша құжаттар мен қадамдар. Бағдарлама таңдау және өтініш үшін төмендегі сілтемені пайдаланыңыз.' ],
            ],
            'Dərəcə növünə görə sənədlər və müraciət addımları.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Dərəcə növünə görə sənədlər və müraciət addımları.', 'en' => 'Documents and application steps by degree type.', 'ru' => 'Документы и этапы подачи заявки по типу степени.', 'fa' => 'مدارک و مراحل درخواست بر اساس نوع مدرک.', 'ar' => 'المستندات وخطوات التقديم حسب نوع الدرجة.', 'kk' => 'Дәреже түрі бойынша құжаттар мен өтініш қадамдары.' ],
            ],
            'Aşağıdakı formu doldurun. Dərəcənizə uyğun sənədlər avtomatik tələb olunacaq.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Aşağıdakı formu doldurun. Dərəcənizə uyğun sənədlər avtomatik tələb olunacaq.', 'en' => 'Fill in the form below. Documents matching your degree will be requested automatically.', 'ru' => 'Заполните форму ниже. Документы, соответствующие вашей степени, будут запрошены автоматически.', 'fa' => 'فرم زیر را پر کنید. مدارک متناسب با مدرک شما به‌طور خودکار درخواست خواهد شد.', 'ar' => 'املأ النموذج أدناه. ستُطلب المستندات المناسبة لدرجتك تلقائيًا.', 'kk' => 'Төмендегі форманы толтырыңыз. Дәрежеңізге сәйкес құжаттар автоматты түрде сұралады.' ],
            ],
            'Bu universitet üçün qəbul tələbləri hələ admin panelində doldurulmayıb və ya uyğun proqram dərəcəsi üçün məzmun yoxdur.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bu universitet üçün qəbul tələbləri hələ admin panelində doldurulmayıb və ya uyğun proqram dərəcəsi üçün məzmun yoxdur.', 'en' => 'Admission requirements for this university have not been filled in the admin panel yet or there is no content for the applicable degree.', 'ru' => 'Требования к поступлению для этого университета ещё не заполнены в админ-панели или нет содержания для соответствующей степени.', 'fa' => 'شرایط پذیرش این دانشگاه هنوز در پنل مدیریت تکمیل نشده یا محتوایی برای مدرک مربوطه وجود ندارد.', 'ar' => 'لم يتم ملء متطلبات القبول لهذه الجامعة في لوحة الإدارة بعد أو لا يوجد محتوى للدرجة المناسبة.', 'kk' => 'Бұл университеттің қабылдау талаптары админ панелінде әлі толтырылмаған немесе тиісті дәреже үшін мазмұн жоқ.' ],
            ],
            'Ulduzla işarələnmiş sahələr məcburidir.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Ulduzla işarələnmiş sahələr məcburidir.', 'en' => 'Fields marked with an asterisk are required.', 'ru' => 'Поля, отмеченные звёздочкой, обязательны.', 'fa' => 'فیلدهای با ستاره الزامی هستند.', 'ar' => 'الحقول المميزة بنجمة إلزامية.', 'kk' => 'Жұлдызшамен белгіленген өрістер міндетті.' ],
            ],
            /* ── Programs archive ── */
            'Bütün proqramları kəşf edin, ödəniş və dil üzrə müqayisə edin və uyğun olanı seçin.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bütün proqramları kəşf edin, ödəniş və dil üzrə müqayisə edin və uyğun olanı seçin.', 'en' => 'Explore all programs, compare by tuition and language, and choose the right one.', 'ru' => 'Изучите все программы, сравните по стоимости и языку и выберите подходящую.', 'fa' => 'تمام برنامه‌ها را کاوش کنید، بر اساس شهریه و زبان مقایسه کنید و مناسب‌ترین را انتخاب کنید.', 'ar' => 'استكشف جميع البرامج، قارن حسب الرسوم واللغة، واختر الأنسب.', 'kk' => 'Барлық бағдарламаларды зерттеңіз, төлем мен тіл бойынша салыстырыңыз және сізге сай біреуін таңдаңыз.' ],
            ],
            'Dərəcə, dil və şəhər üzrə filtrləyin.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Dərəcə, dil və şəhər üzrə filtrləyin.', 'en' => 'Filter by degree, language and city.', 'ru' => 'Фильтруйте по степени, языку и городу.', 'fa' => 'بر اساس مدرک، زبان و شهر فیلتر کنید.', 'ar' => 'صفّ حسب الدرجة واللغة والمدينة.', 'kk' => 'Дәреже, тіл және қала бойынша сүзіңіз.' ],
            ],
            'Proqramlar siyahısına qayıt' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Proqramlar siyahısına qayıt', 'en' => 'Back to programs list', 'ru' => 'Назад к списку программ', 'fa' => 'بازگشت به لیست برنامه‌ها', 'ar' => 'العودة إلى قائمة البرامج', 'kk' => 'Бағдарламалар тізіміне оралу' ],
            ],
            '%d proqram tapıldı.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => '%d proqram tapıldı.', 'en' => '%d programs found.', 'ru' => '%d программ найдено.', 'fa' => '%d برنامه یافت شد.', 'ar' => 'تم العثور على %d برنامج.', 'kk' => '%d бағдарлама табылды.' ],
            ],
            'Səhifə %1$s / %2$s' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Səhifə %1$s / %2$s', 'en' => 'Page %1$s / %2$s', 'ru' => 'Страница %1$s / %2$s', 'fa' => 'صفحه %1$s / %2$s', 'ar' => 'الصفحة %1$s / %2$s', 'kk' => 'Бет %1$s / %2$s' ],
            ],
            'Səhifələmə'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Səhifələmə', 'en' => 'Pagination', 'ru' => 'Постраничная навигация', 'fa' => 'صفحه‌بندی', 'ar' => 'التصفح', 'kk' => 'Беттеу' ],
            ],
            'Növ'              => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Növ', 'en' => 'Type', 'ru' => 'Тип', 'fa' => 'نوع', 'ar' => 'النوع', 'kk' => 'Түрі' ],
            ],
            'Növ:'             => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Növ:', 'en' => 'Type:', 'ru' => 'Тип:', 'fa' => 'نوع:', 'ar' => 'النوع:', 'kk' => 'Түрі:' ],
            ],
            'Sahə'             => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Sahə', 'en' => 'Field', 'ru' => 'Область', 'fa' => 'رشته', 'ar' => 'المجال', 'kk' => 'Сала' ],
            ],
            'Müddət'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Müddət', 'en' => 'Duration', 'ru' => 'Длительность', 'fa' => 'مدت', 'ar' => 'المدة', 'kk' => 'Мерзімі' ],
            ],
            'Universitet adı…' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Universitet adı…', 'en' => 'University name…', 'ru' => 'Название университета…', 'fa' => 'نام دانشگاه…', 'ar' => 'اسم الجامعة…', 'kk' => 'Университет атауы…' ],
            ],
            'Ən azı'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Ən azı', 'en' => 'At least', 'ru' => 'Минимум', 'fa' => 'حداقل', 'ar' => 'على الأقل', 'kk' => 'Кем дегенде' ],
            ],
            'Məlumat yüklənərkən xəta baş verdi. Səhifəni yeniləyin.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Məlumat yüklənərkən xəta baş verdi. Səhifəni yeniləyin.', 'en' => 'An error occurred while loading data. Please refresh the page.', 'ru' => 'Произошла ошибка при загрузке данных. Обновите страницу.', 'fa' => 'هنگام بارگذاری داده خطایی رخ داد. صفحه را بازخوانی کنید.', 'ar' => 'حدث خطأ أثناء تحميل البيانات. يرجى تحديث الصفحة.', 'kk' => 'Деректерді жүктеу кезінде қате орын алды. Бетті жаңартыңыз.' ],
            ],
            'Göndərmə alınmadı. Məlumatları yoxlayın və yenidən cəhd edin.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Göndərmə alınmadı. Məlumatları yoxlayın və yenidən cəhd edin.', 'en' => 'Submission failed. Check the data and try again.', 'ru' => 'Отправка не удалась. Проверьте данные и попробуйте снова.', 'fa' => 'ارسال انجام نشد. اطلاعات را بررسی و دوباره تلاش کنید.', 'ar' => 'فشل الإرسال. تحقق من البيانات وحاول مرة أخرى.', 'kk' => 'Жіберу сәтсіз аяқталды. Деректерді тексеріп, қайталап көріңіз.' ],
            ],
            /* ── Universities archive ── */
            'Universitetlər siyahısı' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Universitetlər siyahısı', 'en' => 'Universities list', 'ru' => 'Список университетов', 'fa' => 'فهرست دانشگاه‌ها', 'ar' => 'قائمة الجامعات', 'kk' => 'Университеттер тізімі' ],
            ],
            'Əməkdaşlıq etdiyimiz təhsil müəssisələri ilə tanış olun.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Əməkdaşlıq etdiyimiz təhsil müəssisələri ilə tanış olun.', 'en' => 'Get to know the educational institutions we cooperate with.', 'ru' => 'Познакомьтесь с учебными заведениями, с которыми мы сотрудничаем.', 'fa' => 'با موسسات آموزشی که با آن‌ها همکاری می‌کنیم آشنا شوید.', 'ar' => 'تعرّف على المؤسسات التعليمية التي نتعاون معها.', 'kk' => 'Біз ынтымақтасатын білім мекемелерімен танысыңыз.' ],
            ],
            'Özəl və dövlət universitetlərini şəhər və növə görə müqayisə edin.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Özəl və dövlət universitetlərini şəhər və növə görə müqayisə edin.', 'en' => 'Compare private and public universities by city and type.', 'ru' => 'Сравните частные и государственные университеты по городу и типу.', 'fa' => 'دانشگاه‌های خصوصی و دولتی را بر اساس شهر و نوع مقایسه کنید.', 'ar' => 'قارن الجامعات الخاصة والحكومية حسب المدينة والنوع.', 'kk' => 'Жеке және мемлекеттік университеттерді қала мен түрі бойынша салыстырыңыз.' ],
            ],
            'Seçilmiş filtrlərə uyğun universitet tapılmadı.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Seçilmiş filtrlərə uyğun universitet tapılmadı.', 'en' => 'No universities found matching the selected filters.', 'ru' => 'Университеты по выбранным фильтрам не найдены.', 'fa' => 'دانشگاهی مطابق فیلترهای انتخاب‌شده یافت نشد.', 'ar' => 'لم يتم العثور على جامعات تطابق المرشحات المختارة.', 'kk' => 'Таңдалған сүзгілерге сәйкес университет табылмады.' ],
            ],
            'Hamısına bax'     => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Hamısına bax', 'en' => 'View all', 'ru' => 'Смотреть все', 'fa' => 'مشاهده همه', 'ar' => 'عرض الكل', 'kk' => 'Барлығын көру' ],
            ],
            'Bax'              => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bax', 'en' => 'View', 'ru' => 'Смотреть', 'fa' => 'مشاهده', 'ar' => 'عرض', 'kk' => 'Көру' ],
            ],
            'Oxu'              => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Oxu', 'en' => 'Read', 'ru' => 'Читать', 'fa' => 'بخوانید', 'ar' => 'اقرأ', 'kk' => 'Оқу' ],
            ],
            /* ── Blog / Archive ── */
            'Bloq'             => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bloq', 'en' => 'Blog', 'ru' => 'Блог', 'fa' => 'وبلاگ', 'ar' => 'المدونة', 'kk' => 'Блог' ],
            ],
            'Təhsil, qəbul və Türkiyə haqqında yazılar.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Təhsil, qəbul və Türkiyə haqqında yazılar.', 'en' => 'Articles about education, admissions and Turkey.', 'ru' => 'Статьи об образовании, поступлении и Турции.', 'fa' => 'مقالات درباره تحصیلات، پذیرش و ترکیه.', 'ar' => 'مقالات عن التعليم والقبول وتركيا.', 'kk' => 'Білім, қабылдау және Түркия туралы мақалалар.' ],
            ],
            'Xəbərlər, məsləhətlər və qəbul təqvimi.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Xəbərlər, məsləhətlər və qəbul təqvimi.', 'en' => 'News, tips and admission calendar.', 'ru' => 'Новости, советы и календарь приёма.', 'fa' => 'اخبار، نکات و تقویم پذیرش.', 'ar' => 'أخبار ونصائح وتقويم القبول.', 'kk' => 'Жаңалықтар, кеңестер және қабылдау күнтізбесі.' ],
            ],
            'Bütün yazılar'    => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bütün yazılar', 'en' => 'All posts', 'ru' => 'Все записи', 'fa' => 'تمام نوشته‌ها', 'ar' => 'جميع المقالات', 'kk' => 'Барлық жазбалар' ],
            ],
            'Bu arxivdə yazı yoxdur.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bu arxivdə yazı yoxdur.', 'en' => 'No posts in this archive.', 'ru' => 'В этом архиве нет записей.', 'fa' => 'هیچ نوشته‌ای در این آرشیو نیست.', 'ar' => 'لا توجد مقالات في هذا الأرشيف.', 'kk' => 'Бұл мұрағатта жазба жоқ.' ],
            ],
            'Hələ yazı yoxdur.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Hələ yazı yoxdur.', 'en' => 'No posts yet.', 'ru' => 'Записей пока нет.', 'fa' => 'هنوز نوشته‌ای نیست.', 'ar' => 'لا توجد مقالات بعد.', 'kk' => 'Жазба әлі жоқ.' ],
            ],
            'Təhsil yolunda'   => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Təhsil yolunda', 'en' => 'On the path to education', 'ru' => 'На пути к образованию', 'fa' => 'در مسیر تحصیل', 'ar' => 'على طريق التعليم', 'kk' => 'Білім жолында' ],
            ],
            'Axtarış'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Axtarış', 'en' => 'Search', 'ru' => 'Поиск', 'fa' => 'جستجو', 'ar' => 'بحث', 'kk' => 'Іздеу' ],
            ],
            'Axtarış: %s'      => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Axtarış: %s', 'en' => 'Search: %s', 'ru' => 'Поиск: %s', 'fa' => 'جستجو: %s', 'ar' => 'بحث: %s', 'kk' => 'Іздеу: %s' ],
            ],
            'Axtar…'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Axtar…', 'en' => 'Search…', 'ru' => 'Поиск…', 'fa' => 'جستجو…', 'ar' => 'بحث…', 'kk' => 'Іздеу…' ],
            ],
            'Heç bir nəticə tapılmadı.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Heç bir nəticə tapılmadı.', 'en' => 'No results found.', 'ru' => 'Ничего не найдено.', 'fa' => 'نتیجه‌ای یافت نشد.', 'ar' => 'لم يتم العثور على نتائج.', 'kk' => 'Ешқандай нәтиже табылмады.' ],
            ],
            'Heç bir məzmun tapılmadı.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Heç bir məzmun tapılmadı.', 'en' => 'No content found.', 'ru' => 'Содержимое не найдено.', 'fa' => 'محتوایی یافت نشد.', 'ar' => 'لم يتم العثور على محتوى.', 'kk' => 'Мазмұн табылмады.' ],
            ],
            'Səhifə tapılmadı' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Səhifə tapılmadı', 'en' => 'Page not found', 'ru' => 'Страница не найдена', 'fa' => 'صفحه یافت نشد', 'ar' => 'الصفحة غير موجودة', 'kk' => 'Бет табылмады' ],
            ],
            'Ünvanı yoxlayın və ya axtarışdan istifadə edin.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Ünvanı yoxlayın və ya axtarışdan istifadə edin.', 'en' => 'Check the address or use search.', 'ru' => 'Проверьте адрес или воспользуйтесь поиском.', 'fa' => 'آدرس را بررسی کنید یا از جستجو استفاده کنید.', 'ar' => 'تحقق من العنوان أو استخدم البحث.', 'kk' => 'Мекенжайды тексеріңіз немесе іздеуді пайдаланыңыз.' ],
            ],
            /* ── Contact ── */
            'Əlaqə'           => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Əlaqə', 'en' => 'Contact', 'ru' => 'Контакты', 'fa' => 'تماس', 'ar' => 'اتصل بنا', 'kk' => 'Байланыс' ],
            ],
            'Əlaqə səhifəsi'  => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Əlaqə səhifəsi', 'en' => 'Contact page', 'ru' => 'Страница контактов', 'fa' => 'صفحه تماس', 'ar' => 'صفحة الاتصال', 'kk' => 'Байланыс беті' ],
            ],
            'Əlaqə məlumatları' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Əlaqə məlumatları', 'en' => 'Contact information', 'ru' => 'Контактная информация', 'fa' => 'اطلاعات تماس', 'ar' => 'معلومات الاتصال', 'kk' => 'Байланыс ақпараты' ],
            ],
            'Bizə yazın'      => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bizə yazın', 'en' => 'Write to us', 'ru' => 'Напишите нам', 'fa' => 'به ما بنویسید', 'ar' => 'راسلنا', 'kk' => 'Бізге жазыңыз' ],
            ],
            'Ad, soyad'        => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Ad, soyad', 'en' => 'Full name', 'ru' => 'Имя, фамилия', 'fa' => 'نام و نام خانوادگی', 'ar' => 'الاسم الكامل', 'kk' => 'Аты-жөні' ],
            ],
            'Telefon'          => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Telefon', 'en' => 'Phone', 'ru' => 'Телефон', 'fa' => 'تلفن', 'ar' => 'الهاتف', 'kk' => 'Телефон' ],
            ],
            'Mövzu'            => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Mövzu', 'en' => 'Subject', 'ru' => 'Тема', 'fa' => 'موضوع', 'ar' => 'الموضوع', 'kk' => 'Тақырып' ],
            ],
            'Mesaj'            => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Mesaj', 'en' => 'Message', 'ru' => 'Сообщение', 'fa' => 'پیام', 'ar' => 'الرسالة', 'kk' => 'Хабарлама' ],
            ],
            'Mesajınız göndərildi. Tezliklə sizinlə əlaqə saxlayacağıq.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Mesajınız göndərildi. Tezliklə sizinlə əlaqə saxlayacağıq.', 'en' => 'Your message has been sent. We will contact you soon.', 'ru' => 'Ваше сообщение отправлено. Мы свяжемся с вами в ближайшее время.', 'fa' => 'پیام شما ارسال شد. به زودی با شما تماس خواهیم گرفت.', 'ar' => 'تم إرسال رسالتك. سنتواصل معك قريبًا.', 'kk' => 'Хабарламаңыз жіберілді. Жақында сізбен хабарласамыз.' ],
            ],
            /* ── Visa ── */
            'Türkiyə tələbə vizası və sənədlər haqqında ümumi məlumat bu səhifədə toplanır.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Türkiyə tələbə vizası və sənədlər haqqında ümumi məlumat bu səhifədə toplanır.', 'en' => 'General information about Turkish student visa and documents is collected on this page.', 'ru' => 'На этой странице собрана общая информация о студенческой визе в Турцию и необходимых документах.', 'fa' => 'اطلاعات کلی درباره ویزای دانشجویی ترکیه و مدارک در این صفحه جمع‌آوری شده است.', 'ar' => 'يتم جمع المعلومات العامة حول تأشيرة الطالب التركية والمستندات في هذه الصفحة.', 'kk' => 'Түркияның студенттік визасы мен құжаттар туралы жалпы ақпарат осы бетте жинақталған.' ],
            ],
            'Viza qaydaları dəyişə bilər. Rəsmi konsulluq və universitet təlimatlarını yoxlayın; dəqiq məsləhət üçün bizimlə əlaqə saxlayın.' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Viza qaydaları dəyişə bilər. Rəsmi konsulluq və universitet təlimatlarını yoxlayın; dəqiq məsləhət üçün bizimlə əlaqə saxlayın.', 'en' => 'Visa rules may change. Check official consulate and university guidelines; contact us for precise advice.', 'ru' => 'Визовые правила могут меняться. Проверяйте официальные указания консульства и университета; свяжитесь с нами за точной консультацией.', 'fa' => 'قوانین ویزا ممکن است تغییر کند. دستورالعمل‌های رسمی کنسولگری و دانشگاه را بررسی کنید؛ برای مشاوره دقیق با ما تماس بگیرید.', 'ar' => 'قد تتغير قواعد التأشيرة. تحقق من إرشادات القنصلية والجامعة الرسمية؛ اتصل بنا للحصول على نصيحة دقيقة.', 'kk' => 'Виза ережелері өзгеруі мүмкін. Ресми консулдық пен университет нұсқауларын тексеріңіз; нақты кеңес үшін бізбен хабарласыңыз.' ],
            ],
            /* ── Misc ── */
            'Keçidlər'         => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Keçidlər', 'en' => 'Links', 'ru' => 'Ссылки', 'fa' => 'لینک‌ها', 'ar' => 'الروابط', 'kk' => 'Сілтемелер' ],
            ],
            'Bu səhifədə'      => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bu səhifədə', 'en' => 'On this page', 'ru' => 'На этой странице', 'fa' => 'در این صفحه', 'ar' => 'في هذه الصفحة', 'kk' => 'Осы бетте' ],
            ],
            'Tam səhifə'       => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Tam səhifə', 'en' => 'Full page', 'ru' => 'Полная страница', 'fa' => 'صفحه کامل', 'ar' => 'الصفحة الكاملة', 'kk' => 'Толық бет' ],
            ],
            'Qeyd'             => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Qeyd', 'en' => 'Note', 'ru' => 'Примечание', 'fa' => 'یادداشت', 'ar' => 'ملاحظة', 'kk' => 'Ескерту' ],
            ],
            'Bəli'             => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Bəli', 'en' => 'Yes', 'ru' => 'Да', 'fa' => 'بله', 'ar' => 'نعم', 'kk' => 'Иә' ],
            ],
            'Universitet və proqram seçiminizi bir yerdə edin' => [
                'context' => 'gettext',
                'langs'   => [ 'az' => 'Universitet və proqram seçiminizi bir yerdə edin', 'en' => 'Choose your university and program in one place', 'ru' => 'Выберите университет и программу в одном месте', 'fa' => 'دانشگاه و برنامه خود را در یک مکان انتخاب کنید', 'ar' => 'اختر جامعتك وبرنامجك في مكان واحد', 'kk' => 'Университет пен бағдарламаңызды бір жерде таңдаңыз' ],
            ],
        ];
    }
}
