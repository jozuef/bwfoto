<?phpnamespace App\AdminModule\Components\Clanky\PrilohyClanok;use DbTable;use Nette\Application\UI\Form;use Nette\Database;use Nette\Security\User;use Nette\Utils\Strings;use Nette\Utils\Image;/** * Formular a jeho spracovanie pre pridanie a editaciu prilohy polozky. * Posledna zmena 16.01.2018 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2018 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.9 */class EditPrilohyFormFactory {    /** @var DbTable\Dokumenty */	private $dokumenty;  /** @var string */  private $prilohy_adresar;  /** @var array */  private $prilohy_images;  /** @var int */  private $id_user_main;  /** @var string */  private $wwwDir;  /**   * @param DbTable\Dokumenty $dokumenty   * @param User $user   * @param string $wwwDir  */  public function __construct(DbTable\Dokumenty $dokumenty, User $user, $wwwDir = "") {    $this->dokumenty = $dokumenty;    $this->id_user_main = $user->getId();    $this->wwwDir = $wwwDir;	}    /**   * Formular pre pridanie prilohy a editaciu polozky.   * @param int $upload_size   * @param string $prilohy_adresar   * @param array $prilohy_images   * @return Form  */  public function create($upload_size, $prilohy_adresar, $prilohy_images)  {    $this->prilohy_adresar = $prilohy_adresar;    $this->prilohy_images = $prilohy_images;    $form = new Form();		$form->addProtection();    $form->addHidden("id");$form->addHidden("id_hlavne_menu");$form->addHidden("id_user_roles");    $form->addRadioList('type', 'Typ prílohy:', [1=>"Iné", 2=>"Obrázok", 3=>"Video"])         ->setDefaultValue(1)         ->addCondition(Form::NOT_EQUAL, 2)          ->toggle("thumb_field");    $form->addUpload('priloha', 'Pridaj prílohu')         ->setOption('description', sprintf('Max veľkosť prílohy v bytoch %s kB', $upload_size/1024))         ->setRequired(FALSE)         ->addCondition(Form::FILLED)          ->addRule(Form::MAX_FILE_SIZE, 'Max veľkosť prílohy v bytoch %d B', $upload_size)         ->endCondition()         ->addConditionOn($form['type'], Form::EQUAL, 2)          ->addRule(Form::IMAGE, 'Príloha musí byť obrázok!')         ->endCondition();    $form->addText('name', 'Nadpis prílohy:', 55, 255)         ->setOption('description', sprintf('Nadpis by mal mať aspoň %s znakov. Inak nebude akceptovaný a bude použitý názov súboru!', 2));    $form->addUpload('thumb', 'Pridaj náhľadový obrázok:')         ->setOption('id', 'thumb_field')         ->setHtmlAttribute('accept', 'image/*')         ->setRequired(FALSE)         ->addRule(Form::IMAGE, 'Náhľadový obrázok musí byť obrázok!');    $form->addText('description', 'Podrobnejší popis prílohy:', 55, 255)         ->setOption('description', sprintf('Popis by mal mať aspoň %s znakov. Inak nebude akceptovaný!', 2));		$form->addSubmit('uloz', 'Ulož')         ->setAttribute('class', 'btn btn-success')         ->onClick[] = [$this, 'editPrilohaFormSubmitted'];    $form->addSubmit('cancel', 'Cancel')         ->setAttribute('class', 'btn btn-default')         ->setAttribute('data-dismiss', 'modal')         ->setAttribute('aria-label', 'Close')         ->setValidationScope(FALSE);		return $form;	}    /**    * Spracovanie formulara pre pridanie a editaciu prilohy polozky.   * @param Nette\Forms\Controls\SubmitButton $button Data formulara    * @throws Database\DriverException   */  public function editPrilohaFormSubmitted($button) {		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara    try {      $uloz = [         'id_hlavne_menu'	 	=> $values->id_hlavne_menu,        'id_user_main'      => $this->id_user_main,        'id_user_roles'     => $values->id_user_roles,        'description'				=> isset($values->description) && strlen($values->description)>2 ? $values->description : NULL,        'change'						=> StrFTime("%Y-%m-%d %H:%M:%S", Time()),        'type'              => $values->type      ];      $nazov = isset($values->name) ? $values->name : "";      if ($values->priloha && $values->priloha->name != "") { //Ak nahravam prilohu        $priloha_info = $this->_uploadPriloha($values);        $uloz = array_merge($uloz, [          'name'				=> strlen($nazov)>2 ? $nazov : $priloha_info['finalFileName'],          'web_name'  	=> Strings::webalize($priloha_info['finalFileName']),          'pripona'			=> $priloha_info['pripona'],          'main_file'		=> $this->prilohy_adresar.$priloha_info['finalFileName'].".".$priloha_info['pripona'],          'thumb_file'	=> $priloha_info['thumb'],          'type'        => $priloha_info['is_image'] ? 2 : $values->type    		]);      } elseif ($values->thumb && $values->thumb->name != "" && $values->thumb->isImage()) { //Ak nahravam len nahlad        $uloz = array_merge($uloz, ['thumb_file'	=> $this->_uploadThumb($values)]);      }  else { //Ak len menim        $uloz = array_merge($uloz, ['name' => strlen($nazov)>2 ? $nazov : ""]);      }      $vysledok = $this->dokumenty->uloz($uloz, $values->id);      if (!empty($vysledok) && isset($priloha_info['is_image']) && $priloha_info['is_image']) { $this->dokumenty->oprav($vysledok['id'], ['znacka'=>'#I-'.$vysledok['id'].'#']);}		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}  }    /**   * Upload prilohy   * @param \Nette\Http\FileUpload $values   * @return array */  private function _uploadPriloha($values) {    $pr = $this->dokumenty->find($values->id);//Zmazanie starej prílohy    if ($pr !== FALSE) {      if (is_file($pr->main_file)) { unlink($this->wwwDir."/".$pr->main_file);}      if (is_file($pr->thumb_file)) { unlink($this->wwwDir."/".$pr->thumb_file);}    }    $fileName = $values->priloha->getSanitizedName();		$pi = pathinfo($fileName);//		$file = $pi['filename'];//		$ext = $pi['extension'];		$additionalToken = 0;		//Najdi meno suboru		if (file_exists($this->prilohy_adresar.$fileName)) {			do { $additionalToken++;			} while (file_exists($this->prilohy_adresar.$pi['filename'].$additionalToken.".".$pi['extension']));    }		$finalFileName = ($additionalToken == 0) ? $pi['filename'] : $pi['filename'].$additionalToken;		//Presun subor na finalne miesto a ak je to obrazok tak vytvor nahlad		$values->priloha->move($this->prilohy_adresar.$finalFileName.".". $pi['extension']);		if ($values->priloha->isImage()) {			$image_name = $this->prilohy_adresar.$finalFileName.".". $pi['extension'];			$thumb_name = $this->prilohy_adresar.'tb_'.$finalFileName.".". $pi['extension'];			$image = Image::fromFile($image_name);      $image->resize($this->prilohy_images['x'], $this->prilohy_images['y'], Image::SHRINK_ONLY);      $image->save($image_name, $this->prilohy_images['kvalita']);			copy($image_name, $thumb_name);			$thumb = Image::fromFile($thumb_name);			$thumb->resize($this->prilohy_images['tx'], $this->prilohy_images['ty'], Image::SHRINK_ONLY);// | Image::EXACT			$thumb->save($thumb_name, $this->prilohy_images['tkvalita']);    } else {      if (isset($values->thumb) && $values->thumb->name != "" && $values->thumb->isImage()) {         $thumbInfo = pathinfo($values->thumb->name);        $thumb_name = $this->prilohy_adresar.'tb_'.$finalFileName.".".$thumbInfo['extension'];         $values->thumb->move($thumb_name);        $thumb = Image::fromFile($thumb_name);        $thumb->resize($this->prilohy_images['tx'], $this->prilohy_images['ty'], Image::SHRINK_ONLY);// | Image::EXACT        $thumb->save($thumb_name, $this->prilohy_images['tkvalita']);      }    }  		return [			'finalFileName' => $finalFileName,			'pripona'				=> $pi['extension'],			'thumb'					=> isset($thumb_name) ? $thumb_name : NULL,      'is_image'      => $values->priloha->isImage()  		];  }    /**   * Upload nahladu   * @param \Nette\Http\FileUpload $values   * @return array */  private function _uploadThumb($values) {    $pr = $this->dokumenty->find($values->id);//Zmazanie starej prílohy    if ($pr !== FALSE) {      if (is_file($pr->thumb_file)) { unlink($this->wwwDir."/".$pr->thumb_file);}    }    $main_file = pathinfo($pr->main_file);    $additionalToken = 0;		//Najdi meno suboru		if (file_exists($this->prilohy_adresar.'tb_'.$main_file['filename'].'.jpg')) {			do { $additionalToken++;			} while (file_exists($this->prilohy_adresar.'tb_'.$main_file['filename'].$additionalToken.".jpg"));    }		$finalFileName = 'tb_'.$main_file['filename'].($additionalToken == 0 ? '' : $additionalToken).".jpg";    $values->thumb->move($this->prilohy_adresar.$finalFileName);    $thumb = Image::fromFile($this->prilohy_adresar.$finalFileName);    $thumb->resize($this->prilohy_images['tx'], $this->prilohy_images['ty'], Image::SHRINK_ONLY);// | Image::EXACT    $thumb->save($this->prilohy_adresar.$finalFileName, $this->prilohy_images['tkvalita']);  		return $this->prilohy_adresar.$finalFileName;  }}