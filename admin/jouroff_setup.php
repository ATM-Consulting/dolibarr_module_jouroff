<?php
    require('../config.php');
    
    dol_include_once('/jouroff/class/jouroff.class.php');
    
    if(dol_include_once('/absence/class/absence.class.php')) {
        $emploiTemps=new TRH_EmploiTemps;
        dol_include_once('/absence/lib/absence.lib.php');    
    }
    else {
        $emploiTemps=null;
    }
    
    
    $langs->load('jouroff@jouroff');
    $langs->load('absence@absence');
    
    $PDOdb=new TPDOdb;
    
    $feries=new TRH_JoursFeries;
    
    if(isset($_REQUEST['action'])) {
        switch($_REQUEST['action']) {
            case 'sync':
                
                if(!empty($conf->global->MAIN_INFO_SOCIETE_COUNTRY)) {
                    list($id_country, $code_country) = explode(':', $conf->global->MAIN_INFO_SOCIETE_COUNTRY);
                    
                    if($code_country=='FR') {
                        $url='https://www.google.com/calendar/ical/fr.french%23holiday%40group.v.calendar.google.com/public/basic.ics';
                    }
                    else{
                        $url = '';  
                    }
                    
                }
                
                
                if(!empty($conf->global->ABSENCE_SYNC_CALENDAR)) {
                    $url = $conf->global->ABSENCE_SYNC_CALENDAR;
                }
                
                if(empty($url)) {
                    setEventMessage($langs->trans('ErrCalendarURLNotFound'), 'errors');
                }
                else{
                    TRH_JoursFeries::syncronizeFromURL($PDOdb, $url);   
                }
                
                
                _liste($PDOdb, $feries , $emploiTemps);
                
                break;
            case 'add':
            case 'new':
                //$PDOdb->db->debug=true;
                $feries->set_values($_REQUEST);
                _fiche($PDOdb, $feries,$emploiTemps, 'edit');
                break;  
            case 'edit' :
                $feries->load($PDOdb, $_REQUEST['idJour']);
                _fiche($PDOdb, $feries,$emploiTemps,'edit');
                break;
                
            case 'save':
                $feries->load($PDOdb, $_REQUEST['idJour']); 
                //print_r($feries);     
                $feries->set_values($_REQUEST);
                
                $existeDeja=$feries->testExisteDeja($PDOdb);
                
                if(!$existeDeja){
                    $feries->save($PDOdb);
                    _liste($PDOdb, $feries , $emploiTemps);
                }else{
                    $mesg = '<div class="error">' . $langs->trans('ErrNoWorkedDayAlreadyExist') . '</div>';
                    _fiche($PDOdb, $feries , $emploiTemps,'edit');
                }
                
                
                break;
            
            case 'view':
                $feries->load($PDOdb, $_REQUEST['idJour']);
                
                _fiche($PDOdb, $feries,$emploiTemps,'view');
                
                
                break;
            case 'delete':
                //$PDOdb->db->debug=true;
                $feries->load($PDOdb, $_REQUEST['idJour']);
                $feries->delete($PDOdb, $_REQUEST['idJour']);
                $mesg = '<div class="ok">' . $langs->trans('DayDeleted') . '</div>';
                $mode = 'edit';
                _liste($PDOdb, $feries , $emploiTemps);
                break;
        }
    }
    elseif(isset($_REQUEST['id'])) {
        _liste($PDOdb, $feries , $emploiTemps);
        
                
    }
    else {
        //$PDOdb->db->debug=true;
        _liste($PDOdb, $feries, $emploiTemps);
    }
    
    
    $PDOdb->close();
    
    llxFooter();
    
    
