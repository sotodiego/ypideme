$.extend({
  getURLVaribales: function(){
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++)
        {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    },
    getURLVaribale: function(name){
        return $.getURLVaribales()[name];
    }
});

$(document).ready(function () {
  var ajax_fucn  = '',
      product_id = 0,
      route      = $.getURLVaribale('route');
  if(typeof route === 'undefined' || route != 'product/product') {
    ajax_fucn = 'getAllAlertProducts'
  }
  // this will be used to add the alert on the all pages
  if (!product_id) {
   //  $.ajax({
   // 			url: 'index.php?route=extension/module/wk_pricealert/'+ajax_fucn,
   // 			type: 'post',
   // 			data: '',
   // 			dataType: 'json',
   // 			success: function(json) {
   //        if(json.wk_pricealert_status) {
   //         // button_html = '';
   //         // button_html = '<button type="button" data-id='+ product_id +' data-toggle="tooltip" class="btn btn-success" title=""  data-original-title="Add Alert" ><i class="fa fa-bell"></i></button>'
   //         // html = '';
   //         if (product_id){
   //            $('#content .row .col-sm-4 .btn-group').append(button_html);
   //         }
   //        }
   //      }
   // })
 }
})
