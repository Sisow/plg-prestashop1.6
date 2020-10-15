{capture name=path}{l s='Afterpay' mod='sisowafterpay'}{/capture}

<h2>{l s='Order summary' mod='sisowafterpay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $afterpayerror != ''} 
	<div class="alert alert-danger" role="alert">{$afterpayerror}</div>
{/if}

<form name="sisowafterpay_form" id="sisowafterpay_form" class="std" action="{$base_dir_ssl}modules/sisow/payment.php?payment={$paymentcode}&paymentname={$paymentname}" method="post">
	<p>
		<img src="{$modules_dir}/sisowafterpay/LogoAfterpay.png"/>
	</p>
	<p>
		<div class="required form-group">
			<label class="required" for="sisowafterpay_gender">{l s='Salutation' mod='sisowafterpay'}</label><br/>
			<select id="sisowafterpay_gender" title="Aanhef" name="sisowafterpay_gender">
				<option value="">{l s='--Please Select--' mod='sisowafterpay'}</option>
				<option value="F" {if $gender == 'F'}selected{/if}>{l s='Female' mod='sisowafterpay'}</option>
				<option value="M" {if $gender == 'M'}selected{/if}>{l s='Male' mod='sisowafterpay'}</option>
			</select>
		</div>
	</p>
	<p>
		<div class="required form-group">
			<label class="required" for="sisowafterpay_phone">{l s='Telephone Number' mod='sisowafterpay'}</label><br/>
			<input id="sisowafterpay_phone" class="is_required validate form-control" maxlength="12" title="Telefoonnummer" value="{$phone}" name="sisowafterpay_phone"/>
		</div>
	</p>
	<p>
		<div class="required form-group">
				<label class="required" for="sisowafterpay_day">{l s='Date of birth' mod='sisowafterpay'}</label></br>
				<select id="sisowafterpay_day" name="sisowafterpay_day" title="sisowafterpay_day" class="year required-entry">
					<option value="">{l s='Day' mod='sisowafterpay'}</option>
					{foreach from=$days key=k item=v}
						<option value="{$k}" {if $dob.day == $k}selected{/if}>{$v}</option>
					{/foreach}
				<select>
				<select id="sisowafterpay_month" name="sisowafterpay_month" title="sisowafterpay_day" class="year required-entry">
					<option value="">{l s='Month' mod='sisowafterpay'}</option>
					{foreach from=$months key=k item=v}
						<option value="{$k}" {if $dob.month == $k}selected{/if}>{$v}</option>
					{/foreach}
				<select>
				<select id="sisowafterpay_year" name="sisowafterpay_year" title="sisowafterpay_year" class="year required-entry">
					<option value="">{l s='Year' mod='sisowafterpay'}</option>
					{foreach from=$years key=k item=v}
						<option value="{$k}" {if $dob.year == $k}selected{/if}>{$v}</option>
					{/foreach}
				<select>
		</div>
	</p>
	<div class="row">
		<div class="col-xs-12">
			<div class="checkbox ">
				<input id="terms" name="afterpay_terms" type="checkbox" required=""/>
				<label for="terms">
					Ik ga akkoord met de <a href="{$termsurl}" style="font-weight: bold; text-decoration: underline;" target="_blank">betalingsvoorwaarden</a> van Afterpay
				</label>
							
			</div>
		</div>
	</div>
	
<p class="cart_navigation clearfix" id="cart_navigation" style="padding-top: 25px;">
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button-exclusive btn btn-default"><i class="icon-chevron-left"></i> {l s='Other payment methods' mod='sisowafterpay'}</a>
	<input type="submit" value="{l s='I confirm my order' mod='sisowafterpay'}" id="sisowsubmit" class="btn btn-success standard-checkout button-medium pull-right" />
</p>
</form>

<script type="text/javascript">
	var mess_sisowafterpay_error = "{l s='Choose your bank!' mod='sisowafterpay' js=1}";
	{literal}
		$(document).ready(function(){

			$('#sisowsubmit').click(function()
				{
				if ($('#issuerid').val() == '')
				{
					alert(mess_sisowafterpay_error);
				}
				else
				{
					$('#sisowafterpay_form').submit();
				}
				return false;
			});
		});
	{/literal}
</script>