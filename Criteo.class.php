<?php
//Klasa Criteo - raklamy apartamentów

class Criteo{
    public $Script = '';
   
    public function __construct() {
      
    }
    
    public function getProductTag($ido,$o,$mp,$dp,$mw,$dw,$k){
        try {
          
          $oSQL = new SQL;
          $oO = new Obiekt($ido, $oSQL->get_db_handler());
          $iPrzyjazd = mktime(12, 0, 0, $mp, $dp, rok_przyjazdu($dp, $mp, $dw, $mw));
          $iWyjazd = mktime(12, 0, 0, $mw, $dw, rok_wyjazdu($dp, $mp, $dw, $mw));
          //$aCeny = $oO->oblicz_ceny($o, $iPrzyjazd, $iWyjazd);
          //if(isset($aCeny['cena_bezzwrotna']))
          //  $iCena = $aCeny['cena_bezzwrotna'];
          //else $iCena = $aCeny['cena_ze_wszystkim'];
          $sHrentalStartdate = date('Y-m-d', $iPrzyjazd);
          $sHrentalEnddate = date('Y-m-d', $iWyjazd);
          
                  
        } catch(Exception $eE) {}
        
        $this->Script = '    
        <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
        <script type="text/javascript">
             window.criteo_q = window.criteo_q || [];
             window.criteo_q.push(
                { event: "setAccount", account: 22085 },
                { event: "setSiteType", type: "'.$this->getDevice($_SERVER["HTTP_USER_AGENT"]).'" },
                { event: "viewSearch", checkin_date: "'.$sHrentalStartdate.'", checkout_date: "'.$sHrentalEnddate.'", city: "'.$this->getCity($k).'", guests_no: "'.$o.'" },
                { event: "viewItem", item: "'.$ido.'" });
        </script>
        ';
        
        //{ event: "viewPrice", price: "'.$iCena.'" }
        
        return $this->Script;    
    }
    
    public function getProductTagID($ido){
        
        $this->Script = '    
        <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
        <script type="text/javascript">
             window.criteo_q = window.criteo_q || [];
             window.criteo_q.push(
                { event: "setAccount", account: 22085 },
                { event: "setSiteType", type: "'.$this->getDevice($_SERVER["HTTP_USER_AGENT"]).'" },
                { event: "viewItem", item: "'.$ido.'" });
        </script>
        ';
        
        return $this->Script;    
    }
    
    public function getProductSearchTag($k){
        
        $this->Script = '    
        <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
        <script type="text/javascript">
             window.criteo_q = window.criteo_q || [];
             window.criteo_q.push(
                { event: "setAccount", account: 22085 },
                { event: "setSiteType", type: "'.$this->getDevice($_SERVER["HTTP_USER_AGENT"]).'" },
                { event: "viewSearch", city: "'.$this->getCity($k).'" });
        </script>
        ';
        
        return $this->Script;    
    }
    
    public function getHomePageTag(){
        
        $this->Script = ' 
        <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
        <script type="text/javascript">
        window.criteo_q = window.criteo_q || [];
             window.criteo_q.push(
                { event: "setAccount", account: 22085 },
                { event: "setSiteType", type: "'.$this->getDevice($_SERVER["HTTP_USER_AGENT"]).'" },
                { event: "setHashedEmail", email: "" },
                { event: "viewHome" });
        </script>
        ';
        
        return $this->Script;    
    }
    
