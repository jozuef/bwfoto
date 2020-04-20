<?phpnamespace App\AdminModule\Components\Article\TitleArticle;use Nette\Application\UI\Form;use Nette\Utils\Html;use DbTable;/** * Formular a jeho spracovanie pre zmenu datumu platnosti polozky. * Posledna zmena 23.01.2019 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2019 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.2 */class ZmenDatumPlatnostiFormFactory {  /** @var DbTable\Hlavne_menu */	private $hlavne_menu;    /**   * @param DbTable\Hlavne_menu $hlavne_menu */  public function __construct(DbTable\Hlavne_menu $hlavne_menu) {		$this->hlavne_menu = $hlavne_menu;	}    /**   * Formular pre zmenu datumu platnosti polozky.   * @param int $id Id polozky v hlavnom menu   * @param Nette\Utils\DateTime $datum_platnosti Datum platnosti polozky   * @return Nette\Application\UI\Form */    public function create($id, $datum_platnosti)  {		$form = new Form();		$form->addProtection();    $form->addHidden("id", $id);    $form->addGroup();    $form->addCheckbox('platnost', ' Sledovanie aktuálnosti článku')         ->setDefaultValue(isset($datum_platnosti) ? 1 : 0)         ->setOption('description', 'Zaškrtnutím sa otvorí pole, v ktorom je možné zadať do kedy je článok aktuálny. Po tomto dátume sa už na webe článok nezobrazí. Bude viditeľný len v administrácii!')         ->addCondition(Form::EQUAL, TRUE)         ->toggle("platnost-i", TRUE);    $form->addGroup()->setOption('container', Html::el('fieldset')->id("platnost-i"));		$form->addText('datum_platnosti', 'Dátum platnosti')         ->setAttribute('class', 'datepicker')         ->setDefaultValue($datum_platnosti)         ->addConditionOn($form['platnost'], Form::EQUAL, TRUE)         ->addRule(Form::FILLED, 'Je nutné vyplniť dátum platnosti!');		$form->addGroup();    $form->addSubmit('uloz', 'Zmeň')         ->setAttribute('class', 'btn btn-success')         ->onClick[] = [$this, 'zmenDatumPlatnostiFormSubmitted'];    $form->addSubmit('cancel', 'Cancel')         ->setAttribute('class', 'btn btn-default')         ->setAttribute('data-dismiss', 'modal')         ->setAttribute('aria-label', 'Close')         ->setValidationScope(FALSE);		return $form;	}    /**    * Spracovanie formulara pre zmenu vlastnika clanku.   * @param Nette\Forms\Controls\SubmitButton $button Data formulara */  public function zmenDatumPlatnostiFormSubmitted($button) {		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara    try {			$this->hlavne_menu->zmenDatumPlatnosti($values);		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}  }}