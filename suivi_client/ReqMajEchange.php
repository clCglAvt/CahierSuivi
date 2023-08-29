<?php 
/*
*	ReqMajEchange.php
*
 * Version CAV - 2.8 - hiver 2023 - Fenêtre modale pour modif pour echange
 *
*/
require_once('../../../main.inc.php');
require_once('../class/suivi_client.class.php');
require_once('../class/html.suivi_client.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
$list_info = array();
$list_info['idEchange'] = GETPOST("idEchange");	
$list_info['idDossier']=GETPOST("idDossier");	
$idDossier=$list_info['idDossier'];	
$list_info['titre']=GETPOST("titre");	
$list_info['description']=GETPOST("description");	
$list_info['action']=GETPOST("action");		
$list_info['idInterlocuteur']=GETPOST("idInterlocuteur");	
$list_info['nvInterlocuteur']=GETPOST("nvInterlocuteur");	
$list_info['telInterl']=GETPOST("telInterl");		
$list_info['mailInterl']=GETPOST("mailInterl");	
$list_info['idtiers']=GETPOST("idtiers");
$list_info['nvtiers'] ="";		

$wsuivi = new cgl_dossier ($db);	
$ret = $wsuivi->EnrTiers_Dossier_Echange(2, $list_info);	

print $ret;
?>