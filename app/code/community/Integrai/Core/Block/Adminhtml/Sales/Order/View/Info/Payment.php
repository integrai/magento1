<?php
class Integrai_Core_Block_Adminhtml_Sales_Order_View_Info_Payment extends Mage_Core_Block_Template {

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function getPaymentResponse() {
        $order = $this->getOrder();
        $paymentAdditionalInformation = $order->getPayment()->getData('additional_information');

        $marketplace_data = array();
        $payments_data = array();
        $marketplace = array_filter((array) $paymentAdditionalInformation['marketplace']);
        $payments = array_filter((array) $paymentAdditionalInformation['payments']);

        if (isset($marketplace) && count($marketplace) > 0) {
            $name = !empty($marketplace['name']) ? $marketplace['name'] : '';
            $order_id = !empty($marketplace['order_id']) ? $marketplace['order_id'] : '';
            $created_at = !empty($marketplace['created_at']) ? date_format(date_create($marketplace['created_at']), 'd/m/Y H:i:s') : '';
            $updated_at = !empty($marketplace['updated_at']) ? date_format(date_create($marketplace['updated_at']), 'd/m/Y H:i:s') : '';

            $marketplace_data = array(
                'Criado por' => $name,
                'Nº Pedido Marketplace' => $order_id,
                'Data criação do pedido no marketplace' => $created_at,
                'Data atualização do pedido no marketplace' => $updated_at
            );
        }

        if (isset($payments) && count($payments) > 0) {
            foreach ($payments as $payment) {
                $method = !empty($payment['method']) ? $payment['method'] : '';
                $module_name = !empty($payment['module_name']) ? $payment['module_name'] : '';
                $value = !empty($payment['value']) ? 'R$' . number_format($payment['value'],2,",",".") : '';
                $transaction_id = !empty($payment['transaction_id']) ? $payment['transaction_id'] : '';
                $date_approved = !empty($payment['date_approved']) ? date_format(date_create($payment['date_approved']), 'd/m/Y H:i:s') : '';
                $installments = !empty($payment['installments']) ? $payment['installments'] . 'x' : '';
                $boleto = !empty($payment['boleto']) ? (array) $payment['boleto']: '';
                $card = !empty($payment['card']) ? (array) $payment['card']: '';
                $pix = !empty($payment['pix']) ? (array) $payment['pix']: '';

                $card_data = '';
                if (isset($card) && is_array($card)) {
                    $card_number = !empty($card['last_four_digits']) ? $card['last_four_digits'] : '';
                    $card_brand = !empty($card['brand']) ? $card['brand'] : '';
                    $card_holder = !empty($card['holder']) ? $card['holder'] : '';
                    $expiration_month = !empty($card['expiration_month']) ? $card['expiration_month'] : '';
                    $expiration_year = !empty($card['expiration_year']) ? $card['expiration_year'] : '';
                    $expiration = implode('/', array_filter(array($expiration_month, $expiration_year)));

                    $card_data = array(
                        'Número do cartão' => "**** **** **** $card_number",
                        'Nome do titular' => $card_holder,
                        'Expiração' => $expiration,
                        'Bandeira' => strtoupper( $card_brand )
                    );
                }

                $payments_data[] = array(
                    'Método' => $method,
                    'Processado por' => $module_name,
                    'Identificação da transação' => $transaction_id,
                    'Data de pagamento' => $date_approved,
                    'Nº de Parcelas' => $installments,
                    'Valor cobrado' => $value,
                    'boleto' => $boleto,
                    'card' => $card_data,
                    'pix' => $pix,
                );
            }
        }

        return array(
            'marketplace_data' => $marketplace_data,
            'payments' => $payments_data,
        );
    }

    private function getOrder() {
        if (is_null($this->order)) {
            if (Mage::registry('current_order')) {
                $order = Mage::registry('current_order');
            }
            elseif (Mage::registry('order')) {
                $order = Mage::registry('order');
            }
            else {
                $order = new Varien_Object();
            }
            $this->order = $order;
        }
        return $this->order;
    }
}