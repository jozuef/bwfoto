<?php
namespace App\FrontModule\Presenters;

use App\FrontModule\Forms\User;
use DbTable;
use Language_support;
use Nette\Application\UI\Multiplier;
use Nette\Application\UI\Presenter;
use Nette\Http;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use PeterVojtech;
use Texy;

/**
 * Zakladny presenter pre vsetky presentery vo FRONT module
 * 
 * Posledna zmena(last change): 26.04.2021
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2021 Ing. Peter VOJTECH ml.
 * @license
 * @link      http://petak23.echo-msz.eu
 * @version 1.6.2
 */
abstract class BasePresenter extends Presenter {
  
  use PeterVojtech\MainLayout\Favicon\faviconTrait;
  use PeterVojtech\MainLayout\GoogleAnalytics\googleAnalyticsTrait;

  // -- DB
  /** @var DbTable\Dokumenty @inject */
	public $dokumenty;
  /** @var DbTable\Druh @inject */
	public $druh;
  /** @var DbTable\Hlavne_menu @inject */
	public $hlavne_menu;
  /** @var DbTable\Hlavne_menu_cast @inject */
  public $hlavne_menu_cast;
  /** @var DbTable\Hlavne_menu_lang @inject*/
  public $hlavne_menu_lang;
  /** @var DbTable\Lang @inject*/
	public $lang;
  /** @var DbTable\User_roles @inject */
	public $user_roles;
  /** @var DbTable\Udaje @inject */
	public $udaje;
  /** @var DbTable\User_main @inject */
	public $user_main;
  /** @var DbTable\Verzie @inject */
	public $verzie;

  /** @var Language_support\LanguageMain @inject */
  public $texty_presentera;
  
  // -- Komponenty
  /** @var \App\FrontModule\Components\Slider\ISliderControl @inject */
  public $sliderControlFactory;
  /** @var \App\FrontModule\Components\User\IKontaktControl @inject */
  public $kontaktControlFactory;
  /** @var \App\FrontModule\Components\User\UserLangMenu\IUserLangMenuControl @inject */
  public $userLangMenuControlFactory;
  /** @var \App\FrontModule\Components\News\INewsControl @inject */
  public $newsControlFactory;
  /** @var \App\FrontModule\Components\Clanky\ZobrazClanok\IZobrazClanokControl @inject */
  public $zobrazClanokControlFactory;
  /** @var \App\FrontModule\Components\Clanky\IAktualneClankyControl @inject */
  public $aktualneClankyControlFactory;
  
  /** @var \App\FrontModule\Components\Autocomplete\IAutocompleteControl @inject */
  public $autocompleteControlFactory;

  // -- Forms
  /** @var User\SignInFormFactory @inject*/
  public $signInForm;


  /** @var string Skratka aktualneho jazyka 
   * @persistent */
  public $language = 'sk';
  /** @persistent */
  public $backlink = '';
	
  /** @var Http\Request @inject*/
  public $httpRequest;
  
  /** @var Texy\Texy @inject */
	public $texy;
  
  /** @var string kmenovy nazov stranky pre rozne ucely typu www.neco.sk*/
  public $nazov_stranky;
  /** @var int Uroven registracie uzivatela  */
	public $id_reg;
  /** @var int Maximalna uroven registracie uzivatela */
	public $max_id_reg = 0;
  
  /** @var array Pole s hlavnymi udajmi webu */
  public $udaje_webu = [
    "nazov" => "",
    "h1part2" => "",
    "description" => "",
    'hl_udaje' => FALSE
  ];

  /** @var int */
  public $language_id = 1;
  
  /** @var array nastavenie z config-u */
  public $nastavenie;

  /** @var int Maximalna velkost suboru pre upload */
  public $upload_size = 0;
  
  public function __construct($parameters) {
    // Nastavenie z config-u
    $this->nastavenie = $parameters;
  }
  
