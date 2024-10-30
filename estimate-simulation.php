<?php
/*
Plugin Name: Make Estimate Simulation
Plugin URI:
Description: 見積りシミュレーション用のフォームを作成します。
Version: 1.0.9
Author:BRISK
Author URI: https://b-risk.jp/
License: GPL2
*/
if (!defined('ABSPATH')) exit;

define('BRISKES_MY_PLUGIN_VERSION', '1.0.9');
define('BRISKES_MY_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BRISKES_MY_PLUGIN_URL', plugins_url('/', __FILE__));


/**
 * 管理画面にメニュー追加
 */
add_action('init', 'EstimateSimulation::init');

class EstimateSimulation
{
  static function init()
  {
    return new self();
  }
  function __construct()
  {
    if (is_admin() && is_user_logged_in()) {
      // メニュー追加
      add_action('admin_menu', [$this, 'set_plugin_menu']);
      add_action('admin_menu', [$this, 'set_plugin_sub_menu']);
      // add_action('wp_enqueue_scripts', array(&$this, 'add_stylesheet'), 9999);
      add_action('wp_enqueue_scripts', 'register_admin_style');
    }
  }

  function plugin_admin_page()
  {
    // 管理画面TOP
    include(plugin_dir_path(__FILE__) . 'view/view-admin.php');
  }

  function show_register_form()
  {
    // 新規追加ページ
    include(plugin_dir_path(__FILE__) . 'view/view-register.php');
  }

  function show_csv_form()
  {
    // CSVページ
    include(plugin_dir_path(__FILE__) . 'view/view-csv.php');
  }

  function show_trash_form()
  {
    // ゴミ箱ページ
    include(plugin_dir_path(__FILE__) . 'view/view-trash.php');
  }

  function show_edit_form()
  {
    // 編集ページ
    include(plugin_dir_path(__FILE__) . 'view/view-edit.php');
  }

  function show_progress_form()
  {
    // 新規追加通過ページ
    include(plugin_dir_path(__FILE__) . 'view/view-register-progress.php');
  }

  function show_preview_page()
  {
    // プレビューページ
    include(plugin_dir_path(__FILE__) . 'view/view-preview.php');
  }

  function set_plugin_menu()
  {
    add_menu_page(
      '見積もりシミュレーション',           /* ページタイトル*/
      '見積もりシミュレーション',           /* メニュータイトル */
      'manage_options',         /* 権限 */
      'estimate-simulation',    /* ページを開いたときのURL */
      [$this, 'plugin_admin_page'],       /* メニューに紐づく画面を描画するcallback関数 */
      'dashicons-money-alt', /* アイコン see: https://developer.wordpress.org/resource/dashicons/#awards */
      99,                          /* 表示位置のオフセット */
    );
  }
  function set_plugin_sub_menu()
  {
    add_submenu_page(
      'estimate-simulation',  /* 親メニューのslug */
      '新規追加',
      '新規追加',
      'manage_options',
      'estimate-simulation-register',
      [$this, 'show_register_form']
    );
    add_submenu_page(
      'estimate-simulation',  /* 親メニューのslug */
      'CSV',
      'CSV',
      'manage_options',
      'estimate-simulation-csv',
      [$this, 'show_csv_form']
    );
    // add_submenu_page(
    //   'estimate-simulation',  /* 親メニューのslug */
    //   '設定',
    //   '設定',
    //   'manage_options',
    //   'estimate-simulation-config',
    //   [$this, 'show_config_form']
    // );
    add_submenu_page(
      'estimate-simulation',  /* 親メニューのslug */
      'ゴミ箱',
      'ゴミ箱',
      'manage_options',
      'estimate-simulation-trash',
      [$this, 'show_trash_form']
    );
    add_submenu_page(
      'estimate-simulation',  /* 親メニューのslug */
      '編集',
      '編集',
      'manage_options',
      'estimate-simulation-edit',
      [$this, 'show_edit_form']
    );
    add_submenu_page(
      'estimate-simulation',  /* 親メニューのslug */
      '',
      '',
      'manage_options',
      'estimate-simulation-progress',
      [$this, 'show_progress_form']
    );
    add_submenu_page(
      'estimate-simulation',  /* 親メニューのslug */
      '',
      '',
      'manage_options',
      'estimate-simulation-preview',
      [$this, 'show_preview_page']
    );
  }

  public function add_stylesheet()
  {

    if (is_admin() || !is_super_admin()) {
      return;
    }

    $stylesheet_path = plugins_url('css/admin-style.css', __FILE__);
    wp_register_style('admin-style', $stylesheet_path, array(), BRISKES_MY_PLUGIN_VERSION);
    wp_enqueue_style('admin-style');
  }
} // end of class

function briskes_add_init()
{
  wp_register_style('admin-style', plugins_url('css/admin-style.css', __FILE__));
  wp_enqueue_style('admin-style');
  // if ($_REQUEST['page'] == 'estimate-simulation-preview') {
    $plugin_url = plugin_dir_url(__FILE__);
    wp_enqueue_style('form_style', $plugin_url . 'css/form-style.css');
  // }
}
add_action('admin_init', 'briskes_add_init');

