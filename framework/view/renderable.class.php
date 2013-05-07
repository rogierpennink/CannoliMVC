<?php
namespace Cannoli\Framework\View;

use Cannoli\Framework\Core;

abstract class Renderable implements Core\IRenderable
{
	public function __toString() {
		return $this->render();
	}
}
?>