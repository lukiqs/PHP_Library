<?php
include_once 'klasy/pop3.php';
include_once 'klasy/mime_parser.php';
include_once 'klasy/rfc822_addresses.php';
@stream_wrapper_register('pop3', 'pop3_stream');

/**
 * Class SyncAirBNB to synchronization with AirBnB
 * Class need file:
 * - pop3.php
 * - mime_parser.php
 * - rfc822_addresses.php
 * Before created object, use @stream_wrapper_register('pop3', 'pop3_stream');
 */

class SyncAirBNB {
    const PATH_LOG = "dump/sync_airbnb/";
    
    private $pop3;
    private $user;
    private $password;
    private $count_of_messages;
    
    protected $message_id;
    protected $message;
    protected $from_mail;
    protected $subject;
    
    protected $sql;

    public function __construct() {
        $this->sql = new sql();
        $this->pop3 = new pop3_class;
        $this->pop3->hostname = "pop.gmail.com";
        $this->pop3->tls = 1;        
        $this->pop3->port = 995;
        $this->pop3->debug = 0;
        $this->pop3->html_debug = 0;
        $this->pop3->join_continuation_header_lines = 1;
        
        $this->user = "****@****.**";
        $this->password = "******";
        $this->count_of_messages = 0;
    }
    
    protected function getMessage(){
        if(($error = $this->pop3->Open()) == "") {
            if(($error = $this->pop3->Login($this->user, $this->password)) == "") {
                if($error = $this->pop3->Statistics($messages, $size)){
                    $this->createLogSys($error);
                    die();
                }
                $this->pop3->GetConnectionName($connection_name);
                $this->count_of_messages = (int) $messages;
                
                if($messages){
                    $message_file = "pop3://{$connection_name}/1";
                    $mime = new mime_parser_class;
                    $mime->ignore_syntax_errors = 1;
                    $mime->mbox = 0;
                    $mime->decode_bodies = 1;
                    $mime->Decode(array("File" => $message_file), $decoded);
  	
                    $this->message = $decoded[0]['Parts'][0]['Body'];
                    $this->from_mail = $decoded[0]['Headers']['to:'];
                    $this->message_id = $decoded[0]['Headers']['message-id:'];
                    $this->subject = $decoded[0]['Headers']['subject:'];
                    $this->pop3->Close();
                    return true;
                }
                else{
                    //empty inbox
                    $this->pop3->Close();
                    return false;
                }
                //close<<<<<<<<<<<<<<<<<<<<<<<<<
            }
            else $this->createLogSys($error);
        }
        else $this->createLogSys($error);
        $this->pop3->Close();
        return false;
    }
    
    private function createLogSys($data){
        file_put_contents(self::PATH_LOG."error/pop3_".date('d_m_Y_H_i_s').'.txt', print_r($data,1)."\n".print_r(get_object_vars($this), 1)."\n".print_r($_SERVER, 1));
    }    
        
}

/**
 * Class SyncAirBNBValid
 * @author Łukasz Kusy
 * @version 1.0.0.0
 */
class SyncAirBNBValid extends SyncAirBNB {  
    static public $months = array('sty'=>1,'lut'=>2,'mar'=>3,'kwi'=>4,'maj'=>5,'cze'=>6,'lip'=>7,'sie'=>8,
        'wrz'=>9,'paź'=>10,'lis'=>11,'gru'=>12);
    protected $extract_content;
    
    private $valid_mail;
    private $valid_subject;
    private $valid_name_object;
    private $valid_name_surname;
    private $valid_city;
    private $valid_country;
    private $valid_key;
    private $valid_comment;
    private $valid_visitors;
    private $valid_price;
    private $valid_arrival;
    private $valid_departure;
    private $valid_period;
    
    protected $valid;

    protected $date_arrival;
    protected $date_departure;
    
    protected $idOwner;
    protected $idObject;

    public function __construct() {
        parent::__construct();        
    }
    
    /**
     * The method load,extract and valid message.
     * @return boolean $result - getMessage() result.
     */
    protected function loadNext(){
        $result = $this->getMessage();
        if($result){
            $this->extract_content = new SyncAirBNBExtract($this->message);
            $this->valid = $this->validationAll();
        }
        else $this->valid = false;
                
        return $result;
    }

