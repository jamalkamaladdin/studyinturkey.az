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
        ];
    }
}
