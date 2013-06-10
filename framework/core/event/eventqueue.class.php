<?php
namespace Cannoli\Framework\Core\Event;

use Cannoli\Framework\Core\Utility;

class EventQueue
{
	const PRIORITY_LOW 				= 0x0001;
	const PRIORITY_NORMAL 			= 0x0002;
	const PRIORITY_HIGH 			= 0x0004;

	private $highPriorityQueue;
	private $normalPriorityQueue;
	private $lowPriorityQueue;

	public function __construct() {
		// TODO: Naive implementation
		$this->highPriorityQueue = new Utility\Queue();
		$this->normalPriorityQueue = new Utility\Queue();
		$this->lowPriorityQueue = new Utility\Queue();
	}

	/**
	 * Add an event with the specified priority to the internal events queue
	 *
	 * @access public
	 * @param $event 				The event
	 * @param $priority 			The priority with which the event should be handled
	 * @return void
	 */
	public function addEvent(IEvent $event, $priority = self::PRIORITY_NORMAL) {
		$this->checkPriority($priority);

		switch ( $priority ) {
			case self::PRIORITY_LOW:
				$this->lowPriorityQueue->enqueue($event);
				break;
			case self::PRIORITY_NORMAL:
				$this->normalPriorityQueue->enqueue($event);
				break;
			case self::PRIORITY_HIGH:
				$this->highPriorityQueue->enqueue($event);
				break;
		}
	}

	/**
	 * Checks whether there are any events left that have not yet been processed.
	 *
	 * @access public
	 * @return boolean 				Whether or not there are events left in the internal queue
	 */
	public function hasEvents() {
		return $this->highPriorityQueue->hasMore() && $this->normalPriorityQueue->hasMore() &&
				$this->lowPriorityQueue->hasMore();
	}

	/**
	 * Takes an event from the internal queue and processes it
	 *
	 * @access public
	 * @return void
	 */
	public function processNext() {
		$event = $this->highPriorityQueue->hasMore() ? $this->highPriorityQueue->dequeue() :
				 ($this->normalPriorityQueue->hasMore() ? $this->normalPriorityQueue->dequeue() :
				  ($this->lowPriorityQueue->hasMore() ? $this->lowPriorityQueue->dequeue() : null));
		if ( !is_null($event) ) {
			// TODO: Process
		}
	}

	/**
	 * Checks the given priority value for validity. Throws an exception if
	 * invalid.
	 *
	 * @access private
	 * @param $priority 			The priority value that needs to be checked
	 * @return void
	 * @throws InvalidArgumentException
	 */
	private function checkPriority($priority) {
		if ( !in_array($priority, array(self::PRIORITY_LOW, self::PRIORITY_NORMAL, self::PRIORITY_HIGH)) )
			throw new \InvalidArgumentException("Invalid priority");
	}
}
?>