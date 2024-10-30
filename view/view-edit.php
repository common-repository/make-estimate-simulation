<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function briskes_form_update_page()
{
  global $wpdb;

  // DB操作
  include(dirname(plugin_dir_path(__FILE__)) . '/data/data-update.php');

  // 該当フォームの情報取得
  $form_table = $wpdb->prefix . 'est_form_table';
  $field_table = $wpdb->prefix . 'est_field_table';
  $this_form_id = wp_kses_post( wp_unslash( $_REQUEST['form_id'] ) );
  $form_sql = "SELECT title FROM " . $form_table . " WHERE form_id = %d";
  $form_prepared_sql = $wpdb->prepare($form_sql, $this_form_id);
  $form_ttl = $wpdb->get_var($form_prepared_sql);

  // 該当フィールドの情報取得
  $field_sql = "SELECT * FROM " . $field_table . " WHERE form_id = %d";
  $field_prepared_sql = $wpdb->prepare($field_sql, $this_form_id);
  $field_items = $wpdb->get_results($field_prepared_sql);
  $field_items = json_decode(json_encode($field_items), true);

  // 配列をキーでソート
  function sortByKey($key_name, $sort_order, $array)
  {
    $sorted_array = [];
    foreach ($array as $key => $value) {
      $standard_key_array[$key] = $value[$key_name];
    }
    if ( !empty($standard_key_array) ) {
      array_multisort( $standard_key_array, $sort_order, $array );
    }

    return $array;
  }

  $sorted_array = sortByKey('field_name', SORT_ASC, $field_items);

  // 行数取得
  $line_sql = "SELECT * FROM " . $field_table . " WHERE parent_flg = %d AND  form_id = %d";
  $line_prepared_sql = $wpdb->prepare($line_sql, 1, $this_form_id);
  $line_items = $wpdb->get_results($line_prepared_sql);

  // 全ての行が無い不自然な状態
  $line_error_flg = false;
  if ( empty($line_items) ) {
    $line_error_flg = true;
  }


  $i = 0;
?>

<!-- 通知 -->
<?php if (isset($_REQUEST['update']) && $_REQUEST['update'] == 'new') echo '<div class="updated"><p>作成しました。</p></div>'; ?>
<?php if (isset($_REQUEST['update']) && $_POST) echo '<div class="updated"><p>更新しました。</p></div>'; ?>

<div class="wrap br-plg-est-register">
  <form class="form" name="new_form_register" action="admin.php?page=estimate-simulation-edit&action=edited&form_id=<?php echo esc_attr($this_form_id); ?>&update=true" method="post">
        <?php wp_nonce_field( 'update-data' ); ?>

    <h1 class="wp-heading-inline">シミュレーション編集</h1>
    <button type="submit" class="page-title-action" name="update_data">更新</button>
    <button type="button" class="page-title-action" onclick="briskes_preview()" name="plg_est_preview">プレビュー</button>
    <hr class="wp-header-end">
    <div class="content" style="margin-top: 28px;">
      <?php
      // エラーメッセージ
      if ( $line_error_flg ) :
        echo '<div class="error">不明なエラーが発生しました。新しく作り直してください。</div>';

      else : // 正常動作
      ?>

      <div id="titlediv">
        <input type="text" id="title" name="title" value="<?php echo esc_attr($form_ttl); ?>">
      </div>
      <div class="line">
        <input type="number" min="1" name="line" value="<?php echo esc_attr(count($line_items)); ?>" class="js--line_addition_counter">
        <button type="button" class="page-title-action js--line_addition_trigger">ステップ追加</button>
        <span class="est-table-list"><span class="shortcode_input_wrap"><input type="text" readonly="readonly" class="shortcode_input" value="[briskes_shortcode_form id=<?php echo esc_attr($this_form_id); ?>]"></span></span>
      </div>
      <table class="br-plg-est-select-table">
        <thead>
          <tr>
            <th>ステップ</th>
            <th>項目</th>
            <th>注意事項</th>
            <th>タイプ</th>
            <th>選択肢</th>
            <th>単価</th>
            <th>個数の有無</th>
            <th>個数の単位</th>
            <th>初期値</th>
          </tr>
        </thead>
        <?php foreach ($sorted_array as $key => $item) : ?>
          <?php
          // 保存されていたカウント取得
          $name_count = ltrim($item['field_name'], 'key_');
          if ( preg_match('/([0-9]+)_[0-9]+/', $name_count, $matches) ) {
            $base_parent_id = $matches[1];
          }
          
          // 親子関係判定
          if ($item['parent_flg'] == 1) {
            $authority = 'parent';
          } elseif ($item['parent_flg'] == 0) {
            $authority = 'child';
          }

          // 親 && セレクトボックスで「checkbox」または「radio」が選択されているとき、「選択肢追加」ボタンの表示を許可
          if ($authority == 'parent') {
            if ($item['select_type'] == 'checkbox' || $item['select_type'] == 'radio') {
              $add_item = true;
            } else {
              $add_item = false;
            }
          } else {
            $add_item = false;
          }
          
          // 「＋」ボタン（要素追加）を表示させるか判定
          if ($authority == 'parent') {
            if ($item['select_type'] == 'checkbox' || $item['select_type'] == 'radio'){
              $data_add_btn_flg = 'true';
            }else{
              $data_add_btn_flg = 'false';
            }
          } else {
            $data_add_btn_flg = 'true';
          }

          // <tr>クラス
          $tr_class;
          if ($add_item) {
            $tr_class = 'add-item';
          } else {
            $tr_class = 'added';
          }
          if ($authority == 'child') {
            $tr_class .= ' child';
          }

          // disabled
          if ($authority == 'child') {
            $disabled = 'disabled';
          }else{
            $disabled = '';
          }
          
          // required
          if ($authority == 'parent'){
            $required = 'required';
          }else{
            $required = '';
          }
          ?>
          <?php if ($authority == 'parent') echo '<tbody>'; // 行のかたまりごとに<tbody>で囲む ?>
          <tr class="<?php echo esc_attr($tr_class); ?>" data-add_btn_flg="<?php echo esc_attr($data_add_btn_flg); ?>" data-select_type="<?php echo esc_attr($item['select_type']); ?>">
            <!-- 行 -->
            <td data-type="count">
              <div class="br-plg-est-order-before-btn"></div>
              <div class="br-plg-est-num"><?php if ($item['parent_flg'] == 1) echo esc_html($item['parent_id'] + 1); ?></div>
              <div class="br-plg-est-order-after-btn"></div>
            </td>
            <!-- 項目 -->
            <td data-type="item">
              <textarea name="item[<?php echo esc_attr($name_count); ?>]" <?php echo esc_attr($required); ?> <?php echo esc_attr($disabled); ?>><?php echo esc_html($item['item']); ?></textarea>
            </td>
            <!-- 注意事項 -->
            <td data-type="notes">
              <textarea name="notes[<?php echo esc_attr($name_count); ?>]" <?php echo esc_attr($disabled); ?>><?php echo esc_html($item['notes']); ?></textarea>
            </td>
            <!-- タイプ -->
            <td data-type="select_type">
              <select name="select_type[<?php echo esc_attr($name_count); ?>]" value="<?php echo esc_html($item['select_type']); ?>" <?php echo esc_attr($disabled); ?>>
                <option value="none" <?php if ($item['select_type'] == 'none') echo 'selected'; ?>>選択肢なし</option>
                <option value="text" <?php if ($item['select_type'] == 'text') echo 'selected'; ?>>数値入力</option>
                <option value="checkbox" <?php if ($item['select_type'] == 'checkbox') echo 'selected'; ?>>チェックボックス</option>
                <option value="radio" <?php if ($item['select_type'] == 'radio') echo 'selected'; ?>>ラジオボタン</option>
              </select>
            </td>
            <!-- 選択肢 -->
            <td data-type="select_item">
              <textarea name="select_item[<?php echo esc_attr($name_count); ?>]"><?php echo esc_html($item['select_item']); ?></textarea>
            </td>
            <!-- 単価 -->
            <td data-type="fee">
              <input type="number" name="fee[<?php echo esc_attr($name_count); ?>]" min="0" value="<?php echo esc_html($item['fee']); ?>">
            </td>
            <!-- 個数の有無 -->
            <td data-type="count_flg">
              <input type="checkbox" name="count_flg[<?php echo esc_attr($name_count); ?>]" value="1" <?php if ($item['count_flg'] == 1) echo 'checked'; ?>>
            </td>
            <!-- 個数の単位 -->
            <td data-type="count_unit">
              <input type="text" name="count_unit[<?php echo esc_attr($name_count); ?>]" value="<?php if ($item['count_flg'] == 1) echo esc_html($item['count_unit']) ?>" <?php if ($item['count_flg'] != 1) echo 'disabled'; ?>>
            </td>
            <!-- 初期値 -->
            <td data-type="default_flg">
              <input type="checkbox" name="default_flg[<?php echo esc_attr($name_count); ?>]" value="1" <?php if ($item['default_flg'] == 1) echo 'checked'; ?>>
              <input name="parent_flg[<?php echo esc_attr($name_count); ?>]" type="hidden" value="<?php echo esc_attr($item['parent_flg']); ?>">
              <input name="parent[<?php echo esc_attr($name_count); ?>]" type="hidden" value="<?php echo esc_attr($base_parent_id); ?>">
              <button type="button" class="add-btn js--line_addition_trigger js--add_during" title="下に1行追加する">＋</button>
              <button type="button" class="del-btn" title="行を削除する"><img src="<?php echo esc_url(plugins_url().'/make-estimate-simulation/img/ico_trash.svg'); ?>" alt=""></button>
            </td>
          </tr>
          <?php if (count($sorted_array) > $i + 1 && $sorted_array[$i + 1]['parent_flg'] == 1) echo '</tbody>'; ?>
          <?php $i++ ?>
        <?php endforeach; ?>
      </table>
      <div class="line">
        <span>行</span>
        <input type="number" min="1" name="line" value="<?php echo esc_attr(count($line_items)); ?>" class="js--line_addition_counter">
        <button type="button" class="page-title-action js--line_addition_trigger">行追加</button>
      </div>
      <div class="btn-area" style="margin-top: 30px;">
        <button type="submit" class="page-title-action" name="update_data">更新</button>
        <button type="button" class="page-title-action" onclick="briskes_preview()" name="plg_est_preview">プレビュー</button>
      </div>

      <?php endif; // エラーではない正常動作のif文終了 ?>
    </div>
  </form>
</div>

<script type="text/javascript">
  // ページ移動の確認アラート
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }

  // プレビューボタン押下
  function briskes_preview() {
    window.open("admin.php?page=estimate-simulation-preview&form_id=" + <?php echo esc_attr($this_form_id); ?> + "", "hoge", 'width=1200, height=800');
  }
</script>
<?php
}
briskes_form_update_page();
?>