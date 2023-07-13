<div class="modal fade" id="myModal-notification" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo $text_close; ?></span></button>
				<h3 class="modal-title"><?php echo 'Hi ';if(isset($sellerProfile['firstname'])){echo $sellerProfile['firstname'];}if(isset($sellerProfile['lastname'])){echo ' '.$sellerProfile['lastname'];} ?></h3>
			</div>

			<div class="modal-body">
			  <?php if(isset($seller_notifications) && $seller_notifications){ ?>
			    <div class="form-group">
			      <ul style="display:inline-flex;margin-left:-8%;">
			        <li class="dropdown-header" style="font-size:15px;"><b><?php echo $text_order; ?></b></li>
			        <li style="display: block; overflow: auto;margin-top:5px;"><a href="<?php echo $view_all; ?>"><span id="processing_status_total" class="label label-warning" style="margin-right: 5px;"><?php echo $processing_status_total; ?></span><?php echo $text_processing_status; ?></a></li>
			        <li style="display: block; overflow: auto;margin-left:40px;margin-right:40px;margin-top:5px;"><a href="<?php echo $view_all; ?>"><span id="complete_status_total" class="label label-success" style="margin-right: 5px;"><?php echo $complete_status_total; ?></span><?php echo $text_complete_status; ?></a></li>
			        <li style="display: block; overflow: auto;margin-top:5px;"><a href="<?php echo $view_all; ?>"><span id="return_total" class="label label-danger" style="margin-right: 5px;"><?php echo $return_total; ?></span><?php echo $text_return; ?></a></li>
			      </ul>
			      <?php if(isset($seller_notifications) && $seller_notifications){ ?>
			        <ul id="seller_notifications">
			          <?php foreach($seller_notifications AS $seller_notification){ ?>
			            <li><?php echo $seller_notification; ?></li>
			          <?php } ?>
			          <li style="display: block; overflow: auto;"><a href="<?php echo $view_all; ?>"><?php echo $text_view_all; ?></a></li>
			        </ul>
			      <?php } ?>
			    </div>
			    <hr/>
			  <?php } ?>
			  <?php if(isset($seller_product_reviews) && $seller_product_reviews){ ?>
			    <div class="form-group">
			      <ul style="display:inline-flex;margin-left:-8%;">
			        <li class="dropdown-header" style="font-size:15px;"><b><?php echo $text_product; ?></b></li>
			        <li style="display: block; overflow: auto;margin-top:5px;"><a href="<?php echo $view_all.'&tab=product'; ?>"><span id="product_stock_total" class="label label-warning" style="margin-right: 5px;"><?php echo $product_stock_total; ?></span><?php echo $text_stock; ?></a></li>
			        <li style="display: block; overflow: auto;margin-left:40px;margin-right:40px;margin-top:5px;"><a href="<?php echo $view_all.'&tab=product'; ?>"><span id="review_total" class="label label-success" style="margin-right: 5px;"><?php echo $review_total; ?></span><?php echo $text_entry_review; ?></a></li>
			        <li style="display: block; overflow: auto;margin-top:5px;"><a href="<?php echo $view_all.'&tab=product'; ?>"><span id="approval_total" class="label label-danger" style="margin-right: 5px;"><?php echo $approval_total; ?></span><?php echo $text_approval; ?></a></li>
			      </ul>
			      <?php if(isset($seller_product_reviews) && $seller_product_reviews){ ?>
			        <ul id="seller_product_reviews">
			          <?php foreach($seller_product_reviews AS $seller_product_review){ ?>
			            <li><?php echo $seller_product_review; ?></li>
			          <?php } ?>
			          <li style="display: block; overflow: auto;"><a href="<?php echo $view_all.'&tab=product'; ?>"><?php echo $text_view_all; ?></a></li>
			        </ul>
			      <?php } ?>
			    </div>
			    <hr/>
			  <?php } ?>
			  <?php if(isset($seller_reviews) && $seller_reviews){ ?>
			    <div class="form-group">
			      <ul style="display:inline-flex;margin-left:-8%;">
			        <li class="dropdown-header" style="font-size:15px;"><b><?php echo $text_entry_seller; ?></b></li>
			        <li style="display: block; overflow: auto;margin-right:40px;margin-top:5px;"><a href="<?php echo $view_all.'&tab=seller'; ?>"><span id="seller_review_total" class="label label-success" style="margin-right: 5px;"><?php echo $seller_review_total; ?></span><?php echo $text_entry_review; ?></a></li>
			      </ul>
			      <?php if(isset($seller_reviews) && $seller_reviews){ ?>
			        <ul id="seller_reviews">
			          <?php foreach($seller_reviews AS $seller_review){ ?>
			            <li><?php echo $seller_review; ?></li>
			          <?php } ?>
			          <li style="display: block; overflow: auto;"><a href="<?php echo $view_all.'&tab=seller'; ?>"><?php echo $text_view_all; ?></a></li>
			        </ul>
			      <?php } ?>
			    </div>
			  <?php } ?>
			  <?php if((!isset($seller_notifications) && !isset($seller_product_reviews) && !isset($seller_reviews)) || (!$seller_notifications && !$seller_product_reviews && !$seller_reviews)){ ?>
			    <center><h4><?php echo $text_no_notification; ?></h4></center>
			  <?php } ?>
			</div>

			<div class="modal-footer">
				<a href="<?php echo $view_all; ?>" class="btn btn-primary button" style="color:white;"><?php echo $text_view_all_notificatins; ?></a>
				<button type="button" class="btn btn-default button" data-dismiss="modal"><?php echo $text_close; ?></button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
