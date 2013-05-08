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
		
	}

	public function index($recipient = "World", $subject = "George W. Bush") {
		echo "Hello ". $recipient .", how is ". $subject ." today?";
		echo "<br />". $this->input["damn"];
	}
}
?>