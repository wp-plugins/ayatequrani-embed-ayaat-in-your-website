<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class NTX_plugin {

    // the instance of our plugin
    private static $_instance = null;

    // token to use to make every thing unique
    public $token;

    // domain of current plugin
    public $domain;


    // local variables for plugin
    public $version;

    // current file to load
    public $file;

    private $db_prefix;

    //current dir of plugin
    public $dir;

    //current url of asset folder
    public $url;


    public function __construct($file = '', $version = '1.0', $token = "ntx_plugin")
    {
        $this->version = $version;
        $this->file = $file;
        
        $this->token = $token;
        $this->domain = $token;
        global $wpdb;
        $this->db_prefix = $wpdb->prefix . $this->token . "_quran_";

        $this->dir = trailingslashit(dirname($this->file));

        $this->url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

        register_activation_hook( $this->file, array( $this, 'install' ) );
        register_deactivation_hook( $this->file, array( $this, 'uninstall' ) );

        // Load frontend JS & CSS
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
        // add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

        // Load admin JS & CSS
        // add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
        // add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

        // Add Any Shortcode
        add_filter("widget_text","do_shortcode");
        add_shortcode("quran",array($this, "shortcodecb"));

        // debug shortlink method
        //add_filter('get_shortlink', array( &$this, 'mk_go_nitro_link' ), 10, 3);

        // Handle localisation
        $this->load_plugin_textdomain();
        add_action( 'init', array( $this, 'load_localisation' ), 0 );
    }
    public function mk_go_nitro_link()
    {
        return "http://nitroxis.com/";//print_r(func_get_args(),1);
    }

    public function shortcodecb($atts = array(), $content = "")
    {
        extract(shortcode_atts(
            array(
                "surah" => 1,
                "ayat"  => "1",
                "english" => 1,
                "arabic" => 1,
                "urdu" => 1,
                "size" => "m",
                "roman" => 1)
            , $atts));

        $ayats = array();
        $ayaats = array(
            'ar' => "",
            'tr' => "",
            'ur' => "",
            'en' => ""
            );

        if(preg_match("/-/", $ayat))
        {
            $cc = "multiple";
        }
        else
        {
            $cc = "single";
        }

        if($cc == "single")
        {

            if($arabic)
                $ayaats['ar'] = $this->get_ayat($surah, $ayat, "ar")[0]->text;
            if($roman)
                $ayaats['tr'] = $this->get_ayat($surah, $ayat, "tr")[0]->text;
            if($urdu)
                $ayaats['ur'] = $this->get_ayat($surah, $ayat, "ur")[0]->text;
            if($english)
                $ayaats['en'] = $this->get_ayat($surah, $ayat, "en")[0]->text;
            
        }
        else
        {
            if($arabic)
            {
                $ayaats['ar'] = "";
                foreach ($this->get_ayat($surah, $ayat, "ar") as $a) {
                    $ayaats['ar'] .= $a->text . " . ";
                }
            }
            if($roman)
            {
                $ayaats['tr'] = "";
                foreach ($this->get_ayat($surah, $ayat, "tr") as $a) {
                    $ayaats['tr'] .= $a->text . " . ";
                }
            }
            if($urdu)
            {
                $ayaats['ur'] = "";
                foreach ($this->get_ayat($surah, $ayat, "ur") as $a) {
                    $ayaats['ur'] .= $a->text . " . ";
                }
            }
            if($english)
            {
                $ayaats['en'] = "";
                foreach ($this->get_ayat($surah, $ayat, "en") as $a) {
                    $ayaats['en'] .= $a->text . " . ";
                }
            }

        }

        $html = '<div class="ayatequrani_ntx '.$cc.'">';
        
        $html .= '  <div class="ayat_holder">';
            foreach ($ayaats as $lang => $a):
                if(strlen($a) < 1) continue;
                $html .= '<div class="ayat '.$lang;
                if($lang=="ur" || $lang=="ar")
                    {$html .= " rtl ";}
                else {$html .= " ltr ";}
                $html .= ' base-'.$size.'" >';
                $html .= $a;
                $html .= '</div>';
            endforeach;
        $html .= '  </div>';
        $html .= "<span class='ntx_badge'>(" . $surah . " : " . $ayat.")</span>";
        $html .= "<div class='ntx_watermark'><a href='http://nitroxis.com/wordpress/ayatequrani_ntx' target='_blank'>AyateQurani Service - by Nitroxis</a></div>";
        $html .= '</div>';
        return $html;

    }

    private function get_ayat($sura = 1, $aya = "1-1", $lang = "")
    {
        global $wpdb;

        $sql = "SELECT `text` FROM `" . $this->db_prefix . $lang ."` WHERE ";
        $sql .= " `sura` = '".$sura."' ";

        if(preg_match("/-/", $aya))
        {
            list($st, $en) = preg_split("/-/", $aya);
            $sql .= " AND `aya` <= '".$en."' AND `aya` >= '".$st."' ";
        }
        else
        {
            $sql .= " AND `aya` = '".$aya."' ";
        }

        $result = $wpdb->get_results($sql);
        return isset($result[0]) ? $result : array(0=>"n/a");
    }
    
    public function enqueue_styles ()
    {
        wp_register_style( $this->token . '-frontend', esc_url( $this->url ) . 'css/style.css', array(), $this->version );
        wp_enqueue_style( $this->token . '-frontend' );
    }

    public function enqueue_scripts ()
    {
        wp_register_script( $this->token . '-frontend', esc_url( $this->url ) . 'js/plugin.js', array( 'jquery' ), $this->version );
        wp_enqueue_script( $this->token . '-frontend' );
    }

    public function admin_enqueue_styles ( $hook = '' )
    {
        // wp_register_style( $this->token . '-admin', esc_url( $this->url ) . 'css/admin.style.css', array(), $this->version );
        // wp_enqueue_style( $this->token . '-admin' );
    }

    public function admin_enqueue_scripts ( $hook = '' )
    {
        // wp_register_script( $this->token . '-admin', esc_url( $this->url ) . 'js/admin.plugin.js', array( 'jquery' ), $this->version );
        // wp_enqueue_script( $this->token . '-admin' );
    }

    public function load_localisation ()
    {
        load_plugin_textdomain( $this->domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
    }

    public function load_plugin_textdomain ()
    {
        $domain = $this->domain;

        $locale = apply_filters( 'plugin_locale', get_locale(), $this->domain );

        load_textdomain( $domain, WP_LANG_DIR . '/' . $this->domain . '/' . $this->domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
    }

    /*
    *   Activation Hook for your plugin
    */
    public function install ()
    {
        global $wpdb;

        $this->db = $this->load_sql("en");
        $this->db = $this->load_sql("ar");
        $this->db = $this->load_sql("ur");
        $this->db = $this->load_sql("tr");
    }

    private function load_sql ($file = "")
    {
        global $wpdb;
        $dbname = $this->db_prefix . $file;

        $templine = '';
        $result = array();

        $sql_file = file($this->dir . "sql/".$file . ".sql");
        if(!$sql_file)
            return;
        foreach ($sql_file as $line)
        {
            $line = str_replace("[[dbname]]", $dbname, $line);

            if (substr($line, 0, 2) == '--' || $line == '')
            {
                continue;
            }

            $templine .= $line;
            if (substr(trim($line), -1, 1) == ';')
            {
                $now_result = (int) $wpdb->query($templine);
                $result[] = $now_result;
                if(!$now_result) return "error";
                $templine = '';
            }

        }
        return $result;
    }

    /*
    * Deactivation Hook for your plugin\
    * 
    * @since 1.0
    */
    public function uninstall ()
    {
        // do something on uninstalling the plugin
        // TODO: remove the table we've created if supported
        // error_log("Uninstalled the Plugin");
        // file_put_contents("c:\\wamp\\www\\data.txt", "Uninstalled the Plugin", FILE_APPEND);
    }

    // Do not edit these
    public static function instance ( $file = '', $version = '1.0.0', $token = "" )
    {
        if ( is_null( self::$_instance ) )
        {
            self::$_instance = new self( $file, $version );
        }
        return self::$_instance;
    }

    public function __clone ()
    {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->version );
    }

    public function __wakeup ()
    {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->version );
    }

}
?>