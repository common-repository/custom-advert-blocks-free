<?php
/*
  Plugin Name: Custom Advert Blocks Free
  Description: Make Much More Money with Your WordPress Website! Free Version.
  Version: 1.0.4
  Author: Easier Press
  Author URI: https://easier.press
  Text Domain: custom-blocks-free
  Domain Path: /lang/
 */

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

include(dirname(__FILE__) . '/widget/CustomBlocksWidget.php');

// Запрещаем прямой доступ к файлу
if (!function_exists('add_action'))
    exit;

require_once("classes/CustomBlock.php");
require_once("classes/CustomBlockItem.php");

class CustomBlocksPlugin {

    protected $_meta_viewport_found = false;

    public function __construct() {
        // Добавляем пункты меню плагина в админке
        add_action('admin_enqueue_scripts', array($this, 'admin_head_action_menu'));
        add_action('admin_menu', array($this, 'admin_menu_action'));
        add_action('admin_head', array($this, 'admin_add_styles'));
        // Подключаем скрипты на клиенте
        add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts_action'));
        // Подключаем скрипты и стили в админке
        //add_action('admin_head', array($this, 'admin_head_action'));
        // Регистрируем пункты настроек и их обработчики
        add_action('admin_init', array($this, 'admin_init_action'));
        // Регистрирует фильтр обработки контента
        add_action('init', array($this, 'init_action'), 9999);
        // Меняем статус кода по клику в статистике
        add_action('wp_ajax_item_state', array($this, 'item_state_ajax_action'));
        // Ajax действия для отработки клика по блоку
        add_action('wp_ajax_click_block', array($this, 'click_block_ajax_action'));
        add_action('wp_ajax_nopriv_click_block', array($this, 'click_block_ajax_action'));
        // Ajax действия для отработки клика по блоку
        add_action('wp_ajax_get_block', array($this, 'get_ads_ajax_action_prestart'));
        add_action('wp_ajax_nopriv_get_block', array($this, 'get_ads_ajax_action_prestart'));
        //process blocks
        add_action('wp_ajax_input_process_block', array($this, 'process_blocks'));
        add_action('wp_ajax_nopriv_input_process_block', array($this, 'process_blocks'));
        //get list decor template
        add_action('wp_ajax_get_decor_template', array($this, 'get_decor_template'));
        add_action('wp_ajax_nopriv_get_decor_template', array($this, 'get_decor_template'));
        //get decor for codes
        add_action('wp_ajax_get_decor_template_codes', array($this, 'get_decor_template_codes'));
        add_action('wp_ajax_nopriv_get_decor_template_codes', array($this, 'get_decor_template_codes'));
        //check title for decor
        add_action('wp_ajax_check_name_for_decor', array($this, 'check_name_for_decor'));
        add_action('wp_ajax_nopriv_check_name_for_decor', array($this, 'check_name_for_decor'));

        add_action('wp_ajax_checking_groups_templates', array($this, 'checking_groups_templates'));
        add_action('wp_ajax_nopriv_checking_groups_templates', array($this, 'checking_groups_templates'));

        add_action('wp_ajax_ajax_check_url_templates', array($this, 'ajax_check_url_templates'));
        add_action('wp_ajax_nopriv_ajax_check_url_templates', array($this, 'ajax_check_url_templates'));

        add_action('wp_ajax_clear_statistic_block', array($this, 'clear_statistic_block'));
        add_action('wp_ajax_nopriv_clear_statistic_block', array($this, 'clear_statistic_block'));

        add_action('wp_ajax_get_geo', array($this, 'geting_geo'));
        add_action('wp_ajax_get_posting', array($this, 'get_posting'));
        // Регистрируем шорткод
        add_shortcode('block', array($this, 'shortcode_handler'));
        // Обрабатывает контент и расставляет нём блоки
        add_filter('the_content', array($this, 'cb_the_content_filter'), 999);
        add_action('wp_ajax_getcity', array($this, 'get_all_city'));
        add_action('wp_ajax_getregion', array($this, 'get_all_region'));

        add_action('wp_ajax_get_terms_by_taxonomy', array($this, 'get_terms_by_taxonomy'));
        add_action('wp_ajax_nopriv_get_terms_by_taxonomy', array($this, 'get_terms_by_taxonomy'));

        add_action('admin_notices', array($this, 'error_notice_download'));
        add_filter('the_content', 'do_shortcode', 20);
        add_action('simple_edit_form', array($this, 'edit_form_action_show_ad'));
        add_action('edit_form_advanced', array($this, 'edit_form_action_show_ad'));
        add_action('edit_page_form', array($this, 'edit_form_action_show_ad'));
        add_action('edit_post', array($this, 'edit_action_show_ad'));
        add_action('publish_post', array($this, 'edit_action_show_ad'));
        add_action('save_post', array($this, 'edit_action_show_ad'));
        add_action('edit_page_form', array($this, 'edit_action_show_ad'));
        add_action('post_thumbnail_html', array($this, 'adding_ads_to_thumb'));

        register_activation_hook(__FILE__, array($this, 'activation_hook'));
        add_action('plugins_loaded', array($this, 'activation_hook'));
        add_action('plugins_loaded', array($this, 'load_my_textdomain'));
    }

    function load_my_textdomain() {
        load_plugin_textdomain('custom-blocks-free', false, plugin_basename(dirname(__FILE__)) . '/lang');
    }

    function admin_head_action_menu() {
        wp_enqueue_style('custom-blocks-css-style-menu', plugins_url('/css/menu.css', __FILE__));
    }

    function admin_add_styles() {
        wp_enqueue_script('custom-blocks', plugins_url('/js/admin.js', __FILE__), array('jquery'), '1.0.0');
        wp_localize_script('custom-blocks', 'local', array(
            'deletecode' => __('Delete this code?', 'custom-blocks-free'),
            'two_block' => __('You may create only two blocks', 'custom-blocks-free'),
            'notcode' => __('No codes yet', 'custom-blocks-free'),
            'error_not_field' => __('Error: empty field! Please check if all fields are filled correctly!', 'custom-blocks-free'),
            'name_new' => __('Please, specify the title of the new template', 'custom-blocks-free'),
            'name_plugin' => __('Custom Blocks', 'custom-blocks-free'),
            'block_insert' => __('Choose a block to insert', 'custom-blocks-free'),
            ));
    }
    
