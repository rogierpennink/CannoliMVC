<?php
class DatabaseException extends Exception
{
	public function __construct($message = "", $code = 0) {
		__parent::construct($message, $code);
	}
}
?>