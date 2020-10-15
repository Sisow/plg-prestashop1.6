<?php
include_once(dirname(__FILE__).'/../../config/config.inc.php');
include(_PS_MODULE_DIR_.'sisow/sisow.cls5.php');

$paymentname = $_GET['paymentname'];
$payment = $_GET['payment'];

include(_PS_MODULE_DIR_.$paymentname.'/'.$paymentname.'.php');

$paymentclass = new $paymentname();

$returnid = $_GET['ec'];

class validation extends PaymentModule
{
	function validate($paymentclass, $returnid)
	{
		// Set default values
		$merchantid = Configuration::get('SISOW'.strtoupper($paymentclass->paymentcode).'_MERCHANTID');
		$merchantkey = Configuration::get('SISOW'.strtoupper($paymentclass->paymentcode).'_MERCHANTKEY');
		$shopid = Configuration::get('SISOW'.strtoupper($paymentclass->paymentcode).'_SHOPID');
		$createorder = Configuration::get('SISOW'.strtoupper($paymentclass->paymentcode).'_ORDERBEFORE');
		$orderSuccessStatus = (int)Configuration::get('SISOW'.strtoupper($paymentclass->paymentcode).'_ORDERSUCCESSTATE');
		
		// Check order success status id > 0
		if($orderSuccessStatus < 1){
			$orderSuccessStatus = _PS_OS_PAYMENT_;
			
			if($orderSuccessStatus < 1){
				exit('No Success status defined');
			}
		}
		
		if($paymentclass->paymentcode == 'overboeking'){			
			// Initiate Sisow object
			$sisow = new Sisow($merchantid, $merchantkey, $shopid);
			if (($ex = $sisow->StatusRequest($_GET['trxid'])) < 0) // Status request failed, exit script
			{
				$message = 'StatusRequest error: ex(' . $ex . '), errorcode(' . $sisow->errorCode . '), errormessage(' . $sisow->errorMessage . ')';
				PrestaShopLogger::addLog($message, 3, '0000001', 'Sisow', intval($orderid));
				exit;
			}
			
			if ($sisow->status == 'Success' || $sisow->status == 'Cancelled'){
				$orderId = Order::getOrderByCartId($returnid);
				$order = new Order($orderId);
				
				$status = $sisow->status == 'Success' ? $orderSuccessStatus : _PS_OS_CANCELED_;
				
				$oh = new OrderHistory();
				$oh->id_order = $order->id;
				$oh->changeIdOrderState($status, $order->id);
				$oh->addWithemail();
			}
		}
		else{
			// Ger order data
			$db = Db::getInstance();
			$orderquery = $db->ExecuteS("
			SELECT * FROM `" . _DB_PREFIX_ . "sisow`
			WHERE `id_order` = '" . intval($returnid) . "'");

			// if order query success, get TrxId from order
			if($orderquery && isset($orderquery['0']) && isset($orderquery['trxid']) ){
				$trxid = $orderquery['trxid'];
			}
			else{
				$trxid = $_GET['trxid'];
			}	
			
			// Initiate Sisow object
			$sisow = new Sisow($merchantid, $merchantkey, $shopid);
			if (($ex = $sisow->StatusRequest($trxid)) < 0) // Status request failed, exit script
			{
				$message = 'StatusRequest error: ex(' . $ex . '), errorcode(' . $sisow->errorCode . '), errormessage(' . $sisow->errorMessage . ')';
				PrestaShopLogger::addLog($message, 3, '0000001', 'Sisow', intval($orderid));
				exit;
			}
			
			// Status Open or Pending, wait for end status
			if ($sisow->status == 'Open'|| $sisow->status == 'Pending')
				exit;
			
			// Order created before or after transaction?
			if($createorder == "after" || $paymentclass->paymentcode == 'capayable') // Order created after
			{
				// Check if status success
				if ($sisow->status == 'Success' || $sisow->status == 'Reservation') {
					$cart = new Cart((int)$returnid);
					
					$paidAmount = $paymentclass->paymentcode == 'webshop' || $paymentclass->paymentcode == 'vvv' ? $cart->getOrderTotal() : $sisow->amount;
					
					$extra_vars = array();
					$extra_vars['transaction_id'] = $sisow->trxId;
					$paymentclass->validateOrder($returnid, $orderSuccessStatus, $paidAmount, $paymentclass->displayName, NULL, $extra_vars, NULL, false, $cart->secure_key);
					
					$this->updateSisowTable($paymentclass->currentOrder, $sisow->trxId, $paymentclass->paymentcode, $sisow->status, $sisow->consumerBic, $sisow->consumerIban, $sisow->consumerName);
					
					if($paymentclass->paymentcode != 'klarna')
						$sisow->AdjustPurchaseId($sisow->trxId, $returnid, $paymentclass->currentOrder);
				}
			}
			else // order created before
			{
				$order = new Order($returnid);
				
				if($order->current_state != $orderSuccessStatus || $sisow->status == 'Reversed' || $sisow->status == 'Refunded')
				{
					$status = "";
					if($sisow->status == "Success" || $sisow->status == "Reservation")
					{
						$status = $orderSuccessStatus;
					}
					else if($sisow->status == 'Reversed' || $sisow->status == 'Refunded')
					{
						$status = _PS_OS_REFUND_;
					}
					else
					{
						$status = _PS_OS_CANCELED_;
					}
					
					//$order->setCurrentState($st);
					
					$oh = new OrderHistory();
					$oh->id_order = $order->id;
					$oh->changeIdOrderState($status, $order->id);
					$oh->addWithemail();
					
					$this->updateSisowTable($returnid, $sisow->trxId, $paymentclass->paymentcode, $sisow->status, $sisow->consumerBic, $sisow->consumerIban, $sisow->consumerName);
				}
			}
		}
		
		echo 'Order status updated!';
		exit;
	}
	
	function updateSisowTable($orderid, $trxId, $payment, $Status = "", $Bic = "", $Iban = "", $Name = "")
	{
		$db = Db::getInstance();
		
		$db = Db::getInstance();
				$result = $db->Execute("
				INSERT INTO `" . _DB_PREFIX_ . "sisow`
				(`id_order`, `trxid`, `status` ,`consumeraccount`,`consumername`, `payment`)
				VALUES
				(" . $orderid . ", '" . $trxId . "', '".$Status."', '".$Bic . " / " . $Iban."', '".$Name."', '".$payment."')");
		
		return true;
	}
	
	function redirect($paymentclass, $returnid)
	{
		$createorder = Configuration::get('SISOW'.strtoupper($paymentclass->paymentcode).'_ORDERBEFORE');
		
		if($createorder == "after"  || $paymentclass->paymentcode == 'capayable')
		{
			if ($_GET['status'] == 'Success' || $_GET['status'] == 'Reservation') {
				$order = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'orders WHERE id_cart = '.(int)$_GET['ec']);
				Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$_GET['ec'].'&id_module='.$paymentclass->id.'&id_order='.$order['id_order'].'&key='.$order['secure_key']);
			}
			else {
				Tools::redirectLink(__PS_BASE_URI__.'order.php?step=3');
				echo '<p>Payment failed</p>';
			}
		}
		else
		{
			$order = new Order($returnid);
			if ($_GET['status'] == 'Success' || $_GET['status'] == 'Reservation') {
				Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$order->id_cart.'&id_module='.$paymentclass->id.'&id_order='.$order->id.'&key='.$order->secure_key);
				exit;
			}
			else 
			{
				$oldCart = new Cart(Order::getCartIdStatic($order->id, $this->context->customer->id));
				$duplication = $oldCart->duplicate();
				if (!$duplication || !Validate::isLoadedObject($duplication['cart']))
					$this->errors[] = Tools::displayError('Sorry. We cannot renew your order.');
				else if (!$duplication['success'])
					$this->errors[] = Tools::displayError('Some items are no longer available, and we are unable to renew your order.');
				else
				{
					$this->context->cookie->id_cart = $duplication['cart']->id;
					$this->context->cookie->write();
					if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1)
						Tools::redirect('index.php?controller=order-opc');
					Tools::redirect('index.php?controller=order');
				}

				Tools::redirectLink(__PS_BASE_URI__.'order.php?step=3');
				exit;
			}
		}
	}
}

if(isset($_GET['notify']) || isset($_GET['callback']))
{
	$validation = new validation();
	$validation->validate($paymentclass, $returnid);
}
else
{
	$validation = new validation();
	$validation->redirect($paymentclass, $returnid);
}
?>