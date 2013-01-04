<?php
/**
 *
 *
 *
 */
class Juno_Barclays_ProcessController extends Mage_Core_Controller_Front_Action
{
	/**
     * Get singleton of Checkout Session Model.
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Send the user to Barclays.
     */
	public function sendAction()
	{
		try {
            $session = $this->_getCheckout();

            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($session->getLastRealOrderId());
            if (!$order->getId()) {
                Mage::throwException('No order for processing found');
            }
            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage::helper('barclays')->__('The customer was redirected to Barclays.')
            );
            $order->save();

            $session->setBarclaysQuoteId($session->getQuoteId());
            $session->setBarclaysRealOrderId($session->getLastRealOrderId());
            $session->getQuote()->setIsActive(false)->save();
            $session->clear();

            $this->loadLayout();
            $this->renderLayout();
        } catch (Exception $e){
            Mage::logException($e);
            parent::_redirect('checkout/cart');
        }
	}
	
	 /**
     * Successful transaction.
     */
	public function successAction()
	{
		$event = Mage::getModel('barclays/barclays')
					->setEventData($this->getRequest()->getParams());
        try {
            $quoteId = $event->successEvent();
            $this->_getCheckout()->setLastSuccessQuoteId($quoteId);
            $this->_redirect('checkout/onepage/success');
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getCheckout()->addError($e->getMessage());
        } catch(Exception $e) {
            Mage::logException($e);
        }
        $this->_redirect('checkout/cart');
	}
	
	 /**
     * Failed transaction.
     */
	public function abortAction()
	{
		$event = Mage::getModel('barclays/barclays')
                 ->setEventData($this->getRequest()->getParams());
        $message = $event->cancelEvent();
        
        // set quote to active
        $session = $this->_getCheckout();
        if ($quoteId = $session->getBarclaysQuoteId()) {
            $quote = Mage::getModel('sales/quote')->load($quoteId);
            if ($quote->getId()) {
                $quote->setIsActive(true)->save();
                $session->setQuoteId($quoteId);
            }
        }

        $session->addError($message);
        $this->_redirect('checkout/cart');
	}
}