  protected function startup() {
    parent::startup();
    // Kontrola prihlasenia a nacitania urovne registracie
    $this->id_reg = ($this->user->isLoggedIn()) ? ($this->user->getIdentity()->id_user_roles === NULL ? 0 : $this->user->getIdentity()->id_user_roles) : 0;
    
    $modul_presenter = explode(":", $this->name);

    // Nastav jazyk
    $lang_temp = $this->lang->findOneBy(['skratka'=>$this->params['language']]);
    if(isset($lang_temp->skratka) && $lang_temp->skratka == $this->params['language']) {
      $this->language = $this->params['language'];
      $this->language_id = $lang_temp->id;
    }
    //Nastavenie textov podla jazyka 
    $this->texty_presentera->setLanguage($this->language); 

    // Kontrola ACL
    if (!$this->user->isAllowed($this->name, $this->action)) {
      $this->flashRedirect('Homepage:notAllowed', sprintf($this->texty_presentera->translate('base_nie_je_opravnenie'), $this->action), 'danger');
    }

    //Nacitanie hlavnych udajov webu
    $this->udaje_webu = array_merge($this->udaje_webu, $this->udaje->hlavneUdaje($this->language));
    // Nacitanie pomocnych premennych
    $this->udaje_webu['meno_presentera'] = strtolower($modul_presenter[1]); //Meno aktualneho presentera
    $httpR = $this->httpRequest->getUrl();
    $this->nazov_stranky = $httpR->host.$httpR->scriptPath; // Nazov stranky v tvare www.nieco.sk
    $this->nazov_stranky = substr($this->nazov_stranky, 0, strlen($this->nazov_stranky)-1);
    // Priradenie hlavnych parametrov a udajov
    $this->max_id_reg = $this->user_roles->findAll()->max('id');//Najdi max. ur. reg.
    //Najdi info o druhu
    $tmp_druh = $this->druh->findBy(["druh.presenter"=>ucfirst($this->udaje_webu['meno_presentera'])])
                            ->where("druh.modul IS NULL OR druh.modul = ?", $modul_presenter[0])->limit(1)->fetch();
    if ($tmp_druh !== null) {
      if ($tmp_druh->je_spec_naz) { //Ak je spec_nazov pozadovany a mam id
        $hl_udaje = $this->hlavne_menu->hladaj_id(isset($this->params['id']) ? (int)trim($this->params['id']) : 0, $this->id_reg);
      } else {//Ak nie je spec_nazov pozadovany
        $hl_udaje = $this->hlavne_menu->findOneBy(["id_druh"=>$tmp_druh->id]);
      }
    } else { $hl_udaje = null; }
    if ($hl_udaje !== null) { //Ak sa hl. udaje nasli
      //Nacitanie textov hl_udaje pre dany jazyk 
      $lang_hl_udaje = $this->hlavne_menu_lang->findOneBy(['lang.skratka'=>$this->language, 
                                                          'id_hlavne_menu'=>$hl_udaje->id]);
      if ($lang_hl_udaje !== null){ //Nasiel som udaje a tak aktualizujem
        $this->udaje_webu["nazov"] = $lang_hl_udaje->menu_name;
        $this->udaje_webu["h1part2"] = $lang_hl_udaje->h1part2;
        $this->udaje_webu["description"] = " - " . $lang_hl_udaje->view_name;
      } 
      $this->udaje_webu['hl_udaje'] = $hl_udaje->toArray();
    } 
    //Vypocet max. velkosti suboru pre upload
    $ini_v = trim(ini_get("upload_max_filesize"));
    $s = ['g'=> 1<<30, 'm' => 1<<20, 'k' => 1<<10];
    $this->upload_size =  intval($ini_v) * ($s[strtolower(substr($ini_v,-1))] ?: 1);
	}

  /** 
   * Komponenta pre vykreslenie menu
   * @return \App\FrontModule\Components\Menu\Menu */
  public function createComponentMenu() {
    $menu = new \App\FrontModule\Components\Menu\Menu;
    $menu->setTextTitleImage($this->texty_presentera->translate("base_text_title_image"));
    $hl_m = $this->hlavne_menu->getMenuFront($this->language);
    if (count($hl_m)) {
      $servise = $this;
      $menu->fromTable($hl_m, function($node, $row) use($servise) {
        $poll = ["id", "name", "tooltip", "view_name", "avatar", "anotacia", "novinka", "node_class", "poradie_podclankov"];
        foreach ($poll as $v) { $node->$v = $row['node']->$v; }
        // Nasledujuca cast priradi do $node->link odkaz podla kriteria:
        // Ak $rna == NULL - vytvori link ako odkaz do aplikacie
        // Ak $rna zacina "http" - pouzije sa absolutna adresa
        // Ak $rna obsahuje text "Clanky:default 2" - vytvorí sa odkaz do aplikácie na clanok s id 2 - moze byt aj bez casti ":2" odkazu ale musí byť aj default
        $rna = $row['node']->absolutna;
        if ($rna !== NULL) {
          $node->link = strpos($rna, 'http') !== FALSE ? $rna 
                                                      : (count($p = explode(" ", $rna)) == 2 ? $servise->link($p[0], ["id"=>$p[1]]) 
                                                                                              : $servise->link($p[0]));
        } else {
          $node->link = is_array($row['node']->link) ? $servise->link($row['node']->link[0], ["id"=>$row['node']->id]) 
                                                    : $servise->link($row['node']->link);
        }
        return $row['nadradena'] ? $row['nadradena'] : null;
      });
    }
    return $menu;
  }
    
