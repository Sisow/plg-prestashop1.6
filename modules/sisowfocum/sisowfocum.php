<?php 
if (!defined('_PS_VERSION_'))
	exit;
	
class SisowFocum extends PaymentModule
{
	public function __construct()
	{
		$this->paymentcode = 'focum';
		$this->name = 'sisowfocum';
		$this->paymentname = $this->l('Achteraf Betalen');
		$this->tab = 'payments_gateways';
		$this->version = '4.5.1';
		$this->author = 'Sisow';
		$this->available_countries = array('NL');//array('NL','DE','DK','FI','NO','SE');

		$this->currencies = true;
		$this->currencies_mode = 'radio';

		parent::__construct();
		
		$this->displayName = 'Sisow ' . $this->paymentname;
		$this->description = sprintf($this->l('Processing %s transactions with Sisow.'), $this->paymentname);
		$this->confirmUninstall = sprintf($this->l('Are you sure you want to delete Sisow %s?'), $this->paymentname);

		$this->page = basename(__FILE__, '.php');

		if (version_compare(_PS_VERSION_, '1.5', '<'))
		{
			$mobile_enabled = (int)Configuration::get('PS_MOBILE_DEVICE');
			require(_PS_MODULE_DIR_.'/sisow/backward_compatibility/backward.php');
		}
		else
			$mobile_enabled = (int)Configuration::get('PS_ALLOW_MOBILE_DEVICE');

        // Only for translation
        $this->l('Verzendkosten');
        $this->l('Kosten inpakken');
        $this->l('Correctieregel');
        $this->l('Betalen met Achteraf Betalen is niet mogelijk, betaal anders.');
        $this->l('Helaas is er iets mis gegaan met uw bestelling, klik hier om terug te keren');
	}
	
	/**
	*	Function install()
	*	Is called when 'Install' in on this module within administration page
	*/
	public function install()
	{
		require_once(_PS_MODULE_DIR_.'/sisow/install.php');
		$sisow_install = new SisowInstall();
	
		if (!parent::install()
			|| !$sisow_install->updateConfiguration($this->paymentcode)
			|| !$sisow_install->createTables()
			|| !$sisow_install->createOrderState()
			|| !$this->registerHook('payment')
			|| !$this->registerHook('paymentReturn')
			)
			return false;
			
		return true;
	}
	
	public function uninstall()
	{
		require_once(_PS_MODULE_DIR_.'/sisow/install.php');
		$sisow_install = new SisowInstall();
		
		$sisow_install->deleteConfiguration($this->paymentcode)	;
		return parent::uninstall();
	}
	
