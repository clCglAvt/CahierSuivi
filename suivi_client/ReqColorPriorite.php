<?php 
/*
 * * Version CAV - 2.7 - été 2022 - changement de répertoire
 *
*/ 
 		require_once('../../../main.inc.php');
		require_once(DOL_DOCUMENT_ROOT.'/core/db/mysqli.class.php');
		require_once(DOL_DOCUMENT_ROOT.'/custom/CahierSuivi/class/suivi_client.class.php');
		$ID = $_GET["ID"];
		
		$dossier = new cgl_dossier($db);
		$ret = $dossier->rechercheColPriorite($ID);		
 		echo( $ret);
?>