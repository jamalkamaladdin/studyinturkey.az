<?php
/**
 * Shortcode və forma çıxışı.
 */

defined( 'ABSPATH' ) || exit;

final class SIT_Application_Form {

    public static function register(): void {
        add_shortcode( 'sit_application_form', [ __CLASS__, 'render_shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    public static function enqueue_assets(): void {
        global $post;
        $need = is_singular( 'program' );
        if ( ! $need && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'sit_application_form' ) ) {
            $need = true;
        }
        if ( ! $need ) {
            return;
        }

        wp_enqueue_style(
            'sit-application-form',
            SIT_APPLICATION_URL . 'assets/css/sit-application-form.css',
            [],
            SIT_APPLICATION_VERSION
        );
    }

    /**
     * Shortcode: [sit_application_form program_id="123"]
     *
     * @param array<string, string> $atts Shortcode attributes.
     */
    public static function render_shortcode( $atts ): string {
        $atts = shortcode_atts(
            [
                'program_id' => '',
            ],
            $atts,
            'sit_application_form'
        );

        $preselect = absint( $atts['program_id'] );

        wp_enqueue_style(
            'sit-application-form',
            SIT_APPLICATION_URL . 'assets/css/sit-application-form.css',
            [],
            SIT_APPLICATION_VERSION
        );

        ob_start();

        $flash   = SIT_Application_Handler::consume_form_flash();
        $errors  = $flash['errors'];
        $old     = $flash['old'];
        $success = SIT_Application_Handler::has_success_flag();

        if ( is_user_logged_in() ) {
            $u = wp_get_current_user();
            if ( empty( $old['sit_app_name'] ) ) {
                $old['sit_app_name'] = $u->display_name;
            }
            if ( empty( $old['sit_app_email'] ) ) {
                $old['sit_app_email'] = $u->user_email;
            }
            if ( empty( $old['sit_app_phone'] ) ) {
                $phone_meta = get_user_meta( $u->ID, SIT_Application_Account::META_PHONE, true );
                if ( is_string( $phone_meta ) && '' !== $phone_meta ) {
                    $old['sit_app_phone'] = $phone_meta;
                }
            }
        }

        $programs = get_posts(
            [
                'post_type'      => 'program',
                'post_status'    => 'publish',
                'posts_per_page' => 500,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'no_found_rows'  => true,
            ]
        );

        $action_url = esc_url( admin_url( 'admin-post.php' ) );
        ?>
        <div class="sit-app-form-wrapper">
            <?php if ( $success ) : ?>
                <div class="sit-app-form-notice sit-app-form-notice--success" role="status">
                    <?php esc_html_e( 'Müraciətiniz qəbul edildi. Tezliklə sizinlə əlaqə saxlanılacaq.', 'studyinturkey' ); ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $errors ) ) : ?>
                <div class="sit-app-form-notice sit-app-form-notice--error" role="alert">
                    <ul class="sit-app-form-error-list">
                        <?php foreach ( $errors as $err ) : ?>
                            <li><?php echo esc_html( $err ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ( empty( $programs ) ) : ?>
                <p class="sit-app-form-empty"><?php esc_html_e( 'Hazırda açıq proqram yoxdur.', 'studyinturkey' ); ?></p>
            <?php else : ?>
                <?php
                $ov = static function ( string $key ) use ( $old ): string {
                    return isset( $old[ $key ] ) ? (string) $old[ $key ] : '';
                };
                $current_program = 0;
                if ( ! empty( $old['sit_app_program_id'] ) ) {
                    $current_program = absint( $old['sit_app_program_id'] );
                } elseif ( $preselect > 0 ) {
                    $current_program = $preselect;
                }
                $init_level = $current_program > 0 ? SIT_Application_Degree::level_for_program( $current_program ) : 'undergraduate';
                ?>
                <form class="sit-app-form" method="post" action="<?php echo esc_url( $action_url ); ?>" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="action" value="<?php echo esc_attr( SIT_Application_Handler::NONCE_ACTION ); ?>" />

                    <?php wp_nonce_field( SIT_Application_Handler::NONCE_ACTION, SIT_Application_Handler::NONCE_NAME, true, false ); ?>

                    <fieldset class="sit-app-form-section">
                        <legend><?php esc_html_e( 'Əlaqə və şəxsiyyət', 'studyinturkey' ); ?></legend>
                        <div class="sit-app-form-grid">
                            <p class="sit-app-form-field sit-app-form-field--full">
                                <label for="sit_app_name"><?php esc_html_e( 'Tam ad (pasportdakı kimi)', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <input type="text" id="sit_app_name" name="sit_app_name" required maxlength="191" autocomplete="name"
                                    value="<?php echo esc_attr( $ov( 'sit_app_name' ) ); ?>" />
                            </p>
                            <p class="sit-app-form-field sit-app-form-field--full">
                                <label for="sit_app_email"><?php esc_html_e( 'E-poçt', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <input type="email" id="sit_app_email" name="sit_app_email" required maxlength="191" autocomplete="email"
                                    value="<?php echo esc_attr( sanitize_email( $ov( 'sit_app_email' ) ) ); ?>" />
                            </p>
                            <p class="sit-app-form-field sit-app-form-field--full">
                                <label for="sit_app_phone"><?php esc_html_e( 'Telefon', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <span class="sit-app-phone-wrap">
                                    <select name="sit_app_phone_code" class="sit-app-phone-code">
                                        <?php if ( function_exists( 'sit_theme_phone_code_options' ) ) { sit_theme_phone_code_options(); } else { echo '<option value="+994">🇦🇿 +994</option>'; } ?>
                                    </select>
                                    <input type="tel" id="sit_app_phone" name="sit_app_phone" required maxlength="20" autocomplete="tel" placeholder="50 123 45 67"
                                        value="<?php echo esc_attr( $ov( 'sit_app_phone' ) ); ?>" />
                                </span>
                            </p>
                            <p class="sit-app-form-field sit-app-form-field--full">
                                <label><?php esc_html_e( 'Doğum tarixi', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <span style="display:flex;gap:0.5rem;">
                                    <select name="sit_app_dob_day" required style="flex:1;min-width:0;">
                                        <option value=""><?php esc_html_e( 'Gün', 'studyinturkey' ); ?></option>
                                        <?php for ( $d = 1; $d <= 31; $d++ ) : ?><option value="<?php echo esc_attr( $d ); ?>"><?php echo esc_html( str_pad( $d, 2, '0', STR_PAD_LEFT ) ); ?></option><?php endfor; ?>
                                    </select>
                                    <select name="sit_app_dob_month" required style="flex:1.5;min-width:0;">
                                        <option value=""><?php esc_html_e( 'Ay', 'studyinturkey' ); ?></option>
                                        <?php foreach ([1=>'Yanvar',2=>'Fevral',3=>'Mart',4=>'Aprel',5=>'May',6=>'İyun',7=>'İyul',8=>'Avqust',9=>'Sentyabr',10=>'Oktyabr',11=>'Noyabr',12=>'Dekabr'] as $n=>$nm) : ?><option value="<?php echo esc_attr($n); ?>"><?php echo esc_html($nm); ?></option><?php endforeach; ?>
                                    </select>
                                    <select name="sit_app_dob_year" required style="flex:1;min-width:0;">
                                        <option value=""><?php esc_html_e( 'İl', 'studyinturkey' ); ?></option>
                                        <?php $cy=(int)gmdate('Y'); for ($y=$cy-15;$y>=$cy-60;$y--) : ?><option value="<?php echo esc_attr($y); ?>"><?php echo esc_html($y); ?></option><?php endfor; ?>
                                    </select>
                                </span>
                                <input type="hidden" id="sit_app_dob" name="sit_app_dob" value="<?php echo esc_attr( $ov( 'sit_app_dob' ) ); ?>" />
                            </p>
                            <p class="sit-app-form-field">
                                <label for="sit_app_nationality"><?php esc_html_e( 'Vətəndaşlıq', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <select id="sit_app_nationality" name="sit_app_nationality" required>
                                    <?php if ( function_exists( 'sit_theme_country_options' ) ) { sit_theme_country_options(); } else { echo '<option value="">Seçin...</option>'; } ?>
                                </select>
                            </p>
                            <p class="sit-app-form-field">
                                <label for="sit_app_passport_no"><?php esc_html_e( 'Pasport / şəxsiyyət nömrəsi', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <input type="text" id="sit_app_passport_no" name="sit_app_passport_no" required maxlength="64"
                                    value="<?php echo esc_attr( $ov( 'sit_app_passport_no' ) ); ?>" />
                            </p>
                            <p class="sit-app-form-field sit-app-form-field--full">
                                <label for="sit_app_address"><?php esc_html_e( 'Yaşayış ünvanı (ölkə, şəhər, küçə)', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <textarea id="sit_app_address" name="sit_app_address" rows="2" maxlength="500" required><?php echo esc_textarea( $ov( 'sit_app_address' ) ); ?></textarea>
                            </p>
                        </div>
                    </fieldset>

                    <fieldset class="sit-app-form-section">
                        <legend><?php esc_html_e( 'Təhsil və proqram', 'studyinturkey' ); ?></legend>
                        <div class="sit-app-form-grid">
                            <p class="sit-app-form-field sit-app-form-field--full">
                                <label for="sit_app_program_id"><?php esc_html_e( 'Proqram', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <select id="sit_app_program_id" name="sit_app_program_id" required>
                                    <option value=""><?php esc_html_e( 'Seçin…', 'studyinturkey' ); ?></option>
                                    <?php foreach ( $programs as $prog ) : ?>
                                        <?php
                                        $pid   = (int) $prog->ID;
                                        $level = SIT_Application_Degree::level_for_program( $pid );
                                        ?>
                                        <option value="<?php echo esc_attr( (string) $pid ); ?>" data-sit-degree-level="<?php echo esc_attr( $level ); ?>" <?php selected( $current_program, $pid ); ?>>
                                            <?php echo esc_html( get_the_title( $prog ) ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="sit-app-form-hint"><?php esc_html_e( 'Dərəcəyə görə əlavə sənədlər aşağıda göstərilir.', 'studyinturkey' ); ?></span>
                            </p>
                            <p class="sit-app-form-field">
                                <label for="sit_app_edu_institution"><?php esc_html_e( 'Son təhsil müəssisəsi', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <input type="text" id="sit_app_edu_institution" name="sit_app_edu_institution" required maxlength="191"
                                    value="<?php echo esc_attr( $ov( 'sit_app_edu_institution' ) ); ?>" />
                            </p>
                            <p class="sit-app-form-field">
                                <label for="sit_app_edu_country"><?php esc_html_e( 'Təhsil ölkəsi', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <select id="sit_app_edu_country" name="sit_app_edu_country" required>
                                    <?php if ( function_exists( 'sit_theme_country_options' ) ) { sit_theme_country_options(); } else { echo '<option value="">Seçin...</option>'; } ?>
                                </select>
                            </p>
                            <p class="sit-app-form-field">
                                <label for="sit_app_grad_year"><?php esc_html_e( 'Bitirmə ili', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <input type="text" id="sit_app_grad_year" name="sit_app_grad_year" required maxlength="4" inputmode="numeric" pattern="[0-9]{4}"
                                    placeholder="2024" value="<?php echo esc_attr( $ov( 'sit_app_grad_year' ) ); ?>" />
                            </p>
                            <p class="sit-app-form-field">
                                <label for="sit_app_intake"><?php esc_html_e( 'Planlaşdırılan qəbul', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <input type="text" id="sit_app_intake" name="sit_app_intake" required maxlength="80" placeholder="<?php esc_attr_e( 'məs. Payız 2026', 'studyinturkey' ); ?>"
                                    value="<?php echo esc_attr( $ov( 'sit_app_intake' ) ); ?>" />
                            </p>
                        </div>
                    </fieldset>

                    <div class="sit-app-form-section sit-app-form-section--levels" data-sit-app-levels="graduate doctoral" style="<?php echo in_array( $init_level, [ 'graduate', 'doctoral' ], true ) ? '' : 'display:none;'; ?>">
                        <fieldset>
                            <legend><?php esc_html_e( 'Magistr / doktorantura üçün', 'studyinturkey' ); ?></legend>
                            <p class="sit-app-form-field sit-app-form-field--full">
                                <label for="sit_app_work_exp"><?php esc_html_e( 'İş / təcrübə (qısa)', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <textarea id="sit_app_work_exp" name="sit_app_work_exp" rows="3" maxlength="2000"<?php echo in_array( $init_level, [ 'graduate', 'doctoral' ], true ) ? '' : ' disabled'; ?>><?php echo esc_textarea( $ov( 'sit_app_work_exp' ) ); ?></textarea>
                            </p>
                        </fieldset>
                    </div>

                    <div class="sit-app-form-section sit-app-form-section--levels" data-sit-app-levels="doctoral" style="<?php echo ( 'doctoral' === $init_level ) ? '' : 'display:none;'; ?>">
                        <fieldset>
                            <legend><?php esc_html_e( 'Doktorantura üçün', 'studyinturkey' ); ?></legend>
                            <p class="sit-app-form-field sit-app-form-field--full">
                                <label for="sit_app_research"><?php esc_html_e( 'Tədqiqat mövzusu / planı', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <textarea id="sit_app_research" name="sit_app_research" rows="4" maxlength="4000"<?php echo ( 'doctoral' === $init_level ) ? '' : ' disabled'; ?>><?php echo esc_textarea( $ov( 'sit_app_research' ) ); ?></textarea>
                            </p>
                        </fieldset>
                    </div>

                    <fieldset class="sit-app-form-section">
                        <legend><?php esc_html_e( 'Əlavə qeyd', 'studyinturkey' ); ?></legend>
                        <p class="sit-app-form-field sit-app-form-field--full">
                            <label for="sit_app_message"><?php esc_html_e( 'Komissiyaya mesajınız (istəyə bağlı)', 'studyinturkey' ); ?></label>
                            <textarea id="sit_app_message" name="sit_app_message" rows="3" maxlength="5000"><?php echo esc_textarea( $ov( 'sit_app_message' ) ); ?></textarea>
                        </p>
                    </fieldset>

                    <fieldset class="sit-app-form-files sit-app-form-section">
                        <legend><?php esc_html_e( 'Sənədlər', 'studyinturkey' ); ?></legend>
                        <p class="sit-app-form-hint"><?php esc_html_e( 'PDF, Word və ya şəkil (5 MB-dək). Foto: yalnız JPG/PNG/WEBP, pasport standartına uyğun üz şəkli.', 'studyinturkey' ); ?></p>

                        <div class="sit-app-form-grid sit-app-form-grid--files">
                            <p class="sit-app-form-field">
                                <label for="sit_app_passport"><?php esc_html_e( 'Pasport (skan)', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <input type="file" id="sit_app_passport" name="sit_app_passport" required accept=".pdf,.jpg,.jpeg,.png,.webp,image/*,application/pdf" />
                            </p>
                            <p class="sit-app-form-field">
                                <label for="sit_app_transcript"><?php esc_html_e( 'Transkript / attestat', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <input type="file" id="sit_app_transcript" name="sit_app_transcript" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*" />
                            </p>
                            <p class="sit-app-form-field">
                                <label for="sit_app_photo"><?php esc_html_e( 'Pasport formatlı foto', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                <input type="file" id="sit_app_photo" name="sit_app_photo" required accept=".jpg,.jpeg,.png,.webp,image/*" />
                            </p>

                            <div class="sit-app-form-section--levels sit-app-form-field--full" data-sit-app-levels="undergraduate" style="<?php echo ( 'undergraduate' === $init_level ) ? '' : 'display:none;'; ?>">
                                <p class="sit-app-form-field">
                                    <label for="sit_app_secondary_diploma"><?php esc_html_e( 'Orta təhsil diplomu / şəhadətnamə', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                    <input type="file" id="sit_app_secondary_diploma" name="sit_app_secondary_diploma" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*"<?php echo ( 'undergraduate' === $init_level ) ? '' : ' disabled'; ?> />
                                </p>
                            </div>

                            <div class="sit-app-form-section--levels sit-app-form-field--full" data-sit-app-levels="graduate doctoral" style="<?php echo in_array( $init_level, [ 'graduate', 'doctoral' ], true ) ? '' : 'display:none;'; ?>">
                                <p class="sit-app-form-field">
                                    <label for="sit_app_diploma_prior"><?php esc_html_e( 'Əvvəlki dərəcə diplomu', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                    <input type="file" id="sit_app_diploma_prior" name="sit_app_diploma_prior" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*"<?php echo in_array( $init_level, [ 'graduate', 'doctoral' ], true ) ? '' : ' disabled'; ?> />
                                </p>
                                <p class="sit-app-form-field">
                                    <label for="sit_app_cv"><?php esc_html_e( 'CV', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                    <input type="file" id="sit_app_cv" name="sit_app_cv" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*"<?php echo in_array( $init_level, [ 'graduate', 'doctoral' ], true ) ? '' : ' disabled'; ?> />
                                </p>
                                <p class="sit-app-form-field">
                                    <label for="sit_app_motivation"><?php esc_html_e( 'Motivasiya məktubu', 'studyinturkey' ); ?> <span class="required">*</span></label>
                                    <input type="file" id="sit_app_motivation" name="sit_app_motivation" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*"<?php echo in_array( $init_level, [ 'graduate', 'doctoral' ], true ) ? '' : ' disabled'; ?> />
                                </p>
                            </div>

                            <p class="sit-app-form-field sit-app-form-field--full">
                                <label for="sit_app_language_cert"><?php esc_html_e( 'Dil sertifikatı (istəyə bağlı)', 'studyinturkey' ); ?></label>
                                <input type="file" id="sit_app_language_cert" name="sit_app_language_cert" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*" />
                            </p>
                        </div>
                    </fieldset>

                    <p class="sit-app-form-submit">
                        <button type="submit" class="sit-app-form-button"><?php esc_html_e( 'Müraciəti göndər', 'studyinturkey' ); ?></button>
                    </p>
                </form>
                <script>
                (function(){
                    function sitAppFormSyncLevels(){
                        var sel=document.getElementById('sit_app_program_id');
                        if(!sel){return;}
                        var opt=sel.options[sel.selectedIndex];
                        var level=opt&&opt.getAttribute('data-sit-degree-level')?opt.getAttribute('data-sit-degree-level'):'undergraduate';
                        document.querySelectorAll('[data-sit-app-levels]').forEach(function(box){
                            var raw=box.getAttribute('data-sit-app-levels')||'';
                            var levels=raw.split(/\s+/).filter(Boolean);
                            var ok=levels.indexOf(level)!==-1;
                            box.style.display=ok?'':'none';
                            box.querySelectorAll('input,select,textarea').forEach(function(inp){
                                if(ok){inp.removeAttribute('disabled');}else{inp.setAttribute('disabled','disabled');}
                            });
                        });
                    }
                    function sitAppSyncDob(){
                        var d=document.querySelector('[name="sit_app_dob_day"]'),m=document.querySelector('[name="sit_app_dob_month"]'),y=document.querySelector('[name="sit_app_dob_year"]'),h=document.getElementById('sit_app_dob');
                        if(d&&m&&y&&h){h.value=(d.value&&m.value&&y.value)?(('0'+d.value).slice(-2)+'.'+('0'+m.value).slice(-2)+'.'+y.value):'';}
                    }
                    // Custom phone code dropdown with flags
                    function sitAppInitPhoneDropdown(){
                        var sel=document.querySelector('.sit-app-phone-code');
                        if(!sel||sel.dataset.customized)return;
                        sel.dataset.customized='1';
                        var cc={'+994':'az','+90':'tr','+7':'ru','+98':'ir','+966':'sa','+971':'ae','+962':'jo','+961':'lb','+964':'iq','+965':'kw','+968':'om','+974':'qa','+973':'bh','+967':'ye','+963':'sy','+970':'ps','+218':'ly','+216':'tn','+212':'ma','+213':'dz','+20':'eg','+249':'sd','+252':'so','+993':'tm','+996':'kg','+998':'uz','+992':'tj','+995':'ge','+374':'am','+380':'ua','+375':'by','+373':'md','+40':'ro','+359':'bg','+30':'gr','+49':'de','+33':'fr','+44':'gb','+39':'it','+34':'es','+31':'nl','+46':'se','+47':'no','+45':'dk','+43':'at','+41':'ch','+48':'pl','+420':'cz','+36':'hu','+1':'us','+55':'br','+52':'mx','+91':'in','+92':'pk','+93':'af','+880':'bd','+60':'my','+62':'id','+86':'cn','+82':'kr','+81':'jp','+234':'ng','+27':'za','+254':'ke','+255':'tz','+256':'ug','+233':'gh','+225':'ci','+237':'cm','+61':'au'};
                        function flagImg(code,sz){var iso=cc[code]||'xx';return '<img src="https://flagcdn.com/w40/'+iso+'.png" width="'+sz+'" height="'+(sz*0.7)+'" style="border-radius:2px;object-fit:cover;display:inline-block" alt="" />';}
                        var wrap=sel.parentElement;
                        sel.style.display='none';
                        var btn=document.createElement('button');
                        btn.type='button';
                        btn.style.cssText='display:flex;align-items:center;gap:6px;padding:0.5rem 0.625rem;border:1.5px solid #e2e8f0;border-radius:10px;background:#f8fafc;font-size:0.875rem;cursor:pointer;white-space:nowrap;';
                        function updateBtn(){btn.innerHTML=flagImg(sel.value,20)+'<span style="font-weight:500">'+sel.value+'</span><span style="color:#94a3b8;font-size:0.5rem;margin-left:2px">▼</span>';}
                        updateBtn();
                        var dd=document.createElement('div');
                        dd.style.cssText='display:none;position:absolute;z-index:50;top:100%;left:0;margin-top:4px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.12);max-height:260px;overflow-y:auto;width:180px;padding:4px;';
                        Array.from(sel.options).forEach(function(o){
                            var a=document.createElement('div');
                            a.style.cssText='display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:8px;cursor:pointer;font-size:0.875rem;';
                            a.innerHTML=flagImg(o.value,18)+'<span>'+o.value+'</span>';
                            a.addEventListener('mouseenter',function(){a.style.background='#f1f5f9';});
                            a.addEventListener('mouseleave',function(){a.style.background='';});
                            a.addEventListener('click',function(){sel.value=o.value;updateBtn();dd.style.display='none';});
                            dd.appendChild(a);
                        });
                        var container=document.createElement('div');
                        container.style.cssText='position:relative;flex:0 0 auto;';
                        container.appendChild(btn);
                        container.appendChild(dd);
                        wrap.insertBefore(container,wrap.firstChild);
                        btn.addEventListener('click',function(e){e.preventDefault();dd.style.display=dd.style.display==='none'?'block':'none';});
                        document.addEventListener('click',function(e){if(!container.contains(e.target))dd.style.display='none';});
                    }
                    document.addEventListener('DOMContentLoaded',function(){
                        var sel=document.getElementById('sit_app_program_id');
                        if(sel){sel.addEventListener('change',sitAppFormSyncLevels);sitAppFormSyncLevels();}
                        document.querySelectorAll('[name="sit_app_dob_day"],[name="sit_app_dob_month"],[name="sit_app_dob_year"]').forEach(function(s){s.addEventListener('change',sitAppSyncDob);});
                        sitAppInitPhoneDropdown();
                    });
                })();
                </script>
            <?php endif; ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
