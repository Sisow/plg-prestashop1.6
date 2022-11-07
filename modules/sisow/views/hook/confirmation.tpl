{if $paymentcode == 'overboeking' || $paymentcode == 'ebill'}
	<b>{$successline1|sprintf:$shop_name}<b>
	<br/><br/>
	{$successline2|sprintf:$paymentname}	
	<br/>
	{$successline3}
	<br/>
	{$successline4}
	<br/><br/>
{elseif $paymentcode == 'klarna' || $paymentcode == 'klarnaacc'}
	<b>{$successline1|sprintf:$shop_name}<b>
	<br/><br/>
	{$successline2|sprintf:$paymentname}	
	{if $image_url != ''}
		</br></br>
		<img src="{$image_url}" alt="{$paymentname}"/>
	{/if}
{else}
	<b>{$successline1|sprintf:$shop_name}<b>
	<br/><br/>
	{$successline2|sprintf:$paymentname}	
{/if}	