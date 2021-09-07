<?php

/**
 * Callback Handling
 */
class ConcordPay_Payment_Block_Response extends Mage_Core_Block_Abstract
{
    /**
     * @return void
     * @throws Exception
     */
    protected function _toHtml()
    {
        $helper = Mage::helper('concordpay_payment');
        $model  = Mage::getModel('concordpay_payment/concordpay');
        $data   = json_decode(file_get_contents("php://input"), true);

        $state_after_pay    = $model->getConfigData('after_pay_status');
        $state_after_refund = $model->getConfigData('after_refund_status');

        $order = Mage::getModel('sales/order')->loadByIncrementId($data['orderReference']);

        // You can find the exceptions logs in the folder {Magento_Dir}/var/reports.
        if (!$order || !$order->getId()) {
            Mage::throwException(Mage::helper('concordpay_payment')->__('Order not found!'));
        }

        $operation_types = Mage::helper('concordpay_payment')->getOperationTypes();
        if (!isset($data['type']) || !in_array($data['type'], $operation_types, true)) {
            Mage::throwException(Mage::helper('concordpay_payment')->__('Unknown operation type!'));
        }

        if ($helper) {
            $sign = $helper->getResponseSignature($data);
            if (!isset($data['merchantSignature']) ||  $data['merchantSignature'] !== $sign) {
                Mage::throwException(Mage::helper('concordpay_payment')->__('Signature is incorrect or missing!'));
            }
        }

        if ($data['transactionStatus'] === ConcordPay_Payment_Helper_Data::ORDER_APPROVED) {
            if ($data['type'] === ConcordPay_Payment_Helper_Data::RESPONSE_TYPE_PAYMENT) {
                // Ordinary payment.
                $order->setStatus($state_after_pay);
            } elseif ($data['type'] === ConcordPay_Payment_Helper_Data::RESPONSE_TYPE_REVERSE) {
                // Refunded payment.
                $order->setStatus($state_after_refund);
            }
            $order->save();
        }
    }
}