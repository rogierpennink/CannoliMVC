<?php
namespace Cannoli\Framework\Core\Ioc\Modules;

use Cannoli\Framework\Application,
	Cannoli\Framework\Core\Ioc\BindingModule;

class SystemModule extends BindingModule
{
	public function load() {
		// Bind application
		$this->bind("Cannoli\Framework\Application")->to(function() {
			return Application::getInstance();
		})->inSingletonScope();
	}
}
?>