<?php

use Nette\Diagnostics\Debugger;

/**
 * Base Form
 * Provide basic form settings
 *
 * @author     Josef Nevoral
 *
 */

class BaseForm extends Nette\Application\UI\Form {

	/**
	 * Base form constructor.
	 */
	public function __construct(Nette\IComponentContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		// set bootstrap renderer
		$this->setRenderer(new Kdyby\BootstrapFormRenderer\BootstrapRenderer);
	}

	/**
	 * Spam Protection
	 */

	const ANTISPAM_NAME = 'teP82GASutu7Rafr';

	private static function numberCipher($input, $decrypt = false) {
		$result = '';
		for($i=1; $i<=strlen($input); $i++) {
			$digit = substr($input, -$i, 1);
			$digit = $decrypt ? chr(ord($digit)-53) : chr(ord($digit)+53);
			$result .= $digit;
		}
		return $result;
	}

	/**
	 * Generates token to put into hidden form input
	 * @return string $token
	 */
	private function getToken() {
		$time = (int) time();
		$token = self::numberCipher($time);
		return $token;
	}

	public static function validateSpamProtection($control) {
		$time = (int) time();
		$formTime = (int) self::numberCipher($control->value, true);
		$timeTaken = $time - $formTime;
		if($timeTaken<3 || $timeTaken>5*60*60){
			return false;
		}
		else{
			array_unshift($control->getForm()->onSubmit, callback("BaseForm", 'unsetSpamProtection'));
			$control->getForm()->onSubmit[] = callback("BaseForm", 'resetSpamProtection');
			return true;
		}
	}

	public static function unsetSpamProtection($form) {
		$form->removeComponent( $form->getComponent(self::ANTISPAM_NAME) );
	}

	public static function resetSpamProtection($form) {
		$form->addSpamProtection();
	}

	public function addSpamProtection(){
		$this->addHidden(self::ANTISPAM_NAME)
			->setValue($this->getToken())
			->addRule('BaseForm::validateSpamProtection', 'Tvůj příspěvek byl označen jako spam. Zkus to, prosím, znova.');
	}

	public function barDump($value) {
		Debugger::barDump($value);
	}
}