  /** 
   * Naplnenie spolocnych udajov pre sablony */
  public function beforeRender() {
    $this->getComponent('menu')->selectByUrl($this->link('this'));
    $this->template->title = $this->udaje_webu['titulka'];
    $this->template->description = $this->udaje_webu['description'];
    $this->template->keywords = $this->udaje_webu['keywords'];
    $this->template->author = $this->udaje_webu['autor'];
    $this->template->copy = $this->udaje_webu['copy'];
		$this->template->verzia = $this->verzie->posledna();
		$this->template->urovregistr = $this->id_reg;
    $this->template->maxurovregistr = $this->max_id_reg;
    $this->template->language = $this->language;
    $this->template->user_admin = $this->user_main->findOneBy(['user_roles.role'=>'admin']);
    $this->template->user_spravca = $this->user_main->findOneBy(['user_roles.role'=>'manager']);
    $this->template->nazov_stranky = $this->nazov_stranky;
    $this->template->nastavenie = $this->nastavenie;
    $this->template->text_title_image = $this->texty_presentera->translate("base_text_title_image");
		$this->template->article_avatar_view_in = $this->nastavenie["article_avatar_view_in"];
    $this->template->omrvinky_enabled = $this->nastavenie["omrvinky_enabled"];
    $this->template->view_log_in_link_in_header = $this->nastavenie['user_panel']["view_log_in_link_in_header"];
    $this->template->fa = [
      'success' => 'far fa-check-circle',
      'warning' => 'fas fa-exclamation-triangle',
      'info'    => 'fas fa-info-circle',
      'danger'  => 'fas fa-exclamation-circle',
    ];
    $this->template->setTranslator($this->texty_presentera);
    $servise = $this;
    $this->template->addFilter('obr_v_txt', function ($text) use($servise){
      $rozloz = explode("#", $text);
      $serv = $servise->presenter;
      $vysledok = '';
      $cesta = 'http://'.$serv->nazov_stranky."/";
      foreach ($rozloz as $k=>$cast) {
        if (substr($cast, 0, 2) == "I-") {
          $obr = $serv->dokumenty->find((int)substr($cast, 2));
          if ($obr !== FALSE) {
            $cast = Html::el('a class="fotky" rel="fotky"')->href($cesta.$obr->subor)->title($obr->nazov)
                                  ->setHtml(Html::el('img')->src($cesta.$obr->thumb)->alt($obr->nazov));
          }
        }
        $vysledok .= $cast;
      }
      return $vysledok;
    });
    $this->template->addFilter('sponzor', function ($text) use($servise){
      $rozloz = explode("#", $text);
      $serv = $servise->presenter;
      $vysledok = '';
      $cesta = 'http://'.$serv->nazov_stranky."/";
      foreach ($rozloz as $k=>$cast) {
        if (substr($cast, 0, 2) == "I-") {
          $obr = $serv->dokumenty->find((int)substr($cast, 2));
          if ($obr !== FALSE) {
            $cast = Html::el('img', ['class'=>'img-center img-responsive'])->src($cesta.$obr->thumb)->alt($obr->nazov);
          }
        }
        $vysledok .= $cast;
      }
      return $vysledok;
    });
    $this->template->addFilter('koncova_znacka', function ($text) use($servise){
      $rozloz = explode("{end}", $text);
      $vysledok = $text;
      if (count($rozloz)>1) {    //Ak som nasiel znacku
        $vysledok = $rozloz[0].\Nette\Utils\Html::el('a class="cely_clanok"')->href($servise->link("this"))->title($servise->texty_presentera->translate("base_title"))
                ->setHtml('&gt;&gt;&gt; '.$servise->texty_presentera->translate("base_viac")).'<div class="ostatok">'.$rozloz[1].'</div>';
      }
      return $vysledok;
    });
    $this->template->addFilter('hlmenuclass', function ($id, $id_user_roles, $hl_udaje) {
      $polozka_class = $id_user_roles>2 ? 'adminPol' : '';
      //TODO $classPol .= ' zvyrazni';
      if ($id == $hl_udaje) { $polozka_class .= ' active'; }
      return $polozka_class;
    });
    $this->template->addFilter('nahodne', function ($max) { //Generuje nahodne cislo do template v rozsahu od 0 do max
      return (int)rand(0, $max);
    });
    $this->template->addFilter('uprav_email', function ($email) { //Upravi email aby sa nedal pouzit ako nema

      return Strings::replace($email, ['~@~' => '[@]', '~\.~' => '[dot]']);
    });
    $this->template->addFilter('textreg', function ($text, $id_user_roles, $max_id_reg) {
      for ($i = $max_id_reg; $i>=0; $i--) {
        $z_zac = "#REG".$i."#"; //Pociatocna znacka
        $z_alt = "#REG-A".$i."#"; //Alternativna znacka
        $z_kon = "#/REG".$i."#";//Koncova znacka
        if (($p_zac = strpos($text, $z_zac)) !== FALSE && ($p_kon = strpos($text, $z_kon)) !== FALSE && $p_zac < $p_kon) { //Ak som našiel začiatok a koniec a sú v správnom poradí
          $text = substr($text, 0, $p_zac) //Po zaciatocnu zancku
                  .(($p_alt = strpos($text, $z_alt)) === FALSE ? // Je alternativa
                    ($i < $id_user_roles ? substr($text, $p_zac+strlen($z_zac), $p_kon-$p_zac-strlen($z_zac)) : '') : // Bez alternativy
                    ($i < $id_user_roles ? substr($text, $p_zac+strlen($z_zac), $p_alt-$p_zac-strlen($z_zac)) : substr($text, $p_alt+strlen($z_alt), $p_kon-$p_alt-strlen($z_alt))))// S alternativou
                  .substr($text, $p_kon+strlen($z_kon)); //Od koncovej znacky
        } 
      }
      return $text;
    });
    $this->template->addFilter('vytvor_odkaz', function ($row) use($servise){
      return isset($row->absolutna) ? $row->absolutna :
                          (isset($row->spec_nazov) ? $servise->link($row->druh->presenter.':default',$row->spec_nazov)
                                                  : $servise->link($row->druh->presenter.':default'));
    });
    $this->template->addFilter('menu_mutacia_nazov', function ($id) use($servise){
      $pom = $servise->hlavne_menu_lang->findOneBy(['id_hlavne_menu'=>$id, 'id_lang'=>$servise->language_id]);
      return $pom !== FALSE ? $pom->nazov : $id;
    });
    $this->template->addFilter('menu_mutacia_title', function ($id) use($servise){
      $pom = $servise->hlavne_menu_lang->findOneBy(['id_hlavne_menu'=>$id, 'id_lang'=>$servise->language_id]);
      return $pom !== FALSE ? ((isset($pom->view_name) && strlen ($pom->view_name)) ? $pom->view_name : $pom->menu_name) : $id;
    });
    $this->template->addFilter('menu_mutacia_h1part2', function ($id) use($servise){
      $pom = $servise->hlavne_menu_lang->findOneBy(['id_hlavne_menu'=>$id, 'id_lang'=>$servise->language_id]);
      return $pom !== FALSE ? $pom->h1part2 : $id;
    });
    $this->template->addFilter('nadpisH1', function ($key){
      $out = "";
      foreach (explode(" ", $key) as $v) {
        $out .= "<div>".$v." </div>";
      }
      return $out;
    });
            
    $this->texy->allowedTags = TRUE;
    $this->texy->headingModule->balancing = "FIXED";
    $this->template->addFilter('texy', [$this->texy, 'process']);
	}
  
