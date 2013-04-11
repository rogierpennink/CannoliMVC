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
}
?>