    /**
     * The method call all methods which are responsible for vaidate data.
     */
    private function validationAll(){
        $this->validEmail();
        //$this->validSubject();
        $this->validNameObject();
        $this->validNameSurname();
        $this->validCity();
        $this->validCountry();
        $this->validKey();
        $this->validComment();
        $this->validVisitors();
        $this->validPrice();
        $this->validArrival();
        $this->validDeparture();
        $this->validPeriod();
        
        if(!$this->valid_mail || 
                !$this->valid_name_object || !$this->valid_name_surname ||
                !$this->valid_visitors || !$this->valid_price ||
                !$this->valid_arrival || !$this->date_departure ||
                !$this->valid_period) return false;
        
        return true;
    }
    
    /**
     * Owner search on the base of email.
     */
    private function validEmail(){
                
        if($this->idOwner != null) {
            $this->valid_mail = true;
        } else {
            $this->createLogValid('Blad walidacji maila: nie można odszukac właciciela');
            $this->valid_mail = false;
        }
    }
    
    /**
     * Name of objects, search on the base of name in AirBNB
     */
    private function validNameObject(){
        //$this->sql->query("SELECT `id_o` FROM `sys_obiekty_info_dod` WHERE name_airbnb='{$this->extract_content->name_object}'; ");
        //$this->idObject = $this->sql->get_result(0, 'id_o');
        $listaO = [];
        if(isset($listaO[$this->extract_content->name_object])) $this->idObject = $listaO[$this->extract_content->name_object];
        else $this->idObject = null;
        
        if($this->idObject != null) {
            $this->valid_name_object = true;
        } else {
            $this->createLogValid('Blad walidacji nazwy obiektu: nie mozna odszukać obiektu');
            $this->valid_name_object = false;
        }
    }
    
    /**
     * The method return result work.
     */
    public function debugRaport(){
        if($this->valid_mail) $r_vm = "<font color='green'>Znaleziono właściciela w bazie</font>";
        else $r_vm = "<font color='red'>Nie naleziono właściciela w bazie</font>";
        if($this->valid_subject) $r_vs = "<font color='green'>Temat rezerwacji</font>";
        else $r_vs = "<font color='red'>Temat nie zidentyfikowany</font>";
        if($this->valid_name_object) $r_vno = "<font color='green'>Znaleziono obiekt</font>";
        else $r_vno = "<font color='red'>Nie znaleziono obiektu w bazie</font>";
        if($this->valid_name_surname) $r_vns = "<font color='green'>Wykryto</font>";
        else $r_vns = "<font color='red'>Brak danych</font>";
        if($this->valid_city) $r_vc = "<font color='green'>Wykryto</font>";
        else $r_vc = "<font color='orange'>Brak danych</font>";
        if($this->valid_country) $r_vco = "<font color='green'>Wykryto</font>";
        else $r_vco = "<font color='red'>Brak danych</font>";
        if($this->valid_key) $r_vk = "<font color='green'>Wykryto</font>";
        else $r_vk = "<font color='orange'>Brak danych</font>";
        if($this->valid_comment) $r_vcom = "<font color='green'>Wykryto</font>";
        else $r_vcom = "<font color='orange'>Brak danych</font>";
        if($this->valid_visitors) $r_vv = "<font color='green'>Wykryto, prawidłowa ilość</font>";
        else $r_vv = "<font color='red'>Brak danych lub nie prawidłowa ilość </font>";
        if($this->valid_price) $r_vp = "<font color='green'>Wykryto, prawidłowa cena</font>";
        else $r_vp = "<font color='red'>Brak danych lub nie prawidłowa cena </font>";
        if($this->valid_arrival) $r_va = "<font color='green'>Wykryto</font>";
        else $r_va = "<font color='red'>Brak danych</font>";
        if($this->valid_departure) $r_vd = "<font color='green'>Wykryto</font>";
        else $r_vd = "<font color='red'>Brak danych</font>";
        if($this->valid_period) $r_vpe = "<font color='green'>Prawidłowy przedział dat</font>";
        else $r_vpe = "<font color='red'>Nie prawidłowy przedział dat</font>";
        
        echo<<<HTML
        
        <html>
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body{
                font-family: monospace;
            }
            th,td{
                border: 1px solid black;
                padding-left:7px;
                padding-right:7px;
            }
        </style>
        </head>
        <body>
        <center>
            Odczyt maila nr <strong>$this->message_id</strong> <br><br>
            <table>
                <tr>
                    <th>Opis</th>
                    <th>Wartość</th>
                    <th>Rezultat</th>
                </tr>
                <tr>
                    <th>Maila otrzymano od</td>
                    <td>$this->from_mail</td>
                    <th>$r_vm ($this->idOwner)</th>
                </tr>
                <tr>
                    <th>Temat</td>
                    <td>$this->subject</td>
                    <th>$r_vs</th>
                </tr>
            </table><br><br>
            Analiza zawartości<br><br>
                <table>
                <tr>
                    <th>Opis</th>
                    <th>Wartość wyłuskana</th>
                    <th>Rezultat</th>
                </tr>
                <tr>
                    <th>Nazwa apartamentu</th>
                    <td>{$this->extract_content->name_object}</td>
                    <th>{$r_vno} ($this->idObject)</th>
                </tr>
                <tr>
                    <th>Imię i Nazwisko</th>
                    <td>{$this->extract_content->name_surname}</td>
                    <th>{$r_vns}</th>
                </tr>
                <tr>
                    <th>Miasto i rejon</th>
                    <td>{$this->extract_content->city_region}</td>
                    <th>{$r_vc}</th>
                </tr>
                <tr>
                    <th>Kraj</th>
                    <td>{$this->extract_content->country}</td>
                    <th>{$r_vco}</th>
                </tr>
                <tr>
                    <th>Przyjazd</th>
                    <td>{$this->extract_content->arrival}</td>
                    <th>{$r_va}</th>
                </tr>
                <tr>
                    <th>Wyjazd</th>
                    <td>{$this->extract_content->departure}</td>
                    <th>{$r_vd}</th>
                </tr>
                <tr>
                    <th>Przyjazd i wyjazd</th>
                    <td>Od ({$this->date_arrival}) do ({$this->date_departure})</td>
                    <th>{$r_vpe}</th>
                </tr>
                <tr>
                    <th>Ilość osób</th>
                    <td>{$this->extract_content->count_of_people}</td>
                    <th>{$r_vv}</th>
                </tr>
                <tr>
                    <th>Kod rezerwacji w AirBNB</th>
                    <td>{$this->extract_content->key_reservation}</td>
                    <th>{$r_vk}</th>
                </tr>
                <tr>
                    <th>Komentarz</th>
                    <td>{$this->extract_content->comment}</td>
                    <th>{$r_vcom}</th>
                </tr>
                <tr>
                    <th>Cena</th>
                    <td>{$this->extract_content->price}</td>
                    <th>{$r_vp}</th>
                </tr>
                </table>
        </center>
        </body>
HTML;
    }
    
