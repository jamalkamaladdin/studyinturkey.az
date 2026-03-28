<?php
/**
 * Shortcode [sit_language_switcher] və widget.
 */

defined( 'ABSPATH' ) || exit;

class SIT_Language_Switcher {

    public static function init(): void {
        add_shortcode( 'sit_language_switcher', [ __CLASS__, 'shortcode' ] );
        add_action( 'widgets_init', [ __CLASS__, 'register_widget' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'register_assets' ] );
    }

    public static function register_widget(): void {
        register_widget( SIT_Widget_Language_Switcher::class );
    }

    public static function register_assets(): void {
        wp_register_style(
            'sit-lang-switcher',
            SIT_MULTILANG_URL . 'assets/css/sit-switcher.css',
            [],
            SIT_MULTILANG_VERSION
        );
    }

    /**
     * @param array<string, string> $atts
     */
    public static function shortcode( $atts ): string {
        $atts = shortcode_atts(
            [
                'type'       => 'list',
                'show_flags' => '1',
                'show_names' => '1',
                'class'      => '',
            ],
            $atts,
            'sit_language_switcher'
        );

        return self::render(
            [
                'type'       => 'dropdown' === $atts['type'] ? 'dropdown' : 'list',
                'show_flags' => '1' === $atts['show_flags'] || 'true' === $atts['show_flags'],
                'show_names' => '1' === $atts['show_names'] || 'true' === $atts['show_names'],
                'class'      => sanitize_html_class( $atts['class'] ),
            ]
        );
    }

    /**
     * @param array{type?:string, show_flags?:bool, show_names?:bool, class?:string} $args
     */
    public static function render( array $args = [] ): string {
        if ( SIT_Rewrite::should_bypass_routing() ) {
            return '';
        }

        wp_enqueue_style( 'sit-lang-switcher' );

        $type       = isset( $args['type'] ) && 'dropdown' === $args['type'] ? 'dropdown' : 'list';
        $show_flags = ! isset( $args['show_flags'] ) || $args['show_flags'];
        $show_names = ! isset( $args['show_names'] ) || $args['show_names'];
        $extra      = isset( $args['class'] ) ? sanitize_html_class( $args['class'] ) : '';

        $languages = SIT_Languages::get_active_languages();
        if ( empty( $languages ) ) {
            return '';
        }

        $current = sit_get_current_lang();

        ob_start();

        if ( 'dropdown' === $type ) {
            $base_class = 'sit-lang-switcher sit-lang-switcher--dropdown';
            if ( $extra ) {
                $base_class .= ' ' . $extra;
            }
            echo '<div class="' . esc_attr( $base_class ) . '">';
            echo '<label class="screen-reader-text" for="sit-lang-select">' . esc_html__( 'Dil seçin', 'studyinturkey' ) . '</label>';
            echo '<select id="sit-lang-select" class="sit-lang-switcher__select" data-sit-lang-switcher>';
            foreach ( $languages as $lang ) {
                $url   = esc_url( sit_get_page_url_in_language( $lang->code ) );
                $label = $show_names ? $lang->native_name : strtoupper( $lang->code );
                if ( $show_flags && ! empty( $lang->flag ) ) {
                    $label = trim( (string) $lang->flag . ' ' . $label );
                }
                printf(
                    '<option value="%1$s" data-url="%2$s"%3$s>%4$s</option>',
                    esc_attr( $lang->code ),
                    esc_url( $url ),
                    selected( $current, $lang->code, false ),
                    esc_html( $label )
                );
            }
            echo '</select></div>';
            self::inline_dropdown_script();
        } else {
            $base_class = 'sit-lang-switcher';
            if ( $extra ) {
                $base_class .= ' ' . $extra;
            }
            echo '<ul class="' . esc_attr( $base_class ) . '">';
            foreach ( $languages as $lang ) {
                $url      = sit_get_page_url_in_language( $lang->code );
                $is_here  = ( $lang->code === $current );
                $li_class = 'sit-lang-switcher__item';
                $a_class  = 'sit-lang-switcher__link' . ( $is_here ? ' is-current' : '' );
                echo '<li class="' . esc_attr( $li_class ) . '">';
                echo '<a class="' . esc_attr( $a_class ) . '" href="' . esc_url( $url ) . '"' . ( $is_here ? ' aria-current="true"' : '' ) . '>';
                if ( $show_flags && ! empty( $lang->flag ) ) {
                    echo '<span class="sit-lang-switcher__flag" aria-hidden="true">' . esc_html( $lang->flag ) . '</span> ';
                }
                if ( $show_names ) {
                    echo '<span class="sit-lang-switcher__name">' . esc_html( $lang->native_name ) . '</span>';
                } else {
                    echo '<span class="sit-lang-switcher__code">' . esc_html( strtoupper( $lang->code ) ) . '</span>';
                }
                echo '</a></li>';
            }
            echo '</ul>';
        }

        return (string) ob_get_clean();
    }

