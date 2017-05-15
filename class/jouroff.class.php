<?php 

class TRH_JoursFeries extends TObjetStd {
    function __construct() {
        global $langs;
         
        parent::set_table(MAIN_DB_PREFIX.'rh_absence_jours_feries');
        parent::add_champs('date_jourOff','type=date;index;');
        parent::add_champs('moment','type=chaine;index;');
        parent::add_champs('commentaire','type=chaine;');
        parent::add_champs('entity','type=entier;index;');
        
        
        parent::_init_vars();
        parent::start();    
        
        $this->TFerie=array();
        $this->TMoment=array(
            'allday'=> $langs->trans('AbsenceAllDay'),
            'matin'=> $langs->trans('AbsenceMorning'),
            'apresmidi'=> $langs->trans('AbsenceAfternoon')
        );
        
        $this->moment = 'allday';
    }
    
    
    function save(&$db) {
        global $conf;
        $this->entity = $conf->entity;
        
        if(!$this->testExisteDeja($db)) {
            parent::save($db);  
        }

    }

    
    //fonction qui renvoie 1 si le jour férié que l'on veut créer existe déjà à la date souhaitée, sinon 0
    function testExisteDeja(&$PDOdb){
        global $conf;
        //on récupère toutes les dates de jours fériés existant
        $sql="SELECT count(*) as 'nb'  FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries
             WHERE date_jourOff='".$this->get_date('date_jourOff','Y-m-d')."' AND rowid!=".$this->getId();
        $PDOdb->Execute($sql);
        $obj = $PDOdb->Get_line();
            
        //on teste si l'un d'eux est égal à celui que l'on veut créer
        if($obj->nb > 0){
            return 1;   
        }
        
        return 0;
    }
    
    static function estFerie(&$PDOdb, $date) {
        global $conf, $TCacheTFerie;
		
		if(empty($TCacheTFerie))$TCacheTFerie=array();
		
		if(!empty($TCacheTFerie[$date])) return $TCacheTFerie[$date];
		
		
        //on récupère toutes les dates de jours fériés existant
        $sql="SELECT count(*) as 'nb'  FROM ".MAIN_DB_PREFIX."rh_absence_jours_feries
             WHERE entity IN (0,".(! empty($conf->multicompany->enabled) && ! empty($conf->multicompany->transverse_mode)?"1,":"").$conf->entity.")
             AND  date_jourOff=".$PDOdb->quote($date);
             
        $PDOdb->Execute($sql);
        $obj = $PDOdb->Get_line();
            
        //on teste si l'un d'eux est égal à celui que l'on veut créer
        if($obj->nb > 0){
            $TCacheTFerie[$date] = true;    
        }
        else {
        	$TCacheTFerie[$date] = false;
        }
        
        return $TCacheTFerie[$date];
    }
    
    static function syncronizeFromURL(&$PDOdb, $url) {
        
        $iCal = new ICalReader( $url );
		
		$TListDays[strtoupper(trim("Noël"))] = true;
		$TListDays[strtoupper(trim("L'Armistice"))] = true;
		$TListDays[strtoupper(trim("La Toussaint"))] = true;
		$TListDays[strtoupper(trim("L'Assomption"))] = true;
		$TListDays[strtoupper(trim("La fête nationale"))] = true;
		$TListDays[strtoupper(trim("Le lundi de Pentecôte"))] = true;
		$TListDays[strtoupper(trim("Pentecôte"))] = true;
		$TListDays[strtoupper(trim("L'Ascension"))] = true;
		$TListDays[strtoupper(trim("Fête de la Victoire 1945"))] = true;
		$TListDays[strtoupper(trim("La fête du Travail"))] = true;
		$TListDays[strtoupper(trim("Le lundi de Pâques"))] = true;
		$TListDays[strtoupper(trim("Pâques"))] = true;
		$TListDays[strtoupper(trim("Jour de l'an"))] = true;
        
        foreach($iCal->cal['VEVENT'] as $event) {
        	$label = strtoupper(trim($event['SUMMARY']));
            if($event['STATUS']=='CONFIRMED' && !empty($TListDays[$label])) {
                //var_dump($event);
                $jf = new TRH_JoursFeries;
                $jf->commentaire = $event['SUMMARY'];
                
                $aaaa = substr($event['DTSTART'], 0,4);
                $mm = substr($event['DTSTART'], 4,2);
                $jj = substr($event['DTSTART'], 6,2);
                
                $jf->set_date('date_jourOff', $jj.'/'.$mm.'/'.$aaaa);
                
                $jf->save($PDOdb);
                
            }


        }
        
    }
    
    
    static function getAll(&$PDOdb, $date_start='', $date_end='') {
        global $conf;   
        
        $Tab=array();
              //récupération des jours fériés 
        $sql2=" SELECT moment,commentaire,date_jourOff,rowid FROM  ".MAIN_DB_PREFIX."rh_absence_jours_feries
         WHERE entity IN (0,".(! empty($conf->multicompany->enabled) && ! empty($conf->multicompany->transverse_mode)?"1,":"").$conf->entity.")";
         
        if(!empty($date_start) && !empty($date_end)) $sql2.="AND date_jourOff BETWEEN ".$PDOdb->quote($date_start)." AND ".$PDOdb->quote($date_end);
         
        
        $PDOdb->Execute($sql2);
        
         while ($row = $PDOdb->Get_line()) {
             $Tab[] =$row;
          
         }
        
        return $Tab;
    
    }
    
}

class TJourOff extends TRH_JoursFeries {
    
    function __construct() {
        parent::__construct();
    }
    
}
