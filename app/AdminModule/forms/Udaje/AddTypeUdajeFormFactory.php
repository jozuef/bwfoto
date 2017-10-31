<?phpnamespace App\AdminModule\Forms\Udaje;use Nette\Application\UI\Form;/** * Tovarnicka pre formular pre volbu typu udaju pri pridavani * Posledna zmena 31.10.2017 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.0 */class AddTypeUdajeFormFactory {    public function create($udaje_typ_form)  {    $form = new Form();		$form->addProtection();    $form->addSelect('id_udaje_typ', 'Vyber typ údaju, ktorý chceš pridať:', $udaje_typ_form);    $form->addSubmit('uloz', 'Pokračuj')->setAttribute('class', 'btn btn-success');    $form->addSubmit('cancel', 'Cancel')->setAttribute('class', 'btn btn-default')         ->setValidationScope(FALSE);		return $form;  }}