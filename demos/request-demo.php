<?php

require_once __DIR__ . '/../vendor/autoload.php';

use FC\Request\FCRequest;
use FC\Request\FCTypicalRequest;

$url = 'https://service.fangcha.me/api/test/http/test_post_json';

$params = ['my_num' => 123, 'my_str' => 'sss'];
$request = new FCRequest($url, $params);
$request->requestType = FCRequest::kRequestJSON;
$response = $request->post();
var_dump($response);

$params = ['my_num' => 123, 'my_str' => 'sss'];
$request = new FCRequest($url, $params);
$request->requestType = FCRequest::kRequestForm;
$response = $request->post();
var_dump($response);

$request = new FCRequest($url, 'xxxxx');
$request->requestType = FCRequest::kRequestText;
$response = $request->post();
var_dump($response);

$url = 'https://service.fangcha.me/api/test/http/test_code';
$request = new FCRequest($url);
$request->post();
var_dump($request->isOK(), $request->getCode());

$url = 'https://cdn.fangcha.me/static/files/demo.json';
$request = new FCRequest($url);
$response = $request->get();
var_dump($response);

$url = 'https://service.fangcha.me/api/test/http/test_post_json';
$params = ['my_num' => 123, 'my_str' => 'sss'];
$request = new FCTypicalRequest($url, $params);
$response = $request->post();
var_dump($response);

$url = 'https://cdn.fangcha.me/static/svg/alert.svg';
$request = new FCRequest($url);
$request->download(__DIR__ . '/../run.local/');
