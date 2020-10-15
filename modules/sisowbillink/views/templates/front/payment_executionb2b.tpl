{capture name=path}{l s='billink' mod='sisowbillink'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='sisowbillink'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<form name="sisowbillink_form" id="sisowbillink_form" action="{$base_dir_ssl}modules/sisow/payment.php?payment={$paymentcode}&paymentname={$paymentname}" method="post">
	<p>
		<img src="{$modules_dir}/sisowbillink/LogoBillink.png"/>
	</p>
	<p>
		<div class="input-box">
			<label class="required" for="sisowbillink_gender">{l s='Salutation' mod='sisowbillink'}</label><br/>
			<select id="sisowbillink_gender" class="input-text required-entry" title="Aanhef" name="sisowbillink_gender">
				<option value="">{l s='--Please Select--' mod='sisowbillink'}</option>
				<option value="F" {if $gender == 'F'}selected{/if}>{l s='Female' mod='sisowbillink'}</option>
				<option value="M" {if $gender == 'M'}selected{/if}>{l s='Male' mod='sisowbillink'}</option>
			</select>
		</div>
	</p>
	<p>
		<div class="input-box">
			<label class="required" for="sisowbillink_phone">{l s='Telephone Number' mod='sisowbillink'}</label><br/>
			<input id="sisowbillink_phone" class="input-text required-entry" maxlength="12" title="Telefoonnummer" value="{$phone}" name="sisowbillink_phone"/>
		</div>
	</p>
	<p>
		<div class="select">
			<label class="required" for="sisowbillink_day">{l s='Date of birth' mod='sisowbillink'}</label></br>
			<select id="sisowbillink_day" name="sisowbillink_day" title="sisowbillink_day" class="year required-entry">
				<option value="">{l s='Day' mod='sisowbillink'}</option>
				{foreach from=$days key=k item=v}
					<option value="{$k}" {if $dob.day == $k}selected{/if}>{$v}</option>
				{/foreach}
			<select>
			<select id="sisowbillink_month" name="sisowbillink_month" title="sisowbillink_day" class="year required-entry">
				<option value="">{l s='Month' mod='sisowbillink'}</option>
				{foreach from=$months key=k item=v}
					<option value="{$k}" {if $dob.month == $k}selected{/if}>{$v}</option>
				{/foreach}
			<select>
			<select id="sisowbillink_year" name="sisowbillink_year" title="sisowbillink_year" class="year required-entry">
				<option value="">{l s='Year' mod='sisowbillink'}</option>
				{foreach from=$years key=k item=v}
					<option value="{$k}" {if $dob.year == $k}selected{/if}>{$v}</option>
				{/foreach}
			<select>
		</div>
	</p>
	
	<p>
		<div class="input-box">
			<label class="" for="sisowbillink_coc">{l s='CoC number' mod='sisowbillink'}</label><br/>
			<input id="sisowbillink_coc" class="input-text required-entry" maxlength="12" title="{l s='CoC number' mod='sisowbillink'}" name="sisowbillink_coc"/><br/>
			<small>({l s='Only for B2B' mod='sisowbillink'})</small>
		</div>
	</p>

<p class="cart_navigation" id="cart_navigation" style="padding-top: 25px;">
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='sisowbillink'}</a>
	<input type="submit" value="{l s='I confirm my order' mod='sisowbillink'}" id="sisowsubmit" class="exclusive_large" />
</p>
</form>

<script type="text/javascript">
	var mess_sisowbillink_error = "{l s='Choose your bank!' mod='sisowbillink' js=1}";
	{literal}
		$(document).ready(function(){

			$('#sisowsubmit').click(function()
				{
				if ($('#issuerid').val() == '')
				{
					alert(mess_sisowbillink_error);
				}
				else
				{
					$('#sisowbillink_form').submit();
				}
				return false;
			});
		});
	{/literal}
</script>