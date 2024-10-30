<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function briskes_form_register_page()
{
  // DB操作
  include(dirname(plugin_dir_path(__FILE__)) . '/data/data-register.php');
?>
  <div class="wrap br-plg-est-register">
    <form class="form" name="new_form_register" action="admin.php?page=estimate-simulation-progress" method="post" id="est_form_register_id">
    <?php wp_nonce_field( 'regist-data' ); ?>
      <h1 class="wp-heading-inline">シミュレーション新規追加</h1>
      <button type="submit" class="page-title-action" name="submit_data">保存</button>
      <a href="#" class="page-title-action">プレビュー</a>

      <div class="content">

        <div id="titlediv">
          <input type="text" placeholder="シミュレーション名入力" id="title" name="title">
        </div>
        <div class="line">
          <span>行</span>
          <input type="number" min="1" value="1" name="line" class="js--line_addition_counter">
          <button type="button" class="page-title-action js--line_addition_trigger">行追加</button>
        </div>
        <table class="br-plg-est-select-table">
          <thead>
            <tr>
              <th>行</th>
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
          <tbody>
            <tr>
              <td data-type="count">1</td>
              <td data-type="item">
                <textarea name="item[0]" required></textarea>
              </td>
              <td data-type="notes">
                <textarea name="notes[0]"></textarea>
              </td>
              <td data-type="select_type">
                <select name="select_type[0]">
                  <option value="none">選択肢なし</option>
                  <option value="text">数値入力</option>
                  <option value="checkbox">チェックボックス</option>
                  <option value="radio">ラジオボタン</option>
                </select>
              </td>
              <td data-type="select_item">
                <textarea name="select_item[0]"></textarea>
              </td>
              <td data-type="fee">
                <input type="number" name="fee[0]" min="0">
              </td>
              <td data-type="count_flg">
                <input name="count_flg[0]" type="hidden" value="0">
                <input type="checkbox" name="count_flg[0]" value="1">
              </td>
              <td data-type="count_unit">
                <input type="text" name="count_unit[0]" disabled>
              </td>
              <td data-type="default_flg">
                <input name="default_flg[0]" type="hidden" value="0">
                <input type="checkbox" name="default_flg[0]" value="1">
                <input name="parent_flg[0]" type="hidden" value="1">
                <input name="parent[0]" type="hidden" value="0">
                <button type="button" class="add-btn js--line_addition_trigger js--add_during" title="下に1行追加する">＋</button>
                <button type="button" class="del-btn" title="行を削除する"><img src="<?php echo esc_url(plugins_url().'/make-estimate-simulation/img/ico_trash.svg'); ?>" alt=""></button>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="line">
          <span>行</span>
          <input type="number" min="1" value="1" name="line" class="js--line_addition_counter">
          <button type="button" class="page-title-action js--line_addition_trigger">行追加</button>
        </div>
      </div>
    </form>
  </div>
  <script> if ( window.history.replaceState ) { window.history.replaceState( null, null, window.location.href ); } </script>
<?php
}
briskes_form_register_page();
?>