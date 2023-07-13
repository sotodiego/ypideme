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

$(document).ready(function(){
  var html ='';
  var html_tab ='';
  var user_token = $.getURLVaribale('user_token');
  var p_id  = $.getURLVaribale('product_id');
  var product_id = 0;

  if (typeof p_id === "undefined") {
     product_id = 0;
  } else {
     product_id = p_id;
  }
  $.ajax({
    url : 'index.php?route=customerpartner/pricealert/renderAlertHTML&product_id='+product_id+'&user_token='+user_token,
    type : "GET",
    dataType : 'json',
    success :function(json) {
      if(json['wk_pricealert_status']) {
        html_tab ='<li><a href="#tab-alertproduct" data-toggle="tab">'+json['tab_palert']+'</a></li>';
        html = '<div id="tab-alertproduct" class="tab-pane">';

        html += '   <div class="form-group">';
        html += '     <label class="col-sm-2 control-label" for="input-pricealert">'+json['text_palert']+'</label>';
        html += '     <div class="col-sm-10">';
        html += '       <select name="pricealert" id="input-prigetListcealert" class="form-control">';
        if(json['is_alert_product'] == 0) {
          html += '         <option value="1" >'+json['text_enable']+'</option>';
          html += '         <option value="0" selected="selected">'+json['text_disable']+'</option>';
        } else {
          html += '         <option value="1" selected="selected">'+json['text_enable']+'</option>';
          html += '         <option value="0">'+json['text_disable']+'</option>';
        }
        html += '       </select>';
        html += '     </div>';
        html += '  </div>';

        html += '</div>';
        $('a[href = "#tab-design"]').parent().parent().append(html_tab);
        $('#tab-design').parent().append(html);
      }
    }
  });

});
