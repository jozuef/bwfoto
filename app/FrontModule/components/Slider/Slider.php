<?phpnamespace App\FrontModule\Components\Slider;use Nette\Application\UI\Control;use DbTable;/**  * Komponenta pre vykreslenie slider-u. *Posledna zmena(last change): 13.10.2017 * * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2013 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version 1.0.8 */class SliderControl extends Control {  	/** @var DbTable\Slider */	private $slider;      	/** @var array */	private $nastavenie;  /**   * @param DbTable\Slider $slider */  public function __construct(DbTable\Slider $slider) {    parent::__construct();    $this->slider = $slider;	}    /**   * Nastavenie slider-u   * @param array $nastavenie   * @return \App\FrontModule\Components\Slider\SliderControl */  public function setNastavenie($nastavenie) {    $this->nastavenie = $nastavenie;    return $this;  }  /** Render */	public function render() {    $this->template->setFile(__DIR__ . '/Slider_'.$this->nastavenie["varianta"].'.latte');    $p_name = explode(":", $this->presenter->name);    $id_pre_zobrazenie = ($p_name[1] == "Clanky") ? (isset($this->presenter->params['id']) ? $this->presenter->params['id'] : 0) : 0;        if ($this->nastavenie["varianta"] == 5) {      $menu = $this->presenter->getComponent('menu')->getPath();      foreach ($menu as $p) {        if ($p->id == $id_pre_zobrazenie) { $id_pre_zobrazenie = $menu[1]->id; };      }       $this->template->slider = $this->_findIn($id_pre_zobrazenie)->limit(1)->fetch();    } elseif ($this->nastavenie["varianta"] > 0 && $this->nastavenie["varianta"] < 5) { //Varianty 1 - 4      $this->template->slider = $this->_findIn($id_pre_zobrazenie)->fetchAll();    } else { //Varianta 0      $s = $this->slider->getSlider();      $this->template->slider = $s->limit(1,  (int)rand(0, count($s)-1))->fetch();    }        $this->template->id_pre_zobrazenie = $id_pre_zobrazenie;    $this->template->nastavenie = $this->nastavenie;    $this->template->p_name = $p_name;    $this->template->render();	}    /**   * Najdenie poloziek slidera   * @param int $id_pre_zobrazenie   * @return \Nette\Database\Table\Selection */  private function _findIn($id_pre_zobrazenie) {    $p_name = explode(":", $this->presenter->name);    $slider = $this->slider->getSlider('poradie ASC');    $slider_zobrazenie = $slider->fetchPairs("id", "zobrazenie");    $vysa = [];    foreach ($slider_zobrazenie as $k => $v) {      $vy = [];      $vy[$k] = strpos($v, ",") !== FALSE ? explode(",", $v) : $v;      $vysledok = FALSE;      if (is_array($vy[$k])) {        foreach ($vy[$k] as $ke => $z) {          $vysledok = $this->_zisti($z, $p_name[1], $id_pre_zobrazenie) == TRUE ? TRUE : $vysledok;        }      } else {        $vysledok = $this->_zisti($vy[$k], $p_name[1], $id_pre_zobrazenie);      }      if ($vysledok == TRUE) { $vysa[] = $k;}    }//    dump($id_pre_zobrazenie);//    dump(count($vysa));die();    return ($id_pre_zobrazenie > 0 && count($vysa)) ? $slider->where('id', $vysa)->where('NOT zobrazenie', null) : $slider->where('id', $vysa);  }    /**    * Pre vyhodnotenie zobrazenia		* @param mix     $z  zobrazenie polozky		* @param string  $p  nazov presentera		* @param int     $i  id pre zobrazenie		* @return boolean   */  private function _zisti($z, $p, $i) {    return $z == NULL ? TRUE : ($z == 0 && $p == 'Homepage' ? TRUE : ($z > 0 && $z == $i ? TRUE : FALSE));  	}}interface ISliderControl {  /** @return SliderControl */  function create();}