    /**
     * The method validete subject.
     */
    private function validSubject(){
        if (strpos($this->subject, 'Potwierdzono_Rezerwacj=C4=99') !== false) {
            $this->valid_subject = true;
        } else {
            $this->createLogValid('Blad walidacji tematu:nie wykryto maila rezerwacyjnego');
            $this->valid_subject = false;
        }
    }
    
    /**
     * The method check out content name_surname field.
     */
    private function validNameSurname(){
        if ($this->extract_content->name_surname != null && $this->extract_content->name_surname != "") {
            $this->valid_name_surname = true;
        } else {
            $this->createLogValid('Blad walidacji imienia i nazwiska: brak danych');
            $this->valid_name_surname = false;
        }
    }
    
    /**
     * The method check out content city_region field.
     */
    private function validCity(){
        if($this->extract_content->city_region != null && $this->extract_content->city_region != ""){
            $this->valid_city = true;
        }
        else $this->valid_city = false;
    }
    
    /**
     * The method check out content country field.
     */
    private function validCountry(){
        if($this->extract_content->country != null && $this->extract_content->country != ""){
            $this->valid_country = true;
        }
        else $this->valid_country = false;
    } 
    
    /**
     * The method check out content key_reservation field.
     */
    private function validKey(){
        if($this->extract_content->key_reservation != null && $this->extract_content->key_reservation != ""){
            $this->valid_key = true;
        }
        else $this->valid_key = false;
    }
    
    /**
     * The method check out content comment field.
     */
    private function validComment(){
        if ($this->extract_content->comment != null && $this->extract_content->comment != "") {
            $this->valid_comment = true;
        } else {
            //$this->createLogValid('Brak komentarza');
            $this->valid_comment = false;
        }
    }
    
    /**
     * The method check out content count_of_people field.
     */
    private function validVisitors(){
        if ($this->extract_content->count_of_people != null && $this->extract_content->count_of_people != "") {
            //minimum one person, otherwise return error
            if ($this->extract_content->count_of_people >= 1)
                $this->valid_visitors = true;
            else {
                $this->createLogValid('Blad walidacji ilości gości: zbyt mała ilość');
                $this->valid_visitors = false;
            }
        }
        else {
            $this->createLogValid('Blad walidacji ilości gości: brak danych');
            $this->valid_visitors = false;
        }
    }
    
