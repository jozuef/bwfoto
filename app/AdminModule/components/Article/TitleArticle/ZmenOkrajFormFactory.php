<?php

declare(strict_types=1);

namespace App\AdminModule\Components\Article\TitleArticle;

use DbTable;
use Nette;
use Nette\Application\UI\Form;
use Nette\Database;

/**
 * Formular a jeho spracovanie pre zmenu sablony polozky.
 * Posledna zmena 28.09.2021
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.3
 */
class ZmenOkrajFormFactory {
  /** @var DbTable\Hlavne_menu */
	private $hlavne_menu;
  
  /**
   * @param DbTable\Hlavne_menu $hlavne_menu */
  public function __construct(DbTable\Hlavne_menu $hlavne_menu) {
		$this->hlavne_menu = $hlavne_menu;
	}
  
  /**
   * Formular.
   * @param Nette\Database\Table\ActiveRow $hlavne_menu Polozka v hlavnom menu
   * @return Nette\Application\UI\Form */  
  public function create(Nette\Database\Table\ActiveRow $hlavne_menu): Form  {
		$form = new Form();
		$form->addProtection();
    $form->addHidden("id", (string)$hlavne_menu->id);
    $p_a = $hlavne_menu->border_a !== null ? explode('|', $hlavne_menu->border_a) : ['#000000', 0];
    $p_b = $hlavne_menu->border_b !== null ? explode('|', $hlavne_menu->border_b) : ['#000000', 0];
    $p_c = $hlavne_menu->border_c !== null ? explode('|', $hlavne_menu->border_c) : ['#000000', 0];
    $form->addInteger('border_a_width')
         ->setHtmlType('number')
         ->setDefaultValue($p_a[1])
         ->setRequired('Šírka okraja "A" musí byť zadaná!')
         ->addRule(Form::RANGE, 'Šírka okraja "A" musí byť číslo od %d do %d!', [0,20]);
    $form->addText('border_a_color')
         ->setHtmlType('color')
         ->setDefaultValue($p_a[0])
         ->setRequired('Farba okraja "A" musí byť zadaná!');
    $form->addInteger('border_b_width')
         ->setHtmlType('number')
         ->setDefaultValue($p_b[1])
         ->setRequired('Šírka okraja "B" musí byť zadaná!')
         ->addRule(Form::RANGE, 'Šírka okraja "B" musí byť číslo od %d do %d!', [0,20]);
    $form->addText('border_b_color')
         ->setHtmlType('color')
         ->setDefaultValue($p_b[0])
         ->setRequired('Farba okraja "B" musí byť zadaná!');
    $form->addInteger('border_c_width')
         ->setHtmlType('number')
         ->setDefaultValue($p_c[1])
         ->setRequired('Šírka okraja "C" musí byť zadaná!')
         ->addRule(Form::RANGE, 'Šírka okraja "C" musí byť číslo od %d do %d!', [0,20]);
    $form->addText('border_c_color')
         ->setHtmlType('color')
         ->setDefaultValue($p_c[0])
         ->setRequired('Farba okraja "C" musí byť zadaná!');
    $form->addSubmit('uloz', 'Zmeň')
         ->setHtmlAttribute('class', 'btn btn-success')
         ->onClick[] = [$this, 'changeFormSubmitted'];
    $form->addSubmit('cancel', 'Cancel')
         ->setHtmlAttribute('class', 'btn btn-default')
         ->setHtmlAttribute('data-dismiss', 'modal')
         ->setHtmlAttribute('aria-label', 'Close')
         ->setValidationScope([]);
		return $form;
	}
  
  /** 
   * Spracovanie formulara.
   * @param Nette\Forms\Controls\SubmitButton $button Data formulara */
  public function changeFormSubmitted(Nette\Forms\Controls\SubmitButton $button) {
		$values = $button->getForm()->getValues();
    try {
			$this->hlavne_menu->changeBorders($values);
		} catch (Database\DriverException $e) {
			$button->addError($e->getMessage());
		}
  }
}