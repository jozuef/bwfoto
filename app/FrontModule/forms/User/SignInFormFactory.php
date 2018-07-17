<?php

namespace App\FrontModule\Forms\User;
use Language_support;
use Nette\Application\UI\Form;
use Nette\Security;


/**
 * Prihlasovaci formular
 * Posledna zmena 24.01.2018
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2018 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.3
 */
class SignInFormFactory {
  /** @var User */
  private $user;
  /** @var Language_support\User */
  private $user_texts;

  /** @param Security\User $user   */
  public function __construct(Security\User $user, Language_support\User $user_texts) {
    $this->user = $user;
    $this->user_texts = $user_texts;
	}
  
  /** 
   * Vratenie textu pre dany kluc a jazyk
   * @param string $key - kluc daneho textu
   * @return string - hodnota pre dany text */
  private function trLang($key) {
    return $this->user_texts->trText($key);
  }
  
  /**
   * Prihlasovaci formular
   * @return Nette\Application\UI\Form */
  public function create()  {
    $form = new Form();
		$form->addProtection();
		$form->addText('email', $this->trLang('base_SignInForm_email'), 40, 100)
				 ->setRequired($this->trLang('base_SignInForm_email_req'));
		$form->addPassword('password', $this->trLang('base_SignInForm_password'), 40)
				 ->setRequired($this->trLang('base_SignInForm_password_req'));
		$form->addCheckbox('remember', $this->trLang('base_SignInForm_remember'));
    $form->addSubmit('login', $this->trLang('base_SignInForm_login'))
         ->setAttribute('class', 'btn btn-success')
         ->onClick[] = [$this, 'signInFormSubmitted'];
		return $form;
	}
  
  /** 
   * Overenie po prihlaseni
   * @param Nette\Forms\Controls\SubmitButton $button Data formulara */
	public function signInFormSubmitted($button) {
    $values = $button->getForm()->getValues();
    try {
			if ($values->remember) {
				$this->user->setExpiration('+ 14 days', FALSE);
			} else {
				$this->user->setExpiration('+ 30 minutes', TRUE);
			}
			$this->user->login($values->email, $values->password);
		} catch (Security\AuthenticationException $e) {
      $button->addError($e->getMessage());
    }
	}
}