    private static function inline_dropdown_script(): void {
        static $done = false;
        if ( $done ) {
            return;
        }
        $done = true;
        wp_enqueue_script( 'jquery' );
        wp_add_inline_script(
            'jquery',
            'jQuery(function($){$(document).on("change","select[data-sit-lang-switcher]",function(){var u=$(this).find("option:selected").data("url");if(u){window.location.href=u;}});});'
        );
    }
}

/**
 * Widget: Dil keçidi.
 */
class SIT_Widget_Language_Switcher extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'sit_language_switcher',
            esc_html__( 'SIT: Dil keçidi', 'studyinturkey' ),
            [
                'description' => esc_html__( 'StudyInTurkey çoxdilli keçid siyahısı və ya siyahı.', 'studyinturkey' ),
            ]
        );
    }

    /**
     * @param array<string, string> $args
     * @param array<string, string> $instance
     */
    public function widget( $args, $instance ): void {
        $type = isset( $instance['display_type'] ) && 'dropdown' === $instance['display_type'] ? 'dropdown' : 'list';
        $html = SIT_Language_Switcher::render(
            [
                'type'       => $type,
                'show_flags' => ! empty( $instance['show_flags'] ),
                'show_names' => ! isset( $instance['show_names'] ) || $instance['show_names'],
            ]
        );
        if ( '' === $html ) {
            return;
        }
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        echo $html;
        echo $args['after_widget'];
    }

    /**
     * @param array<string, string> $instance
     */
    public function form( $instance ): void {
        $title       = isset( $instance['title'] ) ? $instance['title'] : '';
        $display     = isset( $instance['display_type'] ) ? $instance['display_type'] : 'list';
        $show_flags  = ! empty( $instance['show_flags'] );
        $show_names  = ! isset( $instance['show_names'] ) || $instance['show_names'];
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Başlıq:', 'studyinturkey' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>"><?php esc_html_e( 'Görünüş:', 'studyinturkey' ); ?></label>
            <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_type' ) ); ?>">
                <option value="list" <?php selected( $display, 'list' ); ?>><?php esc_html_e( 'Siyahı', 'studyinturkey' ); ?></option>
                <option value="dropdown" <?php selected( $display, 'dropdown' ); ?>><?php esc_html_e( 'Açılan siyahı', 'studyinturkey' ); ?></option>
            </select>
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_flags' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_flags' ) ); ?>" value="1" <?php checked( $show_flags ); ?> />
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_flags' ) ); ?>"><?php esc_html_e( 'Bayraq göstər', 'studyinturkey' ); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'show_names' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_names' ) ); ?>" value="1" <?php checked( $show_names ); ?> />
            <label for="<?php echo esc_attr( $this->get_field_id( 'show_names' ) ); ?>"><?php esc_html_e( 'Yerli ad göstər', 'studyinturkey' ); ?></label>
        </p>
        <?php
    }

    /**
     * @param array<string, string> $new_instance
     * @param array<string, string> $old_instance
     * @return array<string, string|int>
     */
    public function update( $new_instance, $old_instance ): array {
        return [
            'title'        => sanitize_text_field( $new_instance['title'] ?? '' ),
            'display_type' => 'dropdown' === ( $new_instance['display_type'] ?? '' ) ? 'dropdown' : 'list',
            'show_flags'   => ! empty( $new_instance['show_flags'] ) ? 1 : 0,
            'show_names'   => ! empty( $new_instance['show_names'] ) ? 1 : 0,
        ];
    }
}
