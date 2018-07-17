<?phpnamespace App\FrontModule\Components\Clanky;use DbTable;use Language_support;use Nette;/** * Komponenta pre zobrazenie aktualnych projektov pre FRONT modul *  * Posledna zmena(last change): 31.07.2017 * * @author Ing. Peter VOJTECH ml <petak23@gmail.com> * @copyright Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link http://petak23.echo-msz.eu * @version 1.0.3 * */class AktualneClankyControl extends Nette\Application\UI\Control {  /** @var DbTable\Hlavne_menu_lang $hlavne_menu_lang */  private $hlavne_menu_lang;  /** @var Nette\Security\User $user*/  private $user;  /** @var string $avatar_path  Cesta k titulnemu obrazku clanku */  private $avatar_path = "";  /** @var Language_support\Clanok */  private $texts;  /** @var Nette\Database\Table\Selection $prilohy Prilohy k clanku */  private $prilohy;  /** @var int $language_id Id jazyka  */  private $language_id;  /**   * @param DbTable\Hlavne_menu_lang $hlavne_menu_lang   * @param DbTable\Dokumenty $dokumenty   * @param Nette\Security\User $user   * @param Language_support\Clanky $texts */  public function __construct(DbTable\Hlavne_menu_lang $hlavne_menu_lang, DbTable\Dokumenty $dokumenty, Nette\Security\User $user, Language_support\Clanky $texts) {    parent::__construct();    $this->hlavne_menu_lang = $hlavne_menu_lang;    $this->user = $user;    $this->prilohy = $dokumenty->findAll();    $this->texts = $texts;  }    /**    * Nastavenie jazyka    * @param int|string $language_id jazyk    * @return \App\FrontModule\Components\Clanky\AktualnyProjektControl */  public function setLanguage($language_id) {    $this->language_id = $language_id;    $this->texts->setLanguage($language_id);    return $this;  }  /**    * Nastavenie cesty k obrazku   * @param string $avatar_path Cesta k titulnemu obrazku clanku   * @return \App\FrontModule\Components\Clanky\AktualnyProjektControl */  public function setAvatarPath($avatar_path) {    $this->avatar_path = $avatar_path;    return $this;  }    /**    * Render funkcia pre vypisanie odkazu na clanok    * @param array $p      * @see Nette\Application\Control#render() */  public function render($p) {     $this->template->setFile(__DIR__ . '/AktualneClanky.latte');    $this->template->aktuality = $this->hlavne_menu_lang->findBy([                                  "hlavne_menu.datum_platnosti >= '".StrFTime("%Y-%m-%d",strtotime("0 day"))."'",                                  "hlavne_menu.id_user_roles <= ".($this->user->isLoggedIn()) ? ($this->user->getIdentity() === NULL ? 0 : $this->user->getIdentity()->id_user_roles) : 0,                                  "hlavne_menu.id_nadradenej = ".$p["id"],                                  ])->order('datum_platnosti DESC');    $this->template->avatar_path = $this->avatar_path;    $this->template->texts = $this->texts;    $this->template->prilohy = $this->prilohy;    $this->template->render();  }    protected function createTemplate($class = NULL) {    $servise = $this;    $template = parent::createTemplate($class);    $template->addFilter('koncova_znacka', function ($text) use($servise){      $rozloz = explode("{end}", $text);      $vysledok = $text;			if (count($rozloz)>1) {		 //Ak som nasiel znacku				$vysledok = $rozloz[0].\Nette\Utils\Html::el('a class="cely_clanok"')->href('#')->title($servise->texts->trText('base_view_all'))                ->setHtml('&gt;&gt;&gt; '.$servise->texts->trText('base_viac')).'<div class="ostatok">'.$rozloz[1].'</div>';			}      return $vysledok;    });    return $template;	}}interface IAktualneClankyControl {  /** @return AktualneClankyControl */  function create();}