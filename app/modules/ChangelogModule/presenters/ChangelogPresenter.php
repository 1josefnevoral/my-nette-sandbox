<?php

namespace ChangelogModule;

/**
 * Description of ChangelogPresenter
 *
 * @author Josef Nevoral <josef.nevoral@gmail.com>
 */
class ChangelogPresenter extends \BasePresenter {

	private $dbChangelog;

	public function injectDbChangelog(DbChangelog $dbChangelog)
	{
		$this->dbChangelog = $dbChangelog;
	}

	public function actionDefault()
	{
		$this->template->errors = array();
		$this->dbChangelog->importNewChangelogData();
	}

	public function renderDefault()
	{
		$this->template->queriesToExecute = $this->models->changelog->getTable()->where('executed', 0);
	}

	public function createComponentAddToChangelog()
	{
		if (class_exists('\BaseForm')) {
			$form = new \BaseForm();
		} else {
			$form = new \Nette\Application\UI\Form();
		}
		$form->addText('description', 'Short description')
			->setAttribute('class', 'input-large')
			->setRequired('Write short description what you are changing');
		$form->addTextArea('queries', 'SQL queries')
			->setAttribute('class', 'span7')
			->setRequired('Huh?');
		$form->addSubmit('send', 'Save')
			->getControlPrototype()->class('btn btn-primary');
		$form->onSuccess[] = callback($this, 'addToChangelog');
		return $form;
	}

	public function handleExecuteQueries()
	{
		$queriesToExecute = $this->models->changelog->getTable()
			->where('executed', 0)
			->order('ins_dt');
		$errors = $this->context->dbChangelog->executeQueries($queriesToExecute);
		if (empty($errors)) {
			$this->flashMessage('All queries has been executed successfully', 'success');
			$this->redirect('Changelog:');
		}

		$this->template->errors = $errors;
	}

	public function addToChangelog($form)
	{
		$values = $form->getValues();
		$this->context->dbChangelog->addNewQueries($values['description'], $values['queries']);

		$this->flashMessage('Queries saved');
		$this->redirect('Changelog:add');
	}
}