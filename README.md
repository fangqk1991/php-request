# 简介
这是一个基于系统方法封装的网络请求框架，只支持同步请求。

### 依赖
* PHP 5.3+
* [Composer](https://getcomposer.org)

### 安装
编辑 `composer.json`，将 `fang/php-request` 加入其中

```
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/fangqk1991/php-request"
    }
  ],
  ...
  ...
  "require": {
    "fang/php-request": "dev-master"
  }
}

```

执行命令

```
composer install
```

### 使用
#### FCRequest
```
// 请求、响应内容格式
public $requestType;
public $responseType;

// 初始化方法
public function __construct($url, $params = array());
    
// 常用配置
public function setProxy($proxy);
public function setCert($rsaCertPem, $rsaPrivatePem);
public function setSSLVerify($bool);
public function addCustomHeader($value);

// 常用请求方式
public function get();
public function post();
public function download($targetPath);
```

* `FCRequest` 请求与响应的内容格式默认均采用 `application/json` 格式，内容格式可通过 `requestType` 和 `responseType` 进行设定。

### POST
```
// POST application/json
$params = ['my_num' => 123, 'my_str' => 'sss'];
$request = new FCRequest($url, $params);
$request->requestType = FCRequest::kRequestJSON;
$response = $request->post();
var_dump($response);
```

```
// POST application/x-www-form-urlencoded
$params = ['my_num' => 123, 'my_str' => 'sss'];
$request = new FCRequest($url, $params);
$request->requestType = FCRequest::kRequestForm;
$response = $request->post();
```

```
// POST text
$request = new FCRequest($url, 'xxxxx');
$request->requestType = FCRequest::kRequestText;
$response = $request->post();
var_dump($response);
```

### Error

```
$request = new FCRequest($url);
$request->post();
var_dump($request->isOK(), $request->getCode());
```

### Get 请求
```
$request = new FCRequest($url);
$response = $request->get();
var_dump($response);
```

### Download
```
$request = new FCRequest($url);
$request->download(__DIR__ . '/../run.local/');
```

#### FCTypicalRequest
`FCTypicalRequest` 在 `FCRequest` 的基础上，将 Response 锁定为 JSON 格式

正确返回时，一定包含 `data` 字段，其值可以是任意合法类型。

```
{
    "data": "字符串|数字|字典|数组|空值"
}
```

错误信息返回时，一定包含 `error` 字段，`error` 中包含 `code` 和 `msg`；一定不含 `data` 字段，其格式为

```
{
    "error": {
        "code": -1,
        "msg": "some error message"
    }
}
```

这样，返回正确的情况下，回调将收到 `data` 字段下的内容；通常，这是业务真正想要的数据

```
// FCTypicalRequest
$params = ['my_num' => 123, 'my_str' => 'sss'];
$request = new FCTypicalRequest($url, $params);
$response = $request->post();
var_dump($response);
```