    /**
     * The method check out content price field.
     */
    private function validPrice(){
        if ($this->extract_content->price != null && $this->extract_content->price != "") {
            //minimum 1, otherwise return error
            if ($this->extract_content->price > 0)
                $this->valid_price = true;
            else {
                $this->createLogValid('Blad walidacji ceny: nieprawidłowa cena');
                $this->valid_price = false;
            }
        }
        else {
            $this->createLogValid('Blad walidacji ceny: brak ceny');
            $this->valid_price = false;
        }
    }
    
    /**
     * The method check out content arrival field.
     */
    private function validArrival(){
        if ($this->extract_content->arrival != null && $this->extract_content->arrival != "") {
            $this->valid_arrival = true;
        } else {
            $this->createLogValid('Blad walidacji daty przyjazdu');
            $this->valid_arrival = false;
        }
    }  
    
    /**
     * The method check out content departure field.
     */
    private function validDeparture(){
        if ($this->extract_content->departure != null && $this->extract_content->departure != "") {
            $this->valid_departure = true;
        } else {
            $this->createLogValid('Blad walidacji daty wyjazdu');
            $this->valid_departure = false;
        }
    }
    
    /**
     * The method check out corect dates.
     */
    private function validPeriod(){
        try{
            $tempDateAr = explode(" ", $this->extract_content->arrival);
            $tempDateDe = explode(" ", $this->extract_content->departure);
          
            $actMon = (int) date('m');
            $actYea = (int) date('Y');
        
            $ArDay = $tempDateAr[1];
            $ArMon = self::$months[$tempDateAr[2]];
            $ArYea = $actMon <= $ArMon ? $actYea : $actYea+1;
        
            $DeDay = $tempDateDe[1];
            $DeMon = self::$months[$tempDateDe[2]];
            $DeYea = $actMon <= $DeMon ? $actYea : $actYea+1;
               
            $this->date_arrival = "$ArDay-$ArMon-$ArYea";
            $this->date_departure = "$DeDay-$DeMon-$DeYea";
            
            $this->valid_period = true;
        } catch (Exception $ex) {
            $this->createLogValid('Blad walidacji przedziału czasowego');
            $this->valid_period = false;
        }
    } 
    
    /**
     * The method save valid errors and warnings.
     * @param mixed_type $data
     */
    private function createLogValid($data){
        file_put_contents(self::PATH_LOG."valid/".date('d_m_Y_H_i_s').'.txt', print_r($data,1)."\n".print_r(get_object_vars($this), 1)."\n".print_r($_SERVER, 1));
    }
}

/**
 * Class extractAirBNB
 * Final class, not for extending !!!
 * @author Łukasz Kusy
 * @version 1.0.0.0
 */
final class SyncAirBNBExtract {
    const PATH_LOG = "dump/sync_airbnb/";
    
    private $contentMail;
    private $keyFile;
    private $error = false;
    
    public $name_object; 
    public $name_surname;
    public $city_region;
    public $country;
    public $arrival;
    public $departure;
    public $count_of_people;
    public $key_reservation;
    public $comment;
    public $price;
    
    /**
     * Construct, mainly method
     * @param string $message - content of mail
     */
    public function __construct($message) {
        $this->contentMail = $message;
        $this->keyFile = uniqid(rand(), true);
        $this->extract();
    }
    
