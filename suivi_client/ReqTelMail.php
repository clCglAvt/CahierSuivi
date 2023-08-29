<?php 
 		require_once('../../../main.inc.php');
		require_once(DOL_DOCUMENT_ROOT.'/core/db/mysqli.class.php');
		require_once('../class/html.suivi_client.class.php');
		require_once('../class/suivi_client.class.php');
		
		$w = new FormCglSuivi($db);
		$line = new cgl_dossier($db);
		$ID = $_GET["ID"];	
		
		$out = $w->ChercheTelMailIdTiersContact($line, $ID);
		
		$out .='?'.$line->TiersTel;
		$out .='?'.$line->TiersMail;
		
		print $out;		
?>