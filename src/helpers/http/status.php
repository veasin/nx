<?php

namespace nx\helpers\http;
/**
 * @see https://datatracker.ietf.org/doc/html/rfc9110#name-status-codes
 * @see https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
 *
 */
final class status{
	/*
	Method
	get 获取 200 成功
	head 获取 无内容
	post 处理 创建 追加 201 已创建 206 一部分 304 未修改 416 范围不满足 303 已存在并重定向
	put 创建或替换 201 创建 200 替换 204 替换但不返回内容 409 冲突 415 替换时格式不同
	delete 删除 202 已接受 操作可能成功但可能未成功 204 成功无返回 200 成功返回一些信息
	options 获取可选项

	Request Context Fields 18.4
	期待
	Expect =      #expectation
	expectation = token [ "=" ( token / quoted-string ) parameters ]
	从
	From    = mailbox
	mailbox = <mailbox, see [RFC5322], Section 3.4>
	引用者
	Referer = absolute-URI / partial-URI
	挂载 https://developer.mozilla.org/zh-CN/docs/Web/HTTP/Headers/Trailer
	TE                 = #t-codings
	t-codings          = "trailers" / ( transfer-coding [ weight ] )
	transfer-coding    = token *( OWS ";" OWS transfer-parameter )
	transfer-parameter = token BWS "=" BWS ( token / quoted-string )
	用户代理
	User-Agent = product *( RWS ( product / comment ) )
	product         = token ["/" product-version]
	product-version = token
	允许
	Allow = #method
	位置 201 位置指向资源 3xx 重定向到资源(get <=303 Location:xxx)
	Location = URI-reference
	重试后
	Retry-After = HTTP-date / delay-seconds
	delay-seconds  = 1*DIGIT
	服务器 Server: CERN/3.0 libwww/2.17
	Server = product *( RWS ( product / comment ) )
	身份验证
		方案
		auth-scheme    = token
		参数
		token68        = 1*( ALPHA / DIGIT / "-" / "." / "_" / "~" / "+" / "/" ) *"="
		auth-param     = token BWS "=" BWS ( token / quoted-string )
		挑战与回应
		challenge   = auth-scheme [ 1*SP ( token68 / #auth-param ) ]
		凭证
		credentials = auth-scheme [ 1*SP ( token68 / #auth-param ) ]
		万维网认证
		WWW-Authenticate = #challenge
		授权
		Authorization = credentials
		身份验证信息
		Authentication-Info = #auth-param
	向代理验证客户端
		代理身份验证
		Proxy-Authenticate = #challenge
		代理授权
		Proxy-Authentication-Info = #auth-param
	内容协商
		质量
		weight = OWS ";" OWS "q=" qvalue
		qvalue = ( "0" [ "." 0*3DIGIT ] ) / ( "1" [ "." 0*3("0") ] )
		接受
		Accept = #( media-range [ weight ] )
		media-range    = ( "* /*" / ( type "/" "*" ) / ( type "/" subtype ) ) parameters
		接受字符集
		Accept-Charset = #( ( token / "*" ) [ weight ] )
		接受编码
		Accept-Encoding  = #( codings [ weight ] )
		codings          = content-coding / "identity" / "*"
		接受语言
		Accept-Language = #( language-range [ weight ] )
		language-range  = <language-range, see [RFC4647], Section 2.1>
		变化
		Vary = #( "*" / field-name )
	条件请求
		如果匹配 412 检查失败 2xx 成功
		If-Match = "*" / #entity-tag
		如果不匹配 412 检查失败 2xx 成功 304 未修改
		If-None-Match = "*" / #entity-tag
		如果修改自 304 未修改
		If-Modified-Since = HTTP-date
		如果未修改自 412 检查失败 2xx 成功
		If-Unmodified-Since = HTTP-date
		如果范围
		If-Range = entity-tag / HTTP-date
	范围请求
		range-unit       = token
		范围说明符
		ranges-specifier = range-unit "=" range-set
		range-set        = 1#range-spec
		range-spec       = int-range / suffix-range / other-range
		int-range     = first-pos "-" [ last-pos ]
		first-pos     = 1*DIGIT
		last-pos      = 1*DIGIT
		suffix-range  = "-" suffix-length
		suffix-length = 1*DIGIT
		other-range   = 1*( %x21-2B / %x2D-7E ); 1*(VCHAR excluding comma)
		字节范围
		bytes= 0-999, 4500-5499, -1000
		范围
		Range = ranges-specifier
		接受范围
		Accept-Ranges     = acceptable-ranges
		acceptable-ranges = 1#range-unit
		内容范围 Content-Range: bytes 42-1233/*
		Content-Range       = range-unit SP ( range-resp / unsatisfied-range )
		range-resp          = incl-range "/" ( complete-length / "*" )
		incl-range          = first-pos "-" last-pos
		unsatisfied-range   = "* /" complete-length
		complete-length     = 1*DIGIT


	 */
	public static array $Message = [
		//用于传达连接状态或请求进度的临时响应 在完成请求的操作并发送最终响应之前
		100 => "Continue", //[RFC9110, Section 15.2.1] 已接受部份，还需要后续再继续
		101 => "Switching Protocols", //[RFC9110, Section 15.2.2] 切换协议
		102 => "Processing", //[RFC2518]
		103 => "Early Hints", //[RFC8297]
		//客户的请求被成功接收、理解和接受
		200 => "OK", //[RFC9110, Section 15.3.1] 成功 get 内容 post 操作状态或内容 put delete 操作状态
		201 => "Created", //[RFC9110, Section 15.3.2] 操作已创建新资源 考虑添加 Content-Location
		202 => "Accepted", //[RFC9110, Section 15.3.3] 已接受请求但未完成处理 如异步时
		203 => "Non-Authoritative Information", //[RFC9110, Section 15.3.4] 如中继第三方接口？
		204 => "No Content", //[RFC9110, Section 15.3.5] 成功但无返回内容 用途如：保存 并可继续编辑
		205 => "Reset Content", //[RFC9110, Section 15.3.6] 成功并不返回内容同时重置用户界面 用途如：创建并继续创建新条目
		206 => "Partial Content", //[RFC9110, Section 15.3.7] 成功并返回部分内容 参见If-Range 和 Content-Range
		207 => "Multi-Status", //[RFC4918]
		208 => "Already Reported", //[RFC5842]
		226 => "IM Used", //[RFC3229]
		//用户代理需要采取进一步行动才能实现请求
		300 => "Multiple Choices", //[RFC9110, Section 15.4.1] 用户参与选择 返回选项
		301 => "Moved Permanently", //[RFC9110, Section 15.4.2] 永久移动到新地址 可改用 308
		302 => "Found", //[RFC9110, Section 15.4.3] 已找到（临时） 可改用307
		303 => "See Other", //[RFC9110, Section 15.4.4] 查看其他的，可能是 任何
		304 => "Not Modified", //[RFC9110, Section 15.4.5] 数据未修改或未变化
		305 => "Use Proxy", //[RFC9110, Section 15.4.6] 使用代理 废弃
		306 => "(Unused)", //[RFC9110, Section 15.4.7] 废弃
		307 => "Temporary Redirect", //[RFC9110, Section 15.4.8] 临时重定向
		308 => "Permanent Redirect", //[RFC9110, Section 15.4.9] 永久重定向
		//客户端似乎犯了错误
		400 => "Bad Request", //[RFC9110, Section 15.5.1] 错误请求，服务器不知道应该怎样处理 例如，格式错误的请求语法、无效的请求 消息框架或欺骗性请求路由
		401 => "Unauthorized", //[RFC9110, Section 15.5.2] 未经授权，可考虑包含诊断信息 生成 401 响应的服务器必须发送 WWW 身份验证标头字段 （第 11.6.1 节）
		402 => "Payment Required", //[RFC9110, Section 15.5.3] 需要付款 保留
		403 => "Forbidden", //[RFC9110, Section 15.5.4] 禁止访问(服务器理解请求，但拒绝满足它) 如权限不足？
		404 => "Not Found", //[RFC9110, Section 15.5.5] 未找到(未找到资源或不愿意返回资源)
		405 => "Method Not Allowed", //[RFC9110, Section 15.5.6] 方法不允许 存在资源但不接受method
		406 => "Not Acceptable", //[RFC9110, Section 15.5.7] 不可接受 参见 User-Agent 12.1
		407 => "Proxy Authentication Required", //[RFC9110, Section 15.5.8] 需要代理身份验证 类似 401
		408 => "Request Timeout", //[RFC9110, Section 15.5.9] 请求超时（服务器在时间内未收到完整的请求消息 它准备等待）
		409 => "Conflict", //[RFC9110, Section 15.5.10] 冲突（由于与目标的当前状态冲突而无法完成 资源） 如多次PUT请求
		410 => "Gone", //[RFC9110, Section 15.5.11] 消失（资源没了，已经确定了的，没确定用404）
		411 => "Length Required", //[RFC9110, Section 15.5.12] 需要长度 参见Content-Length
		412 => "Precondition Failed", //[RFC9110, Section 15.5.13] 前提条件失败（服务器拒绝处理请求）参见 IF 13
		413 => "Content Too Large", //[RFC9110, Section 15.5.14] 内容太大（服务器拒绝处理请求）可考虑添加 Retry-After
		414 => "URI Too Long", //[RFC9110, Section 15.5.15] URI 太长，服务器不愿意解释 如重定向的参数等
		415 => "Unsupported Media Type", //[RFC9110, Section 15.5.16] 不支持的媒体类型 参见 Content-Type 或 Content-Encoding
		416 => "Range Not Satisfiable", //[RFC9110, Section 15.5.17] 范围不满足 参见14.2
		417 => "Expectation Failed", //[RFC9110, Section 15.5.18] 预期失败 参见 Expect 10.1.1
		418 => "(Unused)", //[RFC9110, Section 15.5.19] 哈哈 coffee
		421 => "Misdirected Request", //[RFC9110, Section 15.5.20] 错误定向请求?
		422 => "Unprocessable Content", //[RFC9110, Section 15.5.21] 无法处理的内容(服务器了解但无法处理)
		423 => "Locked", //[RFC4918]
		424 => "Failed Dependency", //[RFC4918]
		425 => "Too Early", //[RFC8470]
		426 => "Upgrade Required", //[RFC9110, Section 15.5.22] 需要升级 参见 7.8 Upgrade: HTTP/3.0
		428 => "Precondition Required", //[RFC6585]
		429 => "Too Many Requests", //[RFC6585]
		431 => "Request Header Fields Too Large", //[RFC6585]
		451 => "Unavailable For Legal Reasons", //[RFC7725]
		//服务器知道它已出错或无法执行请求的方法
		500 => "Internal Server Error", //[RFC9110, Section 15.6.1] 内部服务器错误 未拦截异常或未知错误
		501 => "Not Implemented", //[RFC9110, Section 15.6.2] 未实现(服务器不支持满足请求所需的功能。 当服务器无法识别 请求方法，并且无法为任何资源支持它)
		502 => "Bad Gateway", //[RFC9110, Section 15.6.3] 错误的网关 如中继第三方接口？
		503 => "Service Unavailable", //[RFC9110, Section 15.6.4] 服务不可用(由于临时过载，服务器当前无法处理请求 或定期维护，这可能会在延迟一段时间后得到缓解)
		504 => "Gateway Timeout", //[RFC9110, Section 15.6.5] 网关超时 如中继第三方接口？
		505 => "HTTP Version Not Supported", //[RFC9110, Section 15.6.6] 不支持 HTTP 版本
		506 => "Variant Also Negotiates", //[RFC2295]
		507 => "Insufficient Storage", //[RFC4918]
		508 => "Loop Detected", //[RFC5842]
		510 => "Not Extended (OBSOLETED)", //[RFC2774][status-change-http-experiments-to-historic]
		511 => "Network Authentication Required", //[RFC6585]
	];
	public static function message(int $status): string{
		return "$status " . (self::$Message[$status] ?? '');
	}
}