    /**
     * Method extract data content, without use regular expressions.
     * Method requires update !!! It's not safe. 
     */
    private function extract(){
        $this->saveMessage();
        $tempArray = array();
        $i = 0;
        $listaO = [];
        
        $handle = @fopen(self::PATH_LOG.'getMessage/'.$this->keyFile.'.txt', "r");
        if ($handle) {            
            while (($buffer = fgets($handle, 4096)) !== false) {
                //echo $buffer."<br>";
                $tempArray[$i] = trim($buffer);
                $i++;
            }
            if (!feof($handle)){
                $this->createLogSys("Error: unexpected fgets() fail");
                $this->error = true;
                return;
            }
            //var_dump($tempArray);
            fclose($handle);
        }
        
        ///array searching 
        $count_of_line = 0;
        $index_primary = null;
        $index_com_start = null;
        $index_com_end = null;
        $index_ar = null;
        $index_de = null;
        $index_vistors = null;
        $index_key = null;
        $index_price = null;
        $index_message_to = null;
        for($k=0;$k<$i;$k++){
            if(strpos($tempArray[$k], 'Członek Airbnb od') !== false) $index_primary = $k;
            if(strpos($tempArray[$k], '“') !== false) $index_com_start = $k;
            if(strpos($tempArray[$k], '”') !== false) $index_com_end = $k;
            if(strpos($tempArray[$k], 'Przyjazd') !== false) $index_ar = $k+1;
            if(strpos($tempArray[$k], 'Wyjazd') !== false) $index_de = $k+1;
            if(strpos($tempArray[$k], 'Goście') !== false) $index_vistors = $k+1;
            if(strpos($tempArray[$k], 'Kod potwierdzenia') !== false) $index_key = $k+1;
            if(strpos($tempArray[$k], 'Gość płaci') !== false) $index_price = $k+1;
            if(in_array($tempArray[$k], $listaO))
                $this->name_object = $tempArray[$k];
            if(strpos($tempArray[$k], 'Wyślij wiadomość do:') !== false) $index_message_to = $k;
        }
        
        //Wycinamy imie
        $tempExplode = explode(":",$tempArray[$index_message_to]);
        $temp_name = $tempExplode[1];
        
        if(strpos($tempArray[$index_primary-1], trim($temp_name)) !== false){
            //imie i nzazwisko jest w i-1
            if(strpos($tempArray[$index_primary-1], ',') !== false){
                $tempExplode = explode(",",$tempArray[$index_primary-1]);
                $this->name_surname = $tempExplode[0];                
            }
            else
                $this->name_surname = $tempArray[$index_primary-1];
            
        }
        elseif(strpos($tempArray[$index_primary-2], trim($temp_name)) !== false){
            //imie i nzazwisko jest w i-2
            if(strpos($tempArray[$index_primary-2], ',') !== false){
                $tempExplode = explode(",",$tempArray[$index_primary-2]);
                $this->name_surname = $tempExplode[0];
            }
            else
                $this->name_surname = $tempArray[$index_primary-2];            
        }
        elseif(strpos($tempArray[$index_primary-3], trim($temp_name)) !== false){
//            imie i nzazwisko jest w i-3 raczej sie nie zdarzy ale warto sprawdzic
            if(strpos($tempArray[$index_primary-3], ',') !== false){
                $tempExplode = explode(",",$tempArray[$index_primary-3]);
                $this->name_surname = $tempExplode[0];
            }
            else
                $this->name_surname = $tempArray[$index_primary-3];
        }
        
        $tempExplode = explode(",",$tempArray[$index_primary-1]);
        $this->city_region = $tempExplode[0];
        if(strpos($tempArray[$index_primary-1], 'Poland') !== false)  $this->country = "Polska";
        else $this->country = "Inne";
        //$this->name_surname = $tempArray[$index_primary-2];
        for($index_com_start;$index_com_start<=$index_com_end;$index_com_start++){
            $this->comment.= $tempArray[$index_com_start];
        }
        $this->arrival = $tempArray[$index_ar];
        $this->departure = $tempArray[$index_de];
        $this->count_of_people = (int) $tempArray[$index_vistors];
        $this->key_reservation = $tempArray[$index_key];
        $tempExplode = explode(" ",$tempArray[$index_price]);
        $this->price = (int) $tempExplode[0];
        //sprawdzamy moze jest złamana linia
        if($this->price == 0){
            $tempExplode = explode(" ",$tempArray[$index_price-1]);
            $this->price = (int) $tempExplode[2];
        }
    }
     
    /**
     * Method save content of mail to text file.
     */
    private function saveMessage(){
        file_put_contents(self::PATH_LOG.'getMessage/'.$this->keyFile.'.txt',  $this->contentMail);
    }
    
    /**
     * Method save errors.
     * @param mixed_type $data
     */
    private function createLogSys($data){
        file_put_contents(self::PATH_LOG."error/file_".date('d_m_Y_H_i_s').'.txt', print_r($data,1)."\n".print_r(get_object_vars($this), 1)."\n".print_r($_SERVER, 1));
    }
    
    public function __destruct() {
        
    }
}

class SyncAirBNBLoader extends SyncAirBNBValid{
    
    public function __construct() {
        parent::__construct();
        $i=0;
        //$this->loadNext();
        //$this->debugRaport();
         
        while($this->loadNext()){
            //$this->debugRaport();
            
        if($this->valid){            
                       
            try{
                //$oR->zapisz_rezerwacje($oRR);
            } catch (Exception $ex) {
                echo "nope ;/";
                echo $ex;
            }
        }
        }
    }
    
    
}

//$sync = new SyncAirBNBValid();
//$sync->debugRaport();

new SyncAirBNBLoader();