  /** 
   * Akcia pre odhlasenie - spolocna pre vsetky presentery */
	public function actionSignOut(): void {
		$this->getUser()->logout(TRUE);
    $this->id_reg = 0;    
		$this->flashRedirect('Homepage:', $this->texty_presentera->translate('base_log_out_mess'), 'success');
	}

  /** 
   * Signal prepinania jazykov
   * @param string $language skratka noveho jazyka */
  public function handleSetLang(string $language) {
    if ($this->language != $language) { //Cokolvek rob len ak sa meni
      //Najdi v DB pozadovany jazyk
      $la_tmp = $this->lang->findOneBy(['skratka'=>$language]);
      //Ak existuje tak akceptuj
      if (isset($la_tmp->skratka) && $la_tmp->skratka == $language) { $this->language = $language; }
    }
    $this->redirect('this');
	}
  
  /** 
   * Komponenta pre výpis css a js súborov
   * @return \PeterVojtech\Base\CssJsFilesControl */
  /*public function createComponentFiles() {
    return new PeterVojtech\Base\CssJsFilesControl($this->nastavenie['web_files'], $this->name, $this->action);
  }*/
  
  /**
   * Vytvorenie komponenty pre menu uzivatela a zaroven panel jazykov
   * @return \App\FrontModule\Components\User\UserLangMenu */
  public function createComponentUserLangMenu() {
    $ulm = $this->userLangMenuControlFactory->create();
    $ulm->setLanguage($this->language)->setStoreRequest($this->storeRequest());
    return $ulm;
  }
  
