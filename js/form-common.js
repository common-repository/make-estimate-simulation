(function ($) {
  'use strict';

  $(function () {
    if ($('.est-form')[0]) {

      $('.est-table-form tr:last-of-type td').addClass('last-td');

      function number_total() {
        var total = null;
        $('.est-table-form td').each(function () {
          if (
            !$(this).hasClass('input-items') &&
            !$(this).hasClass('last-td') &&
            $(this).find('input[name="price"]')[0] &&
            $(this).find('input[type="number"]')[0]
          ) {
            var price = $(this).find('input[name="price"]').val();
            var num = $(this).find('input[type="number"]').val();
            total += price * num;
          }
        })
        return total;
      }

      function calc_count_input($count_input) {
        var total = null;
        var num = $count_input.find('input[type="number"]').val();
        var price = $count_input.find('input[name="price"]').val();
        total = num * price;
        return total;
      }

      function checkbox_radio_total() {
        var total = null;
        $('.est-table-form .input-items').each(function () {
          $(this).find('.item').each(function () {
            if ($(this).find('input').prop('checked')) {
              var price = null;
              if ($(this).find('.count-input').length > 0) {
                var price = calc_count_input($(this));
                total += price;
              } else {
                var price = $(this).find('input[name="price"]').val();
                total += Number(price);
              }
            }
          })
        })
        return total;
      }

      // ページ読み込み時に実行
      $(function () {
        var input_total = number_total();
        var check_radio_total = checkbox_radio_total();
        var all_total = input_total + check_radio_total;
        if ( !all_total ) {
          all_total = 0;
        }
        $('.est-table-form .num').text(Number(all_total).toLocaleString());
      })

      // フォームの値変更時に実行
      $(document).change(function () {
        var input_total = number_total();
        var check_radio_total = checkbox_radio_total();
        var all_total = input_total + check_radio_total;
        if ( !all_total ) {
          all_total = 0;
        }
        $('.est-table-form .num').text(Number(all_total).toLocaleString());
      })

      $('.est-table-form td').each(function () {
        if ($(this).find('.checkbox_input').length >= 1 || $(this).find('.radio_input').length >= 1) {
          $(this).addClass('input-items');
        }

        if ($(this).find('.count-input').length >= 1) {
          if ($(this).hasClass('input-items')) {
            $(this).find('.count-input').each(function () {
            })
          }
        }
      })
    }
  })

})(jQuery);