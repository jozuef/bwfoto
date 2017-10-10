<?phpnamespace App\AdminModule\Presenters\Forms\Verzie;use Nette\Application\UI\Form;use DbTable;/** * Tovarnicka pre formular na editaciu verzie * Posledna zmena 18.09.2017 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.3 */class EditVerzieFormFactory {  /** @var DbTable\Verzie */	private $verzie;  /**   * @param DbTable\Verzie $verzie */  public function __construct(DbTable\Verzie $verzie) {		$this->verzie = $verzie;	}    /**   * Formular pre editaciu udajov verzie.   * @return Nette\Application\UI\Form */    public function create($send_e_mail_news = FALSE)  {    $form = new Form();		$form->addProtection();    $form->addGroup();    $form->addHidden("id");    $form->addHidden("id_user_main");		$form->addText('cislo', 'Číslo verzie:', 0, 80)         ->setAttribute('autofocus', 'autofocus')				 ->addRule(Form::FILLED, 'Číslo verzie musí byť zadané!');		$form->addText('subory', 'Zmenené súbory:', 0, 80);    if ($send_e_mail_news) {      $form->addCheckbox('posli_news', ' Posielatie NEWS o tejto aktualite');    } else {      $form->addHidden("posli_news", FALSE);    }		$form->addTextArea('text', 'Popis zmien:', 0, 15)->getControlPrototype()->class("texyla");		$form->addSubmit('uloz', 'Ulož')         ->setAttribute('class', 'btn btn-success')         ->onClick[] = [$this, 'editVerzieFormSubmitted'];    $form->addSubmit('cancel', 'Cancel')->setAttribute('class', 'btn btn-default')         ->setValidationScope(FALSE);		return $form;	}    /**    * Spracovanie vstupov z formulara   * @param Nette\Forms\Controls\SubmitButton $button Data formulara */	public function editVerzieFormSubmitted($button)	{    $values = $button->getForm()->getValues();    try {			$this->verzie->ulozVerziu($values);		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}	}}