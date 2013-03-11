<?php
namespace Cannoli\Framework\Core\Ioc\Modules;

use Cannoli\Framework\Application,
	Cannoli\Framework\Core\Ioc\BindingModule;

class ContractsModule extends BindingModule
{
	public function load() {
		$this->setNamespace("Cannoli\\Framework\\Contract");

		$this->bind("Database\\IDatabaseConnection")->to(function() {
			return Application::getInstance()->getDatabaseConnectionManager()->getActiveConnection();
		});
	}
}
?>