
let ajax_url           = $('#ajax_url').val();

let filter_name        = '',
filter_term       = '',
filter_count       = '',
order              = 'ASC',
sort               = 'pd.name',
page               = 1,
start              = 0,
end                = 10,
product_listed     = 0,
product_prev       = 0,
all                = false,
in_process         = false,
checkEvent         = 1;

$(document).ready(function(){
  filter_name        = '',
  filter_term       = '',
  filter_count       = '',
  order              = 'ASC',
  sort               = 'pd.name',
  page               = 1,
  start              = 0,
  end                = 10,
  product_listed     = 0,
  product_prev       = 0,
  all                = false,
  in_process         = false,
  checkEvent         = 1;
  ajax_url           = $('#ajax_url').val();
  _loadData();
});

function sorts(val) {
  if(order == 'DESC'){
    order = 'ASC';
  }else{
    order = 'DESC';
  }
  sort = val;
   $('#elem_body').html('<tr></tr>');
   $('#topsearchFoot').empty();
  _loadData();
}

function _clearFilter() {
  $('input[name=\'filter_name\']').val('');
  $('input[name=\'filter_term\']').val('');
  $('input[name=\'filter_count\']').val('');
  $('input[name=\'filter_start\']').val('');
  $('input[name=\'filter_limit\']').val('');

  filter_name        =  '',
  filter_term        =  '',
  filter_count       =  '',
  order              =  'ASC',
  sort               =  'pd.name',
  page               =  1,
  start              =  0,
  end                =  10;
  $('#elem_body').html('<tr></tr>');
  $('#topsearchFoot').empty();
  _loadData();
}

function _filter() {
  filter_name        =  $('input[name=\'filter_name\']').val(),
  filter_term       =  $('input[name=\'filter_term\']').val(),
  filter_count       =  $('input[name=\'filter_count\']').val(),
  order              = 'ASC',
  sort               = 'pd.name',
  page               = 1,
  start              =  $('input[name=\'filter_start\']').val(),
  end                = $('input[name=\'filter_limit\']').val();
  $('#elem_body').html('<tr></tr>');
  $('#topsearchFoot').empty();
  _loadData();
}

function _loadData() {
  var elem = {
    filter_name        : filter_name,
    filter_term        : filter_term,
    filter_count       : filter_count,
    start              : start,
    limit              : end,
    order              : order,
    sort               : sort,
    page               : page,
  };

  $.ajax({
    url      : 'index.php?route='+ajax_url,
    data     :  elem,
    type     : 'post',
    dataType : 'json',
    beforeSend: function () {
      $('#topsearchFoot').append(' <i class="fa fa-spin fa-spinner"></i>');
      in_process = true;
    },
    success: function(json) {
      product_listed = 0;
      in_process = false;
      if (json['success']) {
        var htm = '';
        var topsearch = json['topsearch'];
        for (var i = 0; i < topsearch.length; i++) {
          product_listed++;
          htm += '<tr cashier-id="' + topsearch[i]['id'] + '">';

          htm += '  <td class="text-center"><a href="' + topsearch[i]['href'] + '  ">' + topsearch[i]['name'] + '</a></td>';

          htm += '  <td class="text-center">' + topsearch[i]['count'] + '  '+json['text_time']+'        </td>';

          htm += '  <td class="text-center tags">';
            $.each(topsearch[i]['terms'], function( index, value ) {
                  htm += '<a class="tag">'+value+'</a>';
            });
          htm += '   </td>';

          if(typeof json['_adminCheck'] !== 'undefined' && json['_adminCheck']){
            htm += '  <td class="text-center">' + topsearch[i]['seller'] + '</td>';
          }

          htm += '  <td class="text-center">';

          if(topsearch[i]['offer']) {
            htm += '<a class="btn" href ="' + topsearch[i]['offer'] + '"  data-toggle="tooltip" title="'+json['help_offer']+'" style="background-color:rgb(90,90,90);color: white;border: 0px none; border-radius: 0px;margin-right:10px">  '+json['entry_offer']+' ';
            htm += ' <i class="fa fa-gift" aria-hidden="true"></i></a>';
          }


          htm += '<a class="btn" href ="' + topsearch[i]['action'] + '"  data-toggle="tooltip" title="'+json['button_edit']+'" style="background-color:rgb(10,10,10);color: white;border: 0px none; border-radius: 0px;">  '+json['button_edit']+' ';
          htm += ' <i class="fa fa-pencil"></i></a></td>';
          htm += '</tr>';
          if (product_listed == json['product_total']) {
            all = true;
          }
        }

        $('#elem_body').append(htm);
        $('#topsearchFoot').text('Showing ' + product_listed + ' of ' + json['total']);
      } else {
        $('#elem_body').append('<tr></tr>');
        $('#topsearchFoot').text('There are no more top search items available');
      }
    },
    error: function () {
      in_process = false;
      //location = 'index.php?route=accoun/customerpartner/topsearch';
    }
  });
}
