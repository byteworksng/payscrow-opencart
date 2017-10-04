<form action="<?php echo $action; ?>" method="POST" class="form-horizontal" id="payscrow_form_redirect">
  <fieldset id="payment">
    <input type="hidden" name="txntype" value="<?php echo $txntype; ?>" />
    <input type="hidden" name="timezone" value="GMT" />
    <input type="hidden" name="txndatetime" value="<?php echo $timestamp; ?>" />
    <input type="hidden" name="hash" value="<?php echo $hash; ?>" />
    <input type="hidden" name="AccessKey" value="<?php echo $merchant_id; ?>" />
    <?php $i=0; foreach($product_detail as $detail): ?>
    <input type="hidden" name="Items[<?php echo $i?>].Name" id="Items[<?php echo $i?>]_Name" value="<?php echo $detail['name']. ' '. $detail['model']; ?>" />
    <input type="hidden" name="Items[<?php echo $i?>].Price" id="Items[<?php echo $i?>]_Price" value="<?php echo $detail['price']; ?>" />
    <input type="hidden" name="Items[<?php echo $i?>].Quantity" id="Items[<?php echo $i?>]_Quantity" value="<?php echo $detail['quantity']; ?>" />
    <input type="hidden" name="Items[<?php echo $i?>].Description" id="Items[<?php echo $i?>]_Description" value="<?php echo $detail['description']; ?>" />
    <input type="hidden" name="Items[<?php echo $i?>].Deliverable" id="Items[<?php echo $i?>]_Deliverable" value="true" checked="checked" />
    <?php $i++; endforeach; ?>
    <input type="hidden" name="Amount" value="<?php echo $amount; ?>" />
    <input type="hidden" name="Currency" value="<?php echo $currency; ?>" />
    <input type="hidden" name="oid" value="<?php echo $order_id; ?>" />
    <input type="hidden" name="mobileMode" value="<?php echo $mobile; ?>" />
    <input type="hidden" name="responseSuccessURL" value="<?php echo $url_success; ?>" />
    <input type="hidden" name="responseFailURL" value="<?php echo $url_fail; ?>" />
    <input type="hidden" name="transactionNotificationURL" value="<?php echo $url_notify; ?>" />
    <input type="hidden" name="sname" value="<?php echo $sname; ?>" />
    <input type="hidden" name="saddr1" value="<?php echo $saddr1; ?>" />
    <input type="hidden" name="saddr2" value="<?php echo $saddr2; ?>" />
    <input type="hidden" name="scity" value="<?php echo $scity; ?>" />
    <input type="hidden" name="sstate" value="<?php echo $sstate; ?>" />
    <input type="hidden" name="scountry" value="<?php echo $scountry; ?>" />
    <input type="hidden" name="szip" value="<?php echo $szip; ?>" />
    <input type="hidden" name="bcompany" value="<?php echo $bcompany; ?>" />
    <input type="hidden" name="bname" value="<?php echo $bname; ?>" />
    <input type="hidden" name="baddr1" value="<?php echo $baddr1; ?>" />
    <input type="hidden" name="baddr2" value="<?php echo $baddr2; ?>" />
    <input type="hidden" name="bcity" value="<?php echo $bcity; ?>" />
    <input type="hidden" name="bstate" value="<?php echo $bstate; ?>" />
    <input type="hidden" name="bcountry" value="<?php echo $bcountry; ?>" />
    <input type="hidden" name="bzip" value="<?php echo $bzip; ?>" />
	<input type="hidden" name="email" value="<?php echo $email; ?>" />
    <input type="hidden" name="invoicenumber" value="<?php echo $version; ?>" />
    <input type="hidden" name="DeliveryDurationInDays" value="<?php echo $delivery_duration; ?>" />
  </fieldset>
</form>

<div class="buttons">
  <div class="pull-right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" />
  </div>
</div>

<script type="text/javascript"><!--
$('#button-confirm').bind('click', function() {
  $('#payscrow_form_redirect').submit();
});
//--></script>