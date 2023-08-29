<?php 
/*
*
 * Version CAV - 2.8 - hiver 2023 - reassociation BU/LO à un autre contrat
*
*/
require_once('../../../main.inc.php');
require_once(DOL_DOCUMENT_ROOT.'/core/db/mysqli.class.php');
require_once('../class/suivi_client.class.php');
require_once DOL_DOCUMENT_ROOT.'/custom/cglinscription/class/bulletin.class.php';
		
global $conf, $db, $uer;
dol_syslog('CCA - ReqCreerDossBull.php entree');
		
$id_bull = $_GET["id_bull"];	
$libdoss = $_GET["nom_dossier"];	
$id_tiers = $_GET["id_tiers"];	

$wsuivi = new cgl_dossier ($db);
$wsuivi->dossier = $libdoss;
$wsuivi->fk_tiers = $id_tiers;

$wbull = new Bulletin($db);
$wbull->fetch_complet_filtre(-1, $id_bull)	;	
$db->begin();
$ret = $wsuivi->create($user, false);

if ($conf->cglinscription and $ret > 0) {
	$ret1 = $wbull->update_champs("fk_dossier",$wsuivi->id);
}
$db->commit();
print $ret;
?>