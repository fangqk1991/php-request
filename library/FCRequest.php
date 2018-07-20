<?php

namespace FC\Request;

use Exception;

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

    protected function urlForGet()
    {
        $params = $this->paramsForGet();
        return $this->addQueryParams($this->_url, $params);
    }

    protected function paramsForGet()
    {
        return $this->_params;
    }

    protected function urlForPost()
    {
        return $this->_url;
    }

    protected function paramsForPost()
    {
        return $this->_params;
    }

    public function get()
    {
        $url = $this->urlForGet();

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
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
        $url = $this->urlForPost();
        $params = $this->paramsForPost();

        if($this->requestType === self::kRequestJSON)
        {
            $postFields = json_encode($params, JSON_UNESCAPED_UNICODE);
            $headers = array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($postFields)
            );
        }
        else if($this->requestType === self::kRequestText)
        {
            $postFields = strval($params);
            $headers = array(
                'Content-Length: ' . strlen($postFields)
            );
        }
        else
        {
            $postFields = $params;
        }

        $curl = curl_init($url);
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

    public function download($targetPath)
    {
        $url = $this->urlForGet();

        if(is_dir($targetPath))
        {
            $dirPath = $targetPath;
            $fileName = basename($url);
        }
        else
        {
            $dirPath = dirname($targetPath);
            if(!is_dir($dirPath))
            {
                mkdir($dirPath, 0775, TRUE);
            }
            $fileName = basename($targetPath);
        }

        $targetPath = sprintf('%s/%s', $dirPath, $fileName);
        $tmpPath = $targetPath . '.tmp';

        if(file_exists($tmpPath))
            unlink($tmpPath);

        $fp = fopen($tmpPath, 'wb');

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);

        if(count($this->_customHeaders) > 0)
        {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_customHeaders);
        }

        if($this->_proxy)
        {
            curl_setopt($curl, CURLOPT_PROXY, $this->_proxy);
        }

        curl_exec($curl);
        $this->_httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        fclose($fp);

        if($this->isOK())
        {
            if(file_exists($targetPath))
                unlink($targetPath);
            rename($tmpPath, $targetPath);
        }
    }

    public function isOK()
    {
        return $this->_httpCode === 200;
    }

    public function getResponse()
    {
        return $this->_response;
    }

    protected function addQueryParams($url, $params)
    {
        if(empty($params))
        {
            return $url;
        }

        $parts = parse_url($url);
        $queryParams = array();

        if(isset($parts['query']))
        {
            parse_str($parts['query'], $queryParams);
        }

        $queryParams = array_merge($queryParams, $params);
        return sprintf('%s://%s%s?%s', $parts['scheme'], $parts['host'],
            $parts['path'], http_build_query($queryParams));
    }
}