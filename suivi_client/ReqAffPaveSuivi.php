<?php 
/*
*		ReqAffPaveSuivi*

 * Version CAV - 2.8 - hiver 2023 - reassociation BU/LO à un autre contrat
*
*/
 		require_once('../../../main.inc.php');
		require_once(DOL_DOCUMENT_ROOT.'/core/db/mysqli.class.php');
		require_once DOL_DOCUMENT_ROOT.'/custom/CahierSuivi/class/html.suivi_client.class.php';
		require_once DOL_DOCUMENT_ROOT.'/custom/CahierSuivi/class/suivi_client.class.php';
		require_once DOL_DOCUMENT_ROOT.'/custom/cglinscription/class/bulletin.class.php';
		
		$id_dossier = GETPOST("id_dossier");
dol_syslog('CCA ReqAffPaveSuivi - 	id_dossier:'.$id_dossier);	
		$origine = GETPOST("origine");		
		$id_bull = GETPOST("id_bull");		
		$fenetre = GETPOST("fenetre");	
		$wfsuivi = new FormCglSuivi ($db);
		$bull = new Bulletin($db);	
		global $bull, $conf, $bc;
		
//		$bull->fetch($id_bull);	
		$bull->fetch_complet_filtre(-1, $id_bull)	;	
		$ret = $wfsuivi->html_PaveSuivi ($id_dossier, $origine, $fenetre)  ;
		
		$wsuivi = new cgl_dossier ($db);
		$wsuivi->fetch($id_dossier);
		
dol_syslog('CCA ReqAffPaveSuivi - 	libelle:'.$wsuivi->libelle);
dol_syslog('CCA ReqAffPaveSuivi - 	id_propriete:'.$wsuivi->id_propriete);	
dol_syslog('CCA ReqAffPaveSuivi - 	color_priorite:'.$wsuivi->color_priorite);
		$ret1=$wfsuivi->html_AffDossier($id_dossier, $wsuivi->dossier, $wsuivi->fk_priorite, 'priorite1', $wsuivi->coulpriorite );

		print  $ret.'!@&!'.$ret1;
?>