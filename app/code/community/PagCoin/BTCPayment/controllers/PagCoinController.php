<?php
class PagCoin_BTCPayment_PagCoinController extends Mage_Core_Controller_Front_Action
{
	public function  IPNAction()
    {
		$headers = apache_request_headers();
	
		if(!isset($headers["EnderecoPagCoin"]) || !isset($headers["AssinaturaPagCoin"])){
			die("Header invalido.");
		}
		
		$postdata = file_get_contents("php://input");
		$apikey = Mage::helper('core')->decrypt(Mage::getStoreConfig('payment/BTCPayment/merchantkey'));
		
		$signature = hash_hmac('sha256', $headers["EnderecoPagCoin"].$postdata, $apikey);
		
		if($signature != $headers["AssinaturaPagCoin"]){
			die("Assinatura não confere.");
		}
		
		$fields = json_decode($postdata);
		
		if ($fields["statusPagamento"] == 'confirmado'){
			$quote = Mage::getModel('sales/quote')->load($fields["idInterna"]);
			$order_id = $quote->getReservedOrderId();
			
			$order = Mage::getModel('sales/order')->load($order_id, 'increment_id');
			$payment = $order->getPayment();
			
			$this->_createInvoice($order);
			$order->setState(Mage_Sales_Model_Order::STATE_NEW ,true,$msg,false);
			$order->save();
		}
		
		echo '200 OK';
    }
	
	protected function _createInvoice($orderObj)
    {
        if (!$orderObj->canInvoice()) {
            return false;
        }
        $invoice = $orderObj->prepareInvoice();
        $invoice->register();
        if($invoice->canCapture()){
            $invoice->capture();
        }
        $invoice->save();
        $orderObj->addRelatedObject($invoice);
        return $invoice;
    }
}
?>
