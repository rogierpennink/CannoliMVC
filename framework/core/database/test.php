<?php
namespace Cannoli\Framework\Core\Database;

include("../exception/database/databaseconnectionexception.class.php");
include("../exception/database/databasedisconnectexception.class.php");
include("../exception/database/databasenotconnectedexception.class.php");
include("../exception/database/databasequeryexception.class.php");
include("../exception/database/databasequeryexecutionexception.class.php");
include("../exception/database/databaseresultsetexception.class.php");
include("../plugin/contracts/database/iresultset.class.php");
include("../plugin/contracts/database/idatabaseconnection.class.php");
include("pdoresultset.class.php");
include("pdodatabaseconnection.class.php");
include("../../plugin/cannolimysqlpdo/mysqlpdoresultset.class.php");
include("../../plugin/cannolimysqlpdo/mysqlpdodatabaseconnection.class.php");

$db = new \Cannoli\Framework\Plugin\CannoliMySQLPDO\MySQLPDODatabaseConnection();
$db->connect("localhost", "root", "", "test");

$result = $db->query("SELECT * FROM pages");
$page = $result->fetchObject();
echo $page->name_page ."<br />";
//$result->seekStart();
$page = $result->fetchObject();
echo $page->name_page ."<br />";
$result->close();
?>