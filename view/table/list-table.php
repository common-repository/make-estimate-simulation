<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ベースを読み込む
if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

// リストテーブル作成
class briskes_estimateSimulation_List_Table extends WP_List_Table
{
  // 参照するコンストラクタ設定
  function __construct()
  {
    global $status, $page;

    // デフォルト値
    parent::__construct(array(
      'singular'  => 'form',
      'plural'    => 'forms',
      'ajax'      => false
    ));
  }


  // カラム作成
  function column_default($item, $column_name)
  {
    switch ($column_name) {
      case 'title':
      case 'form_id':
        return $item[$column_name];
      default:
        return print_r($item, true);
    }
  }


  // タイトル
  function column_title($item)
  {
    // タイトル直下の行のアクション
    $actions = array(
      'edit'      => sprintf('<a href="?page=estimate-simulation-edit&action=edit&form_id=%s&update=false">編集</a>', $item['form_id']),
      'trash'    => sprintf('<a href="?page=%s&action=%s&form_id=%s">ごみ箱へ移動</a>', $_REQUEST['page'], 'trash', $item['form_id']),
    );

    // タイトル直下の行のコンテンツ
    return sprintf(
      '%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
      /*$1%s*/
      '<a href="?page=estimate-simulation-edit&action=edit&form_id=' . $item['form_id'] . '&update=false"><strong style="display: inline-block;">' . $item['title'] . '</strong></a>',
      /*$2%s*/
      $item['form_id'],
      /*$3%s*/
      $this->row_actions($actions)
    );
  }

  // チェックボックス
  function column_cb($item)
  {
    return sprintf(
      '<input type="checkbox" name="%1$s[]" value="%2$s" />',
      /*$1%s*/
      $this->_args['singular'],
      /*$2%s*/
      $item['ID'],
    );
  }

  // 日付
  function column_date($item)
  {
    return sprintf(
      $item['register_date']
    );
  }

  // ショートコード
  function column_shortcode($item)
  {
    return sprintf(
      '<span class="shortcode_input_wrap"><input type="text" readonly="readonly" class="shortcode_input" value="[briskes_shortcode_form id=' . $item['form_id'] . ']"></span>'
    );
  }


  // テーブルのカラムとタイトルを指定
  function get_columns()
  {
    $columns = array(
      'cb'        => '<input type="checkbox" />',
      'title'     => 'タイトル',
      'shortcode'     => 'ショートコード',
      'date'    => '日付',
    );
    return $columns;
  }


  // テーブルソート
  function get_sortable_columns()
  {
    $sortable_columns = array(
      'title'     => array('title', false),
      'form_id'    => array('form_id', false),
    );
    return $sortable_columns;
  }


  // バルクアクション
  function get_bulk_actions()
  {
    $actions = array(
      'trash' => 'ごみ箱へ移動',
    );
    return $actions;
  }


  // バルクアクションの処理
  function process_bulk_action()
  {
    // 「ごみ箱へ移動」が押されたら del_flg を 1 にする
    if ('trash' === $this->current_action()) {
      global $wpdb;
      $form_table = $wpdb->prefix . 'est_form_table';

      if (isset($_REQUEST['form_id'])) {
        $form_id_array[0] = wp_kses_post( wp_unslash( $_REQUEST['form_id'] ) );
      } else {
        $id_array = [];
        foreach ( $_REQUEST['form'] as $tmp_key => $tmp_data ) {
          $id_array[$tmp_key] = wp_kses_post( wp_unslash( $tmp_data ) );
        }
        $count = 0;
        foreach ($id_array as $this_id) {
          $get_id_sql = "SELECT form_id FROM " . $form_table . " WHERE id = %d";
          $get_id_prepared_sql = $wpdb->prepare($get_id_sql, $this_id);
          $result = $wpdb->get_var($get_id_prepared_sql);
          $form_id_array[$count] = $result;
          $count++;
        }
      }

      foreach ($form_id_array as $form_id) {
        $wpdb->update("$form_table", array('del_flg' => 1), array('form_id' => $form_id));
      }
    }
  }


  // データベースから値取得
  public function results_form_table()
  {
    global $wpdb;

    $form_table = $wpdb->prefix . 'est_form_table';
    $sql = "SELECT * FROM " . $form_table . " WHERE del_flg = %d";
    $prepared_sql = $wpdb->prepare($sql, 0);
    $results_form = $wpdb->get_results($prepared_sql);
    $results_form = json_decode(json_encode($results_form), true);

    return $results_form;
  }

  function prepare_items()
  {
    // 各ページの表示数
    $per_page = 10;


    // カラム定義
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();


    // カラムの配列作成
    $this->_column_headers = array($columns, $hidden, $sortable);


    // バルクアクション処理
    $this->process_bulk_action();


    // データベースにアクセス
    $data = $this->results_form_table();


    // 配列のデータをソート
    function usort_reorder($a, $b)
    {
      $orderby = (!empty($_REQUEST['orderby'])) ? wp_kses_post( wp_unslash( $_REQUEST['orderby'] ) ) : 'register_date';
      $order = (!empty($_REQUEST['order'])) ? wp_kses_post( wp_unslash( $_REQUEST['order'] ) ) : 'desc';
      $result = strcmp($a[$orderby], $b[$orderby]);
      return ($order === 'asc') ? $result : -$result;
    }
    usort($data, 'usort_reorder');


    // ページング 現在のページ番号を取得
    $current_page = $this->get_pagenum();

    // ページング 配列をカウント
    $total_items = count($data);


    // ページ分割
    $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);


    //itemsプロパティ追加 
    $this->items = $data;


    // ページング
    $this->set_pagination_args(array(
      'total_items' => $total_items,
      'per_page'    => $per_page,
      'total_pages' => ceil($total_items / $per_page)
    ));
  }
}


// テーブル作成
function briskes_briskes_estSimulationListTable()
{
  // インスタンスを作成
  $briskes_estSimulationListTable = new briskes_estimateSimulation_List_Table();
  // データの取得、準備、ソート、フィルタリング
  $briskes_estSimulationListTable->prepare_items();
?>
  <div class="est-table-list">
    <form id="est_table_list_form" method="get">
      <input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field($_REQUEST['page']) ) ?>" />
      <ul class="subsubsub">
        <li class="all"><a href="admin.php?page=estimate-simulation" class="current">すべて</a> | </li>
        <li class="trash"><a href="admin.php?page=estimate-simulation-trash">ごみ箱</a></li>
      </ul>
      <?php $briskes_estSimulationListTable->display() ?>
    </form>
  </div>
<?php
}

echo briskes_briskes_estSimulationListTable();
?>