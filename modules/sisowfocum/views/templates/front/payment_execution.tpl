{capture name=path}{l s='Achteraf Betalen' mod='sisowfocum'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='sisowfocum'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>Achteraf betalen</h3>
<form name="sisowfocum_form" id="sisowfocum_form" action="{$base_dir_ssl}modules/sisow/payment.php?payment={$paymentcode}&paymentname={$paymentname}" method="post">
<p>
			<img src="{$base_dir_ssl}modules/sisowgiropay/focum.png" width="125px"/>
			<br/>
			<label class="required" for="sisowfocum_gender">{l s='Salutation' mod='sisowfocum'}</label>
			<div class="input-box">
				<select id="sisowfocum_gender" class="input-text required-entry" title="Aanhef" name="sisowfocum_gender">
					<option value="">{l s='--Please Select--' mod='sisowfocum'}</option>
					<option value="F" {if $gender == 'F'}selected{/if}>{l s='Female' mod='sisowfocum'}</option>
					<option value="M" {if $gender == 'M'}selected{/if}>{l s='Male' mod='sisowfocum'}</option>
				</select>
			</div>
			<br/>
			<label class="required" for="sisowfocum_iban">{l s='IBAN' mod='sisowfocum'}</label>
			<div class="input-box">
				<input id="sisowfocum_iban" class="input-text required-entry" maxlength="20" title="IBAN" value="" name="sisowfocum_iban"/>
			</div>
			<br/>
			<label class="required" for="sisowfocum_phone">{l s='Telephone Number' mod='sisowfocum'}</label>
			<div class="input-box">
				<input id="sisowfocum_phone" class="input-text required-entry" maxlength="12" title="Telefoonnummer" value="{$phone}" name="sisowfocum_phone"/>
			</div>
			<br/>
			<label class="required" for="sisowfocum_day">{l s='Date of birth' mod='sisowfocum'}</label></br>
			<div class="select">
					<select id="sisowfocum_day" name="sisowfocum_day" title="sisowfocum_day" class="year required-entry">
						<option value="">{l s='Day' mod='sisowfocum'}</option>
						{foreach from=$days key=k item=v}
							<option value="{$k}" {if $dob.day == $k}selected{/if}>{$v}</option>
						{/foreach}
					<select>
					<select id="sisowfocum_month" name="sisowfocum_month" title="sisowfocum_day" class="year required-entry">
						<option value="">{l s='Month' mod='sisowfocum'}</option>
						{foreach from=$months key=k item=v}
							<option value="{$k}" {if $dob.month == $k}selected{/if}>{$v}</option>
						{/foreach}
					<select>
					<select id="sisowfocum_year" name="sisowfocum_year" title="sisowfocum_year" class="year required-entry">
						<option value="">{l s='Year' mod='sisowfocum'}</option>
						{foreach from=$years key=k item=v}
							<option value="{$k}" {if $dob.year == $k}selected{/if}>{$v}</option>
						{/foreach}
					<select>
			</div>
</p>

<p class="cart_navigation" id="cart_navigation">
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='sisowfocum'}</a>
	<input type="submit" value="{l s='I confirm my order' mod='sisowfocum'}" id="sisowsubmit" class="exclusive_large" />
</p>
</form>

<script type="text/javascript">
	var mess_sisowfocum_error = "{l s='Choose your bank!' mod='sisowfocum' js=1}";
	{literal}
		$(document).ready(function(){

			$('#sisowsubmit').click(function()
				{
				if ($('#issuerid').val() == '')
				{
					alert(mess_sisowfocum_error);
				}
				else
				{
					$('#sisowfocum_form').submit();
				}
				return false;
			});
		});
	{/literal}
</script>