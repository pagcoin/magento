<?php
class PagCoin_BTCPayment_Model_Standard extends Mage_Payment_Model_Method_Abstract
{ 
    
    protected $_code = 'BTCPayment';
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_apiToken = null;

    public function getOrderPlaceRedirectUrl()
    {
        $url = $this->getConfigData('redirecturl');
        
        if(!isset($this->_apiToken)){
           $this->_apiToken = Mage::getSingleton('checkout/session')->getData('apiToken');
        }
         
		return 'https://pagcoin.com' . $this->_apiToken;
    }
    
    public function initialize($paymentAction, $stateObject)
    {   
        parent::initialize($paymentAction, $stateObject);
        
        if($paymentAction != 'sale'){
            return $this;
        }
        
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT; // state now = 'pending_payment'
        $stateObject->setState($state); 
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
        
        try{
            $this->_customBeginPayment();
        }catch (Exception $e){
            Mage::log($e);    
            Mage::throwException($e->getMessage());
        }
        return $this;
    }
    
    protected  function _customBeginPayment(){
    	$apikey = Mage::helper('core')->decrypt($this->getConfigData('merchantkey'));
        
        $sessionCheckout = Mage::getSingleton('checkout/session');
        $quoteId = $sessionCheckout->getQuoteId();
    
        $sessionCheckout->setData('pagcoinQuoteId',$quoteId);
        
        $quote = Mage::getModel("sales/quote")->load($quoteId);
        
        $grandTotal = $quote->getData('grand_total');
        $billingData = $quote->getBillingAddress()->getData();
        $apiEmail = $billingData['email'];
        $apiOrderId = (str_pad($quoteId, 9,0,STR_PAD_LEFT));
        $storeName = Mage::app()->getStore()->getName();
        
        $oUrl = Mage::getModel('core/url');
        $apiRedirect = $oUrl->getUrl("/");
        
        $pagCoinUrl = "https://pagcoin.com/api/1/CriarInvoice/";
	
		$request = array(
			"apiKey" => $apikey, 
			"valorEmMoedaOriginal" => (float)$grandTotal, 
			"nomeProduto" => 'Carrinho de compras - ' . $storeName, 
			"idInterna" => $apiOrderId, 
			"email" => $apiEmail, 
			"redirectURL" => $apiRedirect
		);
		
		$jsonRequest = json_encode($request);
		
		$ch = curl_init($pagCoinUrl);
		$cabundle = dirname(__FILE__).'/ca-bundle.crt';
		
		curl_setopt( $ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonRequest);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt( $ch, CURLOPT_HEADER, 0);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt( $ch, CURLOPT_CAINFO, $cabundle);
		
		$redirectUrl = curl_exec($ch);
		$http_status = curl_getinfo($ch);


		curl_close($curl);
		
		if($http_status == 200){
            $token = $redirectUrl;
            $sessionCheckout->setData('apiToken',$redirectUrl);
        }else{
           	$msg = '(' . $http_status . ' ' . $redirectUrl . ') ';
           	
           	$sessionCheckout->addError('Ocorreu um erro ao criar a ordem de pagamento com bitcoins.');
           	Mage::throwException('erro: ' . $msg);
        }
        return $this;
    }
}