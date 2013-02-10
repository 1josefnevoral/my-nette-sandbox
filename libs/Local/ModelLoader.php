<?php

namespace Local;
use Nette\DI\Container;

/**
 * Model loader
 * @link http://wiki.nette.org/cs/cookbook/dynamicke-nacitani-modelu
 * @author Majkl578
 */
final class ModelLoader
{
	/** @var Nette\DI\Container */
	private $modelContainer;

	/** @var array */
	private $models = array();

	public function __construct(Container $container)
	{
		$modelContainer = new Container;
		$modelContainer->addService('database', $container->database);
		$modelContainer->addService('cacheStorage', $container->cacheStorage);
		$modelContainer->addService('session', $container->session);
		$modelContainer->params = $container->params;
		$modelContainer->freeze();
		$this->modelContainer = $modelContainer;
	}

	public function getModel($name)
	{
		$lname = strtolower($name);

		if (!isset($this->models[$lname])) {
			$class = 'Models\\' . ucfirst($name);
			// transform model name to table_name
			$tableName = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);
			$tableName = strtolower(implode('_', $tableName));
			if (!class_exists($class)) {
				// create BaseModel with table_name extracted from model name
				$this->models[$lname] = new \Models\BaseModel($this->modelContainer, $tableName);
			} else {
				$this->models[$lname] = new $class($this->modelContainer, $tableName);
			}
		}

		return $this->models[$lname];
	}

	public function __get($name)
	{
		return $this->getModel($name);
	}
}