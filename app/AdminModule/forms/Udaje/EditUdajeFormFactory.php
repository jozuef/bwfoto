<?phpnamespace App\AdminModule\Forms\Udaje;use DbTable;use Nette\Application\UI\Form;use Nette\Database;use Nette\Utils\Html;/** * Tovarnicka pre formular pre pridanie/editaciu udaja * Posledna zmena 31.10.2017 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.0 */class EditUdajeFormFactory {  /** @var DbTable\Udaje */  private $udaje;    private $ur_reg;  /** @param DbTable\Udaje $udaje  */  public function __construct(DbTable\Udaje $udaje) {		$this->udaje = $udaje;	}    /**   * Formular pre editaciu udajov   * @param boolean $admin   * @param array $druh   * @return Nette\Application\UI\Form   */  public function create($admin, $druh, $ur_reg)  {    $this->ur_reg = $ur_reg;    $form = new Form();		$form->addProtection();    $form->addGroup();    $form->addHidden('id');$form->addHidden('id_udaje_typ');    if ($admin) {      $form->addText('nazov', 'Názov prvku:', 20, 20)           ->addRule(Form::MIN_LENGTH, 'Názov musí mať spoň %d znaky!', 2)           ->setAttribute('class', 'heading')           ->setRequired('Názov musí byť zadaný!');      $form->addText('comment', 'Komentár k hodnote :', 90, 255)				 ->addRule(Form::MIN_LENGTH, 'Komentár musí mať spoň %d znaky!', 2)				 ->setRequired('Komentár musí byť zadaný!');    } else {      $form->addHidden('nazov');      $form->addHidden('comment');    }    $form->addText('text', 'Hodnota prvku:', 90, 255)				 ->setRequired('Hodnota prvku musí byť zadaná!');    if ($admin) {      $form->addCheckbox('spravca', ' Povolená zmena pre správcu')           ->setDefaultValue(1);      $form->addCheckbox("druh_null", " Hodnota druhu je NULL")           ->setDefaultValue(1)           ->addCondition(Form::EQUAL, TRUE)           ->toggle("druh", FALSE);      $form->addGroup()->setOption('container', Html::el('fieldset')->id("druh"));      $form->addSelect('id_druh', 'Druhová skupina pre nastavenia:', $druh)           ->setDefaultValue(1);      $form->setCurrentGroup(NULL);    }		$form->addSubmit('uloz', 'Ulož')         ->setAttribute('class', 'btn btn-success')         ->onClick[] = [$this, 'editUdajeFormSubmitted'];    $form->addSubmit('cancel', 'Cancel')->setAttribute('class', 'btn btn-default')         ->setValidationScope(FALSE);		return $form;	}    /** Spracovanie vstupov z formulara   * @param Nette\Forms\Controls\SubmitButton $button Data formulara   */	public function editUdajeFormSubmitted($button)	{    try {			$this->udaje->ulozUdaj($button->getForm()->getValues(), $this->ur_reg);		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}	}}