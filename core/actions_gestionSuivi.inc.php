<?php
/* 
 * Version CAV - 2.7 - été 2022 - Migration Dolibarr V15
 * Version CAV - 2.8 - hiver 2023 - correction technique
 *					 			  - Installation popup Modif/creation Suivi pour Inscription/Location
 *								   - fiabilisation des foreach
 * Version CAV - 2.8.3 - printemps 2023
 *		- ajout suppression echange dans pavesuivi
 *
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
* or see http://www.gnu.org/
*/

/**
 *	\file			htdocs/core/actions_sendSMSs.inc.php
 *  \brief			Code pour actions sépcifiques à la gestion du suivi dans cglinscription
 */


require_once(DOL_DOCUMENT_ROOT."/custom/CahierSuivi/class/suivi_client.class.php");
/*
 * Add file in SMS form
 */
 
if ('VARIABLE' =='VARIABLE')
{
	global $CREE_TIERS_BULL_DOSS, $CREE_BULL_DOSS , $action;
	global $ENR_ECH, $EDIT_ECH , $SUP_ECH, $CONF_SUP_ECH;
	global $Iddossier, $Idechange;
	global $suivi_action, $suivi_titre, $suivi_description, $suivi_idInterlo;
			
 
	$CREE_BULL_DOSS='CreeBullDos';
	$CREE_TIERS_BULL_DOSS='CreeTiersBullDos';
	$ENR_ECH='ENREchange';
	$EDIT_ECH='EditEchange';
	$SUP_ECH='SupEchange';
	$CONF_SUP_ECH='ConfSupEchange';
			
	
	if ($action == $CREE_BULL_DOSS) { // Arrivée par 1ere page Inscription ou Suivi Tiers	- dossier pouvant être connu	
		$tbrowid = array();
		$tbrowid = GETPOST("rowid", 'int');
		$nvdossier = GETPOST('nvdossier','alpha');
		$rdnvdoss = GETPOST('rdnvdoss','alpha');
		$priorite = GETPOST('priorite','int');  // variablble URL revenant de la demande création Dossier dans page Initiale Tiers/dossier de  BU/LO/RESA
		$Refdossier =  GETPOST('rdselectdoss','int'); 
	}
	if ($action == $CREE_TIERS_BULL_DOSS) { // Arrivée par 1ere page Inscription - Nouveau Tiers - dossier nouveau
		$nvdossier = GETPOST('nvdossier','alpha');
		$rdnvdoss = GETPOST('rdnvdoss','alpha');
		$priorite = GETPOST('priorite','int');  // variablble URL revenant de la demande création Dossier dans page Initiale Tiers/dossier de  BU/LO/RESA
	}
	
	$Iddossier = GETPOST("dossier",'int');
	$Idechange = GETPOST("arg_idEchange",'int');
	$suivi_action = GETPOST("arg_action",'alpha');
	$suivi_titre = GETPOST("arg_titre",'alpha');
	$suivi_description = GETPOST("arg_description");
	$suivi_idInterlo = GETPOST("arg_idtiersLig",'int');
}

/*
 * Enregistrer ligne echange
 */
 
if (GETPOST('btEnrEcg','alpha'))
{	
	$echange = new cgl_dossier($db);
	$ret = $echange->Maj_echange( $Idechange , $Iddossier,  $suivi_action, $suivi_titre, $suivi_description, $suivi_idInterlo , '' , $user->id, $user->id);
	if ($ret < 0 ) {
		$error++;
		setEventMessages($langs->trans("ErrorSQL").' '.$langs->transnoentitiesnoconv("ErrEchg"),'','errors');
	}	
	elseif ($ret > 0) {
		$Idechange = $ret;
		$flMAJBase = true;
	}
	unset ($echange);
}
/*
* Supprimer un echange
*/
if ($action == $CONF_SUP_ECH and GETPOST('confirm','alpha')=='yes') {
	$wline_echange = new cgl_echange($db);
	$wline_echange->id = $Idechange;
	$ret = $wline_echange->delete();
	$Idechange ='';
	
	if ($ret >= 0) {
		$flMAJBase = true;
	}
	else {
		$error++;
		setEventMessages($langs->trans("ErrorSQL").' '.$langs->transnoentitiesnoconv("ErrSupEchange"),'','errors');
	}	
}


