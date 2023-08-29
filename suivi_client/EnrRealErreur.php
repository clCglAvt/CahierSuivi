<?php
 /*
 * Historique
 * Version CAV - 2.8.3 - printemps 2023
 *		-reconfiguration ligne échange dans 4saisons (bug 255)
 */
 		require_once('../../../main.inc.php');
		require_once(DOL_DOCUMENT_ROOT.'/core/db/mysqli.class.php');
		require_once('../class/html.suivi_client.class.php');
		require_once('../class/suivi_client.class.php');
		
		$line = new cgl_echange($db);
		$ID = $_GET["ID"];	
		$dos = $_GET["dos"];	
		$w= New FormCglSuivi($db);
		// Mettre à jour action comme action realisée
		$ret = $line->update_non_realise($ID) ;
		
		// Reconstruire les echanges condensée et l'action générale
		$Realisation = $w->Realisation ($ID, $dos);
		print $Realisation;
?>