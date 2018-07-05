<?php

namespace FC\Request;

class FCTypicalRequest extends FCRequest
{
    public $data;
    public $error;

    private function proceedResponse()
    {
        if(is_array($this->response))
        {
            if(isset($this->response['data']))
            {
                $this->data = $this->response['data'];
            }
            else
            {
                $error = array(
                    'code' => -1,
                    'msg' => "It isn't a typical response!"
                );

                if(isset($this->error) && is_array($this->error)
                    && isset($this->error['code']) && isset($this->error['msg']))
                {
                    $error = $this->response['error'];
                }

                $this->error = $error;
            }
        }
    }

    public function get($url)
    {
        $this->responseType = self::kResponseJSON;
        parent::get($url);
        $this->proceedResponse();
    }

    public function post($url, $params)
    {
        $this->responseType = self::kResponseJSON;
        parent::post($url, $params);
        $this->proceedResponse();
    }
}