<?php
class sisowepspaymentModuleFrontController extends ModuleFrontController
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
		$this->addJS("https://www.sisow.nl/Sisow/scripts/giro-eps.js");
		$this->addCSS("https://bankauswahl.giropay.de/eps/widget/v1/style.css");
		
		parent::initContent();
		
		$this->context->smarty->assign('paymentcode', $this->module->paymentcode);
		$this->context->smarty->assign('paymentname', $this->module->name);

		$this->setTemplate('payment_execution.tpl');
	}
}
?>