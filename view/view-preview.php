<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function briskes_preview_page()
{
?>

  <div class="br-plg-est-register">
    <div class="bg">
      <div class="inner-block" style="max-width: 1240px; margin: 0 auto; padding: 30px 20px;">
        <?php echo do_shortcode('[briskes_shortcode_form id=' . wp_kses_post( wp_unslash( $_REQUEST['form_id'] ) ) . ']'); ?>
      </div>
    </div>
  </div>
<?php
}
briskes_preview_page();
?>