<?php 
/*
*	ReqDossiers
*
 * Version CAV - 2.8 - hiver 2023 - Fenêtre modale pour changement de dossier d'un bulletin/contrat/réservation
 *
*/
require_once('../../../main.inc.php');
require_once('../class/suivi_client.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
$ID = GETPOST("ID");		
dol_syslog('CCA - ReqDossiers ');
$ListDossier = array();
$wsuivi = new cgl_dossier ($db);	
$ListDossier = $wsuivi->ListDossByTiers($ID);

	$separateurchamp='?@';
	$separateurligne='?!@';
	$out = "";
foreach($ListDossier as $key =>$dossier) {
	if (!empty($out)) 
		$out .= $separateurligne;
	$out .= $dossier["id"].$separateurchamp;
	$out .= $dossier["libelle"].$separateurchamp;
	$out .= $dossier["couleur"].$separateurchamp;
	$out .= $dossier["priorite"].$separateurchamp;
}

print $out;
?>