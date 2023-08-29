<?php 
/*
 * Version CAV - 2.8 - hiver 2023 - Fenêtre modale pour modif pour echange
 *
*/
		
require_once('../../../main.inc.php');
require_once('../class/suivi_client.class.php');
$ID = GETPOST("ID", 'int');		//$conn = new DoliDBMysqli('mysqli', 'localhost', 'dolibarrmysql' , '', 'dolibarr', 100);

	if (empty($ID)) print 'toto';
	$wsuivi = new cgl_echange ($db);	
	$ret = $wsuivi->fetch($ID);
	$separateur='?@';
$out = $wsuivi->dossier.$separateur;
$out .= $wsuivi->fk_dossier.$separateur;
$out .= $wsuivi->datec.$separateur;
$out .= $wsuivi->tms.$separateur;
$out .= $wsuivi->fk_user_create.$separateur;
$out .= $wsuivi->fk_user_mod.$separateur;
$out .= $wsuivi->titre.$separateur;
$out .= $wsuivi->desc.$separateur;
$out .= $wsuivi->action.$separateur;
$out .= $wsuivi->id_interlocuteur.$separateur;
$out .= $wsuivi->interlocuteur.$separateur;
$out .= $wsuivi->date_realise.$separateur;
$out .= $wsuivi->URfirstname.$separateur;
$out .= $wsuivi->fk_secteur.$separateur;
$out .= $wsuivi->URlastname;

print $out;
?>