
$(document).ready(function () {
  $.ajax({
 			url: 'index.php?route=extension/module/wk_pricealert/accontPageAjax',
 			type: 'post',
 			data: '',
 			dataType: 'json',
 			success: function(json) {
        if(json.wk_pricealert_status) {
          if (json.href ){
            button_html ='<h2 class=\'text-success\'>'+json.page_title+'</h2><ul class=\'list-unstyled\'><li><a href='+json.href+'>'+json.title+'</a></li></ul>';
            $("#content").append(button_html);
         }
        }
      }
 })

})
