<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function briskes_plugin_admin_page()
{
?>
  <div class="wrap">
    <h1 class="wp-heading-inline">見積もりシミュレーション</h1>
    <a href="admin.php?page=estimate-simulation-register" class="page-title-action">新規追加</a>
  </div>

<?php

include(dirname(plugin_dir_path(__FILE__)).'/view/table/list-table.php');
}
// echo ABSPATH . 'wp-admin/includes/upgrade.php';
briskes_plugin_admin_page();
?>