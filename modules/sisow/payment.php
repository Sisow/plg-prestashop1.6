<?php
include_once(dirname(__FILE__).'/../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../init.php');
include_once(dirname(__FILE__).'/../sisow/prep.php');
include_once(dirname(__FILE__).'/../sisow/sisow.cls5.php');

class Payment extends PaymentModule
{
	function pay($payment, $paymentname)
	{
		if(file_exists(_PS_MODULE_DIR_.$paymentname.'/'.$paymentname.'.php'))
		{
			include_once(_PS_MODULE_DIR_.$paymentname.'/'.$paymentname.'.php');
			$paymentclass = new $paymentname();
		}
		else
			exit('payment class not found!');

        $module = Module::getInstanceByName($paymentname);
		
		if($paymentname == 'sisowafterpay')
		{
			$terms = Tools::getValue('afterpay_terms');
			
			if(!$terms)
			{
				$url = $this->context->link->getModuleLink('sisowafterpay','payment', array('error' => $module->l('U dient de voorwaarden te accepteren')));
				Tools::redirectLink($url);
				exit;
			}
		}
			
		$merchantid = Configuration::get('SISOW'.strtoupper($payment).'_MERCHANTID');
		$merchantkey = Configuration::get('SISOW'.strtoupper($payment).'_MERCHANTKEY');	
		$shopid = Configuration::get('SISOW'.strtoupper($payment).'_SHOPID');			
		$testmode = Configuration::get('SISOW'.strtoupper($payment).'_TEST');
		$createorder = Configuration::get('SISOW'.strtoupper($payment).'_ORDERBEFORE');
		$prefix = Configuration::get('SISOW'.strtoupper($payment).'_PREFIX');

		$total = floatval(number_format($this->context->cart->getOrderTotal(true, 3), 2, '.', ''));

		/* begin */
		if (Module::isEnabled('bestkit_paymentfee')) {
			require_once _PS_MODULE_DIR_ . 'bestkit_paymentfee/includer.php';
			$p_fee = BestkitPaymentfee::getRowByModuleName($paymentname);

			if (isset($p_fee['id_bestkit_paymentfee'])) {
			$total = $total * $p_fee['value_percent'] + $p_fee['value_amount'];
			}
		}
		/* end */

				
		$arr = _prepare($this->context->cart, $payment, $module);
		
		if($testmode == "test")
			$arr['testmode'] = 'true';
				
		if($payment == 'focum' || $payment == 'afterpay' || $payment == 'capayable' || $payment == 'billink')
		{	
			switch($payment)
			{
				case "focum":
					$method = "sisowfocum";
					break;
				case "afterpay":
					$method = "sisowafterpay";
					break;
				case "capayable":
					$method = "sisowcapayable";
					break;
				case "billink":
					$method = "sisowbillink";
					break;
			}
			
			$arr['gender'] = $_POST[$method . '_gender'];
			$arr['birthdate'] = $_POST[$method . '_day'] . $_POST[$method . '_month'] . $_POST[$method . '_year'];
			$arr['billing_phone'] = $_POST[$method . '_phone'];
			
			if($payment == 'klarnaacc')
			{
				$arr['pclass'] = $_POST[$method . '_pclass'];
			}
			else if($payment == 'focum')
			{
				$arr['iban'] = $_POST[$method . '_iban'];
			}
			else if($payment == 'afterpay')
			{
				$createInvoice = Configuration::get('SISOW'.strtoupper($payment).'_CREATEAPINVOICE');
				$createInvoiceStatus = Configuration::get('SISOW'.strtoupper($payment).'_CREATEAPINVOICEATSTATUS');
				
				$arr['makeinvoice'] = $createInvoice == 'yes' && empty($createInvoiceStatus) ? 'true' : 'false';
			}
		}	
		else if($payment == 'overboeking')
		{
			if(Configuration::get('SISOW'.strtoupper($payment).'_INCLUDE') == 'yes')
				$arr['including'] = 'true';
			if(Configuration::get('SISOW'.strtoupper($payment).'_DAYS') > 0)
				$arr['days'] = Configuration::get('SISOW'.strtoupper($payment).'_DAYS');
		}
		
		if(isset($_POST[$payment . '_bic']) && !empty($_POST[$payment . '_bic']))
			$arr['bic'] = $_POST[$payment . '_bic'];

		if($createorder == 'before')
		{
			$st = Configuration::getGlobalValue('SISOW_PENDING');
			
			if (floatval(substr(_PS_VERSION_, 0, 3)) < 1.4)
				$paymentclass->validateOrder($this->context->cart->id, $st, 0, $paymentclass->displayName); //, NULL, NULL, $currency->id);
			else 
				$paymentclass->validateOrder($this->context->cart->id, $st, 0, $paymentclass->displayName, NULL, NULL, NULL, false, $this->context->cart->secure_key);
				
			$orderid = $paymentclass->currentOrder;
		}
		else
		{
			$orderid = $this->context->cart->id;
		}

		$sisow = new Sisow($merchantid, $merchantkey, $shopid);
		$sisow->amount = $total;
		$sisow->payment = $payment;
		
		if($payment == 'ideal')
			$sisow->issuerId = $_POST['issuerid'];
			
		$sisow->purchaseId = $orderid;
		$sisow->description = ($prefix == "") ? $this->context->shop->name . ' ' . $orderid : $prefix . $orderid;
		$sisow->notifyUrl = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/sisow/validation.php?id_cart='.$this->context->cart->id . '&payment='.$payment.'&paymentname='.$paymentname;
		$sisow->returnUrl = $sisow->notifyUrl;

        if (($ex = $sisow->TransactionRequest($arr)) < 0) {
			echo '<a href="' . __PS_BASE_URI__.'order.php?step=3&'.$payment."error=".$error . '">'.$module->l('Helaas is er iets mis gegaan met uw bestelling, klik hier om terug te keren').' (' . $sisow->errorCode . ').</a>';
			exit;
			
			if($payment != "focum" && $payment != 'afterpay' && $payment != 'billink' && $createorder == 'before')
			{
				if (version_compare(_PS_VERSION_, '1.6', '>='))
				{
					if(class_exists("PrestaShopLogger"))
					{
						$message = 'TransactionRequest error: ex(' . $ex . '), errorcode(' . $sisow->errorCode . '), errormessage(' . $sisow->errorMessage . ')';	
						PrestaShopLogger::addLog($message, 3, '0000001', 'Sisow', intval($paymentclass->currentOrder));
					}
				}
				$order = new Order($paymentclass->currentOrder);
				$order->setCurrentState(Configuration::get('SISOW_PAYMENTFAIL'));
				
				$oldCart = new Cart(Order::getCartIdStatic($paymentclass->currentOrder, $this->context->customer->id));
				$duplication = $oldCart->duplicate();
				if (!$duplication || !Validate::isLoadedObject($duplication['cart']))
					$this->errors[] = Tools::displayError('Sorry. We cannot renew your order.');
				else if (!$duplication['success'])
					$this->errors[] = Tools::displayError('Some items are no longer available, and we are unable to renew your order.');
				else
				{
					$this->context->cookie->id_cart = $duplication['cart']->id;
					$this->context->cookie->write();

					$error = 'Sisow error: ' . $ex . ' ' . $sisow->errorCode;
					Tools::redirectLink(__PS_BASE_URI__.'order.php?step=3&'.$payment."error=".$error);			
					exit;
				}
			}
			else
			{
				if($sisow->payment == 'focum') {
                    $error = $module->l('Betalen met Achteraf Betalen is niet mogelijk, betaal anders.');
                } else if($sisow->payment == 'afterpay') {
                    $error = $module->l('Betalen met Afterpay is niet mogelijk, betaal anders.');
                } else if($sisow->payment == 'billink') {
                    $error = $module->l('Betalen met Billink is niet mogelijk, betaal anders.');
                } else
					$error = 'Sisow error: ' . $ex . ' ' . $sisow->errorCode;
						
				Tools::redirectLink(__PS_BASE_URI__.'order.php?step=3&'.$payment."error=".$error);			
				exit;
			}

			//echo 'Sisow error: ' . $ex . ' ' . $sisow->errorMessage;
		}
		else {
			if($payment == 'overboeking' || $payment == 'ebill' || $payment == 'focum' || $payment == 'afterpay' || $payment == 'billink')
			{	
				$status = 'Pending';
				$pendingklarna = '&pendingklarna=true';
				
				if($payment == 'focum' || $payment == 'afterpay' || $payment == 'billink')
				{
					$orderstatus = _PS_OS_PAYMENT_;
					$status = 'Reservation';
					$pendingklarna = '';
				}
				else if(($payment == "klarna" || $payment == "klarnaacc") && !$sisow->pendingKlarna)
				{
					$orderstatus = _PS_OS_PAYMENT_;
					$status = 'Reservation';
					$pendingklarna = '';
				}
				else if($payment == 'overboeking' || $payment == 'ebill')
				{
					$orderstatus = _PS_OS_BANKWIRE_;
					$status = 'Pending';
				}
				
				if($orderstatus > 0)
				{
					$extra_vars = array();
					$extra_vars['transaction_id'] = $sisow->trxId;
					if (floatval(substr(_PS_VERSION_, 0, 3)) < 1.4)
						$paymentclass->validateOrder($this->context->cart->id, $orderstatus, $total, $paymentclass->displayName); //, NULL, NULL, $currency->id);
					else 
						$paymentclass->validateOrder($this->context->cart->id, $orderstatus, $total, $paymentclass->displayName, NULL, $extra_vars, NULL, false, $this->context->cart->secure_key);
					
					$order = new Order($paymentclass->currentOrder);
				}
				else
				{
					$order = new Order($sisow->purchaseId);
				}
				
				$db = Db::getInstance();
				$result = $db->Execute("delete from `" . _DB_PREFIX_ . "sisow` where `id_order` = " . $order->id);
				
				$result = $db->Execute("
				INSERT INTO `" . _DB_PREFIX_ . "sisow`
				(`id_order`, `trxid`, `status` ,`consumeraccount`,`consumername`,`payment`)
				VALUES
				(" . $order->id . ", '" . $sisow->trxId . "', '".$status."', '', '', '".$paymentclass->paymentcode."')");
										
				Tools::redirectLink('order-confirmation.php?id_cart='.$order->id_cart.'&id_module='.$paymentclass->id.'&id_order='.$order->id.'&key='.$order->secure_key . $pendingklarna);	
			}
			else
			{	
				$order = new Order($paymentclass->currentOrder);				
				header('Location: ' . $sisow->issuerUrl);
			}
			exit;
		}
	}
}

$payment = $_GET['payment'];
$paymentname = $_GET['paymentname'];

$paymentclass = new Payment();
$paymentclass->pay($payment, $paymentname);