    public function getSalesTag($ido,$o,$mp,$dp,$mw,$dw,$k,$key){
        try {
          
          $oSQL = new SQL;
          $oO = new Obiekt($ido, $oSQL->get_db_handler());
          $iPrzyjazd = mktime(12, 0, 0, $mp, $dp, rok_przyjazdu($dp, $mp, $dw, $mw));
          $iWyjazd = mktime(12, 0, 0, $mw, $dw, rok_wyjazdu($dp, $mp, $dw, $mw));
          $aCeny = $oO->oblicz_ceny($o, $iPrzyjazd, $iWyjazd);
          if(isset($aCeny['cena_bezzwrotna']))
            $iCena = $aCeny['cena_bezzwrotna'];
          else $iCena = $aCeny['cena_ze_wszystkim'];
          $sHrentalStartdate = date('Y-m-d', $iPrzyjazd);
          $sHrentalEnddate = date('Y-m-d', $iWyjazd);
          
          if($k == null){
              $sQuery = "SELECT `kategorie` FROM sys_obiekty WHERE id=".$ido;
              $oSQL->query($sQuery);
              foreach($oSQL->get_table_hash() as $aRow) {
                  $k = substr($aRow['kategorie'],1,2);
                  //echo '|||||||>'.$k.'<||||||';
              }
          }
          
          $mail = '';
          $sQuery = "SELECT `k_email` FROM rez_rezerwacje WHERE kod='".$key."';";
          $oSQL->query($sQuery);
          foreach($oSQL->get_table_hash() as $aRow) {
              $mail = $aRow['k_email'];
          }
                  
        } catch(Exception $eE) {}
        
        $this->Script = '    
        <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
        <script type="text/javascript">
             window.criteo_q = window.criteo_q || [];
             window.criteo_q.push(
                { event: "setAccount", account: 22085 },
                { event: "setSiteType", type: "'.$this->getDevice($_SERVER["HTTP_USER_AGENT"]).'" },
                { event: "viewSearch", checkin_date: "'.$sHrentalStartdate.'", checkout_date: "'.$sHrentalEnddate.'", city: "'.$this->getCity($k).'", guests_no: "'.$o.'" },
                { event: "setHashedEmail", email: "'.$this->hashCriteo($mail).'" },
                { event: "trackTransaction", id:"'.$key.'", item:[ 
                            { id: "'.$ido.'", price: '.$iCena.', quantity:1 } ]}
                );
        </script>
        ';
        
        return $this->Script;    
    }
    
    public function getBasketTag($ido,$o,$mp,$dp,$mw,$dw,$k){
        try {
          
          $oSQL = new SQL;
          $oO = new Obiekt($ido, $oSQL->get_db_handler());
          $iPrzyjazd = mktime(12, 0, 0, $mp, $dp, rok_przyjazdu($dp, $mp, $dw, $mw));
          $iWyjazd = mktime(12, 0, 0, $mw, $dw, rok_wyjazdu($dp, $mp, $dw, $mw));
          $aCeny = $oO->oblicz_ceny($o, $iPrzyjazd, $iWyjazd);
          if(isset($aCeny['cena_bezzwrotna']))
            $iCena = $aCeny['cena_bezzwrotna'];
          else $iCena = $aCeny['cena_ze_wszystkim'];
          $sHrentalStartdate = date('Y-m-d', $iPrzyjazd);
          $sHrentalEnddate = date('Y-m-d', $iWyjazd);
          
          if($k == null){
              $sQuery = "SELECT `kategorie` FROM sys_obiekty WHERE id=".$ido;
              $oSQL->query($sQuery);
              foreach($oSQL->get_table_hash() as $aRow) {
                  $k = substr($aRow['kategorie'],1,2);
                  //echo '|||||||>'.$k.'<||||||';
              }
          }
                  
        } catch(Exception $eE) {}
        
        $this->Script = '    
        <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
        <script type="text/javascript">
             window.criteo_q = window.criteo_q || [];
             window.criteo_q.push(
                { event: "setAccount", account: 22085 },
                { event: "setSiteType", type: "'.$this->getDevice($_SERVER["HTTP_USER_AGENT"]).'" },
                { event: "viewSearch", checkin_date: "'.$sHrentalStartdate.'", checkout_date: "'.$sHrentalEnddate.'", city: "'.$this->getCity($k).'", guests_no: "'.$o.'" },
                { event: "viewBasket", item: [ 
                            { id: "'.$ido.'", price: '.$iCena.', quantity:1 } ]}
                );
        </script>
        ';
        
        return $this->Script;    
    }
    