function briskes_add_form_css()
{
  $plugin_url = plugin_dir_url(__FILE__);
  wp_enqueue_style('form_style', $plugin_url . 'css/form-style.css');
}
add_action('wp_enqueue_scripts', 'briskes_add_form_css');

function briskes_add_form_js()
{
  $plugin_url = plugin_dir_url(__FILE__);
  wp_enqueue_script('form_js', $plugin_url . 'js/form-common.js', array( 'jquery' ), false, true);
}
add_action('wp_enqueue_scripts', 'briskes_add_form_js');

function briskes_add_top_js()
{
  wp_enqueue_script('jquery-lib');
  wp_register_script('top-common', plugins_url('js/top-common.js', __FILE__));
  wp_enqueue_script('top-common');
  // if ($_REQUEST['page'] == 'estimate-simulation-preview') {
    $plugin_url = plugin_dir_url(__FILE__);
    wp_enqueue_script('form_js', $plugin_url . 'js/form-common.js', array( 'jquery' ), false, true);
  // }
}
add_action('admin_init', 'briskes_add_top_js');

function briskes_add_bottom_init()
{

  wp_register_script('admin-common', plugins_url('js/admin-common.js', __FILE__));
  wp_enqueue_script('admin-common');
}
add_action('admin_footer', 'briskes_add_bottom_init');

/**
 * 有効化でDB作成 / 削除でDB削除
 */
register_activation_hook(__FILE__, 'briskes_create_data');
register_uninstall_hook(__FILE__, 'briskes_delete_data');

global $briskes_jal_db_version;
$briskes_jal_db_version = '1.1';

function briskes_create_data()
{
  global $wpdb;

  $form_table = $wpdb->prefix . 'est_form_table';
  $field_table = $wpdb->prefix . 'est_field_table';
  $charset_collate = $wpdb->get_charset_collate();

  if ($wpdb->get_var($wpdb->prepare("show tables like '%s'", $form_table)) != $form_table) {
    $sql = "CREATE TABLE  {$form_table} (
            ID int NOT NULL AUTO_INCREMENT,
            form_id int,
            title VARCHAR(400),
            del_flg int,
            register_date datetime,
            update_date datetime,
            PRIMARY KEY  (ID)
            ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  if ($wpdb->get_var($wpdb->prepare("show tables like '%s'", $field_table)) != $field_table) {
    $sql = "CREATE TABLE  {$field_table} (
            ID int NOT NULL AUTO_INCREMENT,
            form_id int,
            field_name VARCHAR(400),
            -- field_type VARCHAR(400),
            item VARCHAR(400),
            notes VARCHAR(400),
            select_type VARCHAR(400),
            select_item VARCHAR(400),
            fee int,
            count_flg int,
            count_unit VARCHAR(400),
            default_flg int,
            parent_flg int,
            parent_id int,
            del_flg int,
            register_date datetime,
            update_date datetime,
            PRIMARY KEY  (ID)
            ) $charset_collate";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
}

function briskes_delete_data()
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'est_form_table';
  $sql = "DROP TABLE IF EXISTS {$table_name}";
  $wpdb->query($sql);
}
// 更新時DB更新
$installed_ver = get_option("briskes_jal_db_version");

