<?phpnamespace DbTable;use Nette;/** * Model, ktory sa stara o tabulku udaje *  * Posledna zmena 26.06.2017 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2017 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.5 */class Udaje extends Table {  /** @var string */  protected $tableName = 'udaje';  /** Vrati pozadovany zaznam kluca alebo false   * @param string $kluc Nazov kluca   * @return \Nette\Database\Table\ActiveRow|FALSE */  public function getKluc($kluc) {    return strlen($kluc) ? $this->findOneBy(['nazov'=>$kluc]) : FALSE;  }    /** Opravy v tabulke zaznam s danym nazvom   * @param string $kluc polozka stlpca nazov   * @param string $data hodnota stlpca text pre dany kluc   * @return integer */  public function opravKluc($kluc, $data) {    return $this->getTable()->where(['nazov'=>$kluc])->update(['text'=>$data]);  }  /** Vrati vsetky dostupne udaje podla registracie   * @param int $id_reg min. uroven registracie   * @return \Nette\Database\Table\Selection */  public function vypisUdaje($id_reg = 0) {    return $this->getTable()->where("id_user_roles <= ?", $id_reg);  }    /** Funkcia vrati celociselnu hodnotu udaju s nazvom   * @param string $nazov Nazov udaju   * @return int   */  public function getUdajInt($nazov = "") {    $p = $this->findOneBy(['nazov'=>$nazov]);    return $p !== FALSE ? (int)$p->text : 0;  }    /** Funkcia pre ulozenie udaju   * @param Nette\Utils\ArrayHash $values   * @param array $ur_reg   * @return Nette\Database\Table\ActiveRow|FALSE   * @throws Database\DriverException   */  public function ulozUdaj(Nette\Utils\ArrayHash $values, $ur_reg) {    $id = isset($values->id) ? $values->id : 0;    unset($values->id);    if (isset($values->spravca)) {      $values->offsetSet("id_user_roles", $values->spravca ? $ur_reg['manager'] : $ur_reg['admin']);      unset($values->spravca);    }    if (isset($values->druh_null)) {      $values->offsetSet("id_druh", $values->druh_null ? NULL : (isset($values->id_druh) ? $values->id_druh : 1));      unset($values->druh_null);    } elseif (isset($values->id_druh)) { unset($values->id_druh); }    try {      return $this->uloz($values, $id);    } catch (Exception $e) {      throw new Database\DriverException('Chyba ulozenia: '.$e->getMessage());    }  }    /** Vrati pozadovane usporiadanie oznamov alebo false   * @return boolean */  public function getOznamUsporiadanie() {    $tmp = $this->findOneBy(['nazov'=>"oznam_usporiadanie"]);    return $tmp !== FALSE ? (boolean)$tmp->text : FALSE;  }    /** Vrati pozadovanu skupinu udajov alebo false podla druhu   * @param string $kluc Nazov druhu   * @param array $ur_reg Minimalna uroven registracie   * @return \Nette\Database\Table\Selection|FALSE */  public function getDruh($kluc = "", $ur_reg = 5) {    return strlen($kluc) ? $this->findBy(['druh.presenter'=>$kluc, "id_user_roles <= ".$ur_reg]) : FALSE;  }    /** Ulozi udaj podla nazvu   * @param string $key Nazov udaja   * @param string $value Hodnota (text) udaja   * @return \Nette\Database\Table\ActiveRow|FALSE */  public function saveUdaj($key, $value) {    return count($tmp = $this->findOneBy(["nazov"=>$key])) == 1 ? $this->oprav($tmp->id, ["text"=>$value]) : FALSE;  }}