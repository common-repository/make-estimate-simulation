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

    //Set parent defaults
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
      'recovery'      => sprintf('<a href="?page=%s&action=%s&form_id=%s">復元</a>', wp_kses_post( wp_unslash( $_REQUEST['page'] ) ), 'recovery', $item['form_id']),
      'delete'    => sprintf('<a href="?page=%s&action=%s&form_id=%s">完全に削除する</a>', wp_kses_post( wp_unslash( $_REQUEST['page'] ) ), 'delete', $item['form_id']),
    );

    // タイトル直下の行のコンテンツ
    return sprintf(
      '%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
      /*$1%s*/
      '<strong style="display: inline-block;">' . $item['title'] . '</strong>',
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


  // テーブルのカラムとタイトルを指定
  function get_columns()
  {
    $columns = array(
      'cb'        => '<input type="checkbox" />',
      'title'     => 'タイトル',
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
      'recovery' => '復元',
      'delete'    => '完全に削除する',
    );
    return $actions;
  }


  // バルクアクションの処理
  function process_bulk_action()
  {
    global $wpdb;

    if (isset($_REQUEST['form_id'])) {
      $form_id_array[0] = wp_kses_post( wp_unslash( $_REQUEST['form_id'] ) );
    } elseif (isset($_REQUEST['form'])) {
      $id_array = [];
      foreach ( $_REQUEST['form'] as $tmp_key => $tmp_data ) {
        $id_array[$tmp_key] = wp_kses_post( wp_unslash( $tmp_data ) );
      }
      $count = 0;
      foreach ($id_array as $this_id) {
        $get_id_sql = "SELECT form_id FROM " . $wpdb->prefix . "est_form_table" . " WHERE id = %d";
        $get_id_prepared_sql = $wpdb->prepare($get_id_sql, $this_id);
        $result = $wpdb->get_var($get_id_prepared_sql);
        $form_id_array[$count] = $result;
        $count++;
      }
    }

    // 完全に削除
    if ('delete' === $this->current_action()) {
      foreach ($form_id_array as $form_id) {
        $tmp_query = $wpdb->prepare("DELETE FROM " . $wpdb->prefix . "est_form_table WHERE form_id = %d", $form_id);
        $wpdb->query($tmp_query);
        $tmp_query = $wpdb->prepare("DELETE FROM " . $wpdb->prefix . "est_field_table WHERE form_id = %d", $form_id);
        $wpdb->query($tmp_query);
      }
    }
    // 復旧
    if ('recovery' === $this->current_action()) {
      foreach ($form_id_array as $form_id) {
        $wpdb->update($wpdb->prefix . "est_form_table", array('del_flg' => 0), array('form_id' => $form_id));
      }
    }
  }


  // データベースから値取得
  public function results_form_table()
  {
    global $wpdb;

    $form_table = $wpdb->prefix . 'est_form_table';
    $sql = "SELECT * FROM " . $form_table . " WHERE del_flg = %d";
    $prepared_sql = $wpdb->prepare($sql, 1);
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
function briskes_estSimulationListTrashTable()
{
  // インスタンスを作成
  $briskes_estSimulationListTable = new briskes_estimateSimulation_List_Table();
  // データの取得、準備、ソート、フィルタリング
  $briskes_estSimulationListTable->prepare_items();
?>
  <div class="wrap">
    <form id="movies-filter" method="get">
      <input type="hidden" name="page" value="<?php echo wp_kses_post( wp_unslash( $_REQUEST['page'] ) ) ?>" />
      <ul class="subsubsub">
        <li class="all"><a href="admin.php?page=estimate-simulation">すべて</a> | </li>
        <li class="trash"><a href="admin.php?page=estimate-simulation-trash" class="current">ごみ箱</a></li>
      </ul>
      <?php $briskes_estSimulationListTable->display() ?>
    </form>
  </div>
<?php
}

echo briskes_estSimulationListTrashTable();
?>