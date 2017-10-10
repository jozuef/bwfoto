<?phpnamespace App\AdminModule\Components\Article\TitleArticle;use DbTable;use Nette\Application\UI\Form;/** * Formular a jeho spracovanie pre zmenu sablony polozky. * Posledna zmena 03.10.2017 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.0 */class ZmenSablonuFormFactory {  /** @var DbTable\Hlavne_menu */	private $hlavne_menu;    /** @var DbTable\Hlavne_menu_template */	private $hlavne_menu_template;    /**   * @param DbTable\Hlavne_menu $hlavne_menu   * @param DbTable\Hlavne_menu_template $hlavne_menu_template */  public function __construct(DbTable\Hlavne_menu $hlavne_menu, DbTable\Hlavne_menu_template $hlavne_menu_template) {		$this->hlavne_menu = $hlavne_menu;    $this->hlavne_menu_template = $hlavne_menu_template;	}    /**   * Formular.   * @param int $id Id polozky v hlavnom menu   * @param int $id_hlavne_menu_template Id sucasnej sablony   * @return Nette\Application\UI\Form */    public function create($id, $id_hlavne_menu_template)  {		$form = new Form();		$form->addProtection();    $form->addHidden("id", $id);    $form->addRadioList('id_hlavne_menu_template', 'Nová šablóna:', $this->hlavne_menu_template->formPairs())         ->setDefaultValue($id_hlavne_menu_template);    $form->addSubmit('uloz', 'Zmeň')         ->setAttribute('class', 'btn btn-success')         ->onClick[] = [$this, 'changeFormSubmitted'];    $form->addSubmit('cancel', 'Cancel')         ->setAttribute('class', 'btn btn-default')         ->setAttribute('data-dismiss', 'modal')         ->setAttribute('aria-label', 'Close')         ->setValidationScope(FALSE);		return $form;	}    /**    * Spracovanie formulara.   * @param Nette\Forms\Controls\SubmitButton $button Data formulara */  public function changeFormSubmitted($button) {		$values = $button->getForm()->getValues();    try {			$this->hlavne_menu->changeTemplate($values);		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}  }}