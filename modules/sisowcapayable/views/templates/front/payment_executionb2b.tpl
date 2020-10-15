{capture name=path}{l s='In3 Gespreid Betalen' mod='sisowcapayable'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='sisowcapayable'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<form name="sisowcapayable_form" id="sisowcapayable_form" action="{$base_dir_ssl}modules/sisow/payment.php?payment={$paymentcode}&paymentname={$paymentname}" method="post">
	<p>
		<img src="{$modules_dir}/sisowcapayable/LogoCapayable.png"/>
	</p>
	<p>
		<div class="input-box">
			<label class="required" for="sisowcapayable_gender">{l s='Salutation' mod='sisowcapayable'}</label><br/>
			<select id="sisowcapayable_gender" class="input-text required-entry" title="Aanhef" name="sisowcapayable_gender">
				<option value="">{l s='--Please Select--' mod='sisowcapayable'}</option>
				<option value="F" {if $gender == 'F'}selected{/if}>{l s='Female' mod='sisowcapayable'}</option>
				<option value="M" {if $gender == 'M'}selected{/if}>{l s='Male' mod='sisowcapayable'}</option>
			</select>
		</div>
	</p>
	<p>
		<div class="input-box">
			<label class="required" for="sisowcapayable_phone">{l s='Telephone Number' mod='sisowcapayable'}</label><br/>
			<input id="sisowcapayable_phone" class="input-text required-entry" maxlength="12" title="Telefoonnummer" value="{$phone}" name="sisowcapayable_phone"/>
		</div>
	</p>
	<p>
		<div class="select">
			<label class="required" for="sisowcapayable_day">{l s='Date of birth' mod='sisowcapayable'}</label></br>
			<select id="sisowcapayable_day" name="sisowcapayable_day" title="sisowcapayable_day" class="year required-entry">
				<option value="">{l s='Day' mod='sisowcapayable'}</option>
				{foreach from=$days key=k item=v}
					<option value="{$k}" {if $dob.day == $k}selected{/if}>{$v}</option>
				{/foreach}
			<select>
			<select id="sisowcapayable_month" name="sisowcapayable_month" title="sisowcapayable_day" class="year required-entry">
				<option value="">{l s='Month' mod='sisowcapayable'}</option>
				{foreach from=$months key=k item=v}
					<option value="{$k}" {if $dob.month == $k}selected{/if}>{$v}</option>
				{/foreach}
			<select>
			<select id="sisowcapayable_year" name="sisowcapayable_year" title="sisowcapayable_year" class="year required-entry">
				<option value="">{l s='Year' mod='sisowcapayable'}</option>
				{foreach from=$years key=k item=v}
					<option value="{$k}" {if $dob.year == $k}selected{/if}>{$v}</option>
				{/foreach}
			<select>
		</div>
	</p>
	
	<p>
		<div class="input-box">
			<label class="" for="sisowcapayable_coc">{l s='CoC number' mod='sisowcapayable'}</label><br/>
			<input id="sisowcapayable_coc" class="input-text required-entry" maxlength="12" title="{l s='CoC number' mod='sisowcapayable'}" name="sisowcapayable_coc"/><br/>
			<small>({l s='Only for B2B' mod='sisowcapayable'})</small>
		</div>
	</p>

<p class="cart_navigation" id="cart_navigation" style="padding-top: 25px;">
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='sisowcapayable'}</a>
	<input type="submit" value="{l s='I confirm my order' mod='sisowcapayable'}" id="sisowsubmit" class="exclusive_large" />
</p>
</form>

<script type="text/javascript">
	var mess_sisowcapayable_error = "{l s='Choose your bank!' mod='sisowcapayable' js=1}";
	{literal}
		$(document).ready(function(){

			$('#sisowsubmit').click(function()
				{
				if ($('#issuerid').val() == '')
				{
					alert(mess_sisowcapayable_error);
				}
				else
				{
					$('#sisowcapayable_form').submit();
				}
				return false;
			});
		});
	{/literal}
</script>