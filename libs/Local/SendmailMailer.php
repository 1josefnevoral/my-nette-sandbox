<?php

namespace Local;

/**
 * Description of SendmailMailer
 *
 * @author Josef Nevoral <josef.nevoral@gmail.com>
 */
class SendmailMailer extends \Nette\Mail\SendmailMailer {
	
	/** @var bool */
	private $productionMode;

	private $sendEmailInDevelopment;

	public function __construct($productionMode, $sendEmailInDevelopment = false)
	{
		$this->productionMode = $productionMode;
		$this->sendEmailInDevelopment = $sendEmailInDevelopment;
	}

	/**
	 * Checks if is not in production mode
	 * If is in production sends email, else does not
	 * @param  Message
	 * @return void
	 */
	public function send(\Nette\Mail\Message $mail)
	{
		if ($this->productionMode || $this->sendEmailInDevelopment) {
			parent::send($mail);
		} else {
			// @todo add some logging, that email would be send
		}

	}

}

?>
