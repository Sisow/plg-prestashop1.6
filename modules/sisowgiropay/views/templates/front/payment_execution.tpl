
{capture name=path}{l s='Giropay payment' mod='sisowgiropay'}{/capture}

<h2>{l s='Order summary' mod='sisowgiropay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>{l s='Giropay payment' mod='sisowgiropay'}</h3>
<form name="sisowgiropay_form" id="sisowgiropay_form" action="{$base_dir_ssl}modules/sisow/payment.php?payment={$paymentcode}&paymentname={$paymentname}" method="post">
<p>
	<img src="{$base_dir_ssl}modules/sisowgiropay/giropay.png" width="64px" alt="Giropay" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='You have chosen to pay with Giropay.' mod='sisowgiropay'}
	</br>
	Bankleitzahl:
	<input id="giropay_widget" autocomplete="off" name="{$paymentcode}_bic" />
</p>

<p class="cart_navigation clearfix" id="cart_navigation">
	<a class="button-exclusive btn btn-default" title="Vorige" href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}">
		<i class="icon-chevron-left"></i>
		{l s='Other payment methods' mod='sisowgiropay'}
	</a>

	<button class="button btn btn-default standard-checkout button-medium" id="sisowsubmit" name="sisowsubmit" type="submit" style="">
		<span>
			{l s='I confirm my order' mod='sisowgiropay'}
			<i class="icon-chevron-right right"></i>
		</span>
	</button>
</p>
</form>

<script type="text/javascript">
	var mess_sisow_error = "{l s='Choose your bank!' mod='sisowgiropay' js=1}";
	{literal}
		$(document).ready(function(){

			$('#sisowsubmit').click(function()
				{
				if ($('#issuerid').val() == '')
				{
					alert(mess_sisow_error);
				}
				else
				{
					$('#sisowgiropay_form').submit();
				}
				return false;
			});
		});
	{/literal}
</script>

<script>
	{literal}
	( function($) {
			// we can now rely on $ within the safety of our "bodyguard" function
			$(document).ready(function() {
		$('#giropay_widget').giropay_widget({'return': 'bic','kind': 1});
	});
		} ) ( jQuery );
	{/literal}
</script>