     public function get_links_for_admin($support_links = true) {
        $add_pages = array();
        if (isset($_GET['page']) && $_GET['page']) {
            switch ($_GET['page']) {
                case 'custom-blocks':
                    if (isset($_GET['action']) && $_GET['action'] == 'update') {
                        $add_pages[] = array('name' => __('Targeting by countries, regions and cities (GEO)', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/geo-targeting/', 'color' => '');
                        $add_pages[] = array('name' => __('Targeting by content (posts, categories, etc.)', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/content-targeting/', 'color' => '');
                        $add_pages[] = array('name' => __('Targeting by time', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/time-targeting/', 'color' => '');
                        $add_pages[] = array('name' => __('Setting up a block style', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/setup/block-layout/', 'color' => '');
                        $add_pages[] = array('name' => __('Using targeting templates', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/targeting-templates-usage/', 'color' => '');
                        $add_pages[] = array('name' => __('Using screen resolutions', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/screens-usage/', 'color' => '');
                        $add_pages[] = array('name' => __('How to bypass the Adblock detection', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/bypass-adblock/', 'color' => '');
                        /* $add_pages[] = array('name' => __('Настройка и использование заглушки', 'custom-blocks-free'), 'link' => '#', 'color' => ''); */
                        $add_pages[] = array('name' => __('How to add a PHP code inside blocks', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/expet-php/', 'color' => '');
                    } else {
                        $add_pages[] = array('name' => __('Quick Start Guide', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/quick-start-cb/', 'color' => '');
                        $add_pages[] = array('name' => __('Managing custom blocks', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/add-blocks/', 'color' => '');
                        $add_pages[] = array('name' => __('Targeting setup', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/setup/targeting-cb/', 'color' => '');
                        $add_pages[] = array('name' => __('How to insert blocks inside posts and pages using shortcodes', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/blocks-in-posts/', 'color' => '');
                        $add_pages[] = array('name' => __('How to insert blocks inside your theme using PHP', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/blocks-in-theme/', 'color' => '');
                    }
                    break;

                case 'custom-blocks-settings':
                    $add_pages[] = array('name' => __('Description and usage of output formats', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/output-formats/', 'color' => '');
                    $add_pages[] = array('name' => __('How to output blocks', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/output-blocks/', 'color' => '');
                    $add_pages[] = array('name' => __('How to add a PHP code inside blocks', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/expet-php/', 'color' => '');
                    $add_pages[] = array('name' => __('Setting up a custom ad ID', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/setup/ad-id/', 'color' => '');
                    break;
                case 'custom-blocks-statistics':
                    $add_pages[] = array('name' => __('How to use statistics', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/stats-usage/', 'color' => '');
                    break;
                case 'custom-blocks-template-filter':
                    $add_pages[] = array('name' => __('Targeting templates setup', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/setup/targeting-templates/', 'color' => '');
                    $add_pages[] = array('name' => __('How to use targeting templates', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/targeting-templates-usage/', 'color' => '');
                    break;
                case 'custom-blocks-resolution':
                    $add_pages[] = array('name' => __('Screen resolutions setup', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/setup/screens-setup/', 'color' => '');
                    $add_pages[] = array('name' => __('How to effectively use screen resolutions', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/cases/screens-usage/', 'color' => '');
                    break;

                case 'custom-blocks-decor':
                    $add_pages[] = array('name' => __('Create and edit style templates', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/setup/layout-templates/', 'color' => '');
                    break;
                case 'custom-blocks-download':
                    $add_pages[] = array('name' => __('How to upload Geolocation base', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/setup/import-export/', 'color' => '');
                    break;
                case 'custom-blocks-export':
                    $add_pages[] = array('name' => __('How to export and import settings and blocks', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/setup/import-export/', 'color' => '');
                    break;
                case 'custom-blocks-license':
                    $add_pages[] = array('name' => __('How to Activate Licenses', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/setup/license-activate/', 'color' => '');
                    $add_pages[] = array('name' => __('What to do if your license key is lost', 'custom-blocks-free'), 'link' => 'https://easier.press/docs/troubles/license-lost/', 'color' => '');
                    break;
            }
        }
        ?>
        <div class="block_permament_cb_help" style="">
            <a style="color: #666666;" target="_blank" href="https://easier.press/docs/"><?= __('Documentation', 'custom-blocks-free'); ?></a>
            <a style="color: #666666;" target="_blank" href="https://easier.press/support/"><?= __('Technical Support', 'custom-blocks-free'); ?></a>
            <span style="color: #999999; font-style: italic;">&copy; <a style="color: #999999;" target="_blank"  href="https://easier.press/"><?= __('Easier Press', 'custom-blocks-free'); ?></a></span>
        </div>
        <?PHP if ($support_links) : ?>
            <div class="block_dynamic_cb_help" style="">
                <?PHP if ($add_pages) : ?><ul>
                    <?PHP foreach ($add_pages as $ap) : ?>
                        <li><a style="color: #<?= ($ap['color']) ? $ap['color'] : '999999'; ?>" target="_blank" href="<?= $ap['link']; ?>"><?= $ap['name']; ?></a></li>
                    <?PHP endforeach; ?></ul>
                <?PHP endif; ?>
            </div>
        <?PHP endif; ?>
        <style>
            .block_permament_cb_help{
                display: inline; font-size: 12px; font-family: sans-serif; font-weight: normal; float: right;
            }
            .block_dynamic_cb_help {
                display: block; font-size: 12px; font-family: sans-serif; font-weight: normal;
            }
            .block_dynamic_cb_help ul {
                display: block;
                margin: 0;
                padding: 15px;
                line-height: 180%;
                background-color: #e9e9e9;
            }
            .block_dynamic_cb_help ul li {
                display: block;
                margin: 0;
                padding: 0;
            }
            .block_dynamic_cb_help ul li a, .block_dynamic_cb_help ul li a:link, .block_dynamic_cb_help ul li a:active, .block_dynamic_cb_help ul li a:visited {
                text-decoration: underline;
            }
            .block_dynamic_cb_help ul li a:hover {
                text-decoration: none;
            }
        </style>
        <?PHP
    }

    /**
     * Get selected posts for code
     * @global object $wpdb
     * @param int $allow
     * @param int $code_id
     * @return array
     */
    static function get_selected_posts_for_code($allow, $code_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "custom_block_item_rules";
        $sql = "SELECT post_id as posts FROM $table_name WHERE allow=" . $allow . " AND post_id IS NOT NULL AND item_id = " . $code_id;
        $query = $wpdb->get_results($sql);
        $ids = array();
        if ($query) {
            foreach ($query as $value) {
                $ids[] = $value->posts;
            }
        }
        return $ids;
    }

    /**
     * Get selected posts categories for code
     * @global object $wpdb
     * @param int $allow
     * @param int $code_id
     * @return array
     */
    static function get_selected_posts_categories_for_code($allow, $code_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . "custom_block_item_rules";
        $sql = "SELECT category_id as categories FROM $table_name WHERE allow=$allow AND category_id IS NOT NULL AND item_id = " . $code_id;
        $query = $wpdb->get_results($sql);
        $ids = array();
        if ($query) {
            foreach ($query as $value) {
                $ids[] = $value->categories;
            }
        }
        return $ids;
    }

    /**
     * Get all posts
     * @param int $allow
     * @return array
     */
    static public function get_all_posts($allow, $code_id = null) {
        $result = $selectedPosts = array();
        $args = array(
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish'
        );
        if ($code_id) {
            $selectedPosts = self::get_selected_posts_for_code($allow, $code_id);
            if ($selectedPosts) {
                $args['include'] = implode(',', $selectedPosts);
            } else {
                return $result;
            }
        }
        $posts = get_posts($args);
        if ($posts) {
            foreach ($posts as $post) {
                $result[$post->ID] = array(
                    'post_id' => $post->ID,
                    'post_title' => $post->post_title,
                    'selected' => (array_search($post->ID, $selectedPosts) !== false) ? true : false
                );
            }
        }
        return $result;
    }

    /**
     * Get all posts categories
     * @return array
     */
    static public function get_all_posts_categories($allow, $code_id = null) {
        $result = $selectedCategories = array();
        $args = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'hide_empty' => 1,
            'taxonomy' => 'category',
        );
        if ($code_id) {
            $selectedCategories = self::get_selected_posts_categories_for_code($allow, $code_id);
            if ($selectedCategories) {
                $args['include'] = implode(',', $selectedCategories);
            } else {
                return $result;
            }
        }
        $categories = get_categories($args);
        if ($categories) {
            foreach ($categories as $category) {
                $result[$category->term_id] = array(
                    'category_id' => $category->term_id,
                    'category_title' => $category->name,
                    'selected' => (array_search($category->term_id, $selectedCategories) !== false) ? true : false
                );
            }
        }
        return $result;
    }

    static function get_tax($allow, $code_id = null) {
        global $wpdb;
        $allows = (int) $allow;
        $code_ids = (int) $code_id;
        $selected_taxonomy = $wpdb->get_col('SELECT taxonomy_id FROM ' . $wpdb->prefix . 'custom_block_item_rules WHERE item_id=' . $code_ids . ' AND allow=' . $allows . ' AND taxonomy_id IS NOT NULL;');
        $result = array();
        foreach (get_taxonomies(array('_builtin' => false), 'objects') as $tax_key => $tax_val) {
            $result[$tax_key] = array(
                'tax_id' => $tax_key,
                'tax_title' => $tax_val->labels->name,
                'selected' => (array_search($tax_key, $selected_taxonomy) !== false) ? true : false
            );
        }
        return $result;
    }

    static function get_tterm($allow, $code_id = null) {
        global $wpdb;
        $allows = (int) $allow;
        $code_ids = (int) $code_id;
        $selected_taxonomy = $wpdb->get_col('SELECT taxonomy_id FROM ' . $wpdb->prefix . 'custom_block_item_rules WHERE item_id=' . $code_ids . ' AND allow=' . $allows . ' AND taxonomy_id IS NOT NULL;');
        $selected_terms = $wpdb->get_col('SELECT term_id FROM ' . $wpdb->prefix . 'custom_block_item_rules WHERE item_id=' . $code_ids . ' AND allow=' . $allows . ' AND term_id IS NOT NULL;');
        $result = array();
        if ($selected_taxonomy) {
            $args = array(
                'taxonomy' => $selected_taxonomy,
                'orderby' => 'name',
                'order' => 'ASC',
                'hide_empty' => false,
                'fields' => 'all',
                'count' => false,
                'slug' => '',
                'parent' => '',
                'hierarchical' => false,
            );

            $terms = get_terms($args);
            if ($terms) {
                foreach ($terms as $term) {
                    $result[$term->term_id] = array(
                        'term_id' => $term->term_id,
                        'term_title' => $term->name,
                        'selected' => (array_search($term->term_id, $selected_terms) !== false) ? true : false
                    );
                }
            }
        }

        return $result;
    }

    public function ajax_check_url_templates() {
        global $wpdb;
        $black_list = '';
        $error_urls = array();
        if (isset($_POST['blacklist']) && $_POST['blacklist']) {
            $black_list = $_POST['blacklist'];
        }
        if (isset($_POST['type']) && $_POST['type']) {
            $type = sanitize_text_field($_POST['type']);
        } else {
            $type = 'record';
        }
        $aliases = array();
        $add_list = array();
        if ($type == 'record') {
            $aliases = $wpdb->get_col("SELECT post_name FROM " . $wpdb->prefix . "posts WHERE post_status='publish'");
        }
        if ($type == 'cat') {
            $aliases = $wpdb->get_col("SELECT slug FROM " . $wpdb->prefix . "terms");
        }
        if (isset($_POST['urls']) && $_POST['urls']) {
            $urls = explode("\n", $_POST['urls']);
            if ($urls) {
                foreach ($urls as $url) {
                    if (trim($url)) {
                        $tmp_item = $this->checking_url_items($aliases, sanitize_text_field($url), $type);
                        $add_item_check = false;
                        if ($tmp_item) {
                            if (is_array($black_list) && $black_list) {
                                $adding = true;
                                foreach ($black_list as $bl) {
                                    if ($type == 'record' && $tmp_item['ID'] == $bl) {
                                        $adding = false;
                                    }

                                    if ($type == 'cat' && $tmp_item['term_id'] == $bl) {
                                        $adding = false;
                                    }
                                }
                                if ($adding) {
                                    $add_list[] = $tmp_item;
                                    $add_item_check = true;
                                }
                            } else {
                                $add_list[] = $tmp_item;
                                $add_item_check = true;
                            }
                            if ($type == 'record') {
                                $black_list[] = $tmp_item['ID'];
                            }
                            if ($type == 'cat') {
                                $black_list[] = $tmp_item['term_id'];
                            }
                            if (!$add_item_check) {
                                $error_urls[] = $url;
                            }
                        } else {
                            $error_urls[] = $url;
                        }
                    }
                }
            }
        }
        echo json_encode(array('list' => $add_list, 'error' => $error_urls));
        exit();
    }

    function checking_url_items($aliases, $url, $type) {
        global $wpdb;
        $site_url = get_option('siteurl');
        $curr_url = trim(str_replace($site_url, '', $url));
        $aliases_list = array();
        if ($curr_url) {
            foreach ($aliases as $alias) {
                if (strpos($curr_url, $alias) !== false) {
                    $aliases_list[] = array(
                        'alias' => $alias,
                        'ver' => (strpos($curr_url, $alias) + 1) * strlen($alias)
                    );
                }
            }
        }
        $max_ver = 0;
        $ver = '';
        if ($aliases_list) {
            foreach ($aliases_list as $list) {
                if ($list['ver'] > $max_ver) {
                    $max_ver = $list['ver'];
                    $ver = $list['alias'];
                }
            }
        }
        if ($type == 'cat') {
            return $wpdb->get_row('SELECT term_id,name FROM ' . $wpdb->prefix . 'terms WHERE slug="' . $ver . '"', ARRAY_A);
        }
        if ($type = 'record') {
            return $wpdb->get_row('SELECT ID,post_title FROM ' . $wpdb->prefix . 'posts WHERE post_name="' . $ver . '"', ARRAY_A);
        }
    }

    public function checking_groups_templates() {
        global $wpdb;
        $geo_keys = array(
            'geo_country_access',
            'geo_city_access',
            'geo_country_ban',
            'geo_city_ban',
        );
        $content_keys = array(
            'content_by_category',
            'content_by_post',
            'content_by_category_ban',
            'content_by_post_ban',
        );
        $time_keys = array(
            'time_start_timetargeting',
            'time_end_timetargeting',
            'start_date_timetargeting',
            'end_date_timetargeting',
            'only_work_time',
            'all_hours',
            'only_holiday_time',
        );

        $error = array();
        $success = __('No contradiction detected', 'custom-blocks-free');
        if (isset($_POST['templates']) && $_POST['templates']) {
            $templates_numbers = $_POST['templates'];
            //getting all information
            $block = array();
            foreach ($templates_numbers as $tmp_num) {
                $tmp_number=(int)$tmp_num;
                $tmp_res = $wpdb->get_results('SELECT meta_key,meta_value FROM ' . $wpdb->prefix . 'custom_block_template_meta WHERE template_id=' . $tmp_num);
                if ($tmp_res) {
                    $block[(int) $tmp_num] = $tmp_res;
                }
            }
            $sum_block = array();

            foreach ($block as $_block) {
                //block geo
                if ($this->handler_templates_array($_block, 'geo', true)) {
                    foreach ($geo_keys as $gk) {
                        if (isset($sum_block[$gk]) && $sum_block[$gk]) {
                            $sum_block[$gk] = array_unique(array_merge($sum_block[$gk], $this->handler_templates_array($_block, $gk)));
                        } else {
                            $sum_block[$gk] = array_unique($this->handler_templates_array($_block, $gk));
                        }
                    }
                    //analyze summary_block
                    if ($this->check_conflict($sum_block, 'geo_country_access', 'geo_country_ban')) {
                        $error[] = __('Contradiction detected in the "Country" field', 'custom-blocks-free');
                    }
                    if ($this->check_conflict($sum_block, 'geo_city_access', 'geo_city_ban')) {
                        $error[] = __('Contradiction detected in the "City" field', 'custom-blocks-free');
                    }
                }
                //block content
                if ($this->handler_templates_array($_block, 'content', true)) {
                    foreach ($content_keys as $ck) {
                        if (isset($sum_block[$ck]) && $sum_block[$ck]) {
                            $sum_block[$ck] = array_unique(array_merge($sum_block[$ck], $this->handler_templates_array($_block, $ck)));
                        } else {
                            $sum_block[$ck] = array_unique($this->handler_templates_array($_block, $ck));
                        }
                    }
                    //analyze summary_block
                    if ($this->check_conflict($sum_block, 'content_by_category', 'content_by_category_ban')) {
                        $error[] = __('Contradiction detected in the "Category" field', 'custom-blocks-free');
                    }
                    if ($this->check_conflict($sum_block, 'content_by_post', 'content_by_post_ban')) {
                        $error[] = __('Contradiction detected in the "Post" field', 'custom-blocks-free');
                    }
                }
                //block time
                if ($this->handler_templates_array($_block, 'time', true)) {
//            'start_date_timetargeting',
//            'end_date_timetargeting',
//            
//            'only_work_time',
//            'only_holiday_time',
                    foreach ($time_keys as $tk) {
                        switch ($tk) {
                            case 'start_date_timetargeting':
                                if (isset($sum_block[$tk]) && $sum_block[$tk]) {
                                    $tmp_date = $this->handler_templates_array($_block, $tk, true);
                                    if ($tmp_date) {
                                        $sum_block[$tk] = array_unique(array_merge($sum_block[$tk], array(date_create_from_format('d.m.Y', $tmp_date)->format('Y-m-d'))));
                                    }
                                } else {
                                    $tmp_date = $this->handler_templates_array($_block, $tk, true);
                                    if ($tmp_date) {
                                        $sum_block[$tk] = array_unique(array(date_create_from_format('d.m.Y', $tmp_date)->format('Y-m-d')));
                                    }
                                }
                                break;
                            case 'end_date_timetargeting':
                                if (isset($sum_block[$tk]) && $sum_block[$tk]) {
                                    $tmp_date = $this->handler_templates_array($_block, $tk, true);
                                    if ($tmp_date) {
                                        $sum_block[$tk] = array_unique(array_merge($sum_block[$tk], array(date_create_from_format('d.m.Y', $tmp_date)->format('Y-m-d'))));
                                    }
                                } else {
                                    $tmp_date = $this->handler_templates_array($_block, $tk, true);
                                    if ($tmp_date) {
                                        $sum_block[$tk] = array_unique(array(date_create_from_format('d.m.Y', $tmp_date)->format('Y-m-d')));
                                    }
                                }
                                break;
//                            case 'time_start_timetargeting':
//                                 if (isset($sum_block[$tk]) && $sum_block[$tk]) {
//                                     var_dump($this->handler_templates_array($_block, $tk,true));
//                                     die();
//                                 }
//                                break;
//                            case 'time_end_timetargeting':
//                                 if (isset($sum_block[$tk]) && $sum_block[$tk]) {
//                                     var_dump($this->handler_templates_array($_block, $tk,true));
//                                     die();
//                                 }
//                                break;
                            default:
                                if (isset($sum_block[$tk]) && $sum_block[$tk]) {
                                    $sum_block[$tk] = array_unique(array_merge($sum_block[$tk], array($this->handler_templates_array($_block, $tk, true))));
                                } else {
                                    $sum_block[$tk] = array_unique(array($this->handler_templates_array($_block, $tk, true)));
                                }
                                break;
                        }
                    }
                    //time
                    //date
                    //day of week
                    //all_time
                    if (isset($sum_block['time_start_timetargeting']) && $sum_block['time_start_timetargeting'] && isset($sum_block['time_end_timetargeting']) && $sum_block['time_end_timetargeting'] && (max($sum_block['time_start_timetargeting']) >= min($sum_block['time_end_timetargeting']))) {
                        $error[] = __('Contradiction detected in the "Time" field, the start time is later than the end time', 'custom-blocks-free');
                    }
                    if (isset($sum_block['start_date_timetargeting']) && $sum_block['start_date_timetargeting'] && isset($sum_block['end_date_timetargeting']) && $sum_block['end_date_timetargeting'] && (max($sum_block['start_date_timetargeting']) >= min($sum_block['end_date_timetargeting']))) {
                        $error[] = __('Contradiction detected in the "Date" field, the start date is later than the end date', 'custom-blocks-free');
                    }
                }
            }

//var_dump($sum_block);
//            die();
//            echo json_encode($block);
//            die();
        } else {
            $success = '';
        }

        if ($error) {
            echo json_encode(array('status' => 'error', 'error' => '<u>' . __('Fix the following errors', 'custom-blocks-free') . ':</u><br><br><li>' . implode('</li><li>', array_unique($error)) . '</li>'));
        } else {
            echo json_encode(array('status' => 'success', 'success' => $success));
        }
        die();
    }

    function clear_statistic_block() {
        global $wpdb;
        $id = 0;
        if (isset($_POST['codeid']) && $_POST['codeid']) {
            $id = (int)$_POST['codeid'];
        }
        echo $wpdb->update($wpdb->prefix . 'custom_block_item', array('show' => 0, 'click' => 0, 'show_index' => 0), array('id' => $id));
        exit();
    }

    function check_conflict($array, $key1, $key2) {
        if (isset($array[$key1]) && $array[$key1] && isset($array[$key2]) && $array[$key2] && array_intersect($array[$key1], $array[$key2])) {
            return true;
        }
        return false;
    }

    function handler_templates_array($array, $name_key, $only_value = null) {
        $return = array();
        if ($array) {
            foreach ($array as $element) {
                if ($element->meta_key == $name_key) {
                    $return[] = $element->meta_value;
                }
            }
        }
        if (count($return) < 2 && $only_value) {
            $return = current($return);
        }
        return $return;
    }

    static public function get_posting() {
        global $wpdb;
        if (isset($_REQUEST['search'])) {
            $search = sanitize_text_field($_REQUEST['search']);
        } else {
            exit;
        }

        if (isset($_REQUEST['type_block'])) {
            $type = sanitize_text_field($_REQUEST['type_block']);
        } else {
            exit;
        }

        switch ($type) {
            case '1':
                $query = $wpdb->get_results("SELECT `term_id`,`name` FROM `" . $wpdb->prefix . "terms` WHERE name LIKE '%" . $search . "%' AND term_id IN (SELECT term_id FROM " . $wpdb->prefix . "term_taxonomy WHERE taxonomy='category');");
                $result = array();
                if ($query) {
                    foreach ($query as $category) {
                        $result[$category->term_id] = $category->name;
                    }
                }
                echo json_encode($result);
                break;
            case '2':
                $search_query = 'SELECT ID FROM ' . $wpdb->prefix . 'posts WHERE post_type = "post" AND post_title LIKE "%' . $search . '%"';
                $results = $wpdb->get_col($search_query);
                foreach ($results as $array) {
                    $quote_ids[] = $array;
                }
                $result = array();
                if ($quote_ids) {
                    $quotes = get_posts(array('post_type' => 'post', 'orderby' => 'title', 'order' => 'ASC', 'post__in' => $quote_ids, 'post_status' => 'publish', 'numberposts' => -1));
                    if ($quotes) {
                        foreach ($quotes as $quote) {
                            $result[$quote->ID] = $quote->post_title;
                        }
                    }
                }
                echo json_encode($result);
                break;
        }
        exit;
    }

    static public function get_decor_template() {
        global $wpdb;
        $code_id = (int)$_REQUEST['ids'];
        $table = $wpdb->prefix . 'custom_block_decor_link';
        $ids_decor = $wpdb->get_col("SELECT DISTINCT id_decor FROM " . $table . " WHERE active='1'");
        if (!$ids_decor) {
            exit;
        }
        //getting id if setting
        $setting_id = $wpdb->get_var('SELECT id_decor FROM ' . $table . ' WHERE id_item=' . $code_id);
        $table = $wpdb->prefix . 'custom_block_decor';
        $template = $wpdb->get_results("SELECT id,name FROM " . $table . " WHERE id IN (" . implode(',', $ids_decor) . ");", ARRAY_A);
        if (!$template) {
            exit;
        }
        echo '<select name="codes[' . $code_id . '][decors][template]" rel="' . $code_id . '">';
        echo '<option value="">' . __('Do not use', 'custom-blocks-free') . '</option>';
        foreach ($template as $elem) {
            $checked = '';
            if ($setting_id == $elem['id']) {
                $checked = 'selected';
            }
            echo '<option value="' . $elem['id'] . '" ' . $checked . '>' . $elem['name'] . '</option>';
        }
        echo '</select>';
        exit;
    }

    static public function get_time_targeting($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'custom_block_time';
        $query = $wpdb->get_row("SELECT * FROM " . $table . " WHERE id_item=" . $id, ARRAY_A);
        $array = array();
        $array['status'] = (isset($query['status']) && $query['status']) ? true : false;
        $array['time_start'] = (isset($query['time_start']) && $query['time_start']) ? date_create_from_format('H:i:s', $query['time_start'])->format('H:i') : '';
        $array['time_end'] = (isset($query['time_end']) && $query['time_end']) ? date_create_from_format('H:i:s', $query['time_end'])->format('H:i') : '';
        $array['show_date_start'] = (isset($query['show_date_start']) && $query['show_date_start']) ? date_create_from_format('Y-m-d', $query['show_date_start'])->format('d.m.Y') : '';
        $array['show_date_end'] = (isset($query['show_date_end']) && $query['show_date_end']) ? date_create_from_format('Y-m-d', $query['show_date_end'])->format('d.m.Y') : '';
        $array['all_hours'] = (isset($query['all_hours']) && $query['all_hours']) ? true : false;
        $array['only_work_day'] = (isset($query['only_work_day']) && $query['only_work_day']) ? true : false;
        $array['only_holiday'] = (isset($query['only_holiday']) && $query['only_holiday']) ? true : false;
        return $array;
    }

    static public function get_all_country($ids = array()) {
        global $wpdb;
        $array_result = array();
        if (count($ids)) {
            $query = $wpdb->get_results("SELECT id,name_ru FROM " . $wpdb->prefix . "geoip_sxgeo_country WHERE id IN (" . implode(',', $ids) . ") order by name_ru;");
            if ($query) {
                foreach ($query as $value) {
                    $array_result[$value->id]['id'] = $value->id;
                    $array_result[$value->id]['name'] = $value->name_ru;
                }
            }
        } else {
            $query = $wpdb->get_results("SELECT id,name_ru FROM " . $wpdb->prefix . "geoip_sxgeo_country order by name_ru");
            if ($query) {
                foreach ($query as $value) {
                    $array_result[$value->id]['id'] = $value->id;
                    $array_result[$value->id]['name'] = $value->name_ru;
                }
            }
        }

        return $array_result;
    }

    static public function get_all_city($allow = null) {
        global $wpdb;
        if (is_array($_POST['ids']) and count($_POST['ids']) > 0) {
            $countrys_id_array = array_diff($_POST['ids'], array(''));
            if (!count($countrys_id_array)) {
                exit;
            }
            $t_countrys_id_array=array();
            foreach ($countrys_id_array as $_tmp)
            {
                $t_countrys_id_array[]=(int)$_tmp;
            }
            $countrys_id_array=$t_countrys_id_array;
            $sql = "SELECT " . $wpdb->prefix . "geoip_sxgeo_regions.id FROM " . $wpdb->prefix . "geoip_sxgeo_country LEFT JOIN " . $wpdb->prefix . "geoip_sxgeo_regions ON " . $wpdb->prefix . "geoip_sxgeo_regions.country=" . $wpdb->prefix . "geoip_sxgeo_country.iso WHERE " . $wpdb->prefix . "geoip_sxgeo_country.id in (" . implode(',', $countrys_id_array) . ");";
            $res = $wpdb->get_results($sql);
            foreach ($res as $value) {
                $region_id[] = $value->id;
            }
            if (isset($region_id) and count($region_id)) {
                $sql = "SELECT " . $wpdb->prefix . "geoip_sxgeo_cities.id," . $wpdb->prefix . "geoip_sxgeo_cities.name_ru FROM " . $wpdb->prefix . "geoip_sxgeo_regions LEFT JOIN " . $wpdb->prefix . "geoip_sxgeo_cities ON " . $wpdb->prefix . "geoip_sxgeo_regions.id=" . $wpdb->prefix . "geoip_sxgeo_cities.region_id WHERE " . $wpdb->prefix . "geoip_sxgeo_regions.id IN (" . implode(',', $region_id) . ") ORDER BY " . $wpdb->prefix . "geoip_sxgeo_cities.name_ru ;";
                $res = $wpdb->get_results($sql, ARRAY_A);
                $result = array();
                foreach ($res as $key => $value) {
                    $result[$key]['name'] = $value['name_ru'];
                    $result[$key]['id'] = $value['id'];
                }
                echo json_encode($result);
            }
        }
        exit;
    }

    static public function geting_geo() {
        global $wpdb;
        $table_country = $wpdb->prefix . "geoip_sxgeo_country";
        $table_region = $wpdb->prefix . "geoip_sxgeo_regions";
        $table_city = $wpdb->prefix . "geoip_sxgeo_cities";
        if (isset($_REQUEST['search'])) {
            $search = sanitize_text_field($_REQUEST['search']);
        } else {
            exit;
        }

        if (isset($_REQUEST['type_block'])) {
            $type = sanitize_text_field($_REQUEST['type_block']);
        } else {
            exit;
        }

        switch ($type) {
            case '1':
                //country
                $query = $wpdb->get_results("SELECT id,name_ru FROM " . $table_country . " WHERE name_ru LIKE '%" . $search . "%' order by name_ru;", ARRAY_A);
                if ($query) {
                    $result = array();
                    foreach ($query as $query_item) {
                        $result[(int) $query_item['id']] = $query_item['name_ru'];
                    }
                    echo json_encode($result);
                }
                break;
            case '2':
                //city
                if (isset($_REQUEST['country'])) {
                    $country = $_REQUEST['country'];
                    $query = $wpdb->get_col("SELECT `iso` FROM " . $table_country . " WHERE id IN (" . implode(',', $country) . ");");
                    $query = $wpdb->get_col("SELECT `id` FROM " . $table_region . " WHERE country IN ('" . implode("','", $query) . "');");
                    $query = $wpdb->get_results("SELECT `id`,`name_ru` FROM " . $table_city . " WHERE region_id IN (" . implode(',', $query) . ") AND name_ru LIKE '%" . $search . "%';", ARRAY_A);
                    if ($query) {
                        $result = array();
                        foreach ($query as $query_item) {
                            $result[(int) $query_item['id']] = $query_item['name_ru'];
                        }
                        echo json_encode($result);
                    }
                }
                break;
        }
        exit;
    }

    static public function get_country_from_city($city_id) {
        global $wpdb;
        $sql = "SELECT " . $wpdb->prefix . "geoip_sxgeo_cities.region_id FROM " . $wpdb->prefix . "geoip_sxgeo_cities WHERE " . $wpdb->prefix . "geoip_sxgeo_cities.id=" . (int) $city_id;
        $region_id = $wpdb->get_var($sql);
        $sql = "SELECT " . $wpdb->prefix . "geoip_sxgeo_country.id FROM " . $wpdb->prefix . "geoip_sxgeo_regions LEFT JOIN " . $wpdb->prefix . "geoip_sxgeo_country ON " . $wpdb->prefix . "geoip_sxgeo_country.iso=" . $wpdb->prefix . "geoip_sxgeo_regions.country WHERE " . $wpdb->prefix . "geoip_sxgeo_regions.id=" . (int) $region_id;
        return $wpdb->get_var($sql);
    }

    static public function get_country($id, $allow) {
        global $wpdb;
        $country = array();
        $sql = "SELECT country_id FROM `" . $wpdb->prefix . "custom_block_geoip` WHERE `item_id` = '" . $id . "' and `allow`=" . $allow;
        $rows = $wpdb->get_results($sql);
        $selected = array();
        if (is_array($rows) and count($rows)) {
            foreach ($rows as $value) {
                $selected[$value->country_id] = $value->country_id;
            }
        }

        if ($selected) {
            $country = self::get_all_country($selected);
            foreach ($selected as $value) {
                $country[$value]['selected'] = true;
            }
        }
        return $country;
    }

    static public function get_city($id, $allow) {
        global $wpdb;
        $sql = "SELECT country_id,city_id FROM `" . $wpdb->prefix . "custom_block_geoip` WHERE `item_id` = '" . $id . "' and `allow`=" . $allow;
        $rows = $wpdb->get_results($sql);
        $selected_country = array();
        $selected_city = array();
        $result = array();
        if ($rows) {
            foreach ($rows as $value) {
                $selected_country[] = $value->country_id;
                if ($value->city_id) {
                    $selected_city[] = $value->city_id;
                }
            }
        }
        if ($selected_country) {
            $sql = "SELECT " . $wpdb->prefix . "geoip_sxgeo_regions.id FROM " . $wpdb->prefix . "geoip_sxgeo_country LEFT JOIN " . $wpdb->prefix . "geoip_sxgeo_regions ON " . $wpdb->prefix . "geoip_sxgeo_regions.country=" . $wpdb->prefix . "geoip_sxgeo_country.iso WHERE " . $wpdb->prefix . "geoip_sxgeo_country.id in (" . implode(',', $selected_country) . ");";
            $res = $wpdb->get_results($sql);
            foreach ($res as $value) {
                $region_id[] = $value->id;
            }
            if (isset($region_id) and count($region_id)) {
                if ($selected_city) {
                    $sql = "SELECT id,name_ru FROM " . $wpdb->prefix . "geoip_sxgeo_cities WHERE id IN (" . implode(',', $selected_city) . ") order by name_ru;";
                } else {
                    $sql = "SELECT " . $wpdb->prefix . "geoip_sxgeo_cities.id," . $wpdb->prefix . "geoip_sxgeo_cities.name_ru FROM " . $wpdb->prefix . "geoip_sxgeo_regions LEFT JOIN " . $wpdb->prefix . "geoip_sxgeo_cities ON " . $wpdb->prefix . "geoip_sxgeo_regions.id=" . $wpdb->prefix . "geoip_sxgeo_cities.region_id WHERE " . $wpdb->prefix . "geoip_sxgeo_regions.id IN (" . implode(',', $region_id) . ") ORDER BY " . $wpdb->prefix . "geoip_sxgeo_cities.name_ru ;";
                }
                $res = $wpdb->get_results($sql, ARRAY_A);
                $result = array();
                foreach ($res as $key => $value) {
                    $result[$key]['name'] = $value['name_ru'];
                    $result[$key]['id'] = $value['id'];
                    if (in_array($value['id'], $selected_city)) {
                        $result[$key]['selected'] = true;
                    }
                }
            }
        }
        return $result;
    }

    public function item_state_ajax_action() {
        $item = CustomBlockItem::findById((int)$_POST['id']);
        $item->published = (int) $_POST['published'];
        $item->save();
    }

    public function shortcode_handler($params) {
        $post_id = get_the_ID();
        if (!$post_id) {
            $post_id = 0;
        }

        $disabled = get_post_meta($post_id, 'cb_disable', true);
        if ($disabled == 'on') {
            return "";
        }
        if (!isset($params['id']))
            return "";
        $id = (int) $params['id'];
        $block = CustomBlock::findById($id);
        if (!$block)
            return "";
        if ($block->sync == '0') {
            $container_id = rand(1, 9999) . rand(1, 9999);
            $func_name = (get_option('cb_functionname')) ? get_option('cb_functionname') : 'custom_block';
            return '<script id="custom-block-' . $container_id . '" type="text/javascript">' . $func_name . '(' . $id . ', ' . $container_id . ', ' . $post_id . ');</script>';
        } else {
            if (!$block->published)
                return '';
            if (isset($_COOKIE['wordpress_custom_setting'])) {
                $rotation = $_COOKIE['wordpress_custom_setting'];
            } else {
                $rotation = null;
            }
            $item = $block->rollItem(1, $post_id, $rotation, CustomBlocksPlugin::get_php_time_client(), false);
            if ($item) {
                $item->show = $item->show + 1;
                $item->show_index = $item->show_index + 1;
                $item->save();
                $code_html = $item->html;
                if (get_option('cb_expertmode')) {
                    ob_start();
                    eval('?>' . $code_html);
                    $code_html = ob_get_contents();
                    ob_end_clean();
                }
                $return = "<div class='" . CustomBlocksPlugin::get_class_name() . "' rel='" . $item->id . "'>" . $code_html . "</div>" . CustomBlocksPlugin::get_styles($item->id);
                return $return;
            } else {
                return '';
            }
        }
    }

    static public function get_class_name() {
        if (get_option('cb_functionname')) {
            $class_name = get_option('cb_functionname');
        } else {
            $class_name = 'custom-block';
        }
        return $class_name;
    }

    static public function get_php_time_client() {
        global $wpdb;
        if (isset($_COOKIE['city_id']) && (int) $_COOKIE['city_id'] <> 0) {
            $city_id = (int) $_COOKIE['city_id'];
            if ($city_id == 0) {
                return null;
            }
            $region_id = $wpdb->get_var("SELECT region_id FROM " . $wpdb->prefix . "geoip_sxgeo_cities WHERE id=" . $city_id);
            if (!$region_id) {
                return null;
            }
            $timezone = $wpdb->get_var("SELECT timezone FROM " . $wpdb->prefix . "geoip_sxgeo_regions WHERE id=" . $region_id);
            if (!$timezone) {
                return null;
            }
            date_default_timezone_set($timezone);
            return date("H:i");
        }
        return null;
    }

    static public function edit_action_show_ad($id) {
        if (isset($_POST["cb_edit"]) && !empty($_POST["cb_edit"])) {
            $cb_disable = (isset($_POST["cb_disable"])) ? sanitize_text_field($_POST["cb_disable"]) : null;
            delete_post_meta((int)$id, 'cb_disable');
            if (isset($cb_disable) && !empty($cb_disable)) {
                add_post_meta((int)$id, 'cb_disable', $cb_disable);
            }
        }
    }

    function edit_form_action_show_ad() {
        global $post;
        $post_id = $post;
        if (is_object($post_id)) {
            $post_id = $post_id->ID;
        }
        $cb_check = get_post_meta($post_id, 'cb_disable', true);
        ?>
        <input value="cb_edit" type="hidden" name="cb_edit" />
        <table style="margin-bottom:40px; margin-top:30px;">
            <tr>
                <th scope="row" style="text-align:right; vertical-align:top;">
                    <?= __('Disable advertisement', 'custom-blocks-free'); ?>
                </th>
                <td>
                    <input type="checkbox" name="cb_disable" <?php if ($cb_check) echo "checked=\"1\""; ?>/>
                </td>
            </tr>
        </table>
        <?php
    }

    static public function set_calendar() {
        $calendar_json = '{"data":{"2003":{"1":{"1":{"isWorking":2},"2":{"isWorking":2},"3":{"isWorking":2},"4":{"isWorking":0},"5":{"isWorking":3},"6":{"isWorking":2},"7":{"isWorking":2}},"2":{"24":{"isWorking":2}},"3":{"7":{"isWorking":3},"10":{"isWorking":2}},"4":{"30":{"isWorking":3}},"5":{"1":{"isWorking":2},"2":{"isWorking":2},"8":{"isWorking":3},"9":{"isWorking":2}},"6":{"11":{"isWorking":3},"12":{"isWorking":2},"13":{"isWorking":2},"21":{"isWorking":0}},"11":{"6":{"isWorking":3},"7":{"isWorking":2}},"12":{"11":{"isWorking":3},"12":{"isWorking":2},"31":{"isWorking":3}}},"2004":{"1":{"1":{"isWorking":2},"2":{"isWorking":2},"6":{"isWorking":3},"7":{"isWorking":2}},"2":{"23":{"isWorking":2}},"3":{"8":{"isWorking":2}},"4":{"30":{"isWorking":3}},"5":{"3":{"isWorking":2},"4":{"isWorking":2},"10":{"isWorking":2}},"6":{"11":{"isWorking":3},"14":{"isWorking":2}},"11":{"8":{"isWorking":2}},"12":{"13":{"isWorking":2},"31":{"isWorking":3}}},"2005":{"1":{"3":{"isWorking":2},"4":{"isWorking":2},"5":{"isWorking":2},"6":{"isWorking":2},"7":{"isWorking":2},"10":{"isWorking":2}},"2":{"22":{"isWorking":3},"23":{"isWorking":2}},"3":{"5":{"isWorking":3},"7":{"isWorking":2},"8":{"isWorking":2}},"5":{"2":{"isWorking":2},"9":{"isWorking":2}},"6":{"13":{"isWorking":2}},"11":{"3":{"isWorking":3},"4":{"isWorking":2}},"12":{"12":{"isWorking":2}}},"2006":{"1":{"2":{"isWorking":2},"3":{"isWorking":2},"4":{"isWorking":2},"5":{"isWorking":2},"6":{"isWorking":2},"9":{"isWorking":2}},"2":{"22":{"isWorking":3},"23":{"isWorking":2},"24":{"isWorking":2},"26":{"isWorking":0}},"3":{"7":{"isWorking":3},"8":{"isWorking":2}},"5":{"1":{"isWorking":2},"6":{"isWorking":3},"8":{"isWorking":2},"9":{"isWorking":2}},"6":{"12":{"isWorking":2}},"11":{"3":{"isWorking":3},"6":{"isWorking":2}}},"2007":{"1":{"1":{"isWorking":2},"2":{"isWorking":2},"3":{"isWorking":2},"4":{"isWorking":2},"5":{"isWorking":2},"8":{"isWorking":2}},"2":{"22":{"isWorking":3},"23":{"isWorking":2}},"3":{"7":{"isWorking":3},"8":{"isWorking":2}},"4":{"28":{"isWorking":3},"30":{"isWorking":2}},"5":{"1":{"isWorking":2},"8":{"isWorking":3},"9":{"isWorking":2}},"6":{"9":{"isWorking":3},"11":{"isWorking":2},"12":{"isWorking":2}},"11":{"5":{"isWorking":2}},"12":{"29":{"isWorking":3},"31":{"isWorking":2}}},"2008":{"1":{"1":{"isWorking":2},"2":{"isWorking":2},"3":{"isWorking":2},"4":{"isWorking":2},"7":{"isWorking":2},"8":{"isWorking":2}},"2":{"22":{"isWorking":3},"25":{"isWorking":2}},"3":{"7":{"isWorking":3},"10":{"isWorking":2}},"4":{"30":{"isWorking":3}},"5":{"1":{"isWorking":2},"2":{"isWorking":2},"4":{"isWorking":0},"8":{"isWorking":3},"9":{"isWorking":2}},"6":{"7":{"isWorking":0},"11":{"isWorking":3},"12":{"isWorking":2},"13":{"isWorking":2}},"11":{"1":{"isWorking":3},"3":{"isWorking":2},"4":{"isWorking":2}},"12":{"31":{"isWorking":3}}},"2009":{"1":{"1":{"isWorking":2},"2":{"isWorking":2},"5":{"isWorking":2},"6":{"isWorking":2},"7":{"isWorking":2},"8":{"isWorking":2},"9":{"isWorking":2},"11":{"isWorking":0}},"2":{"23":{"isWorking":2}},"3":{"9":{"isWorking":2}},"4":{"30":{"isWorking":3}},"5":{"1":{"isWorking":2},"8":{"isWorking":3},"11":{"isWorking":2}},"6":{"11":{"isWorking":3},"12":{"isWorking":2}},"11":{"3":{"isWorking":3},"4":{"isWorking":2}},"12":{"31":{"isWorking":3}}},"2010":{"1":{"1":{"isWorking":2},"4":{"isWorking":2},"5":{"isWorking":2},"6":{"isWorking":2},"7":{"isWorking":2},"8":{"isWorking":2}},"2":{"22":{"isWorking":2},"23":{"isWorking":2},"27":{"isWorking":3}},"3":{"8":{"isWorking":2}},"4":{"30":{"isWorking":3}},"5":{"3":{"isWorking":2},"10":{"isWorking":2}},"6":{"11":{"isWorking":3},"14":{"isWorking":2}},"11":{"3":{"isWorking":3},"4":{"isWorking":2},"5":{"isWorking":2},"13":{"isWorking":0}},"12":{"31":{"isWorking":3}}},"2011":{"1":{"3":{"isWorking":2},"4":{"isWorking":2},"5":{"isWorking":2},"6":{"isWorking":2},"7":{"isWorking":2},"10":{"isWorking":2}},"2":{"22":{"isWorking":3},"23":{"isWorking":2}},"3":{"5":{"isWorking":3},"7":{"isWorking":2},"8":{"isWorking":2}},"5":{"2":{"isWorking":2},"9":{"isWorking":2}},"6":{"13":{"isWorking":2}},"11":{"3":{"isWorking":3},"4":{"isWorking":2}}},"2012":{"1":{"2":{"isWorking":2},"3":{"isWorking":2},"4":{"isWorking":2},"5":{"isWorking":2},"6":{"isWorking":2},"9":{"isWorking":2}},"2":{"22":{"isWorking":3},"23":{"isWorking":2}},"3":{"7":{"isWorking":3},"8":{"isWorking":2},"9":{"isWorking":2},"11":{"isWorking":0}},"4":{"28":{"isWorking":3},"30":{"isWorking":2}},"5":{"1":{"isWorking":2},"5":{"isWorking":0},"7":{"isWorking":2},"8":{"isWorking":2},"9":{"isWorking":2},"12":{"isWorking":3}},"6":{"9":{"isWorking":3},"11":{"isWorking":2},"12":{"isWorking":2}},"11":{"5":{"isWorking":2}},"12":{"29":{"isWorking":3},"31":{"isWorking":2}}},"2013":{"1":{"1":{"isWorking":2},"2":{"isWorking":2},"3":{"isWorking":2},"4":{"isWorking":2},"7":{"isWorking":2},"8":{"isWorking":2}},"2":{"22":{"isWorking":3}},"3":{"7":{"isWorking":3},"8":{"isWorking":2}},"4":{"30":{"isWorking":3}},"5":{"1":{"isWorking":2},"2":{"isWorking":2},"3":{"isWorking":2},"8":{"isWorking":3},"9":{"isWorking":2},"10":{"isWorking":2}},"6":{"11":{"isWorking":3},"12":{"isWorking":2}},"11":{"4":{"isWorking":2}},"12":{"31":{"isWorking":3}}},"2014":{"1":{"1":{"isWorking":2},"2":{"isWorking":2},"3":{"isWorking":2},"6":{"isWorking":2},"7":{"isWorking":2},"8":{"isWorking":2}},"2":{"24":{"isWorking":3}},"3":{"7":{"isWorking":3},"10":{"isWorking":2}},"4":{"30":{"isWorking":3}},"5":{"1":{"isWorking":2},"2":{"isWorking":2},"8":{"isWorking":3},"9":{"isWorking":2}},"6":{"11":{"isWorking":3},"12":{"isWorking":2},"13":{"isWorking":2}},"11":{"3":{"isWorking":2},"4":{"isWorking":2}},"12":{"31":{"isWorking":3}}},"2015":{"1":{"1":{"isWorking":2},"2":{"isWorking":2},"5":{"isWorking":2},"6":{"isWorking":2},"7":{"isWorking":2},"8":{"isWorking":2},"9":{"isWorking":2}},"2":{"20":{"isWorking":3},"23":{"isWorking":2}},"3":{"6":{"isWorking":3},"9":{"isWorking":2}},"4":{"30":{"isWorking":3}},"5":{"1":{"isWorking":2},"4":{"isWorking":2},"8":{"isWorking":3},"11":{"isWorking":2}},"6":{"11":{"isWorking":3},"12":{"isWorking":2}},"11":{"3":{"isWorking":3},"4":{"isWorking":2}},"12":{"31":{"isWorking":3}}},"2016":{"1":{"1":{"isWorking":2},"4":{"isWorking":2},"5":{"isWorking":2},"6":{"isWorking":2},"7":{"isWorking":2},"8":{"isWorking":2}},"2":{"20":{"isWorking":3},"22":{"isWorking":2},"23":{"isWorking":2}},"3":{"7":{"isWorking":2},"8":{"isWorking":2}},"5":{"2":{"isWorking":2},"3":{"isWorking":2},"9":{"isWorking":2}},"6":{"13":{"isWorking":2}},"11":{"3":{"isWorking":3},"4":{"isWorking":2}}}}}';
        $calendar_array = json_decode($calendar_json);
        $now_year = (int) date('Y');
        $calendar = array();
        foreach ($calendar_array->data as $year => $month) {
            if ((int) $year >= $now_year) {
                $calendar[$year] = $month;
            }
        }
        update_option('cb_calendar', $calendar);
    }

    static public function activation_hook() {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        if (!is_admin()) {
            return;
        }
        global $wpdb;
        $tables_exists = $wpdb->get_col('show tables');
        $need_tables = array(
            $wpdb->prefix . "custom_block",
            $wpdb->prefix . "custom_block_item",
            $wpdb->prefix . "custom_block_item_rules",
            $wpdb->prefix . "custom_block_geoip",
            $wpdb->prefix . "custom_block_resolution",
            $wpdb->prefix . "custom_block_resolution_type",
            $wpdb->prefix . "custom_block_time",
            $wpdb->prefix . "custom_block_decor",
            $wpdb->prefix . "custom_block_decor_link",
            $wpdb->prefix . "custom_block_template",
            $wpdb->prefix . "custom_block_template_meta",
        );

        $need_upgrade = false;
        foreach ($need_tables as $nt) {
            if (!in_array($nt, $tables_exists)) {
                $need_upgrade = true;
            }
        }
        if ($need_upgrade == false) {
            return;
        }

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        CustomBlocksPlugin::set_calendar();
        $table_name = $wpdb->prefix . "custom_block";
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `title` varchar(254) NOT NULL,
                    `published` tinyint(1) NOT NULL,
                    `sync` TINYINT(1) NULL DEFAULT '0',
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
            $wpdb->query($sql);
        } else {
            if (!$wpdb->query($wpdb->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='%s' AND COLUMN_NAME='sync'", $table_name))) {
                $sql = "ALTER TABLE `" . $table_name . "` ADD COLUMN `sync` TINYINT(1) NULL DEFAULT '0' AFTER `published`;";
                $wpdb->query($sql);
            }
        }

        $table_name = $wpdb->prefix . "custom_block_item";
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                      `block_id` int(11) NOT NULL,
                      `type_id` int(11) NOT NULL,
                      `title` varchar(254) NOT NULL,
                      `html` text NOT NULL,
                      `show` int(11) NOT NULL,
                      `click` int(11) NOT NULL,
                      `published` tinyint(1) NOT NULL,
                      `geotargeting` tinyint(1) NULL,
                      `content_filter` tinyint(1) NULL,
                      `subhead` tinyint(1) NULL,
                      `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      `show_index` INT(11) UNSIGNED NOT NULL DEFAULT '0',
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $wpdb->query($sql);
        } else {
            foreach (array('filter_by_post_id', 'filter_by_category_id') as $col) {
                if ($wpdb->get_var("show columns FROM `" . $table_name . "` where `Field` = '" . $col . "'")) {
                    $sql = "ALTER TABLE `" . $table_name . "` DROP COLUMN " . $col;
                    $wpdb->query($sql);
                }
            }
            foreach (array('geotargeting', 'content_filter', 'subhead') as $value) {
                if (!$wpdb->get_var("show columns FROM `" . $table_name . "` where `Field` = '" . $value . "'")) {
                    $sql = "ALTER TABLE `" . $table_name . "` ADD `" . $value . "` tinyint(1) NULL";
                    $wpdb->query($sql);
                }
            }
            $value = 'show_index';
            if (!$wpdb->get_var("show columns FROM `" . $table_name . "` where `Field` = '" . $value . "'")) {
                $sql = "ALTER TABLE `" . $table_name . "` ADD COLUMN `" . $value . "` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`";
                $wpdb->query($sql);
            }
        }

        $table_name = $wpdb->prefix . "custom_block_item_rules";
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `item_id` bigint(20) NOT NULL,
              `allow` tinyint(1) NOT NULL,
              `category_id` int(11) DEFAULT NULL,
              `post_id` int(11) DEFAULT NULL,
              `taxonomy_id` TEXT DEFAULT NULL,
              `term_id` INT(11) DEFAULT NULL,
              PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $wpdb->query($sql);
        } else {
            if (!$wpdb->query($wpdb->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='%s' AND COLUMN_NAME='term_id'", $table_name))) {
                $sql = "ALTER TABLE `" . $table_name . "` ADD COLUMN `taxonomy_id` TEXT NULL DEFAULT NULL AFTER `post_id`,	ADD COLUMN `term_id` INT(11) NULL DEFAULT NULL AFTER `taxonomy_id`;";
                $wpdb->query($sql);
            }
        }

        $table_name = $wpdb->prefix . "custom_block_geoip";
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `item_id` bigint(20) NOT NULL,
                        `allow` tinyint(1) NOT NULL,
                        `country_id` int(11) NOT NULL,
                        `city_id` int(11) DEFAULT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $wpdb->query($sql);
        }

        $table_name = $wpdb->prefix . "custom_block_resolution";
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `resolution_id` int(11) NOT NULL,
                        `block_id` int(11) NOT NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $wpdb->query($sql);
        }

        $table_name = $wpdb->prefix . "custom_block_resolution_type";
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `width` int(11) NOT NULL,
                        `width_stop` int(11) NULL,
                        PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT = 3;";
            $wpdb->query($sql);
            //add custom resolution
        } else {
            $sql = "SHOW COLUMNS FROM " . $table_name . " WHERE Field='height'";
            $query = $wpdb->get_results($sql);
            if ($wpdb->num_rows > 0) {
                $sql = "ALTER TABLE `" . $table_name . "`
                        ADD COLUMN `width_stop` INT(11) NULL AFTER `width`,
                        DROP COLUMN `height`;";
                $wpdb->query($sql);
            }
        }
        $sql = "SELECT count(*) as counts FROM " . $table_name;
        if ($wpdb->get_var($sql) == 0) {
            $resolution = array(
                array('width' => 0, 'width_stop' => 319),
                array('width' => 320, 'width_stop' => 766),
                array('width' => 767, 'width_stop' => 999),
                array('width' => 1000),
            );
            foreach ($resolution as $resoult) {
                $wpdb->insert($table_name, $resoult, array('%d', '%d'));
            }
        }
        $table_name = $wpdb->prefix . "custom_block_time";
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE `$table_name` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `id_item` INT NULL DEFAULT NULL,
                    `status` TINYINT(1) NULL DEFAULT NULL,
                    `time_start` TIME NULL DEFAULT NULL,
                    `time_end` TIME NULL DEFAULT NULL,
                    `all_hours` TINYINT(1) NULL DEFAULT NULL,
                    `only_work_day` TINYINT(1) NULL DEFAULT NULL,
                    `only_holiday` TINYINT(1) NULL DEFAULT NULL,
                    `show_date_start` DATE NULL DEFAULT NULL,
                    `show_date_end` DATE NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $wpdb->query($sql);
        }
        $table_name = $wpdb->prefix . "custom_block_decor";
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE `$table_name` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(256) NULL DEFAULT NULL,
                    `property` TEXT NULL DEFAULT NULL,
                    INDEX `id` (`id`),
                    PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $wpdb->query($sql);
        }
        $table_name = $wpdb->prefix . "custom_block_decor_link";
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE `$table_name` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `id_decor` INT NULL DEFAULT NULL,
                    `id_item` INT NULL DEFAULT NULL,
                    `active` TINYINT(1) NULL DEFAULT NULL,
                    INDEX `id` (`id`),
                    PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $wpdb->query($sql);
        }
        //templates
        $table_name = $wpdb->prefix . "custom_block_template";
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE `$table_name` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(512) NOT NULL DEFAULT 'Untitled',
                    PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $wpdb->query($sql);
        }
        //templates_meta
        $table_name = $wpdb->prefix . "custom_block_template_meta";
        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
            $sql = "CREATE TABLE `$table_name` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `template_id` INT(11) UNSIGNED NOT NULL,
                    `meta_key` VARCHAR(256) NOT NULL,
                    `meta_value` VARCHAR(1024) NOT NULL,
                    PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $wpdb->query($sql);
        }
    }

    public function admin_head_action() {
        wp_enqueue_script("jquery-ui-core");
        wp_enqueue_script("jquery-ui-tabs");
        wp_enqueue_script('jquery-tmpl', plugins_url('/js/jquery.tmpl.js', __FILE__), array('jquery'));
        wp_enqueue_script('undo-redo', plugins_url('/js/undo-redo.js', __FILE__), array('jquery'));
        wp_enqueue_script("jquery-ui-timepicker", plugins_url('/js/jquery.ui.timepicker.js', __FILE__), array('jquery', 'jquery-ui-core'));
        wp_enqueue_style('jquery-ui-timepicker-css', plugins_url('/css/jquery.ui.timepicker.css', __FILE__));
        wp_enqueue_style('jquery-ui-tabs', plugins_url('/css/jquery-ui.min.css', __FILE__));
        wp_enqueue_script('chosen', plugins_url('/js/chosen/chosen.jquery.min.js', __FILE__), array('jquery'));
        wp_enqueue_style('chosen', plugins_url('/js/chosen/chosen.css', __FILE__));
        wp_enqueue_style('colorpicker', plugins_url('/css/colorpicker.css', __FILE__));
        wp_enqueue_script('colorpicker-custom', plugins_url('/js/colorpicker.js', __FILE__));
        wp_localize_script('chosen', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
//        wp_enqueue_script('custom-blocks', plugins_url('/js/admin.js', __FILE__), array('jquery'), '1.0.0');
        wp_enqueue_style('custom-blocks', plugins_url('/css/admin.css', __FILE__));
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
    }

    public function click_block_ajax_action() {
        $item = CustomBlockItem::findById((int)$_REQUEST['id']);
        if (!$item)
            wp_die();
        $item->click = $item->click + 1;
        $item->save();
    }

    public static function is_work_day() {
        $workday = date('w');
        $now_date = array(
            'y' => date('Y'),
            'm' => date('m'),
            'd' => date('d')
        );
        $cb_calendar = get_option('cb_calendar', true);
        if (!$cb_calendar) {
            $cb_calendar = get_option('cb_calendar', true);
        }
        //проверяем исключения
        if (isset($cb_calendar[$now_date['y']]->$now_date['m']->$now_date['d'])) {
            $exteption = $cb_calendar[$now_date['y']]->$now_date['m']->$now_date['d']->isWorking;
            switch ($exteption) {
                case '0':
                    return true;
                    break;
                case '2':
                    return false;
                    break;
            }
        }
        if ($workday <= 5) {
            return true;
        } else {
            return false;
        }
    }

    public static function check_time_targeting($ids, $time = null) {
        global $wpdb;
        $date_now = date("Y-m-d");
        $work = CustomBlocksPlugin::is_work_day();
        if (isset($ids) && $ids) {
            foreach ($ids as $key => $value) {
                $flag = true;
                $res = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "custom_block_time WHERE id_item=" . $value, ARRAY_A);
                if ($res && $res['status']) {
                    //check date
                    if ($res['show_date_start'] && ($res['show_date_start'] > $date_now)) {
                        $flag = false;
                    }
                    if ($res['show_date_end'] && ($res['show_date_end'] < $date_now)) {
                        $flag = false;
                    }
                    //check time
                    if ($time) {
                        if (!$res['all_hours']) {
                            if ($res['time_start'] && (date_create_from_format('H:i:s', $res['time_start']) > date_create_from_format('H:i', $time))) {
                                $flag = false;
                            }
                            if ($res['time_end'] && (date_create_from_format('H:i:s', $res['time_end']) < date_create_from_format('H:i', $time))) {
                                $flag = false;
                            }
                        }
                    }
                    if ($res['only_work_day'] && !$work) {
                        $flag = false;
                    }
                    if ($res['only_holiday'] && $work) {
                        $flag = false;
                    }
                    if (!$flag) {
                        unset($ids[$key]);
                    }
                }
                if ($flag) {
                    if ($templates = $wpdb->get_col("SELECT template_id FROM {$wpdb->prefix}custom_block_template_meta WHERE meta_key='include_block' AND meta_value='{$value}';")) {
                        if ((int) $wpdb->get_col("SELECT count(*) FROM {$wpdb->prefix}custom_block_template_meta WHERE template_id IN (" . implode(',', $templates) . ") AND meta_key='time' AND meta_value='1';")) {
                            $key_for_time = array(
                                'time_start_timetargeting',
                                'time_end_timetargeting',
                                'start_date_timetargeting',
                                'end_date_timetargeting',
                                'only_work_time',
                                'all_hours',
                                'only_holiday_time'
                            );
                            $template_times = $wpdb->get_results("SELECT meta_key,meta_value FROM {$wpdb->prefix}custom_block_template_meta WHERE template_id IN (" . implode(',', $templates) . ") AND meta_key IN ('" . implode("','", $key_for_time) . "');");
                            $res2 = array();
                            if ($template_times) {
                                if ($template_times) {
                                    foreach ($template_times as $obj_el) {
                                        switch ($obj_el->meta_key) {
                                            case 'start_date_timetargeting':
                                                $res2['show_date_start'] = $obj_el->meta_value;
                                                break;
                                            case 'end_date_timetargeting':
                                                $res2['show_date_end'] = $obj_el->meta_value;
                                                break;
                                            case 'all_hours':
                                                $res2['all_hours'] = $obj_el->meta_value;
                                                break;
                                            case 'time_start_timetargeting':
                                                $res2['time_start'] = $obj_el->meta_value;
                                                break;
                                            case 'time_end_timetargeting':
                                                $res2['time_end'] = $obj_el->meta_value;
                                                break;
                                            case 'only_work_time':
                                                $res2['only_work_day'] = $obj_el->meta_value;
                                                break;
                                            case 'only_holiday_time':
                                                $res2['only_holiday'] = $obj_el->meta_value;
                                                break;
                                        }
                                    }
                                }
                                //check date
                                if (isset($res2['show_date_start']) && $res2['show_date_start'] && (date_create_from_format('d.m.Y', $res2['show_date_start'])->format('Y-m-d') > $date_now)) {
                                    $flag = false;
                                }
                                if (isset($res2['show_date_end']) && $res2['show_date_end'] && (date_create_from_format('d.m.Y', $res2['show_date_end'])->format('Y-m-d') < $date_now)) {
                                    $flag = false;
                                }

                                //check time
                                if ($time) {
                                    if (!isset($res2['all_hours'])) {
                                        if (isset($res2['time_start']) && $res2['time_start'] && (date_create_from_format('H:i', $res2['time_start']) > date_create_from_format('H:i', $time))) {
                                            $flag = false;
                                        }
                                        if (isset($res2['time_end']) && $res2['time_end'] && (date_create_from_format('H:i', $res2['time_end']) < date_create_from_format('H:i', $time))) {
                                            $flag = false;
                                        }
                                    }
                                }
                                if (isset($res2['only_work_day']) && $res2['only_work_day'] && !$work) {
                                    $flag = false;
                                }
                                if (isset($res2['only_holiday']) && $res2['only_holiday'] && $work) {
                                    $flag = false;
                                }
                                if (!$flag) {
                                    unset($ids[$key]);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $ids = array();
        }
        return $ids;
    }

    public function get_ads_ajax_action_prestart() {
        if (isset($_REQUEST['chest']) && $_REQUEST['chest']) {
            $answer = array();
            foreach ($_REQUEST['chest'] as $block_key => $chest) {
                $_REQUEST['id'] = $block_key;
                $ads = $this->get_ads_ajax_action($_REQUEST);
                $counter = true;
                foreach ($chest as $container_chest) {
                    if (isset($ads['id'])) {
                        if ($counter) {
                            $style = CustomBlocksPlugin::get_styles($ads['id']);
                            $answer[$container_chest]['text'] = $ads['ads'] . $style;
                            $answer[$container_chest]['id'] = $ads['id'];
                            $counter = false;
                        } else {
                            $answer[$container_chest]['text'] = $ads['ads'];
                            $answer[$container_chest]['id'] = $ads['id'];
                        }
                    } else {
                        $answer[$container_chest]['text'] = '';
                        $answer[$container_chest]['id'] = '';
                    }
                }
            }
            echo json_encode((array) $answer);
        }
        exit;
    }

    public function get_ads_ajax_action($request) {
        global $wpdb;

        $block = CustomBlock::findById($request['id']);
        if (!$block or ! $block->published)
            return '';

        $post_id = (isset($request['post_id'])) ? (int) $request['post_id'] : 0;
        $time = (isset($request['time'])) ? $request['time'] : null;
        $reqWidth = (int) $request['width'];

        if ($request['block'] == "true") {
            $adblock = true;
            $type_id = 2;
        } else {
            $adblock = false;
            $widths = $wpdb->get_results("SELECT id,width,width_stop FROM " . $wpdb->prefix . "custom_block_resolution_type ORDER BY width", ARRAY_A);
            //take type_id and continute
            if (count($widths)) {
                foreach ($widths as $width) {
                    if ($width['width'] <> null && $width['width_stop'] <> null) {
                        if ($reqWidth >= $width['width'] && $reqWidth <= $width['width_stop']) {
                            $rule[1][] = $width;
                        }
                    } elseif ($width['width'] <> null) {
                        if ($reqWidth >= $width['width']) {
                            $rule[2][] = $width;
                        }
                    } elseif ($width['width_stop'] <> null) {
                        if ($reqWidth >= $width['width_stop']) {
                            $rule[2][] = $width;
                        }
                    }
                }
                if (isset($rule[1]) && count($rule[1])) {
                    if (count($rule[1]) == 1) {
                        $type_id = $rule[1][0]['id'];
                    } else {
                        $key_min = 0;
                        $value_min = 10000;
                        foreach ($rule[1] as $key_rule => $value_rule) {
                            if (($value_rule['width_stop'] - $value_rule['width']) <= $value_min) {
                                $value_min = $value_rule['width_stop'] - $value_rule['width'];
                                $key_min = $key_rule;
                            }
                        }
                        $type_id = $rule[1][$key_min]['id'];
                        $types[0] = $type_id;
                        unset($rule[1][$key_min]);
                        foreach ($rule[1] as $temp_sort) {
                            $types[] = $temp_sort['id'];
                        }
                    }
                } else if (isset($rule[2]) && count($rule[2])) {
                    if (count($rule[2]) == 1) {
                        $type_id = $rule[2][0]['id'];
                    } else {
                        $key_min = 0;
                        $value_min = 10000;
                        foreach ($rule[2] as $key_rule => $value_rule) {
                            if ($value_rule['width'] <= $value_min) {
                                $value_min = $value_rule['width'];
                                $key_min = $key_rule;
                            }
                        }
                        $type_id = $rule[2][$key_min]['id'];
                        if (!isset($types)) {
                            $types[0] = $type_id;
                            unset($rules[2][$key_min]);
                        }
                        foreach ($rule[2] as $temp_sort) {
                            $types[] = $temp_sort['id'];
                        }
                    }
                } else {
                    $type_id = 1;
                }
            } else {
                $type_id = 1;
            }
        }
        if (isset($_COOKIE['wordpress_custom_setting'])) {
            $rotation = $_COOKIE['wordpress_custom_setting'];
        } else {
            $rotation = null;
        }
        if (!isset($types)) {
            $item = $block->rollItem($type_id, $post_id, $rotation, $time, $adblock);
        } else {
            $item = $block->rollItem(array_reverse($types), $post_id, $rotation, $time, $adblock);
        }
        if (!$item)
            return '';
        $item->show_index = $item->show_index + 1;
        $item->show = $item->show + 1;
        $item->save();
        $result_string = '';
        $result_string.= "<div class='" . CustomBlocksPlugin::get_class_name() . "' rel='" . $item->id . "'>";
        $code_html = $item->html;
        if (get_option('cb_expertmode')) {
            ob_start();
            eval('?>' . $code_html);
            $code_html = ob_get_contents();
            ob_end_clean();
        }
        $result_string.=$code_html;
        $result_string.="</div>";
        return array('ads' => $result_string, 'id' => $item->id);
    }

    static function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = array($r, $g, $b);
        return implode(",", $rgb);
    }

    public static function get_styles($item_ads, $only_id_decor = null) {
        global $wpdb;
        if (!$only_id_decor) {
            $id_decor = $wpdb->get_var('SELECT id_decor FROM ' . $wpdb->prefix . 'custom_block_decor_link WHERE id_item=' . (int) $item_ads);
        } else {
            $id_decor = $only_id_decor;
        }
        if (!$id_decor) {
            return '';
        }
        $array_style = $wpdb->get_var('SELECT property FROM ' . $wpdb->prefix . 'custom_block_decor WHERE id=' . (int) $id_decor);
        if ($array_style) {
            $style = unserialize($array_style);
        } else {
            return '';
        }
        $prop = array();
        $prop_hover = array();
        $js_prop = array();
        //определяем стили 
        foreach ($style as $key => $value) {
            if ($value) {
                switch ($key) {
                    case 'width':
                        if (isset($value['value'])) {
                            $val_type = (isset($value['type'])) ? $value['type'] : 'px';
                            $prop[$key] = $value['value'] . $val_type;
                            $prop['max-' . $key] = $value['value'] . $val_type;
                        }
                        break;
                    case 'height':
                        if (isset($value['value'])) {
                            $val_type = (isset($value['type'])) ? $value['type'] : 'px';
                            $prop[$key] = $value['value'] . $val_type;
                        }
                        break;
                    case 'border':
                        if ($value['style'] == 'your') {
                            $border_style = $value['style_your'];
                        } else {
                            $border_style = $value['style'];
                        }
                        if (isset($value['width']) && isset($border_style) && isset($value['color'])) {
                            $prop[$key] = $value['width'] . 'px ' . $border_style . ' ' . $value['color'];
                        }
                        //radius
                        if (isset($value['radius']) && $value['radius']) {
                            if (isset($value['radius']['top']['left']) && $border_radius_now = $value['radius']['top']['left']) {
                                $prop['-webkit-border-top-left-radius'] = (int) $border_radius_now . 'px';
                                $prop['-moz-border-radius-topleft'] = (int) $border_radius_now . 'px';
                                $prop['border-top-left-radius'] = (int) $border_radius_now . 'px';
                            }
                            if (isset($value['radius']['top']['right']) && $border_radius_now = $value['radius']['top']['right']) {
                                $prop['-webkit-border-top-right-radius'] = (int) $border_radius_now . 'px';
                                $prop['-moz-border-radius-topright'] = (int) $border_radius_now . 'px';
                                $prop['border-top-right-radius'] = (int) $border_radius_now . 'px';
                            }
                            if (isset($value['radius']['down']['left']) && $border_radius_now = $value['radius']['down']['left']) {
                                $prop['-webkit-border-bottom-left-radius'] = (int) $border_radius_now . 'px';
                                $prop['-moz-border-radius-bottomleft'] = (int) $border_radius_now . 'px';
                                $prop['border-bottom-left-radius'] = (int) $border_radius_now . 'px';
                            }
                            if (isset($value['radius']['down']['right']) && $border_radius_now = $value['radius']['down']['right']) {
                                $prop['-webkit-border-bottom-right-radius'] = (int) $border_radius_now . 'px';
                                $prop['-moz-border-radius-bottomright'] = (int) $border_radius_now . 'px';
                                $prop['border-bottom-right-radius'] = (int) $border_radius_now . 'px';
                            }
                        }
                        break;
                    case 'margin':
                        foreach ($value as $margin_key => $margin_value) {
                            $prop[$key . '-' . $margin_key] = $margin_value . 'px';
                        }
                        break;
                    case 'padding':
                        foreach ($value as $margin_key => $margin_value) {
                            $prop[$key . '-' . $margin_key] = $margin_value . 'px';
                        }
                        break;
                    case 'background':
                        if (isset($value['color']) && $value['color']) {
                            if (isset($value['opacity']) && $value['opacity']) {
                                $prop['background-color'] = 'rgba(' . CustomBlocksPlugin::hex2rgb($value['color']) . ', ' . $value['opacity'] . ')';
                            } else {
                                $prop['background-color'] = $value['color'];
                            }
                        }
                        break;
                    case 'font':
                        if (isset($value['family'])) {
                            $prop['font-family'] = $value['family'];
                        }
                        if (isset($value['size'])) {
                            $prop['font-size'] = $value['size'] . 'px !important';
                        }
                        if (isset($value['color'])) {
                            $prop['color'] = $value['color'];
                        }
                        if (isset($value['weight'])) {
                            $prop['font-weight'] = 'bold';
                        }
                        if (isset($value['style'])) {
                            $prop['font-style'] = 'italic';
                        }
                        break;
                    case 'letter-spacing':
                        if ($value['type']) {
                            if (strlen($value['type']) > 2) {
                                $prop['letter-spacing'] = $value['type'];
                            } elseif (isset($value['value'])) {
                                $prop['letter-spacing'] = $value['value'] . $value['type'];
                            }
                        }
                        break;
                    case 'text-transform':
                        $prop[$key] = $value;
                        break;
                    case 'line-height':
                        $prop[$key] = $value . '%';
                        break;
                    case 'spec':
                        foreach ($value as $key_spec => $value_spec) {
                            if ($key_spec == 'time') {
                                foreach (array('-webkit-transition', '-moz-transition', '-o-transition', 'transition') as $spec_type_tran) {
                                    $prop[$spec_type_tran] = $value_spec['value'] . $value_spec['type'] . ' linear';
                                }
                            } elseif ((isset($value_spec['status']) && ($value_spec['status'] == 'on'))) {
                                switch ($key_spec) {
                                    case 'width':
                                        if (!isset($prop['max-width'])) {
                                            $tmp = (int) $value_spec['value'] + 100;
                                            $prop['max-width'] = $tmp . 'px';
                                        }
                                        if (isset($value_spec['value']) && $value_spec['type']) {
                                            $prop_hover['max-width'] = $value_spec['value'] . $value_spec['type'];
                                        }
                                        break;
                                    case 'opacity':
                                        if (!isset($prop['opacity'])) {
                                            $prop['opacity'] = '1';
                                        }
                                        $prop_hover['opacity'] = $value_spec['value'];
                                        break;
                                    case 'rotate':
                                        $prop['position'] = 'relative';
                                        $prop['z-index'] = '10';
                                        $prop_hover['-webkit-transform'] = 'rotate(' . (int) $value_spec['value'] . 'deg)';
                                        $prop_hover['transform'] = 'rotate(' . (int) $value_spec['value'] . 'deg)';
                                        break;
                                    case 'scale':
                                        $prop['position'] = 'relative';
                                        $prop['z-index'] = '10';
                                        $prop_hover['-webkit-transform'] = 'scale(' . $value_spec['value'] . ')';
                                        $prop_hover['transform'] = 'rotate(' . $value_spec['value'] . ')';
                                        break;
                                    case 'top':
                                        $prop['position'] = 'relative';
                                        $prop['z-index'] = '10';
                                        $prop['top'] = '0';
                                        $prop_hover['top'] = (int) $value_spec['value'] . 'px';
                                        break;
                                }
                            }
                        }
                        break;
                    case 'js':
                        foreach ($value as $js_key => $js_code) {
                            if ($js_code) {
                                switch ($js_key) {
                                    case 'x':
                                        $js_prop[$js_key] = $js_code;
                                        break;
                                    case 'y':
                                        $js_prop[$js_key] = $js_code;
                                        break;
                                    case 'rotation':
                                        $js_prop[$js_key] = $js_code;
                                        break;
                                    case 'speed':
                                        $js_prop[$js_key] = $js_code;
                                        break;
                                    case 'opacity':
                                        if ($js_code == 'coffee') {
                                            $js_prop['x'] = 6;
                                            $js_prop['y'] = 6;
                                            $js_prop['rotation'] = 6;
                                            $js_prop['speed'] = 5;
                                            $js_prop['opacity'] = true;
                                            $js_prop['opacityMin'] = '.05';
                                        } else {
                                            $js_prop[$js_key] = $js_code;
                                        }
                                        break;
                                    case 'opacity_value':
                                        if ($js_prop['opacity'] == 'number') {
                                            $js_prop['opacity'] = 'true';
                                            $js_prop['opacityMin'] = $js_code;
                                        }
                                        break;
                                    case 'type_var':
                                        $js_prop[$js_key] = $js_code;
                                }
                            }
                        }
                        break;
                }
            }
        }
        $return = '';
        if ($only_id_decor) {
            $selector = 'div.admin-block[rel="' . $only_id_decor . '"]';
        } else {
            $selector = 'div.' . CustomBlocksPlugin::get_class_name() . '[rel="' . $item_ads . '"]';
        }

        $to_style = array();
        foreach ($prop as $styles => $value) {
            $to_style[] = $styles . ': ' . $value . '';
        }
        $result = implode('; ', $to_style);
        $to_style = array();
        foreach ($prop_hover as $styles => $value) {
            $to_style[] = $styles . ': ' . $value . '';
        }
        $result_prop = implode('; ', $to_style);
        $just_style = '';
        if ($result) {
            $just_style.=$selector . ' {' . $result . ' } ';
        }
        if ($result_prop) {
            $just_style.=$selector . ':hover {' . $result_prop . ' } ';
        }
        if ($just_style) {
            $return.='<style> ' . $just_style . ' </style>';
        }

        if ($js_prop) {
            $js_inp = array();
            $access_js = array('x', 'y', 'rotation', 'opacity', 'opacityMin');
            foreach ($js_prop as $key_js => $value_js) {
                if (in_array($key_js, $access_js)) {
                    $js_inp[] = $key_js . ': ' . $value_js;
                }
            }
            $js_input = '<script>';
            $js_input.="jQuery(document).ready(function(){ jQuery('" . $selector . "').jrumble(); jQuery('" . $selector . "').jrumble({ ";
            $js_input.=implode(', ', $js_inp);
            $js_input.="}); ";
            //add action
            $js_action = "jQuery('" . $selector . "').hover(function(){ jQuery(this).trigger('startRumble');}, function(){ jQuery(this).trigger('stopRumble'); }); ";
            if (isset($js_prop['type_var'])) {
                switch ($js_prop['type_var']) {
                    case 'click_stop':
                        $js_action = "jQuery('" . $selector . "').toggle(function(){ jQuery(this).trigger('startRumble'); }, function(){ jQuery(this).trigger('stopRumble'); });";
                        break;
                    case 'click_activate':
                        $js_action = "jQuery('" . $selector . "').bind({ 'mousedown': function(){ jQuery(this).trigger('startRumble'); }, 'mouseup': function(){ jQuery(this).trigger('stopRumble'); }});";
                        break;
                    case 'non_stop':
                        $js_action = "jQuery('" . $selector . "').trigger('startRumble');";
                        break;
                    case 'click_activate_time':
                        $thising = '$this';
                        $js_action = "var demoTimeout; jQuery('" . $selector . "').click(function() { " . $thising . " = jQuery(this); clearTimeout(demoTimeout); " . $thising . ".trigger('startRumble'); demoTimeout = setTimeout(function(){ " . $thising . ".trigger('stopRumble');}, 1500) });";
                        break;
                    case 'pulse':
                        $js_action = "var demoStart = function(){ jQuery('" . $selector . "').trigger('startRumble'); setTimeout(demoStop, 300); };	
                                    var demoStop = function(){ jQuery('" . $selector . "').trigger('stopRumble'); setTimeout(demoStart, 300); };	demoStart();";
                        break;
                }
            }
            $js_input.= $js_action . " });";
            $js_input.='</script>';
            $return.=$js_input;
        }
        return $return;
    }

    public function wp_enqueue_scripts_action() {
        wp_enqueue_style('custom-blocks-client', plugins_url('/css/client.css', __FILE__));
        wp_enqueue_style('custom-blocks-reveals-css', plugins_url('/css/reveal.css', __FILE__));
        wp_enqueue_style('custom-blocks-filps', plugins_url('/css/jquery.m.flip.css', __FILE__));
        wp_enqueue_script('custom-blocks-ads', plugins_url('/js/ads.js', __FILE__), array(), "1.0.0", false);
        wp_enqueue_script('jquery');
        wp_enqueue_script('custom-blocks-cookies-js', plugins_url('/js/jquery.cookie.js', __FILE__), array(), "1.0.0", false);
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('custom-blocks-flipper', plugins_url('/js/jquery.m.flip.js', __FILE__), array(), "1.0.0", false);
        if (get_option('cb_functionname') && get_option('cb_functionname') <> 'custom_block') {
            ?><script>function <?= get_option('cb_functionname', 'custom_block'); ?>(id, container_id, post) {
                    custom_block(id, container_id, post);
                }
                ;</script><?PHP
        }
        wp_enqueue_script('custom-blocks-reveal-js', plugins_url('/js/jquery.plainmodal.min.js', __FILE__), array(), "1.0.0", false);
        wp_enqueue_script('custom-blocks-client-js', plugins_url('/js/client.js', __FILE__), array(), "1.0.1", false);
        wp_localize_script('custom-blocks-client-js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'class_block' => (get_option('cb_functionname')) ? get_option('cb_functionname') : 'custom-block'));
//        wp_enqueue_script('custom-blocks-reveal-js', plugins_url('/js/jquery.reveal.js', __FILE__), array(), "1.0.0", false);
        wp_enqueue_script('custom-blocks-jrumble-js', plugins_url('/js/jquery.jrumble.1.3.min.js', __FILE__), array(), "1.0.0", false);
    }

    /**
     * Добавляем пункты меню в админке
     */
    public function admin_menu_action() {
        add_menu_page(__('Custom Advert Blocks Plugin', 'custom-blocks-free'), __('Custom Blocks', 'custom-blocks-free'), 'manage_options', 'custom-blocks', array($this, 'admin_page_blocks'), plugins_url( 'images/logo_cb.svg', __FILE__ ));
        $pages = array();
        $pages[] = add_submenu_page('custom-blocks', __('Output Settings', 'custom-blocks-free'), __('Output Settings', 'custom-blocks-free'), 'manage_options', 'custom-blocks-settings', array($this, 'admin_page_settings'));
        $pages[] = add_submenu_page('custom-blocks', __('Statistics', 'custom-blocks-free'), __('Statistics', 'custom-blocks-free'), 'manage_options', 'custom-blocks-statistics', array($this, 'admin_page_statistics'));

        $pages[] = add_submenu_page('custom-blocks', __('Targeting Templates', 'custom-blocks-free'), __('Targeting Templates', 'custom-blocks-free'), 'manage_options', 'custom-blocks-template-filter', array($this, 'promo_page'));
        $pages[] = add_submenu_page('custom-blocks', __('Screen Resolutions', 'custom-blocks-free'), __('Screen Resolutions', 'custom-blocks-free'), 'manage_options', 'custom-blocks-resolution', array($this, 'promo_page'));
        $pages[] = add_submenu_page('custom-blocks', __('Style Templates', 'custom-blocks-free'), __('Style Templates', 'custom-blocks-free'), 'manage_options', 'custom-blocks-decor', array($this, 'promo_page'));
        $pages[] = add_submenu_page('custom-blocks', __('Upload GEO-Data', 'custom-blocks-free'), __('Upload GEO-Data', 'custom-blocks-free'), 'manage_options', 'custom-blocks-download', array($this, 'promo_page'));

        $pages[] = add_submenu_page('custom-blocks', __('Export/Import', 'custom-blocks-free'), __('Export/Import', 'custom-blocks-free'), 'manage_options', 'custom-blocks-export', array($this, 'admin_page_export'));
        $pages[] = add_submenu_page('custom-blocks', __('Go Premium', 'custom-blocks-free'), __('Go Premium', 'custom-blocks-free') . '<img class="star_img_cb" src="'.plugins_url( 'images/star_cb.svg', __FILE__ ).'">', 'manage_options', 'custom-blocks-tariff', array($this, 'promo_page'));
        foreach ($pages as $page) {
            add_action('load-' . $page, array($this, 'load_admin_js'));
        }
    }

    public function promo_page() {
        wp_enqueue_script('jquery');
        include_once 'admin_pages/template_promo.php';
    }

    public function load_admin_js() {
        add_action('admin_enqueue_scripts', array($this, 'admin_head_action'));
    }

    public function admin_page_resolution() {
        global $wpdb;
        $table = $wpdb->prefix . 'custom_block_resolution_type';
        $table2 = $wpdb->prefix . 'custom_block_item';
        if (isset($_POST['add_resolution'])) {
            $flag = 0;
            if (isset($_POST['width'])) {
                $flag++;
            }
            if (isset($_POST['width_stop'])) {
                $flag++;
            }
            if ($flag == 2) {
                $wpdb->insert(
                        $table, array('width' => (int)$_POST['width'],
                    'width_stop' => (int)$_POST['width_stop']), array(
                    '%d',
                    '%d'
                        )
                );
            }
        }

        if (isset($_POST['delete_resolution'])) {
            if (isset($_POST['id'])) {
                $wpdb->delete($table, array('id' => (int)$_POST['id']));
                $wpdb->delete($table2, array('type_id' => (int)$_POST['id']));
            }
        }

        if (isset($_POST['change']) && isset($_POST['change_id'])) {
            $data = array(
                'width' => (int)$_POST['width'],
                'width_stop' => (int)$_POST['width_stop']
            );
            $wpdb->update($table, $data, array('id' => (int)$_POST['change_id']));
        }

        if (isset($_POST['change_resolution']) && isset($_POST['id'])) {
            $results = $wpdb->get_row("SELECT * FROM " . $table . " WHERE id='" . (int) $_POST['id'] . "';");
            ?>
            <h3><?= __('Edit Screen Resolution', 'custom-blocks-free'); ?></h3>
            <hr/>
            <form method="post" action="">
                <input name="change_id" type="hidden" value="<?= (int)$_POST['id']; ?>">
                <table class="form-table">
                    <tr>
                        <th scope="row"><strong><?= __('Width from:', 'custom-blocks-free'); ?></strong></th>
                        <td>
                            <input name="width" required type="number" min="0" value="<?= $results->width; ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><strong><?= __('Width to:', 'custom-blocks-free'); ?></strong></th>
                        <td>
                            <input name="width_stop" required type="number" min="0" value="<?= $results->width_stop; ?>">
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Save changes', 'custom-blocks-free'), "primary", 'change'); ?>
            </form>
            <?PHP
        } else {
            ?>
            <h3><?= __('Screen Resolution', 'custom-blocks-free'); ?></h3>
            <hr/>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row"><strong><?= __('Width from:', 'custom-blocks-free'); ?></strong></th>
                        <td>
                            <input name="width" required type="number" min="1">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><strong><?= __('Width to:', 'custom-blocks-free'); ?></strong></th>
                        <td>
                            <input name="width_stop" required type="number" min="1">
                        </td>
                    </tr>
                </table>
                <?php submit_button(__('Add', 'custom-blocks-free'), "primary", 'add_resolution'); ?>
            </form>
            <hr/>
            <?PHP
            $resolutions = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "custom_block_resolution_type");
            echo '<table>';
            foreach ($resolutions as $value) {
                ?>
                <form method="post" action="">
                    <div class="resolut_text"><?= __('from', 'custom-blocks-free') . ' ' . $value->width; ?><?= ($value->width_stop) ? ' ' . __('to', 'custom-blocks-free') . ' ' . $value->width_stop : ''; ?></div>
                    <?php submit_button(__('Edit Resolution', 'custom-blocks-free'), "small", 'change_resolution', false); ?>
                    <input type="text" hidden="hidden" value="<?= $value->id; ?>" name="id">
                    <?php submit_button(__('Delete Resolution', 'custom-blocks-free'), "small", 'delete_resolution', false); ?><br>
                </form>
                <?PHP
            }
            echo '</table>*' . __('All blocks related to the resolution will be also deleted.', 'custom-blocks-free');
        }
    }

    public function admin_page_export() {
        global $wpdb;
        $tables = array(
            "custom_block",
            "custom_block_geoip",
            "custom_block_item",
            "custom_block_item_rules",
            "custom_block_resolution",
            "custom_block_resolution_type",
            "custom_block_decor",
            "custom_block_decor_link",
            "custom_block_time",
        );
        switch (@$_REQUEST['action']) {
            case "export":
                $plugin_data = get_plugin_data(__FILE__);
                $data = array(
                    "version" => $plugin_data['Version'],
                    "cb_show_for" => get_option("cb_show_for"),
                    "cb_blocks" => get_option("cb_blocks"),
                );
                foreach ($tables as $table) {
                    $data[$table] = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}{$table}", ARRAY_A);
                }
                header('Content-disposition: attachment; filename=custom_blocks.json');
                header('Content-type: application/json');
                echo json_encode($data);
                die();
                break;
            case "import":
                if (!isset($_FILES['file']))
                    break;
                $flag = false;
                $params = json_decode(file_get_contents($_FILES['file']['tmp_name']), true);
                switch ($params['version']) {
                    case "0.6.7":
                    case "0.6.8":
                    default:
                        foreach ($tables as $table) {
                            foreach ($params[$table] as $line) {
                                if ($line) {
                                    $flag = true;
                                }
                            }
                        }
                }
                if ($flag) {
                    //check file for record if isset 1 and more - truncate, record  from import
                    update_option('cb_show_for', $params['cb_show_for']);
                    update_option('cb_blocks', $params['cb_blocks']);

                    foreach ($tables as $table) {
                        $wpdb->query("TRUNCATE {$wpdb->prefix}{$table}");
                    }
                    $count_records = 0;
                    switch ($params['version']) {
                        case "0.6.7":
                        case "0.6.8":
                        default:
                            foreach ($tables as $table) {
                                foreach ($params[$table] as $line) {
                                    $r = $wpdb->insert($wpdb->prefix . $table, $line);
                                    $count_records++;
                                }
                            }
                    }
                    echo '<h4>' . __('The Database is successfully imported. New lines in DB', 'custom-blocks-free') . ': ' . $count_records . '.</h4>';
                } else {
                    echo '<h4>' . __('The file is empty or has wrong extension.', 'custom-blocks-free') . '</h4>';
                }
                break;
        }
        ?>
        <div class="wrap">
            <h2><?= __('Custom Blocks', 'custom-blocks-free'); ?><?PHP $this->get_links_for_admin(); ?></h2>
            <h3><?= __('Export', 'custom-blocks-free'); ?></h3>
            <p><?= __('Click', 'custom-blocks-free'); ?> <a href="/wp-admin/admin.php?page=custom-blocks-export&action=export&noheader=true"><?= __('here', 'custom-blocks-free'); ?></a> <?= __('to download the file', 'custom-blocks-free'); ?></p>
            <h3><?= __('Import', 'custom-blocks-free'); ?></h3>
            <p><?= __('Attention! All current settings and advertising blocks will be deleted!', 'custom-blocks-free'); ?><br> <?= __('File in
                "<b>.json</b>", exported earlier.', 'custom-blocks-free'); ?>
            <form action="/wp-admin/admin.php?page=custom-blocks-export&action=import" enctype="multipart/form-data"
                  method="post">
                <input type="file" name="file" accept="application/json">
                <input type="submit" value="<?= __('Upload', 'custom-blocks-free'); ?>">
            </form>
        </p>
        <?PHP $this->get_links_for_admin(false); ?>
        </div>
        <?php
    }

    public function admin_page_statistics() {
        global $wpdb;
        $resolutions = array(
            0 => __('Default Block', 'custom-blocks-free'),
            1 => __('For All Screen Resolutions', 'custom-blocks-free'),
            2 => __('For AdBlock Users', 'custom-blocks-free')
        );
        $query = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "custom_block_resolution_type");
        if (count($query)) {
            foreach ($query as $value) {
                $resolutions[$value->id] = __('from', 'custom-blocks-free') . ' ' . $value->width;
                ($value->width_stop) ? $resolutions[$value->id].=' ' . __('to', 'custom-blocks-free') . ' ' . $value->width_stop : '';
            }
        }
        ?>
        <div class="wrap">
            <h2><?= __('Custom Blocks', 'custom-blocks-free'); ?><?PHP $this->get_links_for_admin(); ?></h2>
            <h3><?= __('Statistics', 'custom-blocks-free'); ?></h3>
            <table class="stats-table" style="font-size: 18px;">
                <tr class="header">
                    <td style="border: none;">&nbsp;
                    </td>
                    <td class="col1">
                        <?= __('Block', 'custom-blocks-free'); ?>
                    </td>
                    <td class="col2">
                        <?= __('Screen Resolution', 'custom-blocks-free'); ?>
                    </td>
                    <td class="col3">
                        <?= __('Code', 'custom-blocks-free'); ?>
                    </td>
                    <td class="col4">
                        <?= __('Views', 'custom-blocks-free'); ?>
                    </td>
                    <td class="col5">
                        <?= __('Clicks', 'custom-blocks-free'); ?>
                    </td>
                    <td class="col6">
                        <?= __('CTR', 'custom-blocks-free'); ?>
                    </td>
                    <td style="border: none;">&nbsp;
                    </td>
                </tr>
                <?php
                foreach (CustomBlock::findAll() as $block) {
                    ?>
                    <tr>
                        <td class="block-collapse" rel="<?= $block->id ?>">[-]</td>
                        <td colspan="6" class="bordered">
                            <?= $block->title ?>
                        </td>
                        <td>
                        </td>
                    </tr>
                    <?php foreach ($resolutions as $key => $value) { ?>
                        <tr class="block-<?= $block->id ?>">
                            <td>&nbsp;
                            </td>
                            <td>&nbsp;
                            </td>
                            <td colspan="5" class="bordered">
                                <?= $value ?>
                            </td>
                            <td>
                            </td>
                        </tr>
                        <?php
                        foreach ($block->getItems() as $item) {
                            if ($item->type_id != $key)
                                continue;
                            if ($item->click == 0) {
                                $ctr = sprintf("%.2f", 0) . "%";
                            } else {
                                $ctr = sprintf("%.2f", ($item->click / $item->show) * 100) . "%";
                            }
                            ?>
                            <tr class="block-<?= $block->id ?>">
                                <td>&nbsp;
                                </td>
                                <td>&nbsp;
                                </td>
                                <td>&nbsp;
                                </td>
                                <td class="bordered">
                                    <?= $item->title ?>
                                </td>
                                <td class="bordered clear-show" style="text-align: right;">
                                    <?= $item->show ?>
                                </td>
                                <td class="bordered clear-click" style="text-align: right;">
                                    <?= $item->click ?>
                                </td>
                                <td class="bordered clear-ctr" style="text-align: right;">
                                    <?= $ctr ?>
                                </td>
                                <td>
                                    <input class="stat-item-state"
                                           type="checkbox" <?= ($item->published ? "checked='checked'" : "") ?>
                                           value="<?= $item->id ?>">
                                </td>
                                <td>
                                    <input class="button-clear-stat-block button-secondary" type="button" data-code-id="<?= $item->id; ?>" value="<?= __('Clear', 'custom-blocks-free'); ?>">
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    <?php
                }
                ?>
            </table>
            <?PHP $this->get_links_for_admin(false); ?>
        </div>
        <?php
    }

    // копирует блоки
    public function admin_page_blocks_copy() {
        $end = '<script type="text/javascript">window.location.href = "/wp-admin/admin.php?page=custom-blocks"</script>';
        if (isset($_REQUEST['id']) && (int) $_REQUEST['id']) {
            $id = (int) $_REQUEST['id'];
            global $wpdb;
            $table = array(
                'block' => $wpdb->prefix . 'custom_block',
                'geo' => $wpdb->prefix . 'custom_block_geoip',
                'item' => $wpdb->prefix . 'custom_block_item',
                'item_rules' => $wpdb->prefix . 'custom_block_item_rules',
                'res' => $wpdb->prefix . 'custom_block_resolution'
            );
            //block
            $block = $wpdb->get_row('SELECT * FROM ' . $table['block'] . ' WHERE id=' . $id, ARRAY_A);
            unset($block['id']);
            $block['title'] = __('Copy', 'custom-blocks-free') . ' ' . $block['title'];
            $wpdb->insert($table['block'], $block);
            $new_block_id = $wpdb->insert_id;
            if (!$new_block_id) {
                echo $end;
                exit();
            }
            //item
            $block_item = $wpdb->get_results('SELECT * FROM ' . $table['item'] . ' WHERE block_id=' . $id, ARRAY_A);
            if ($block_item) {
                foreach ($block_item as $key => $value) {
                    $block_item_id = $block_item[$key]['id'];
                    unset($block_item[$key]['id']);
                    unset($block_item[$key]['created']);
                    $block_item[$key]['block_id'] = $new_block_id;
                    $block_item[$key]['show'] = 0;
                    $block_item[$key]['click'] = 0;
                    $wpdb->insert($table['item'], $block_item[$key]);
                    $new_block_item_id = $wpdb->insert_id;
                    //geoip
                    $geoip = $wpdb->get_results('SELECT * FROM ' . $table['geo'] . ' WHERE item_id=' . $block_item_id, ARRAY_A);
                    if ($geoip) {
                        foreach ($geoip as $geo_key => $geo_value) {
                            unset($geoip[$geo_key]['id']);
                            $geoip[$geo_key]['item_id'] = $new_block_item_id;
                            $wpdb->insert($table['geo'], $geoip[$geo_key]);
                        }
                    }
                    //item rules
                    $item_rules = $wpdb->get_results('SELECT * FROM ' . $table['item_rules'] . ' WHERE item_id=' . $block_item_id, ARRAY_A);
                    if ($item_rules) {
                        foreach ($item_rules as $rules_key => $rules_value) {
                            unset($item_rules[$rules_key]['id']);
                            $item_rules[$rules_key]['item_id'] = $new_block_item_id;
                            $wpdb->insert($table['item_rules'], $item_rules[$rules_key]);
                        }
                    }
                }
            }
            //resolution
            $resolution = $wpdb->get_results('SELECT * FROM ' . $table['res'] . ' WHERE block_id=' . $id, ARRAY_A);
            if ($resolution) {
                foreach ($resolution as $res_key => $res_value) {
                    unset($resolution[$res_key]['id']);
                    $resolution[$res_key]['block_id'] = $new_block_id;
                    $wpdb->insert($table['res'], $resolution[$res_key]);
                }
            }
        }
        echo $end;
    }

    function temp_meta($id_template, $key, $value) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'custom_block_template_meta', array('template_id' => $id_template, 'meta_key' => $key, 'meta_value' => $value), array('%d', '%s', '%s'));
    }

    public function copy_rules_code_as_template($codeid, $name) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'custom_block_template', array('name' => $name));
        $insert_id = $wpdb->insert_id;
        //checked
        $checked = $wpdb->get_row('SELECT geotargeting,content_filter FROM ' . $wpdb->prefix . 'custom_block_item WHERE id=' . $codeid);
        if ($checked) {
            if ($checked->geotargeting == '1') {
                $this->temp_meta($insert_id, 'geo', '1');
            }
            if ($checked->content_filter == '1') {
                $this->temp_meta($insert_id, 'content', '1');
            }
        }
        //geotargeting
        $geotargeting = $wpdb->get_results('SELECT allow,country_id,city_id FROM ' . $wpdb->prefix . 'custom_block_geoip WHERE item_id=' . $codeid, ARRAY_A);
        if ($geotargeting) {
            $geo_city_access = array();
            $geo_country_access = array();
            $geo_city_block = array();
            $geo_country_block = array();
            foreach ($geotargeting as $geo) {
                if ($geo['allow'] == '1') {
                    if ($geo['country_id']) {
                        $geo_country_access[] = $geo['country_id'];
                    }
                    if ($geo['city_id']) {
                        $geo_city_access[] = $geo['city_id'];
                    }
                } else {
                    if ($geo['country_id']) {
                        $geo_country_block[] = $geo['country_id'];
                    }
                    if ($geo['city_id']) {
                        $geo_city_block[] = $geo['city_id'];
                    }
                }
            }
            $geo_list = array(
                'geo_city_access' => $geo_city_access,
                'geo_city_ban' => $geo_city_block,
                'geo_country_access' => $geo_country_access,
                'geo_country_ban' => $geo_country_block
            );
            foreach ($geo_list as $name_meta => $values_meta) {
                if ($values_meta) {
                    foreach (array_unique($values_meta) as $tmp_val) {
                        $this->temp_meta($insert_id, $name_meta, $tmp_val);
                    }
                }
            }
        }
        //content
        $content = $wpdb->get_results('SELECT allow,category_id,post_id FROM ' . $wpdb->prefix . 'custom_block_item_rules WHERE item_id=' . $codeid, ARRAY_A);
        if ($content) {
            $content_post_access = array();
            $content_cat_access = array();
            $content_post_block = array();
            $content_cat_block = array();
            foreach ($content as $cont) {
                if ($cont['allow'] == '1') {
                    if ($cont['category_id']) {
                        $content_cat_access[] = $cont['category_id'];
                    }
                    if ($cont['post_id']) {
                        $content_post_access[] = $cont['post_id'];
                    }
                } else {
                    if ($cont['category_id']) {
                        $content_cat_block[] = $cont['category_id'];
                    }
                    if ($cont['post_id']) {
                        $content_post_block[] = $cont['post_id'];
                    }
                }
            }
            $cont_list = array(
                'content_by_post' => $content_post_access,
                'content_by_post_ban' => $content_post_block,
                'content_by_category' => $content_cat_access,
                'content_by_category_ban' => $content_cat_block
            );
            foreach ($cont_list as $name_meta => $values_meta) {
                if ($values_meta) {
                    foreach (array_unique($values_meta) as $tmp_val) {
                        $this->temp_meta($insert_id, $name_meta, $tmp_val);
                    }
                }
            }
        }
        //time
        $time = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . 'custom_block_time WHERE id_item=' . $codeid);
        if ($time) {
            $time_key = array(
                'status' => 'time',
                'time_start' => 'time_start_timetargeting',
                'time_end' => 'time_end_timetargeting',
                'show_date_start' => 'start_date_timetargeting',
                'show_date_end' => 'end_date_timetargeting',
                'all_hours' => 'all_hours',
                'only_holiday' => 'only_holiday_time',
                'only_work_day' => 'only_work_time',
            );
            foreach ($time_key as $i_key => $o_key) {
                if ($i_key == 'time_start' || $i_key == 'time_end') {
                    $this->temp_meta($insert_id, $o_key, date_create_from_format('H:i:s', $time->$i_key)->format('H:i'));
                } elseif ($i_key == 'show_date_start' || $i_key == 'show_date_end') {
                    $this->temp_meta($insert_id, $o_key, date_create_from_format('Y-m-d', $time->$i_key)->format('d.m.Y'));
                } elseif (isset($time->$i_key) && $time->$i_key) {
                    $this->temp_meta($insert_id, $o_key, $time->$i_key);
                }
            }
        }
    }

    /*
     * Обновляет\создает рекламный блок
     */

    public function admin_page_blocks_update() {
        wp_enqueue_script("jquery-ui-core");
		wp_enqueue_script("jquery-ui-tabs");
        wp_enqueue_script("jquery-ui-datepicker");
        wp_enqueue_script('jquery-tmpl', plugins_url('/js/jquery.tmpl.js', __FILE__), array('jquery'));
        wp_enqueue_script('undo-redo', plugins_url('/js/undo-redo.js', __FILE__), array('jquery'));
        wp_enqueue_script("jquery-ui-timepicker", plugins_url('/js/jquery.ui.timepicker.js', __FILE__), array('jquery', 'jquery-ui-core'));
        wp_enqueue_style('jquery-ui-timepicker-css', plugins_url('/css/jquery.ui.timepicker.css', __FILE__));
        wp_enqueue_script('chosen', plugins_url('/js/chosen/chosen.jquery.min.js', __FILE__), array('jquery'));
        wp_enqueue_style('chosen', plugins_url('/js/chosen/chosen.css', __FILE__));
        wp_enqueue_style('colorpicker', plugins_url('/css/colorpicker.css', __FILE__));
        wp_enqueue_script('colorpicker-custom', plugins_url('/js/colorpicker.js', __FILE__));
        wp_localize_script('chosen', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
//        wp_enqueue_script('custom-blocks', plugins_url('/js/admin.js', __FILE__), array('jquery'), '1.0.0');
        wp_enqueue_style('custom-blocks', plugins_url('/css/admin.css', __FILE__));
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        global $wpdb;
        $add_template_rules = array();
        $list_geo_ids = array();
        if (isset($_REQUEST['codes'])) {
            if (!$block = CustomBlock::findById((int)$_REQUEST['id'])) {
                $block = new CustomBlock();
            }
            if (isset($_REQUEST['type_sync']) && $_REQUEST['type_sync'] == 'on') {
                $block->sync = 1;
            } else {
                $block->sync = 0;
            }
            $block->title = sanitize_text_field($_REQUEST['title']);
            $block->save();
            $refresh_counter_index = false;
            // Отключение показа для заданных разрешений
            $id_resolutions = $wpdb->get_col('SELECT id FROM ' . $wpdb->prefix . 'custom_block_resolution_type');
            foreach ($id_resolutions as $i) {
                $sql = "DELETE FROM " . $wpdb->prefix . "custom_block_resolution WHERE `block_id` = " . $block->id . " AND `resolution_id` = " . $i;
                $wpdb->query($sql);
                if (isset($_REQUEST['resolution']) && isset($_REQUEST['resolution'][$i])) {
                    $sql = "INSERT INTO " . $wpdb->prefix . "custom_block_resolution (`resolution_id`, `block_id`) VALUES ('" . $i . "', '" . $block->id . "');";
                    $wpdb->query($sql);
                }
            }
            $existItems = $block->getItems();
            $sentItemsIds = array();
            foreach ($_REQUEST['codes'] as $id => $params) {
                $code = CustomBlockItem::findById((int)$id);
                if (!$code) {
                    $code = new CustomBlockItem();
                    $refresh_counter_index = true;
                }



                //$code->id = $id;
                $code->block_id = $block->id;
                $code->type_id = $params['type_id'];
                $code->title = $params['title'];
                $code->html = $params['html'];
                $code->published = (int) isset($params['published']);
                if (isset($params['geotargeting'])) {
                    $code->geotargeting = 1;
                } else {
                    $code->geotargeting = 0;
                }
                if (isset($params['content_filter'])) {
                    $code->content_filter = 1;
                } else {
                    $code->content_filter = 0;
                }
                if (isset($params['subhead'])) {
                    $code->subhead = 1;
                } else {
                    $code->subhead = 0;
                }
                unset($new_id);
                $new_id = $code->save();

                if ($new_id) {
                    $codes_id = $new_id;
                } else {
                    $codes_id = $code->id;
                }
                $old_id[$id] = $codes_id;
                if ($code->geotargeting == 1) {
                    if ($new_id) {
                        $array_codes_geo[] = $new_id;
                    } else {
                        $array_codes_geo[] = $code->id;
                    }
                }
                $sentItemsIds[] = $codes_id;
                //delete old rules
                $sql = "DELETE FROM `" . $wpdb->prefix . "custom_block_item_rules` WHERE `item_id` = '" . $code->id . "'";
                $wpdb->query($sql);
                //add to rules
                $array_indexes = array();
                foreach (array('rules_by_category', 'rules_by_post', 'rules_by_category_ban', 'rules_by_post_ban', 'tax_allow', 'term_allow', 'tax_ban', 'term_ban') as $type) {
                    if (isset($params[$type])) {
                        $array_indexes[$type] = $params[$type];
                    }
                }
                foreach ($array_indexes as $key => $value) {
                    if (isset($params[$key])) {
                        foreach ($params[$key] as $values_id) {
                            unset($add_array);
                            $data_type_array = array('%s', '%d');
                            $add_array['item_id'] = $codes_id;
                            if (stristr($key, '_ban') === FALSE) {
                                $add_array['allow'] = 1;
                            } else {
                                $add_array['allow'] = 0;
                            }
                            if (stristr($key, 'category') !== FALSE) {
                                $add_array['category_id'] = $values_id;
                                $data_type_array[] = '%d';
                            }
                            if (stristr($key, 'post') !== FALSE) {
                                $add_array['post_id'] = $values_id;
                                $data_type_array[] = '%d';
                            }
                            if (stristr($key, 'tax') !== FALSE) {
                                $add_array['taxonomy_id'] = $values_id;
                                $data_type_array[] = '%s';
                            }
                            if (stristr($key, 'term') !== FALSE) {
                                $add_array['term_id'] = $values_id;
                                $data_type_array[] = '%d';
                            }

                            $wpdb->insert($wpdb->prefix . 'custom_block_item_rules', $add_array, $data_type_array);
                        }
                    }
                }
                //time targeting
                $table_time = $wpdb->prefix . 'custom_block_time';
                $flag_time = false;
                $time_name_array = array('time_targeting', 'time_start_timetargeting', 'time_end_timetargeting', 'start_date_timetargeting', 'end_date_timetargeting', 'only_work_time', 'all_hours', 'only_holiday_time');
                foreach ($time_name_array as $index_n) {
                    if (isset($params[$index_n]) and $params[$index_n]) {
                        $flag_time = true;
                    }
                }
                $wpdb->delete($table_time, array('id_item' => $code->id));
                if ($flag_time) {
                    $array_time = array();
                    $array_time['id_item'] = $code->id;
                    $array_time['status'] = (isset($params['time_targeting']) && $params['time_targeting']) ? true : false;
                    if (isset($params['time_start_timetargeting']) && $params['time_start_timetargeting'] <> '') {
                        $array_time['time_start'] = $params['time_start_timetargeting'];
                    }
                    if (isset($params['time_end_timetargeting']) && $params['time_end_timetargeting'] <> '') {
                        $array_time['time_end'] = $params['time_end_timetargeting'];
                    }
                    $array_time['all_hours'] = (isset($params['all_hours']) && $params['all_hours']) ? true : false;
                    $array_time['only_work_day'] = (isset($params['only_work_time']) && $params['only_work_time']) ? true : false;
                    $array_time['only_holiday'] = (isset($params['only_holiday_time']) && $params['only_holiday_time']) ? true : false;
                    if (isset($params['start_date_timetargeting']) && $params['start_date_timetargeting']) {
                        $array_time['show_date_start'] = date_create_from_format('d.m.Y', $params['start_date_timetargeting'])->format('Y-m-d');
                    }
                    if (isset($params['end_date_timetargeting']) && $params['end_date_timetargeting']) {
                        $array_time['show_date_end'] = date_create_from_format('d.m.Y', $params['end_date_timetargeting'])->format('Y-m-d');
                    }
                    $wpdb->insert($table_time, $array_time);
                }
                $sql = "DELETE FROM `" . $wpdb->prefix . "custom_block_geoip` WHERE `item_id` = '" . $code->id . "'";
                $wpdb->query($sql);
                //decorations
                $id_for_decor = $old_id[$id];
                if (isset($params['decor'])) {
                    if (isset($_REQUEST['settings'][$id])) {
                        $as_template = false;
                        $title_decor_on_template = '';
                        if (isset($_REQUEST['template'][$id]['check'])) {
                            $id_decor = 'new';
                            $title_decor_on_template = $_REQUEST['template'][$id]['title'];
                            $as_template = true;
                        } else {
                            //geting id decor block if exist (id or new)
                            $test_id_decor = (int) $wpdb->get_var("SELECT id_decor FROM " . $wpdb->prefix . "custom_block_decor_link WHERE id_item=" . $id_for_decor . " AND active=0");
                            $its_template = (int) $wpdb->get_var("SELECT count(*) FROM " . $wpdb->prefix . "custom_block_decor_link WHERE active=1");
                            if (!$test_id_decor) {
                                $id_decor = 'new';
                            } elseif ($its_template) {
                                $id_decor = 'new';
                            } else {
                                $id_decor = $id_for_decor;
                            }
                        }
                        CustomBlocksPlugin::admin_page_decor_update($id_decor, $title_decor_on_template, $_REQUEST['settings'][$id], $id_for_decor, $as_template);
                    } elseif (isset($params['decors']['template'])) {
                        //set templates
                        CustomBlocksPlugin::set_link_to_decor($params['decors']['template'], $id_for_decor);
                    }
                } else {
                    $test_id_decor = (int) $wpdb->get_var("SELECT id_decor FROM " . $wpdb->prefix . "custom_block_decor_link WHERE id_item=" . $id_for_decor . " AND active=0");
                    if ($test_id_decor) {
                        $wpdb->update($wpdb->prefix . "custom_block_decor_link", array('active' => 2), array('id_item' => $id_for_decor));

                        //$wpdb->delete($wpdb->prefix."custom_block_decor_link", array('id_decor' => $test_id_decor));
                        //$wpdb->delete($wpdb->prefix."custom_block_decor", array('id' => $test_id_decor));
                    }
                }
                if (!isset($params['geotargeting'])) {
                    $list_geo_ids[] = $id;
                }
                if (isset($params['templates']) && $params['templates']) {
                    $wpdb->delete($wpdb->prefix . "custom_block_template_meta", array('meta_value' => $code->id, 'meta_key' => 'include_block'));
                    foreach ($params['templates'] as $param_templates) {
                        $wpdb->insert(
                                $wpdb->prefix . "custom_block_template_meta", array('template_id' => $param_templates, 'meta_key' => 'include_block', 'meta_value' => $code->id), array('%d', '%s', '%s')
                        );
                    }
                }
                if (isset($params['save_as_template']) && $params['save_as_template']) {

                    $add_template_rules[$code->id] = $params['name_save_template'];
                }
            }
            //refresh_counter
            if ($refresh_counter_index) {
                $wpdb->query("UPDATE `{$wpdb->prefix}custom_block_item` SET `show_index` = '0' WHERE `block_id` = " . $block->id);
            }

            if (isset($_REQUEST['rule'])) {
                $i = 0;
                foreach ($_REQUEST['rule'] as $code_id => $params) {
                    if (!in_array($code_id, $list_geo_ids)) {
                        if (isset($array_codes_geo[$i])) {
                            $items_id = $array_codes_geo[$i];
                            $i++;
                        }
                        $allow = 0;
                        if (isset($params['city_ban']) and count($params['city_ban'])) {
                            foreach ($params['city_ban'] as $value) {
                                $sql = "INSERT INTO `" . $wpdb->prefix . "custom_block_geoip` (`item_id`, `allow`, `country_id`, `city_id`) VALUES (
                              '" . $items_id . "',
                              '" . $allow . "',
                              '" . self::get_country_from_city($value) . "',
                              '" . $value . "'
                              )";
                                $wpdb->query($sql);
                            }
                        }
                        if (isset($params['country_ban']) and count($params['country_ban'])) {
                            foreach ($params['country_ban'] as $value) {
                                $sql = "INSERT INTO `" . $wpdb->prefix . "custom_block_geoip` (`item_id`, `allow`, `country_id`) VALUES (
                              '" . $items_id . "',
                              '" . $allow . "',
                              '" . $value . "'
                              )";
                                $wpdb->query($sql);
                            }
                        }
                        $allow = 1;
                        if (isset($params['city_access']) and count($params['city_access'])) {
                            foreach ($params['city_access'] as $value) {
                                $sql = "INSERT INTO `" . $wpdb->prefix . "custom_block_geoip` (`item_id`, `allow`, `country_id`, `city_id`) VALUES (
                              '" . $items_id . "',
                              '" . $allow . "',
                              '" . self::get_country_from_city($value) . "',
                              '" . $value . "'
                              )";
                                $wpdb->query($sql);
                            }
                        }
                        if (isset($params['country_access']) and count($params['country_access'])) {
                            foreach ($params['country_access'] as $value) {
                                $sql = "INSERT INTO `" . $wpdb->prefix . "custom_block_geoip` (`item_id`, `allow`, `country_id`) VALUES (
                              '" . $items_id . "',
                              '" . $allow . "',
                              '" . $value . "'
                              )";
                                $wpdb->query($sql);
                            }
                        }
                    }
                }
            }

            // delete all items that was not sent
            foreach ($existItems as $item) {
                if (!in_array($item->id, $sentItemsIds)) {
                    $item->delete();
                }
            }
            if (isset($add_template_rules) && $add_template_rules) {
                foreach ($add_template_rules as $atr_key => $art_val) {
                    $this->copy_rules_code_as_template($atr_key, $art_val);
                }
            }
            ?>
            <script type="text/javascript">window.location.href = "/wp-admin/admin.php?page=custom-blocks"</script>
            <?PHP
            exit;
        }
        if (!$block = CustomBlock::findById((int)$_REQUEST['id'])) {
            $block = new CustomBlock();
        }
        ?>
        <script type="text/javascript">
            jQuery(function () {
                var $ = jQuery;
                jQuery('#tabs')
                        .tabs()
                        .addClass('ui-tabs-vertical ui-helper-clearfix');
                jQuery("#tabs li a input").click(function (event) {
                    event.stopPropagation();
                });
                jQuery("#submit-button").click(function () {
                });
            });
        </script>
        <script type="text/x-jquery-tmpl" id="code-template">
            <div class="code" id="code${id}">
            <div class="head_code" id="code${id}"><div class="js_title_code"><a>{%if $title %}${title}{%else%}<?= __('New Code', 'custom-blocks-free'); ?>{%/if%}</a><div class="rectangle_open" /></div>
            <div class="code_icons"><a href="#" class="add-code js-add-code" rel="${type_id}"> </a><a href="#" class="copy-code" rel="${id}"></a><a href="#" class="remove-code" rel="${id}"></a></div>
            </div>
            <div class="other_code"  id="code${id}">
            <div class="title">
            <input type="hidden" name=codes[${id}][type_id] value="${type_id}" />
            <input type="text" placeholder="<?= __('New Code', 'custom-blocks-free'); ?>" class="input-title" name="codes[${id}][title]" value="${title}" />
            </div>
            <div class="body">
            <div>
            <a href="#" class="undo"><img title="<?= __('Cancel Changes', 'custom-blocks-free'); ?>" src="<?= plugins_url('/images/undo.png', __FILE__); ?>" /></a>
            <a href="#" class="redo"><img title="<?= __('Restore Changes', 'custom-blocks-free'); ?>" src="<?= plugins_url('/images/redo.png', __FILE__); ?>" /></a>
            </div>
            <div>
            <table style="width:100%;">
            <tr>
            <td>
            <textarea class="input-html" placeholder="<?= __('HTML here', 'custom-blocks-free'); ?>" name="codes[${id}][html]">${html}</textarea>
            </td>
            <td class="settings_visible">
            <?= __('Targeting and Styling', 'custom-blocks-free'); ?>
            <hr>
            <input type="checkbox" data-id="${id}" data-type="content_filter" name="codes[${id}][content_filter]"{%if content_filter%} checked{%/if%}><?= __('Targeting by Content', 'custom-blocks-free'); ?><br><br>
            <input type="checkbox" data-id="${id}" data-type="time_targeting" name="codes[${id}][time_targeting]"{%if time_targeting.status%} checked{%/if%}><?= __('Targeting by Time', 'custom-blocks-free'); ?><br><br>
            <div class="pro_available">
            <input type="checkbox" disabled data-id="${id}" data-type="geotargeting" name="codes[${id}][geotargeting]"{%if geotargeting%} checked{%/if%}><?= __('GEO-Targeting', 'custom-blocks-free'); ?><br><br>
            <input type="checkbox" disabled data-id="${id}" data-type="decor" name="codes[${id}][decor]"{%if decor%} checked{%/if%}><?= __('Block Style', 'custom-blocks-free'); ?><br><br>
            <input type="checkbox" disabled data-id="${id}" data-type="templates" name="codes[${id}][templates]"{%if templates%} checked{%/if%}><?= __('Targeting Templates', 'custom-blocks-free'); ?>
            </div>
            </td>
            </tr>
            </table>
            </div>
            <div>
            <input class="published" type="checkbox" {%if $data.published %}checked="checked"{%else%}{%/if%} name='codes[${id}][published]'> <?= __('Active', 'custom-blocks-free'); ?>
            <input class="name_save_template" type="text" name='codes[${id}][name_save_template]' placeholder="<?= __('Template Title', 'custom-blocks-free'); ?>">
            </div>
            </div>
            <div class="footer">
            <div>
            </div>
            <div class="options">
            </div>
            </div>
            <div class="post_category">
            <table class="post_options_geo js_geo" {%if geotargeting%}{%else%}style="display: none;"{%/if%}>
            <tr>
            <td class="table_options_geo_name">
            <?= __('Geo Targeting', 'custom-blocks-free'); ?>
            </td>
            <td class="table_options_geo_center_text">
            <?= __('Country', 'custom-blocks-free'); ?>
            </td>
            <td class="table_options_geo_center_text">
            <?= __('City', 'custom-blocks-free'); ?>
            </td>
            </tr>
            <tr>
            <td  style="border-bottom: 1px solid #000000;">
            <?= __('Allow:', 'custom-blocks-free'); ?>
            </td>
            <td class="table_border_add block_chos block_geos">
            <select disabled="" data-placeholder="<?= __('Choose a country...', 'custom-blocks-free'); ?>"  name="rule[${id}][country_access][]" data-code-id="${id}" id="country_access${id}" class="js_country_access" multiple style="width:100%">
            {%each country_access%}
            <option value="${id}"{%if selected%} selected{%/if%}>${name}</option>
            {%/each%}
            </select>
            </td>
            <td class="table_border_add block_chos block_geos">
            <select disabled="" data-placeholder="<?= __('Choose a city...', 'custom-blocks-free'); ?>"  name="rule[${id}][city_access][]" data-code-id="${id}" class="js_city_access" id="city_access${id}" multiple style="width:100%">
            {%each city_access%}
            <option value="${id}"{%if selected%} selected{%/if%}>${name}</option>
            {%/each%}
            </select>
            </td>
            </tr>
            <tr>
            <td>
            <?= __('Disallow:', 'custom-blocks-free'); ?>
            </td>
            <td class="table_border_add block_chos block_geos">
            <select disabled="" data-placeholder="<?= __('Choose a country...', 'custom-blocks-free'); ?>"  name="rule[${id}][country_ban][]" data-code-id="${id}" class=" js_country_block" id="country_block${id}" multiple style="width:100%">
            {%each country_ban%}
            <option value="${id}"{%if selected%} selected{%/if%}>${name}</option>
            {%/each%}
            </select>
            </td>
            <td class="table_border_add block_chos block_geos">
            <select disabled="" data-placeholder="<?= __('Choose a city...', 'custom-blocks-free'); ?>"  name="rule[${id}][city_ban][]" data-code-id="${id}" class="js_city_block" id="city_block${id}" multiple style="width:100%">
            {%each city_ban%}
            <option value="${id}"{%if selected%} selected{%/if%}>${name}</option>
            {%/each%}
            </select>
            </td>
            </tr>
            </table>
            <table class="post_options_geo js_cont" {%if content_filter%}{%else%}style="display: none;"{%/if%}>
            <tr>
            <td class="table_options_geo_name">
            <?= __('Targeting by Content', 'custom-blocks-free'); ?>
            </td>
            <td class="table_options_geo_center_text">
            <?= __('Category', 'custom-blocks-free'); ?>
            </td>
            <td class="table_options_geo_center_text">
            <?= __('Post', 'custom-blocks-free'); ?>
            </td>
            </tr>
            <tr>
            <td style="border-bottom: 1px solid #000000;">
            <?= __('Allow:', 'custom-blocks-free'); ?>
            </td>

            <td class="table_border_add block_chos block_post">
            <select data-placeholder="<?= __('Choose a category...', 'custom-blocks-free'); ?>"  name="codes[${id}][rules_by_category][]" id="cat_access${id}" class="chosen-select" multiple style="width:100%">
            {%each categories%}
            <option value="${category_id}"{%if selected%} selected{%/if%}>${category_title}</option>
            {%/each%}
            </select>
            </select>
            </td>
            <td class="table_border_add block_chos block_post">
            <select data-placeholder="<?= __('Choose a post...', 'custom-blocks-free'); ?>"  name="codes[${id}][rules_by_post][]" id="post_access${id}" class="chosen-select" multiple style="width:100%">
            {%each posts%}
            <option value="${post_id}"{%if selected%} selected{%/if%}>${post_title}</option>
            {%/each%}
            </select>
            </td>
            </tr>
            <tr>
            <td>
            <?= __('Disallow:', 'custom-blocks-free'); ?>
            </td>


            <td class="table_border_add block_chos block_post">
            <select data-placeholder="<?= __('Choose a category...', 'custom-blocks-free'); ?>"  name="codes[${id}][rules_by_category_ban][]" id="cat_block${id}" class="chosen-select" multiple style="width:100%">
            {%each categories_ban%}
            <option value="${category_id}"{%if selected%} selected{%/if%}>${category_title}</option>
            {%/each%}
            </select>
            </td>
            <td class="table_border_add block_chos block_post">
            <select data-placeholder="<?= __('Choose a post...', 'custom-blocks-free'); ?>"  name="codes[${id}][rules_by_post_ban][]" id="post_block${id}" class="chosen-select" multiple style="width:100%">
            {%each posts_ban%}
            <option value="${post_id}"{%if selected%} selected{%/if%}>${post_title}</option>
            {%/each%}
            </select>
            </td>
            </tr>
            <tr><td></td>
            <td colspan="3"><input type="checkbox" data-id="${id}" name="codes[${id}][subhead]"{%if subhead%} checked{%/if%}> <?= __('Including subcategories (for categories)', 'custom-blocks-free'); ?></td>
            </tr>
            </table>
            <table class="time_targeting_settings" {%if time_targeting.status%}{%else%}style="display: none;"{%/if%}>
            <tr><td class="table_options_geo_name"><?= __('Targeting by Time', 'custom-blocks-free'); ?></td></tr>
            <tr>
            <td style="width:200px;"><i><?= __('Time', 'custom-blocks-free'); ?></i></td>
            <td style="width:250px;"><i><?= __('Dates', 'custom-blocks-free'); ?></i></td>
            </tr>
            <tr>
            <td><input class="time_start_timetargeting" {%if time_targeting.all_hours%}disabled{%/if%} data-type="time_start_timetargeting" data-id="${id}" type='text' name='codes[${id}][time_start_timetargeting]' value='${time_targeting.time_start}'> - <input class="time_end_timetargeting" {%if time_targeting.all_hours%}disabled{%/if%} type='text' data-id="${id}" name='codes[${id}][time_end_timetargeting]' value='${time_targeting.time_end}'></td>
            <td><input class="start_date_timetargeting" data-type="start_date_timetargeting"  data-id="${id}" type="text"  name='codes[${id}][start_date_timetargeting]' value='${time_targeting.show_date_start}'> - <input class="end_date_timetargeting" type="text" data-id="${id}"  name='codes[${id}][end_date_timetargeting]' value='${time_targeting.show_date_end}'></td>
            <td><input class="only_work_time" data-id="${id}" data-type="only_work_time" type="checkbox" {%if time_targeting.only_work_day%}checked="checked"{%/if%} name='codes[${id}][only_work_time]'><?= __('Weekdays only', 'custom-blocks-free'); ?></td>
            </tr>
            <tr>
            <td><input class="all_hours" data-id="${id}" data-type="all_hours" type="checkbox" {%if time_targeting.all_hours%}checked="checked"{%/if%} name='codes[${id}][all_hours]'><?= __('24/7', 'custom-blocks-free'); ?></td>
            <td></td>
            <td><input class="only_holiday_time" data-id="${id}" data-type="only_holiday_time" type="checkbox" {%if time_targeting.only_holiday%}checked="checked"{%/if%} name='codes[${id}][only_holiday_time]'><?= __('Weekend only', 'custom-blocks-free'); ?></td>
            </tr>
            </table>
            <div class="decor_settings" rel="${id}" {%if decor%}{%else%}style="display: none;"{%/if%}>
            <a class="button-primary inputing_decor_create" rel="${id}"><?= __('Create a Custom Style', 'custom-blocks-free'); ?></a>
            <a class="button-primary inputing_decor_chose" rel="${id}"><?= __('Use a Template', 'custom-blocks-free'); ?></a>
            <div class="decor_settings_select" rel="${id}"></div>
            <div class="place_decor" rel="${id}"></div>
            </div>

            <table class="templates_settings" rel="${id}" {%if templates%}{%else%}style="display: none;"{%/if%}>
            <tr><td><span><?= __('Targeting Templates (save block settings before using this feature)', 'custom-blocks-free'); ?></span></td></tr>
            <tr><td>
            <select name="codes[${id}][templates][]" class="chosen-select block_chos templates_special_select" multiple>
            {%each templates_element%}
            <option value="${id}"{%if selected%} selected{%/if%}>${name}</option>
            {%/each%}
            </select>
            </td>
            </tr>
            <tr><td><div class="templates_error_place"></div></td>
            </tr>
            </table>
            <div>
            </div>
            </div>
        </script>
        <div class="wrap">
            <h2><?= __('Custom Blocks', 'custom-blocks-free'); ?><?PHP $this->get_links_for_admin(); ?></h2>
            <h3><?= ($block->id) ? __('Edit Block', 'custom-blocks-free') : __('Adding a New Block', 'custom-blocks-free'); ?></h3>
            <form id="block-form" method="post" onsubmit="return codeFormSubmit()">
                <input type="hidden" name="id" value="<?= $block->id ?>">
                <input type="hidden" name="action" value="update">
                <?php wp_nonce_field(); ?>
                <div class="form-field">
                    <input type="text" name="title" value="<?= $block->title ?>" placeholder="<?= __('Block Title', 'custom-blocks-free'); ?>" required/><br>
                    <label for="type_sync"><input type="checkbox" name="type_sync" id="type_sync" <?= ($block->sync) ? 'checked' : ''; ?>> <?= __('Synchronous Code Type', 'custom-blocks-free'); ?></label><br/><br/>
                </div> 
                <div id="tabs" class="form-field">
                    <ul>
                        <li><a class="tab1-a" href="#tab1"><?= __('For all', 'custom-blocks-free'); ?><br><?= __('screen resolutions', 'custom-blocks-free'); ?> (<span class="counter">0</span>)</a>
                        </li>
                        <?PHP
                        $resolution = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "custom_block_resolution_type");
                        $special_for_blocking = array();
                        foreach ($resolution as $value) {
                            $special_for_blocking[] = $value->id;
                            ?>
                            <li disabled=""><a class="tab<?= $value->id; ?>-a" disabled=""
                                               href="#tab<?= $value->id; ?>"><?PHP
                                                   echo __('from', 'custom-blocks-free') . ' ' . $value->width;
                                                   echo ($value->width_stop) ? '<br>' . __('to', 'custom-blocks-free') . ' ' . $value->width_stop : '';
                                                   ?>
                                    (<span class="counter">0</span>) <input disabled=""
                                                                            type="checkbox" <?= ($block->resolutionEnabled($value->id) ? '' : '') ?>
                                                                            name="resolution[<?= $value->id; ?>]" value="1"></a></li>
                            <?PHP }
                            ?>
                        <li><a class="tab2-a" href="#tab2" disabled=""><?= __('For Adblock Users', 'custom-blocks-free'); ?> (<span class="counter">0</span>)</a></li>
                        <li><a class="tab0-a" href="#tab0" disabled=""><?= __('Default Block', 'custom-blocks-free'); ?> (<span class="counter">0</span>)</a></li>
                    </ul>
                    <?php
                    $query = $wpdb->get_col("SELECT id FROM " . $wpdb->prefix . "custom_block_resolution_type");
                    $query[] = 0;
                    $query[] = 1;
                    $query[] = 2;
                    foreach ($query as $value) {
                        ?>
                        <div id="tab<?= $value ?>" class="tab-content" rel="<?= $value ?>">
                            <div class="code_icons" style="width: 100%; text-align: right; margin-right: 15px;">
                                <a href="#" class="add-button button-primary js-add-code" rel="<?= $value ?>"><?= __('Add Block', 'custom-blocks-free'); ?></a></div>
                            <div class="codes">
                                <?php
                                $codes = $block->getItems();
                                if (sizeof($codes) == 0) {
                                    echo __('No codes yet', 'custom-blocks-free');
                                } else {
                                    foreach ($codes as $code) {
                                        
                                    }
                                }
                                ?>
                            </div>
                            <div class="code_icons" style="width: 100%; text-align: right; margin-right: 15px;">
                                <a href="#" class="add-button button-primary js-add-code" rel="<?= $value ?>"><?= __('Add Block', 'custom-blocks-free'); ?></a></div>
                        </div>
                    <?php } ?>
                </div>
                <div>
                    <br/>
                    <input id="submit-button" class="button button-primary" type="submit" value="<?= __('Save', 'custom-blocks-free'); ?>">
                </div>
            </form>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
        <?PHP
        $i = 2;
        $j = 0;
        foreach ($block->getItems() as $item_block) {
            if ($j < $i) {
                $item = (array) $item_block;
                $json_array = json_encode(array('id' => $item['id'], 'type_id' => $item['type_id'], 'title' => $item['title'], 'html' => $item['html'], 'published' => (bool) $item['published'], 'posts' => self::get_all_posts(1, $item['id']), 'posts_ban' => self::get_all_posts(0, $item['id']), 'categories' => self::get_all_posts_categories(1, $item['id']), 'geotargeting' => ($item['geotargeting'] == 'true') ? true : false, 'content_filter' => ($item['content_filter'] == 'true') ? true : false, 'time_targeting' => self::get_time_targeting($item['id']), 'decor' => self::get_decor($item['id']), 'country_ban' => self::get_country($item['id'], 0), 'country_access' => self::get_country($item['id'], 1), 'city_ban' => self::get_city($item['id'], 0), 'city_access' => self::get_city($item['id'], 1), 'categories_ban' => self::get_all_posts_categories(0, $item['id']), 'templates_element' => self::get_templates_info($item['id']), 'templates' => self::get_templates_info($item['id'], false), 'tax_allow' => self::get_tax(1, $item['id']), 'tax_ban' => self::get_tax(0, $item['id']), 'term_allow' => self::get_tterm(1, $item['id']), 'term_ban' => self::get_tterm(0, $item['id']), 'subhead' => ($item['subhead'] == 'true') ? true : false));
                echo "addCode(" . $json_array . "); ";
                $j++;
            }
        }
        ?>
                    $('.chosen-select').chosen();

                    $(document).ready(function () {
                        $("#tabs").tabs("disable");
                        $('.time_start_timetargeting').timepicker({
                            showPeriodLabels: false,
                            hourText: '<?= __('Hours', 'custom-blocks-free'); ?>',
                            minuteText: '<?= __('Minutes', 'custom-blocks-free'); ?>',
                            timeSeparator: ':',
                            nowButtonText: '<?= __('Now', 'custom-blocks-free'); ?>',
                            showNowButton: true,
                            closeButtonText: '<?= __('Close', 'custom-blocks-free'); ?>',
                            showCloseButton: true,
                            deselectButtonText: '<?= __('Cancel', 'custom-blocks-free'); ?>',
                            showDeselectButton: true
                        });
                        $('.time_end_timetargeting').timepicker({
                            showPeriodLabels: false,
                            hourText: '<?= __('Hours', 'custom-blocks-free'); ?>',
                            minuteText: '<?= __('Minutes', 'custom-blocks-free'); ?>',
                            timeSeparator: ':',
                            nowButtonText: '<?= __('Now', 'custom-blocks-free'); ?>',
                            showNowButton: true,
                            closeButtonText: '<?= __('Close', 'custom-blocks-free'); ?>',
                            showCloseButton: true,
                            deselectButtonText: '<?= __('Cancel', 'custom-blocks-free'); ?>',
                            showDeselectButton: true
                        });
                        $('.end_date_timetargeting').datepicker({
                            changeMonth: true,
                            changeYear: true
                        });
                        $('.start_date_timetargeting').datepicker({
                            changeMonth: true,
                            changeYear: true
                        });
                        $.datepicker.regional['ru'] = {
                            closeText: '<?= __('Close', 'custom-blocks-free'); ?>',
                            prevText: '&#x3c;<?= __('Prev', 'custom-blocks-free'); ?>',
                            nextText: '<?= __('Next', 'custom-blocks-free'); ?>&#x3e;',
                            currentText: '<?= __('Today', 'custom-blocks-free'); ?>',
                            monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                                'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
                            monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
                                'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
                            dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
                            dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
                            dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
                            dateFormat: 'dd.mm.yy',
                            firstDay: 1,
                            isRTL: false
                        };
                        $.datepicker.setDefaults($.datepicker.regional['ru']);
                        $(document).on('click', '.save_as_template', function () {
                            if ($(this).is(":checked"))
                            {
                                $(this).parent().find('.name_save_template').show(350);
                            } else {
                                $(this).parent().find('.name_save_template').hide(350);
                            }
                        });
                    });



                    replaceAll = function (string, omit, place, prevstring) {
                        if (prevstring && string === prevstring)
                            return string;
                        prevstring = string.replace(omit, place);
                        return replaceAll(prevstring, omit, place, string)
                    }
                    function emptyObject(obj) {
                        for (var i in obj) {
                            return false;
                        }
                        return true;
                    }
                    function forEaching(data, callback) {
                        for (var key in data) {
                            if (data.hasOwnProperty(key)) {
                                callback(key, data[key]);
                            }
                        }
                    }
                    function setCloneChosen(elem_obj, old_id, new_id) {
                        forEaching(elem_obj, function (key, value) {
                            var new_key = key.replace(old_id, new_id);
                            $("#" + new_key + " option:selected").each(function () {
                                this.selected = false;
                            });
                            forEaching(value, function (key_val, value_val) {
                                //ищем элемент
                                if ($("#" + new_key + " [value='" + key_val + "']").length)
                                {
                                    $("#" + new_key + " [value='" + key_val + "']").attr("selected", "selected");
                                } else {
                                    $("#" + new_key).append($('<option value="' + key_val + '" selected>' + value_val + '</option>'));
                                }
                            });
                        });
                    }
                    $('body').on('click', '.inputing_decor_create', function () {
                        var create_for_id = $(this).attr('rel');
                        $.ajax({
                            type: 'POST',
                            url: ajax_object.ajax_url,
                            data: {
                                action: 'get_decor_template_codes',
                                id_rel: create_for_id,
                                new : 1
                            },
                            success: function (data) {
                                $('.place_decor[rel="' + create_for_id + '"]').html(data);
                                $('.decor_settings_select[rel="' + create_for_id + '"]').html('');
                            },
                            dataType: 'html'
                        });
                    });

                    $('body').on('click', '.copy-code', function () {
                        var id_copy_code = $(this).attr('rel');
                        var randomnumber = Math.floor(Math.random() * (999999 - 1 + 1)) + 1;
                        var rel_id = $(this).parents('.tab-content').attr('rel');
                        var selected_chosens = new Object();
                        $("#code" + id_copy_code).find('select').each(function () {
                            var element_obj = new Object();
                            $(this).find('option').each(function () {
                                if ($(this).attr("selected") == 'selected')
                                {
                                    element_obj[$(this).val()] = $(this).html();
                                }
                            });
                            selected_chosens[$(this).attr('id')] = element_obj;
                        });
                        $("#code" + id_copy_code)
                                .clone(true)
                                .attr('id', 'code' + randomnumber)
                                .appendTo("#tab" + rel_id + " .codes");
                        var new_sel = $('#code' + randomnumber);

                        new_sel.find('[rel*="' + id_copy_code + '"]').each(function () {
                            $(this).attr('rel', randomnumber);
                        });
                        new_sel.find('[id*="code"],[id*="access"],[id*="block"],[id*="template_title"]').each(function () {
                            var tmps = $(this).attr('id').replace(id_copy_code, randomnumber);
                            $(this).attr('id', tmps);
                        });
                        new_sel.find('[data-id*="' + id_copy_code + '"]').each(function () {
                            $(this).attr("data-id", randomnumber);
                        });
                        new_sel.find('[data-code-id*="' + id_copy_code + '"]').each(function () {
                            $(this).attr('data-code-id', randomnumber);
                        });
                        new_sel.find('[name*="access"],[name*="ban"],[name*="codes"],[name*="rule"],[name*="settings"],[name*="template"]').each(function () {
                            var tmps = $(this).attr('name').replace(id_copy_code, randomnumber);
                            $(this).attr('name', tmps);
                        });
                        new_sel.find('.hasDatepicker').each(function () {
                            $(this).removeClass('hasDatepicker').removeAttr('id');
                        });

                        new_sel.find('.hasTimepicker').each(function () {
                            $(this).removeClass('hasTimepicker').removeAttr('id');
                        });
                        new_sel.find('.block_chos,.templates_special_select').each(function () {
                            var new_class = $(this).find('.chosen-select').attr('id');
                            jQuery(this).find('select').removeClass("chosen-select").css("display", "block").next().remove();
                        });

                        $("#code" + randomnumber).addClass('todelete');
                        $("#code" + randomnumber)
                                .clone(false)
                                .addClass("cbnewblock")
                                .attr('id', '')
                                .appendTo("#tab" + rel_id + " .codes");
                        $(".cbnewblock").removeClass('todelete');
                        $('.todelete').remove();
                        $('.cbnewblock').attr('id', 'code' + randomnumber).removeClass('cbnewblock');
                        setCloneChosen(selected_chosens, id_copy_code, randomnumber);
                        $(".block_chos select").chosen();

                        $('.time_start_timetargeting').timepicker({
                            showPeriodLabels: false,
                            hourText: '<?= __('Hours', 'custom-blocks-free'); ?>',
                            minuteText: '<?= __('Minutes', 'custom-blocks-free'); ?>',
                            timeSeparator: ':',
                            nowButtonText: '<?= __('Now', 'custom-blocks-free'); ?>',
                            showNowButton: true,
                            closeButtonText: '<?= __('Close', 'custom-blocks-free'); ?>',
                            showCloseButton: true,
                            deselectButtonText: '<?= __('Cancel', 'custom-blocks-free'); ?>',
                            showDeselectButton: true
                        });
                        $('.time_end_timetargeting').timepicker({
                            showPeriodLabels: false,
                            hourText: '<?= __('Hours', 'custom-blocks-free'); ?>',
                            minuteText: '<?= __('Minutes', 'custom-blocks-free'); ?>',
                            timeSeparator: ':',
                            nowButtonText: '<?= __('Now', 'custom-blocks-free'); ?>',
                            showNowButton: true,
                            closeButtonText: '<?= __('Close', 'custom-blocks-free'); ?>',
                            showCloseButton: true,
                            deselectButtonText: '<?= __('Cancel', 'custom-blocks-free'); ?>',
                            showDeselectButton: true
                        });
                        $('.end_date_timetargeting').datepicker({
                            changeMonth: true,
                            changeYear: true
                        });
                        $('.start_date_timetargeting').datepicker({
                            changeMonth: true,
                            changeYear: true
                        });
                        var selector_bookmark = $("#code" + randomnumber).parents(".tab-content").attr('id');
                        var counter_now = parseInt($("." + selector_bookmark + "-a .counter").html());
                        $(".tab1-a .counter").html(counter_now + 1);

                        $('.colorSelect').ColorPicker({
                            color: '#0000ff',
                            onShow: function (colpkr) {
                                $(colpkr).fadeIn(500);
                                return false;
                            },
                            onHide: function (colpkr) {
                                $(colpkr).fadeOut(500);
                                return false;
                            },
                            onChange: function (hsb, hex, rgb) {
                                var el = $(this).data('colorpicker').el;
                                $(el).val('#' + hex);
                            }
                        });
                        return false;
                    });

                    $('.js_geo select').attr('disabled', 'disabled');
                    $('.place_decor input').attr('disabled', 'disabled');
                    $('.place_decor select').attr('disabled', 'disabled');
                });
            </script>
            <?PHP $this->get_links_for_admin(false); ?>
        </div>
        <?php
    }

    public static function get_decor($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'custom_block_decor_link';
        $id_decor = $wpdb->get_var("SELECT id_decor FROM " . $table . " WHERE id_item='" . (int) $id . "'");
        if (!$id_decor) {
            return false;
        }
        $table = $wpdb->prefix . 'custom_block_decor';
        $decor = $wpdb->get_row("SELECT * FROM " . $table . " WHERE id='" . $id_decor . "'", ARRAY_A);
        if (!$decor) {
            return false;
        }
        return $decor;
    }

    public
            function admin_page_blocks_activate() {
        $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
        foreach ($ids as $id) {
            $block = CustomBlock::findById((int)$id);
            $block->activate();
        }
        wp_redirect("/wp-admin/admin.php?page=custom-blocks");
        echo '<script type="text/javascript">window.location.href="/wp-admin/admin.php?page=custom-blocks"</script>';
        exit;
    }

    public
            function admin_page_blocks_deactivate() {
        $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
        foreach ($ids as $id) {
            $block = CustomBlock::findById((int)$id);
            $block->deactivate();
        }
        wp_redirect("/wp-admin/admin.php?page=custom-blocks");
        echo '<script type="text/javascript">window.location.href="/wp-admin/admin.php?page=custom-blocks"</script>';
        exit;
    }

    function check_upload_size($key_check) {
        $sizes = array(1 => '2341948', 2 => '2179636');
        $max_size = ini_get('upload_max_filesize');
        foreach (array(1 => 'k', 2 => 'm', 3 => 'g') as $key => $value) {
            if ($pos = strripos($max_size, $value)) {
                $return = (int) $max_size * pow(1024, $key);
            }
        }
        if (!isset($return)) {
            $return = (int) $max_size;
        }
        if ($return < $sizes[$key_check]) {
            return false;
        } else {
            return true;
        }
    }

    function error_notice_download() {
        include 'admin_pages/teaser_other.php';
        include 'admin_pages/teaser.php';

        $blocked_plugins = array(
            'adsense-booster-manager/adsense-booster.php' => 'AdSense Booster & Manager',
            'speed-sense/speed-sense.php' => 'AdSense Speed Sense',
            'adsense-box/index.php' => 'Adsense Box',
            'quick-adsense-reloaded/quick-adsense-reloaded.php' => 'AdSense Plugin WP QUADS',
            'advanced-advertising-system/advanced_advertising_system.php' => 'Advanced Advertising System',
            'advanced-ads/advanced-ads.php' => 'Advanced Ads',
            'adrotate/adrotate.php' => 'AdRotate',
            'ad-inserter/ad-inserter.php' => 'Ad Inserter',
            'adsense-in-post-ads-by-oizuled/adsense-inpost-ads.php' => 'AdSense In-Post Ads',
            'adsplacer/adsplacer.php' => "AdsPlace'r",
            'google-publisher/GooglePublisherPlugin.php' => 'Google AdSense',
            'wp-advertize-it/bootstrap.php' => 'WP Advertize It',
            'website-monetization-by-magenet/monetization-by-magenet.php' => 'Website Monetization by MageNet',
            'quick-adsense/quick-adsense.php' => 'Quick Adsense',
        );
        foreach ($blocked_plugins as $path_plugin => $name_plugin) {
            if (is_plugin_active($path_plugin)) {
                ?><div class="error">
                    <p><?= __('There is a conflict of ', 'custom-blocks-free'); ?><b><?= __('Custom Advert Blocks', 'custom-blocks-free'); ?></b> <?= __('and', 'custom-blocks-free'); ?> <b><?= $name_plugin; ?></b>.<br> <?= __('Please, deactivate the plugin', 'custom-blocks-free'); ?> <b><?= $name_plugin; ?></b> </p>
                </div><?PHP
            }
        }
    }

    static function get_templates_info($code_id, $full = true) {
        global $wpdb;
        $selected = $wpdb->get_col("SELECT template_id FROM " . $wpdb->prefix . "custom_block_template_meta WHERE meta_value='" . $code_id . "' AND meta_key='include_block';");

        if ($full) {
            $list = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "custom_block_template;");
            if ($list) {
                $returned = array();
                foreach ($list as $listing) {
                    $select = false;
                    if (in_array($listing->id, $selected)) {
                        $select = true;
                    }
                    $returned[$listing->id] = array(
                        'id' => $listing->id,
                        'name' => trim($listing->name),
                        'selected' => $select
                    );
                }
                return $returned;
            }
        } else {
            if (count($selected)) {
                return count($selected);
            }
        }
    }

    public
            function admin_page_blocks_delete() {
        $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
        foreach ($ids as $id) {
            $block = CustomBlock::findById((int)$id);
            $block->delete();
        }
        wp_redirect("/wp-admin/admin.php?page=custom-blocks");
        echo '<script type="text/javascript">window.location.href="/wp-admin/admin.php?page=custom-blocks"</script>';
        exit;
    }

    function get_count_of_block($block_id) {
        global $wpdb;
        return (int) $wpdb->get_var('SELECT count(id) FROM ' . $wpdb->prefix . 'custom_block_item WHERE block_id=' . (int) $block_id);
    }

    /**
     * Админка плагина / сраница блоков
     */
    public
            function admin_page_blocks() {
        switch (@$_REQUEST['action']) {
            case "download_database":
                return $this->admin_page_blocks_download_database();
            case "download_sql":
                return $this->admin_page_blocks_download_sql();
            case "update":
                return $this->admin_page_blocks_update();
            case "copy":
                return $this->admin_page_blocks_copy();
            case "activate":
                return $this->admin_page_blocks_activate();
            case "deactivate":
                return $this->admin_page_blocks_deactivate();
            case "delete":
                return $this->admin_page_blocks_delete();
        }


        $filter = 'all';
        switch (@$_REQUEST['filter']) {
            case "active":
            case "inactive":
                $filter = $_REQUEST['filter'];
                break;
        }

        $blocks = CustomBlock::findAll();

        $counter_active = 0;
        $counter_inactive = 0;

        foreach ($blocks as $block) {
            $block->published ? $counter_active++ : $counter_inactive++;
        }
        ?>
        <script type="text/javascript">
            jQuery(function ($) {
                $('#bulk-action-selector-top').change(function () {
                    $("#bulk-action-selector-bottom").val($(this).val())
                });
                $('#bulk-action-selector-bottom').change(function () {
                    $("#bulk-action-selector-top").val($(this).val())
                });
            });
        </script>
        <div class="wrap">
            <h2><?= __('Custom Blocks', 'custom-blocks-free'); ?>
                <a href="admin.php?page=custom-blocks&action=update&id=0" class="add-new-h2"><?= __('Add New', 'custom-blocks-free'); ?></a><?PHP $this->get_links_for_admin(false); ?>
            </h2>
            <ul class="subsubsub">
                <li class="all"><a
                        href="admin.php?page=custom-blocks" <?= ($filter == "all" ? 'class="current"' : "") ?>><?= __('All', 'custom-blocks-free'); ?> <span
                            class="count">(<?= count($blocks) ?>)</span></a> |
                </li>
                <li class="active"><a
                        href="admin.php?page=custom-blocks&filter=active" <?= ($filter == "active" ? 'class="current"' : "") ?>><?= __('Active', 'custom-blocks-free'); ?>
                        <span class="count">(<?= $counter_active ?>)</span></a> |
                </li>
                <li class="inactive"><a
                        href="admin.php?page=custom-blocks&filter=inactive" <?= ($filter == "inactive" ? 'class="current"' : "") ?>><?= __('Inactive', 'custom-blocks-free'); ?>
                        <span class="count">(<?= $counter_inactive ?>)</span></a></li>
            </ul>
            <form method="post">
                <input type="hidden" name="filter" value="<?= $filter ?>">
                <?php wp_nonce_field(); ?>
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector-top" class="screen-reader-text"><?= __('Choose a bulk action', 'custom-blocks-free'); ?></label>
                        <select name="action" id="bulk-action-selector-top" data-cip-id="cIPJQ342845639">
                            <option value="-1" selected="selected"><?= __('Bulk Actions', 'custom-blocks-free'); ?></option>
                            <option value="activate"><?= __('Activate', 'custom-blocks-free'); ?></option>
                            <option value="deactivate"><?= __('Deactivate', 'custom-blocks-free'); ?></option>
                            <option value="delete"><?= __('Delete', 'custom-blocks-free'); ?></option>
                        </select>
                        <input type="submit" name="" id="doaction" class="button action" value="<?= __('Apply', 'custom-blocks-free'); ?>">
                        <a class="button" onclick="jQuery('.close-spoiler').trigger('click');"><?= __('Show All', 'custom-blocks-free'); ?><a/>
                            <a class="button" onclick="jQuery('.open-spoiler').trigger('click');"><?= __('Hide All', 'custom-blocks-free'); ?><a/>
                                </div>
                                </div>
                                <table class="wp-list-table widefat plugins">
                                    <thead>
                                        <tr>
                                            <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                                                <label class="screen-reader-text" for="cb-select-all-1"><?= __('Select All', 'custom-blocks-free'); ?></label>
                                                <input id="cb-select-all-1" type="checkbox">
                                            </th>
                                            <th scope="col" id="name" class="manage-column column-name" style=""><?= __('Title', 'custom-blocks-free'); ?></th>
                                            <th scope="col" id="description" class="manage-column column-description" style=""><?= __('Status', 'custom-blocks-free'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        global $wpdb;
                                        foreach ($blocks as $block) {
                                            if ($filter == 'active' && !$block->published)
                                                continue;
                                            if ($filter == 'inactive' && $block->published)
                                                continue;
                                            ?>
                                            <tr id="cuty-code-blocks" class="inactive">
                                                <th scope="row" class="check-column">
                                                    <input type="checkbox" name="id[]" value="<?= $block->id ?>"
                                                           id="checkbox_<?= $block->id ?>">
                                                </th>
                                                <td class="plugin-title">
                                                    <strong><b><?= $block->title ?></b> (<?= $this->get_count_of_block($block->id); ?>)</strong>

                                                    <div class="row-actions visible">
                                                        <span class="edit"><a
                                                                href="admin.php?page=custom-blocks&action=update&id=<?= $block->id ?>"
                                                                title="<?= __('Edit Block', 'custom-blocks-free'); ?>" class="edit"><?= __('Edit', 'custom-blocks-free'); ?></a> | </span>
                                                            <?php if ($block->published) { ?>
                                                            <span class="deactivate"><a
                                                                    href="admin.php?page=custom-blocks&filter=<?= $filter ?>&action=deactivate&id[]=<?= $block->id ?>"
                                                                    title="<?= __('Deactivate Block', 'custom-blocks-free'); ?>" class="edit"><?= __('Deactivate', 'custom-blocks-free'); ?></a> | </span>
                                                            <?php } else { ?>
                                                            <span class="activate"><a
                                                                    href="admin.php?page=custom-blocks&filter=<?= $filter ?>&action=activate&id[]=<?= $block->id ?>"
                                                                    title="<?= __('Activate Block', 'custom-blocks-free'); ?>" class="edit"><?= __('Activate', 'custom-blocks-free'); ?></a> | </span>
                                                            <?php } ?>
                                                        <span class="delete"><a
                                                                onclick="return confirm('<?= __('Are you sure you want to delete this block?', 'custom-blocks-free'); ?>')"
                                                                href="admin.php?page=custom-blocks&filter=<?= $filter ?>&action=delete&id[]=<?= $block->id ?>"
                                                                title="<?= __('Delete Block', 'custom-blocks-free'); ?>" class="delete"><?= __('Delete', 'custom-blocks-free'); ?></a> |
                                                        </span>
                                                        <span class="copy"><a
                                                                href="admin.php?page=custom-blocks&action=copy&id=<?= $block->id ?>"
                                                                title="<?= __('Copy Block', 'custom-blocks-free'); ?>" class="copy"><?= __('Copy Block', 'custom-blocks-free'); ?></a>
                                                        </span><br><span style="color: black;">
                                                            <?= __('Shortcode for using within WordPress', 'custom-blocks-free'); ?>:
                                                            <code><span>[</span>block id="<?= $block->id; ?>"<span>]</span></code><br>
                                                            <?= __('Shortcode for using inside your theme', 'custom-blocks-free'); ?>:
                                                            <code>&lt;?= do_shortcode('[block id="<?= $block->id; ?>"]');?></code><br>
                                                            <?PHP
                                                            $this->get_rules_to_ads($block->id);
                                                            ?>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="column-description desc">
                                                    <?php
                                                    if ($block->published) {
                                                        echo '<span style="color:green;">' . __('Activated', 'custom-blocks-free') . '</span><br>';
                                                    } else {
                                                        echo '<span style="color:red;">' . __('Deactivated', 'custom-blocks-free') . '</span><br>';
                                                    }
                                                    if ($block->sync) {
                                                        echo '<span>' . __('Synchronous', 'custom-blocks-free') . '</span>';
                                                    } else {
                                                        echo '<span>' . __('Asynchronous', 'custom-blocks-free') . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th scope="col" class="manage-column column-cb check-column" style="">
                                                <label class="screen-reader-text" for="cb-select-all-2"><?= __('Select All', 'custom-blocks-free'); ?></label>
                                                <input id="cb-select-all-2" type="checkbox">
                                            </th>
                                            <th scope="col" class="manage-column column-name" style=""><?= __('Title', 'custom-blocks-free'); ?></th>
                                            <th scope="col" class="manage-column column-description" style=""><?= __('Status', 'custom-blocks-free'); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="tablenav bottom">
                                    <div class="alignleft actions bulkactions">
                                        <label for="bulk-action-selector-top" class="screen-reader-text"><?= __('Choose a bulk action', 'custom-blocks-free'); ?></label>
                                        <select name="action" id="bulk-action-selector-bottom" data-cip-id="cIPJQ342845639">
                                            <option value="-1" selected="selected"><?= __('Bulk Actions', 'custom-blocks-free'); ?></option>
                                            <option value="activate"><?= __('Activate', 'custom-blocks-free'); ?></option>
                                            <option value="deactivate"><?= __('Deactivate', 'custom-blocks-free'); ?></option>
                                            <option value="delete"><?= __('Delete', 'custom-blocks-free'); ?></option>
                                        </select>
                                        <input type="submit" name="" id="doaction" class="button action" value="<?= __('Apply', 'custom-blocks-free'); ?>">
                                    </div>
                                </div>
                                </form>
            <?PHP $this->get_links_for_admin(false); ?>
                                </div>
                                <script>
                                    jQuery(document).ready(function ($) {
                                        $('.js-custom-spoiler').click(function () {
                                            var now_action = $(this).children('ul.list_params_custom').css('display');
                                            if (now_action === 'none')
                                            {
                                                $(this).children('ul.list_params_custom').show(300);
                                                $(this).children('p').removeClass("close-spoiler").addClass("open-spoiler").html('<?= __('Hide', 'custom-blocks-free'); ?>');
                                            } else {
                                                $(this).children('ul.list_params_custom').hide(300);
                                                $(this).children('p').removeClass("open-spoiler").addClass("close-spoiler").html('<?= __('Show', 'custom-blocks-free'); ?>');
                                            }
                                        });
                                    });
                                </script>
                                <?php
                            }

                            function stripslashes_deep($value) {
                                $value = is_array($value) ?
                                        array_map('stripslashes_deep', $value) :
                                        stripslashes($value);

                                return $value;
                            }

                            function adding_ads_to_thumb($html) {
                                $before = '';
                                $after = '';
                                $blocks = get_option('cb_blocks', false);
                                if ($blocks) {
                                    foreach ($blocks as $block) {
                                        if (isset($block['show']) && isset($block['show_type'])) {
                                            if ($block['show_type'] == '17') {
                                                if (isset($block['noindex']) && $block['noindex'] == 'on') {
                                                    $before.='<!--noindex-->' . $block['code'] . '<!--/noindex-->';
                                                } else {
                                                    $before.=$block['code'];
                                                }
                                            }
                                            if ($block['show_type'] == '18') {
                                                if (isset($block['noindex']) && $block['noindex'] == 'on') {
                                                    $after.='<!--noindex-->' . $block['code'] . '<!--/noindex-->';
                                                } else {
                                                    $after.=$block['code'];
                                                }
                                            }
                                        }
                                    }
                                }
                                return do_shortcode($before . ' ' . $html . ' ' . $after);
                            }

                            function get_from_text_block($text) {
                                preg_match_all('/\[block id="([\d]{1,})"\]/', $text, $matches);
                                if (isset($matches[1]) && $matches[1]) {
                                    global $wpdb;
                                    $blocks = array();
                                    foreach ($matches[1] as $match) {
                                        $name_block = "";
                                        $tmp_query = $wpdb->get_var("SELECT title FROM " . $wpdb->prefix . "custom_block WHERE id='" . $match . "'");
                                        if ($tmp_query) {
                                            $name_block = ' - ' . $tmp_query;
                                        }
                                        $blocks[] = __('Block №', 'custom-blocks-free') . $match . $name_block;
                                    }
                                    return '<li>' . implode('</li><li>', $blocks) . '</li>';
                                }
                                return __('No blocks yet', 'custom-blocks-free');
                            }

                            /**
                             * Админка плагина / страница настроек
                             */
                            public function admin_page_settings() {
                                if (isset($_POST['cb_blocks'])) {
                                    foreach (array('cb_show_for', 'cb_blocks') as $value_type) {
                                        if (isset($_POST[$value_type])) {
                                            $options[$value_type] = sanitize_text_field(stripslashes_deep($_POST[$value_type]));
                                            update_option(sanitize_text_field($value_type), sanitize_text_field($options[$value_type]));
                                        } else {
                                            delete_option(sanitize_text_field($value_type));
                                        }
                                    }
                                }
                                if (isset($_POST['doaction']) && isset($_POST['action']) && isset($_POST['id'])) {
                                    CustomBlocksPlugin::input_blocks_process(sanitize_text_field($_POST['action']), (int)$_POST['id']);
                                }
                                $options = $this->get_options();
                                $available_post_types = array();

                                foreach (get_post_types(array(), 'objects') as $post_type => $name) {
                                    if (!in_array($post_type, array('nav_menu_item', 'revision', 'attachment'))) {
                                        $available_post_types[$post_type] = $name->labels->name;
                                    }
                                }
                                ?>
                                <div class="wrap">
                                    <h2><?= __("Output Settings", 'custom-blocks-free') ?><?PHP $this->get_links_for_admin(); ?></h2>
                                    <?php if (isset($_GET['settings-updated'])) { ?>
                                        <div id="message" class="updated">
                                            <p><strong><?php _e('Settings saved.', 'custom-blocks-free') ?></strong></p>
                                        </div>
                                    <?php } ?>

                                    <?php if (!function_exists('mb_strlen')) { ?>
                                        <p class="description" style="color: red;"><?= __('PHP mbstring must be activated for the right work of the plugin.', 'custom-blocks-free'); ?></p>
                                    <?php } ?>
                                    </p>
                                    <?PHP
                                    if (isset($_POST['block_settings_save'])) {
                                        if (isset($_POST['cb_blockquote'])) {
                                            if (get_option('cb_blockquote')) {
                                                update_option('cb_blockquote', sanitize_text_field($_POST['cb_blockquote']));
                                            } else {
                                                add_option('cb_blockquote', sanitize_text_field($_POST['cb_blockquote']), '', 'yes');
                                            }
                                        } else {
                                            if (get_option('cb_blockquote')) {
                                                delete_option('cb_blockquote');
                                            }
                                        }
                                    }
                                    $cb_blockquote = (get_option('cb_blockquote')) ? 'checked' : '';
                                    $cb_expertmode = (get_option('cb_expertmode')) ? 'checked' : '';
                                    ?>
                                    <form action="" method="post" class="form-count-blocks">
                                        <table class="form-table">
                                            <tbody>
                                                <tr valign="top"> 
                                                    <th><?= __('Number of Blocks', 'custom-blocks-free'); ?>:</th>
                                                    <td>
                                                        <input type="number" min="1" max="100" disabled="" value="3"> <a class="pro_link_settings" target="_blank" href="/wp-admin/admin.php?page=custom-blocks-tariff"><b><?= __('Available in Premium Version', 'custom-blocks-free'); ?></b></a>
                                                    </td>
                                                </tr>
                                                <tr valign="top">
                                                    <th><?= __('Count blockquotes with paragraphs', 'custom-blocks-free'); ?>:<br>
                                                        <?= __('(Blockquote must not have paragraphs inside)', 'custom-blocks-free'); ?>
                                                    </th>
                                                    <td>
                                                        <input type="checkbox" name="cb_blockquote" <?= $cb_blockquote; ?>>
                                                    </td>
                                                </tr>
                                                <tr valign="top">
                                                    <th><?= __('Custom Ad ID', 'custom-blocks-free'); ?>:<br>
                                                        <span style="font-size: 12px; font-weight: normal; font-style: italic;"><?= __('The ID may contain: letters, numbers and symbol _', 'custom-blocks-free'); ?><br>
                                                            <?= __('The first character may not be a number.', 'custom-blocks-free'); ?></span>
                                                    </th>
                                                    <td>
                                                        <input type="name" value="<?= get_option('cb_functionname'); ?>" disabled=""> <a class="pro_link_settings" target="_blank" href="/wp-admin/admin.php?page=custom-blocks-tariff"><b><?= __('Available in Premium Version', 'custom-blocks-free'); ?></b></a>
                                                    </td>
                                                </tr>
                                                <tr valign="top">
                                                    <th><?= __('Watermark', 'custom-blocks-free'); ?>:<br>
                                                        <span style="font-size: 12px; font-weight: normal; font-style: italic;"><?= __('Default watermark to use if nothing is uploaded', 'custom-blocks-free'); ?></span>
                                                    </th>
                                                    <td>
                                                        <input disabled="" class="cb_js_watermark" placeholder="<?= __('Image URL', 'custom-blocks-free'); ?>" type="name" value="<?= get_option('cb_watermark', ''); ?>"> <input disabled="" class="cb_js_watermark_button" type="button" value="Выбрать">  <a class="pro_link_settings" href="/wp-admin/admin.php?page=custom-blocks-tariff"><b><?= __('Available in Premium Version', 'custom-blocks-free'); ?></b></a>
                                                    </td>
                                                </tr>
                                                <tr valign="top">
                                                    <th><?= __('Expert Mode', 'custom-blocks-free'); ?>:<br>
                                                        <span style="font-size: 12px; font-weight: normal; font-style: italic;"><?= __('Using any php-code inside blocks, like <br><code>&lt;?php CODE HERE ?></code>', 'custom-blocks-free'); ?></span>
                                                    </th>
                                                    <td>
                                                        <input type="checkbox" disabled="" <?= $cb_expertmode; ?>> <a class="pro_link_settings" target="_blank" href="/wp-admin/admin.php?page=custom-blocks-tariff"><b><?= __('Available in Premium Version', 'custom-blocks-free'); ?></b></a>
                                                    </td>
                                                </tr>
                                                <tr valign="top">
                                                    <td><?PHP submit_button(__('Save block settings', 'custom-blocks-free'), "primary", "block_settings_save"); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </form>
                                    <hr>
                                    <form method="post" action="" novalidate>

                                        <?php settings_fields('custom-blocks-settings-group') ?>
                                        <input type="hidden" name="action" value="update"/>
                                        <input type="hidden" name="page_options" value="cb_show_for,cb_blocks"/>
                                        <?php submit_button(__('Save output settings', 'custom-blocks-free')); ?>
                                        <table class="form-table">
                                            <tr valign="top">
                                                <th scope="row"><?= __('Output in post types:', 'custom-blocks-free') ?></th>
                                                <td>
                                                    <?php foreach ($available_post_types as $type => $name) { ?>
                                                        <input type="checkbox"
                                                               name="cb_show_for[<?= $type ?>]"
                                                               value="<?= $type ?>"
                                                               id="post-type-<?= $type ?>"
                                                               <?php if (isset($options['cb_show_for'][$type])): ?> checked <?php endif; ?>
                                                               />
                                                        <label for="post-type-<?= $type ?>"><?= $name ?></label><br/>
                                                    <?php } ?>
                                                    <p class="description"><?= __('Choose post types, where you wish to output blocks automatically.', 'custom-blocks-free'); ?></p>
                                                </td>
                                            </tr>
                                        </table>
                                        <div class="tablenav top">
                                            <div class="alignleft actions bulkactions">
                                                <label for="bulk-action-selector-top" class="screen-reader-text"><?= __('Choose a bulk action', 'custom-blocks-free'); ?></label>
                                                <select name="action">
                                                    <option value="-1" selected="selected"><?= __('Bulk Actions', 'custom-blocks-free'); ?></option>
                                                    <option value="activate"><?= __('Activate', 'custom-blocks-free'); ?></option>
                                                    <option value="deactivate"><?= __('Deactivate', 'custom-blocks-free'); ?></option>
                                                    <option value="delete"><?= __('Delete', 'custom-blocks-free'); ?></option>
                                                </select>
                                                <input type="submit" name="doaction" id="doaction" class="button action" value="<?= __('Apply', 'custom-blocks-free'); ?>">
                                                <a class="button" onclick="jQuery('.code-editing').show(400);"><?= __('Show All', 'custom-blocks-free'); ?></a>
                                                <a class="button" onclick="jQuery('.code-editing').hide(400);"><?= __('Hide All', 'custom-blocks-free'); ?></a></div></div>
                                        <table class="wp-list-table widefat plugins">
                                            <thead>
                                                <tr>
                                                    <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                                                        <label class="screen-reader-text" for="cb-select-all-1"><?= __('Select All', 'custom-blocks-free'); ?></label>
                                                        <input id="cb-select-all-1" type="checkbox">
                                                    </th>
                                                    <th scope="col" id="name" class="manage-column column-name" style=""><?= __('Title', 'custom-blocks-free'); ?></th>
                                                    <th scope="col" id="description" class="manage-column column-description" style=""><?= __('Status', 'custom-blocks-free'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $after = __('the beginning of the text', 'custom-blocks-free');
                                                for ($i = 1; $i <= bindec('11') + 1; $i++) {
                                                    if ($i > 1)
                                                        $after = __('the previous block', 'custom-blocks-free');
                                                    ?>
                                                    <tr class="inactive" valign="top" <?= (3 < $i) ? 'style="display:none;"' : ''; ?> >
                                                        <th scope="row" class="check-column">
                                                            <input type="checkbox" name="id[]" value="<?= $i; ?>" id="checkbox_<?= $i; ?>">
                                                        </th>
                                                        <th scope="row" class="plugin-title">
                                                            <strong>
                                                                <b><?= __('Block', 'custom-blocks-free'); ?> №<?= $i; ?></b> <br><?= $this->get_from_text_block(@$options['cb_blocks'][$i]['code']); ?>
                                                            </strong>
                                                <div class="row-actions visible">
                                                    <span class="edit"><a class="edit buttons-inputing edit-code-block" data-id="<?= $i; ?>"><?= __('Edit', 'custom-blocks-free'); ?></a> |</span>
                                                    <span class="edit">
                                                        <?= (@isset($options['cb_blocks'][$i]['show'])) ? '<a class="edit buttons-inputing deactivate-code-block" data-id="' . $i . '">' . __('Deactivate', 'custom-blocks-free') . '</a>' : '<a class="edit buttons-inputing activate-code-block" data-id="' . $i . '">' . __('Activate', 'custom-blocks-free') . '</a>'; ?>
                                                    </span>
                                                </div>
                                                <div style="display:none;" id="code-editing-<?= $i; ?>" class="code-editing">
                                                    <div class="cb-code-info">
                                                        <label for="cb-blocks-code-<?= $i; ?>">
                                                            <?= __('Code', 'custom-blocks-free'); ?>
                                                        </label>
                                                        <br/>
                                                        <div  id="code-edit-<?= $i ?>">
                                                            <textarea style="width: 400px; height: 100px;" id="cb-blocks-code-<?= $i ?>"
                                                                      name="cb_blocks[<?= $i ?>][code]"><?= @$options['cb_blocks'][$i]['code'] ?></textarea>
                                                        </div>
                                                        <p>
                                                        <div style="display: none;">
                                                            <input 
                                                                <?php if (@isset($options['cb_blocks'][$i]['show'])): ?>checked<?php endif; ?>
                                                                id="cb-blocks-show-<?php echo $i; ?>" type="checkbox"
                                                                name="cb_blocks[<?php echo $i; ?>][show]" value="1"/>
                                                            <label for="cb-blocks-show-<?php echo $i; ?>"><?= __('Active', 'custom-blocks-free'); ?></label></div>
                                                        <span class="js_selecting" data-id-select="<?= $i; ?>">
                                                            <select name="cb_blocks[<?php echo $i; ?>][show_type]">
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 2): ?>selected<?php endif; ?>
                                                                    value="2"><?= __('After N Paragraphs', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 5): ?>selected<?php endif; ?>
                                                                    value="5"><?= __('After the Post Title', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 4): ?>selected<?php endif; ?>
                                                                    value="4"><?= __('In the End of the Post', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 15): ?>selected<?php endif; ?>
                                                                    value="15"><?= __('In the Middle of the Post', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 1): ?>selected<?php endif; ?>
                                                                    value="1" disabled=""><?= __('After N Characters', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 3): ?>selected<?php endif; ?>
                                                                    value="3"  disabled=""><?= __('Before the Last Paragraph', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 6): ?>selected<?php endif; ?>
                                                                    value="6" disabled=""><?= __('Before an Image Inside the Text', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 7): ?>selected<?php endif; ?>
                                                                    value="7" disabled=""><?= __('After an Image Inside the Text', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 8): ?>selected<?php endif; ?>
                                                                    value="8" disabled=""><?= __('Image Hover', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 16): ?>selected<?php endif; ?>
                                                                    value="16" disabled=""><?= __('Image Hover (rotation)', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 9): ?>selected<?php endif; ?>
                                                                    value="9" disabled=""><?= __('After TOC+', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 10): ?>selected<?php endif; ?>
                                                                    value="10" disabled=""><?= __('Floating Window', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 11): ?>selected<?php endif; ?>
                                                                    value="11" disabled=""><?= __('Pop-up Window', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 12): ?>selected<?php endif; ?>
                                                                    value="12" disabled=""><?= __('After Headings', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 13): ?>selected<?php endif; ?>
                                                                    value="13" disabled=""><?= __('N characters after the end of the text', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 14): ?>selected<?php endif; ?>
                                                                    value="14" disabled=""><?= __('N paragraphs after the end of the text', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>

                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 17): ?>selected<?php endif; ?>
                                                                    value="17" disabled=""><?= __('Before the Thumbnail', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>
                                                                <option
                                                                    <?php if (@$options['cb_blocks'][$i]['show_type'] == 18): ?>selected<?php endif; ?>
                                                                    value="18" disabled=""><?= __('After the Thumbnail', 'custom-blocks-free'); ?> - <?= __('Available in Premium Version', 'custom-blocks-free'); ?>
                                                                </option>

                                                            </select>
                                                        </span>


                                                        <span class="n_style" id="<?= $i; ?>">&nbsp; N = <input type="text"
                                                                                                                style="width: 50px;"
                                                                                                                name="cb_blocks[<?= $i; ?>][show_value]"
                                                                                                                value="<?= @$options['cb_blocks'][$i]['show_value']; ?>"/><br>
                                                        </span>
                                                        <span class="wm_style" id="wm-<?= $i; ?>"> 
                                                            <br>    
                                                            <?= __('Use watermark', 'custom-blocks-free'); ?>
                                                            <input type="checkbox"  name="cb_blocks[<?= $i; ?>][wm]" <?= (@$options['cb_blocks'][$i]['wm'] == 'on') ? 'checked' : ''; ?>  />
                                                        </span>
                                                        <span class="link_style" id="link-<?= $i; ?>"><br><?= __('Link', 'custom-blocks-free'); ?><input type="text"
                                                                                                                                                           name="cb_blocks[<?= $i; ?>][link]"
                                                                                                                                                           value="<?= @$options['cb_blocks'][$i]['link']; ?>"/>
                                                        </span>

                                                        <span class="all_select_style" id="all-image-<?= $i; ?>"> 
                                                            <br>    
                                                            <?= __('For all', 'custom-blocks-free'); ?>
                                                            <input type="checkbox"  name="cb_blocks[<?= $i; ?>][show_value_all]" <?= (@$options['cb_blocks'][$i]['show_value_all'] == 'on') ? 'checked' : ''; ?>  />
                                                        </span>
                                                        <span class="speed_style" id="speed-<?= $i; ?>"><br><?= __('Speed in milliseconds', 'custom-blocks-free'); ?> = <input type="number"
                                                                                                                                                                                  min="200" max="1000"
                                                                                                                                                                                  style="width: 50px;"
                                                                                                                                                                                  name="cb_blocks[<?= $i; ?>][speed]"
                                                                                                                                                                                  value="<?= (@$options['cb_blocks'][$i]['speed']) ? $options['cb_blocks'][$i]['speed'] : '300'; ?>"/><br>
                                                            <?= __('Show the close button', 'custom-blocks-free'); ?><input type="checkbox" name="cb_blocks[<?= $i; ?>][cross]" <?= (@$options['cb_blocks'][$i]['cross']) ? 'checked' : ''; ?>/>

                                                        </span>
                                                        <span class="h_style" id="h-<?= $i; ?>"><br><?= __('Level', 'custom-blocks-free'); ?> 
                                                            <select style="width: 50px;" name="cb_blocks[<?= $i; ?>][level_h]">
                                                                <?PHP
                                                                for ($h_elem = 2; $h_elem <= 6; $h_elem++) {
                                                                    if (@$options['cb_blocks'][$i]['level_h'] == $h_elem) {
                                                                        $selected = ' selected';
                                                                    } else {
                                                                        $selected = '';
                                                                    }
                                                                    echo '<option value="' . $h_elem . '"' . $selected . '>H' . $h_elem . '</option>';
                                                                }
                                                                ?>
                                                            </select>
                                                        </span>
                                                        <span class="pop_style" id="pop-<?= $i; ?>">
                                                            <br>
                                                            <?= __('Show the window', 'custom-blocks-free'); ?> 
                                                            <select id="pop-type-<?= $i; ?>" name="cb_blocks[<?= $i; ?>][pop_show]">
                                                                <?PHP
                                                                foreach (array('start' => __('On page load', 'custom-blocks-free'), 'nsec' => __('After N seconds', 'custom-blocks-free')) as $pop_key => $pop_value) {
                                                                    if (@$options['cb_blocks'][$i]['pop_show'] == $pop_key) {
                                                                        $selected = ' selected';
                                                                    } else {
                                                                        $selected = '';
                                                                    }
                                                                    echo '<option value="' . $pop_key . '"' . $selected . '>' . $pop_value . '</option>';
                                                                }
                                                                ?>
                                                            </select>
                                                            <br>
                                                            <?= __('Show in N seconds', 'custom-blocks-free'); ?> 
                                                            <input type="number" min="0" max="60" style="width: 50px;" name="cb_blocks[<?= $i; ?>][nsec]" value="<?= (@$options['cb_blocks'][$i]['nsec']) ? $options['cb_blocks'][$i]['nsec'] : '5'; ?>"/>
                                                            <br>
                                                            <?= __('Opacity (from 0.1 to 1.0)', 'custom-blocks-free'); ?> 
                                                            <input type="text" style="width: 50px;" name="cb_blocks[<?= $i; ?>][opacity]" value="<?= (@$options['cb_blocks'][$i]['opacity']) ? $options['cb_blocks'][$i]['opacity'] : '1'; ?>"/>
                                                            <br><?= __('How many times to show it to the same user', 'custom-blocks-free'); ?><br>
                                                            <input type="radio" style="width: 16px;" name="cb_blocks[<?= $i; ?>][pop_showing][type]" <?= (@$options['cb_blocks'][$i]['pop_showing']['type'] == 'always') ? 'checked' : ''; ?>  value="always"/> <?= __('Always', 'custom-blocks-free'); ?><br>
                                                            <input type="radio" style="width: 16px;" name="cb_blocks[<?= $i; ?>][pop_showing][type]" <?= (@$options['cb_blocks'][$i]['pop_showing']['type'] == 'n') ? 'checked' : ''; ?> value="n"/><input type="number" min="0" style="width: 50px;" name="cb_blocks[<?= $i; ?>][pop_showing][n]" value="<?= (@$options['cb_blocks'][$i]['pop_showing']['n']) ? $options['cb_blocks'][$i]['pop_showing']['n'] : ''; ?>"> <?= __('times during the first visit', 'custom-blocks-free'); ?><br>
                                                        </span>
                                                        <span class="margins_style" id="margin-<?= $i; ?>"><br><?= __('Margins (in pixels):', 'custom-blocks-free'); ?> 
                                                            <?PHP
                                                            foreach (array(
                                                        'up' => __('Top', 'custom-blocks-free'),
                                                        'right' => __('Right', 'custom-blocks-free'),
                                                        'down' => __('Bottom', 'custom-blocks-free'),
                                                        'left' => __('Left', 'custom-blocks-free')) as $margin_key => $margin_value) {
                                                                ?>
                                                                <br><input type="text" style="width: 50px;" name="cb_blocks[<?= $i; ?>][margins][<?= $margin_key; ?>]" value="<?php echo @$options['cb_blocks'][$i]['margins'][$margin_key]; ?>"/>
                                                                <?PHP
                                                                echo $margin_value;
                                                            }
                                                            ?>
                                                        </span>

                                                        <span class="wheel_window"  id="wheel-<?= $i; ?>">
                                                            <br><?= __('Float', 'custom-blocks-free'); ?><br> 
                                                            <?php $wheel_pos = (@$options['cb_blocks'][$i]['wheel']['position'] == 'right') ? 'right' : 'left'; ?>
                                                            <input type="radio" style="width: 16px;" name="cb_blocks[<?= $i; ?>][wheel][position]" <?= ($wheel_pos == 'left') ? 'checked' : ''; ?> value="left"/> <?= __('From the left', 'custom-blocks-free'); ?>
                                                            <input type="radio" style="width: 16px;" name="cb_blocks[<?= $i; ?>][wheel][position]" <?= ($wheel_pos == 'right') ? 'checked' : ''; ?> value="right"/> <?= __('Right', 'custom-blocks-free'); ?><br>
                                                            <select name="cb_blocks[<?= $i; ?>][wheel][type]">
                                                                <option <?php if (@$options['cb_blocks'][$i]['wheel']['type'] == 1): ?>selected<?php endif; ?> value="1"><?= __('In the End of the Post', 'custom-blocks-free'); ?></option>
                                                                <option <?php if (@$options['cb_blocks'][$i]['wheel']['type'] == 2): ?>selected<?php endif; ?> value="2"><?= __('In N Characters', 'custom-blocks-free'); ?></option>
                                                                <option <?php if (@$options['cb_blocks'][$i]['wheel']['type'] == 3): ?>selected<?php endif; ?> value="3"><?= __('In N paragraphs', 'custom-blocks-free'); ?></option>
                                                            </select>
                                                            N=<input type="number" min="0" style="width: 50px;" name="cb_blocks[<?= $i; ?>][wheel][n]" value="<?= @$options['cb_blocks'][$i]['wheel']['n']; ?>"/>
                                                        </span>

                                                        <br/>
                                                        <?= __('If the post length is more than', 'custom-blocks-free'); ?>
                                                        <input type="text" style="width: 50px;"
                                                               name="cb_blocks[<?= $i; ?>][total_chars_count]"
                                                               value="<?= @$options['cb_blocks'][$i]['total_chars_count']; ?>"/>
                                                        <?= __('characters.', 'custom-blocks-free'); ?><br>
                                                        <?= __('Wrap in', 'custom-blocks-free'); ?> <code>noindex</code>
                                                        <input type="checkbox"  name="cb_blocks[<?= $i; ?>][noindex]" <?= (@$options['cb_blocks'][$i]['noindex'] == 'on') ? 'checked' : ''; ?>  />
                                                        <input style="display: none;" type="checkbox" name="cb_blocks[<?= $i; ?>][delete]" id="delete-<?= $i; ?>">
                                                        </p>
                                                    </div>
                                                    <div class="cb-shortcode-info"><?php CustomBlocksPlugin::get_shortcode_item(); ?></div>
                                                </div>
                                                </th>
                                                <td class="column-description desc">
                                                    <?= (@isset($options['cb_blocks'][$i]['show'])) ? '<span style="color:green;">' . __('Activated', 'custom-blocks-free') . '</span>' : '<span style="color:red;">' . __('Deactivated', 'custom-blocks-free') . '</span>'; ?>
                                                </td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th scope="col" class="manage-column column-cb check-column">
                                                        <label class="screen-reader-text" for="cb-select-all-2"><?= __('Select All', 'custom-blocks-free'); ?></label>
                                                        <input id="cb-select-all-2" type="checkbox">
                                                    </th>
                                                    <th scope="col" class="manage-column column-name" style=""><?= __('Title', 'custom-blocks-free'); ?></th>
                                                    <th scope="col" class="manage-column column-description" style=""><?= __('Status', 'custom-blocks-free'); ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        <?php submit_button(__('Save output settings', 'custom-blocks-free')); ?>
                                    </form>
<?PHP $this->get_links_for_admin(false); ?>
                                </div>
                                <script>
                                    jQuery(function ($) {
                                        var first_numbers = 0;
                                        var last_number = 0;

                                        $(".js_selecting select").each(function () {
                                            var idSelect = $(this).parents('span').data('idSelect');

                                            if ($(this).val() == 1 || $(this).val() == 2) {
                                                if (first_numbers == 0) {
                                                    last_number = idSelect;
                                                    first_numbers = idSelect;
                                                    $("span.n_style#" + idSelect + " span").html("<?= __('from the beginning of the text', 'custom-blocks-free'); ?>");
                                                } else {
                                                    $("span.n_style#" + idSelect + " span").html("<?= __('from the block', 'custom-blocks-free'); ?> №" + last_number);
                                                    last_number = idSelect;
                                                }
                                            }
                                            //n_style
                                            if (jQuery.inArray($(this).val(), ['1', '2', '6', '7', '8', '12', '13', '14', '16']) != '-1')
                                            {
                                                $("span.n_style#" + idSelect).show();
                                            } else {
                                                $("span.n_style#" + idSelect).hide();
                                            }

                                            //wm_style
                                            if (jQuery.inArray($(this).val(), ['8', '16']) != '-1')
                                            {
                                                $("span.wm_style#wm-" + idSelect).show();
                                            } else {
                                                $("span.wm_style#wm-" + idSelect).hide();
                                            }
                                            if (jQuery.inArray($(this).val(), ['6', '7', '8', '12', '16']) != '-1')
                                            {
                                                $("span.all_select_style#all-image-" + idSelect).show();
                                            } else {
                                                $("span.all_select_style#all-image-" + idSelect).hide();
                                            }
                                            if (jQuery.inArray($(this).val(), ['10', '11']) != '-1')
                                            {
                                                $("span.speed_style#speed-" + idSelect).show();
                                                $("span.margins_style#margin-" + idSelect).show();
                                            } else {
                                                $("span.speed_style#speed-" + idSelect).hide();
                                                $("span.margins_style#margin-" + idSelect).hide();
                                            }

                                            if ($(this).val() == 10) {
                                                $("span.wheel_window#wheel-" + idSelect).show();
                                            } else {
                                                $("span.wheel_window#wheel-" + idSelect).hide();
                                            }

                                            if ($(this).val() == 11) {
                                                $("span.pop_style#pop-" + idSelect).show();
                                            } else {
                                                $("span.pop_style#pop-" + idSelect).hide();
                                            }

                                            if ($(this).val() == 12) {
                                                $("span.h_style#h-" + idSelect).show();
                                            } else {
                                                $("span.h_style#h-" + idSelect).hide();
                                            }
                                            if ($(this).val() == 8) {
                                                $("span.link_style#link-" + idSelect).show();
                                            } else {
                                                $("span.link_style#link-" + idSelect).hide();
                                            }
                                        });

                                        $(".js_selecting select").change(function () {
                                            var first_numbers = 0;
                                            var last_number = 0;
                                            $(".js_selecting select").each(function () {
                                                var idSelect = $(this).parents('span').data('idSelect');

                                                if ($(this).val() == 1 || $(this).val() == 2) {
                                                    if (first_numbers == 0) {
                                                        last_number = idSelect;
                                                        first_numbers = idSelect;
                                                        $("span.n_style#" + idSelect + " span").html("<?= __('from the beginning of the text', 'custom-blocks-free'); ?>");
                                                    } else {
                                                        $("span.n_style#" + idSelect + " span").html("<?= __('from the block', 'custom-blocks-free'); ?> №" + last_number);
                                                        last_number = idSelect;
                                                    }
                                                }
                                                //n_style
                                                if (jQuery.inArray($(this).val(), ['1', '2', '6', '7', '8', '12', '13', '14', '16']) != '-1')
                                                {
                                                    $("span.n_style#" + idSelect).show();
                                                } else {
                                                    $("span.n_style#" + idSelect).hide();
                                                }

                                                if (jQuery.inArray($(this).val(), ['8', '16']) != '-1')
                                                {
                                                    $("span.wm_style#wm-" + idSelect).show();
                                                } else {
                                                    $("span.wm_style#wm-" + idSelect).hide();
                                                }

                                                if (jQuery.inArray($(this).val(), ['6', '7', '8', '12', '16']) != '-1')
                                                {
                                                    $("span.all_select_style#all-image-" + idSelect).show();
                                                } else {
                                                    $("span.all_select_style#all-image-" + idSelect).hide();
                                                }
                                                if (jQuery.inArray($(this).val(), ['10', '11']) != '-1')
                                                {
                                                    $("span.speed_style#speed-" + idSelect).show();
                                                    $("span.margins_style#margin-" + idSelect).show();
                                                } else {
                                                    $("span.speed_style#speed-" + idSelect).hide();
                                                    $("span.margins_style#margin-" + idSelect).hide();
                                                }

                                                if ($(this).val() == 10) {
                                                    $("span.wheel_window#wheel-" + idSelect).show();
                                                } else {
                                                    $("span.wheel_window#wheel-" + idSelect).hide();
                                                }

                                                if ($(this).val() == 11) {
                                                    $("span.pop_style#pop-" + idSelect).show();
                                                } else {
                                                    $("span.pop_style#pop-" + idSelect).hide();
                                                }

                                                if ($(this).val() == 12) {
                                                    $("span.h_style#h-" + idSelect).show();
                                                } else {
                                                    $("span.h_style#h-" + idSelect).hide();
                                                }
                                                if ($(this).val() == 8) {
                                                    $("span.link_style#link-" + idSelect).show();
                                                } else {
                                                    $("span.link_style#link-" + idSelect).hide();
                                                }
                                            });
                                        });
                                    });
                                    jQuery(document).ready(function ($) {
                                        $('.edit-code-block').click(function () {
                                            var id_cb = $(this).data('id');
                                            $('#code-editing-' + id_cb).toggle(400);
                                        });
                                        $('.deactivate-code-block').click(function () {
                                            var id_cb = $(this).data('id');
                                            $.ajax({
                                                type: 'POST',
                                                url: ajax_object.ajax_url,
                                                data: {
                                                    action: 'input_process_block',
                                                    types: 'deactivate',
                                                    id: id_cb
                                                },
                                                success: function (data) {
                                                    location.reload();
                                                },
                                                error: function (xhr, str) {
                                                    alert('<?= __('An error has occurred during the deactivation', 'custom-blocks-free'); ?> ' + str);
                                                },
                                                dataType: 'json'
                                            });
                                            return false;
                                        });

                                        $('.activate-code-block').click(function () {
                                            var id_cb = $(this).data('id');
                                            $.ajax({
                                                type: 'POST',
                                                url: ajax_object.ajax_url,
                                                data: {
                                                    action: 'input_process_block',
                                                    types: 'activate',
                                                    id: id_cb
                                                },
                                                success: function (data) {
                                                    location.reload();
                                                },
                                                error: function (xhr, str) {
                                                    alert('<?= __('An error has occurred during the deactivation', 'custom-blocks-free'); ?> ' + str);
                                                },
                                                dataType: 'json'
                                            });
                                            return false;
                                        });
                                        $('.delete-code-block').click(function () {
                                            var id_cb = $(this).data('id');
                                            if (confirm("<?= __('Are you sure you want to delete this Block?', 'custom-blocks-free'); ?> №" + id_cb + "?")) {
                                                $.ajax({
                                                    type: 'POST',
                                                    url: ajax_object.ajax_url,
                                                    data: {
                                                        action: 'input_process_block',
                                                        types: 'delete',
                                                        id: id_cb
                                                    },
                                                    success: function (data) {
                                                        location.reload();
                                                    },
                                                    error: function (xhr, str) {
                                                        alert('<?= __('An error has occurred during the removal', 'custom-blocks-free'); ?> ' + str);
                                                    },
                                                    dataType: 'json'
                                                });
                                            }
                                            return false;
                                        });
                                    });
                                </script>
                                <?php
                            }

                            public static function input_blocks_process($action, $ids) {
                                $cb_blocks = get_option('cb_blocks');
                                if (!is_array($ids)) {
                                    $id = $ids;
                                    $ids = array($id);
                                }
                                foreach ($ids as $value) {
                                    switch ($action) {
                                        case 'activate':
                                            $cb_blocks[$value]['show'] = 1;
                                            break;
                                        case 'deactivate':
                                            if (isset($cb_blocks[$value]['show'])) {
                                                unset($cb_blocks[$value]['show']);
                                            }
                                            break;
                                    }
                                }
                                update_option('cb_blocks', array_combine(range(1, count($cb_blocks)), array_values($cb_blocks)));
                                return 'ok';
                            }

                            public function get_terms_by_taxonomy() {
                                $list = array();
                                if (isset($_POST['tax']) && $_POST['tax']) {
                                    $args = array(
                                        'taxonomy' => sanitize_text_field($_POST['tax']),
                                        'orderby' => 'name',
                                        'order' => 'ASC',
                                        'hide_empty' => false,
                                        'fields' => 'all',
                                        'count' => false,
                                        'slug' => '',
                                        'parent' => '',
                                        'hierarchical' => false,
                                    );

                                    $terms = get_terms($args);
                                    if ($terms) {
                                        foreach ($terms as $term) {
                                            $list[$term->term_id] = $term->name;
                                        }
                                    }
                                }
                                echo json_encode($list);
                                die();
                            }

                            public static function get_shortcode_item() {
                                global $wpdb;
                                $checked = 3;
                                if ($select = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "custom_block limit 0,{$checked}", ARRAY_A)) {
                                    ?>
                                    <p><?= __('Shortcodes', 'custom-blocks-free'); ?></p>                    
                                    <p><select class="cb-shortcoding">
                                            <option><?= __('Choose a block to insert', 'custom-blocks-free'); ?></option>
                                            <?PHP
                                            foreach ($select as $value) {
                                                if ($value['published']) {
                                                    $published = __('Published', 'custom-blocks-free');
                                                } else {
                                                    $published = __('Not Published', 'custom-blocks-free');
                                                }
                                                echo '<option value="' . $value['id'] . '">' . $value['title'] . ' - ' . $published . '</option>';
                                            }
                                            ?>
                                        </select></p>
                                    <p></p>
                                    <p><?= __('Use this shortcode', 'custom-blocks-free'); ?></p> 
                                    <p><input type="text" value="<?= __('Choose a block to insert', 'custom-blocks-free'); ?>"></p>
                                    <?PHP
                                }
                            }

                            public function process_blocks() {
                                if (isset($_REQUEST['types']) && isset($_REQUEST['id'])) {
                                    echo json_encode(CustomBlocksPlugin::input_blocks_process($_REQUEST['types'], $_REQUEST['id']));
                                }
                                exit;
                            }

                            protected
                                    function _getIP() {
                                foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
                                    if (array_key_exists($key, $_SERVER) === true) {
                                        foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                                            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                                                return $ip;
                                            }
                                        }
                                    }
                                }
                            }

                            function get_rules_to_ads($block_id) {
                                global $wpdb;
                                $query = $wpdb->get_col("SELECT DISTINCT type_id FROM " . $wpdb->prefix . "custom_block_item WHERE block_id=" . (int)$block_id);
                                $type_res = array();
                                if (count($query)) {
                                    foreach ($query as $query_result) {
                                        switch ($query_result) {
                                            case 0:
                                                $query_name = __('Default Block', 'custom-blocks-free');
                                                break;
                                            case 1:
                                                $query_name = __('For All Screen Resolutions', 'custom-blocks-free');
                                                break;
                                            case 2:
                                                $query_name = __('For AdBlock Users', 'custom-blocks-free');
                                                break;
                                            default:
                                                $res = $wpdb->get_row("SELECT width,width_stop FROM " . $wpdb->prefix . "custom_block_resolution_type WHERE id=" . $query_result);
                                                if ($res) {
                                                    $query_name = __('from ', 'custom-blocks-free') . $res->width;
                                                    $query_name.=($res->width_stop) ? ' ' . __('to', 'custom-blocks-free') . ' ' . $res->width_stop : '';
                                                } else {
                                                    $query_name = __('Resolution is not detected', 'custom-blocks-free');
                                                }
                                                break;
                                        }
                                        $type_res[$query_result] = $query_name;
                                    }
                                }


                                if (count($type_res)) {
                                    ?>
                                    <p><?= __('Codes inside the block', 'custom-blocks-free'); ?>:</p>
                                    <div class="js-custom-spoiler"><p class="close-spoiler"><?= __('Show', 'custom-blocks-free'); ?></p><ul class="list_params_custom" style="display: none;">
                                            <?PHP foreach ($type_res as $res_key => $res_value) { ?>
                                                <li><?= $res_value; ?>
                                                    <?PHP
                                                    $query = $wpdb->get_results("SELECT id,title,geotargeting,content_filter FROM " . $wpdb->prefix . "custom_block_item WHERE block_id=" . $block_id . " AND type_id=" . $res_key);
                                                    if (count($query)) {
                                                        ?>
                                                        <ul>
                                                            <?PHP
                                                            foreach ($query as $value) {
                                                                echo "<li>" . $value->title;
                                                                $flag_start = false;
                                                                if ($value->geotargeting) {
                                                                    $geo = $wpdb->get_results('SELECT allow,country_id,city_id FROM ' . $wpdb->prefix . 'custom_block_geoip WHERE item_id=' . $value->id);
                                                                    if (count($geo)) {
                                                                        $geo_block = $geo_access = array();
                                                                        foreach ($geo as $geo_item) {
                                                                            if ($geo_item->allow == 0) {
                                                                                $geo_block[] = array('country' => $geo_item->country_id, 'city' => $geo_item->city_id);
                                                                            } elseif ($geo_item->allow == 1) {
                                                                                $geo_access[] = array('country' => $geo_item->country_id, 'city' => $geo_item->city_id);
                                                                            }
                                                                        }
                                                                        if (isset($geo_access) or isset($geo_block)) {
                                                                            echo '<div class="custom_settings_congeo"><b>' . __('Geolocation', 'custom-blocks-free') . ':</b>';
                                                                            $flag_start = true;
                                                                            if (isset($geo_access)) {
                                                                                echo '<p>' . __('Allowed', 'custom-blocks-free') . ': </p><ul>';
                                                                                foreach ($geo_access as $geo_access_item) {
                                                                                    echo '<li>';
                                                                                    echo $wpdb->get_var("SELECT name_ru as name FROM " . $wpdb->prefix . "geoip_sxgeo_country WHERE id=" . $geo_access_item['country']) . ' ';
                                                                                    if ($geo_access_item['city']) {
                                                                                        echo '(' . $wpdb->get_var("SELECT name_ru as name FROM " . $wpdb->prefix . "geoip_sxgeo_cities WHERE id=" . $geo_access_item['city']) . ') ';
                                                                                    }
                                                                                    echo '</li>';
                                                                                }
                                                                                echo '</ul>';
                                                                            }
                                                                            if (isset($geo_block)) {
                                                                                echo '<p>' . __('Disallowed', 'custom-blocks-free') . ': </p><ul>';
                                                                                foreach ($geo_block as $geo_access_item) {
                                                                                    echo '<li>';
                                                                                    echo $wpdb->get_var("SELECT name_ru as name FROM " . $wpdb->prefix . "geoip_sxgeo_country WHERE id=" . $geo_access_item['country']) . ' ';
                                                                                    if ($geo_access_item['city']) {
                                                                                        echo '(' . $wpdb->get_var("SELECT name_ru as name FROM " . $wpdb->prefix . "geoip_sxgeo_cities WHERE id=" . $geo_access_item['city']) . ') ';
                                                                                    }
                                                                                    echo '</li>';
                                                                                }
                                                                                echo '</ul>';
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                //получаем правила фильтра постов и категорий
                                                                if ($value->content_filter) {
                                                                    $cont = $wpdb->get_results("SELECT allow,category_id,post_id FROM  " . $wpdb->prefix . "custom_block_item_rules WHERE item_id=" . $value->id);
                                                                    if (count($cont)) {
                                                                        $content_block = $content_access = array();
                                                                        foreach ($cont as $content_item) {
                                                                            switch ($content_item->allow) {
                                                                                case 0:
                                                                                    $content_block[] = array('category' => $content_item->category_id, 'post' => $content_item->post_id);
                                                                                    break;
                                                                                case 1:
                                                                                    $content_access[] = array('category' => $content_item->category_id, 'post' => $content_item->post_id);
                                                                                    break;
                                                                            }
                                                                        }
                                                                        if (isset($content_access) or isset($content_block)) {
                                                                            if (!$flag_start) {
                                                                                echo '<div class="custom_settings_congeo">';
                                                                                $flag_start = true;
                                                                            }
                                                                            echo '<b>' . __('Content', 'custom-blocks-free') . ':</b>';
                                                                            if (isset($content_access)) {
                                                                                echo '<p>' . __('Allowed', 'custom-blocks-free') . ': </p><ul>';
                                                                                foreach ($content_access as $content_access_item) {
                                                                                    if ($content_access_item['category']) {
                                                                                        echo '<li>' . __('Category', 'custom-blocks-free') . ': ' . $wpdb->get_var("SELECT name FROM " . $wpdb->prefix . "terms WHERE term_id=" . $content_access_item['category']) . '</li>';
                                                                                    }
                                                                                    if ($content_access_item['post']) {
                                                                                        echo '<li>' . __('Post', 'custom-blocks-free') . ': ' . $wpdb->get_var("SELECT post_title FROM " . $wpdb->prefix . "posts WHERE id=" . $content_access_item['post']) . '</li>';
                                                                                    }
                                                                                }
                                                                                echo '</ul>';
                                                                            }
                                                                            if (isset($content_block)) {
                                                                                echo '<p>' . __('Disallowed', 'custom-blocks-free') . ': </p><ul>';
                                                                                foreach ($content_block as $content_access_item) {
                                                                                    if ($content_access_item['category']) {
                                                                                        echo '<li>' . __('Category', 'custom-blocks-free') . ': ' . $wpdb->get_var("SELECT name FROM " . $wpdb->prefix . "terms WHERE term_id=" . $content_access_item['category']) . '</li> ';
                                                                                    }
                                                                                    if ($content_access_item['post']) {
                                                                                        echo '<li>' . __('Post', 'custom-blocks-free') . ': ' . $wpdb->get_var("SELECT post_title FROM " . $wpdb->prefix . "posts WHERE id=" . $content_access_item['post']) . '</li>';
                                                                                    }
                                                                                }
                                                                                echo '</ul>';
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                if ($flag_start) {
                                                                    echo '</div>';
                                                                }
                                                                echo "</li>";
                                                            }
                                                            echo '</ul></li>';
                                                        }
                                                    }
                                                    echo '</ul>';
                                                }
                                            }

                                            public
                                                    function register_setting_callback($value) {
                                                return $value;
                                            }

// Регистрируем пункты настроек и их обработчики
                                            public
                                                    function admin_init_action() {
                                                register_setting('custom-blocks-settings-group', 'cb_show_for', array($this, 'register_setting_callback'));
                                                register_setting('custom-blocks-settings-group', 'cb_blocks', array($this, 'register_setting_callback'));
                                            }

// Регистрирует фильтр обработки контента
                                            public
                                                    function init_action() {
                                            }

                                            public function cb_clear_string($string) {
                                                $string = strip_tags($string);
                                                $string = preg_replace('/([^\pL\pN\pP\pS\pZ])|([\xC2\xA0])/u', '', $string);
                                                $string = str_replace(' ', '', $string);
                                                $string = trim($string);
                                                return $string;
                                            }

                                            // Обрабатывает контент и расставляет нём блоки
                                            public function cb_the_content_filter($content) {
                                                global $post;
                                                $disable = get_post_meta($post->ID, 'cb_disable', true);
                                                if ($disable && $disable == 'on') {
                                                    return $content;
                                                }
                                                if (is_page() || is_singular() || is_single()) {
                                                    $options = $this->get_options();
                                                    $show = (isset($options['cb_show_for'])) ? $options['cb_show_for'] : array();
                                                    // Если не показываются блоки для текущего типа текста
                                                    if (!isset($show[$post->post_type]))
                                                        return $content;
                                                    $bq = (get_option('cb_blockquote')) ? true : false;
                                                    $blocks = $options['cb_blocks'];


                                                    if (empty($blocks))
                                                        return $content;
                                                    //проверяем сколько блоков включено
                                                    $count_enter_block = 0;
                                                    foreach ($blocks as $block_value) {
                                                        if (isset($block_value['show']) && $block_value['show']) {
                                                            $count_enter_block++;
                                                        }
                                                    }
                                                    if (!$count_enter_block)
                                                        return $content;
                                                    $flipper_a = array();
                                                    $flipper_check = false;
                                                    $url_img_adr_play = (get_option("cb_watermark")) ? get_option("cb_watermark") : plugins_url('/images/play.png', __FILE__);
                                                    // Получаем общее кол-во символов в тексте без тегов
                                                    $string = $this->cb_clear_string($post->post_content);
                                                    $content_length = mb_strlen($string, 'UTF-8');
                                                    $images_types = array('all' => false, 'other' => false, 'count' => 0);
                                                    $h_types = array();
                                                    $h_need_types = array();
                                                    $p_types = array();
                                                    $center_p = array();

                                                    // Скрываем блоки которые не подходят по максимальной длине текста
                                                    $chars_counter = false;
                                                    $only_img_blocks = array();
                                                    foreach ($blocks as $id => $block) {
                                                        if (isset($block['show']) && $block['show'] == "1") {
                                                            if (in_array((int) $block['show_type'], array(13, 14))) {
                                                                $bq = false;
                                                            }
                                                        }
                                                        if (isset($block['show']) && $block['show'] == "1" && in_array((int) $block['show_type'], array(6, 7, 8, 16)) && $block['total_chars_count'] <= $content_length) {
                                                            if (isset($block['show_value_all']) && $block['show_value_all'] && !$images_types['all']) {
                                                                $images_types['all'] = true;
                                                                $images_types['all_id'] = $id;
                                                                if (isset($block['wm'])) {
                                                                    $images_types['wm'] = $block['wm'];
                                                                }
                                                                $images_types['count'] ++;
                                                                if ($block['show_type'] == 8) {
                                                                    $images_types['link'] = $block['link'];
                                                                }
                                                            } elseif (isset($block['show_value']) && $block['show_value'] && !isset($block['show_value_all'])) {
                                                                $images_types['other'] = true;
                                                                $images_types['count'] ++;
                                                            }
                                                            $only_img_blocks[$id] = $block;
                                                            $only_img_blocks[$id]['id'] = $id;
                                                            $images_types['count'] ++;
                                                        }
                                                        if (isset($block['show']) && $block['show'] == "1" && (int) $block['show_type'] == 12 && $block['total_chars_count'] <= $content_length) {
                                                            $h_types[$id] = $block;
                                                            $h_types[$id]['id'] = $id;
                                                            $h_need_types[$block['level_h']] = 'h' . $block['level_h'];
                                                            array_unique($h_need_types, SORT_STRING);
                                                        }
                                                        if (isset($block['show']) && $block['show'] == "1" && (int) $block['show_type'] == 14 && $block['total_chars_count'] <= $content_length) {
                                                            $p_types[$id] = $block;
                                                            $p_types[$id]['id'] = $id;
                                                        }
                                                        if (isset($block['show']) && $block['show'] == "1" && (int) $block['show_type'] == 15 && $block['total_chars_count'] <= $content_length) {
                                                            $center_p[$id] = $block;
                                                            $center_p[$id]['id'] = $id;
                                                        }
                                                        if ($block['total_chars_count'] <= $content_length && isset($block['show']) && $block['show'] == "1") {
                                                            // Рассчитываем в каких позициях будут выводиться блоки
                                                            if ($chars_counter === false) {
                                                                $chars_counter = (int) $block['show_value'];
                                                            } else {
                                                                $chars_counter += (int) $block['show_value'];
                                                                $blocks[$id]['show_value'] = $chars_counter;
                                                            }
                                                            $blocks[$id]['show'] = true;
                                                        } else {
                                                            $blocks[$id]['show'] = false;
                                                        }
                                                        $blocks[$id]['showed'] = false;
                                                        $blocks[$id]['id'] = $id;
                                                    }
                                                    // Формируем из контента DOM
                                                    $doc = new DOMDocument();
                                                    libxml_use_internal_errors(true);
                                                    @$doc->loadHTML("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />" . $content);
                                                    $nodeList = $doc->getElementsByTagName('body')->item(0)->childNodes;
                                                    $allowed_tags = array('p', 'div', 'blockquote', 'ul', 'ol', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6');
                                                    $summary_tags = array();
                                                    // обработка действий картинок
                                                    $counter_img = 0;
                                                    if ($images_types['count'] > 0) {
                                                        if ($nodeImg = $doc->getElementsByTagName('img')) {
                                                            foreach ($nodeImg as $nodeImgValue) {
                                                                if ($nodeImgValue->parentNode->getAttribute("class") <> 'image_onhover') {
                                                                    $counter_img++;
                                                                    if ($images_types['other']) {
                                                                        foreach ($only_img_blocks as $i_key => $block) {
                                                                            if (in_array((int) $block['show_type'], array(6, 7, 8, 16)) && $block['show'] && (!isset($block['show_value_all'])) && $nodeImgValue && (int) $block['show_value'] == $counter_img) {
                                                                                switch ($block['show_type']) {
                                                                                    case 6:
                                                                                        $element_p = $doc->createElement('p');
                                                                                        $element = $doc->createElement('div');
                                                                                        $element->setAttribute('id', 'block_' . $block['id']);
                                                                                        $newNode = $nodeImgValue->parentNode;
                                                                                        $element_p->appendChild($element);
                                                                                        if (isset($blocks[$i_key]['noindex']) && $blocks[$i_key]['noindex']) {
                                                                                            $element_noindex = $doc->createElement('noindex');
                                                                                            $element_noindex->appendChild($element_p);
                                                                                            $newNode->parentNode->insertBefore($element_noindex, $nodeImgValue->parentNode);
                                                                                        } else {
                                                                                            $newNode->parentNode->insertBefore($element_p, $nodeImgValue->parentNode);
                                                                                        }
                                                                                        $blocks[$i_key]['showed'] = true;
                                                                                        break;
                                                                                    case 7:
                                                                                        $element_p = $doc->createElement('p');
                                                                                        $element = $doc->createElement('div');
                                                                                        $element->setAttribute('id', 'block_' . $block['id']);
                                                                                        if ($nodeImgValue->tagName == 'a' || $nodeImgValue->parentNode->tagName == 'figure') {
                                                                                            $newNode = $nodeImgValue->parentNode->parentNode;
                                                                                        } else {
                                                                                            $newNode = $nodeImgValue->parentNode;
                                                                                        }
                                                                                        $element_p->appendChild($element);
                                                                                        if (isset($blocks[$i_key]['noindex']) && $blocks[$i_key]['noindex']) {
                                                                                            $element_noindex = $doc->createElement('noindex');
                                                                                            $element_noindex->appendChild($element_p);
                                                                                            $newNode->appendChild($element_noindex);
                                                                                        } else {
                                                                                            $newNode->appendChild($element_p);
                                                                                        }
                                                                                        $blocks[$i_key]['showed'] = true;
                                                                                        break;
                                                                                    case 8:
                                                                                        if ((isset($block['link'])) && $block['link']) {
                                                                                            $href_onhover = $block['link'];
                                                                                            $element2 = $doc->createElement('a');
                                                                                            $element2->setAttribute('class', 'text-content-on-image');
                                                                                            $element2->setAttribute('href', $href_onhover);
                                                                                            $element3 = $doc->createElement('div');
                                                                                            $element3->setAttribute('id', 'block_' . $block['id']);
                                                                                        } else {
                                                                                            $element2 = $doc->createElement('div');
                                                                                            $element2->setAttribute('class', 'text-content-on-image');
                                                                                            $element3 = $doc->createElement('div');
                                                                                            $element3->setAttribute('id', 'block_' . $block['id']);
                                                                                        }

                                                                                        $element = $doc->createElement('div');
                                                                                        $element->setAttribute('class', 'image_onhover');

                                                                                        if ($nodeImgValue->parentNode == 'a') {
                                                                                            $newNode = $nodeImgValue->parentNode->parentNode;
                                                                                        } else {
                                                                                            $newNode = $nodeImgValue->parentNode;
                                                                                        }

                                                                                        $element2->appendChild($element3);
                                                                                        $element->appendChild($element2);
                                                                                        if (((isset($block['wm'])) && $block['wm'] == 'on')) {
                                                                                            $play_icon = $doc->createElement('img');
                                                                                            $play_icon->setAttribute('class', 'hovered_play_icon');
                                                                                            $play_icon->setAttribute('src', $url_img_adr_play);
                                                                                            $element->appendChild($play_icon);
                                                                                        }
                                                                                        $element->insertBefore($nodeImgValue);
                                                                                        if (isset($blocks[$i_key]['noindex']) && $blocks[$i_key]['noindex']) {
                                                                                            $element_noindex = $doc->createElement('noindex');
                                                                                            $element_noindex->appendChild($element);
                                                                                            $newNode->insertBefore($element_noindex, $newNode->firstChild);
                                                                                        } else {
                                                                                            $newNode->insertBefore($element, $newNode->firstChild);
                                                                                        }
                                                                                        $blocks[$i_key]['showed'] = true;
                                                                                        break;
                                                                                    case 16:
                                                                                        $element_head = $doc->createElement('div');
                                                                                        $last_rand = rand(1, 999);
                                                                                        $flipper_a[] = $last_rand;
                                                                                        $element_head->setAttribute('id', 'cb_flipper_' . $last_rand);
                                                                                        $element_head->setAttribute('class', 'box');
                                                                                        $element_head->setAttribute('class', 'm-flip');
                                                                                        $element_front = $doc->createElement('div');
                                                                                        $element_front->setAttribute('class', 'front');
                                                                                        $element_back = $doc->createElement('div');
                                                                                        $element_back->setAttribute('class', 'back');
                                                                                        $element_ads = $doc->createElement('div');
                                                                                        $element_ads->setAttribute('id', 'block_' . $block['id']);

                                                                                        if ($nodeImgValue->parentNode->tagName == 'a') {
                                                                                            $newNode = $nodeImgValue->parentNode->parentNode;
                                                                                        } else {
                                                                                            $newNode = $nodeImgValue->parentNode;
                                                                                        }

                                                                                        $width_height_style = '';
                                                                                        if (isset($nodeImgValue->attributes->getNamedItem("width")->value) && $last_width = $nodeImgValue->attributes->getNamedItem("width")->value) {
                                                                                            $width_height_style.='width: ' . $last_width . 'px; ';
                                                                                        }
                                                                                        if (isset($nodeImgValue->attributes->getNamedItem("height")->value) && $last_height = $nodeImgValue->attributes->getNamedItem("height")->value) {
                                                                                            $width_height_style.='height: ' . $last_height . 'px; ';
                                                                                        }
                                                                                        $element_head->setAttribute('style', $width_height_style);
                                                                                        $element_back->appendChild($element_ads);

                                                                                        if (((isset($block['wm'])) && $block['wm'] == 'on')) {
                                                                                            $play_icon = $doc->createElement('img');
                                                                                            $play_icon->setAttribute('class', 'hovered_play_icon');
                                                                                            $play_icon->setAttribute('src', $url_img_adr_play);
                                                                                            $element_front->appendChild($play_icon);
                                                                                        }
                                                                                        $element_front->appendChild($nodeImgValue);
                                                                                        $element_head->appendChild($element_front);
                                                                                        $element_head->appendChild($element_back);
                                                                                        if (isset($blocks[$i_key]['noindex']) && $blocks[$i_key]['noindex']) {
                                                                                            $element_noindex = $doc->createElement('noindex');
                                                                                            $element_noindex->appendChild($element_head);
                                                                                            $newNode->insertBefore($element_noindex, $newNode->firstChild);
                                                                                        } else {
                                                                                            $newNode->insertBefore($element_head, $newNode->firstChild);
                                                                                        }
                                                                                        $blocks[$i_key]['showed'] = true;
                                                                                        break;
                                                                                }
                                                                            }
                                                                        }
                                                                    } elseif ($images_types['all']) {
                                                                        switch ($blocks[$images_types['all_id']]['show_type']) {
                                                                            case 6:
                                                                                $element_p = $doc->createElement('p');
                                                                                $element = $doc->createElement('div');
                                                                                $element->setAttribute('id', 'block_' . $images_types['all_id']);
                                                                                $newNode = $nodeImgValue->parentNode;
                                                                                $element_p->appendChild($element);
                                                                                if (isset($blocks[$images_types['all_id']]['noindex']) && $blocks[$images_types['all_id']]['noindex']) {
                                                                                    $element_noindex = $doc->createElement('noindex');
                                                                                    $element_noindex->appendChild($element_p);
                                                                                    $newNode->parentNode->insertBefore($element_noindex, $nodeImgValue->parentNode);
                                                                                } else {
                                                                                    $newNode->parentNode->insertBefore($element_p, $nodeImgValue->parentNode);
                                                                                }
                                                                                $blocks[$images_types['all_id']]['showed'] = true;
                                                                                break;
                                                                            case 7:
                                                                                $element_p = $doc->createElement('p');
                                                                                $element = $doc->createElement('div');
                                                                                $element->setAttribute('id', 'block_' . $images_types['all_id']);
                                                                                if ($nodeImgValue->parentNode->tagName == 'a' || $nodeImgValue->parentNode->tagName == 'figure') {
                                                                                    $newNode = $nodeImgValue->parentNode->parentNode;
                                                                                } else {
                                                                                    $newNode = $nodeImgValue->parentNode;
                                                                                }
                                                                                $element_p->appendChild($element);
                                                                                if (isset($blocks[$images_types['all_id']]['noindex']) && $blocks[$images_types['all_id']]['noindex']) {
                                                                                    $element_noindex = $doc->createElement('noindex');
                                                                                    $element_noindex->appendChild($element_p);
                                                                                    $newNode->appendChild($element_noindex);
                                                                                } else {
                                                                                    $newNode->appendChild($element_p);
                                                                                }

                                                                                $blocks[$images_types['all_id']]['showed'] = true;
                                                                                break;
                                                                            case 8:
                                                                                if ((isset($images_types['link'])) && $images_types['link']) {
                                                                                    $href_onhover = $images_types['link'];
                                                                                    $element2 = $doc->createElement('a');
                                                                                    $element2->setAttribute('class', 'text-content-on-image');
                                                                                    $element2->setAttribute('href', $href_onhover);
                                                                                    $element3 = $doc->createElement('div');
                                                                                    $element3->setAttribute('id', 'block_' . $images_types['all_id']);
                                                                                } else {
                                                                                    $element2 = $doc->createElement('div');
                                                                                    $element2->setAttribute('class', 'text-content-on-image');
                                                                                    $element3 = $doc->createElement('div');
                                                                                    $element3->setAttribute('id', 'block_' . $images_types['all_id']);
                                                                                }
                                                                                $element = $doc->createElement('div');
                                                                                $element->setAttribute('class', 'image_onhover');
                                                                                if ($nodeImgValue->parentNode == 'a') {
                                                                                    $newNode = $nodeImgValue->parentNode->parentNode;
                                                                                } else {
                                                                                    $newNode = $nodeImgValue->parentNode;
                                                                                }
                                                                                $element2->appendChild($element3);
                                                                                $element->appendChild($element2);
                                                                                if ((isset($images_types['wm'])) && $images_types['wm'] == 'on') {

                                                                                    $play_icon = $doc->createElement('br');
                                                                                    $play_icon->setAttribute('class', 'hovered_play_icon');
                                                                                    $element->appendChild($play_icon);
                                                                                }

                                                                                $element->insertBefore($nodeImgValue);

                                                                                if (isset($blocks[$images_types['all_id']]['noindex']) && $blocks[$images_types['all_id']]['noindex']) {
                                                                                    $element_noindex = $doc->createElement('noindex');
                                                                                    $element_noindex->appendChild($element);
                                                                                    $newNode->insertBefore($element_noindex, $newNode->firstChild);
                                                                                } else {
                                                                                    $newNode->insertBefore($element, $newNode->firstChild);
                                                                                }
                                                                                $blocks[$images_types['all_id']]['showed'] = true;
                                                                                break;
                                                                            case 16:
                                                                                $element_head = $doc->createElement('div');
                                                                                $last_rand = rand(1, 999);
                                                                                $flipper_a[] = $last_rand;
                                                                                $element_head->setAttribute('id', 'cb_flipper_' . $last_rand);
                                                                                $element_head->setAttribute('class', 'box');
                                                                                $element_head->setAttribute('class', 'm-flip');
                                                                                $element_front = $doc->createElement('div');
                                                                                $element_front->setAttribute('class', 'front');
                                                                                $element_back = $doc->createElement('div');
                                                                                $element_back->setAttribute('class', 'back');
                                                                                $element_ads = $doc->createElement('div');
                                                                                $element_ads->setAttribute('id', 'block_' . $images_types['all_id']);

                                                                                if ($nodeImgValue->parentNode->tagName == 'a') {
                                                                                    $newNode = $nodeImgValue->parentNode->parentNode;
                                                                                } else {
                                                                                    $newNode = $nodeImgValue->parentNode;
                                                                                }
                                                                                $width_height_style = '';
                                                                                if (isset($nodeImgValue->attributes->getNamedItem("width")->value) && $last_width = $nodeImgValue->attributes->getNamedItem("width")->value) {
                                                                                    $width_height_style.='width: ' . $last_width . 'px; ';
                                                                                }
                                                                                if (isset($nodeImgValue->attributes->getNamedItem("height")->value) && $last_height = $nodeImgValue->attributes->getNamedItem("height")->value) {
                                                                                    $width_height_style.='height: ' . $last_height . 'px; ';
                                                                                }
                                                                                $element_head->setAttribute('style', $width_height_style);
                                                                                $element_back->appendChild($element_ads);
                                                                                if ((isset($images_types['wm'])) && $images_types['wm'] == 'on') {
                                                                                    $play_icon = $doc->createElement('br');
                                                                                    $play_icon->setAttribute('class', 'hovered_play_icon');
                                                                                    $element_front->appendChild($play_icon);
                                                                                }
                                                                                $element_front->appendChild($nodeImgValue);
                                                                                $element_head->appendChild($element_front);
                                                                                $element_head->appendChild($element_back);

                                                                                if (isset($blocks[$images_types['all_id']]['noindex']) && $blocks[$images_types['all_id']]['noindex']) {
                                                                                    $element_noindex = $doc->createElement('noindex');
                                                                                    $element_noindex->appendChild($element_head);
                                                                                    $newNode->insertBefore($element_noindex, $newNode->firstChild);
                                                                                } else {
                                                                                    $newNode->insertBefore($element_head, $newNode->firstChild);
                                                                                }

                                                                                $blocks[$images_types['all_id']]['showed'] = true;
                                                                                break;
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    //h levels
                                                    if (count($h_need_types)) {
                                                        foreach ($h_need_types as $h_type_need_value) {
                                                            if ($nodeH = $doc->getElementsByTagName($h_type_need_value)) {
                                                                $max_h_i = $nodeH->length;
                                                                foreach ($h_types as $h_types_value) {
                                                                    $name_h_level = 'h' . $h_types_value['level_h'];
                                                                    if ($name_h_level == $h_type_need_value) {
                                                                        for ($h_i = 0; $h_i < $max_h_i; $h_i++) {
                                                                            if (((isset($h_types_value['show_value_all'])) && $h_types_value['show_value_all']) || ((isset($h_types_value['show_value'])) && $h_types_value['show_value'] && $h_i == $h_types_value['show_value'] - 1)) {
                                                                                $element = $doc->createElement('div');
                                                                                $element->setAttribute('id', 'block_' . $h_types_value['id']);
                                                                                $tmp_node = $nodeH->item($h_i)->parentNode;
                                                                                $tmp_node2 = $nodeH->item($h_i)->nextSibling;
                                                                                if (isset($blocks[$h_types_value['id']]['noindex']) && $blocks[$h_types_value['id']]['noindex']) {
                                                                                    $element_noindex = $doc->createElement('noindex');
                                                                                    $element_noindex->appendChild($element);
                                                                                    $tmp_node->insertBefore($element_noindex, $tmp_node2);
                                                                                } else {
                                                                                    $tmp_node->insertBefore($element, $tmp_node2);
                                                                                }
                                                                                $blocks[$h_types_value['id']]['showed'] = true;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                    //all_paragrafs
                                                    $paragraf = $doc->getElementsByTagName('p');
                                                    $list_paragraf = $this->cb_check_p($paragraf);
                                                    $paragraf_count = count($list_paragraf);
                                                    $center_paragraf = round($paragraf_count / 2);
                                                    if ($p_types) {
                                                        foreach ($p_types as $p_types_value) {
                                                            $value_parag = (int) $p_types_value['show_value'];
                                                            $now_item_tmp = $paragraf_count - $value_parag;
                                                            if (isset($list_paragraf[$now_item_tmp]) && $list_paragraf[$now_item_tmp]) {
                                                                $now_item = $list_paragraf[$now_item_tmp];
                                                            } else {
                                                                $now_item = 0;
                                                            }
                                                            if ($now_item > 0) {
                                                                $element = $doc->createElement('div');
                                                                $element->setAttribute('id', 'block_' . $p_types_value['id']);
                                                                if (isset($blocks[$p_types_value['id']]['noindex']) && $blocks[$p_types_value['id']]['noindex']) {
                                                                    $element_noindex = $doc->createElement('noindex');
                                                                    $element_noindex->appendChild($element);
                                                                    $paragraf->item($now_item)->parentNode->insertBefore($element_noindex, $paragraf->item($now_item)->nextSibling);
                                                                } else {
                                                                    $paragraf->item($now_item)->parentNode->insertBefore($element, $paragraf->item($now_item)->nextSibling);
                                                                }
                                                                $blocks[$p_types_value['id']]['showed'] = true;
                                                            }
                                                        }
                                                    }
                                                    if ($center_p) {
                                                        foreach ($center_p as $center_p_value) {
                                                            $element = $doc->createElement('div');
                                                            $element->setAttribute('id', 'block_' . $center_p_value['id']);
                                                            if (isset($list_paragraf[$center_paragraf]) && $list_paragraf[$center_paragraf]) {
                                                                $now_item = $list_paragraf[$center_paragraf];
                                                            } else {
                                                                $now_item = 0;
                                                            }
                                                            if ($paragraf->item($now_item)->parentNode->tagName == 'div') {
                                                                if ($paragraf->item($now_item + 1)->parentNode->tagName && $paragraf->item($now_item + 1)->parentNode->tagName <> 'div') {
                                                                    $now_item++;
                                                                } elseif ($paragraf->item($now_item - 1)->parentNode->tagName && $paragraf->item($now_item - 1)->parentNode->tagName <> 'div') {
                                                                    $now_item--;
                                                                } else {
                                                                    $now_item = 0;
                                                                }
                                                            }
                                                            if ($now_item > 0) {
                                                                if (isset($blocks[$center_p_value['id']]['noindex']) && $blocks[$center_p_value['id']]['noindex']) {
                                                                    $element_noindex = $doc->createElement('noindex');
                                                                    $element_noindex->appendChild($element);
                                                                    $paragraf->item($now_item)->parentNode->insertBefore($element_noindex, $paragraf->item($now_item));
                                                                } else {
                                                                    $paragraf->item($now_item)->parentNode->insertBefore($element, $paragraf->item($now_item));
                                                                }
                                                            }
                                                            $blocks[$center_p_value['id']]['showed'] = true;
                                                        }
                                                    }
                                                    $chars_counter = 0;
                                                    $paragraphs_counter = 0;
                                                    $last_paragraph = false;
                                                    $this_node_id = -1;

                                                    if (isset($nodeList) && $nodeList) {
                                                        foreach ($nodeList as $node) {
                                                            $this_node_id++;
                                                            $node_tag = (isset($node->tagName)) ? $node->tagName : '';
                                                            if (!in_array($node_tag, $allowed_tags))
                                                                continue;
                                                            $chars_counter += mb_strlen($node->nodeValue, 'UTF-8');
                                                            $summary_tags[$this_node_id] = mb_strlen($node->nodeValue, 'UTF-8');
                                                            if ($node->tagName == "p") {
                                                                $paragraphs_counter++;
                                                                $last_paragraph = $node;
                                                            }
                                                            if ($bq) {
                                                                if ($node->tagName == "blockquote") {
                                                                    $paragraphs_counter++;
                                                                    $last_paragraph = $node;
                                                                }
                                                            }

                                                            foreach ($blocks as &$block) {
                                                                if (!$block['show'])
                                                                    continue;
                                                                if ($block['showed'])
                                                                    continue;
                                                                if ($block['show_type'] == 1 && $block['show_value'] >= $chars_counter)
                                                                    continue;
                                                                if ($block['show_type'] == 2 && $block['show_value'] > $paragraphs_counter)
                                                                    continue;
                                                                if (in_array((int) $block['show_type'], array(6, 7, 8, 14, 15)))
                                                                    continue;
                                                                switch ($block['show_type']) {
                                                                    // Блоки уже прошли проверку, можно вставлять
                                                                    case 1:
                                                                    case 2:
                                                                        $element = $doc->createElement('div');
                                                                        $element->setAttribute('id', 'block_' . $block['id']);
                                                                        if (isset($block['noindex']) && $block['noindex']) {
                                                                            $element_noindex = $doc->createElement('noindex');
                                                                            $element_noindex->appendChild($element);
                                                                            $node->parentNode->insertBefore($element_noindex, $node->nextSibling);
                                                                        } else {
                                                                            $node->parentNode->insertBefore($element, $node->nextSibling);
                                                                        }
                                                                        $block['showed'] = true;
                                                                        break;
                                                                    // в конце текста
                                                                    case 4:
                                                                        $element = $doc->createElement('div');
                                                                        $element->setAttribute('id', 'block_' . $block['id']);
                                                                        if (isset($block['noindex']) && $block['noindex']) {
                                                                            $element_noindex = $doc->createElement('noindex');
                                                                            $element_noindex->appendChild($element);
                                                                            $node->parentNode->appendChild($element_noindex);
                                                                        } else {
                                                                            $node->parentNode->appendChild($element);
                                                                        }
                                                                        $block['showed'] = true;
                                                                        break;
                                                                    // под заголовком
                                                                    case 5:
                                                                        $element = $doc->createElement('div');
                                                                        $element->setAttribute('id', 'block_' . $block['id']);
                                                                        if (isset($block['noindex']) && $block['noindex']) {
                                                                            $element_noindex = $doc->createElement('noindex');
                                                                            $element_noindex->appendChild($element);
                                                                            $node->parentNode->insertBefore($element_noindex, $node->parentNode->firstChild);
                                                                        } else {
                                                                            $node->parentNode->insertBefore($element, $node->parentNode->firstChild);
                                                                        }
                                                                        $block['showed'] = true;
                                                                        break;
                                                                    case 9:
                                                                        $element_p = $doc->createElement('p');
                                                                        $element = $doc->createElement('div');
                                                                        $element->setAttribute('id', 'block_' . $block['id']);
                                                                        if ($toc_tmp = $doc->getElementById('toc_container')) {
                                                                            $element_p->appendChild($element);
                                                                            if (isset($block['noindex']) && $block['noindex']) {
                                                                                $element_noindex = $doc->createElement('noindex');
                                                                                $element_noindex->appendChild($element_p);
                                                                                $node->parentNode->insertBefore($element_noindex, $toc_tmp->nextSibling);
                                                                            } else {
                                                                                $node->parentNode->insertBefore($element_p, $toc_tmp->nextSibling);
                                                                            }
                                                                        }
                                                                        $block['showed'] = true;
                                                                        break;
                                                                    case 10:
                                                                        if (isset($block['wheel']['type'])) {
                                                                            $ten_type_count = (isset($block['wheel']['n'])) ? (int) $block['wheel']['n'] : 0;
                                                                            switch ($block['wheel']['type']) {
                                                                                case 2:
                                                                                    if ($chars_counter >= $ten_type_count) {
                                                                                        $element = $doc->createElement('div');
                                                                                        $element->setAttribute('id', 'block_slide_' . $block['id']);
                                                                                        if (isset($block['noindex']) && $block['noindex']) {
                                                                                            $element_noindex = $doc->createElement('noindex');
                                                                                            $element_noindex->appendChild($element);
                                                                                            $node->parentNode->insertBefore($element_noindex, $node->nextSibling);
                                                                                        } else {
                                                                                            $node->parentNode->insertBefore($element, $node->nextSibling);
                                                                                        }
                                                                                        $block['showed'] = true;
                                                                                    }
                                                                                    break;
                                                                                case 3:
                                                                                    if ($paragraphs_counter >= $ten_type_count) {
                                                                                        $element = $doc->createElement('div');
                                                                                        $element->setAttribute('id', 'block_slide_' . $block['id']);
                                                                                        if (isset($block['noindex']) && $block['noindex']) {
                                                                                            $element_noindex = $doc->createElement('noindex');
                                                                                            $element_noindex->appendChild($element);
                                                                                            $node->parentNode->insertBefore($element_noindex, $node->nextSibling);
                                                                                        } else {
                                                                                            $node->parentNode->insertBefore($element, $node->nextSibling);
                                                                                        }
                                                                                        $block['showed'] = true;
                                                                                    }
                                                                                    break;
                                                                            }
                                                                        }
                                                                        break;
                                                                }
                                                            }
                                                        }
                                                    }
                                                    krsort($summary_tags);
                                                    // Перед последним абзацем
                                                    foreach ($blocks as &$block) {
                                                        if (!$last_paragraph)
                                                            continue;
                                                        if (!$block['show'])
                                                            continue;
                                                        if ($block['showed'])
                                                            continue;
                                                        if ((int) $block['show_type'] == 10 && (isset($block['wheel']['type'])) && (int) $block['wheel']['type'] == 1) {
                                                            $element = $doc->createElement('div');
                                                            $element->setAttribute('id', 'block_slide_' . $block['id']);
                                                            if (isset($block['noindex']) && $block['noindex']) {
                                                                $element_noindex = $doc->createElement('noindex');
                                                                $element_noindex->appendChild($element);
                                                                $last_paragraph->parentNode->appendChild($element_noindex);
                                                            } else {
                                                                $last_paragraph->parentNode->appendChild($element);
                                                            }
                                                            $block['showed'] = true;
                                                        } elseif ((int) $block['show_type'] == 11) {
                                                            $element = $doc->createElement('div');
                                                            $element->setAttribute('id', 'block_pop_' . $block['id']);
                                                            if (isset($block['noindex']) && $block['noindex']) {
                                                                $element_noindex = $doc->createElement('noindex');
                                                                $element_noindex->appendChild($element);
                                                                $last_paragraph->parentNode->appendChild($element_noindex);
                                                            } else {
                                                                $last_paragraph->parentNode->appendChild($element);
                                                            }
                                                            $block['showed'] = true;
                                                        }
                                                        if ((int) $block['show_type'] == 13 && (isset($block['show_value'])) && (int) $block['show_value'] > 0) {
                                                            $show_value_for_sym = 0;
                                                            $more_first_for_counter = false;
                                                            foreach ($summary_tags as $sum_key => $sum_value) {
                                                                $show_value_for_sym += $sum_value;
                                                                if (($show_value_for_sym >= $block['show_value']) && !$block['showed'] && $more_first_for_counter) {
                                                                    $element = $doc->createElement('div');
                                                                    $element->setAttribute('id', 'block_' . $block['id']);
                                                                    if (isset($block['noindex']) && $block['noindex']) {
                                                                        $element_noindex = $doc->createElement('noindex');
                                                                        $element_noindex->appendChild($element);
                                                                        $nodeList->item($sum_key)->parentNode->insertBefore($element_noindex, $nodeList->item($sum_key)->nextSibling);
                                                                    } else {
                                                                        $nodeList->item($sum_key)->parentNode->insertBefore($element, $nodeList->item($sum_key)->nextSibling);
                                                                    }
                                                                    $block['showed'] = true;
                                                                }
                                                                $more_first_for_counter = true;
                                                            }
                                                        }
                                                        if ($block['show_type'] != 3)
                                                            continue;

                                                        $element = $doc->createElement('div');
                                                        $element->setAttribute('id', 'block_' . $block['id']);
                                                        if (isset($block['noindex']) && $block['noindex']) {
                                                            $element_noindex = $doc->createElement('noindex');
                                                            $element_noindex->appendChild($element);
                                                            $last_paragraph->parentNode->insertBefore($element_noindex, $last_paragraph);
                                                        } else {
                                                            $last_paragraph->parentNode->insertBefore($element, $last_paragraph);
                                                        }
                                                        $block['showed'] = true;
                                                    }


                                                    $content = $doc->saveHTML();

                                                    $replace = array(
                                                        '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">' => '',
                                                        '<html>' => '',
                                                        '</html>' => '',
                                                        '<head>' => '',
                                                        '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' => '',
                                                        '</head>' => '',
                                                        '<body>' => '',
                                                        '</body>' => '',
                                                        '<br class="hovered_play_icon">' => '<img class="hovered_play_icon" src="' . $url_img_adr_play . '">',
                                                        '<noindex>' => '<!--noindex-->',
                                                        '</noindex>' => '<!--/noindex-->'
                                                    );
                                                    $content = str_replace(array_keys($replace), array_values($replace), $content);
                                                    foreach ($blocks as $block) {
                                                        if ($block['showed']) {
                                                            if ($block['show_type'] == 10) {
                                                                $margins = 'style="display:none; ';
                                                                foreach (array('top' => 'up', 'right' => 'right', 'bottom' => 'down', 'left' => 'left') as $key_margin => $margin_value) {
                                                                    if (isset($block['margins'][$margin_value])) {
                                                                        $margins.=$key_margin . ': ' . $block['margins'][$margin_value] . 'px; ';
                                                                    }
                                                                }
                                                                if (isset($block['cross']) && $block['cross'] == 'on') {
                                                                    $cross = "<a class='close'></a>";
                                                                } else {
                                                                    $cross = '';
                                                                }
                                                                $margins.='"';
                                                                $speed = (@$block['speed']) ? $block['speed'] : '300';
                                                                $w_position = (@$block['wheel']['position']) ? $block['wheel']['position'] : 'left';
                                                                $js_on_popup = '<script>jQuery(document).ready(function ($) { $(function () {';
                                                                $js_on_popup.= "$(window).scroll(function () {

                var distanceTop = $('#last-" . get_option('cb_functionname', 'custom-block') . "').offset().top - $(window).height();
                if ($(window).scrollTop() > distanceTop && $('#slidebox-" . get_option('cb_functionname', 'custom-block') . "').html().length>21)
                {
                    $('#slidebox-" . get_option('cb_functionname', 'custom-block') . "').show();
                    $('#slidebox-" . get_option('cb_functionname', 'custom-block') . "').animate({'" . $w_position . "': '0px'}, " . $speed . ");
                } else
                {
                    $('#slidebox-" . get_option('cb_functionname', 'custom-block') . "').stop(true).animate({'" . $w_position . "': '-1000px'}, " . $speed . ");
                }
            });
            $('#slidebox-" . get_option('cb_functionname', 'custom-block') . " .close').bind('click', function () {
                $(this).parent().remove();
            });";


                                                                $js_on_popup.='});});</script>';
                                                                $slide_code = '<span id="last-' . get_option('cb_functionname', 'custom-block') . '"></span><div class="slidebox-spec-cb" id="slidebox-' . get_option('cb_functionname', 'custom-block') . '" ' . $margins . '>' . $cross . $block['code'] . '</div>' . $js_on_popup;
                                                                $content = str_replace("<div id=\"block_slide_{$block['id']}\"></div>", $slide_code, $content);
                                                            } elseif ($block['show_type'] == 11) {
                                                                $show_pop_ads = true;
                                                                //pop_showing
                                                                if (isset($block['pop_showing']['type']) && $block['pop_showing']['type'] == 'n') {
                                                                    if (isset($block['pop_showing']['n']) && (int) $block['pop_showing']['n'] > 0) {
                                                                        $count = (int) $block['pop_showing']['n'];
                                                                        if (isset($_COOKIE['wordpress_poping'])) {
                                                                            $count_showed = (int) $_COOKIE['wordpress_poping'];
                                                                            $count_showed++;
                                                                            if ($count < $count_showed) {
                                                                                $show_pop_ads = false;
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                if ($show_pop_ads) {
                                                                    $speed = (@$block['speed']) ? (int) $block['speed'] : 300;
                                                                    $opacity = (@$block['opacity']) ? $block['opacity'] : '1';
                                                                    $js_on_popup = '<script>';
                                                                    switch ($block['pop_show']) {
                                                                        case 'start':
                                                                            $js_on_popup.="jQuery( document ).ready(function() { setInterval( function() { if (show_modal===false && showing_modal_window===true) { jQuery('#pop-" . get_option('cb_functionname', 'custom-block') . '-' . $block['id'] . "').plainModal('open', {duration: " . $speed . "}); show_modal=true; }    },1000); jQuery(document).on('click','.close-reveal-modal',function(){ jQuery('.plainmodal-overlay').click();}); });";
                                                                            break;
                                                                        case 'nsec':
                                                                            $nsec = (@$block['nsec']) ? $block['nsec'] * 1000 : '5000';
                                                                            $js_on_popup.="window.setTimeout(function () {  if (reveal_close === false) { jQuery('#pop-" . get_option('cb_functionname', 'custom-block') . '-' . $block['id'] . "').plainModal('open', {duration: " . $speed . "}); } }, " . $nsec . ");  jQuery(document).on('click','.close-reveal-modal',function(){  jQuery('.plainmodal-overlay').click();});";
                                                                            break;
                                                                    }
                                                                    $js_on_popup.='</script>';
                                                                    $add_style = '';
                                                                    foreach (array('up', 'right', 'down', 'left') as $margin_value) {
                                                                        if (isset($block['margins'][$margin_value]) and $block['margins'][$margin_value]) {
                                                                            switch ($margin_value) {
                                                                                case 'up':
                                                                                    $add_style.=' top: ' . (int) $block['margins'][$margin_value] . 'px !important;';
                                                                                    break;
                                                                                case 'right':
                                                                                    $add_style.=' right: ' . (int) $block['margins'][$margin_value] . 'px !important;';
                                                                                    break;
                                                                                case 'down':
                                                                                    $add_style.=' bottom: ' . (int) $block['margins'][$margin_value] . 'px !important;';
                                                                                    break;
                                                                                case 'left':
                                                                                    $add_style.=' left: ' . (int) $block['margins'][$margin_value] . 'px !important;';
                                                                                    break;
                                                                            }
                                                                        }
                                                                    }
                                                                    if (isset($block['cross']) && $block['cross'] == 'on') {
                                                                        $cross = '<a class="close-reveal-modal" aria-label="Close">&#215;</a>';
                                                                    } else {
                                                                        $cross = '';
                                                                    }
                                                                    $pop_code = '<style>#pop-' . get_option('cb_functionname', 'custom-block') . '-' . $block['id'] . '{' . $add_style . '}</style>'
                                                                            . '<div id="pop-' . get_option('cb_functionname', 'custom-block') . '-' . $block['id'] . '" class="reveal-modal" style="opacity: ' . $opacity . ' !important; position: fixed; display:none;"><p>' . $block['code'] . '</p>' . $cross . '</div>' . $js_on_popup;
                                                                    $content = str_replace("<div id=\"block_pop_{$block['id']}\"></div>", $pop_code, $content);
                                                                } else {
                                                                    $content = str_replace("<div id=\"block_pop_{$block['id']}\"></div>", "", $content);
                                                                }
                                                            } elseif ($block['show_type'] == 16) {
                                                                $js_code_flipper = "";
                                                                if (count($flipper_a) && $flipper_check == false) {
                                                                    $flipper_check = true;
                                                                    $js_code_flipper = "<script>jQuery(document).ready(function(){";
                                                                    foreach ($flipper_a as $elem_flip) {
                                                                        $js_code_flipper.='jQuery("#cb_flipper_' . $elem_flip . '").mflip(); ';
                                                                    }
                                                                    $js_code_flipper.="}); </script>";
                                                                }
                                                                $content = str_replace("<div id=\"block_{$block['id']}\"></div>", $block['code'], $content);
                                                            } else {
                                                                $content = str_replace("<div id=\"block_{$block['id']}\"></div>", $block['code'], $content);
                                                            }
                                                        }
                                                    }
                                                }
                                                if (isset($js_code_flipper)) {
                                                    $content.=$js_code_flipper;
                                                }
                                                return do_shortcode($content);
                                            }

                                            function cb_check_p($node_in) {
                                                $length_old = $node_in->length;
                                                $list_items = array();
                                                $list_items[0] = null;
                                                for ($now_item = 1; $now_item < $length_old - 1; $now_item++) {
                                                    if ($this->cb_recursion_check_node($node_in->item($now_item))) {
                                                        $list_items[] = $now_item;
                                                    }
                                                }
                                                unset($list_items[0]);
                                                return $list_items;
                                            }

                                            function cb_recursion_check_node($node) {
                                                $tag_name = $node->parentNode->tagName;
                                                if ($tag_name) {
                                                    if ($tag_name == 'blockquote') {
                                                        return false;
                                                    }
                                                    if ($tag_name == 'body') {
                                                        return true;
                                                    }
                                                    if ($tag_name == 'html') {
                                                        return true;
                                                    }
                                                    return $this->cb_recursion_check_node($node->parentNode);
                                                } else {
                                                    return true;
                                                }
                                            }

                                            protected
                                                    function cb_parentHasThisValue($node) {
                                                $exclude = array('body', 'html', 'head');
                                                if (false !== array_search($node->parentNode->tagName, $exclude)) {
                                                    return false;
                                                }
                                                return @strpos($node->parentNode->nodeValue, $node->nodeValue);
                                            }

                                            protected
                                                    function cb_myInsertNode($newNode, $refNode, $insertMode = null) {
                                                if (!$insertMode || $insertMode == "inside") {
                                                    $refNode->appendChild($newNode);
                                                } else if ($insertMode == "before") {
                                                    $refNode->parentNode->insertBefore($newNode, $refNode);
                                                } else if ($insertMode == "after") {
                                                    if ($refNode->nextSibling) {
                                                        $refNode->parentNode->insertBefore($newNode, $refNode->nextSibling);
                                                    } else {
                                                        $refNode->parentNode->appendChild($newNode);
                                                    }
                                                }
                                            }

                                            protected
                                                    function get_options() {
                                                return array(
                                                    'cb_show_for' => get_option('cb_show_for', array('post', 'page')),
                                                    'cb_blocks' => get_option('cb_blocks', false),
                                                );
                                            }

                                            public function admin_page_decor_delete($id) {
                                                global $wpdb;
                                                if ((int) $id > 0) {
                                                    $wpdb->delete($wpdb->prefix . 'custom_block_decor', array('id' => $id), array('%d'));
                                                    $wpdb->delete($wpdb->prefix . 'custom_block_decor_link', array('id_decor' => $id), array('%d'));
                                                }
                                                ?>
                                                <script type="text/javascript">window.location.href = "/wp-admin/admin.php?page=custom-blocks-decor"</script>
                                                <?PHP
                                            }

                                            static function recursiveRemoval($array) {
                                                if (is_array($array)) {
                                                    foreach ($array as $key => $arrayElement) {
                                                        if (is_array($array[$key])) {
                                                            if (count($array[$key])) {
                                                                $array[$key] = CustomBlocksPlugin::recursiveRemoval($array[$key]);
                                                            } else {
                                                                unset($array[$key]);
                                                            }
                                                        } elseif (!$array[$key]) {
                                                            unset($array[$key]);
                                                        }
                                                    }
                                                }
                                                return $array;
                                            }

                                            public static function admin_page_decor_update($id, $title, $settings, $linked = null, $as_template = false) {
                                                global $wpdb;
                                                $table = $wpdb->prefix . 'custom_block_decor';
                                                $table_link = $table . '_link';
                                                //clean empty settings
                                                for ($i = 0; $i < 4; $i++) {
                                                    $settings = CustomBlocksPlugin::recursiveRemoval($settings);
                                                }

                                                $data = array('name' => $title, 'property' => serialize($settings));
                                                if ($id == 'new') {
                                                    $wpdb->insert($table, $data);
                                                    $id = $wpdb->insert_id;
                                                    $data = array(
                                                        'id_decor' => $id,
                                                    );
                                                    if ($linked) {
                                                        //get_all_link_to item
                                                        $id_to_delete = $wpdb->get_col("SELECT id FROM " . $table_link . " WHERE id_item=" . (int) $linked);
                                                        foreach ($id_to_delete as $del_id) {
                                                            $wpdb->delete($table_link, array('id' => $del_id));
                                                        }
                                                        $data['id_item'] = $linked;
                                                        $data['active'] = 0;
                                                        $wpdb->insert($table_link, $data);
                                                        if ($as_template) {
                                                            $data['active'] = 1;
                                                            $data['id_item'] = null;
                                                            $wpdb->insert($table_link, $data);
                                                        }
                                                    } else {
                                                        $data['active'] = 1;
                                                        $data['id_item'] = null;
                                                        $wpdb->insert($table_link, $data);
                                                    }
                                                } else {
                                                    if ($wpdb->get_var('SELECT COUNT(*) FROM ' . $table . ' WHERE id=' . (int) $id)) {
                                                        if (!$data['name']) {
                                                            unset($data['name']);
                                                        }
                                                        $wpdb->update($table, $data, array('id' => (int) $id));
                                                    }
                                                }
                                                return $id;
                                            }

                                            public static function admin_page_decor_get($id) {
                                                global $wpdb;
                                                $table = $wpdb->prefix . 'custom_block_decor';
                                                return $wpdb->get_row('SELECT * FROM ' . $table . ' WHERE id=' . $id, ARRAY_A);
                                            }

                                            public static function check_name_decor_block($id, $title) {
                                                global $wpdb;
                                                $table_link = $wpdb->prefix . 'custom_block_decor_link';
                                                $table = $wpdb->prefix . 'custom_block_decor';
                                                $id_decors = $wpdb->get_col("SELECT distinct id_decor FROM " . $table_link . " WHERE active='1' AND id_decor<>" . $id);
                                                if (!$id_decors) {
                                                    return true;
                                                }
                                                if ($wpdb->get_var("SELECT COUNT(*) FROM " . $table . " WHERE id IN (" . implode(',', $id_decors) . ") AND name='" . $title . "'")) {
                                                    return false;
                                                } else {
                                                    return true;
                                                }
                                            }

                                            public static function get_decor_template_codes() {
                                                $code_id = (int)$_REQUEST['id_rel'];
                                                if (isset($_REQUEST['new'])) {
                                                    CustomBlocksPlugin::admin_page_edit_block('new', $code_id, true);
                                                } else {
                                                    global $wpdb;
                                                    $table = $wpdb->prefix . 'custom_block_decor_link';
                                                    $ads_id = $wpdb->get_var("SELECT id_decor FROM " . $table . " WHERE id_item=" . $code_id);
                                                    if ($ads_id) {
                                                        //its template
                                                        if ($wpdb->get_var("SELECT count(*) FROM " . $table . " WHERE active=1 and id_decor=" . (int) $ads_id)) {
                                                            echo 'template';
                                                        } else {
                                                            CustomBlocksPlugin::admin_page_edit_block($ads_id, $code_id, true);
                                                            if ($wpdb->get_var("SELECT active FROM " . $table . " WHERE active=2 and id_decor=" . (int) $ads_id)) {
                                                                ?>
                                                                <script>
                                                                    if (decoring_js !== undefined)
                                                                    {

                                                                    } else {
                                                                        var decoring_js = new Array();
                                                                    }
                                                                    decoring_js['<?= $code_id; ?>'] = '2';
                                                                </script>
                                                                <?PHP
                                                            }
                                                        }
                                                    }
                                                }

                                                exit;
                                            }

                                            public static function admin_page_edit_block($id, $code_id = 0, $remote_from = false) {
                                                $new = false;
                                                $error_title = false;
                                                //check geting id
                                                if (isset($_REQUEST['submit']) && $code_id == 0) {
                                                    if (CustomBlocksPlugin::check_name_decor_block((int)$_REQUEST['id_decor'], sanitize_text_field($_REQUEST['title']))) {
                                                        $ids = CustomBlocksPlugin::admin_page_decor_update((int)$_REQUEST['id_decor'], sanitize_text_field($_REQUEST['title']), $_REQUEST['settings'][0]);
                                                        echo '<script type="text/javascript">window.location.href = "/wp-admin/admin.php?page=custom-blocks-decor&action=update&id=' . $ids . '"</script>';
                                                        exit;
                                                    } else {
                                                        $error_title = true;
                                                    }
                                                }
                                                ?>
                                                <div class="wrap">
                                                    <?PHP
                                                    if ($code_id == 0) {
                                                        if ((int) $id) {
                                                            ?>
                                                            <h2><?= __('Style Templates', 'custom-blocks-free'); ?></h2><h3><?= __('Edit Style', 'custom-blocks-free'); ?></h3><hr>
                                                            <?PHP
                                                        } else {
                                                            //new block
                                                            $new = true;
                                                            ?><h2><?= __('Style Templates', 'custom-blocks-free'); ?></h2><h3><?= __('Create Style', 'custom-blocks-free'); ?></h3><hr>
                                                            <?PHP
                                                        }
                                                    } else {
                                                        if ((int) $id == 0) {
                                                            $new = true;
                                                        }
                                                    }

                                                    if (!$new) {
                                                        $options = CustomBlocksPlugin::admin_page_decor_get($id);
                                                        $prop = unserialize($options['property']);
                                                    }
                                                    if ($error_title && $code_id == 0) {
                                                        $prop = $_REQUEST['settings'][0];
                                                        $options['name'] = sanitize_text_field($_REQUEST['title']);
                                                        echo '<span style="color:red;">'.__('This title is already in use, specify something else.', 'custom-blocks-free').'</span><br>';
                                                    }
                                                    ?>
                                                    <?PHP if ($code_id == 0) { ?><form action="" method="post">
                                                            <input type="hidden" name="id_decor" value="<?= ($new) ? 'new' : $id; ?>">
                                                            <input class="decor-title" type="text" name="title" value="<?= (@$options['name']) ? $options['name'] : ''; ?>" placeholder="<?= __('Style Title', 'custom-blocks-free'); ?>" required=""><?PHP } ?>
                                                        <table class='wp-list-table widefat plugins decor-table-style'>
                                                            <?PHP if ($code_id == 0) { ?><thead>
                                                                    <tr><td><?php submit_button(__('Save Style', 'custom-blocks-free')); ?></td></tr>
                                                                </thead> <?PHP } ?>
                                                            <tbody>
                                                                <tr><th>
                                                                        <?= __('Block Size', 'custom-blocks-free'); ?>
                                                                    </th></tr>
                                                                <tr>
                                                                    <td>
                                                                        <span><?= __('Width', 'custom-blocks-free'); ?></span><input disabled="" type='number' min="0" name='settings[<?= $code_id; ?>][width][value]' value='<?= (@$prop['width']['value']) ? $prop['width']['value'] : ''; ?>'>
                                                                        <select name='settings[<?= $code_id; ?>][width][type]' disabled="">
                                                                            <option value=""><?= __('By default', 'custom-blocks-free'); ?></option>
                                                                            <option value='px' <?= (isset($prop['width']['type']) && $prop['width']['type'] == 'px') ? 'selected' : ''; ?>><?= __('Pixels', 'custom-blocks-free'); ?></option>
                                                                            <option value='%' <?= (isset($prop['width']['type']) && $prop['width']['type'] == '%') ? 'selected' : ''; ?>><?= __('Percent', 'custom-blocks-free'); ?></option>
                                                                        </select> <br>
                                                                        <span><?= __('Height', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='number' min="0" name='settings[<?= $code_id; ?>][height][value]' value='<?= (isset($prop['height']['value'])) ? $prop['height']['value'] : ''; ?>'>
                                                                        <select disabled="" name='settings[<?= $code_id; ?>][height][type]'>
                                                                            <option value=""><?= __('By default', 'custom-blocks-free'); ?></option>
                                                                            <option value='px' <?= (isset($prop['height']['type']) && $prop['height']['type'] == 'px') ? 'selected' : ''; ?>><?= __('Pixels', 'custom-blocks-free'); ?></option>
                                                                            <option value='%' <?= (isset($prop['height']['type']) && $prop['height']['type'] == '%') ? 'selected' : ''; ?>><?= __('Percent', 'custom-blocks-free'); ?></option>
                                                                        </select> 
                                                                    </td>
                                                                </tr>   
                                                                <tr><th>
                                                                        <?= __('Border', 'custom-blocks-free'); ?>
                                                                    </th></tr>
                                                                <tr>
                                                                    <td>
                                                                        <span><?= __('Border Style', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type="radio" name="settings[<?= $code_id; ?>][border][style]" value="solid" <?= (isset($prop['border']['style']) && $prop['border']['style'] == 'solid') ? 'checked' : ''; ?>><?= __('Solid', 'custom-blocks-free'); ?><br>
                                                                        <input disabled="" type="radio" name="settings[<?= $code_id; ?>][border][style]" value="dashed" <?= (isset($prop['border']['style']) && $prop['border']['style'] == 'dashed') ? 'checked' : ''; ?>><?= __('Dashed', 'custom-blocks-free'); ?><br>
                                                                        <input disabled="" type="radio" name="settings[<?= $code_id; ?>][border][style]" value="dotted" <?= (isset($prop['border']['style']) && $prop['border']['style'] == 'dotted') ? 'checked' : ''; ?>><?= __('Dotted', 'custom-blocks-free'); ?><br>
                                                                        <input disabled="" type="radio" name="settings[<?= $code_id; ?>][border][style]" value="your" <?= (isset($prop['border']['style']) && $prop['border']['style'] == 'your') ? 'checked' : ''; ?>><input type='text' name='settings[<?= $code_id; ?>][border][style_your]'  disabled="" value='<?= (@$prop['border']['style_your']) ? $prop['border']['style_your'] : ''; ?>' placeholder="<?= __('Custom', 'custom-blocks-free'); ?>"> 
                                                                        <span><?= __('Border Width', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='number' min="0" name='settings[<?= $code_id; ?>][border][width]' value='<?= (isset($prop['border']['width'])) ? $prop['border']['width'] : ''; ?>'> <?= __('px', 'custom-blocks-free'); ?>
                                                                        <span><?= __('Border Color', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" class='colorSelect bordercolorSelect' type='text' name='settings[<?= $code_id; ?>][border][color]' value='<?= (@$prop['border']['color']) ? $prop['border']['color'] : ''; ?>'>    
                                                                    </td>
                                                                </tr>
                                                                <tr><th>
                                                                        <?= __('Rounded Corners', 'custom-blocks-free'); ?>
                                                                    </th></tr>
                                                                <tr>
                                                                    <td>
                                                                        <span><?= __('Top-Left', 'custom-blocks-free'); ?></span><input disabled="" type='number' min='0' name='settings[<?= $code_id; ?>][border][radius][top][left]' value='<?= (isset($prop['border']['radius']['top']['left'])) ? $prop['border']['radius']['top']['left'] : ''; ?>' placeholder="px">
                                                                        <span><?= __('Top-Right', 'custom-blocks-free'); ?></span><input disabled="" type='number' min='0' name='settings[<?= $code_id; ?>][border][radius][top][right]' value='<?= (isset($prop['border']['radius']['top']['right'])) ? $prop['border']['radius']['top']['right'] : ''; ?>' placeholder="px">
                                                                        <span><?= __('Bottom-Left', 'custom-blocks-free'); ?></span><input disabled="" type='number' min='0' name='settings[<?= $code_id; ?>][border][radius][down][left]' value='<?= (isset($prop['border']['radius']['down']['left'])) ? $prop['border']['radius']['down']['left'] : ''; ?>' placeholder="px">
                                                                        <span><?= __('Bottom-Right', 'custom-blocks-free'); ?></span><input disabled="" type='number' min='0' name='settings[<?= $code_id; ?>][border][radius][down][right]' value='<?= (isset($prop['border']['radius']['down']['right'])) ? $prop['border']['radius']['down']['right'] : ''; ?>' placeholder="px">
                                                                    </td>
                                                                </tr>
                                                                <tr><th>
                                                                        <?= __('Margins', 'custom-blocks-free'); ?>
                                                                    </th></tr>
                                                                <tr>
                                                                    <td>
                                                                        <span><?= __('Top', 'custom-blocks-free'); ?></span><input disabled="" type='number' name='settings[<?= $code_id; ?>][margin][top]' value='<?= (isset($prop['margin']['top'])) ? $prop['margin']['top'] : ''; ?>' placeholder="px">
                                                                        <span><?= __('Right', 'custom-blocks-free'); ?></span><input disabled="" type='number' name='settings[<?= $code_id; ?>][margin][right]' value='<?= (isset($prop['margin']['right'])) ? $prop['margin']['right'] : ''; ?>' placeholder="px">
                                                                        <span><?= __('Bottom', 'custom-blocks-free'); ?></span><input disabled="" type='number' name='settings[<?= $code_id; ?>][margin][bottom]' value='<?= (isset($prop['margin']['bottom'])) ? $prop['margin']['bottom'] : ''; ?>' placeholder="px">
                                                                        <span><?= __('Left', 'custom-blocks-free'); ?></span><input disabled="" type='number' name='settings[<?= $code_id; ?>][margin][left]' value='<?= (isset($prop['margin']['left'])) ? $prop['margin']['left'] : ''; ?>' placeholder="px">
                                                                    </td>
                                                                </tr>
                                                                <tr><th>
                                                                        <?= __('Paddings', 'custom-blocks-free'); ?>
                                                                    </th></tr>
                                                                <tr>
                                                                    <td>
                                                                        <span><?= __('Top', 'custom-blocks-free'); ?></span><input disabled="" type='number' name='settings[<?= $code_id; ?>][padding][top]' value='<?= (isset($prop['margin']['top'])) ? $prop['padding']['top'] : ''; ?>' placeholder="px">
                                                                        <span><?= __('Right', 'custom-blocks-free'); ?></span><input disabled="" type='number' name='settings[<?= $code_id; ?>][padding][right]' value='<?= (isset($prop['margin']['right'])) ? $prop['padding']['right'] : ''; ?>' placeholder="px">
                                                                        <span><?= __('Bottom', 'custom-blocks-free'); ?></span><input disabled="" type='number' name='settings[<?= $code_id; ?>][padding][bottom]' value='<?= (isset($prop['margin']['bottom'])) ? $prop['padding']['bottom'] : ''; ?>' placeholder="px">
                                                                        <span><?= __('Left', 'custom-blocks-free'); ?></span><input disabled="" type='number' name='settings[<?= $code_id; ?>][padding][left]' value='<?= (isset($prop['margin']['left'])) ? $prop['padding']['left'] : ''; ?>' placeholder="px">
                                                                    </td>
                                                                </tr>
                                                                <tr><th>
                                                                        <?= __('Background', 'custom-blocks-free'); ?>
                                                                    </th></tr>
                                                                <tr>
                                                                    <td>
                                                                        <span><?= __('Color', 'custom-blocks-free'); ?></span><input disabled="" class='colorSelect backgroundcolorSelect' type='text' name='settings[<?= $code_id; ?>][background][color]' value='<?= (@$prop['background']['color']) ? $prop['background']['color'] : ''; ?>'>
                                                                        <span><?= __('Opacity (from 0.1 to 1.0)', 'custom-blocks-free'); ?></span><input disabled="" type='number' min='0' max='1' step="0.1" name='settings[<?= $code_id; ?>][background][opacity]' value='<?= (@$prop['background']['opacity']) ? $prop['background']['opacity'] : ''; ?>'>
                                                                    </td>
                                                                </tr>
                                                                <tr><th>
                                                                        <?= __('Font', 'custom-blocks-free'); ?>
                                                                    </th></tr>
                                                                <tr>
                                                                    <td>
                                                                        <span><?= __('Font Family', 'custom-blocks-free'); ?></span>
                                                                        <select disabled="" name='settings[<?= $code_id; ?>][font][family]'>
                                                                            <option value=""><?= __('By default', 'custom-blocks-free'); ?></option>
                                                                            <option value='Arial' <?= (@$prop['font']['family'] == 'Arial') ? 'selected' : ''; ?>>Arial</option>
                                                                            <option value='Verdana' <?= (@$prop['font']['family'] == 'Verdana') ? 'selected' : ''; ?>>Verdana</option>
                                                                            <option value='Tahoma' <?= (@$prop['font']['family'] == 'Tahoma') ? 'selected' : ''; ?>>Tahoma</option>
                                                                            <option value='Times New Roman' <?= (@$prop['font']['family'] == 'Times New Roman') ? 'selected' : ''; ?>>Times New Roman</option>
                                                                        </select>
                                                                        <span><?= __('Size', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='number' min="1" name='settings[<?= $code_id; ?>][font][size]' value='<?= (@$prop['font']['size']) ? $prop['font']['size'] : ''; ?>'>
                                                                        <span><?= __('Bold', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='checkbox' name='settings[<?= $code_id; ?>][font][weight]' <?= (isset($prop['font']['weight'])) ? 'checked' : ''; ?>>
                                                                        <span><?= __('Italic', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='checkbox' name='settings[<?= $code_id; ?>][font][style]' <?= (isset($prop['font']['style'])) ? 'checked' : ''; ?>>
                                                                        <span><?= __('Letter Spacing', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='text' name='settings[<?= $code_id; ?>][letter-spacing][value]' value='<?= (isset($prop['letter-spacing']['value'])) ? $prop['letter-spacing']['value'] : ''; ?>'> 
                                                                        <select disabled="" name='settings[<?= $code_id; ?>][letter-spacing][type]'>
                                                                            <option value=""><?= __('By default', 'custom-blocks-free'); ?></option>
                                                                            <option value='px' <?= (@$prop['letter-spacing']['type'] == 'px') ? 'selected' : ''; ?>><?= __('Pixel', 'custom-blocks-free'); ?></option>
                                                                            <option value='in' <?= (@$prop['letter-spacing']['type'] == 'in') ? 'selected' : ''; ?>><?= __('Inch', 'custom-blocks-free'); ?></option>
                                                                            <option value='pt' <?= (@$prop['letter-spacing']['type'] == 'pt') ? 'selected' : ''; ?>><?= __('Point', 'custom-blocks-free'); ?></option>
                                                                            <option value='em' <?= (@$prop['letter-spacing']['type'] == 'em') ? 'selected' : ''; ?>><?= __('Relative (em)', 'custom-blocks-free'); ?></option>
                                                                            <option value='normal' <?= (@$prop['letter-spacing']['type'] == 'normal') ? 'selected' : ''; ?>><?= __('Normal', 'custom-blocks-free'); ?></option>
                                                                            <option value='inherit' <?= (@$prop['letter-spacing']['type'] == 'inherit') ? 'selected' : ''; ?>><?= __('Inherit', 'custom-blocks-free'); ?></option>
                                                                        </select>
                                                                        <span><?= __('Color', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" class='colorSelect fontcolorSelect' type='text' name='settings[<?= $code_id; ?>][font][color]' value='<?= (@$prop['font']['color']) ? $prop['font']['color'] : ''; ?>'>
                                                                        <span><?= __('Text Transform', 'custom-blocks-free'); ?></span>
                                                                        <select disabled="" name='settings[<?= $code_id; ?>][text-transform]'>
                                                                            <option value=""><?= __('By default', 'custom-blocks-free'); ?></option>
                                                                            <option value='capitalize' <?= (@$prop['text-transform'] == 'capitalize') ? 'selected' : ''; ?>><?= __('The first character of every word will be uppercased', 'custom-blocks-free'); ?></option>
                                                                            <option value='lowercase' <?= (@$prop['text-transform'] == 'lowercase') ? 'selected' : ''; ?>><?= __('All characters will become lowercased', 'custom-blocks-free'); ?></option>
                                                                            <option value='uppercase' <?= (@$prop['text-transform'] == 'uppercase') ? 'selected' : ''; ?>><?= __('All characters will become uppercased', 'custom-blocks-free'); ?></option>
                                                                            <option value='inherit' <?= (@$prop['text-transform'] == 'inherit') ? 'selected' : ''; ?>><?= __('Inherit', 'custom-blocks-free'); ?></option>
                                                                        </select> 
                                                                        <span><?= __('Line Height', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='number' min="1" name='settings[<?= $code_id; ?>][line-height]' value='<?= (@$prop['line-height']) ? $prop['line-height'] : ''; ?>'>%
                                                                    </td>
                                                                </tr>
                                                                <tr><th>
                                                                        <?= __('Special Effects (on hover)', 'custom-blocks-free'); ?>
                                                                    </th></tr>
                                                                <tr>
                                                                    <td>
                                                                        <span><?= __('Animation time', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='number' min="0" name='settings[<?= $code_id; ?>][spec][time][value]' value='<?= (@$prop['spec']['time']['value']) ? $prop['spec']['time']['value'] : ''; ?>'>
                                                                        <select disabled="" name='settings[<?= $code_id; ?>][spec][time][type]'>
                                                                            <option value=""><?= __('Disabled', 'custom-blocks-free'); ?></option>
                                                                            <option value='s' <?= (@$prop['spec']['time']['type'] == 's') ? 'selected' : ''; ?>><?= __('Seconds', 'custom-blocks-free'); ?></option>
                                                                            <option value='ms' <?= (@$prop['spec']['time']['type'] == 'ms') ? 'selected' : ''; ?>><?= __('Milliseconds', 'custom-blocks-free'); ?></option>
                                                                        </select>
                                                                        <span><?= __('Effects', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='checkbox' name='settings[<?= $code_id; ?>][spec][width][status]' <?= (isset($prop['spec']['width']['status'])) ? 'checked' : ''; ?>>
                                                                        <input disabled="" type='number' min="0" name='settings[<?= $code_id; ?>][spec][width][value]' value='<?= (@$prop['spec']['width']['value']) ? $prop['spec']['width']['value'] : ''; ?>'> <?= __('Change width in ', 'custom-blocks-free'); ?>
                                                                        <select disabled="" name='settings[<?= $code_id; ?>][spec][width][type]'>
                                                                            <option value='px' <?= (isset($prop['spec']['width']['type']) && $prop['spec']['width']['type'] == 'px') ? 'selected' : ''; ?>><?= __('Pixels', 'custom-blocks-free'); ?></option>
                                                                            <option value='%' <?= (isset($prop['spec']['width']['type']) && $prop['spec']['width']['type'] == '%') ? 'selected' : ''; ?>><?= __('Percent', 'custom-blocks-free'); ?></option>
                                                                        </select>
                                                                        <br>
                                                                        <i><?= __('* if you change width in percent, you should specify it in percent as well.', 'custom-blocks-free'); ?></i><br>
                                                                        <input disabled="" type='checkbox' name='settings[<?= $code_id; ?>][spec][opacity][status]' <?= (isset($prop['spec']['opacity']['status'])) ? 'checked' : ''; ?>>
                                                                        <input disabled="" type='number' min="0" max="1" step="0.1" name='settings[<?= $code_id; ?>][spec][opacity][value]' value='<?= (@$prop['spec']['opacity']['value']) ? $prop['spec']['opacity']['value'] : ''; ?>'> <?= __('Opacity transform (from 0.1 to 1.0)', 'custom-blocks-free'); ?><br>
                                                                        <input disabled="" type='checkbox' class="js_rotate_radio" data-code-id="<?= $code_id; ?>" name='settings[<?= $code_id; ?>][spec][rotate][status]' <?= (isset($prop['spec']['rotate']['status'])) ? 'checked' : ''; ?>>
                                                                        <input disabled="" type='number'  min="0" step="1" name='settings[<?= $code_id; ?>][spec][rotate][value]' value='<?= (@$prop['spec']['rotate']['value']) ? $prop['spec']['rotate']['value'] : ''; ?>'> <?= __('Rotation (in degrees)', 'custom-blocks-free'); ?><br>
                                                                        <input disabled="" type='checkbox' class="js_scale_radio" data-code-id="<?= $code_id; ?>" name='settings[<?= $code_id; ?>][spec][scale][status]' <?= (isset($prop['spec']['scale']['status'])) ? 'checked' : ''; ?>>
                                                                        <input disabled="" type='number' min="0" step="0.1" name='settings[<?= $code_id; ?>][spec][scale][value]' value='<?= (@$prop['spec']['scale']['value']) ? $prop['spec']['scale']['value'] : ''; ?>'> <?= __('Enlargement (scale multiplier)', 'custom-blocks-free'); ?><br>
                                                                        <i><?= __('* You can choose rotation or enlargement', 'custom-blocks-free'); ?></i><br>
                                                                        <input disabled="" type='checkbox' name='settings[<?= $code_id; ?>][spec][top][status]' <?= (isset($prop['spec']['top']['status'])) ? 'checked' : ''; ?>>
                                                                        <input disabled="" type='number' min="0" step="1" name='settings[<?= $code_id; ?>][spec][top][value]' value='<?= (@$prop['spec']['top']['value']) ? $prop['spec']['top']['value'] : ''; ?>'> <?= __('Move down (in pixels)', 'custom-blocks-free'); ?><br>


                                                                    </td>
                                                                </tr>
                                                                <tr><th>
                                                                        <?= __('Shake Effects', 'custom-blocks-free'); ?>
                                                                    </th></tr>
                                                                <tr>
                                                                    <td>
                                                                        <span><?= __('Standart scripts:', 'custom-blocks-free'); ?></span>
                                                                        <select disabled="" name='settings[<?= $code_id; ?>][js][type_var]'>
                                                                            <option value=""><?= __('On hover', 'custom-blocks-free'); ?></option>
                                                                            <option value='click_stop' <?= (@$prop['js']['type_var'] == 'click_stop') ? 'selected' : ''; ?>><?= __('On click (stop shaking)', 'custom-blocks-free'); ?></option>
                                                                            <option value='click_activate' <?= (@$prop['js']['type_var'] == 'click_activate') ? 'selected' : ''; ?>><?= __('On click (activate shaking)', 'custom-blocks-free'); ?></option>
                                                                            <option value='click_activate_time' <?= (@$prop['js']['type_var'] == 'click_activate_time') ? 'selected' : ''; ?>><?= __('On click (activate shaking for several seconds)', 'custom-blocks-free'); ?></option>
                                                                            <option value='non_stop' <?= (@$prop['js']['type_var'] == 'non_stop') ? 'selected' : ''; ?>><?= __('Nonstop', 'custom-blocks-free'); ?></option>
                                                                            <option value='pulse' <?= (@$prop['js']['type_var'] == 'pulse') ? 'selected' : ''; ?>><?= __('Pulsing', 'custom-blocks-free'); ?></option>
                                                                        </select>
                                                                        <span><?= __("Horizontal dispersion (in pixels)", 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='number' min="0" step="1" name='settings[<?= $code_id; ?>][js][x]' value='<?= (@$prop['js']['x']) ? $prop['js']['x'] : ''; ?>'>
                                                                        <span><?= __("Vertical dispersion (in pixels)", 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='number' min="0" step="1" name='settings[<?= $code_id; ?>][js][y]' value='<?= (@$prop['js']['y']) ? $prop['js']['y'] : ''; ?>'>
                                                                        <span><?= __('Rotation (degrees) ', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='number' min="0" step="1" name='settings[<?= $code_id; ?>][js][rotation]' value='<?= (@$prop['js']['rotation']) ? $prop['js']['rotation'] : ''; ?>'>
                                                                        <span><?= __('Rotation speed (less the number, more it shakes)', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type='number' min="0" step="1" name='settings[<?= $code_id; ?>][js][speed]' value='<?= (@$prop['js']['speed']) ? $prop['js']['speed'] : ''; ?>'>
                                                                        <span><?= __('Opacity:', 'custom-blocks-free'); ?></span>
                                                                        <input disabled="" type="radio" name="settings[<?= $code_id; ?>][js][opacity]" value=""><?= __('Disabled', 'custom-blocks-free'); ?><br>
                                                                        <input disabled="" type="radio" name="settings[<?= $code_id; ?>][js][opacity]" value="true" <?= (@$prop['js']['opacity'] == 'true') ? 'checked' : ''; ?>><?= __('Enabled', 'custom-blocks-free'); ?><br>
                                                                        <input disabled="" type="radio" name="settings[<?= $code_id; ?>][js][opacity]" value="coffee" <?= (@$prop['js']['opacity'] == 'coffee') ? 'checked' : ''; ?>><?= __('Coffee', 'custom-blocks-free'); ?><br>
                                                                        <input disabled="" type="radio" name="settings[<?= $code_id; ?>][js][opacity]" value="number" <?= (@$prop['js']['opacity'] == 'number') ? 'checked' : ''; ?>><input type='number' min="0" max="1" step="0.1" name='settings[<?= $code_id; ?>][js][opacity_value]'  disabled="" value='<?= (@$prop['js']['opacity_value']) ? $prop['js']['opacity_value'] : ''; ?>' placeholder=""> <?= __('Custom (from 0.1 to 1.0)', 'custom-blocks-free'); ?>
                                                                    </td>
                                                                </tr>
                                                                <?PHP if ($remote_from) { ?>
                                                                    <tr>
                                                                        <td>
                                                                            <p id="template_title<?= $code_id; ?>" style="display:none;"><span><?= __('Title', 'custom-blocks-free'); ?></span><input type='text' class="title_test" name='template[<?= $code_id; ?>][title]'></p>
                                                                        </td>
                                                                    </tr>
                                                                <?PHP } ?>
                                                            </tbody>
                                                            <?PHP if ($code_id == 0) { ?><tfoot>
                                                                    <tr><td><?php submit_button(__('Save Style', 'custom-blocks-free')); ?></td></tr>
                                                                </tfoot><?PHP } ?>
                                                        </table>
                                                        <?PHP if ($code_id == 0) { ?></form><?PHP } ?>
                                                </div>
                                                <script>
                                                    jQuery(document).ready(function ($) {
                                                        $('.colorSelect').ColorPicker({
                                                            color: '#0000ff',
                                                            onShow: function (colpkr) {
                                                                $(colpkr).fadeIn(500);
                                                                return false;
                                                            },
                                                            onHide: function (colpkr) {
                                                                $(colpkr).fadeOut(500);
                                                                return false;
                                                            },
                                                            onChange: function (hsb, hex, rgb) {
                                                                var el = $(this).data('colorpicker').el;
                                                                $(el).val('#' + hex);
                                                            }
                                                        });
                                                    });
                                                </script>

                                                <?PHP
                                            }

                                            public static function check_template_used($id) {
                                                global $wpdb;
                                                if ($id_ads_items = $wpdb->get_col('SELECT id_item FROM ' . $wpdb->prefix . 'custom_block_decor_link WHERE id_decor=' . $id . ' AND id_item is not NULL')) {
                                                    $titles = $wpdb->get_col('SELECT title FROM ' . $wpdb->prefix . 'custom_block_item WHERE id IN (' . implode(',', $id_ads_items) . ')');
                                                    if ($titles) {
                                                        echo '<span style="color:green;">'. __('Used in', 'custom-blocks-free').':</span><br>';
                                                        echo implode('<br>', $titles);
                                                    } else {
                                                        echo '<span style="color:red;">'. __('Not in use', 'custom-blocks-free').'</span>';
                                                    }
                                                } else {
                                                    echo '<span style="color:red;">'.__('Not in use', 'custom-blocks-free').'</span>';
                                                }
                                            }

                                            public function admin_page_decor_list() {
                                                global $wpdb;
                                                ?>
                                                <div class="wrap">
                                                    <h2><?= __('Style Templates', 'custom-blocks-free'); ?><a href="admin.php?page=custom-blocks-decor&action=new" class="add-new-h2"><?= __('Add a new style', 'custom-blocks-free'); ?></a></h2><hr>
                                                    <?PHP
                                                    $get_id_template = $wpdb->get_col("SELECT DISTINCT id_decor FROM " . $wpdb->prefix . "custom_block_decor_link WHERE active='1'");
                                                    if ($get_id_template) {
                                                        $list = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "custom_block_decor WHERE id IN (" . implode(',', $get_id_template) . ") limit 0,3", ARRAY_A);
                                                    } else {
                                                        $list = '';
                                                    }
                                                    if (!$list) {
                                                        echo __('No styles yet.', 'custom-blocks-free');
                                                    } else {
                                                        wp_enqueue_script('custom-blocks-jrumble-js', plugins_url('/js/jquery.jrumble.1.3.min.js', __FILE__), array('jquery'), "1.0.0", false);
                                                        ?>
                                                        <div class="alignleft actions bulkactions">
                                                            <a class="button" onclick="jQuery('.admin-block').show(100);"><?= __('Show All', 'custom-blocks-free'); ?></a><a>
                                                            </a><a class="button" onclick="jQuery('.admin-block').hide(100);"><?= __('Hide All', 'custom-blocks-free'); ?></a><a>
                                                            </a></div>
                                                        <table class="wp-list-table widefat plugins">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col" id="name" class="manage-column column-name" style=""><?= __('Title', 'custom-blocks-free'); ?></th>
                                                                    <th scope="col" id="decor" class="manage-column column-name" style=""><?= __('Used', 'custom-blocks-free'); ?></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?PHP foreach ($list as $val_list) { ?>
                                                                    <tr id="cuty-code-blocks" class="inactive">
                                                                        <td class="plugin-title">
                                                                            <strong><b><?= $val_list['name']; ?></b></strong>
                                                                            <div class="row-actions visible">
                                                                                <span class="edit"><a href="admin.php?page=custom-blocks-decor&action=update&id=<?= $val_list['id']; ?>" title="<?= __('Edit Block', 'custom-blocks-free'); ?>" class="edit"><?= __('Edit', 'custom-blocks-free'); ?></a> | </span>
                                                                                <span class="delete"><a onclick="return confirm('<?= __('Are you sure you want to delete this style?', 'custom-blocks-free'); ?>')" href="admin.php?page=custom-blocks-decor&action=delete&id=<?= $val_list['id']; ?>" title="<?= __('Delete Block', 'custom-blocks-free'); ?>" class="delete"><?= __('Delete', 'custom-blocks-free'); ?></a> |
                                                                                </span>
                                                                                <span><a title="<?= __('Preview', 'custom-blocks-free'); ?>" class="js_preview_decor"><?= __('Preview', 'custom-blocks-free'); ?></a></span>
                                                                            </div><br>
                                                                            <div style="display:none;" class="admin-block" rel="<?= $val_list['id']; ?>"><?= $val_list['name']; ?></div>
                                                                            <?= CustomBlocksPlugin::get_styles(null, (string) $val_list['id']); ?>
                                                                        </td>
                                                                        <td>
                                                                            <?PHP CustomBlocksPlugin::check_template_used($val_list['id']); ?>   
                                                                        </td>
                                                                    </tr>
                                                                <?PHP } ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <th scope="col" class="manage-column column-name" style=""><?= __('Title', 'custom-blocks-free'); ?></th>
                                                                    <th scope="col" class="manage-column column-name" style=""><?= __('Used', 'custom-blocks-free'); ?></th>
                                                                </tr>
                                                            </tfoot>
                                                        </table>


                                                        <?PHP
                                                    }
                                                    ?>
                                                </div>
                                                <?PHP
                                            }

                                            public static function set_link_to_decor($id_decor, $id_item) {
                                                global $wpdb;
                                                $table_link = $wpdb->prefix . "custom_block_decor_link";
                                                //search decor link
                                                $count_linked = $wpdb->get_var("SELECT count(*) FROM " . $table_link . " WHERE id_item=" . (int) $id_item);
                                                if ($count_linked > 1) {
                                                    $query = $wpdb->get_results("SELECT * FROM " . $table_link . " WHERE id_item=" . (int) $id_item, ARRAY_A);
                                                    if ($query) {
                                                        $first_link_id = null;
                                                        foreach ($query as $query_value) {
                                                            if (!$first_link_id) {
                                                                $first_link_id = $query_value['id'];
                                                            } else {
                                                                $wpdb->delete($table_link, array('id' => $query_value['id']));
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($count_linked) {
                                                    if (isset($first_link_id)) {
                                                        $id_link = $first_link_id;
                                                    } else {
                                                        $id_link = $wpdb->get_var("SELECT id FROM " . $table_link . " WHERE id_item=" . (int) $id_item);
                                                    }
                                                    $wpdb->update($table_link, array('id_decor' => $id_decor, 'active' => 0), array('id' => $id_link));
                                                    //checking for no need decor
                                                } else {
                                                    //create new
                                                    $wpdb->insert($table_link, array('id_decor' => $id_decor, 'id_item' => $id_item, 'active' => 0));
                                                }
                                            }

                                            public function check_name_for_decor() {
                                                global $wpdb;
                                                if (isset($_REQUEST['title']) && $_REQUEST['title']) {
                                                    $query = $wpdb->get_var("SELECT Count(*) FROM " . $wpdb->prefix . "custom_block_decor WHERE name='" . sanitize_text_field($_REQUEST['title']) . "'");
                                                    if ($query == 0) {
                                                        $result = 1;
                                                    } else {
                                                        $result = 0;
                                                    }
                                                } else {
                                                    $result = 0;
                                                }
                                                echo json_encode(array('result' => $result));
                                                exit;
                                            }

                                            public function admin_page_decor() {
                                                global $wpdb;
                                                if (isset($_REQUEST['action'])) {
                                                    switch ($_REQUEST['action']) {
                                                        case 'new':
                                                            $this->admin_page_edit_block(0);
                                                            break;
                                                        case 'update':
                                                            if ((isset($_REQUEST['id'])) && $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "custom_block_decor WHERE id=" . (int) $_REQUEST['id']) > 0) {
                                                                $this->admin_page_edit_block($_REQUEST['id']);
                                                            } else {
                                                                $this->admin_page_decor_list();
                                                            }
                                                            break;
                                                        case 'delete':
                                                            if ((isset($_REQUEST['id'])) && $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "custom_block_decor WHERE id=" . (int) $_REQUEST['id']) > 0) {
                                                                $this->admin_page_decor_delete((int)$_REQUEST['id']);
                                                            }
                                                            break;
                                                        default:
                                                            $this->admin_page_decor_list();
                                                            break;
                                                    }
                                                } else {
                                                    $this->admin_page_decor_list();
                                                }
                                            }

                                            public function admin_temp_filter_settings() {

                                                $action = '';
                                                if (isset($_GET['action']) && $_GET['action']) {
                                                    $action = $_GET['action'];
                                                }
                                                switch ($action) {
                                                    case 'update':
                                                        if (isset($_GET['id']) && (int) $_GET['id']) {
                                                            $new = false;
                                                            $id = (int) $_GET['id'];
                                                        } else {
                                                            $new = true;
                                                            $id = 0;
                                                        }
                                                        $info = $this->template_filter_updater($id, $new);
                                                        include_once 'admin_pages/template_filter_update.php';
                                                        break;
                                                    case 'delete':
                                                        if (isset($_GET['id']) && $_GET['id']) {
                                                            global $wpdb;
                                                            $wpdb->query("DELETE FROM " . $wpdb->prefix . "custom_block_template WHERE id IN (" . implode(',', $_GET['id']) . ");");
                                                            $wpdb->query("DELETE FROM " . $wpdb->prefix . "custom_block_template_meta WHERE template_id IN (" . implode(',', $_GET['id']) . ");");
                                                            echo '<script type="text/javascript">window.location.href="/wp-admin/admin.php?page=custom-blocks-template-filter"</script>';
                                                            exit();
                                                        }
                                                        break;
                                                    case 'copy':
                                                        global $wpdb;
                                                        if (isset($_GET['id']) && (int) $_GET['id']) {
                                                            $id = (int) $_GET['id'];
                                                            $name_filter = $wpdb->get_var("SELECT name FROM " . $wpdb->prefix . "custom_block_template WHERE id=" . $id . ";");
                                                            $settings = $wpdb->get_results("SELECT meta_key,meta_value FROM " . $wpdb->prefix . "custom_block_template_meta WHERE template_id=" . $id . ";");
                                                            $wpdb->insert($wpdb->prefix . 'custom_block_template', array('name' => $name_filter . ' - копия'), array('%s'));
                                                            $id = $wpdb->insert_id;
                                                            if ($settings) {
                                                                foreach ($settings as $setting) {
                                                                    $wpdb->insert(
                                                                            $wpdb->prefix . 'custom_block_template_meta', array('template_id' => $id, 'meta_key' => $setting->meta_key, 'meta_value' => $setting->meta_value), array('%d', '%s', '%s')
                                                                    );
                                                                }
                                                            }
                                                        }
                                                        echo '<script type="text/javascript">window.location.href="/wp-admin/admin.php?page=custom-blocks-template-filter"</script>';
                                                        exit();
                                                        break;
                                                    default:
                                                        include_once 'admin_pages/template_filter.php';
                                                        break;
                                                }
                                            }

                                            public function template_filter_updater($id, $new = false) {
                                                global $wpdb;
                                                $id_tmp = $id;
                                                if ($new || $id == 0) {

                                                    if (isset($_POST['title']) && $_POST['title']) {
                                                        $title = sanitize_text_field($_POST['title']);
                                                        if (!$title) {
                                                            $title =  __('Untitled', 'custom-blocks-free'); 
                                                        }
                                                        $wpdb->insert($wpdb->prefix . 'custom_block_template', array('name' => $title), array('%s'));
                                                        $id = $wpdb->insert_id;
                                                    }
                                                } else {
                                                    if (isset($_POST['title']) && $_POST['title']) {
                                                        $title = sanitize_text_field($_POST['title']);
                                                        $wpdb->update($wpdb->prefix . 'custom_block_template', array('name' => $title), array('id' => $id));
                                                    }
                                                }
                                                $access_fields = array(
                                                    'geo',
                                                    'geo_country_access',
                                                    'geo_city_access',
                                                    'geo_country_ban',
                                                    'geo_city_ban',
                                                    'content',
                                                    'content_by_category',
                                                    'content_by_post',
                                                    'content_by_category_ban',
                                                    'content_by_post_ban',
                                                    'time',
                                                    'time_start_timetargeting',
                                                    'time_end_timetargeting',
                                                    'start_date_timetargeting',
                                                    'end_date_timetargeting',
                                                    'only_work_time',
                                                    'all_hours',
                                                    'only_holiday_time',
                                                );
                                                if (isset($_POST['spec_filter_template']) && $_POST['spec_filter_template'] && $id) {
                                                    $wpdb->query("DELETE FROM {$wpdb->prefix}custom_block_template_meta WHERE template_id = '{$id}' AND meta_key <> 'include_block'");
                                                    foreach ($access_fields as $field) {
                                                        if (isset($_POST[$field]) && $_POST[$field]) {
                                                            if (is_array($_POST[$field])) {
                                                                foreach ($_POST[$field] as $filed_array) {
                                                                    $wpdb->insert(
                                                                            $wpdb->prefix . 'custom_block_template_meta', array('template_id' => $id, 'meta_key' => $field, 'meta_value' => sanitize_text_field($filed_array)), array('%d', '%s', '%s')
                                                                    );
                                                                }
                                                            } else {
                                                                $wpdb->insert(
                                                                        $wpdb->prefix . 'custom_block_template_meta', array('template_id' => $id, 'meta_key' => $field, 'meta_value' => sanitize_text_field($_POST[$field])), array('%d', '%s', '%s')
                                                                );
                                                            }
                                                        }
                                                    }
                                                    if ($id_tmp == 0) {
                                                        echo '<script type="text/javascript">window.location.href="/wp-admin/admin.php?page=custom-blocks-template-filter&action=update&id=' . $id . '"</script>';
                                                        exit();
                                                    }
                                                }
                                                $results = $wpdb->get_results('SELECT meta_key,meta_value from ' . $wpdb->prefix . 'custom_block_template_meta WHERE template_id=' . $id);
                                                $result = array();
                                                foreach ($access_fields as $field) {
                                                    $result[$field] = '';
                                                }
                                                if ($results) {
                                                    foreach ($results as $res) {
                                                        $result[$res->meta_key] = $res->meta_value;
                                                    }
                                                }
                                                return $result;
                                            }

                                            public function list_template_selected($id, $name) {
                                                global $wpdb;
                                                $list = array();
                                                switch ($name) {
                                                    case 'geo_city_access':
                                                        $sql = "SELECT meta_value as mkey,name_ru as mval from " . $wpdb->prefix . "custom_block_template_meta "
                                                                . "INNER JOIN " . $wpdb->prefix . "geoip_sxgeo_cities ON " . $wpdb->prefix . "custom_block_template_meta.meta_value=" . $wpdb->prefix . "geoip_sxgeo_cities.id "
                                                                . "WHERE template_id=" . $id . " AND meta_key='" . $name . "';";
                                                        $list = $wpdb->get_results($sql);
                                                        break;
                                                    case 'geo_city_ban':
                                                        $sql = "SELECT meta_value as mkey,name_ru as mval from " . $wpdb->prefix . "custom_block_template_meta "
                                                                . "INNER JOIN " . $wpdb->prefix . "geoip_sxgeo_cities ON " . $wpdb->prefix . "custom_block_template_meta.meta_value=" . $wpdb->prefix . "geoip_sxgeo_cities.id "
                                                                . "WHERE template_id=" . $id . " AND meta_key='" . $name . "';";
                                                        $list = $wpdb->get_results($sql);
                                                        break;
                                                    case 'geo_country_access':
                                                        $sql = "SELECT meta_value as mkey,name_ru as mval from " . $wpdb->prefix . "custom_block_template_meta "
                                                                . "INNER JOIN " . $wpdb->prefix . "geoip_sxgeo_country ON " . $wpdb->prefix . "custom_block_template_meta.meta_value=" . $wpdb->prefix . "geoip_sxgeo_country.id "
                                                                . "WHERE template_id=" . $id . " AND meta_key='" . $name . "';";
                                                        $list = $wpdb->get_results($sql);
                                                        break;
                                                    case 'geo_country_ban':
                                                        $sql = "SELECT meta_value as mkey,name_ru as mval from " . $wpdb->prefix . "custom_block_template_meta "
                                                                . "INNER JOIN " . $wpdb->prefix . "geoip_sxgeo_country ON " . $wpdb->prefix . "custom_block_template_meta.meta_value=" . $wpdb->prefix . "geoip_sxgeo_country.id "
                                                                . "WHERE template_id=" . $id . " AND meta_key='" . $name . "';";
                                                        $list = $wpdb->get_results($sql);
                                                        break;
                                                    case 'content_by_post':
                                                        $sql = "SELECT meta_value as mkey,post_title as mval from " . $wpdb->prefix . "custom_block_template_meta "
                                                                . "INNER JOIN " . $wpdb->prefix . "posts ON " . $wpdb->prefix . "custom_block_template_meta.meta_value=" . $wpdb->prefix . "posts.ID "
                                                                . "WHERE template_id=" . $id . " AND meta_key='" . $name . "';";
                                                        $list = $wpdb->get_results($sql);
                                                        break;
                                                    case 'content_by_post_ban':
                                                        $sql = "SELECT meta_value as mkey,post_title as mval from " . $wpdb->prefix . "custom_block_template_meta "
                                                                . "INNER JOIN " . $wpdb->prefix . "posts ON " . $wpdb->prefix . "custom_block_template_meta.meta_value=" . $wpdb->prefix . "posts.ID "
                                                                . "WHERE template_id=" . $id . " AND meta_key='" . $name . "';";
                                                        $list = $wpdb->get_results($sql);
                                                        break;

                                                    case 'content_by_category':
                                                        $sql = "SELECT meta_value as mkey,name as mval from " . $wpdb->prefix . "custom_block_template_meta "
                                                                . "INNER JOIN " . $wpdb->prefix . "terms ON " . $wpdb->prefix . "custom_block_template_meta.meta_value=" . $wpdb->prefix . "terms.term_id "
                                                                . "WHERE template_id=" . $id . " AND meta_key='" . $name . "';";
                                                        $list = $wpdb->get_results($sql);
                                                        break;
                                                    case 'content_by_category_ban':
                                                        $sql = "SELECT meta_value as mkey,name as mval from " . $wpdb->prefix . "custom_block_template_meta "
                                                                . "INNER JOIN " . $wpdb->prefix . "terms ON " . $wpdb->prefix . "custom_block_template_meta.meta_value=" . $wpdb->prefix . "terms.term_id "
                                                                . "WHERE template_id=" . $id . " AND meta_key='" . $name . "';";
                                                        $list = $wpdb->get_results($sql);
                                                        break;
                                                }
                                                if ($list) {
                                                    foreach ($list as $element) {
                                                        echo '<option value="' . $element->mkey . '" selected="">' . $element->mval . '</option>';
                                                    }
                                                }
                                            }

                                        }

                                        $plugin = new CustomBlocksPlugin();
                                        