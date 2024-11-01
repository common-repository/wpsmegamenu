<?php
/**
 * Plugin Name: wpsmegamenu
 * Plugin URI: https://wordpress.org/plugins/wpsmegamenu/
 * Description: This Plugin is customized for any Elementor-based Theme.
 * Version: 1.0.0
 * Author: Rashid87
 * Text Domain: wpsmegamenu
 * Domain Path: /languages/
 * Author URI: https://profiles.wordpress.org/rashid87/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Core plugin class
class wpsmegaMenu {

    private $plugin_dir;
    private $plugin_url;
    private $version;

    public function __construct() {
        $this->plugin_dir = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);
        $this->version = '1.0.0'; // You can change this to your plugin version

        // Enqueue style.css and script.js
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles_scripts'));
        // Load text domain
        add_action('plugins_loaded', array($this, 'wpsmegamenu_load_textdomain'));
    }

    public function enqueue_styles_scripts() {
        // Register the style with version
        $style_version = filemtime($this->plugin_dir . 'assets/css/style.css');
        wp_register_style('wpsmegamenu-style', $this->plugin_url . 'assets/css/style.css', array(), $style_version);
        // Enqueue the style
        wp_enqueue_style('wpsmegamenu-style');

        // Register the script with version
        $script_version = filemtime($this->plugin_dir . 'assets/js/script.js');
        wp_register_script('wpsmegamenu-script', $this->plugin_url . 'assets/js/script.js', array('jquery'), $script_version, true);
        // Enqueue the script
        wp_enqueue_script('wpsmegamenu-script');

        // Optionally add inline JavaScript
        $custom_js = "
            jQuery(document).ready(function($) {
                console.log('wpsmegamenu script loaded.');
            });";
        wp_add_inline_script('wpsmegamenu-script', $custom_js);
    }

    public function wpsmegamenu_load_textdomain() {
        load_plugin_textdomain('wpsmegamenu', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}




// Shortcode functions
function wpsmegaMenuShortcode_nav_menu($nav_menu, $args) {
    return preg_replace_callback('/<li [^>]+>(.+?)<\/li>/', function($matches) {
        return str_replace($matches[1], do_shortcode($matches[1]), $matches[0]);
    }, $nav_menu);
}
add_filter('wp_nav_menu', 'wpsmegaMenuShortcode_nav_menu', 10, 2);

// Admin menu functions
add_action('admin_menu', 'wpsmegamenu_add_admin_menus');

function wpsmegamenu_add_admin_menus() {
    add_menu_page(
        'WPS MegaMenu',
        'WPS MegaMenu',
        'manage_options',
        'wpsmegamenu_template',
        'wpsmegamenu_template_page',
        'dashicons-welcome-widgets-menus',
        40
    );

    add_submenu_page(
        'wpsmegamenu_template',
        'Add New MegaMenu',
        'Add New MegaMenu',
        'manage_options',
        'wpsmegamenu_add_new',
        'wpsmegamenu_add_new_page'
    );
}

function wpsmegamenu_template_page() {
    echo '<div class="wrap"><h1>WPS MegaMenu</h1></div>';
}

function wpsmegamenu_add_new_page() {
    echo '<div class="wrap"><h1>Add New MegaMenu</h1></div>';
}

// Elementor Post Type trait
trait wpsmegaMenuPostType {
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('WpsmegaMenu Templates', 'Post Type General Name', 'wpsmegamenu'),
            'singular_name'         => _x('WpsmegaMenu Template', 'Post Type Singular Name', 'wpsmegamenu'),
            'menu_name'             => __('WpsmegaMenu Templates', 'wpsmegamenu'),
            'name_admin_bar'        => __('WpsmegaMenu Templates', 'wpsmegamenu'),
            'archives'              => __('List Archives', 'wpsmegamenu'),
            'parent_item_colon'     => __('Parent List:', 'wpsmegamenu'),
            'all_items'             => __('WpsmegaMenu Templates', 'wpsmegamenu'),
            'add_new_item'          => __('Add New WpsmegaMenu Template', 'wpsmegamenu'),
            'add_new'               => __('Add New WpsmegaMenu', 'wpsmegamenu'),
            'new_item'              => __('New WpsmegaMenu Template', 'wpsmegamenu'),
            'edit_item'             => __('Edit WpsmegaMenu Template', 'wpsmegamenu'),
            'update_item'           => __('Update WpsmegaMenu Template', 'wpsmegamenu'),
            'view_item'             => __('View WpsmegaMenu Template', 'wpsmegamenu'),
            'search_items'          => __('Search WpsmegaMenu Template', 'wpsmegamenu'),
            'not_found'             => __('Not found', 'wpsmegamenu'),
            'not_found_in_trash'    => __('Not found in Trash', 'wpsmegamenu')
        );
        $args = array(
            'label'                 => __('WpsmegaMenu Templates', 'wpsmegamenu'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor'),
            'public'                => true,
            'rewrite'               => false,
            'show_ui'               => true,
            'show_in_menu'          => 'wpsmegamenu_template',
            'show_in_nav_menus'     => false,
            'exclude_from_search'   => true,
            'capability_type'       => 'post',
            'hierarchical'          => false,
            'menu_icon'             => 'dashicons-image-rotate-right',
            'menu_position'         => 60
        );
        register_post_type('wpsmega_templates', $args);

        add_post_type_support('wpsmega_templates', 'elementor');
    }
}

// Elementor Post Type class
class wpsmegaMenuMyPlugin {
    use wpsmegaMenuPostType;

    public function __construct() {
        add_action('init', [$this, 'register_post_type'], 9);
    }
}

// Shortcode handler class
class wpsmegaMenuMrShortcode {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        add_shortcode('WPSMEGAMENU_SHORTCODE', [$this, 'render_shortcode']);
        add_filter('widget_text', 'do_shortcode');
    }

    public function render_shortcode($atts) {
        if (!isset($atts['id']) || empty($atts['id'])) {
            return '';
        }

        $post_id = sanitize_text_field($atts['id']);
        $post = get_post($post_id);

        if (!$post) {
            return '';
        }

        // Return the content of the custom post
        return apply_filters('the_content', $post->post_content);
    }
}

// Meta Boxes class
class wpsmegamenuMrMetaBoxes {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        add_action("add_meta_boxes", [$this, 'add_meta_boxes']);
        add_filter('manage_wpsmega_templates_posts_columns', [$this, 'add_column']);
        add_action('manage_wpsmega_templates_posts_custom_column', [$this, 'column_data'], 10, 2);
    }

    public function add_meta_boxes() {
        add_meta_box('mr-shortcode-box', 'WpsmegaMenu Shortcode Area', [$this, 'wpsmegamenu_shortcode_box'], 'wpsmega_templates', 'side', 'high');
    }

    public function wpsmegamenu_shortcode_box($post) {
        ?>
        <h4><?php esc_html_e('Dynamic Shortcode', 'wpsmegamenu'); ?></h4>
        <div style="position: relative;">
            <?php
            $post_id = sanitize_text_field($post->ID); // Sanitize the post ID
            $shortcode_value = esc_attr('[WPSMEGAMENU_SHORTCODE id="' . $post_id . '"]'); // Escape the shortcode value for HTML attribute
            ?>
            <input type="text" id="shortcode-input-<?php echo esc_attr($post_id); ?>" class="widefat" value="<?php echo esc_attr($shortcode_value); ?>" readonly>
            <button class="copy-shortcode" data-id="<?php echo esc_attr($post_id); ?>" >
                <?php esc_html_e('Copy', 'wpsmegamenu'); ?>
            </button>
        </div>
        <?php
    }

    function add_column($columns) {
        $columns['wpsmega_post_column'] = __('Wpsection Shortcode', 'wpsmegamenu');
        return $columns;
    }

    function column_data($column, $post_id) {
        switch ($column) {
            case 'wpsmega_post_column':
                $post_id = sanitize_text_field($post_id); // Sanitize the post ID
                $shortcode_value = esc_attr('[WPSMEGAMENU_SHORTCODE id="' . $post_id . '"]'); // Escape the shortcode value for HTML attribute
                ?>
                <div style="position: relative;">
                    <input type="text" id="shortcode-input-<?php echo esc_attr($post_id); ?>" class="widefat" value="<?php echo esc_attr($shortcode_value); ?>" readonly>
                    <button class="copy-shortcode" data-id="<?php echo esc_attr($post_id); ?>">
                        <?php esc_html_e('Copy', 'wpsmegamenu'); ?>
                    </button>
                </div>
                <?php
                break;
        }
    }
}

// Instantiate the classes
$wpsmegamenu_instance = new wpsmegaMenu();
new wpsmegaMenuMyPlugin();
wpsmegaMenuMrShortcode::instance();
wpsmegamenuMrMetaBoxes::instance();
?>

