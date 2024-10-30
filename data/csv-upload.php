<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;
global $briskes_file_name;
global $briskes_upload_error;

// 登録されるフォームのIDを生成
$form_table = $wpdb->prefix . 'est_form_table';
$field_table = $wpdb->prefix . 'est_field_table';

$form_id_col = $wpdb->get_col("SELECT form_id FROM " . $wpdb->prefix . 'est_form_table');
$create_form_id = $form_id_col[count($form_id_col) - 1] + 1;

// 現在時刻
$now = current_time('mysql');

// est_form_table へ登録
$form_id = $create_form_id;
$title = $now;
$wpdb->query($wpdb->prepare("INSERT INTO $wpdb->prefix" . "est_form_table" . "(form_id,title,del_flg,register_date,update_date) VALUES('%s','%s',0,'%s','%s')", $form_id, $title, $now, $now));

$utf_8_flg = false;

$filepath = $briskes_file_name;

$file = new SplFileObject($filepath);
$file->setFlags(SplFileObject::READ_CSV);
$i = 0;
$count = 0;
$next_count = null;
$parent_count = 0;
// ファイル取得
foreach ($file as $key => $line) {
  $next_count = $count + 1;
  $previous_count = $count - 1;
  if ($i === 0) {

    // 文字コードを確認
    if ( mb_detect_encoding( $line[0] ) == 'UTF-8' ) {
      $csv_heads = $line;
      $utf_8_flg = true;
    }
    else {
      foreach ( $line as $tmp_key => $tmp_data ) {
        $line[$tmp_key] = wp_kses_post( wp_unslash( mb_convert_encoding($tmp_data, 'UTF-8', 'auto') ) );
      }
      $csv_heads = $line;
      $utf_8_flg = false;
    }

    $csv_heads_key = array_flip($csv_heads);
    $head_count = count($csv_heads);

    $i++;
    continue;
  }
  if ($line[0] != NULL) {
    if ( $utf_8_flg ) {
      foreach ( $line as $tmp_key => $tmp_data ) {
        $line[$tmp_key] = wp_kses_post( wp_unslash( $tmp_data ) );
      }
      $array[$count] = $line;
    }
    else {
      foreach ( $line as $tmp_key => $tmp_data ) {
        $line[$tmp_key] = wp_kses_post( wp_unslash( mb_convert_encoding($tmp_data, 'UTF-8', 'auto') ) );
      }
      $array[$count] = $line;
    }
  }

  $count++;
}

$array_count = 0;
$parent_id = 0;
$key_count = 0;
$my_key = 0;
$parent_key = 0;

if ( isset($array) && !empty($array) ) {

  foreach ($array as $key => $each) {
    $next_count = $array_count + 1;
    $previous_count = $array_count - 1;

    // var_dump($array[$next_count][0] . ' . ' . $each[0]);

    if (!empty($array[$next_count])) {
      // 次の配列がある時
      if (isset($array[$next_count]) && $array[$next_count][0] == $each[0] &&
        isset($array[$previous_count]) && $array[$previous_count][0] != $each[0]) {
        // 次の配列の「項目」 == 今の配列の「項目」 && 前の配列の「項目」 != 今の配列の「項目」
        $each['parent_flg'] = 1;
        $parent_id++;
        $key_count = 0;
        $my_key = $parent_id;
        if (!empty($array[$previous_count])) {
          $parent_key = $parent_key + 1;
          $my_key = $parent_key;
        }
        // var_dump(true);
      } elseif (!isset($array[$previous_count][0]) && $array[$next_count][0] != $each[0]) {
        // 前の配列が無い && 次の配列の「項目」 != 今の配列の「項目」
        $each['parent_flg'] = 1;
        $parent_id++;
        $key_count = 0;
        $my_key = $parent_id;
        if (!empty($array[$previous_count])) {
          $parent_key = $parent_key + 1;
          $my_key = $parent_key;
        }
        // var_dump(true);

      } elseif ($array[$next_count][0] != $each[0] && $array[$previous_count][0] != $each[0]) {
        $each['parent_flg'] = 1;
        $parent_id++;
        $key_count = 0;
        $my_key = $parent_id;
        if (!empty($array[$previous_count])) {
          $parent_key = $parent_key + 1;
          $my_key = $parent_key;
        }
      } else {
        // 上記以外の場合
        if (!isset($each['parent_flg'])) {
          $each['parent_flg'] = 0;
          $key_count++;
          $my_key = $parent_key . '_' . $key_count;
          // var_dump(false);
        }
      }
    } else {
      // 次の配列が無いとき
      if ($array[$previous_count][0] != $each[0]) {
        // 前の配列の「項目」 != 今の配列の「項目」
        $each['parent_flg'] = 1;
        $parent_id++;
        $my_key = $parent_id;
        if (!empty($array[$previous_count])) {
          $parent_key = $parent_key + 1;
          $my_key = $parent_key;
        }
        // var_dump(true);

      } else {
        // 上記以外の場合
        if (!isset($each['parent_flg'])) {
          $each['parent_flg'] = 0;
          $key_count++;
          $my_key = $parent_key . '_' . $key_count;
        }
        // var_dump(false);

      }

      // $my_key = $parent_key;
    }

    if (empty($array[$previous_count])) {
      $parent_key = $key + 1;
      $my_key = $parent_key;
      $parent_id = 0;
      $each['parent_flg'] = 1;
    }

    $each['parent_id'] = $parent_key;

    // データ整理
    $insert_key = 'key_' . $my_key;
    $item = $each[0];
    if (isset($each[1])) {
      $notes = $each[1];
    } else {
      $notes = '';
    }
    $select_type = $each[2];
    if (isset($each[3])) {
      $select_item = $each[3];
    } else {
      $select_item = '';
    }
    if (isset($each[4])) {
      $fee = $each[4];
    } else {
      $fee = 0;
    }
    if (isset($each[5]) && $each[5] == 'TRUE') {
      $count_flg = 1;
    } else {
      $count_flg = 0;
    }
    if (isset($each[6])) {
      $count_unit = $each[6];
    } else {
      $count_unit = '';
    }
    if (isset($each[7]) && $each[7] == 'TRUE') {
      $default_flg = 1;
    } else {
      $default_flg = 0;
    }
    $parent_flg = $each['parent_flg'];
    $parent_id = $each['parent_id'];

    // 以下DB操作
    $wpdb->query($wpdb->prepare("INSERT INTO " . $wpdb->prefix . 'est_field_table' . "(form_id,field_name,item,notes,select_type,select_item,fee,count_flg,count_unit,default_flg,parent_flg,parent_id,del_flg,register_date,update_date) VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',0,'%s','%s')", $form_id, $insert_key, $item, $notes, $select_type, $select_item, $fee, $count_flg, $count_unit, $default_flg, $parent_flg, $parent_id, $now, $now));


    $array_count++;
  }

}
else {
  $upload_message = "CSVファイルが空です。";
  $briskes_upload_error = 1;
}