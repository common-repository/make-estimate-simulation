(function ($) {
  'use strict';

  $(function () {
    // 管理画面 サブメニュー削除
    if ($('.wp-submenu')[0]) {
      function search(slug) {
        $('.wp-submenu li a[href*=' + slug + ']').closest('.wp-submenu li').remove();
      }
      // ゴミ箱
      search('estimate-simulation-trash');
      search('estimate-simulation-edit');
      search('estimate-simulation-progress');
    }
  });

  $(function () {
    // 登録・編集ページ
    if ($('.br-plg-est-register')[0]) {
      const fn = {
        /**
         * 「＋ / ゴミ箱」ボタン表示のフラグを付け替え
         * @param {Element} $this 「タイプ」選択のセレクトボックス
         */
        replacementAddBtnFlg: function ($this) {
          const field_val = $this.val(),
            $parent_tbody = $this.parents('tbody'),
            $tr = $parent_tbody.find('tr');;

          if (field_val == 'checkbox' || field_val == 'radio') {
            $tr.attr('data-add_btn_flg', true);
          } else {
            $tr.attr('data-add_btn_flg', false);
          }
        },

        /**
         * 「タイプ」変更時のデータ反映と表示制御
         * @param {Element} $this 「タイプ」選択のセレクトボックス
         */
        setType: function ($this) {
          const field_val = $this.val(),
            $tbody = $this.parents('tbody'),
            $tr = $tbody.find('tr');

          // <tr>にデータタイプを反映
          $tr.attr('data-select_type', field_val);
          // <tbody>内の全てのセレクトボックスに反映
          $tr.not($this).find('select[name^=select_type]').val(field_val);
        },

        /**
         * 列削除
         * @param {Element} $trigger 「ゴミ箱」ボタン
         */
        removeLine: function ($trigger) {
          const $tr = $trigger.parents('tr'),
            $tbody = $trigger.parents('tbody');

          if ($tr.hasClass('child')) {
            $tr.remove();
          } else {
            if ( $('.br-plg-est-select-table tbody').length > 0 ) {  // 最後の一つは消さない
              $tbody.remove();
            }
          }
        },

        /**
         * 行カウント
         */
        countLine: function () {
          const $tbody = $('.br-plg-est-select-table').find('tbody'),
            length = $tbody.length,
            $add_counter = $('.js--line_addition_counter');

          var count = 1;

          // 「行」の数値を振る
          $tbody.each(function () {
            const $tr = $(this).find('tr').first();
            $tr.find("[data-type=count] .br-plg-est-num").text(length - (length - count));
            count++;
          });
          
          // 「行」の<input>内の数値を連動させる
          $add_counter.val(length);

          // 「行」が１行なら削除ボタンを非表示に
          if ( $tbody.length > 1 ) {
            $('.br-plg-est-select-table .del-btn').show();
          }
          else {
            if ( $('.br-plg-est-select-table tbody tr').length > 1 ) {
              $('.br-plg-est-select-table .del-btn').show();
            }
            else {
              $('.br-plg-est-select-table .del-btn').hide();
            }
          }
        },

        /**
         * 数値から行の増減を制御
         * @param {Element} $trigger 「行」の数値変更<input>
         */
        controlLineByNumber: function ($trigger) {
          const count = $trigger.val(),
            $tbody = $('.br-plg-est-select-table').find('tbody'),
            $add_line_trigger = $trigger.parents('.line').find('.js--line_addition_trigger'),
            tbody_length = $tbody.length;

          if (count > tbody_length) {
            for (let i = tbody_length + 1; count >= i; i++) {
              $add_line_trigger.trigger('click');
            }
          } else {
            for (let i = tbody_length; count < i; i--) {
              $('.br-plg-est-select-table').find('tbody').last().remove();
            }
          }
        },

        /**
         * 「個数の単位」の活性制御
         * @param {Element} $this 「個数の有無」チェックボックス
         */
        countUnitDisplayControl: function ($this) {
          const $tr = $this.parents('tr'),
            $field = $tr.find('input[name^=count_unit]');

          if ($this.prop('checked')) {
            $field.prop('disabled', false);
          } else {
            $field.prop('disabled', true);
          }
        },

        /**
         * 全要素のnameを振りなおす
         */
        rewriteName: function () {
          const $tbody = $('.br-plg-est-select-table').find('tbody');
          let this_name;

          $tbody.each(function () {
            const index = $(this).index(),
              $tr = $(this).find('tr');

            if ($tr.length === 1) {
              $tr.find('input,textarea,select').each(function () {
                this_name = $(this).attr('name').substr($(this).attr('name').indexOf('['));
                this_name = $(this).attr('name').replace(this_name, '[' + index + ']');

                // 実行
                $(this).attr('name', this_name);
              });
            } else {
              $tr.each(function () {
                const tr_index = $(this).index();

                $(this).find('input,textarea,select').each(function () {
                  this_name = $(this).attr('name').substr($(this).attr('name').indexOf('['));
                  this_name = $(this).attr('name').replace(this_name, '[' + index + '_' + tr_index + ']');

                  // 実行
                  $(this).attr('name', this_name);
                });
              });
            }
          });
        },

        /**
         * 行追加
         * @param {Element} $trigger 追加ボタン
         */
        lineAddition: function ($trigger) {
          const $table = $('.br-plg-est-select-table'),
            $tbody = $table.find('tbody');

          let add_btn_flg,
            select_type,
            parent_flg,
            parent_count,
            $clone_item = $table.find('tbody:first-of-type tr').first().clone(),
            $insert_before_item;

          const $field_item = $clone_item.find('textarea[name^=item]'),
            $field_notes = $clone_item.find('textarea[name^=notes]'),
            $field_select_type = $clone_item.find('select[name^=select_type]');

          if ($trigger.hasClass('js--add_during')) { // 「＋」ボタン押下の場合
            const $tr = $trigger.parents('tr'),
              $this_tbody = $tr.parents('tbody');

            add_btn_flg = 'true';
            select_type = $tr.find('select[name^=select_type]').val();
            parent_flg = 0;
            parent_count = $this_tbody.index();
            $insert_before_item = $tr;
            $clone_item.addClass('child');

            $field_item.prop('disabled', true);
            $field_notes.prop('disabled', true);
            $field_select_type.prop('disabled', true);
          } else { // 「行追加」ボタン押下の場合
            add_btn_flg = 'false';
            select_type = 'text';
            parent_flg = 1;
            $insert_before_item = $tbody.last();
            $clone_item.removeClass('child');

            $field_item.prop('disabled', false);
            $field_notes.prop('disabled', false);
            $field_select_type.prop('disabled', false);
          }

          // 初期化
          $clone_item.attr('data-add_btn_flg', add_btn_flg).attr('data-select_type', select_type); // <tr>
          $clone_item.find('input').val(''); // <input>
          $clone_item.find('input[type="checkbox"]').val('1').prop('checked', false); // <input>
          $clone_item.find('textarea').val(''); // <textarea>
          $clone_item.find('select[name^=select_type]').val(select_type); // <select>
          $clone_item.find('input[name^=count_unit]').prop('disabled', true); // 「個数の単位」
          $clone_item.find('[data-type=count] .br-plg-est-num').text(''); // 「行」
          (!parent_flg) ? $clone_item.find('input[name^=parent]').val(parent_count): false; // 親<tbody>のindex番号追加
          $clone_item.find('input[name^=parent_flg]').val(parent_flg); // 先頭の要素か

          if (!$trigger.hasClass('js--add_during')) { // 「行追加」ボタン押下の場合、<tbody>で囲む
            $clone_item = $('<tbody>').prepend($clone_item);
          }

          // 実行
          $insert_before_item.after($clone_item);
        },

        /**
         * 順番入れ替え
         * @param $itemCount    ステップ番号
         * @param $before_after 'before' or 'after'
         */
        changeOrder: function ($itemCount, $before_after) {
          const $table = $('.br-plg-est-select-table'),
            $tbody = $table.find('tbody');
          console.log($before_after);

          if ( $before_after === 'before' ) {
            console.log($itemCount);
            $tbody.eq($itemCount - 1).insertBefore($tbody.eq($itemCount - 2));
          }
          if ( $before_after === 'after' ) {
            $tbody.eq($itemCount - 1).insertAfter($tbody.eq($itemCount));
          }


        },
      };


      // 初期カウント
      fn.countLine();

      // 「タイプ」のチェンジイベント
      $(document).on('change', 'select[name^="select_type"]', function () {
        // 実行
        fn.replacementAddBtnFlg($(this));
        fn.setType($(this));
      });

      // 「ゴミ箱」ボタン押下
      $(document).on('click', '.del-btn', function () {
        // 実行
        fn.removeLine($(this));
        fn.countLine();
        fn.rewriteName();
      });

      // 「個数の有無」のチェンジイベント
      $(document).on('change', 'input[name^=count_flg]', function () {
        // 実行
        fn.countUnitDisplayControl($(this));
      });

      // 「行追加 / ＋」ボタン押下
      $(document).on('click', '.js--line_addition_trigger', function () {
        // 実行
        fn.lineAddition($(this));
        fn.countLine();
        fn.rewriteName();
      });

      // 「行」の数値チェンジイベント
      $(document).on('change', '.js--line_addition_counter', function () {
        // 実行
        fn.controlLineByNumber($(this));
        fn.countLine();
        fn.rewriteName();
      });

      // 順番並べ替えイベント
      $(document).on('click', '.br-plg-est-order-before-btn', function () {
        // 実行
        fn.changeOrder(parseInt($(this).closest('tbody').find('tr').eq(0).find('.br-plg-est-num').text()), 'before');
        fn.countLine();
        fn.rewriteName();
      });
      // 順番並べ替えイベント
      $(document).on('click', '.br-plg-est-order-after-btn', function () {
        // 実行
        fn.changeOrder(parseInt($(this).closest('tbody').find('tr').eq(0).find('.br-plg-est-num').text()), 'after');
        fn.countLine();
        fn.rewriteName();
      });

      // 未更新時、ダイアログ表示
      // var dialog_flg = null;
      // $(document).on('change', function () {
      //   dialog_flg = true;
      // })
      // $('button[name="update_data"]').on('click', function () {
      //   // 「更新」ボタンをクリックしたときは除外
      //   dialog_flg = false;
      // })
      // $(window).on('beforeunload', function () {
      //   if (dialog_flg) {
      //     return 'このページを離れようとしています。';
      //   }
      // });

      // 編集ページ 値を入力したらプレビューをクリックできないようにする
      if ($('button[name=plg_est_preview]')[0]) {
        $(document).on('change', '.br-plg-est-select-table *', function () {
          $('button[name=plg_est_preview]').each(function () {
            $(this).attr('disabled', true);
          });
        });
      }
    }
  });


  $(function () {
    // 一覧ページ
    $('.est-table-list .shortcode_input').on('click', function () {
      var copyItem = $(this).val();
      navigator.clipboard.writeText(copyItem);
      if ($(this).parent().find('.copy_message').length == 0) {
        $('<p class="red copy_message">コピーしました！</p>').insertAfter($(this));
      }
    })
  });

})(jQuery);