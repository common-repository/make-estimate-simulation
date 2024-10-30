<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
$form_table = $wpdb->prefix . 'est_form_table';
$field_table = $wpdb->prefix . 'est_field_table';
// 現在時刻
$now = current_time('mysql');
// form_id
$get_form_id = htmlentities($_REQUEST['form_id'], ENT_QUOTES, "UTF-8");
// 既存取得
$field_sql = "SELECT * FROM " . $field_table . " WHERE form_id = %d";
$field_prepared_sql = $wpdb->prepare($field_sql, $get_form_id);
$field_items = $wpdb->get_results($field_prepared_sql);
$field_items = json_decode(json_encode($field_items), true);

if (isset($_POST['update_data'])) {
  check_admin_referer( 'update-data' );

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
  $post_val['count_unit'] = isset($post_sanitize['count_unit']) ? $post_sanitize['count_unit'] : '';
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
  $count_unit_array = briskes_add_array($post_val['count_unit'], 'count_unit');
  $default_flg_array = briskes_add_array($post_val['default_flg'], 'default_flg');
  $parent_flg_array = briskes_add_array($post_val['parent_flg'], 'parent_flg');
  $parent_array = briskes_add_array($post_val['parent'], 'parent');
  // 同一keyを連想配列としてマージ
  $merge_array = array_merge_recursive($item_array, $notes_array, $select_type_array, $select_item_array, $fee_array, $count_flg_array, $count_unit_array, $default_flg_array, $parent_flg_array, $parent_array);
  // ★ insert01 : フォーム更新
  $wpdb->update(
    "$form_table",
    array(
      'title' => $title,
      'update_date' => $now,
    ),
    array('form_id' => $get_form_id),
  );

  $all_field_name = array_column($field_items, 'field_name');

  // マージした配列をそれぞれ回す
  foreach ($merge_array as $key => $each_array) {
    $item = isset($each_array['item']) ? $each_array['item'] : '';
    $notes = isset($each_array['notes']) ? $each_array['notes'] : '';
    $select_type = isset($each_array['select_type']) ? $each_array['select_type'] : '';
    $select_item = isset($each_array['select_item']) ? $each_array['select_item'] : '';
    $fee = isset($each_array['fee']) ? $each_array['fee'] : '';
    $count_flg = isset($each_array['count_flg']) ? $each_array['count_flg'] : '';
    $count_unit = isset($each_array['count_unit']) ? $each_array['count_unit'] : '';
    $default_flg = isset($each_array['default_flg']) ? $each_array['default_flg'] : '';
    $parent_flg = isset($each_array['parent_flg']) ? $each_array['parent_flg'] : '';
    $parent_id = isset($each_array['parent']) ? $each_array['parent'] : '';

    if ( $item == '' &&  $notes == '' &&  $select_type == '' &&  $select_item == '' && ($fee == '' || $fee == '0') && ($count_flg == '' || $count_flg == '0') &&  $count_unit == '' && ($default_flg == '' || $default_flg == '0') && ($parent_flg == '' || $parent_flg == '0') ) {
      $all_field_name[] = $key;
    }
    else {
      // ★ insert02 : フィールド更新 / 登録
      if ( in_array( $key, $all_field_name, false ) ) {
        $wpdb->update(
          "$field_table",
          array(
            'item' => $item,
            'notes' => $notes,
            'select_type' => $select_type,
            'select_item' => $select_item,
            'fee' => $fee,
            'count_flg' => $count_flg,
            'count_unit' => $count_unit,
            'default_flg' => $default_flg,
            'parent_flg' => $parent_flg,
            'parent_id' => $parent_id,
            'update_date' => $now,
          ),
          array(
            'form_id' => $get_form_id,
            'field_name' => $key
          )
        );
      } else {
        $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . "est_field_table (form_id,field_name,item,notes,select_type,select_item,fee,count_flg,count_unit,default_flg,parent_flg,parent_id,del_flg,register_date,update_date) VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',0,'%s','%s')", $get_form_id, $key, $item, $notes, $select_type, $select_item, $fee, $count_flg, $count_unit, $default_flg, $parent_flg, $parent_id, $now, $now));
      }
      // 削除されたフィールドを削除
      foreach ($all_field_name as $this_key => &$each_name) {
        if ($each_name == $key) {
          unset($all_field_name[$this_key]);
        }
      }
      unset($each_name);
    }
  }
  if (!empty(array_filter($all_field_name))) {
    foreach ($all_field_name as $del_name) {
      $wpdb->query($wpdb->prepare("DELETE FROM " .$wpdb->prefix . "est_field_table WHERE form_id = '%s' AND field_name = '%s'", $get_form_id, $del_name));
    }
  }
}