if ($installed_ver != $briskes_jal_db_version) {
  global $wpdb;

  $form_table = $wpdb->prefix . 'est_form_table';
  $field_table = $wpdb->prefix . 'est_field_table';
  $charset_collate = $wpdb->get_charset_collate();

  if ($wpdb->get_var($wpdb->prepare("show tables like '%s'", $form_table)) != $form_table) {
    $sql = "CREATE TABLE  {$form_table} (
            ID int NOT NULL AUTO_INCREMENT,
            form_id int,
            title VARCHAR(400),
            del_flg int,
            register_date datetime,
            update_date datetime,
            PRIMARY KEY  (ID)
            ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  if ($wpdb->get_var($wpdb->prepare("show tables like '%s'", $field_table)) != $field_table) {
    $sql = "CREATE TABLE  {$field_table} (
            ID int NOT NULL AUTO_INCREMENT,
            form_id int,
            field_name VARCHAR(400),
            -- field_type VARCHAR(400),
            item VARCHAR(400),
            notes VARCHAR(400),
            select_type VARCHAR(400),
            select_item VARCHAR(400),
            fee int,
            count_flg int,
            count_unit VARCHAR(400),
            default_flg int,
            parent_flg int,
            parent_id int,
            del_flg int,
            register_date datetime,
            update_date datetime,
            PRIMARY KEY  (ID)
            ) $charset_collate";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }

  update_option("briskes_jal_db_version", $briskes_jal_db_version);
}


function briskes_sortByKey_item($key_name, $sort_order, $array)
{
  foreach ($array as $key => $value) {
    $standard_key_array[$key] = $value[$key_name];
  }

  array_multisort($standard_key_array, $sort_order, $array);

  return $array;
}


// ショートコード作成
function briskes_show_test_test($id)
{
  if ( !isset($id) || !isset($id['id']) ) {
    return false;
  }

  $form_id = $id['id'];
  global $wpdb;
  $field_table = $wpdb->prefix . 'est_field_table';
  $field_sql = "SELECT * FROM " . $field_table . " WHERE form_id = %d";
  $field_prepared_sql = $wpdb->prepare($field_sql, $form_id);
  $field_items = $wpdb->get_results($field_prepared_sql);

  if ( !$field_items ) {
    return false;
  }

  $field_items = json_decode(json_encode($field_items), true);

  $sort_items = briskes_sortByKey_item('field_name', SORT_ASC, $field_items);
  $count = 0;
  $array_count = count($sort_items);

  // 出力タグ作成
  $echo_tag = '';
  $table_start = '<form class="est-form"><table class="est-table-form"><tbody>';
  $table_end = '<tr><th><p class="name">合計</p></th><td><p class="price"><span class="num">0</span>円</p></td></tr></tbody></table></form>';
  $echo_tag = $table_start;

  $parent_item = [];
  foreach ($sort_items as $item) {
    if ( $item['parent_flg'] === '1' ) {
      $parent_item = $item;
    }

    $next_count = $count + 1;
    // 先頭の要素の時、以下出力
    if ($item['parent_flg'] == 1) {
      $echo_tag .= '<tr class="' . $item['parent_id'] . '">';
      $echo_tag .= '<th><p class="name">' . nl2br($item['item']) . '</p>';
      if ( $item['select_type'] != 'none' ) {
        $echo_tag .= '<p class="note">' . nl2br($item['notes']) . '</p>';
      }
      $echo_tag .= '</th><td>';
    }
    // テキストエリアではないとき、以下出力
    if ($item['select_type'] != 'textarea') {
      $sub = '';
      $count_tag = '';
      // input[type="text"]の場合、数字フィールドに変更
      if ($item['select_type'] == 'text') {
        $type_item = 'number';
        $sub = 'min = 0';
        $input = '';
      } else {
        if ( $item['parent_id'] > 0 ) {
          $type_item = $parent_item['select_type'];
          $item_parent_id = $item['parent_id'];
        }
        else {
          $type_item = $item['select_type'];
          if ( preg_match('/key_([0-9]+)_[0-9]+/', $item['field_name'], $matches) ) {
            $item_parent_id = $matches[1];
          }
          else {
            $item_parent_id = '';
          }
        }
        if ($item['default_flg']) {
          $checked = 'checked';
        } else {
          $checked = '';
        }
        if ( $type_item != 'none' ) {
          $input = '<label><input type="' . $type_item . '" name="' . $type_item . '_' . $item_parent_id . '" ' . $checked . '><span class="' . $type_item . '_input"></span><span class="input-sub">' . $item['select_item'] . '</span></label>';
        }
        else {
          $input = '';
          $echo_tag .= '<p class="note">' . nl2br($item['notes']) . '</p>';
        }
      }
      // 個数をカウントさせる場合、以下出力
      if ($item['count_flg'] == 1 || $item['select_type'] == 'text') {
        if ($item['select_type'] == 'text') {
          $count_tag = '<div class="count-input"><p class="before-text">' . nl2br($item['select_item']) . '</p><label><input type="number" name="count_field" min=0 value="' . ($item['default_flg'] ? 1 : 0) . '"><span class="input-sub">' . $item['count_unit'] . '</span></label></div>';
        } else {
          $count_tag = '<div class="count-input"><label><input type="number" name="count_field" min=0 value="' . ($item['default_flg'] ? 1 : '') . '"><span class="input-sub">' . $item['count_unit'] . '</span></label></div>';
        }
      }
      // 総出力タグ
      $echo_tag .= '<div class="item">' . $input . '' . $count_tag . '<input type="hidden" name="price" value="' . $item['fee'] . '"></div>';
    } else {
      $echo_tag .= '<div class="item"><textarea rows="5" name="' . $item['select_type'] . '_' . $item['parent_id'] . '"></textarea><p class="textarea-sub">' . $item['select_item'] . '</p></div>';
    }
    // 最後の要素の場合
    if ($next_count == $array_count) {
      $tr_end_flg = true;
    } elseif ($sort_items[$next_count]['parent_flg'] == 1) {
      // 次の要素のparent_flg = 1 (= 最後の子要素)の場合
      $tr_end_flg = true;
    } else {
      $tr_end_flg = false;
    }

    if ($tr_end_flg) {
      $echo_tag .= '</td></tr>';
    }

    $count++;
  }

  $echo_tag .= $table_end;
  return $echo_tag;
}
add_shortcode('briskes_shortcode_form', 'briskes_show_test_test');
