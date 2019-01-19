<?php

namespace FC\Request;

class FCTypicalRequest extends FCRequest
{
    public $data;
    public $error;

    protected function loadDefaultSettings()
    {
        $this->requestType = self::kRequestJSON;
        $this->responseType = self::kResponseJSON;
    }

    public function get()
    {
        parent::get();
        $this->proceedResponse();

        return $this->_response;
    }

    public function post()
    {
        parent::post();
        $this->proceedResponse();

        return $this->_response;
    }

    private function proceedResponse()
    {
        if(is_array($this->_response))
        {
            if(isset($this->_response['data']))
            {
                $this->data = $this->_response['data'];
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
                    $error = $this->_response['error'];
                }

                $this->error = $error;
            }
        }
    }
}