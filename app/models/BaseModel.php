<?php

namespace Models;

/**
 * Base model
 * @link http://wiki.nette.org/cs/cookbook/dynamicke-nacitani-modelu
 * @author Majkl578
 */
class BaseModel extends \Nette\Object
{
	/** @var \Nette\DI\Container */
	private $context;

	private $tableName;

	private $dibi;

	public function __construct(\Nette\DI\Container $container, $tableName = null)
	{
		$this->context = $container;
		$this->tableName = $tableName;
	}

	/**
	 * @return \Nette\DI\Container
	 */
	final public function getContext()
	{
		return $this->context;
	}

	/**
	 * @return \DibiConnection
	 */
	final public function getDatabase()
	{
		return $this->context->database;
	}

	public function getTable()
	{
		return $this->getDatabase()->table($this->tableName);
	}

}