$(document).ready(function () {
function wk_notify(){
	$.ajax({
	  url: 'index.php?route=account/customerpartner/notification/notifications&json_notification=true',
	  type: 'get',
	  methodType: 'json',
	  success: function(data) {

			if(data['notification_total']){
			  if (data['separate_view']) {
			    $("#notification").html('<span class="label label-danger pull-left">' + data['notification_total'] + '</span> <img src="admin/view/image/notify.png" title="<?php echo $text_notifications; ?>">');
			  } else {
			    $("#notification").html('<?php echo $text_notifications; ?>'+' <span class="badge">'+data['notification_total']+'<span>');
			  }
			} else {
			  if (data['separate_view']) {
			    $("#notification").html('<img src="admin/view/image/notify.png" title="<?php echo $text_notifications; ?>">');
			  } else {
			    $("#notification").text('<?php echo $text_notifications; ?>');
			  }
			}

			$( "span" ).each( function( index, element ){
				var span_id = $( this ).attr('id');
				if(span_id && data[span_id]){
					$("#"+span_id).text(data[span_id]);
				}
			});

			$( "ul" ).each( function( index, element ){
				var ul_id = $( this ).attr('id');
				if(ul_id && data[ul_id]){
					var html = '';
					for (var i = 0; i < data[ul_id].length; i++) {
						html += '<li>' + data[ul_id][i] + '</li>';
					}
					if(ul_id == 'seller_product_reviews'){
						html += '<li style="display: block; overflow: auto;"><a href="<?php echo $view_all; ?>&tab=product"><?php echo $text_view_all; ?></a></li>';
					} else if (ul_id == 'seller_reviews') {
						html += '<li style="display: block; overflow: auto;"><a href="<?php echo $view_all; ?>&tab=seller"><?php echo $text_view_all; ?></a></li>';
					} else{
						html += '<li style="display: block; overflow: auto;"><a href="<?php echo $view_all; ?>"><?php echo $text_view_all; ?></a></li>';
					}
					$("#"+ul_id).html(html);
				}
			});
	  }
	});
}
setInterval(wk_notify, 30000);
wk_notify();
});
</script>