  /**
   * Vytvorenie komponenty pre doplňovanie pri vyhľadávaní
   * @return \App\FrontModule\Components\Autocomplete\AutocompleteControl */
  public function createComponentAutocomplete() {
    $autocomplete = $this->autocompleteControlFactory->create();
    $autocomplete->setLanguage($this->language);
    return $autocomplete;
  }

  /** 
   * Vytvorenie komponenty pre potvrdzovaci dialog
   * @return Nette\Application\UI\Form */
  public function createComponentConfirmForm() {
    $form = new PeterVojtech\Confirm\ConfirmationDialog($this->getSession('news'));
    $form->addConfirmer(
        'delete', // názov signálu bude confirmDelete!
        [$this, 'confirmedDelete'], // callback na funkciu pri kliknutí na YES
        [$this, 'questionDelete'] // otázka
    );
    return $form;
  }
  
  /**
   * Zostavenie otázky pre ConfDialog s parametrom
   * @param Nette\Utils\Html $dialog
   * @param array $params
   * @return string $question */
  public function questionDelete($dialog, $params) {
    $dialog->getQuestionPrototype();
    return sprintf($this->texty_presentera->translate('base_delete_text'),
                    isset($params['zdroj_na_zmazanie']) ? $params['zdroj_na_zmazanie'] : "položku",
                    isset($params['nazov']) ? $params['nazov'] : '');
  }
  
  /** 
   * Vytvorenie komponenty slideru
   * @return \App\FrontModule\Components\Slider\Slider */
	public function createComponentSlider() {
    return $this->sliderControlFactory->create();
	}
  
  /** 
   * Komponenta pre zobrazenie clanku
   * @return Multiplier */
  public function createComponentUkazClanok() {
    $servise = $this;
		return new Multiplier(function ($id) use ($servise) {
      try {
        if (is_numeric($id)) {
          $clanok = $servise->hlavne_menu_lang->getOneArticleId($id, $servise->language_id, 0);
        } else {
          $clanok = $servise->hlavne_menu_lang->getOneArticleSp($id, $servise->language_id, 0);
        }
      } catch (DbTable\ArticleMainMenuException $th) {
        return;
      }
      $ukaz_clanok = $servise->zobrazClanokControlFactory->create();
      $ukaz_clanok->setArticle($clanok)
                  ->setLanguage($servise->language)
                  ->setClanokHlavicka($servise->udaje_webu['clanok_hlavicka']);
      if ($clanok->hlavne_menu->nazov_ul_sub !== null) {$ukaz_clanok->setClanokTemplate($clanok->hlavne_menu->nazov_ul_sub);}
      return $ukaz_clanok;
    });
  }
  
  /** 
   * Vytvorenie komponenty pre vypisanie aktualnych oznamov
   * @return \App\FrontModule\Components\Oznam\AktualneOznamyControl */
	public function createComponentAktualne() {
    $aktualne = $this->aktualneOznamyControlFactory->create();
    $aktualne->setNastavenie($this->context->parameters['oznam']);
    return $aktualne;
	}
  
  /** 
   * Komponenta pre zobrazenie aktualnych clankov 
   * @return \App\FrontModule\Components\Clanky\AktualneClankyControl */
  public function createComponentAktualneClanky() {
    $aktualne_clanky = $this->aktualneClankyControlFactory->create();
    $aktualne_clanky->setLanguage($this->language);

		return $aktualne_clanky;
  }
  
  /** 
   * Komponenta pre zobrazenie noviniek
   * @return \App\FrontModule\Components\News\INewsControl */
  public function createComponentNews() {
    $news = $this->newsControlFactory->create();
    $news->setLanguage($this->language);
    return $news;
  }
  
