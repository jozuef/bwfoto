<?phpnamespace App\AdminModule\Components\Clanky;use DbTable;use Nette\Application\UI\Control;use Nette\Utils\Html;use Texy;/** * Komponenta pre zobrazenie konkretneho clanku * Posledna zmena(last change): 05.10.2018 * * @author Ing. Peter VOJTECH ml <petak23@gmail.com> * @copyright Copyright (c) 2012 - 2018 Ing. Peter VOJTECH ml. * @license * @link http://petak23.echo-msz.eu * @version 1.1.3 */class ZobrazClanokAControl extends Control {  /** @var DbTable\Hlavne_menu_lang */	public $hlavne_menu_lang;  /** @var int */  protected $id_hlavne_menu;  /** @var boolean $zobraz_anotaciu Zobrazenie anotacie polozky*/  private $zobraz_anotaciu;  /** @var Texy\Texy */	public $texy;  /**   * @param boolean $zobraz_anotaciu Povolenie zobrazenia anotacie - Nastavenie priamo cez servises.neon   * @param DbTable\Hlavne_menu_lang $hlavne_menu_lang   * @param Texy\Texy $texy */     public function __construct($zobraz_anotaciu, DbTable\Hlavne_menu_lang $hlavne_menu_lang, Texy\Texy $texy) {    parent::__construct();    $this->hlavne_menu_lang = $hlavne_menu_lang;    $this->texy = $texy;    $this->zobraz_anotaciu = $zobraz_anotaciu;  }    /** Nastavenie komponenty   * @param int $id_hlavne_menu   * @param boolean $zobraz_anotaciu */  public function setZobraz($id_hlavne_menu/*, $zobraz_anotaciu = FALSE*/) {    $this->id_hlavne_menu = $id_hlavne_menu;    return $this;  }    /** Render */  public function render() {    $this->template->setFile(__DIR__ . "/ZobrazClanok.latte");    $this->template->cl_texts = $this->hlavne_menu_lang->findBy(["id_hlavne_menu"=> $this->id_hlavne_menu]);    $this->template->zobraz_anotaciu = $this->zobraz_anotaciu;    $this->template->render();  }     protected function createTemplate($class = NULL) {    $servise = $this;    $template = parent::createTemplate($class);    $template->addFilter('obr_v_txt', function ($text) use($servise){      $rozloz = explode("#", $text);      $serv = $servise->presenter;      $vysledok = '';      $cesta = 'http://'.$serv->nazov_stranky."/";      foreach ($rozloz as $k=>$cast) {        if (substr($cast, 0, 2) == "I-") {          $obr = $serv->dokumenty->find((int)substr($cast, 2));					if ($obr !== FALSE) {            $cast = Html::el('img class="jslghtbx-thmb img-rounded noajax"')->src($cesta.$obr->thumb_file)                    ->alt($obr->name)->addAttributes([ 'data-jslghtbx' => $cesta.$obr->main_file, 'data-ajax'=>'false', 'data-jslghtbx-group'=>"mygroup1"]);					}        }        $vysledok .= $cast;      }      return $vysledok;    });    $template->addFilter('koncova_znacka', function ($text) use($servise){      $rozloz = explode("{end}", $text);      $vysledok = $text;			if (count($rozloz)>1) {		 //Ak som nasiel znacku				$vysledok = $rozloz[0].Html::el('a class="cely_clanok"')->href($servise->link("this"))->title("Zobrazenie celého článku")                ->setHtml('&gt;&gt;&gt; viac').'<div class="ostatok">'.$rozloz[1].'</div>';			}      return $vysledok;    });        $this->texy->allowedTags = TRUE;    $this->texy->headingModule->balancing = "FIXED";    $template->addFilter('texy', [$this->texy, 'process']);    return $template;	}}interface IZobrazClanokAControl {  /** @return ZobrazClanokAControl */  function create();}