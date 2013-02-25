<?php
interface ILogger
{
	public function log();
	
	public function info();
	
	public function warning();
	
	public function error();
	
	public function fatal();
}
?>