    public function getListingTag($ido,$ido1,$ido2,$o,$mp,$dp,$mw,$dw,$k){
        try {
          
          $oSQL = new SQL;
          
          if($ido != NULL){
          $oO = new Obiekt($ido, $oSQL->get_db_handler());
          $iPrzyjazd = mktime(12, 0, 0, $mp, $dp, rok_przyjazdu($dp, $mp, $dw, $mw));
          $iWyjazd = mktime(12, 0, 0, $mw, $dw, rok_wyjazdu($dp, $mp, $dw, $mw));
          $aCeny = $oO->oblicz_ceny($o, $iPrzyjazd, $iWyjazd);
          if(isset($aCeny['cena_bezzwrotna']))
            $iCena = $aCeny['cena_bezzwrotna'];
          else $iCena = $aCeny['cena_ze_wszystkim'];
          }
          else $iCena = 'none';
          
          if($ido1 != NULL){
          $oO1 = new Obiekt($ido1, $oSQL->get_db_handler());
          $aCeny1 = $oO1->oblicz_ceny($o, $iPrzyjazd, $iWyjazd);
          if(isset($aCeny1['cena_bezzwrotna']))
            $iCena1 = $aCeny1['cena_bezzwrotna'];
          else $iCena1 = $aCeny1['cena_ze_wszystkim'];
          }
          else $iCena1 = 'none';
          
          if($ido2 != NULL){
          $oO2 = new Obiekt($ido2, $oSQL->get_db_handler());
          $aCeny2 = $oO2->oblicz_ceny($o, $iPrzyjazd, $iWyjazd);
          if(isset($aCeny2['cena_bezzwrotna']))
            $iCena2 = $aCeny2['cena_bezzwrotna'];
          else $iCena2 = $aCeny2['cena_ze_wszystkim'];
          }
          else $iCena2 = 'none';
          
          $sHrentalStartdate = date('Y-m-d', $iPrzyjazd);
          $sHrentalEnddate = date('Y-m-d', $iWyjazd);
          
          
        } catch(Exception $eE) {}
        
        $this->Script = '    
        <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
        <script type="text/javascript">
             window.criteo_q = window.criteo_q || [];
             window.criteo_q.push(
                { event: "setAccount", account: 22085 },
                { event: "setSiteType", type: "'.$this->getDevice($_SERVER["HTTP_USER_AGENT"]).'" },
                { event: "viewSearch", checkin_date: "'.$sHrentalStartdate.'", checkout_date: "'.$sHrentalEnddate.'", city: "'.$this->getCity($k).'", guests_no: "'.$o.'" },
                { event: "viewList", item: ["'.$ido.'", "'.$ido1.'", "'.$ido2.'"] },
                { event: "viewPrice", price: ["'.$iCena.'", "'.$iCena1.'", "'.$iCena2.'"] });
        </script>
        ';
        
        return $this->Script;    
    }
    
    private function getDevice($pv_browser_user_agent){
        $mobile_working_test = ''; 
        $a_mobile_search = array('android', 'epoc', 'linux armv', 'palmos', 'palmsource', 'windows ce', 'symbianos', 'symbian os', 'symbian', 'webos', 'benq', 'blackberry', 'danger hiptop', 'ddipocket', ' droid', 'htc_dream', 'htc hero', 'ipod', 'iphone', 'kindle', 'lge-cx', 'lge-lx', 'lge-mx', 'lge vx', 'lge ', 'lge-', 'lg;lx', 'nintendo wii', 'nokia', 'palm', 'pdxgw', 'playstation', 'sagem', 'samsung', 'sec-sgh', 'sharp', 'sonyericsson', 'sprint', 'j-phone', 'n410', 'mot 24', 'mot-', 'htc-', 'htc_', 'sec-', 'sie-m', 'sie-s', 'spv ', 'vodaphone', 'smartphone', 'armv', 'midp', 'mobilephone', 'avantgo', 'blazer', 'elaine', 'eudoraweb', 'iemobile',  'minimo', 'mobile safari', 'mobileexplorer', 'opera mobi', 'opera mini', 'netfront', 'opwv', 'polaris', 'semc-browser', 'up.browser', 'webpro', 'wms pie', 'xiino', 'astel',  'docomo',  'novarra-vision', 'portalmmm', 'reqwirelessweb', 'vodafone'); 
        $j_count = count($a_mobile_search); 
        for($j = 0; $j < $j_count; $j++){ 
            if(stristr($pv_browser_user_agent, $a_mobile_search[$j])){ 
                $mobile_working_test = $a_mobile_search[$j]; break; 
                
            } 
        } 
        $Agent = 'd';
        
        if($mobile_working_test)$Agent = 'm';
        else $Agent = 'd';
        
    return $Agent;
    }
    
    private function getCity($k){
        $City = 'none';
        if($k == 1)$City = 'Kraków';
        if($k == 2)$City = 'Warszawa';
        if($k == 3)$City = 'Wrocław';
        if($k == 4)$City = 'Poznań';
        if($k == 6)$City = 'Góry';
        if($k == 5)$City = 'Morze';
        return $City;
    }
    
    private function hashCriteo($source_address){
        
        $processed_address = strtolower($source_address);//conversion to lower case
        $processed_address = trim($processed_address); //trimming
        $processed_address = mb_convert_encoding($processed_address, "UTF-8", "ISO-8859-1"); //conversion from ISO-8859-1 to UTF-8 (replace "ISO-8859-1" by the source encoding of your string) 
        $processed_address = md5($processed_address); //hash with MD5 algorithm 
        //echo "Source e-mail: ".$source_address." | Hashed e-mail: ".$processed_address;
        
        return $processed_address;
    }
    
}
