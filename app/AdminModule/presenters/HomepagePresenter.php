<?phpnamespace App\AdminModule\Presenters;use DbTable;/** * Domovský preseter administracie. * * Posledna zmena(last change): 05.10.2017 * *	Modul: ADMIN * * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version 1.0.6 */class HomepagePresenter extends BasePresenter {    // -- DB  /** @var DbTable\Hlavne_menu_cast @inject */  public $hlavne_menu_cast;    /** Vychodzi render    * @param int $id Id casti hlavneho menu ako zaporne cislo */  public function renderDefault($id) {    $user = $this->user;    $this->template->id_menu = -1*$id;    $this->template->view_add_link = $user->isAllowed('Admin:Menu', 'addpol') &&                                      ($user->isInRole("admin") ? TRUE :	$this->nastavenie['add_uroven0']) &&                                     ($id ? $this->id_reg >= $this->hlavne_menu_cast->find(-1*$id)->id_user_roles : FALSE);	}}