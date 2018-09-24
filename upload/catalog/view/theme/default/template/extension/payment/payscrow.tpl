



<form action="<?php echo $action; ?>" method="POST" class="form-horizontal" id="payscrow_form_redirect">

  <fieldset id="payment">
    <input type="hidden" name="Hash" value="<?php echo $hash; ?>" />
    <input type="hidden" name="AccessKey" value="<?php echo $merchant_id; ?>" />
    <?php $i=0; foreach($product_detail as $detail): ?>

    <input type="hidden" name="Items[<?php echo $i?>].Name" id="Items[<?php echo $i?>]_Name" value="<?php echo $detail['name']. ' '. $detail['model']; ?>" />
    <input type="hidden" name="Items[<?php echo $i?>].Price" id="Items[<?php echo $i?>]_Price" value="<?php echo $detail['price']; ?>" />
    <input type="hidden" name="Items[<?php echo $i?>].Quantity" id="Items[<?php echo $i?>]_Quantity" value="<?php echo $detail['quantity']; ?>" />
    <input type="hidden" name="Items[<?php echo $i?>].Description" id="Items[<?php echo $i?>]_Description" value="<?php echo strip_tags(html_entity_decode($detail['description'])); ?>" />
    <input type="hidden" name="Items[<?php echo $i?>].Deliverable" id="Items[<?php echo $i?>]_Deliverable" value="<?php echo !$isDownloadable($detail['product_id']) ?>" />
    <input type="hidden" name="Items[<?php echo $i?>].TaxAmount" id="Items[<?php echo $i?>]_TaxAmount" value="<?php echo $detail['tax']; ?>" />
    <?php $i++; endforeach; ?>
    <input type="hidden" name="GrandTotal" value="<?php echo $amount; ?>" />
    <input type="hidden" name="ShippingAmount" value="<?php echo $shipping; ?>" />
    <input type="hidden" name="Currency" value="<?php echo $currency; ?>" />
    <input type="hidden" name="ResponseURL" value="<?php echo $url_notify; ?>" />

    <input type="hidden" name="Ref" value="<?php echo $order_id; ?>" />
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