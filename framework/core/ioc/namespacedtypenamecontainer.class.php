<?php
namespace Cannoli\Framework\Core\Ioc;

/**
 * Represents a container for typename information. The container
 * extends the NamespaceContainer abstract class and thus offers
 * full namespace support.
 * 
 * @package Cannoli
 * @subpackage Framework\Core\Ioc
 * @author Rogier Pennink
 * @category Ioc
 */
abstract class NamespacedTypeNameContainer extends NamespaceContainer
{
	private $typeName;

	/**
	 * Overrides the NamespaceContainer::setNamespace method in order to 
	 * properly prefix the current typeName with the new namespace.
	 * 
	 * @access public
	 * @param $namespace 			The new namespace
	 * @return void
	 */
	public function setNamespace($namespace) {
		$this->removeNamespaceFromTypeName($this->getNamespace());
		parent::setNamespace($namespace);
		$this->addNamespaceToTypeName($this->getNamespace());
	}
	
	/**
	 * Returns the fully namespaced typename.
	 *
	 * @access public
	 * @return string 				The fully namespaced type name
	 */
	public function getTypeName() {
		return $this->typeName;
	}

	protected function setTypeName($typeName) {
		$this->typeName = $typeName;
		$this->addNamespaceToTypeName($this->getNamespace());
	}

	/**
	 * Adds given namespace to the typeName (where typeName is assumed to be
	 * a class name only). Does no overlap checks so be sure that typeName
	 * is in fact an class name only.
	 * 
	 * @access private
	 * @param $namespace 			The namespace string
	 * @return void
	 */
	private function addNamespaceToTypeName($namespace) {
		if ( $namespace == "" ) return;
		$namespace = trim($namespace, "\\");
		$this->typeName = $namespace ."\\". $this->typeName;
	}

	/**
	 * Remove all namespace information from the typeName, essentially
	 * making it just a class name.
	 *
	 * @access private
	 * @param $namespace 			The namespace string that must be removed
	 * @return void
	 */
	private function removeNamespaceFromTypeName($namespace) {
		if ( $namespace == "" ) return;

		// It must be the starting namespace, we don't want to take part of a namespace away from the middle
		if ( ($namespacePosition = strpos($this->typeName, $namespace)) === 0 ) {
			$typeName = substr_replace($this->getTypeName(), $namespace, 0, strlen($namespace));
			$this->typeName = $typeName;
		}
	}
}
?>