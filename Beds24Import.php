<?php


if(isset($_GET['propkey']) && isset($_GET['bookid']) && isset($_GET['status'])){
    
    $API = new Beds24GetBook($_GET['propkey'], $_GET['bookid']);
    $referer = $API->getResInfo();
    
    if($referer)
        switch ($_GET['status']){
        
            case "new";
                $API->addReservation();  
            break;
        
            case "modify";
            break;
        
            case "cancel";
            break;
        
        }
    
    file_put_contents("dump/dane_API_".date('d_m_Y_H_i_s').'.txt', print_r($_GET,1)."\n".print_r(get_object_vars($this), 1)."\n".print_r($_SERVER, 1));
    
}

class Beds24GetBook{
    const PATH_LOG = "dump/sync_beds24/import/";
    private $refererList = ['Airbnb xml','Booking.com'];
    private $refererPage = ['Airbnb xml' => 'airbnb.pl','Booking.com' => 'booking.com'];
    private $sql;
    private $propKey;
    private $apiKey;
    private $bookId;
    private $dataReservation;
    
    private $idObject;
    private $idOwner;
            
    function __construct($propKey,$bookId) {
        $this->sql = new sql();
        $this->propKey = $propKey;
        $this->bookId = $bookId;
        $this->getApiKey();
    }
    
    public function addReservation(){
            $this->getIdsOwnerObject();
            
//            $oRR = new rez_rezerwacja();
//            $oR = new Rezerwacja;
//            $oO = new Obiekt($this->idObject, $this->sql->get_db_handler());
//            
//            $oRR->setField('id', $this->idObject);
//            list($iY, $iM, $iD) = explode('-', $this->dataReservation['firstNight']);
//            $oRR->setField('data_prz', mktime(12, 0, 0, $iM * 1, $iD * 1, $iY * 1));
//            list($iY, $iM, $iD) = explode('-', date('Y-m-d',strtotime($this->dataReservation['lastNight'])+86400));
//            $oRR->setField('data_wyj', mktime(12, 0, 0, $iM * 1, $iD * 1, $iY * 1));
//            $oRR->setField('godzina_przyjazdu',$oO->GetField('dobaOd'));
//            $oRR->setField('godzina_wyjazdu',$oO->GetField('dobaDo'));
//            $oRR->setField('k_imie_nazwisko', "{$this->dataReservation['guestFirstName']} {$this->dataReservation['guestName']}");
//            $oRR->setField('adres_ip', '255.0.0.255');
//            $oRR->setField('rez', '0');
//            $oRR->setField('rabat_bzzw', $oO->GetField('rabat_bzzw'));
//            $oRR->setField('czy', '0');
//            $oRR->setField('rez', '0');
//            $oRR->setField('cena', (float)$this->dataReservation['price']);
//            $oRR->setField('z', '0');
//            $oRR->setField('wp', (float)$this->dataReservation['price']);
//            $oRR->setField('sw', '1');
//            $oRR->setField('dkz', time()+60*30);
//            $oRR->setField('zaliczka', time());
//            $oRR->setField('io', (int)$this->dataReservation['numAdult']+(int)$this->dataReservation['numChild']);
//            $oRR->setField('lang', 'pl');
//            $oRR->setField('domena', $this->refererPage[$this->dataReservation['referer']]);
//            $oRR->setField('status', '3');
//            $sql1 = new SQL;
//            $sql1->query("SELECT * FROM wlasciciele_info_dod WHERE `id`={$this->idOwner} ;");
//            $oRR->setField('w', $sql1->get_result(0, 'prowizja_booking'));
//            $oRR->setField('id', $this->idOwner);
//            $oRR->setField('email', $this->dataReservation['guestEmail']);
//            $oRR->setField('kom', $this->dataReservation['guestPhone']);
//            
//            $KEY = false;
//            try{
//                file_put_contents(self::PATH_LOG."loaded/addReservation_".date('d_m_Y_H_i_s').'.txt', print_r($this,1)."\n".print_r(get_object_vars($this), 1)."\n".print_r($_SERVER, 1));
//                $KEY = $oR->zapisz_rezerwacje($oRR);          
//            } catch (Exception $ex) {
//                file_put_contents(self::PATH_LOG."error/addReservation_".date('d_m_Y_H_i_s').'.txt', print_r($ex,1)."\n".print_r(get_object_vars($this), 1)."\n".print_r($_SERVER, 1));
//            }
//            
//            
//            if($KEY)
//                $this->insertBookId($this->bookId, $KEY);
            
    }
    
    public function getResInfo(){
        $data['authentication'] = ['apiKey'=>  $this->apiKey, 'propKey'=> $this->propKey];
        $data['includeInvoice'] = false;
        $data['includeInfoItems'] = false;
        $data['bookId'] = $this->bookId;
        
        var_dump($data);
        
        $json = json_encode($data);
        $url = "https://api.beds24.com/json/getBookings";      
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_POST, 1) ;
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $result = curl_exec($ch);
        curl_close ($ch);
        sleep(5);
        
        $tmp = json_decode($result, true);
        $this->dataReservation = $tmp[0];
        var_dump($this->dataReservation);
        
        return $this->validReferer();
    }
    
    private function insertBookId($id,$key){
        $this->sql->query("INSERT INTO `beds24_res`  (`key`,`id_book`) VALUES('$key',$id) ;");
    }
    
    private function getIdsOwnerObject(){
        $this->sql->query("SELECT `id_o` FROM `obiekty_info_dod` WHERE `beds24_roomid`='{$this->dataReservation['roomId']}' ;");
        $this->idObject = (int) $this->sql->get_result(0, 'id_o');
        
        $this->sql->query("SELECT `id_w` FROM `obiekty` WHERE `id`=$this->idObject ;");
        $this->idOwner = (int) $this->sql->get_result(0, 'id_w');
    }
    
    private function validReferer(){
        return in_array($this->dataReservation['referer'], $this->refererList);
    }

    private function getApiKey(){     
        try{
            $this->sql->query("SELECT `id_o` FROM `obiekty_info_dod` WHERE `beds24_propkey`='$this->propKey' LIMIT 1;");
            $tmp = (int) $this->sql->get_result(0, 'id_o');
            
            if($tmp == null)
                throw new Exception ("Parametr nie jest liczba",1);
            
            $this->sql->query("SELECT `id_w` FROM `obiekty` WHERE `id`=$tmp ;");
            $tmp = (int) $this->sql->get_result(0, 'id_w');
            
            if($tmp == null)
                throw new Exception ("Parametr nie jest liczba",2);
            
            $this->sql->query("SELECT `beds24_apikey` FROM `wlasciciele_info_dod` WHERE `id_w`=$tmp;");
            $this->apiKey = $this->sql->get_result(0, 'beds24_apikey'); 
            
            if($this->apiKey == null || $this->apiKey == "")
                throw new Exception ("ApiKey jest nie prawidlowy",3);
        
        } catch (Exception $ex) {
            file_put_contents(self::PATH_LOG."error/getApiKey_".date('d_m_Y_H_i_s').'.txt', print_r($ex,1)."\n".print_r(get_object_vars($this), 1)."\n".print_r($_SERVER, 1));
        }             
    }
}

