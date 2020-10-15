<?php 

if (!defined('_PS_VERSION_'))
	exit;
	
class SisowEps extends PaymentModule
{
	public function __construct()
	{
		$this->paymentcode = 'eps';
		$this->name = 'sisoweps';
		$this->paymentname = 'EPS';
		$this->tab = 'payments_gateways';
		$this->version = '4.7.1';
		$this->author = 'Sisow';
		
		$this->bootstrap = true;

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
		$output = null;
		
		if (Tools::isSubmit('submit'.$this->name))
		{
			// get settings from post because post can give errors and you want to keep values
			$merchantid = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_MERCHANTID');
			$merchantkey = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_MERCHANTKEY');
			$shopid = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_SHOPID');
			$test = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_TEST');
			$createorder = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_ORDERBEFORE');
			$orderprefix = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_PREFIX');
			$orderSuccessState = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_ORDERSUCCESSTATE');

			// no errors so update the values
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_MERCHANTID', $merchantid);
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_MERCHANTKEY', $merchantkey);
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_SHOPID', $shopid);
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_TEST', $test);
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_ORDERBEFORE', $createorder);
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_PREFIX', $orderprefix);
			Configuration::updateValue('SISOW'.strtoupper($this->paymentcode).'_ORDERSUCCESSTATE', $orderSuccessState);
			
			$output .= $this->displayConfirmation($this->l('Settings updated'));
		}
			
		return $output.$this->displayForm();
	}
	public function displayForm()
	{
		global $cookie;
		$id_lang = $cookie->id_lang;
				
		$fields_form[0]['form'] = array (
			'legend' => array (
				'title' => $this->l('General Settings'),
				'image' => '../img/admin/edit.gif'
			),
			'input' => array (
				array (
					'type' => 'text',
					'label' => $this->l('Merchant ID'),
					'name' => 'SISOW'.strtoupper($this->paymentcode).'_MERCHANTID',
					'size' => 20,
					'required' => true,
					'hint' => $this->l('The Sisow Merchant ID, you can find this in your Sisow profile on www.sisow.nl')
				),
				array (
					'type' => 'text',
					'label' => $this->l('Merchant Key'),
					'name' => 'SISOW'.strtoupper($this->paymentcode).'_MERCHANTKEY',
					'size' => 64,
					'required' => true,
					'hint' => $this->l('The Sisow Merchant Key, you can find this in your Sisow profile on www.sisow.nl')
				),
				array (
					'type' => 'text',
					'label' => $this->l('Shop ID'),
					'name' => 'SISOW'.strtoupper($this->paymentcode).'_SHOPID',
					'size' => 20,
					'required' => true,
					'hint' => $this->l('The Sisow Shop ID, you can find this in your Sisow profile on www.sisow.nl')
				),
				array (
					'type' => 'radio',
					'label' => $this->l('Test/Production Mode'),
					'name' => 'SISOW'.strtoupper($this->paymentcode).'_TEST',
					'class' => 't',
					'values' => array (
						array (
							'id' => 'live',
							'value' => 'live',
							'label' => $this->l('Live Mode')
						),
						array (
							'id' => 'test',
							'value' => 'test',
							'label' => $this->l('Test Mode')
						)
					),
					'required' => true
				),
				array (
					'type' => 'radio',
					'label' => $this->l('Create order'),
					'name' => 'SISOW'.strtoupper($this->paymentcode).'_ORDERBEFORE',
					'class' => 't',
					'values' => array (
						array (
							'id' => 'before',
							'value' => 'before',
							'label' => $this->l(' Before payment, all orders are visible and orderId is sent to Sisow')
						),
						array (
							'id' => 'after',
							'value' => 'after',
							'label' => $this->l('After payment, only succesfull orders are visible and cartId is sent to Sisow')
						)
					),
					'required' => true
				),
				array (
					'type' => 'text',
					'label' => $this->l('Order prefix'),
					'name' => 'SISOW'.strtoupper($this->paymentcode).'_PREFIX',
					'size' => 28,
					'required' => true,
					'hint' => $this->l('The order prefix, this is visible on the bank statement of the customer.')
				),
				array(
				  'type' => 'select',                              // This is a <select> tag.
				  'label' => $this->l('Order Succes state:'),         // The <label> for this <select> tag.
				  'desc' => $this->l('Choose a order success status'),  // A help text, displayed right next to the <select> tag.
				  'name' => 'SISOW'.strtoupper($this->paymentcode).'_ORDERSUCCESSTATE',                   // The content of the 'id' attribute of the <select> tag.
				  'required' => true,                              // If set to true, this option must be set.
				  'options' => array(
					'query' => OrderStateCore::getOrderStates($id_lang),                           // $options contains the data itself.
					'id' => 'id_order_state',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
					'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
				  )
				)
			),
			'submit' => array (
					'title' => $this->l('Save'),
					'class' => 'btn btn-default pull-right'
				)
		);
		
		$helper = new HelperForm();
		
		// Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		
		// Title and toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true; // false -> remove toolbar
		$helper->toolbar_scroll = true; // yes - > Toolbar is always visible on the top of the screen.
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array (
			'save' => array (
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules')
			),
			'back' => array (
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);
		
		if (Tools::isSubmit('submit'.$this->name))
		{
			// get settings from post because post can give errors and you want to keep values
			$merchantid = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_MERCHANTID');
			$merchantkey = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_MERCHANTKEY');
			$shopid = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_SHOPID');
			$test = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_TEST');
			$createorder = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_ORDERBEFORE');
			$orderprefix = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_PREFIX');
			$orderSuccessState = (string)Tools::getValue('SISOW'.strtoupper($this->paymentcode).'_ORDERSUCCESSTATE');
		}
		else
		{
			$merchantid = Configuration::get('SISOW'.strtoupper($this->paymentcode).'_MERCHANTID');
			$merchantkey = Configuration::get('SISOW'.strtoupper($this->paymentcode).'_MERCHANTKEY');
			$shopid = Configuration::get('SISOW'.strtoupper($this->paymentcode).'_SHOPID');
			$test = Configuration::get('SISOW'.strtoupper($this->paymentcode).'_TEST');
			$createorder = Configuration::get('SISOW'.strtoupper($this->paymentcode).'_ORDERBEFORE');
			$orderprefix = Configuration::get('SISOW'.strtoupper($this->paymentcode).'_PREFIX');
			$orderSuccessState = Configuration::get('SISOW'.strtoupper($this->paymentcode).'_ORDERSUCCESSTATE');
		}
		
		// Load current value
		$helper->fields_value['SISOW'.strtoupper($this->paymentcode).'_MERCHANTID'] = $merchantid;
		$helper->fields_value['SISOW'.strtoupper($this->paymentcode).'_MERCHANTKEY'] = $merchantkey;
		$helper->fields_value['SISOW'.strtoupper($this->paymentcode).'_SHOPID'] = $shopid;
		$helper->fields_value['SISOW'.strtoupper($this->paymentcode).'_TEST'] = $test;
		$helper->fields_value['SISOW'.strtoupper($this->paymentcode).'_ORDERBEFORE'] = $createorder;
		$helper->fields_value['SISOW'.strtoupper($this->paymentcode).'_PREFIX'] = $orderprefix;
		$helper->fields_value['SISOW'.strtoupper($this->paymentcode).'_ORDERSUCCESSTATE'] = $orderSuccessState;
		
		return $helper->generateForm($fields_form);
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
		
		$error = '';
		$error = (isset($_GET[$this->paymentcode.'error'])) ? $_GET[$this->paymentcode.'error'] : '';				 
			
		$this->smarty->assign($this->paymentcode.'error', $error);
		$this->smarty->assign('paymentcode', $this->paymentcode);
		$this->smarty->assign('paymentname', $this->name);
		$this->smarty->assign('paymenttext', $this->l('Pay with') . ' ' . $this->paymentname);
		$this->smarty->assign(array(
			'this_path' => $this->_path,
			'this_path_bw' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
				
		return $this->display(__FILE__, 'views/hook/payment.tpl');
		
	}
	
	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return '';
		$this->context->smarty->assign('successline1', $this->l('Your order on %s is complete.'));
		$this->context->smarty->assign('successline2', $this->l('Your payment is processed with %s.'));
		
		$this->context->smarty->assign('paymentcode', $this->paymentcode);
		$this->context->smarty->assign('paymentname', $this->paymentname);
		
		return $this->display(__FILE__, '../sisow/views/hook/confirmation.tpl');
	}	
}