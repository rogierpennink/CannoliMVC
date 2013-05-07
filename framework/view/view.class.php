<?php
namespace Cannoli\Framework\View;

class View extends Renderable
{
	protected $parameters;
	
	protected $templateFile;
	
	public function __construct($template) {
		$this->clear();
		$this->setTemplate($template);
	}
	
	public function setTemplate($template) {
		$this->templateFile = $template;
	}
	
	public function set($key, $value) {
		$this->parameters[$key] = $value;
	}
	
	public function has($key) {
		return isset($this->parameters[$key]);
	}
	
	public function get($key) {
		if ( $this->has($key) )
			return $this->parameters[$key];
		return null;
	}
	
	public function invalidate($key) {
		if ( $this->has($key) )
			unset($this->parameters[$key]);
	}
	
	public function clear() {
		$this->parameters = array();
	}
	
	/**
	 * The render method attempts to use the relative pathname provided in the
	 * class constructor or setTemplate method to execute the php file and
	 * capture its output into a string.
	 * @return string		view template output
	 */
	public function render() {
		/* Start output buffering so we can grab the generated output in a string. */
		ob_start();
		
		/* Extract variables. */
		extract($this->parameters);
		
		/* Attempt to include the template file. */
		include($this->templateFile);
		
		/* Read the output buffer into a string and return it. */
		$output = ob_get_contents();
		ob_clean();
		return $output;
	}
	
	public function __toString() {
		return $this->templateFile;
	}
}
?>