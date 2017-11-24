<?php 
/**
 * Klasa iCal generuje kalendarz obiektu (wszystkie zajętości)
 * 
 * @author Łukasz Kusy
 */
class iCal{
    private $ido;
    private $ics_resource = "";
    
    /**
     * Konstruktor klasy
     */
    public function __construct($ido) {
        $this->ido = $ido;
    }
    
    /**
     * Destruktor klasy
     */
    public function __destruct() {
        
    }
    
    /**
     * Funkcja generuje dane (np do pliku ics) dla danego obiektu
     * @param int $ido - id obiektu
     * @return boolean - zwraca zawartość pliku ics lub false jeżeli wystąpi blad
     */
    public function exportReservation(){
            $oSQL = new SQL;      
            
            $this->iCalBegin();
            $this->iCalHead($oSQL);
            $this->addReservation($oSQL);           
            $this->addManualSelection($oSQL);
            $this->iCalEnd();
         
            return $this->ics_resource;             
    }
    
    public function importReservation(){
        
    }
    
    /**
     * Funkcja dodaje reczne zaznaczenia do kalendarza 
     * @param object $oSQL - uchwyt sql
     */
    private function addManualSelection($oSQL){
        $Day = (int) date('d');
        $Month = (int) date('m');
        $Year = (int) date('Y');
        $DStart = null;
        $DEnd = null;
        
        
        
        //zapytanie sql z bazy ....
        
                        $this->iCalEvent(mktime(), $id, $DStart, $DEnd+(60*60*24));
                        
        
    }

    /**
     * Funkcja dodaje rezerwacje do kalendarza 
     * @param object $oSQL - uchwyt sql
     */
    private function addReservation($oSQL){
        //zapytanie sql z bazy ....
            $this->iCalEvent(mktime(), $aRow['kod'], $aRow['prz'], $aRow['wyj']);
        
    }

    /**
     * Funkcja buduje postawę ics
     */
    private function iCalBegin(){
        $this->ics_resource .= "BEGIN:VCALENDAR\r\n"
                . "VERSION:2.0\r\n"
                . "CALSCALE:GREGORIAN\r\n"
                . "METHOD:PUBLISH\r\n"
                . "PRODID;VALUE=TEXT:-//XX.NET//ICS 1.0//EN\r\n";
    }
    
    /**
     * Funkcja tworzy główke pliku 
     * @param int $ReCalId - (shaszowane w implementacji) id obiektu
     * @param string $Name - nazwa obiektu
     * @param string $Description - opis obiektu opcjonalnie zdublowana nazwa obiektu
     */
    private function iCalHead($oSQL, $Description = null){
        
        //zapytanie sql z bazy ....
        $Name = $oSQL->get_result(0, 'nazwa');
        if($Description == NULL) $Description = $Name;
        $this->ics_resource .= "X-WR-RELCALID;VALUE=TEXT:".md5($this->ido.'ash')."@xx.net\r\n"
                . "X-WR-CALNAME;VALUE=TEXT:".$Name." availability calendar\r\n"
                . "X-WR-CALDESC;VALUE=TEXT:".$Description." availability calendar\r\n"
                . "X-MS-OLK-FORCEINSPECTOROPEN:TRUE\r\n"
                . "X-MICROSOFT-DISALLOW-COUNTER:TRUE\r\n"
                . "X-PUBLISHED-TTL:PT15M\r\n";
    }
    
    /**
     * Funkcja tworzy event w pliku ics
     * @param int $DTStamp - timestamp utworzenia pliku
     * @param int $Uid - id eventu (shaszowane w implementacji) 
     * @param int $DTStart - data rozpoczęcia eventu
     * @param int $DTEnd - data zakonczenia eventu
     */
    private function iCalEvent($DTStamp, $Uid, $DTStart, $DTEnd){
        $this->ics_resource .= "BEGIN:VEVENT\r\n"
                . "DTSTAMP;VALUE=DATE-TIME:".date("Ymd\THis",$DTStamp)."\r\n"
                . "UID;VALUE=TEXT:<".$Uid."@xx.net>\r\n"
                . "STATUS:CONFIRMED\r\n"
                . "TRANSP:OPAQUE\r\n"
                . "DTSTART;VALUE=DATE:".date("Ymd",$DTStart)."\r\n"
                . "DTEND;VALUE=DATE:".date("Ymd",$DTEnd)."\r\n"
                . "X-MICROSOFT-CDO-ALLDAYEVENT:TRUE\r\n"
                . "X-MICROSOFT-MSNCALENDAR-ALLDAYEVENT:TRUE\r\n"
                . "SUMMARY;VALUE=TEXT:Confirmed reservation\r\n"
                . "END:VEVENT\r\n";
    }

    /**
     * Funkcja 'zamyka' plik ics
     */
    private function iCalEnd(){
        $this->ics_resource .="END:VCALENDAR";
    }
    
    /**
     * Funkcja haszująca
     * @param string $Data - dane do shaszowania/ odkodowania
     * @param int $Option - opcja (1) shaszuj, (0) odhaszuj
     * @return string - dane wyjściowe
     */
    public function iCalHash($Data, $Option){
        $cryptKey  = '';//<<< wpisac 
        
        if($Option == 1){
            $qEncoded = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey),$Data, MCRYPT_MODE_CBC, md5(md5($cryptKey))));
            return $qEncoded;
        }
        else if($Option == 0){
            $qDecoded = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), base64_decode($Data), MCRYPT_MODE_CBC, md5( md5($cryptKey))), "\0");
            return $qDecoded;
        }
        else{
            //niepoprawna opcja
        }
        
        return $Data;
    } 
    
    static function nextMonth(& $Month, & $Year){
        if($Month<12){
            $Month++;
        }
        else{
            $Month =1;
            $Year++;
        }
    }
    
}

