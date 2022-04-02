<?php
namespace nx\helpers\http;

final class status{
	static public array $Message = [//请求已被接受，需要继续处理
		100 => "Continue",
		101 => "Switching Protocols",
		102 => "Processing",
		//请求已成功被服务器接收、理解、并接受
		200 => "OK",
		201 => "Created",//POST PUT PATCH 成功  新的资源已经依据请求的需要而建立
		202 => "Accepted",//异步已添加到队列
		203 => "Non-Authoritative Information",//
		204 => "No Content",//DELETE 成功 禁止包含任何消息体
		205 => "Reset Content",//禁止包含任何消息体
		206 => "Partial Content",//已经成功处理了部分 GET 请求
		207 => "Multi-Status",//可能依照之前子请求数量的不同，包含一系列独立的响应代码
		//需要客户端采取进一步的操作才能完成请求
		300 => "Multiple Choices",
		301 => "Moved Permanently",//被请求的资源已永久移动到新位置
		302 => "Found",//请求的资源临时从不同的 URI响应请求 临时
		303 => "See Other",//对应当前请求的响应可以在另一个 URI 上被找到，而且客户端应当采用 GET 的方式访问那个资源
		304 => "Not Modified",//禁止包含消息体
		305 => "Use Proxy",//被请求的资源必须通过指定的代理才能被访问
		306 => "Switch Proxy",//废弃
		307 => "Temporary Redirect",//请求的资源临时从不同的URI 响应请求
		//客户端看起来可能发生了错误，妨碍了服务器的处理
		400 => "Bad Request",//POST PUT PATCH 无效操作 结果幂等 请求参数有误
		401 => "Unauthorized",//无权限 令牌 用户名 密码错误
		402 => "Payment Required",//需付费
		403 => "Forbidden",//用户得到授权 但禁止访问
		404 => "Not Found", //不存在
		405 => "Method Not Allowed", //方法不被允许
		406 => "Not Acceptable",//请求格式无效
		407 => "Proxy Authentication Required",//与401响应类似，只不过客户端必须在代理服务器上进行身份验证
		408 => "Request Timeout",//请求超时
		409 => "Conflict",//指令冲突
		410 => "Gone",//永久删除
		411 => "Length Required",//服务器拒绝在没有定义 Content-Length 头的情况下接受请求
		412 => "Precondition Failed",//服务器在验证在请求的头字段中给出先决条件时，没能满足其中的一个或多个 Token in header
		413 => "Request Entity Too Large",//请求实体过大
		414 => "Request-URI Too Long",//请求地址过长
		415 => "Unsupported Media Type",//不支持的请求格式
		416 => "Requested Range Not Satisfiable",//请求范围超出
		417 => "Expectation Failed",//预期内容错误
		421 => "There are too many connections from your internet address",
		422 => "Unprocessable Entity", //POST PUT PATCH 创建时验证失败 请求格式正确，但是由于含有语义错误
		423 => "Locked", //当前资源被锁定
		424 => "Failed Dependency", //由于之前的某个请求发生的错误，导致当前请求失败，例如 PROPPATCH
		425 => "Unordered Collection",
		426 => "Upgrade Required", //客户端应当切换到TLS/1.0
		429 => "Too Many Requests", //请求数过多
		431 => "Request Header Fields Too Large", //请求头字段过大
		449 => "Retry With", //由微软扩展，代表请求应当在执行完适当的操作后进行重试
		451 => "Unavailable For Legal Reasons", //该请求因法律原因不可用
		//服务器在处理请求的过程中有错误或者异常状态发生
		500 => "Internal Server Error ", //服务器错误 用户无法判断是否成功
		501 => "Not Implemented ",
		502 => "Bad Gateway ",
		503 => "Service Unavailable ",
		504 => "Gateway Timeout ",
		505 => "HTTP Version Not Supported ",
		506 => "Variant Also Negotiates ",
		507 => "Insufficient Storage ",
		509 => "Bandwidth Limit Exceeded ",
		510 => "Not Extended ",
		600 => "Unparseable Response Headers ",//源站没有返回响应头部，只返回实体内容
	];
}