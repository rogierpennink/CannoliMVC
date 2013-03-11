<?php
namespace Cannoli\Framework\Plugin\CannoliMySQLPDO;

use Cannoli\Framework\Core\Exception;

/**
 * A thin wrapper around the framework's PDOResultSet class. This
 * class only makes sure that seek() and seekStart() throw exceptions.
 *
 * @package Cannoli
 * @subpackage Framework\Plugin\CannoliMySQLPDO
 * @author Rogier Pennink
 * @category Database
 */
class MySQLPDOResultSet extends PDOResultSet
{
	public function seek($pos) {
		throw new Exception\Database\DatabaseResultSetException("Scrollable cursors are not supported in MySQLPDO");
	}
}
?>