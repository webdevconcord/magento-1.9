<?php

class ConcordPay_Payment_Model_Concordpay extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var string
     */
    protected $_code = 'concordpay_payment';

    /**
     * @var string
     */
    protected $_formBlockType = 'concordpay_payment/form';

    /**
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('concordpay_payment/redirect', array('_secure' => true));
    }

    /**
     * @return array
     */
    public function getFormFields()
    {
        $order_id = $this->getCheckout()->getLastRealOrderId();
        $order    = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $amount   = round($order->getGrandTotal(), 2);

        $description = Mage::helper('concordpay_payment')->__('Payment by card on the site') . ' ' .
            Mage::getBaseUrl() . ', ' . $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname() . ', ' .
            $order->getBillingAddress()->getTelephone();

        $approve_url  = Mage::getUrl('checkout/onepage/success', array('_secure' => true));
        $failure_url  = Mage::getUrl('checkout/onepage/failure', array('_secure' => true));
        $callback_url = Mage::getUrl('concordpay/response', array('_secure' => true));

        $fields = array(
            'operation'    => 'Purchase',
            'merchant_id'  => $this->getConfigData('merchant'),
            'order_id'     => $order_id,
            'amount'       => $amount,
            'currency_iso' => $order->getOrderCurrencyCode(),
            'description'  => $description,
            'add_params'   => [],
            'approve_url'  => $approve_url,
            'decline_url'  => $failure_url,
            'cancel_url'   => $failure_url,
            'callback_url' => $callback_url,
            // Statistics.
            'client_first_name' => $order->getCustomerFirstname() ?? '',
            'client_last_name'  => $order->getCustomerLastname() ?? '',
            'email'             => $order->getCustomerEmail() ?? '',
            'phone'             => $order->getBillingAddress()->getTelephone() ?? '',
        );

        $cartItems = $order->getAllVisibleItems();

        $productNames  = array();
        $productQty    = array();
        $productPrices = array();

        foreach ($cartItems as $_item) {
            $productNames[]  = $_item->getName();
            $productPrices[] = round($_item->getPrice(), 2);
            $productQty[]    = (int)$_item->getQtyOrdered();
        }

        $fields['signature'] = Mage::helper('concordpay_payment')->getRequestSignature($fields);

        $params = array(
            'button' => $this->getButton(),
            'fields' => $fields,
        );

        return $params;
    }

    /**
     * @return Mage_Core_Model_Abstract|null
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @return string
     */
    public function getButton()
    {
        $button = "<div style='position:absolute; top:50%; left:50%; margin:-40px 0 0 -60px; '>" .
            "</div>" .
            "<script type=\"text/javascript\">
                setTimeout( subform, 100 );
                function subform(){ document.getElementById('ConcordPayForm').submit(); }
            </script>";

        return $button;
    }
}