function _liste(&$PDOdb, $feries, $emploiTemps ) {
    global $langs, $conf, $db, $user;   
    llxHeader('', $langs->trans('ListOfAbsence'));
    
    if(!is_null($emploiTemps))  print dol_get_fiche_head(edtPrepareHead($emploiTemps, 'emploitemps')  , 'joursferies', $langs->trans('Absence'));
    
    //getStandartJS();  
    
    $r = new TSSRenderControler($feries);
    $sql="SELECT rowid as 'ID', date_cre as 'DateCre', 
             date_jourOff, moment as 'Période',  commentaire as 'Commentaire', '' as 'Supprimer'
        FROM  ".MAIN_DB_PREFIX."rh_absence_jours_feries
        WHERE entity IN (0,".$conf->entity.")";
        
    
    $TOrder = array('date_jourOff'=>'DESC');
    if(isset($_REQUEST['orderDown']))$TOrder = array($_REQUEST['orderDown']=>'DESC');
    if(isset($_REQUEST['orderUp']))$TOrder = array($_REQUEST['orderUp']=>'ASC');
                
    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;           
    //print $page;
    $form=new TFormCore($_SERVER['PHP_SELF'],'formtranslateList','GET');
    
    $r->liste($PDOdb, $sql, array(
        'limit'=>array(
            'page'=>$page
            ,'nbLine'=>'30'
        )
        ,'link'=>array(
            'date_jourOff'=>'<a href="?idJour=@ID@&fk_user='.$user->id.'&action=view">@val@</a>'
            ,'Supprimer'=>$user->rights->jouroff->myactions->ajoutJourOff?"<a onclick=\"if (window.confirm('" . $langs->trans('DoYouReallyWantDeletePublicHoliday') . "')){href='?idJour=@ID@&fk_user=".$user->id."&action=delete'};\">".img_delete()."</a>":''
        ) 
        ,'translate'=>array(
            'Période'=>array('matin'=> $langs->trans('AbsenceMorning'),'apresmidi'=> $langs->trans('AbsenceAfternoon'),'allday'=> $langs->trans('AbsenceAllDay'))
        )
        ,'hide'=>array('DateCre')
        ,'type'=>array('date_jourOff'=>'date')
        ,'liste'=>array(
            'titre'=> $langs->trans('PublicHolidayNonWorkedDaysList')
            ,'image'=>img_picto('','title.png', '', 0)
            ,'picto_precedent'=>img_picto('','previous.png', '', 0)
            ,'picto_suivant'=>img_picto('','next.png', '', 0)
            ,'noheader'=> (int)isset($_REQUEST['socid'])
            ,'messageNothing'=>"Aucun jour non travaillé"
            ,'order_down'=>img_picto('','1downarrow.png', '', 0)
            ,'order_up'=>img_picto('','1uparrow.png', '', 0)
            ,'picto_search'=>img_search()
            
        )//theme/rh/img/search.png
        ,'title'=>array(
            'date_jourOff'=> $langs->trans('NoWorkedDays')
        )
        ,'search'=>array(
            'date_jourOff'=>array('recherche'=>'calendar')
            
        )
        ,'orderBy'=>$TOrder
        
    ));
    if($user->rights->jouroff->myactions->ajoutJourOff=="1"){
        ?>
        <div class="tabsAction">
        <a class="butAction" href="?fk_user=<?=$user->id?>&action=new"><?php echo $langs->trans('New'); ?></a>
        &nbsp;
        <a class="butAction" href="?action=sync"><?php echo $langs->trans('OnlineSynchronization'); ?></a>
        <div style="clear:both"></div>
        </div>
        <?php
    }
    $form->end();
    
    llxFooter();
}   
    
function _fiche(&$PDOdb, $feries, $emploiTemps, $mode) {
    global $db,$user,$idUserCompt, $idComptEnCours, $langs;
    llxHeader('', $langs->trans('Schedule'));
    
    $form=new TFormCore($_SERVER['PHP_SELF'],'form1','POST');
    $form->Set_typeaff($mode);
    echo $form->hidden('idJour', $feries->getId());
    echo $form->hidden('action', 'save');
    echo $form->hidden('id', $user->id);

    
    $TBS=new TTemplateTBS();
    print $TBS->render('../tpl/joursferies.tpl.php'
        ,array(
            
        )
        ,array(
            'joursFeries'=>array(
                'id'=>$feries->getId()
                ,'date_jourOff'=>$form->calendrier('', 'date_jourOff', $feries->date_jourOff, 12)
                ,'moment'=>$form->combo('','moment',$feries->TMoment,$feries->moment)
                ,'commentaire'=>$form->zonetexte('','commentaire',$feries->commentaire, 40,3,'','','-')
                ,'titreCreate'=>load_fiche_titre($langs->trans('NewPublicHoliday'),'', 'title.png', 0, '')
                ,'titreAction'=>$_GET['action']
                ,'titreVisu'=>load_fiche_titre($langs->trans('PublicHolidayVisualization'),'', 'title.png', 0, '')
            )
            ,'userCourant'=>array(
                'id'=>$user->id
                ,'droitAjoutJour'=>$user->rights->jouroff->myactions->ajoutJourOff
            )
            ,'view'=>array(
                'mode'=>$mode
                ,'head'=>(!is_null($emploiTemps)) ? dol_get_fiche_head(edtPrepareHead($emploiTemps, 'emploitemps')  , 'joursferies', $langs->trans('Absence')) : ''
            )
            ,'translate' => array(
                'NoWorkedDays' => $langs->trans('NoWorkedDays'),
                'Period' => $langs->trans('Period'),
                'Comment' => $langs->trans('Comment'),
                'ConfirmDeletePublicHoliday' => $langs->trans('ConfirmDeletePublicHoliday'),
                'Delete' => $langs->trans('Delete'),
                'Modify' => $langs->trans('Modify'),
                'Back' => $langs->trans('Back'),
                'Register' => $langs->trans('Register'),
                'Cancel' => $langs->trans('Cancel')
            )
        )   
    );
    
    echo $form->end_form();
    // End of page
    
    global $mesg, $error;
    dol_htmloutput_mesg($mesg, '', ($error ? 'error' : 'ok'));
    llxFooter();
} 


