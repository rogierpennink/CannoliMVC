<?php
namespace Cannoli\Framework\Core\Net;

final class HttpStatus
{
	// Status OK (200) range
	const OK							= 200;
	const CREATED 						= 201;
	const ACCEPTED 						= 202;
	const NON_AUTHORITIVE_INFORMATION	= 203;
	const NO_CONTENT 					= 204;
	const RESET_CONTENT 				= 205;
	const PARTIAL_CONTENT 				= 206;

	// Status client error range
	const BAD_REQUEST 					= 400;
	const UNAUTHORIZED 					= 401;
	const PAYMENT_REQUIRED 				= 402;
	const FORBIDDEN 					= 403;
	const NOT_FOUND 					= 404;
	const METHOD_NOT_ALLOWED			= 405;
	const NOT_ACCEPTABLE 				= 406;
	const PROXY_AUTH_REQUIRED			= 407;
	const REQUEST_TIMEOUT				= 408;
	const CONFLICT 						= 409;

	const LENGTH_REQUIRED 				= 411;

	// Status server error range
	const INTERNAL_SERVER_ERROR			= 500;
	const NOT_IMPLEMENTED				= 501;
	const BAD_GATEWAY 					= 502;
	const SERVICE_UNAVAILABLE 			= 503;
	const GATEWAY_TIMEOUT 				= 504;
	const HTTP_VERSION_NOT_SUPPORTED	= 505;

	private function __construct() {}

	public static function getDescription($code) {
		switch ( $code ) {
			case self::OK:
				return "OK";
			case self::CREATED:
				return "Created";
			case self::ACCEPTED:
				return "Accepted";
			case self::NON_AUTHORITIVE_INFORMATION:
				return "Non Authoritive Information";
			case self::NO_CONTENT:
				return "No Content";
			case self::RESET_CONTENT:
				return "Reset Content";
			case self::PARTIAL_CONTENT:
				return "Partial Content";

			case self::BAD_REQUEST:
				return "Bad Request";
			case self::UNAUTHORIZED:
				return "Unauthorized";
			case self::PAYMENT_REQUIRED:
				return "Payment Required";
			case self::FORBIDDEN:
				return "Forbidden";
			case self::NOT_FOUND:
				return "Not Found";
			case self::METHOD_NOT_ALLOWED:
				return "Method Not Allowed";
			case self::NOT_ACCEPTABLE:
				return "Not Acceptable";
			case self::PROXY_AUTH_REQUIRED:
				return "Proxy Auth Required";
			case self::REQUEST_TIMEOUT:
				return "Request Timeout";
			case self::CONFLICT:
				return "Conflict";
			case self::LENGTH_REQUIRED:
				return "Length Required";

			case self::INTERNAL_SERVER_ERROR:
				return "Internal Server Error";
			case self::NOT_IMPLEMENTED:
				return "Not Implemented";
			case self::BAD_GATEWAY:
				return "Bad Gateway";
			case self::SERVICE_UNAVAILABLE:
				return "Service Unavailable";
			case self::GATEWAY_TIMEOUT:
				return "Gateway Timeout";
			case self::HTTP_VERSION_NOT_SUPPORTED:
				return "Http Version Not Supported";
		}
		return "";
	}
}
?>