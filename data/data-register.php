<?php


if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$form_table = $wpdb->prefix . 'est_form_table';
$field_table = $wpdb->prefix . 'est_field_table';
// 現在時刻
$now = current_time('mysql');
$redirect_flg = 0;

// form_id定義(フォーム識別用)
$form_id_add = $wpdb->get_col("SELECT form_id FROM " . $wpdb->prefix . "est_form_table");
if (!$form_id_add) {
  $form_id = 1;
} else {
  $count = count($form_id_add) - 1;
  $form_id = $form_id_add[$count] + 1;
}

if (isset($_POST['submit_data'])) {
  check_admin_referer( 'regist-data' );
  $title = htmlentities($_POST['title'], ENT_QUOTES, "UTF-8");
  
  // POSTされた値の処理
  function briskes_add_array($item, $type)
  {
    if (isset($item)) {
      $output = array();
      if (!is_array($item)) {
        $item = [$item];
      }
      foreach ($item as $key => $val) {
        $output['key_' . $key] = array($type =>  strval(htmlentities($val, ENT_QUOTES, "UTF-8")));
      }
    }
    return $output;
  }

  $post_val = [];
  $post_sanitize = [];

  // サニタイズ処理
  foreach ( $_POST as $key => $val ) {
    if ( is_array($val) ) {
      foreach ( $val as $key_2 => $val_2 ) {
        $post_sanitize[ $key ][ $key_2 ] = wp_kses_post( wp_unslash( $val_2 ) );
      }
    }
    else {
      $post_sanitize[ $key ] = wp_kses_post( wp_unslash( $val ) );
    }
  }
  
  $post_val['item'] = isset($post_sanitize['item']) ? $post_sanitize['item'] : '';
  $post_val['notes'] = isset($post_sanitize['notes']) ? $post_sanitize['notes'] : '';
  $post_val['select_type'] = isset($post_sanitize['select_type']) ? $post_sanitize['select_type'] : '';
  $post_val['select_item'] = isset($post_sanitize['select_item']) ? $post_sanitize['select_item'] : '';
  $post_val['fee'] = isset($post_sanitize['fee']) ? $post_sanitize['fee'] : '';
  $post_val['count_flg'] = isset($post_sanitize['count_flg']) ? $post_sanitize['count_flg'] : '';
  $post_val['default_flg'] = isset($post_sanitize['default_flg']) ? $post_sanitize['default_flg'] : '';
  $post_val['parent_flg'] = isset($post_sanitize['parent_flg']) ? $post_sanitize['parent_flg'] : '';
  $post_val['parent'] = isset($post_sanitize['parent']) ? $post_sanitize['parent'] : '';
  
  // 値取得
  $item_array = briskes_add_array($post_val['item'], 'item');
  $notes_array = briskes_add_array($post_val['notes'], 'notes');
  $select_type_array = briskes_add_array($post_val['select_type'], 'select_type');
  $select_item_array = briskes_add_array($post_val['select_item'], 'select_item');
  $fee_array = briskes_add_array($post_val['fee'], 'fee');
  $count_flg_array = briskes_add_array($post_val['count_flg'], 'count_flg');
  $default_flg_array = briskes_add_array($post_val['default_flg'], 'default_flg');
  $parent_flg_array = briskes_add_array($post_val['parent_flg'], 'parent_flg');
  $parent_array = briskes_add_array($post_val['parent'], 'parent');

  if ( isset($_POST['count_unit']) ) {
    $count_unit_array = isset($post_sanitize['count_unit']) ? $post_sanitize['count_unit'] : '';
    // 同一keyを連想配列としてマージ
    $merge_array = array_merge_recursive($item_array, $notes_array, $select_type_array, $select_item_array, $fee_array, $count_flg_array, $count_unit_array, $default_flg_array, $parent_flg_array, $parent_array);
  }
  else {
    // 同一keyを連想配列としてマージ
    $merge_array = array_merge_recursive($item_array, $notes_array, $select_type_array, $select_item_array, $fee_array, $count_flg_array, $default_flg_array, $parent_flg_array, $parent_array);
  }

  // ★ insert01 : フォーム登録
  $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "est_form_table (form_id,title,del_flg,register_date,update_date) VALUES('%d','%s',0,'%s','%s')", $form_id, $title, $now, $now));
  // マージした配列をそれぞれ回す
  foreach ($merge_array as $key => $each_array) {
    $item = $each_array['item'];
    $notes = $each_array['notes'];
    $select_type = $each_array['select_type'];
    $select_item = $each_array['select_item'];
    $fee = $each_array['fee'];
    $count_flg = $each_array['count_flg'];
    $default_flg = $each_array['default_flg'];
    $parent_flg = $each_array['parent_flg'];
    $parent_id = $each_array['parent'];

    if ( isset($_POST['count_unit']) ) {
    $count_unit = $each_array['count_unit'];
    // ★ insert02 : フィールド登録
    $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "est_field_table (form_id,field_name,item,notes,select_type,select_item,fee,count_flg,count_unit,default_flg,parent_flg,parent_id,del_flg,register_date,update_date) VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',0,'%s','%s')", $form_id, $key, $item, $notes, $select_type, $select_item, $fee, $count_flg, $count_unit, $default_flg, $parent_flg, $parent_id, $now, $now));
    }
    else {
      // ★ insert02 : フィールド登録
      $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "est_field_table (form_id,field_name,item,notes,select_type,select_item,fee,count_flg,count_unit,default_flg,parent_flg,parent_id,del_flg,register_date,update_date) VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',0,'%s','%s')", $form_id, $key, $item, $notes, $select_type, $select_item, $fee, $count_flg, '', $default_flg, $parent_flg, $parent_id, $now, $now));
    }
  }
  $redirect_flg = 1;

  echo esc_html('
  <script>
  window.onload = function () {

    if (window.name != "any") {
        location.href="admin.php?page=estimate-simulation-edit&action=edit&form_id=' . $form_id . '";
        window.name = "any";
    } else {
        window.name = "";
    }

  }

  </script>');
}