	public function getContent()
	{
		if (Tools::isSubmit('submitSisow'))
		{
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_MERCHANTID', Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_MERCHANTID'));
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_MERCHANTKEY', Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_MERCHANTKEY'));
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_SUBID', Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_SUBID'));
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_PREFIX', Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_PREFIX'));
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_ORDERSUCCESSTATE', Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_ORDERSUCCESSTATE'));
						
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_MINAMOUNT', Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_MINAMOUNT'));
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_MAXAMOUNT', Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_MAXAMOUNT'));

			echo $this->displayConfirmation($this->l('Configuration saved'));
		}
		
		return '
		<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset><legend><img src="../img/admin/contact.gif" /> '.$this->l('Settings').'</legend>
				<label for="merchantid">Merchant ID</label>
				<div class="margin-form">
					<input type="text" id="merchantid" size="20" name="SISOW'.strtoupper($this->paymentcode).'_MERCHANTID" value="'.Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_MERCHANTID', Configuration::get('SISOW'.strtoupper($this->paymentcode).'_MERCHANTID')).'" />
				</div>
				<div class="clear">&nbsp;</div>
				<label for="merchantkey">Merchant Key</label>
				<div class="margin-form">
					<input type="text" id="merchantkey" size="20" name="SISOW'.strtoupper($this->paymentcode).'_MERCHANTKEY" value="'.Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_MERCHANTKEY', Configuration::get('SISOW'.strtoupper($this->paymentcode).'_MERCHANTKEY')).'" />
				</div>
				<div class="clear">&nbsp;</div>
				<label for="subid">Sisow SubId</label>
				<div class="margin-form">
					<input type="text" id="subid" size="20" name="SISOW'.strtoupper($this->paymentcode).'_SUBID" value="'.Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_SUBID', Configuration::get('SISOW'.strtoupper($this->paymentcode).'_SUBID')).'" />
				</div>
				<div class="clear">&nbsp;</div>
				<label for="prefix">Order prefix</label>
				<div class="margin-form">
					<input type="text" id="prefix" size="20" name="SISOW'.strtoupper($this->paymentcode).'_PREFIX" value="'.Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_PREFIX', Configuration::get('SISOW'.strtoupper($this->paymentcode).'_PREFIX')).'" />
				</div>
				<div class="clear">&nbsp;</div>
				<label for="prefix">Min Order Amount</label>
				<div class="margin-form">
					<input type="text" id="prefix" size="20" name="SISOW'.strtoupper($this->paymentcode).'_MINAMOUNT" value="'.Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_MINAMOUNT', Configuration::get('SISOW'.strtoupper($this->paymentcode).'_MINAMOUNT')).'" />
				</div>
				<div class="clear">&nbsp;</div>
				<label for="prefix">Max Order Amount</label>
				<div class="margin-form">
					<input type="text" id="prefix" size="20" name="SISOW'.strtoupper($this->paymentcode).'_MAXAMOUNT" value="'.Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_MAXAMOUNT', Configuration::get('SISOW'.strtoupper($this->paymentcode).'_MAXAMOUNT')).'" />
				</div>
				<div class="clear">&nbsp;</div>
				<input type="submit" name="submitSisow" value="'.$this->l('Save').'" class="button" />
			</fieldset>
		</form>
		<div class="clear">&nbsp;</div>';
	}
	
	/**
	*	hookPayment($params)
	*	Called in Front Office at Payment Screen - displays user this module as payment option
	*/
	function hookPayment($params)
	{
		$minamount = Configuration::get('SISOW'.strtoupper($this->paymentcode).'_MINAMOUNT');
		$maxamount = Configuration::get('SISOW'.strtoupper($this->paymentcode).'_MAXAMOUNT');
		
		if($minamount > 0 || $maxamount > 0)
		{
			$cart = new Cart($params['cart']->id);
			$total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
			if($minamount > 0 && $total < $minamount)
				return;
			
			if($maxamount > 0 && $total > $maxamount)
				return;
		}
		
		$address = new Address($params['cart']->id_address_invoice);	
		$country = new Country($address->id_country);
		
		$error = '';
		$error = (isset($_GET[$this->paymentcode.'error'])) ? $_GET[$this->paymentcode.'error'] : '';				 
			
		$this->context->smarty->assign($this->paymentcode.'error', $error);
		$this->context->smarty->assign('paymentcode', $this->paymentcode);
		$this->context->smarty->assign('paymentname', $this->name);
		$this->context->smarty->assign('paymenttext', $this->l('Pay with') . ' ' . $this->paymentname);
				
		return $this->display(__FILE__, 'views/hook/payment.tpl');
	}
	
	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return '';
		
		$address = new Address($params['objOrder']->id_address_invoice);	
		$country = new Country($address->id_country);
							
		$this->context->smarty->assign('image_url', 'https://www.achterafbetalen.nl/files/logo.png');	
		$this->context->smarty->assign('successline1', $this->l('Your order on %s is complete.'));
		$this->context->smarty->assign('successline2', $this->l('Your payment is processed with %s.'));
		
		$this->context->smarty->assign('paymentcode', $this->paymentcode);
		$this->context->smarty->assign('paymentname', "Achteraf betalen");
		
		return $this->display(__FILE__, '../sisow/views/hook/confirmation.tpl');
	}
}