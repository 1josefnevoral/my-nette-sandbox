<?php
namespace Admin\Forms;

use Nette\Application\UI;

/**
 * Sign in/out presenters.
 */
class SignInForm extends \BaseForm
{
	/** @var \Nette\Security\User */
	private $user;

	public function __construct(\Nette\Security\User $user)
	{
		parent::__construct();
		$this->user = $user;
		$form = $this;
		$form->addText('username', 'Username:')
			->setRequired('Please enter your username.');

		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Keep me signed in');

		$form->addSubmit('send', 'Sign in');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->formSubmitted;
		return $form;
	}



	public function formSubmitted($form)
	{
		$values = $form->getValues();

		if ($values->remember) {
			$this->user->setExpiration('+ 14 days', FALSE);
		} else {
			$this->user->setExpiration('+ 20 minutes', TRUE);
		}

		try {
			$this->user->login($values->username, $values->password);
		} catch (\Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
			return;
		}

		$this->presenter->redirect('Homepage:');
	}

}
