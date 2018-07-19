<?php
namespace App\AdminModule\Components\Article\TitleImage;

use Nette;
use DbTable;

/**
 * Komponenta pre titulku polozky(titulny obrazok a nadpis).
 * 
 * Posledna zmena(last change): 19.07.2018
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright Copyright (c) 2012 - 2018 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.5
 */

class TitleImageControl extends Nette\Application\UI\Control {

  /** @var Nette\Database\Table\ActiveRow $clanok Info o clanku */
  private $clanok;
  /** @var string $dir_to_menu */
  private $dir_to_menu;
  /** @var string $www_dir */
  private $www_dir;
  /** @var array */
  private $admin_links = ['edit'=>FALSE, 'dlink'=>FALSE, 'vlastnik'=>FALSE];
  /** @var Nette\Security\User */
  private $user;
  /** @var DbTable\Hlavne_menu */
  private $hlavne_menu;
  
  /** @var EditTitleImageFormFactory */
	public $editTitleImage;

  /**
   * @param \App\AdminModule\Components\Article\TitleImage\EditTitleImageFormFactory $editTitleImageFormFactory
   * @param Nette\Security\User $user
   * @param DbTable\Hlavne_menu $hlavne_menu */
  public function __construct(EditTitleImageFormFactory $editTitleImageFormFactory, Nette\Security\User $user, DbTable\Hlavne_menu $hlavne_menu) {
    parent::__construct();
    $this->editTitleImage = $editTitleImageFormFactory;
    $this->user = $user;
    $this->hlavne_menu = $hlavne_menu;
  }
  
  /** Nastavenie komponenty
   * @param Nette\Database\Table\ActiveRow $clanok
   * @param array $nastavenie
   * @return \App\AdminModule\Components\Article\TitleArticleControl */
  public function setTitle(Nette\Database\Table\ActiveRow $clanok, $nastavenie, $name) {
    $this->clanok = $clanok;
    $this->dir_to_menu = $nastavenie["dir_to_menu"];
    $this->www_dir = $nastavenie["wwwDir"];
    
    //Test opravneni
    $hlm = $this->clanok->hlavne_menu; // Pre skratenie zapisu
    //Vlastnik je admin a autor clanku
    $vlastnik = $this->user->isInRole('admin') ? TRUE : $this->user->getIdentity()->id == $hlm->id_user_main;
    //Opravnenie mam ak som vlastnik alebo mi to vlastnik povolil
    $opravnenie = $vlastnik ? TRUE : (boolean)($hlm->id_hlavne_menu_opravnenie & 2);
    $this->admin_links = [
      "edit" => $opravnenie && $this->user->isAllowed($name, 'edit'),
      "del"  => $opravnenie && $this->user->isAllowed($name, 'del'),
      "vlastnik" => $vlastnik,
    ];
    return $this;
  }
  
  /** Render */
	public function render() {
    $this->template->setFile(__DIR__ . '/TitleImage.latte');
    $this->template->clanok = $this->clanok;
    $this->template->admin_links = $this->admin_links;
    $this->template->dir_to_menu = $this->dir_to_menu;
		$this->template->render();
	}
  
  /** 
   * Komponenta formulara pre zmenu vlastnika.
   * @return Nette\Application\UI\Form */
  public function createComponentEditTitleImageForm() {
    $form = $this->editTitleImage->create($this->dir_to_menu, $this->www_dir);
    $form->setDefaults(["id" => $this->clanok->id_hlavne_menu,
                        "old_avatar" => $this->clanok->hlavne_menu->avatar,
                        "ikonka"=> $this->clanok->hlavne_menu->ikonka,
                       ]);
    $form['uloz']->onClick[] = function ($button) { 
      $this->presenter->flashOut(!count($button->getForm()->errors), 'this', 'Zmena bola úspešne uložená!', 'Došlo k chybe a zmena sa neuložila. Skúste neskôr znovu...');
		};
    return $this->presenter->_vzhladForm($form);
  }
}

interface ITitleImageControl {
  /** @return TitleImageControl */
  function create();
}