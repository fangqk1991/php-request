<?php

namespace FC\Request;

class FCRequest
{
    const kRequestForm = 0;
    const kRequestJSON = 1;
    const kRequestText = 2;

    const kResponseText = 0;
    const kResponseJSON = 1;

    public $requestType;
    public $responseType;

    protected $_httpCode;
    protected $_response;

    protected $_proxy;

    protected $_url;
    protected $_params;

    protected $_sslVerify;
    protected $_rsaCertPem;
    protected $_rsaPrivatePem;

    protected $_customHeaders;

    public function __construct($url, $params = array())
    {
        $this->_url = $url;
        $this->_params = $params;
        $this->_customHeaders = array();

        $this->loadDefaultSettings();
    }

    protected function loadDefaultSettings()
    {
        $this->requestType = self::kRequestJSON;
        $this->responseType = self::kResponseJSON;
    }

    public function setProxy($proxy)
    {
        $this->_proxy = $proxy;
    }

    public function setCert($rsaCertPem, $rsaPrivatePem)
    {
        $this->_rsaCertPem = $rsaCertPem;
        $this->_rsaPrivatePem = $rsaPrivatePem;
    }

    public function setSSLVerify($bool)
    {
        $this->_sslVerify = $bool;
    }

    public function addCustomHeader($value)
    {
        array_push($this->_customHeaders, $value);
    }

    public function get()
    {
        $url = $this->_url;

        if(!empty($this->_params))
        {
            $url = $this->urlAppendQuery($url, http_build_query($this->_params));
        }
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);

        if(count($this->_customHeaders) > 0)
        {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_customHeaders);
        }

        if($this->_proxy)
        {
            curl_setopt($curl, CURLOPT_PROXY, $this->_proxy);
        }

        $response = curl_exec($curl);
        $this->_httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if($this->responseType === self::kResponseJSON)
        {
            $response = json_decode($response, TRUE);
        }

        $this->_response = $response;
    }

    public function post()
    {
        if($this->requestType === self::kRequestJSON)
        {
            $postFields = json_encode($this->_params, JSON_UNESCAPED_UNICODE);
            $headers = array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($postFields)
            );
        }
        else if($this->requestType === self::kRequestText)
        {
            $postFields = strval($this->_params);
            $headers = array(
                'Content-Length: ' . strlen($postFields)
            );
        }
        else
        {
            $postFields = $this->_params;
        }

        $curl = curl_init($this->_url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);

        if(!empty($headers))
        {
            $headers = array_merge($headers, $this->_customHeaders);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        if($this->_proxy)
        {
            curl_setopt($curl, CURLOPT_PROXY, $this->_proxy);
        }

        if($this->_sslVerify)
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 2);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        }

        if($this->_rsaCertPem && $this->_rsaPrivatePem)
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 2);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLCERT, $this->_rsaCertPem);
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLKEY, $this->_rsaPrivatePem);
        }

        $response = curl_exec($curl);
        $this->_httpCode = intval(curl_getinfo($curl, CURLINFO_HTTP_CODE));
        curl_close($curl);

        if($this->responseType === self::kResponseJSON)
        {
            $response = json_decode($response, TRUE);
        }

        $this->_response = $response;
    }

    public function isOK()
    {
        return $this->_httpCode === 200;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    protected function urlAppendQuery($url, $addition)
    {
        $queryIndex = strpos($url, '?');

        if($queryIndex === FALSE)
        {
            return sprintf('%s?%s', $url, $addition);
        }
        else if($queryIndex + 1 === strlen($url))
        {
            return sprintf('%s%s', $url, $addition);
        }

        return sprintf('%s&%s', $url, $addition);
    }
}