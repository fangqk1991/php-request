<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FC\Request\FCRequest;

$request = new FCRequest();
$request->post('https://service.fangcha.me/api/test/http/test_post_json', array('a' => 1));
var_dump($request->response);

$request = new FCRequest();
$request->responseType = FCRequest::kResponseText;
//$request->setProxy('http://127.0.0.1:6152');
$request->get('https://www.instagram.com/p/BilxELIj3Xr/');
var_dump($request->response);