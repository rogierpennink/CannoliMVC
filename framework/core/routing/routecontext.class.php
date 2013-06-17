<?php
namespace Cannoli\Framework\Core\Routing;

use Cannoli\Framework\Core\Context;

class RouteContext
{
	public function __construct(RouteData $routeData, Context\OperationContext $operationContext) {
		$this->routeData = $routeData;
		$this->operationContext = $operationContext;
	}

	public $routeData;

	public $operationContext;
}
?>