<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="wrap br-plg-est-register">
  <div class="bg"></div>
</div>

<?php
include_once(dirname(plugin_dir_path(__FILE__)) . '/data/data-register.php');
global $wpdb;
$form_table = $wpdb->prefix . 'est_form_table';
$future_id = $wpdb->get_col("SELECT form_id FROM $form_table");
$future_num = count($future_id);
if ( $future_num > 0 ) {
  $form_id = $future_id[count($future_id) - 1];
}
else {
  $form_id = 1;
}
?>

<script>
  var form_id = <?php echo esc_js( $form_id ); ?>;
  var timer = null;

  function redirect(id) {
    if (location.href == 'admin.php?page=estimate-simulation-edit&action=edited&form_id=' + form_id + '') {
      clearInterval(timer);
    } else {
      location.href = 'admin.php?page=estimate-simulation-edit&action=edited&form_id=' + form_id + '&update=new';
    }
  }
  timer = setInterval(redirect(form_id), 200);
</script>