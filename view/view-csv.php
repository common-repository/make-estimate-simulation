<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function briskes_plugin_csv_page()
{
  // アップロード処理
  if (isset($_POST['import']) && isset($_FILES['csv_file'])) {
    check_admin_referer( 'import-csv' );
    $new_date = new DateTime();
    $datetime = $new_date->format('YmdHis');

    $wp_upload_file = wp_handle_upload($_FILES["csv_file"], array('test_form' => false));

    if (isset($wp_upload_file['file'])) {
      $upload_message = "「" . $_FILES["csv_file"]["name"] . "」をアップロードしました。";
      global $briskes_file_name;
      $briskes_file_name = $wp_upload_file['file'];
    }
    global $briskes_upload_error;
    include(dirname(plugin_dir_path(__FILE__)) . '/data/csv-upload.php');
  }



?>
  <div class="wrap csv estimate-simulation-csv">
    <h1 class="wp-heading-inline">CSV</h1>
    <?php 
    if (isset($upload_message)) {
      $echo_message_class = 'updated';
      if ( isset($briskes_upload_error) && $briskes_upload_error === 1 ) {
        $echo_message_class = 'notice notice-warning';
      }
      echo '<div class="' . esc_attr($echo_message_class) . '"><p>' . esc_html($upload_message) . '</p></div>'; 
    } 
    ?>
    <div class="box">
      <h2 class="title">インポート</h2>
      <div class="download">
        <p>テンプレートをダウンロード</p>
        <a href="<?php echo esc_url(plugins_url('make-estimate-simulation')); ?>/lib/import_template.csv" class="page-title-action">ダウンロード</a>
      </div>
      <p class="sub-txt">インポートするCSVファイルは<b class="red"> 必ず上記テンプレートと同じ形式 </b>にするようにしてください。<br>テンプレートの入力例をご確認したい方は、<a href="<?php echo esc_url(plugins_url('make-estimate-simulation')); ?>/lib/import_test.csv" class="link">こちら</a>から入力例をダウンロードしてください。</p>
      <form action="" enctype="multipart/form-data" method="POST" id="form" class="form_wrap">
        <?php wp_nonce_field( 'import-csv' ); ?>
        <input type="file" name="csv_file">
        <button type="submit" name="import" class="page-title-action" value="1">実行</button>
      </form>
    </div>
  </div>
<?php
}
briskes_plugin_csv_page();
?>