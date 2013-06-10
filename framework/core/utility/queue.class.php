<?php
namespace Cannoli\Framework\Core\Utility;

class Queue
{
	private $tail;

	private $head;

	private $count = 0;

	public function enqueue($obj) {		
		$node = new \stdClass();
		$node->value = $obj;
		$node->prev = null;
		$node->next = $this->tail;

		if ( is_null($this->tail) )
			$this->head = $node;
		else
			$node->next->prev = $node;
		$this->tail = $node;

		$this->count++;
	}

	public function dequeue() {
		if ( is_null($this->head) )
			return null;

		$obj = $this->head->value;
		$this->count--;
		
		$this->head = $this->head->prev;

		return $obj;
	}

	public function peek() {
		return is_null($this->head) ? null : $this->head->value;
	}

	public function hasMore() {
		return $this->count > 0;
	}

	public function getCount() {
		return $this->count;
	}
}
?>