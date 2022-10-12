<?php

namespace App\Helper;

use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class Paypal
{
    protected $accept_url = ''; //支付成功和取消交易的跳转地址
    protected $currency = 'USD';//货币单位

    protected $PayPal;

    public function __construct()
    {
        $this->PayPal = new ApiContext(
            new OAuthTokenCredential(
                config('pay.paypal.client_id'),
                config('pay.paypal.secret')
            )
        );

        $this->accept_url = route('api.pay.courseCallback');

        if (config('pay.paypal.mode') == 'live') {
            $this->PayPal->setConfig(
                [
                    'mode' => 'live',
                ]
            );
        }
    }

    public function pay($data)
    {
        $product = $data['product'];
        $price = $data['price'];
        $shipping = 0;
        $description = $data['description'] ?? '';
        $total = $price + $shipping;//总价

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName($product)->setCurrency($this->currency)->setQuantity(1)->setPrice($price);

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $details = new Details();
        $details->setShipping($shipping)->setSubtotal($price);

        $amount = new Amount();
        $amount->setCurrency($this->currency)->setTotal($total)->setDetails($details);

        $transaction = new Transaction();
        $transaction->setAmount($amount)->setItemList($itemList)->setDescription($description)->setInvoiceNumber(uniqid());

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->accept_url . '?success=true')->setCancelUrl($this->accept_url . '/?success=false');

        $payment = new Payment();
        $payment->setIntent('sale')->setPayer($payer)->setRedirectUrls($redirectUrls)->setTransactions([$transaction]);

        try {
            $payment->create($this->PayPal);
        } catch (PayPalConnectionException $e) {
            $rtn = [
                'success' => false,
                'msg' => $e->getData()
            ];
            return $rtn;
        }

        $rtn = [
            'success' => true,
            'approval_url' => $payment->getApprovalLink(),
            'pay_id' => $payment->getId()
        ];

        return $rtn;
    }

    public function callback($param)
    {
        try {
            $payment = Payment::get($param['paymentId'], $this->PayPal);
            $execute = new PaymentExecution();
            $execute->setPayerId($param['PayerID']);
            $payment->execute($execute, $this->PayPal);

        } catch (\Throwable $e) {
            $rtn = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
            return $rtn;
        }
        $result = $payment->toArray();
        if (!empty($result['state']) && $result['state'] === 'approved') {
            $rtn = [
                'success' => true,
                'total' => $result['transactions'][0]['amount']['total'],
                'payer_info' => $result['payer']['payer_info'],
            ];
        }

        return $rtn;
    }

}