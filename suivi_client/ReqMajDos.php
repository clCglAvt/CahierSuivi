<?php 
/*
 * Version CAV - 
 *
*/
// Mise à jour du statut du dossier
// retour de la couleur du nouveau statut 	
	require_once('../../../main.inc.php');
	require_once(DOL_DOCUMENT_ROOT.'/core/db/mysqli.class.php');
	require_once('../class/html.suivi_client.class.php');
	require_once('../class/suivi_client.class.php');
		
	$w = new FormCglSuivi($db);
	$line = new cgl_dossier($db);
	$ID = $_GET["id"];	
	$priorite = $_GET["priorite"];	
	$line->id = $ID;	
/*
if ($line->id < 30) $ret = 	$line->update_priorite($priorite);
el*/
if (!empty($priorite)) $ret = 	$line->update_priorite($priorite);


if ($ret > 0) {
	$ret = $line->fetch( $ID);
$out ="#".$line->coulpriorite		;	}
else $out ="Erreur:".$ret;
	print $out;		
?>