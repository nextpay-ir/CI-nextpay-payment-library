<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Nextpay
{
    private $api_key;
    private $trans_id;
    private $error;
    private $order_id;
    private $url;

    private $token_url = 'https://api.nextpay.org/gateway/token.wsdl';
    private $verify_url = 'https://api.nextpay.org/gateway/verify.wsdl';
    private $payment_url = 'https://api.nextpay.org/gateway/payment/';

    public function __construct($params)
    {
        $this->api_key = $params['api_key'];
    }

    public function request($amount, $callback, $order_id)
    {
      if (!$order_id)
        $order_id = time();

      $client = new SoapClient($this->token_url, array('encoding' => 'UTF-8'));
      $result = $client->TokenGenerator([
        'api_key' => $this->api_key,
        'amount' => $amount,
        'order_id' => $order_id,
        'callback_uri' => $callback,
      ]);
      $result = $result->TokenGeneratorResult;


      if ($result->code !== -1) {
        $this->error = $result->code ;
        return false;
      }


        $this->trans_id = $result->trans_id;
        $this->url = $this->$payment_url.$this->trans_id;

        return true;
    }

    public function redirect()
    {
        if (!function_exists('redirect')) {
            $CI = &get_instance();
            $CI->load->helper('url');
        }

        redirect($this->url);
    }

    public function verify($amount, $trans_id, $order_id)
    {

      $client = new SoapClient($this->verify_url, array('encoding' => 'UTF-8'));
      $result = $client->PaymentVerification(
        [
            'api_key' => $this->api_key,
            'trans_id' => $trans_id,
            'order_id' => $order_id ,
            'amount' => $amount,
        ]
      );

      $result = $result->PaymentVerificationResult;


        if ($result->code !== 0) {
            $this->error = $result->code;

            return false;
        }


        return true;
    }

    public function get_transaction()
    {
        return $this->trans_id;
    }

    public function get_error()
    {
        return $this->error;
    }
}
