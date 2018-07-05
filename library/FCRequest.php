<?php

namespace FC\Request;

class FCRequest
{
    const kRequestForm = 0;
    const kRequestJSON = 1;

    const kResponseText = 0;
    const kResponseJSON = 1;

    public $requestType;
    public $responseType;

    public $httpCode;
    public $response;

    private $_proxy;

    public function __construct()
    {
        $this->requestType = self::kRequestJSON;
        $this->responseType = self::kResponseJSON;
    }

    public function setProxy($proxy)
    {
        $this->_proxy = $proxy;
    }

    public function get($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);

        if($this->_proxy)
        {
            curl_setopt($curl, CURLOPT_PROXY, $this->_proxy);
        }

        $response = curl_exec($curl);
        $this->httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($this->responseType === self::kResponseJSON)
        {
            $response = json_decode($response, TRUE);
        }

        $this->response = $response;
    }

    public function post($url, $params)
    {
        if($this->requestType === self::kRequestJSON)
        {
            $data_string = json_encode($params);
            $headers = array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string)
            );
        }
        else
        {
            $data_string = http_build_query($params);
            $headers = array(
                'Content-Type: application/x-www-form-urlencoded',
                'Content-Length: ' . strlen($data_string)
            );
        }

        $process = curl_init($url);
        curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($process);
        $this->httpCode = curl_getinfo($process, CURLINFO_HTTP_CODE);
        curl_close($process);

        if($this->responseType === self::kResponseJSON)
        {
            $response = json_decode($response, TRUE);
        }

        $this->response = $response;
    }

    public function isOK()
    {
        return $this->httpCode == 200;
    }
}