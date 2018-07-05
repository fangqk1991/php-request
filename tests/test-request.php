<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FC\Request\FCRequest;

$request = new FCRequest('https://service.fangcha.me/api/test/http/test_post_json', array('a' => 1));
$request->post();
var_dump($request->getResponse());

$request = new FCRequest('https://www.instagram.com/p/BilxELIj3Xr/');
$request->responseType = FCRequest::kResponseText;
$request->setProxy('http://127.0.0.1:6152');
$request->get();
var_dump($request->getResponse());