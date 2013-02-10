<?php

use Nette\Templates\TemplateFilters,
	Nette\Http;
use Webloader\JavaScriptLoader,
	Webloader\CssLoader;
use Nette\Diagnostics\Debugger;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	private $models;

	public function injectModelLoader(Local\ModelLoader $models)
	{
		$this->models = $models;
	}

	public function startup()
	{
		parent::startup();
		// check changes in database structure
		// in production, it must be called manualy
		if (!$this->context->params['productionMode']
			&& $this->context->dbChangelog->importNewChangelogData()
			&& $this->presenter->name != 'Changelog:Changelog'
		) {
			$this->redirect(':Changelog:Changelog:');
		}
	}

	protected function createTemplate($class = NULL) {
		$template = parent::createTemplate($class);
		$template->registerHelperLoader('Helpers::loader');
		$template->registerHelper('resize', callback($this->context->imageHelper, 'resize'));
		$template->canonicalUrl = parse_url($this->link('//this'), PHP_URL_PATH);
		return $template;
	}

    /**
     * @return \ModelLoader
     */
    final public function getModels()
    {
        return $this->context->modelLoader;
    }

	public function barDump($value) {
		Debugger::barDump($value);
	}

	public function startTransaction()
	{
		$this->context->database->exec('START TRANSACTION');
	}
	public function commit()
	{
		$this->context->database->exec('COMMIT');
	}
	public function rollback()
	{
		$this->context->database->exec('ROLLBACK');
	}

	public function createComponentCss()
	{
		// připravíme seznam souborů
		// FileCollection v konstruktoru může dostat výchozí adresář, pak není potřeba psát absolutní cesty
		$files = new \WebLoader\FileCollection($this->context->params['wwwDir'] . '/css');

		// kompilátoru seznam předáme a určíme adresář, kam má kompilovat
		$compiler = \WebLoader\Compiler::createCssCompiler($files, $this->context->params['wwwDir'] . '/webtemp');

		// nette komponenta pro výpis <link>ů přijímá kompilátor a cestu k adresáři na webu
		return new \WebLoader\Nette\CssLoader($compiler, $this->template->basePath . '/webtemp');
	}

	public function createComponentJs()
	{
		$files = new \WebLoader\FileCollection($this->context->params['wwwDir'] . '/js');
		$compiler = \WebLoader\Compiler::createJsCompiler($files, $this->context->params['wwwDir'] . '/webtemp');
		return new \WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath . '/webtemp');
	}

}
