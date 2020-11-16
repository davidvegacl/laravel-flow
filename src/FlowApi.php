<?php

namespace DavidVegaCl\LaravelFlow;

use Exception;

class FlowApi
{
    protected $api_url;
    protected $api_key;
    protected $secret_key;

    public function __construct($api_key = null, $secret_key = null)
    {
        if (!empty($api_key)) {
            $this->api_key = $api_key;
        } else {
            $this->api_key = config('laravelflow.api_key', null);
        }

        if (!empty($secret_key)) {
            $this->secret_key = $secret_key;
        } else {
            $this->secret_key = config('laravelflow.secret_key', null);
        }

        $this->api_url = config('laravelflow.api_url', 'https://sandbox.flow.cl/api');
    }

    /**
     * Funcion que invoca un servicio del Api de Flow.
     *
     * @param string $service Nombre del servicio a ser invocado
     * @param array  $params  datos a ser enviados
     * @param string $method  metodo http a utilizar
     *
     * @return string en formato JSON
     *
     * @throws Exception
     */
    public function send($service, $params, $method = 'GET')
    {
        $method = strtoupper($method);
        $url = $this->api_url.'/'.$service;
        $params = ['apiKey' => $this->api_key] + $params;
        $params['s'] = $this->sign($params);
        if ($method == 'GET') {
            $response = $this->httpGet($url, $params);
        } else {
            $response = $this->httpPost($url, $params);
        }

        if (!empty($response['info'])) {
            $code = $response['info']['http_code'];
            if (!in_array($code, ['200', '400', '401'])) {
                throw new Exception('Unexpected error HTTP_CODE: '.$code, $code);
            }
        }

        return json_decode($response['output'], true);
    }

    /**
     * Funcion para setear el apiKey y secretKey y no usar los de la configuracion.
     */
    public function setKeys($api_key, $secret_key)
    {
        $this->api_key = $api_key;
        $this->secret_key = $secret_key;
    }

    /**
     * Funcion que firma los parametros.
     *
     * @param array $params Parametros a firmar
     *
     * @return string de firma
     *
     * @throws Exception
     */
    private function sign($params)
    {
        $keys = array_keys($params);
        sort($keys);
        $toSign = '';
        foreach ($keys as $key) {
            $toSign .= $key.$params[$key];
        }
        if (!function_exists('hash_hmac')) {
            throw new Exception('Function hash_hmac not exist', 1);
        }

        return hash_hmac('sha256', $toSign, $this->secret_key);
    }

    /**
     * Funcion que hace el llamado via http GET.
     *
     * @param string $url    url a invocar
     * @param array  $params los datos a enviar
     *
     * @return array el resultado de la llamada
     *
     * @throws Exception
     */
    private function httpGet($url, $params)
    {
        $url = $url.'?'.http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        if ($output === false) {
            $error = curl_error($ch);
            throw new Exception($error, 1);
        }
        $info = curl_getinfo($ch);
        curl_close($ch);

        return ['output' => $output, 'info' => $info];
    }

    /**
     * Funcion que hace el llamado via http POST.
     *
     * @param string $url    url a invocar
     * @param array  $params los datos a enviar
     *
     * @return array el resultado de la llamada
     *
     * @throws Exception
     */
    private function httpPost($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $output = curl_exec($ch);
        if ($output === false) {
            $error = curl_error($ch);
            throw new Exception($error, 1);
        }
        $info = curl_getinfo($ch);
        curl_close($ch);

        return ['output' => $output, 'info' => $info];
    }

    /**
     * Crea una orden de pago
     * https://sandbox.flow.cl/docs/api.html#tag/payment/paths/~1payment~1create/post.
     */
    public function paymentCreate($params = [])
    {
        $service = 'payment/create';

        if (empty($params['commerceOrder'])) {
            throw new Exception('No commerceOrder', 1);
        }
        if (empty($params['subject'])) {
            throw new Exception('No subject', 1);
        }
        if (empty($params['subject'])) {
            throw new Exception('No subject', 1);
        }
        if (empty($params['amount'])) {
            throw new Exception('No amount', 1);
        }
        if (empty($params['email'])) {
            throw new Exception('No email', 1);
        }
        if (empty($params['urlConfirmation'])) {
            throw new Exception('No urlConfirmation', 1);
        }
        if (empty($params['urlReturn'])) {
            throw new Exception('No urlReturn', 1);
        }

        $response = $this->send($service, $params, 'POST');

        if (empty($response['url']) || empty($response['token'])) {
            throw new Exception('No url / token', 1);
        }

        return [
            'url' => $response['url'],
            'token' => $response['token'],
            'redirect' => $response['url'].'?token='.$response['token'],
        ];
    }

    /**
     * Obtiene estado de una orden de pago
     * https://sandbox.flow.cl/docs/api.html#tag/payment/paths/~1payment~1getStatus/get.
     *
     * @return array
     */
    public function paymentGetStatus($token = null)
    {
        $service = 'payment/getStatus';

        if (empty($token)) {
            throw new Exception('No token', 1);
        }

        $params = [
            'token' => $token,
        ];

        return $this->send($service, $params, 'GET');
    }
}
