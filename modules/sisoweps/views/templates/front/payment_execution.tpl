
{capture name=path}{l s='EPS payment' mod='sisoweps'}{/capture}

<h2>{l s='Order summary' mod='sisoweps'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>{l s='EPS payment' mod='sisoweps'}</h3>
<form name="sisoweps_form" id="sisoweps_form" action="{$base_dir_ssl}modules/sisow/payment.php?payment={$paymentcode}&paymentname={$paymentname}" method="post">
<p>
	<img src="{$base_dir_ssl}modules/sisoweps/eps.gif" width="64px" alt="iDEAL" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='You have chosen to pay with EPS.' mod='sisoweps'}
	</br>
	Bankleitzahl:
	<input id="eps_widget" autocomplete="off" name="{$paymentcode}_bic" />
</p>

<p class="cart_navigation clearfix" id="cart_navigation">
	<a class="button-exclusive btn btn-default" title="Vorige" href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}">
		<i class="icon-chevron-left"></i>
		{l s='Other payment methods' mod='sisoweps'}
	</a>

	<button class="button btn btn-default standard-checkout button-medium" id="sisowsubmit" name="sisowsubmit" type="submit" style="">
		<span>
			{l s='I confirm my order' mod='sisoweps'}
			<i class="icon-chevron-right right"></i>
		</span>
	</button>
</p>
</form>

<script type="text/javascript">
	var mess_sisow_error = "{l s='Choose your bank!' mod='sisoweps' js=1}";
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
					$('#sisoweps_form').submit();
				}
				return false;
			});
		});
	{/literal}
</script>

<script>
	{literal}
	( function($) {
			$(document).ready(function() {
				$('#eps_widget').eps_widget({'return': 'bic'});
			});
		} ) ( jQuery );
	{/literal}
</script>