  /** Komponenta pre vypis kontaktneho formulara
   * @return \App\FrontModule\Components\User\KontaktControl */
	public function createComponentKontakt() {
    $spravca = $this->user_main->findOneBy(["user_roles.role" => "manager"]);
		$kontakt = $this->kontaktControlFactory->create();
    $kontakt->setNastav($this->language)
            ->setEmailsToSend($spravca->email)
            ->setNazovStranky($this->nazov_stranky);
		return $kontakt;	
	}
  /** 
   * Formular pre prihlasenie uzivatela.
   * @return Nette\Application\UI\Form */
  protected function createComponentSignInForm() {
    $form = $this->signInForm->create($this->language);
    $servise = $this;
    $form['login']->onClick[] = function ($form) use ($servise) {
      $er_txt = $servise->texty_presentera->translate('base_login_error');
      $servise->restoreRequest($servise->backlink);
      $servise->flashOut(!count($form->errors), 'Homepage:', 
                          $servise->texty_presentera->translate('base_login_ok'), 
                          sprintf($er_txt, isset($form->errors[0]) ? $servise->texty_presentera->translate('base_Log_In_Error_'.$form->errors[0]) : 'Ch'));
    };
    $form['forgottenPassword']->onClick[] = function () {
      $this->redirect('User:forgottenPassword');
    };
    return $form;
  }
  
  /** Funkcia pre zjednodusenie vypisu flash spravy a presmerovania
   * @param array|string $redirect Adresa presmerovania
   * @param string $text Text pre vypis hlasenia
   * @param string $druh - druh hlasenia */

  public function flashRedirect($redirect, $text = "", $druh = "info") {
		$this->flashMessage($text, $druh);
    if (is_array($redirect)) {
      if (count($redirect) > 1) {
        if (!$this->isAjax()) {
          $this->redirect($redirect[0], $redirect[1]);
        } else {
          $this->redrawControl();
        }
      } elseif (count($redirect) == 1) { $this->redirect($redirect[0]);}
    } else { 
      if (!$this->isAjax()) { 
        $this->redirect($redirect); 
      } else {
        $this->redrawControl();
      }
    }
	}
  /**
   * Funkcia pre zjednodusenie vypisu flash spravy a presmerovania aj pre chybovy stav
   * @param boolean $ok Podmienka
   * @param array|string $redirect Adresa presmerovania
   * @param string $textOk Text pre vypis hlasenia ak je podmienka splnena
   * @param string $textEr Text pre vypis hlasenia ak NIE je podmienka splnena  */

  public function flashOut($ok, $redirect, $textOk = "", $textEr = "") {
    if ($ok) {
      $this->flashRedirect($redirect, $textOk, "success");
    } else {
      $this->flashMessage($textEr, 'danger');
    }
  }
  
  /**
   * Uprava vzhladu formularov
   * @param \Nette\Application\UI\Form $form Formular
   * @param string $form_class Doplnkovy class pre tag form
   * @return \Nette\Application\UI\Form */
  public function _vzhladForm($form, $form_class = "") {
    $form->getElementPrototype()->class('form-horizontal'.(strlen($form_class)?" ".$form_class:""));
    $renderer = $form->getRenderer();
    $renderer->wrappers['error']['container'] = 'div class="row"';
    $renderer->wrappers['error']['item'] = 'div class="col-md-6 col-md-offset-3 alert alert-danger"';
    $renderer->wrappers['controls']['container'] = NULL;
    $renderer->wrappers['pair']['container'] = 'div class=form-group';
    $renderer->wrappers['pair']['.error'] = 'has-error';
    $renderer->wrappers['pair']['container'] = 'div class="form-group row"';
    $renderer->wrappers['label']['container'] = 'div class="col-12 col-sm-3 control-label"';
    $renderer->wrappers['control']['container'] = 'div class="col-12 col-sm-9 control-field"';
    $renderer->wrappers['control']['description'] = 'span class="help-block alert alert-info"';
    $renderer->wrappers['control']['errorcontainer'] = 'span class="help-block alert alert-danger"';
    // make form and controls compatible with Twitter Bootstrap
    
    foreach ($form->getControls() as $control) {
      if ($control instanceof Controls\Button) {
        $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
        $usedPrimary = TRUE;
      } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
        $control->getControlPrototype()->addClass('form-control');
      } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
        $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
      }
    }
    return $form;
  }
}
