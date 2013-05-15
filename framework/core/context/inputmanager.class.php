<?php
namespace Cannoli\Framework\Core\Context;

use Cannoli\Framework\Core\Utility;

class InputManager extends Utility\Singleton implements IInputManager
{
	protected $data = array();

	public function has($key) {
		return isset($this->data[$key]);
	}

	public function data($key, $defaultValue = null) {
		if ( !$this->has($key) ) return $defaultValue;

		return $this->data[$key];
	}

	/*///////////////////////////////\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
						ARRAYACCESS IMPLEMENTATION
	\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\//////////////////////////////*/

	/**
	 * @access public
	 * @param $offset
	 * @return boolean
	 */
	public function offsetExists($offset) {
		return isset($this->data[$offset]);
	}

	/**
	 * @access public
	 * @param $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return (isset($this->data[$offset]) ? $this->data[$offset] : null);
	}

	/** 
	 * @access public
	 * @param $offset
	 * @param $value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		if ( !is_null($offset) ) {
            $this->data[$offset] = $value;
        }
	}

	/**
	 * @access public
	 * @param $offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($this->data[$offset]);
	}

	/**
	 * @access public
	 * @return void
	 */
	public function rewind() {
    	reset($this->data);
    }

    /**
     * @access public
     * @return mixed
     */
    public function current() {
		return current($this->data);
    }

    /**
     * @access public
     * @return mixed
     */
    public function key() {
		return key($this->data);
    }

    /**
     * @access public
     * @return mixed
     */
    public function next() {
		return next($this->data);
    }

    /** 
     * @access public
     * @return boolean
     */
    public function valid() {
		return $this->current() !== false;
    }   
}
?>