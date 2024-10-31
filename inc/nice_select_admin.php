<?php
// Prevent Direct access
if (!defined('ABSPATH')) {
    exit;
}
class NSFW_NiceSelectAdmin {
    public function __construct() {
        // Add actions and filters when the plugin is loaded
        add_action( 'plugin_loaded', array( $this, 'nsfw_bootstrap') );
        add_action( 'admin_menu', array( $this, 'nsfw_create_settings' ) );
        add_action( 'admin_init', array( $this, 'nsfw_setup_sections' ) );
        add_action( 'admin_init', array( $this, 'nsfw_setup_fields' ) );
        add_action( 'admin_enqueue_scripts', array($this, 'nsfw_admin_enqueue_scripts'));
    }

    public function nsfw_admin_enqueue_scripts() {
        // Enqueue scripts and styles on the plugin settings page
        global $pagenow;
        if ( 'options-general.php' === $pagenow  && 'niceselect' === $_GET['page'] ) {
            
            // Enqueue Nice Select CSS
            wp_enqueue_style('nice-select-css', plugin_dir_url(__FILE__) . '../assets/css/nice-select.css', array(), '1.0', 'all');
            
            // Enqueue Nice Select JavaScript
            wp_enqueue_script('nice-select-js', plugin_dir_url(__FILE__) . '../assets/js/jquery.nice-select.min.js', array('jquery'), '1.0', true);
            
            // Enqueue CodeMirror theme
            wp_enqueue_style( 'codemirror-theme', plugin_dir_url(__FILE__) . '../assets/css/dracula.min.css', array(), '5.62.0', 'all' );
    
            // Enqueue Code Editor and customize it with theme and inline script
            $custom_css = wp_enqueue_code_editor( array( 'type' => 'text/css') );
            $custom_css['codemirror']['theme'] = 'dracula';
            wp_add_inline_script(
                'code-editor',
                sprintf(
                    'jQuery( function() {
                        jQuery("select").addClass("wide").niceSelect();
                        jQuery("select").next(".nice-select").css({"width":"200px", "float": "none", "transition" : "none"});
                        wp.codeEditor.initialize( jQuery( "#custom_css" ), %1$s );
                    });',
                    wp_json_encode( $custom_css )
                )
            );
        }
    }
    
    public function nsfw_bootstrap(){
        // Load the plugin text domain for translation
        load_plugin_textdomain( 'nice-select-for-wp', false, plugin_dir_path(__FILE__)."/languages" );
    }

    public function nsfw_create_settings() {
        // Add options page to the WordPress admin menu
        $nice_select_page_title = __('Nice Select', 'nice-select-for-wp');
        $nice_select_menu_title = __('Nice Select', 'nice-select-for-wp');
        $nice_select_capability = 'manage_options';
        $nice_select_slug = 'niceselect';
        $nice_select_callback = array($this, 'nice_select_settings_content');
        add_options_page($nice_select_page_title, $nice_select_menu_title, $nice_select_capability, $nice_select_slug, $nice_select_callback);
    }

    public function nice_select_settings_content() {
    ?>
        <!-- Display the plugin settings content in the WordPress admin -->
        <div class="wrap">
            <h1>Nice Select</h1>
            <form method="POST" action="options.php">
                <?php
                    settings_fields( 'niceselect' );
                    do_settings_sections( 'niceselect' );
                    wp_nonce_field('niceselect_nonce_action', 'niceselect_nonce_field');
                    submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    public function nsfw_setup_sections() {
        // Setup sections for the plugin settings
        add_settings_section( 'niceselect_section', __("Nice select settings", "nice-select-for-wp"), array(), 'niceselect' );
    }

    public function nsfw_setup_fields() {
        // Setup fields for the plugin settings
        $fields = array(
            array(
                'label' => __('Selector', 'nice-select-for-wp'),
                'id' => 'selector',
                'type' => 'text',
                'section' => 'niceselect_section',
                'desc' => __('Add a CSS Class / CSS ID. Example: <span style="color: #1e2136; font-weight: bold">#select</span> or <span style="color: #1e2136; font-weight: bold">.select</span>. </br>
                            If it is blank, it will be applied to all select tags on the site.', 'nice-select-for-wp'),
                'placeholder' => '.nice-select',
            ),
            
            array(
                'label' => __('Placeholder', 'nice-select-for-wp'),
                'id' => 'placeholder_text',
                'type' => 'text',
                'section' => 'niceselect_section',
                'placeholder' => __('placeholder','nice-select-for-wp'),
            ),
            array(
                'label' => __('Aligntment','nice-select-for-wp'),
                'id' => 'alignment',
                'type' => 'select',
                'section' => 'niceselect_section',
                'options' => array(
                    'left' => __('Left','nice-select-for-wp'),
                    'right' => __('Right','nice-select-for-wp'),
                ),
            ),
            array(
                'label' => __('Full Width','nice-select-for-wp'),
                'id' => 'fullWidth',
                'type' => 'select',
                'section' => 'niceselect_section',
                'options' => array(
                    'enable' => __('Enable','nice-select-for-wp'),
                    'disable' => __('Disable','nice-select-for-wp'),
                ),
            ),
            array(
                'label' => __('Custom CSS', 'nice-select-for-wp'),
                'id' => 'custom_css',
                'type' => 'code_editor',
                'section' => 'niceselect_section',
                'desc' => __('Add your custom CSS code here.', 'nice-select-for-wp'),
            )
        );
        foreach( $fields as $field ){
            add_settings_field( wp_kses_post($field['id']), $field['label'], array( $this, 'nice_select_field_callback' ), 'niceselect', $field['section'], $field );
            register_setting( 'niceselect', wp_kses_post($field['id']) );
        }
    }

    public function nice_select_field_callback( $field ) {
        // Callback function to render the HTML for each field
        $value = get_option( wp_kses_post($field['id']) );
        $placeholder = '';
        if ( isset($field['placeholder']) ) {
            $placeholder = $field['placeholder'];
        }
        switch ( $field['type'] ) {
            // Switch based on field type to render appropriate input element
            case 'select':
            case 'multiselect':
                if( ! empty ( $field['options'] ) && is_array( $field['options'] ) ) {
                    $attr = '';
                    $options = '';
                    foreach( $field['options'] as $key => $label ) {
                        $options.= sprintf('<option value="%s" %s>%s</option>',  $key, selected($value, $key, false), $label );
                    }
                    if( $field['type'] === 'multiselect' ){
                        $attr = ' multiple="multiple" ';
                    }
                    printf( '<select  style="width: 200px"  name="%1$s" id="%1$s" %2$s>%3$s</select>',
                        wp_kses_post($field['id']),
                        wp_kses_post($attr),
                        wp_kses($options, array( 
                            'option'=> array(
                                    'value' => true,
                                    'selected' => true
                                ) 
                            )
                        )
                    );
                }
                break;
            case 'code_editor':
                printf('<textarea id="%1$s" name="%1$s" class="code" rows="10">%2$s</textarea>', wp_kses_post($field['id']), wp_kses_post($value));
                break;
            default:
                printf( '<input style="width: 200px" name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />',
                    wp_kses_post($field['id']),
                    wp_kses_post($field['type']),
                    wp_kses_post($placeholder),
                    wp_kses_post($value)
                );
        }
        if( isset($field['desc']) ) {
            if( $desc = $field['desc'] ) {
                printf( '<p class="description">%s </p>', wp_kses_post($desc) );
            }
        }
    }
}

// Instantiate NiceSelectAdmin class only if in the admin panel
if(is_admin()){
    new NSFW_NiceSelectAdmin();
}
