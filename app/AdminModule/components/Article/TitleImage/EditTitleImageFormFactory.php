<?phpnamespace App\AdminModule\Components\Article\TitleImage;use Nette\Application\UI\Form;use Nette\Utils\Html;use DbTable;/** * Formular a jeho spracovanie pre pridanie a editaciu titulneho obrazku polozky. * Posledna zmena 15.11.2017 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.4 */class EditTitleImageFormFactory {  /** @var DbTable\Hlavne_menu */	private $hlavne_menu;  /** @var string */  private $avatar_path;  /** @var string */  private $www_dir;  /**   * @param DbTable\Hlavne_menu $hlavne_menu */  public function __construct(DbTable\Hlavne_menu $hlavne_menu) {		$this->hlavne_menu = $hlavne_menu;	}    /**   * Formular pre pridanie a editaciu titulneho obrazku polozky.   * @return Nette\Application\UI\Form */    public function create($avatar_path, $www_dir)  {    $this->avatar_path = $avatar_path;    $this->www_dir = $www_dir;    $form = new Form();		$form->addProtection();    $form->addHidden("id");    $form->addHidden("old_avatar");    $form->addGroup("Zvoľ čo ideš meniť:");    $form->addRadioList('vyber', 'Zmeň:', [1=>"Ikonku", 2=>"Obrázok"])         ->addCondition(Form::EQUAL, 1)          ->toggle("view_ikonka")         ->endCondition()         ->addCondition(Form::EQUAL, 2)          ->toggle("view_avatar");    $form->addGroup("Obrázok")->setOption('container', Html::el('fieldset id=view_avatar'));		$form->addUpload('avatar', 'Titulný obrázok')         ->setOption('description', sprintf('Max veľkosť obrázka v bytoch %d kB', 1024 * 1024/1000 /* v bytoch */))         ->setRequired(FALSE)         ->setHtmlAttribute('accept', 'image/*')         ->addRule(Form::MAX_FILE_SIZE, 'Max veľkosť obrázka v bytoch %d B', 1024 * 1024 /* v bytoch */)           ->addRule(Form::IMAGE, 'Titulný obrázok musí byť JPEG, PNG alebo GIF.');    $form->addGroup("Ikonka")->setOption('container', Html::el('fieldset id=view_ikonka'));    $form->addText('ikonka', 'Názov class ikonky pre FontAwesome:', 0, 30);    $form->addGroup("");    $form->addSubmit('uloz', 'Zmeň')         ->setAttribute('class', 'btn btn-success')         ->onClick[] = [$this, 'editTitleImageFormSubmitted'];    $form->addSubmit('cancel', 'Cancel')         ->setAttribute('class', 'btn btn-default')         ->setAttribute('data-dismiss', 'modal')         ->setAttribute('aria-label', 'Close')         ->setValidationScope(FALSE);		return $form;	}    /**    * Spracovanie formulara pre zmenu vlastnika clanku.   * @param Nette\Forms\Controls\SubmitButton $button Data formulara    * @throws Database\DriverException   */  public function editTitleImageFormSubmitted($button) {    try {      $this->hlavne_menu->zmenTitleImage($button->getForm()->getValues(), $this->avatar_path, $this->www_dir);		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}  }}