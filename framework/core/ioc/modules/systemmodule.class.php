<?php
namespace Cannoli\Framework\Core\Ioc\Modules;

use Cannoli\Framework\Application,
	Cannoli\Framework\Core\Ioc\BindingModule;

class SystemModule extends BindingModule
{
	public function load() {
		$this->setNamespace("Cannoli\Framework");

		$this->bind("Application")->to(function() {
			return Application::getInstance();
		})->inSingletonScope();

		$this->setNamespace("Cannoli\Framework\Core\Plugin");
		$this->bind("PluginManager")->to("PluginManager")->inSingletonScope();

		$this->setNamespace("Cannoli\Framework\Core\Configuration");
		$this->bind("ConfigurationManager")->to(function() {
			return Application::getInstance()->getConfigurationManager();
		});
	}
}
?>