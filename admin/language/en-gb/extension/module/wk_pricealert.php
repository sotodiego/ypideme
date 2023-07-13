<?php

// Heading
$_['heading_title']      = 'Price Alert';
$_['heading_title_mp']   = 'MarkerPlace Price Alert';
$_['text_extension']     = 'Extension';
$_['text_edit']				   = 'Edit Price Alert Module';
$_['text_price_low']		 = 'The price is low';
$_['text_price_high']		 = 'The price is high';
$_['text_price_change']	 = 'There is a change in Price';
$_['text_registered']		 = 'Registered <sup style="color:red">*</sup>';
$_['text_unregistered']	 = 'Guest User <sup style="color:red">*</sup>';
$_['text_module']        = 'Module';
$_['text_guest']			   = 'Guest Users';
$_['text_users']			   = 'Customer Type';
$_['text_action']		     = 'Action';
$_['text_success']			= 'Success: You have modified Price Alert module!';
// Entry
$_['entry_status']     				= 'Status';
$_['entry_coupon_validity']			= 'Coupon Validity';
$_['entry_coupon_name']				= 'Coupon Name';
$_['help_coupon_validity']		= 'Select the number of days the coupon will be valid after being received';
$_['help_coupon_name']			= 'Insert the coupon name by which you want to create coupon';
// Help
$_['help_status']     				= 'Set enable Status to use the module functionality Disable otherwise';
// tab
$_['tab_setting']			= 'Settings';
$_['tab_quote']				= 'Manage Mails';
$_['tab_coupon']			= 'Coupon';
$_['tab_performance']		= 'Price Alert Restrictions';
$_['tab_price_accepted']	= 'Price Accepted Mail Format:';
$_['tab_price_rejected']	= 'Price Rejected Mail Format:';

$_['text_enable']	   = 'Enabled';
$_['text_disable']	= 'Disabled';

$_['entry_alert_customer']			= 'Alert Customer When:';
$_['enrty_price_change']			  = 'Alert Mail for Price Changes';
$_['entry_admin_notification']	= 'Admin Notification By Email';
$_['entry_allow_seller']	      = 'Allow Seller';
$_['help_allow_seller']	        = 'Allow Seller to manage Alert set on there products ( <b>this will be only Applicable with Marketplace module  </b>)';
$_['entry_allow_guest']	        = 'Allow Guest Customer';
$_['help_allow_guest']	        = 'Allow Guest Customer to use Price Alert';
$_['entry_notify_admin']	      = 'Notify Admin';
$_['help_notify_admin']	        = 'Allow to get notify Admin for sellers product alert( <b>this will be only Applicable with Marketplace module  </b>)';
$_['entry_limit_seller']	      = 'Seller Limit';
$_['help_limit_seller']	        = 'Set limit to add alert product for the seller based on Monthly ( <b>this will be only Applicable with Marketplace module  </b>)';
$_['entry_mp_feature']	        = 'Marketplace Features';
$_['help_mp_feature']	          = '  <b>{ The below listed fields will be only Applicable and used with Marketplace Module }</b>';


$_['entry_email_subject']			  = 'Email Subject';
$_['entry_email_text']				  = 'Email Text';
$_['entry_custom_css']				  = 'Custom CSS';

$_['entry_pricealert_applicable']	= 'Price Alert Vendor Applicable';
$_['entry_notification_times']		= 'Notification Times:';
$_['entry_sellernoti']		  = 'Seller notification by Email:';
$_['entry_sellernoti_applicable']	= 'Vendor applicable for notifications';
$_['entry_email_notification']		= 'E-mail notifications to the VENDOR';

// Help
$_['help_alert_customer']		= 'Choose when the customer will be notified';
$_['help_admin_notification']	= 'You will receive an email when someone wants to be notified for a price change';
$_['help_popup_width']			= 'In pixels';
$_['help_popup_title']			= 'Title to be shown on pop-up';
$_['help_design_popup']			= 'Use these codes:<br>{name_field} - Name<br>{email_field} - Email<br>{submit_button} - Submit Button<br>{price_sug} - Price Suggested';
$_['help_email_subject']		= 'Subject of the email send to the Customer';
$_['help_email_subject_vendor']	= 'Subject of the email send to the Vendor';
$_['help_email_text']			= 'Use these codes(Format of the email):<br>{customer_name} - Customer Name<br>{product_name} - Product Name<br>{product_image} - Product Image<br>{coupon_validity_date} - Coupon validate date<br>{coupon_code} - Coupon code<br>{product_link} - Product Link<br>{times_notification} - Notification Times';
$_['help_custom_css']			= 'Custom CSS';

$_['help_sellernoti_applicable']= 'This configuration let the admin restrict notification to the vendors.';
$_['help_pricealert_applicable']= 'This configuration let the admin restrict price alert feature for the vendors.';
$_['help_notification_times']	= 'Number of times notification can be send to vendor';
$_['help_email_notification']	= 'Use these codes(Format of the email):<br>{customer_name} - Customer Name<br>{seller_name} - Vendor Name<br>{product_name} - Product Name<br>{product_image} - Product Image<br>{product_link} - Product Link';

$_['text_request_by_customer']	= 'Maximum number of products Allowed For Requests by Customer`s In Monthly Basis';
$_['info_mail_add']			= ' You can save Mail messages for default conditions for Price Alert. Use these keyword in the Templates for these conditions -
<br/><br/> Customer request to Price-Alert on any product (Mail to Admin For the alert submitted by customer).
<br/> Customer Price-Alert Approval (Mail to Customer when Alert will be Accepted).
<br/> Customer Price-Alert Rejected (Mail to Customer when Alert will be Rejected).
<br/><br/> So You can add Mail Messages with Subject hereby using these keywords.
<br/>
.';

$_['error_warning']      	 	 = 'Warning: There are some error in the form';
$_['error_range']      	 	 = 'The Value must be in the range of 0 to 999';
$_['error_crange']      	 	 = 'The Value must be in the range of 0 to 100';
$_['error_coupan']             = 'Coupon Name must be greater than 1 and less than 64 characters!';
$_['tab_info']      	 	 = 'Info';
$_['tab_general']         	 = 'Add Message';
$_['entry_for']         	 = 'For';
$_['entry_code']      		 = 'Keyword';
$_['text_alert_info']	= '<p> <b>1 :- </b>You can also use this module with the our <a target="_blank" href="https://store.webkul.com/opencart-multi-vendor-marketplace.html">Marketplace Module </a>if you have installed the Marketplace module for this instance.</p>
<p><b>2 :- </b>Additional to use this module with Marketplace you have to configure fields mentioned under <b> Marketplace Features</b> section in the setting Tab .</p>
<p> <b>3 :- </b>To set the Custom CSS fields for the Mail templates you must have some basic knowledge of the CSS Properties</p>';
$_['error_permission'] = 'Warning: You do not have permission to modify Price Alert module!';
?>
