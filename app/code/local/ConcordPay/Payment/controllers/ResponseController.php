<?php

class ConcordPay_Payment_ResponseController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return void
     */
    public function indexAction()
    {
        $this->getResponse()
            ->setHeader('Content-type', 'text/html; charset=utf8')
            ->setBody(
                $this->getLayout()->createBlock('concordpay_payment/response')->toHtml()
            );
    }
}
