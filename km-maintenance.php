<?php
/**
 * Plugin Name: Simple Pages/Posts Specific Maintenance Mode
 * Description: Simple and elagant Maintenance Message. Can be set to specific page/ posts or whole site.
 * Version: 1.0
 * Author: Mithilesh K.
 * Author URI: http://www.mrdevs.com/
 * License: GNU General Public License version 2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;
class kmithi_maintenance {

    const VERSION   = '1.0';

    // class instance
    static $instance;

    public function __construct(){
        if ( is_admin() ) {
            //to add required js and css files for admin settings
            add_action( 'admin_enqueue_scripts', [$this, 'enqueue_select2_jquery'] );
            //to add settings fields to manage configurations
            add_action( 'admin_init', [$this, 'init'] );
            //registering admin menu
            add_action( 'admin_menu', [$this, 'wpdocs_register_my_menu'] );
        }
        //to add overlay maintenance message at footer to load in case to show overlay maintenance message.
        add_action( 'wp_footer', [$this, 'add_footer_content'], 100);
        //to overwrite template to show only maintenance message and nothing else.
        add_filter( 'template_include', [$this, 'kmithi_maintenance_template'], 100 );
    }

    /** Singleton instance */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /*
     * This is to load jquery select2 plugin
     */
    public function enqueue_select2_jquery() {
        wp_register_style( 'select2css', esc_url( plugins_url( 'assets/css/select2.min.css', __FILE__ ) ), false, self::VERSION, 'all' );
        wp_register_script( 'select2', esc_url( plugins_url( 'assets/js/select2.min.js', __FILE__ ) ), array( 'jquery' ), self::VERSION, true );
        wp_enqueue_style( 'select2css' );
        wp_enqueue_script( 'select2' );
    }

    /*
     * this setup setting fields required to configure maintenance setups
     */
    public function init() {
        add_settings_section( "kmithimaintenance", "Maintenance Settings", null, "kmithi-maintenance" );

        // checkbox field to enable/ disable maintenance mode
        add_settings_field( "kmithi-fld-enable", "Enable", [$this, "enable_checkbox_display"], "kmithi-maintenance", "kmithimaintenance" );

        // this is to save setting value to decide if to show only maintenance message by template overwrite.
        add_settings_field( "kmithi-fld-enable-template-type", "Don't show anything except maintenance message.", [$this, "enable_checkbox_display_template_type"], "kmithi-maintenance", "kmithimaintenance" );

        // Save message to show when maintenance mode is on.
        add_settings_field( "kmithi-fld-message", "Maintenance Message", [$this, "maintenance_message_display"], "kmithi-maintenance", "kmithimaintenance" );

        // field to hold values of users roles to avoid maintenance mode. If user of specified role is logged in maintenance mode will be off
        add_settings_field( "kmithi-fld-avoid-for-user-roles", "Don't show for user roles", [$this, "avoid_maintenance_for_user_roles"], "kmithi-maintenance", "kmithimaintenance" );

        // on/off to all (whole website)/ to selected pages/ to selected posts.
        add_settings_field( "kmithi-fld-displaytooptions", "Maintaenance display ", [$this, "display_to_pages_options"], "kmithi-maintenance", "kmithimaintenance" );

        // selections for pages when to on/off for specific pages/posts
        add_settings_field( "kmithi-fld-pages", "Select Pages", [$this, "maintenance_selectpages_display"], "kmithi-maintenance", "kmithimaintenance" );

        // register fields
        register_setting( "kmithimaintenance", "kmithi-fld-enable" );
        register_setting( "kmithimaintenance", "kmithi-fld-enable-template-type" );
        register_setting( "kmithimaintenance", "kmithi-fld-avoid-for-user-roles", [$this, 'sanitize_multiselectoptions'] );
        register_setting( "kmithimaintenance", "kmithi-fld-message" );
        register_setting( "kmithimaintenance", "kmithi-fld-displaytooptions" );
        register_setting( "kmithimaintenance", "kmithi-fld-pages", [$this, 'sanitize_multiselectoptions'] );
    }

    /*
     * to convert input array to comma separeted value to options
     */
    public function sanitize_multiselectoptions($input){
        $input = !empty($input) ? $input : [];
        $input = implode(",", $input);
        return $input;
    }

    /*
     * setting page output function
     */
    public function _settings_page(){
    ?>
      <div class="wrap">
         <h1>Maintenance Settings</h1>
         <form method="post" action="options.php">
            <?php
               settings_fields("kmithimaintenance");
               do_settings_sections("kmithi-maintenance");
               submit_button();
            ?>
         </form>
      </div>
      <script type="text/javascript">
      jQuery(document).ready(function($) {
          $('select#kmithi-fld-pagemulti, select#kmithi-fld-rolemulti').select2();
      });
      </script>
    <?php
    }

    /*
     * register menu
     */
    public function wpdocs_register_my_menu(){
        $hook = add_submenu_page(
                   'options-general.php',
                   __( 'Configure Maintenance', 'kmithimaintenance' ),
                   'Configure Maintenance',
                   'manage_options',
                   'kmithi-maintenance-setting',
                   [$this, '_settings_page']
                );
    }

    /*
     * on/off maintenance mode
     */
    public function enable_checkbox_display(){
    ?>
        <input type="checkbox" name="kmithi-fld-enable" value="1" <?php checked(1, get_option('kmithi-fld-enable'), true); ?> />
    <?php
    }

    /*
     * to show full page message
     */
    public function enable_checkbox_display_template_type(){
    ?>
        <input type="checkbox" name="kmithi-fld-enable-template-type" value="1" <?php checked(1, get_option('kmithi-fld-enable-template-type'), true); ?> />
        <p class="description" id="template-overwrite-description">Enabling this will show maintenance message only, else message will be shown as overlay over page contents.</p>
    <?php
    }

    /*
     * save message to display
     */
    public function maintenance_message_display(){
        $message = get_option('kmithi-fld-message');
    ?>
        <textarea name="kmithi-fld-message" style="width:100%;" class="regular-text"><?php echo $message ? $message : 'Under Maintenance'; ?></textarea>
    <?php
    }

    /*
     * dropdowns for full page/ overlay maintenance messages
     */
    public function display_to_pages_options(){
        $selectionoption = get_option('kmithi-fld-displaytooptions');
    ?>
        <select name="kmithi-fld-displaytooptions" style="width:100%;">
            <option value="all" <?php echo ($selectionoption == 'all' ? 'selected="selected"' : '');?>>Enable To All</option>
            <option value="enableonlyselected" <?php echo ($selectionoption == 'enableonlyselected' ? 'selected="selected"' : '');?>>Enable To Selected Only</option>
            <option value="disabletoselected" <?php echo ($selectionoption == 'disabletoselected' ? 'selected="selected"' : '');?>>Disable To Selected</option>
        </select>
    <?php
    }

    /*
     * disable for user roles
     */
    public function avoid_maintenance_for_user_roles(){
        global $wp_roles;
        if ( !isset( $wp_roles ) ) $wp_roles = new WP_Roles();

        $selecteduserrole      = get_option('kmithi-fld-avoid-for-user-roles');
        $selecteduserrolearr   = explode(",", $selecteduserrole);
        $available_roles_names = $wp_roles->get_names();
        ?>
        <select name="kmithi-fld-avoid-for-user-roles[]" id="kmithi-fld-rolemulti" multiple="multiple" style="width:100%">
        <?php
        foreach ($available_roles_names as $role_key => $role_name):
        ?>
            <option value="<?php echo $role_key;?>" <?php echo (in_array($role_key, $selecteduserrolearr) ? 'selected="selected"' : '');?>><?php echo $role_name;?></option>
        <?php
        endforeach;?>    
        </select>
        <?php
    }

    /*
     * dropdowns of pages/ posts to show/ not show maintenance messages
     */
    public function maintenance_selectpages_display(){
        $selectedpages = get_option( 'kmithi-fld-pages' );
        $pagesarr      = explode(",", $selectedpages);
        $pages         = get_pages( 'hide_empty=0' );
        $posts         = get_posts( 'hide_empty=0&post_type=any' );
        //$pages         = new WP_Query( ['post_type' => 'any' ] );
        $pagesids      = [];
        ?>
        <select name="kmithi-fld-pages[]" id="kmithi-fld-pagemulti" multiple="multiple" style="width:100%">
            <optgroup label="Pages">
        <?php
        foreach ( $pages as $page ): $pagesids[] = $page->ID; ?>
            <option value="<?php echo $page->ID;?>" <?php echo (in_array($page->ID, $pagesarr) ? 'selected="selected"' : '');?>><?php echo $page->post_title;?></option>
        <?php
        endforeach;?>
            </optgroup>
            <optgroup label="Posts">
        <?php
        foreach ( $posts as $post ): if(in_array($post->ID, $pagesids)){ continue; } ?>
            <option value="<?php echo $post->ID;?>" <?php echo (in_array($post->ID, $pagesarr) ? 'selected="selected"' : '');?>><?php echo $post->post_title;?></option>
        <?php
        endforeach;?>
            </optgroup>
        </select>
        <?php
    }

    /*
     * helper function to check if logged in users role matched to selected role to avoid maintenance mode
     */
    public function do_match_loggedin_user_role(){
        if( is_user_logged_in() ) {
            $user                  = wp_get_current_user();
            $user_roles            = ( array ) $user->roles;
            $selecteduserrole      = get_option('kmithi-fld-avoid-for-user-roles');
            $selecteduserrolearr   = explode(",", $selecteduserrole);
            if( count( $user_roles ) > 0 && count( $selecteduserrolearr ) > 0 ){
                foreach( $user_roles AS $role ){
                    if( in_array( $role, $selecteduserrolearr ) ){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /*
     * This overwrite theme template when maintenace mode is full page and enabled
     * template can be overwritten if plugins template folder is copied to selected theme folder to customize as per requirements
     */
    public function kmithi_maintenance_template( $template ){
        if(self::do_match_loggedin_user_role()){
            return $template;
        }
        $selecteduserrole      = get_option('kmithi-fld-avoid-for-user-roles');
        $selecteduserrolearr   = explode(",", $selecteduserrole);

        $isenabled            = get_option('kmithi-fld-enable');
        $isenabled            = apply_filters( 'km_maintenance_enabled', $isenabled );
        if( $isenabled != 1 ) return $template;
        $tempoverwrite        = get_option('kmithi-fld-enable-template-type');
        $tempoverwrite        = apply_filters( 'km_maintenance_show_only_message', $tempoverwrite );
        if( $tempoverwrite != 1 ) return $template;
        $pageid               = get_the_ID();
        $displaytopageoption  = get_option('kmithi-fld-displaytooptions');
        $displaytopageoption  = apply_filters( 'km_maintenance_display_option', $displaytopageoption );
        $selectedpages        = get_option( 'kmithi-fld-pages' );
        $pagesarr             = explode(",", $selectedpages);
        $pagesarr             = apply_filters( 'km_maintenance_enabled_on_posts', $pagesarr );
        
        if($displaytopageoption == 'all'){
        }elseif($displaytopageoption == 'enableonlyselected'){
            if(!in_array($pageid, $pagesarr)) return $template;
        }elseif($displaytopageoption == 'disabletoselected'){
            if(in_array($pageid, $pagesarr)) return $template;
        }
        $template_name        = 'kmithi-maintenance.php';
        if( locate_template( $template_name ) ){
            return $template_name;
        }else{
            $abs_temp_name    = dirname(__FILE__) . '/templates/' . $template_name;
            return $abs_temp_name;
        }
    }

    /*
     * This loads template at footer when maintenace mode is overlaye and enabled
     * template can be overwritten if plugins template folder is copied to selected theme folder to customize as per requirements
     */
    function add_footer_content(){
        if(self::do_match_loggedin_user_role()){
            return '';
        }
        $isenabled            = get_option('kmithi-fld-enable');
        $isenabled            = apply_filters( 'km_maintenance_enabled', $isenabled );
        if( $isenabled != 1 ) return '';
        $tempoverwrite        = get_option('kmithi-fld-enable-template-type');
        $tempoverwrite        = apply_filters( 'km_maintenance_show_only_message', $tempoverwrite );
        if( $tempoverwrite == 1 ) return '';
        $currentpage          = get_post();
        $pageid               = $currentpage->ID;
        $displaytopageoption  = get_option('kmithi-fld-displaytooptions');
        $displaytopageoption  = apply_filters( 'km_maintenance_display_option', $displaytopageoption );
        $selectedpages        = get_option( 'kmithi-fld-pages' );
        $pagesarr             = explode(",", $selectedpages);
        $pagesarr             = apply_filters( 'km_maintenance_enabled_on_posts', $pagesarr );

        if($displaytopageoption == 'all'){
        }elseif($displaytopageoption == 'enableonlyselected'){
            if(!in_array($pageid, $pagesarr)) return '';
        }elseif($displaytopageoption == 'disabletoselected'){
            if(in_array($pageid, $pagesarr)) return '';
        }
        $template_name        = 'partials/kmithi-maintenance.php';
        if( locate_template( $template_name ) ){
            locate_template( $template_name, true, true);
        }else{
            $abs_temp_name    = dirname(__FILE__) . '/templates/' . $template_name;
            require_once( $abs_temp_name );
        }
    }
}

add_action( 'plugins_loaded', function () {
    kmithi_maintenance::get_instance();
} );
