<?php
namespace Application\Controller;

use Cannoli\Framework\Core\Net,
	Cannoli\Framework\Contract,
	Cannoli\Framework\Controller\Controller;

class TestController extends Controller
{
	//public function _injectDependencies(Contract\Database\IDatabaseConnection &$db) {
		// $result = $db->select("store");
		// while ( $data = $result->fetchAssoc() ) {
		// 	echo $data["name"] ."<br />";
		// }
		// $result->close();
	//}

	public function _initialize() {
		echo $this->request;
	}

	public function index() {
		echo "Hello world";
	}
}
?>