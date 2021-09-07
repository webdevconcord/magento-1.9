<?php

class ConcordPay_Payment_RedirectController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var
     */
    protected $_order;

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->getResponse()
            ->setHeader('Content-type', 'text/html; charset=utf8')
            ->setBody(
                $this->getLayout()->createBlock('concordpay_payment/redirect')->toHtml()
            );
    }

    /**
     * @return void
     */
    public function successAction()
    {
        if ($this->getRequest()->isPost()) {
            Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
            $this->_redirect('checkout/onepage/success', array('_secure' => true));
        }

    }

    protected function _expireAjax()
    {
        if (!Mage::getSingleton('concordpay_payment/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
            die();
        }
    }

}
