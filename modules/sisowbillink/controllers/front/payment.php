<?php
class sisowbillinkpaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	public $display_column_left = false;
	public $display_column_right = false;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		parent::initContent();
		
		$days = array();
		for($i=1;$i<32;$i++)
			$days[sprintf("%02d", $i)] = sprintf("%02d", $i);
		
		$this->sisowbillink = new sisowbillink();
		
		$months = array();
		$months['01'] = $this->sisowbillink->l('January');
		$months['02'] = $this->sisowbillink->l('February');
		$months['03'] = $this->sisowbillink->l('March');
		$months['04'] = $this->sisowbillink->l('April');
		$months['05'] = $this->sisowbillink->l('May');
		$months['06'] = $this->sisowbillink->l('June');
		$months['07'] = $this->sisowbillink->l('July');
		$months['08'] = $this->sisowbillink->l('August');
		$months['09'] = $this->sisowbillink->l('September');
		$months['10'] = $this->sisowbillink->l('October');
		$months['11'] = $this->sisowbillink->l('November');
		$months['12'] = $this->sisowbillink->l('December');

		$year = array();
		for($i=(date("Y")-15);$i>(date("Y")-115);$i--)
			$year[$i] = $i;		
		
		$customer = new Customer($this->context->cart->id_customer);
		$gender = ($customer->id_gender == '1') ? 'M': 'F';
		$dob_year = substr($customer->birthday, 0, 4);
		$dob_month = substr($customer->birthday, 5,2);
		$dob_day = substr($customer->birthday, 8, 2);
		
		$dob = array(
			'day'	=> $dob_day,
			'month' => $dob_month,
			'year' 	=> $dob_year
		);
				
		$address = new Address($this->context->cart->id_address_invoice);
		$this->context->smarty->assign('phone', (!$address->phone) ? $address->phone_mobile : $address->phone );
		$this->context->smarty->assign('days', $days);
		$this->context->smarty->assign('months', $months);
		$this->context->smarty->assign('years', $year);
		$this->context->smarty->assign('dob', $dob);
		
		if(isset($customer->id_gender))
			$this->context->smarty->assign('gender', $gender);
		else
			$this->context->smarty->assign('gender', '');
			
		$this->context->smarty->assign('paymentcode', $this->module->paymentcode);
		$this->context->smarty->assign('paymentname', $this->module->name);

		if(Configuration::get('SISOW'.strtoupper($this->module->paymentcode).'_USEB2B') == 'yes')
			$this->setTemplate('payment_executionb2b.tpl');
		else
			$this->setTemplate('payment_execution.tpl');
	}
}
?>