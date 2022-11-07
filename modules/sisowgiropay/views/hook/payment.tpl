<div class="sisow_error">
	<p><b>{$giropayerror}</b></p>
</div>

{if $smarty.const._PS_VERSION_ >= 1.6}

<div class="row">
	<div class="col-xs-12 col-md-6">
        <p class="payment_module sisow">
			<a href="javascript:void(0)" onclick="$('#sisow_{$paymentcode}_form').submit();" id="sisow{$paymentcode}_process_payment" title="{$paymenttext}">
				<img src="{$base_dir_ssl}modules/sisowgiropay/giropay.png" width="64" alt="Giropay" /> {$paymenttext}				
			</a>
		</p>
    </div>
</div>

<style>
	p.payment_module.sisow a 
	{ldelim}
		padding-left:17px;
	{rdelim}
</style>
{else}
<p class="payment_module">
	<a href="javascript:void(0)" onclick="$('#sisow_{$paymentcode}_form').submit();" id="sisow{$paymentcode}_process_payment" title="{l s='Pay with PayPal' mod='paypal'}">
		<img src="{$base_dir_ssl}modules/sisowgiropay/giropay.png" width="64" alt="Giropay" /> {$paymenttext}				
	</a>
</p>

{/if}
<form id="sisow_{$paymentcode}_form" action="{$link->getModuleLink('sisowgiropay', 'payment', array(), true)|escape:'html'}" data-ajax="false" title="{$paymenttext}" method="post">
	<input type="hidden" name="{$paymentcode}" value="true"/>
</form>