<?php 
/*
 * Version CAV - 2.8 - hiver 2023 - Fenêtre modale pour modif pour echange
 *
*/
		
require_once('../../../main.inc.php');
require_once('../class/suivi_client.class.php');
$id_echange = GETPOST("id_echange", 'int');	
$id_dossier = GETPOST("id_dossier", 'int');	

	//$conn = new DoliDBMysqli('mysqli', 'localhost', 'dolibarrmysql' , '', 'dolibarr', 100);

	$wsuivi = new cgl_echange ($db);	
	[$line, $line_echange] = $wsuivi->Chargement($id_echange, $id_dossier);	

if ($id_echange > 0) $iddossier = $line_echange->fk_dossier;
else $iddossier = $line->id;

	$separateur='?@';
$out = $line->dossier.$separateur; //0
$out .= $iddossier.$separateur; //1

dol_syslog("CCA ReqEchange - idossier:".$iddossier);

$out .= dol_print_date($line_echange->datec,"daytext").$separateur; //2
$out .= dol_print_date($line_echange->tms,"%d/%m").$separateur; //3
$out .= $line_echange->fk_user_create.$separateur; //4
$out .= $line_echange->fk_user_mod.$separateur; //5
$out .= $line_echange->titre.$separateur; //6
$out .= $line_echange->desc.$separateur; //7
$out .= $line_echange->action.$separateur; //8
$out .= $line_echange->id_interlocuteur.$separateur; //9
$out .= $line_echange->interlocuteur.$separateur; //10
$out .= $line_echange->date_realise.$separateur; //11
$out .= $line_echange->URfirstname.$separateur; //12
$out .= $line->fk_secteur.$separateur; //13
$out .= $line_echange->id.$separateur; //14
$out .= $line->fk_tiers.$separateur; //15
$out .= $line->fk_priorite.$separateur; //16
$out .= $line_echange->PrenomCreateur.' '.$line_echange->NomCreateur.$separateur; //17
$out .= $line_echange->PrenomMod.' '.$line_echange->NomMod.$separateur; //18
$out .= $line_echange->NomMod.$separateur; //19
if (!empty($line_echange->date_realise))
	$out .= '&nbsp&nbsp&nbsp&nbsp Realisee par '.$line_echange->URfirstname. ' '.$line_echange->URlastname.' le '.dol_print_date($line_echange->date_realise,"%d/%m") .$separateur;
else
	$out .= $separateur;
$out .= $line_echange->Interphone.$separateur; //21
$out .= $line_echange->Interemail.$separateur; //22
$out .= $line_echange->URlastname.$separateur;//23

$out .= $line->secteur.$separateur; //24
$out .= $line->priorite.$separateur; //25
$out .= $line->coulpriorite.$separateur; //26

print $out;
?>