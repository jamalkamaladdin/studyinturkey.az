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
        ];
    }
}
