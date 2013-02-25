<?php
namespace Cannoli\Framework\View;

use Cannoli\Framework\Core\IRenderable;

/**
 * ViewCollection class
 *
 * Serves as a container for multiple IRenderable objects and allows
 * a sort order to be attached. Because ViewCollection implements the
 * IRenderable interface, it can be returned from controller methods.
 *
 * @package		Cannoli
 * @subpackage	Framework\View
 * @author 		Rogier Pennink
 * @category	View
 */
class ViewCollection implements IRenderable
{
	private $renderables = array();

	private $isSorted = true;
	
	/**
	 * The add method adds a Renderable instance to the ViewCollection's internal
	 * list of Renderable objects. An order can be specified so that when the
	 * collection is rendered, Renderables with a lower sort order are rendered first.
	 * If no order is specified, items are ordered the way they were added.
	 *
	 * @access public
	 * @param IRenderable 			An IRenderable object instance
	 * @param integer 				An integer that indicates sort order. 0 is the highest
	 * 								possible priority.
	 * @return void
	 */
	public function add(IRenderable &$renderable, $order = -1) {
		$this->isSorted = false;

		$this->renderables[] = array(
			"obj" => $renderable,
			"order" => $order == -1 ? count($this->renderables) : $order
		);
	}

	/**
	 * Returns the number of Renderables contained in this view collection.
	 *
	 * @access public
	 * @return integer 				The number of renderables
	 */
	public function count() {
		return count($this->renderables);
	}

	/**
	 * Render the contained Views
	 *
	 * Sorting is applied to the internal collection of IRenderables only if
	 * the collection has remained unchanged since the last call to render().
	 *
	 * @access public
	 * @return string 				The rendered output
	 */
	public function render() {
		/* Sort renderables prior to rendering. */
		$this->sort();

		return array_reduce($this->renderables, function($result, $item) {
			return $result . $item["obj"]->render();
		}, "");
	}

	/**
	 * 
	 */
	private function sort() {
		if ( $this->isSorted ) return;

		$this->isSorted = usort($this->renderables, function($a, $b) {
			return $a["order"] <= $b["order"] ? -1 : 1;
		});
	}
}
?>