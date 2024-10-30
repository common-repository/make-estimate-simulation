<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function briskes_plugin_admin_page()
{
?>
  <div class="wrap">
    <h1 class="wp-heading-inline">ゴミ箱</h1>
  </div>

<?php

include(dirname(plugin_dir_path(__FILE__)).'/view/table/list-table-trash.php');
}
// echo ABSPATH . 'wp-admin/includes/upgrade.php';
briskes_plugin_admin_page();
?>