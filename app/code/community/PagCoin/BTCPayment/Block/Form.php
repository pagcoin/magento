<?php
class PagCoin_BTCPayment_Block_Form extends Mage_Payment_Block_Form
{
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('btcpayment/form.phtml');
	}
}