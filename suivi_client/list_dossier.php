<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2014 -CigaleAventure and claude@cigaleaventure.com---
 *
 * Version CAV - 2.7 - été 2022
 *					 - remplacer method=GET par method=FORM
 *					 - Migration Dolibarr V15
 * Version CAV - 2.8 - hiver 2023 - Pagination (suppression Ajout)
 * 								- vérification de la fiabilité des foreach
 *								- Nom générique si oublie saisie nom du dossier
 *								- Fenêtre modale pour modif pour echange
 *								- reassociation BU/LO à un autre contrat
 * Version CAV - 2.8.2 - printemps 2023
 *		- afficher le détail du dossier même s'il est cloturé
 * Version CAV - 2.8.3 - printemps 2023
 *		- suppression des guillemets
 *		- le téléphone/mail des tiers/contacts s'affichent, l'action réalisée ne s'affiche plus (bug 291-92)
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
 */

/**
 *   	\file       custom/CahierSuivi/suivi_client/list.php
 *		\ingroup    cahiersuivi
 *		\brief      Cahier d'accueil - Liste les échanges avec client
 
 * Module cahier d'accueil
 * liste 1 provenant de Tiers: une ligne par dossier et un bouton Ajout d'un echange
 * liste 2 : provenant de Tiers ou Client: argument Client : liste des dossiers d'un client + une ligne d'ajout d'un echange pour ce client
 * liste 3 : provenant de Tiers ou Client: argument Dossier : liste des échanges d'un dossier
 * saisie des table des secteurs et des priorité afin de mettre de la couleur  - dans Dico de Configuration systeme 
 * 
 */
 ini_set('magic_quotes_gpc', 1);
if ('Include' == 'Include') {
	// Change this following line to use the correct relative path (../, ../../, etc)
	$res=0;

	if (file_exists("../../../main.inc.php")) $res=@include '../../../main.inc.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
	require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
	require_once ('../class/html.suivi_client.class.php');
	require_once ('../class/suivi_client.class.php');
	require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
	require_once(DOL_DOCUMENT_ROOT."/core/class/html.form.class.php");

	// Load traductions files requiredby by page
	$langs->load("other");
	$langs->load("companies");
	$langs->load("cahiersuivi@CahierSuivi");
}

if ('GETPOST' == 'GETPOST') {
	// Get parameters
	$page		= GETPOST("page",'int');

	// récupération des paramètre de l'URL - paramètre
	$TypeListe=trim(GETPOST("typeliste"), 'alpha');
	$listtotal = GETPOST('listtotal', 'alpha');
	$limit = GETPOST('limit', 'alpha');
	if (empty($limit)) $limit = $conf->liste_limit;
	$page = GETPOST('page', 'alpha');
	$listrestreint = GETPOST('listrestreinte', 'alpha');
	$btaction = GETPOST('btaction', 'alpha');

	if (!empty( GETPOST('btactionenr1', 'alpha') ) or !empty( GETPOST('btactionenr2', 'alpha')))
			$btaction = "Enregistrer";	
	if ( !empty(GETPOST('btactionenrret1', 'alpha') ) or   !empty(GETPOST('btactionenrret2', 'alpha')  ))
			$btaction = 'EnrRetCah';
	// récupération des paramètre de l'URL - selection
	$search_dateCreationday=trim(GETPOST("search_dateCreationday", 'int'));
	$search_dateCreationmonth=trim(GETPOST("search_dateCreationmonth", 'int'));
	$search_dateCreationyear=trim(GETPOST("search_dateCreationyear", 'int'));
	$search_dateCreation = dol_mktime(12, 0 , 0, $search_dateCreationmonth, $search_dateCreationday, $search_dateCreationyear);

	$search_Mod=trim(GETPOST("search_Mod", 'int'));
	$search_Createur=trim(GETPOST("search_Createur", 'int'));
	$search_tiers=trim(GETPOST("search_tiers", 'int'));
	$search_secteur=trim(GETPOST("search_secteur", 'int'));
	$search_typedossier=trim(GETPOST("search_typedossier", 'int'));
	$search_priorite=trim(GETPOST("search_priorite", 'int'));
	$search_interlocuteur=trim(GETPOST("search_interlocuteur", 'int'));

	
	// récupération des paramètre de l'URL - saisie
	$socid=trim(GETPOST("socid", 'int'));
	//if (!empty($search_tiers)) $socid = $search_tiers; else $search_tiers = $socid;
	$arg_typedossier=trim(GETPOST("arg_typedossier", 'int'));
	$arg_secteur=trim(GETPOST("arg_secteur", 'int'));
	
		
	// Traitement des champs concernant les tiers
	//	*Cart ==> saisie provenant de la cartouche du dossier
	//  *Lig ==> saisie provenant de la ligne de saise (création ou modification d'échange et/ou dossier)
	// 
		$arg_idtiers = trim(GETPOST("arg_idtiersCart", 'int'));
		if (GETPOST("arg_nvtiersCart", 'alpha') == 'nouveau tiers') $arg_nvtiers = '';
		else $arg_nvtiers = trim(GETPOST("arg_nvtiersCart", 'alpha')) ;		
		
		$arg_idInterlo = trim(GETPOST("arg_idtiersLig", 'int'));
		$arg_nominterl=trim(GETPOST("search_arg_idtiersLig", 'alpha'));
		if (GETPOST("arg_nvtiersLig", 'alpha') == 'nouveau tiers') $arg_nvinterl = '';
		else $arg_nvinterl = trim(GETPOST("arg_nvtiersLig", 'alpha')) ;	



	// Récupération des parametres propres à Dossier et tiers
	$Reftiers=trim(GETPOST("Reftiers", 'int'));
	$Refdossier=trim(GETPOST("Refdossier", 'int'));
	$arg_dossanstiers=trim(GETPOST("arg_dossanstiers", 'alpha'));
	
	
		//$arg_dossier=trim(GETPOST("arg_dossier", 'alpha'));
		$arg_dossier=$_GET["arg_dossier"];
		if (empty($arg_dossier)) $arg_dossier=$_POST["arg_dossier"];

		$arg_id_dossier=$Refdossier;
		
		$arg_teltiers=trim(GETPOST("arg_telCart", 'alpha'));
		$arg_telsuptiers=trim(GETPOST("arg_telSupCart", 'alpha'));
		$arg_mailtiers=trim(GETPOST("arg_mailCart", 'alpha'));
		$arg_mailsuptiers=trim(GETPOST("arg_mailSupCart", 'alpha'));
		
		$arg_telInterl=trim(GETPOST("arg_telLig", 'alpha'));	
		$arg_mailInterl=trim(GETPOST("arg_mailLig", 'alpha'));
	

/*	
print '<p>DEBUG INit Refdossier:'.$Refdossier.'</p>';
print '<p>DEBUG INit Reftiers:'.$Reftiers.'</p>';
print '<p>DEBUG INit arg_id_dossier:'.$arg_id_dossier.'</p>';
print '<p>DEBUG INit arg_dossier:'.$arg_dossier.'</p>';	
print '<p>DEBUG INit arg_idtiers:'.$arg_idtiers.'</p>';	
print '<p>DEBUG INit arg_nvtiers:'.$arg_nvtiers.'</p>';	
print '<p>DEBUG INit arg_nomtiers:'.$arg_nomtiers.'</p>';	
print '<p>DEBUG INit arg_idInterlo:'.$arg_idInterlo.'</p>';
print '<p>DEBUG INit arg_nominterl:'.$arg_nominterl.'</p>';	
print '<p>DEBUG INit arg_teltiers:'.$arg_teltiers.'</p>';	
print '<p>DEBUG INit arg_telsuptiers:'.$arg_telsuptiers.'</p>';
print '<p>DEBUG INit arg_mailtiers:'.$arg_mailtiers.'</p>';	
print '<p>DEBUG INit arg_mailsuptiers:'.$arg_mailsuptiers.'</p>';			

print '<p>DEBUG INit arg_telInterl:'.$arg_telInterl.'</p>';	
print '<p>DEBUG INit arg_mailInterl:'.$arg_mailInterl.'</p>';	
	
	/* saisie dans la parite haute de tiers et dossier 
	$arg_teltiers=trim(GETPOST("arg_teltiers", 'alpha'));
	$arg_teltiers = str_replace(' ', '', $arg_teltiers);
	$arg_telsuptiers=trim(GETPOST("arg_telsuptiers", 'alpha'));
	$arg_telsuptiers = str_replace(' ', '', $arg_telsuptiers);
	$arg_mailtiers=trim(GETPOST("arg_mailtiers", 'alpha'));
	$arg_mailsuptiers=trim(GETPOST("arg_mailsuptiers", 'alpha'));	
	
	$arg_tel=trim(GETPOST("arg_tel", 'alpha'));
	if ($arg_tel == 'Error') $arg_tel ='';
	$arg_tel = str_replace(' ', '', $arg_tel);
	$arg_mail=trim(GETPOST("arg_mail", 'alpha'));
	if ($arg_mail == 'Error') $arg_mail ='';
	$arg_telsup=trim(GETPOST("arg_telsup", 'alpha'));
	$arg_telsup = str_replace(' ', '', $arg_telsup);
	$arg_mailsup=trim(GETPOST("arg_mailsup", 'alpha'));
	
	*/

	
	$arg_createur=trim(GETPOST("arg_createur", 'int'));
	$arg_modificateur=trim(GETPOST("arg_modificateur", 'int'));
//CCA
	$arg_referent=trim(GETPOST("arg_referent", 'int'));
	$arg_nb=trim(GETPOST("arg_nb", 'int'));
	$arg_action=GETPOST("arg_action", 'alpha');
	//$arg_action=$_GET["arg_action"];
	//if (empty($arg_action)) $arg_action=$_POST["arg_action"];
		
	$arg_titre=trim(GETPOST("arg_titre", 'alpha'));
	//$arg_titre=$_GET["arg_titre"];
	//if (empty($arg_titre)) $arg_titre=$_POST["arg_titre"];
	$arg_priorite=trim(GETPOST("arg_priorite", 'int'));
	$arg_origine=trim(GETPOST("arg_origine", 'int'));
	
	$arg_description=GETPOST("arg_description", 'alpha');
	//$arg_description=$_GET["arg_description"];
	//if (empty($arg_description)) $arg_description=$_POST["arg_description"];
	$arg_idEchange=trim(GETPOST("arg_idEchange", 'alpha'));
	
	$dateechg 	= GETPOST('dateechg','date');
	$dateechgyear 	= GETPOST('dateechgyear','int');
	$dateechgmonth 	= GETPOST('dateechgmonth','int');
	$dateechgday 	= GETPOST('dateechgday');
	$dateechghour = GETPOST('dateechghour','int');
	$dateechgmin = GETPOST('dateechgmin','int');
	$st_dateechg =  $dateechg.$dateechghour.$dateechgmin;
	$dateechgmonth = substr('000'.$dateechgmonth	, strlen('000'.$dateechgmonth)-2);
	$dateechgday = substr('000'.$dateechgday	, strlen('000'.$dateechgday)-2);
	if ($dateechgyear and $dateechgmonth and dateechgday and dateechghour and $dateechgmin) {
		$dt_dateechg = new DateTime($dateechgyear.'-'. $dateechgmonth.'-'.$dateechgday.' '.$dateechghour.':'.$dateechgmin );		
		$st_dateechg = $dateechgyear.$dateechgmonth.$dateechgday.$dateechghour.$dateechgmin;
	}

	$sortfield=GETPOST("sortfield",'alpha');
	$sortorder=GETPOST("sortorder",'alpha');
	$page = GETPOST('page', 'int');
	$sortfield=" e.datec";
	if (empty($sortorder)) $sortorder=" DESC ";
		
		
	// Gestion des pages d'affichage des dossiers
	if ($page == -1)  $page = 0 ; 
	if (empty($page)) $page = 0; 
	$offset = $limit * $page ;
	$pageprev = $page - 1;
	$pagenext = $page + 1;
}
		
// Protection if external user
if ($user->societe_id > 0)  {
	accessforbidden();
}
/***************************************************
* VARIABLES
****************************************************/ 
if ('Variable'=='Variable') {
	$form=new Form($db);
	$waff = new FormCglSuivi($db) ;
	$help_url='FR:Module_Inscription';
	
	$line = new cgl_dossier($db);	
	$line_echange = new cgl_echange($db);
	$linerecup = new cgl_dossier($db);	
	$line_echangerecup = new cgl_echange($db);		
	$tiers= new Societe($db);
	$linesoc = new cgl_dossier($db);	
	// renvoyer l'URL sans les info pour créer un dossier, ainsi, on limitera les possibilité de créer des doublons
	if (!empty($listtotal)) $param = '&listtotal='.$listtotal;
	else if (!empty($listrestreinte)) $param = '&listrestreinte='.$listrestreinte;
	$param .= '&Refdossier='.$Refdossier.'&Reftiers='.$Reftiers.'&socid='.$Reftiers;
	$locationUrlRetour= "Location: " . $_SERVER ['PHP_SELF'] . "?typeliste=dossier".$param ;

}

// ENREGISTRER
if ($btaction =='Enregistrer' or $btaction =='EnrRetCah' ){ 
	// verification saisie 		
	global $error;
	$error = 0;
	// DETERMINATION ENVIRONNEMENT DOSSIER - ECHANGE - TIERS
	if ( !empty($arg_action) or !empty($arg_titre) or !empty($arg_description))
		$fglEnchangevide = false;
	else 
		$fglEnchangevide = true;	

	if (($arg_typedossier == -1 or empty($arg_typedossier))
			and ($arg_id_dossier == -1 or empty($arg_id_dossier))
			and empty($arg_dossier) and empty($arg_dossier)	
			and ($arg_secteur == -1 or empty($arg_secteur))
			and empty($arg_nb) 
			and ($arg_priorite == -1 or empty($arg_priorite))
			and ($arg_origine == -1 or empty($arg_origine))	
			)
		$fglDossiervide = true;
	else $fglDossiervide = false;	

	// GESTIONS DES ERREURS DE SAISIE
	// test si type dossier vide sur dossier inconnu
	if ($arg_typedossier == -1 and $arg_id_dossier == -1  and !$fglDossiervide  )  {
		$error++;	
		$texterror = 'errors';
		setEventMessages($langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("typedossier")),'',$texterror);
	}	
	// test si dossier non selectionner et non saisie comme nouveau
	if ($arg_id_dossier == -1  and empty($arg_dossier) and !$fglDossiervide ) {
		$arg_dossier =$langs->trans( 'GenDosCli');		
	}
	// TIERS 
	if (!( $arg_dossanstiers == 'oui' and ($arg_id_dossier <> -1 or empty($arg_dossier))) 
		and (!( $arg_dossanstiers == 'oui' and  $arg_id_dossier > 0 ))) // ne rien faire si on a un nouveau dossier sans tiers dans l'écran general
	{
		if (!empty($arg_nvtiers) or (!empty($arg_idtiers) and $arg_idtiers <> -1)) {
			$ret = $line->Maj_tiers($arg_idtiers, $arg_nvtiers, $arg_teltiers, $arg_telsuptiers, $arg_mailtiers, $arg_mailsuptiers,  $user);
			if ($ret < 0) {
				$error++;
				//setEventMessages($langs->trans("ErrorSQL",$langs->transnoentitiesnoconv("ErrTiers")),'','errors');
			}	
			elseif ($ret > 0) {
				 $arg_idtiers = $ret;
				 // il se peut que l'on vienne de changer le tiers du dossier
					$Reftiers = $arg_idtiers ;	
			}
		}	
		elseif (empty($arg_idtiers) and $Reftiers <> $arg_idtiers ) {
			// demande de suppression du référent du dossier (sans suppression du tiers) - Confirmation
			$form = new Form($db);
			$wline_echange = new cgl_echange($db);
			$wline_echange->fetch($arg_idEchange);
			$question=$langs->trans('ConfEffaceReferentQuest');
			$titre = $langs->trans('ConfEffaceReferent');
			//
			$formconfirm=$form->formconfirm($_SERVER['PHP_SELF'].'?typeliste='.$TypeListe.'&Refdossier='.$Refdossier.'&btaction=SupReferent'.'&Reftiers='.$Reftiers ,$titre,$question,'ConfSUPReferent','','no',2);
			unset ($form);
			unset ($wline_echange);
			print $formconfirm;	
		}
	}

	// INTERLOCUTEUR
	//if (!empty($arg_idtiers) and !empty($arg_idInterlo) and $arg_idtiers <> $arg_idInterlo) {
	if ((( !empty($arg_idInterlo) and $arg_idInterlo <> -1 and empty($arg_idtiers) ) 
		or ( !empty($arg_idInterlo) and $arg_idInterlo <> -1 and !empty($arg_idtiers) and $arg_idtiers <> $arg_idInterlo)
		or !empty($arg_nvinterl))
		and $arg_nvtiers <> $arg_nvinterl	)	{

		$ret = $line->Maj_tiers($arg_idInterlo, $arg_nvinterl, $arg_telInterl,'', $arg_mailInterl, '',  $user);
		if ($ret < 0) {
			$error++;
			 // setEventMessages($langs->trans("ErrorSQL",$langs->transnoentitiesnoconv("ErrInter")),'','errors');
		}	
		elseif ($ret > 0) 		$arg_idInterlo = $ret;		
	}
	// DOSSIER
	$db->begin();
	if ($error == 0 and empty($texterror) and !$fglDossiervide) {
		$ret = 0;	
		//if (!($arg_id_dossier <> -1 and !empty($arg_nvdossier) and $TypeListe <> 'dossier')) {
			if  ($arg_dossanstiers == 'oui')  $widtiers = ''; // le cas de l'écran Général, en création de dossier réputé sans tiers
			else $widtiers = $arg_idtiers;
			$ret = $line->Maj_dossier($arg_id_dossier, $arg_dossier, $arg_typedossier, $arg_secteur, $arg_nb,  $arg_priorite, $arg_origine, $widtiers, $user->id, $arg_referent);
			if ($ret < 0) {
					$error++;
					 setEventMessages($langs->trans("ErrorSQL").' '.$langs->transnoentitiesnoconv("ErrMAJDossier"),'','errors');
			}	
			elseif ($ret > 0) 	$arg_id_dossier = $ret;		
		//}		
	}	
	
	// 	ECHANGE
	if ($error == 0 and empty($texterror) and !$fglDossiervide and !$fglEnchangevide) {
			$ret = $line->Maj_echange( $arg_idEchange, $arg_id_dossier,  $arg_action, $arg_titre, $arg_description, $arg_idInterlo , $st_dateechg, $arg_createur, $arg_modificateur);
			if ($ret < 0 ) {
				$error++;
				 setEventMessages($langs->trans("ErrorSQL").' '.$langs->transnoentitiesnoconv("ErrEchg"),'','errors');
			}	
			elseif ($ret > 0) 	$arg_id_echange = $ret;
	}
	
	if ($error == 0) $db->commit();
	else $db->rollback();
	if ((empty($error) or $error == 0 )  and $fglEnchangevide and !$fglDossiervide) {	
		$arg_typedossier="";
		$arg_id_dossier="";
		$arg_dossier="";
		if ($texterror <> 'warnings') {
			$arg_nvtiers="";
			$arg_idtiers="";
			$arg_teltiers="";
			$arg_mailtiers="";
		}
		$arg_secteur="";
		$fk_user_create = "";
		$arg_nb="";
		$arg_action="";
		$arg_titre="";
		$arg_nvinterl="";
		$arg_idInterlo="";
		$arg_telInterl="";
		$arg_mailInterl="";
		$arg_priorite="";
		$arg_origine="";
		$arg_description="";
		$arg_idEchange="";
/*			unset($arg_tbaction );
			unset($arg_tbdesc);
			unset($arg_tbtitre);
*/
	}	

	if (empty($error) or $error == 0 ) {
		if ($btaction =='EnrRetCah'){
			header('Location: list_cahier.php?typeliste=generale&idmenu=160&idmenu=16945&mainmenu=CahierSuivi&leftmenu=');
			exit;
		}
		else	Header($locationUrlRetour );
		exit;
	} 

}


// Enlever le referent d'un dossier
$confirm = GETPOST('confirm','alpha');
if ($btaction == 'SupReferent' and (!empty($arg_id_dossier)) ) {	
	if ( $confirm == 'yes') { 
		$wdossier = new cgl_dossier($db);
		$ret = $wdossier->dossiersanstiers($arg_id_dossier);
		unset ($wdossier );		
		if (empty($error) or $error == 0 ) {
			Header($locationUrlRetour );
			exit;
		} 
	}
	else	{
		$arg_idtiers = $Reftiers;
	}
}

// SUPPRIMER un echange
if ($btaction == 'ConfSupprime' and GETPOST('confirm','alpha')=='yes') {
	//print '<p>SUPPRIME id_echange :'.$arg_idEchange.'<</p';
	$wline_echange = new cgl_echange($db);
	$wline_echange->id = $arg_idEchange;
	$ret = $wline_echange->delete();
	$arg_idEchange ='';
	
	if (empty($error) or $error == 0 ) {
		Header($locationUrlRetour );
		exit;
	} 
}


/* préparation de la navigation */
if ('navigation' == 'navigation')  
{
   $arrayofmassactions = array();
    $massactionbutton = $form->selectMassAction('', $arrayofmassactions);

/*
	$newcardbutton = '';
	if ($action != 'addline' && $action != 'reconcile')
	{
		if (empty($conf->global->BANK_DISABLE_DIRECT_INPUT))
		{
			if (empty($conf->global->BANK_USE_OLD_VARIOUS_PAYMENT))	// If direct entries is done using miscellaneous payments
			{
			    $newcardbutton = dolGetButtonTitle($langs->trans('AddBankRecord'), '', 'fa fa-plus-circle', DOL_URL_ROOT.'/compta/bank/various_payment/card.php?action=create&accountid='.$search_account.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.urlencode($search_account)), '', $user->rights->banque->modifier);
			}
			else												// If direct entries is not done using miscellaneous payments
			{
                $newcardbutton = dolGetButtonTitle($langs->trans('AddBankRecord'), '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?action=addline&page='.$page.$param, '', $user->rights->banque->modifier);
			}
		}
		else
		{
            $newcardbutton = dolGetButtonTitle($langs->trans('AddBankRecord'), '', 'fa fa-plus-circle', $_SERVER["PHP_SELF"].'?action=addline&page='.$page.$param, '', -1);
		}
	}
*/
	$morehtml = '<div class="inline-block ">';
//	$morehtml .= '<label for="pageplusone">'.$langs->trans("Page")."</label> "; // ' Page ';
//	$morehtml .= '<input type="text" name="pageplusone" id="pageplusone" class="flat right width25 pageplusone" value="'.($page + 1).'">';
//	$morehtml .= '/'.$nbtotalofpages.' ';
	$morehtml .= '</div>';
/*	if ($action != 'addline' && $action != 'reconcile')
	{
		$morehtml .= $buttonreconcile;
	}
*/
	//$morehtml .= '<!-- Add New button -->'.$newcardbutton;

}


/***************************************************
* ENTETE
****************************************************/ 

 llxHeader('','');
 print $waff->PrepScript($TypeListe) ;
 
require_once DOL_DOCUMENT_ROOT."/custom/cglavt/core/js/info_bulle.js";

print '<script type="text/javascript" src="'.DOL_URL_ROOT.'/custom/cglavt/core/js/lib_filtre_car_saisie.js"></script>'."\n";
global $event_filtre_car_saisie;
$event_filtre_car_saisie = '';
$event_filtre_car_saisie .= " onchange='remplaceToutGuillements(this, event);return false;'";
// On privilégie le travail lors de la fin de la saisie, pour récupéré les copier/coller, plutot que le changement imédiat sur l'écran, pour lisibilité
//$event_filtre_car_saisie .= " onkeypress='remplaceGuillements(this, event);return false;'";



/***************************************************
* ACTIONS
****************************************************/ 
// Clique sur bouton Vider les filtres
if (GETPOST("button_removefilter_x",'alpha')){
	$btaction='';
	$search_dateCreation='';
	$search_Createur='';
	$search_Mod='';
	$search_tiers='';
	$search_secteur='';
	$search_interlocuteur='';
	$search_typedossier='';
	$search_priorite='';
	$sortfield=" e.datec";
	$sortorder=" DESC ";
}
//print '<p>btaction:'.$btaction.'<p>';
// CONFIRMER la SUPRESSION d'un echange
if ($btaction == 'Supprime' ) {
	$form = new Form($db);
	$wline_echange = new cgl_echange($db);
	$wline_echange->fetch($arg_idEchange);
	$question=$wline_echange->titre;
	$titre = $langs->trans('ConfEffacerEchange');
	$formconfirm=$form->formconfirm($_SERVER['PHP_SELF'].'?typeliste='.$TypeListe.'&Reftiers='.$Reftiers.'&Refdossier='.$Refdossier.'&btaction=ConfSupprime&arg_idEchange='.$arg_idEchange ,$titre,$question,'ConfSUPActPaimt','','',2);
	unset ($form);
	unset ($wline_echange);
	print $formconfirm;	
}

/***************************************************
* DONNEES
* construction SQL de recherche - liste 1
****************************************************/
if ('OrdreSql'=='OrdreSql') {
	// Recherche du dossier du tiers passé en argument - tiers du dossier et interlocuteur d'un echange du dossier
	$sql = "SELECT DISTINCT d.rowid , d.fk_soc,  d.datec, d.tms,   d.nb  , d.libelle , d.fk_secteur, d.fk_priorite,  d.fk_moduser, d.fk_createur  ";
	$sql .= ", ssect.label as secteur , ssect.color as coulsecteur, spri.label as priorite, spri.ordre as Ord_priorite, spri.color as coulpriorite " ;
	$sql .= ",  st.nom as nomTiers, st.phone as TiersTel, st.email, stex.s_tel2 as TiersSupTel,  stex.s_email2 as TiersSupMail, p.code as country_code ";
	$sql .= ", st.client, st.fournisseur,  cr.label as origine ";
	$sql .= ", styd.label as  typedossier , styd.rowid as  fk_typedossier  ";	
	$sql .= ", uc.lastname as NomCreateur, uc.firstname as PrenomCreateur, uc.rowid as fk_Createur ";
		$sql .= ", e.rowid as IdEchang,  e.datec as EchDate, e.tms as Echtms";
		$sql .= " , e.description, e.titre, e.action ";
		$sql .= ", inter.rowid as idInter, inter.nom as nomInter, inter.phone as InterTel, inter.email as Interemail ";
		$sql .= ", interex.s_tel2 as InterSupTel,  interex.s_email2 as InterSupMail,  pinter.code as Icountry_code";
		$sql .= ",   ume.lastname as NomMod, ume.firstname as PrenomMod , ume.rowid as fk_Moduser";
		$sql .= ", uce.lastname as NomCreateurEch, uce.firstname as PrenomCreateurEch, uce.rowid as fk_CreateurEch ";
			$sql .= ", date_realise, ur.lastname as URlastname, ur.firstname as URfirstname";

	$sql .= " FROM " . MAIN_DB_PREFIX . "cglavt_dossier as d ";	
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cglavt_dossierdet as e on fk_dossier = d.rowid";	
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as inter ON e.fk_soc = inter.rowid  ";	
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_extrafields as interex ON interex.fk_object = inter.rowid  ";		
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_country as pinter ON inter.fk_pays = pinter.rowid';
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "user as ume  ON e.fk_user_mod = ume.rowid";	
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "user as uce  ON e.fk_user_create	 = uce.rowid";	
		$sql .=  " LEFT JOIN " . MAIN_DB_PREFIX . "user as ur  ON user_realise = ur.rowid";	
	 	
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as st ON d.fk_soc = st.rowid  ";	
    $sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_country as p ON st.fk_pays = p.rowid';
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_extrafields as stex ON stex.fk_object = st.rowid  ";	
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cglavt_c_secteur as ssect on d.fk_secteur = ssect.rowid";	
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cglavt_c_priorite as spri on fk_priorite = spri.rowid";
	$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cglavt_c_typedossier as styd on fk_typedossier = styd.rowid";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_input_reason as cr on cr.rowid = fk_origine";		
	$sql .= " LEFT JOIN ". MAIN_DB_PREFIX . "user as uc	 on d.fk_createur = uc.rowid ";		
	$sql .= " WHERE d.entity IN (" . getEntity('agsession') . ")";		
//	if (empty($listtotal) and $search_priorite <> 2  and (empty($search_tiers) or $search_tiers == -1) ) 
//		$sql .= " AND (spri.label not like '%CLOTURE%'  or isnull(spri.label))";

	$sql .= " AND d.rowid = '".$Refdossier."'";

	if ($search_dateCreation ){
		//$sql.= " AND  d.datec >= '".$w->transfDateMysql($search_dateCreation)."' ";
		$sql.= " AND  d.datec >= '".dol_print_date($search_dateCreation,"%Y-%m-%d")."' ";
	}
	if ($search_Createur > 0 ) {
		$sql.= " AND uce.rowid ='".$db->escape($search_Createur)."'";
	}
	if ($search_tiers and !($search_tiers == -1)) { // search_tiers est à -1 quand on n'a pas fait de choix dur tiers à cause du select
		//$sql.= ' AND st.nom like "%'.$db->escape($search_tiers).'%"' ;
			if ($search_tiers == -999) {
				// on veut les dossiers sans tiers 
				$sql .= ' AND ( d.fk_soc = 0) ';
			}
			else {
				$sql .= ' AND ( st.rowid = "'.$search_tiers.'")';
			}
		
	}
	if ($search_secteur > 0 ) {
		$sql.= " AND ssect.rowid ='".$db->escape($search_secteur)."'";
	}

	if ($search_priorite>0 ) {
		$sql.= " AND spri.rowid ='".$db->escape($search_priorite)."'";
	}

	if ($search_typedossier>0 ) {
		$sql.= " AND fk_typedossier ='".$db->escape($search_typedossier)."'";
	}

	if ($search_interlocuteur and !($search_interlocuteur == -1)) { // search_tiers est à -1 quand on n'a pas fait de choix dur tiers à cause du select
		$sql.= ' AND ( st.rowid = '.$search_interlocuteur ;
		$sql .=' or  inter.rowid = '.$search_interlocuteur ;
		$sql .=')';			
	}
	//******************* LECTURE
	// Compte le nb total d'enregistrements
	$nbtotalofrecords = 0;

	if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)){
		$result = $db->query($sql);
		if ($result	)   		$nbtotalofrecords = $db->num_rows($result);
	}
	//$sql.= $db->order($sortfield,$sortorder);
	$sql .= ' ORDER BY '.$sortfield.' DESC';
	$sql.= $db->plimit((int)$limit+1, $offset);
	$resql = $db->query($sql);
	if ($resql	)   	$num = $db->num_rows($resql);
}
//print $sql;

/***************************************************
* AFFICHAGE 
****************************************************/ 
if ('Charge' == 'Charge') {
		$obj = $db->fetch_object($resql);

			$line->id 				= $obj->rowid 	;
			$line->fk_tiers 		= $obj->fk_soc 	;	
			$Reftiers = $obj->fk_soc 	;
			$line->datec 			= $obj->datec 	;
			$line->tms 				= $obj->tms 	;
			$line->dateAff 			=  $obj->dateAff	;
			$line->NomCreateur 		= $obj->NomCreateur 	;
			$line->PrenomCreateur 	= $obj->PrenomCreateur 	;	
			$line->NomCont 			= $obj->NomCont 	;
			$line->PrenomCont 	 	= $obj->PrenomCont 	;
			$line->typedossier 	= $obj->typedossier 	;
			$line->fk_typedossier 	= $obj->fk_typedossier 	;
			$line->dossier 			= $obj->libelle 	;
			$line->secteur 			= $obj->secteur 	;
			$line->fk_secteur 		= $obj->fk_secteur 	;
			$line->fk_priorite 		= $obj->fk_priorite 	;
			//$line->fk_user_mod 		= $obj->fk_createur 	;
			$line->fk_user_create	= $obj->fk_createur 	;
			$line->coulsecteur 		= $obj->coulsecteur 	;
			$line->nb 				= $obj->nb 	;
			$line->tms 				= $obj->tms 	;
			$line->priorite 		= $obj->priorite 	;
			$line->coulpriorite 	= $obj->coulpriorite 	;
			$line->NomTiers		 	= $obj->nomTiers 	;
			$line->TiersTel			= $obj->TiersTel;
			$line->TiersSupTel		= $obj->TiersSupTel;
			$line->TiersMail		= $obj->email;
			$line->TiersSupMail		= $obj->TiersSupMail;

			if ($obj->fk_soc > 0)
				$line->telmail=$waff->ChercheTelMailTiersContact($line->TiersTel	,$line->TiersSupTel, $line->TiersMail,$line->TiersSupMail	, $obj->fk_soc , $obj->country_code);

			$line->origine		 	= $obj->origine 	;		
			$line->descriptioncondense 	= $waff->AfficheCondenseEchange($line->id);
			$line->action_courante 		= $waff->ActionsARealiser($line->id) 	;

			$line_echange->datec	=  $obj->EchDate;
			$line_echange->tms	=  $obj->Echtms;
			$line_echange->id =	$obj->IdEchang	;;
			$texte=str_replace(array(chr(13).chr(10),chr(10)),'<br />',$obj->description);
			$line_echange->desc=	$texte;
			
			$line_echange->titre 			= $obj->titre 	;				
			
			$texte=str_replace(array(chr(13).chr(10),chr(10)),'<br />',$obj->action);
			$line_echange->action=	$texte;	

			$line_echange->id_interlocuteur	= $obj->idInter 	;
			$line_echange->interlocuteur	= $obj->nomInter 	;
			$line_echange->Interphone		= $obj->InterTel 	;
			$line_echange->InterSupTel 		= $obj->InterSupTel;
			$line_echange->InterSupMail 	= $obj->InterSupMail;
			$line_echange->Interemail 		= $obj->Interemail;		
			$line_echange->NomMod 			= $obj->NomMod 	;
			$line_echange->PrenomMod 		= $obj->PrenomMod 	;	
			$line_echange->NomCreateur 		= $obj->NomCreateurEch 	;
			$line_echange->PrenomCreateur 	= $obj->PrenomCreateurEch 	;
			$line_echange->fk_user_create 	= $obj->fk_CreateurEch 	;
			$line_echange->fk_user_mod 		= $obj->fk_Moduser 	;
			$line_echange->fk_user_realise	= $obj->user_realise 	;
			$line_echange->user_realise 	= $obj->URfirstname 	;
			if (empty($line_echange->user_realise))
				$line_echange->user_realise = $obj->URlastname 	;
			$line_echange->date_realise 	= $obj->date_realise 	;
			if ($obj->idInter > 0) 
				$line_echange->telmail = $waff->ChercheTelMailTiersContact($obj->InterTel, $obj->InterSupTel, $obj->Interemail,  $obj->InterSupMail, $obj->idInter, $obj->Icountry_code );
			//$socid = $obj->fk_soc 	;
			
}	
	
$url=$_SERVER["PHP_SELF"];

 print '<form method="FORM" action="'.$url.'?" name="formfilter">';
 print '<input type="hidden" name="typeliste" value="'.$TypeListe.'">';
 print '<input type="hidden" name="Refdossier" value="'.$Refdossier.'">';
 print '<input type="hidden" name="ID" value="'.$Reftiers.'">';
 print '<input type="hidden" name="Reftiers" value="'.$Reftiers.'">';
 print '<input type="hidden" name="socid" value="'.$Reftiers.'">';
 print '<input type="hidden" name="token" value="'.newtoken().'">';
 print '<input type="hidden" name="search_Mod" value="'.$search_Mod.'">'; 
 print '<input type="hidden" name="search_Createur" value="'.$search_Createur.'">';
 print '<input type="hidden" name="search_priorite" value="'.$search_priorite.'">';
 print '<input type="hidden" name="search_typedossier" value="'.$search_typedossier.'">';
 print '<input type="hidden" name="search_dossier" value="'.$search_dossier.'">';
 print '<input type="hidden" name="search_secteur" value="'.$search_secteur.'">';
 print '<input type="hidden" name="search_phone" value="'.$search_phone.'">';
 print '<input type="hidden" name="limit" value="'.$limit.'">';
 print '<input type="hidden" name="listtotal" value="'.$listtotal.'">';
 print '<input type="hidden" name="token" value="'.newtoken().'">';
/*
 print '<input type="hidden" name="search_interlocuteur" value="'.$search_interlocuteur.'">';
style="background-color:orange; border:1px solid black; color:yellow; font-size:150%; padding:1em
*/		
		
if ('Entete' == 'Entete') {
	// permet d'afficher le petit livre, le titre la succession des page et le numéro de la  page courante
	// paramètres a passer dans les boutons de page successives
	$params = "&amp;typeliste=".$TypeListe;
	$params .= "&amp;Refdossier=".$Refdossier;
	$params .= "&amp;ID=".$Refdossier;
	$params .= "&amp;search_dateCreation=".$search_dateCreation;
	$params .= "&amp;search_Mod=".$search_Mod."&amp;search_Createur=".$search_Createur;
	$params .= "&amp;search_priorite=".$search_priorite."&amp;search_typedossier=".$search_typedossier;
	$params.= "&amp;search_secteur=".$search_secteur;
	$params.= "&amp;search_interlocuteur=".$search_interlocuteur;
	//$params.= "&amp;sortfield=".$sortfield."&amp;sortorder=".$sortorder;
	$params .='&amp;Reftiers='.$Reftiers;
	$params.= "&amp;socid=".$search_tiers;	 
	//if (!empty($listtotal)) $params .= '&amp;listtotal='.$listtotal;
	//else if (!empty($listrestreint)) $params = '&amp;listrestreint='.$listrestreint;
	$params .='&amp;limit='.$limit;
	if ($search_phone != '')   $params.= "&amp;search_phone=".urlencode($search_phone);
	
$wparams=$params;
if (!empty($listtotal)) $wparams .= '&amp;listtotal='.$listtotal;
else if (!empty($listrestreint)) $wparams = '&amp;listrestreint='.$listrestreint;

	// TITRE
	if (empty($listtotal)) $titre = 'CS_ListeDosTitrePar';
	else $titre = 'CS_ListeDosTitreTot';
	$title=$langs->trans($titre);
	if ( !empty($socid)) 	$ret = 	$tiers->fetch($socid);
	if ( !empty($socid) and $ret > 0) 	{		
		$head = societe_prepare_head($tiers);
		//dol_fiche_head($head, 'customer', $langs->trans("ThirdParty"),0,'company');
		dol_fiche_head($head, 'tabcahiersuivi', $langs->trans("ThirdParty"),0,'company');
	}
	if ($error > 0 or $texterror == 'warnings' )  {  // erreur sur l'enregistrement on reprend les info saisies
		$linerecup->recupInfoDos($TypeListe);
		$line_echangerecup->recupInfoEch();
	}
	else $linerecup->fk_user_create = $user->id;
	// Affichage des info Tiers et Dossier			
	$waff->AfficheInfoCummune($TypeListe,$search_tiers, $line, $line_echange);
	// BOUTONS Nouvelle Insciption - Nouveau contrat - Enregistrer - Affichage complet/partiel - Retour au général
	$waff->boutons(1,$listtotal, $params) ;
	
	// affichage barre de sélection
	print_barre_liste($title, $page, $_SERVER["PHP_SELF"],$wparams,$sortfield,$sortorder,'',$num,$nbtotalofrecords, '', 0, $morehtml, '', $limit, 0, 0, 0);
	print '&nbsp';
	//print '<div style="overflow : scroll; border:1" >';
	print '<div style="border:1" >';
	$taille='100%';
	//print '<table class="liste"   border=1 style="overflow : scroll; width:'.$taille.'" >';
	print '<table class="liste"   border=1 >';



    // affiche la barre grise de titres des filtres
    // affiche la barre grise des filtres
	$waff->AfficheBandeauEntete($TypeListe, $listtotal);
 		
	
	print "<tr>\n";
	print "<td>\n";
	print "</td>\n";
	print "</tr>\n";
}
if ('Liste' == 'Liste') {
print '<div id="Liste ">';
	$var=True;
	$i=0;	
	print "<tr ".$bc[1].">";
	$i=0; 
	$var = 0;
	if (empty($btaction ) or $btaction == 'create'  or $btaction == 'Enregistrer' or $btaction == 'Supprime' or $btaction == 'edit'  or $btaction == 'ConfSupprime') {
		// on affiche  la ligne de saisie 
		//		en début de traitement (btaction vide)
		//		Enregistrement correct (btaction = Enregistrer et error=0
		//		enregistrement incorrect et warning (btaction = Enregistrer et texterror='warning')
		//		enregistrement incorrect et arg_idEchange vide
		//		pas de demande de modification (btaction = 'edit')
		//		supp non confirmé

		// on affiche  la ligne de saisie 
		//		en début de traitement (btaction vide)
		// 		on affiche une ligne de saisie vide si toute action précédente OK
		// sinon on récupère les information apssée en argument à l'URL
		if (!empty($btaction ) and ( ($btaction == 'Enregistrer' and $error == 0 )  or $btaction == 'ConfSupprime'))
			$waff->AfficheLignedossier($TypeListe,'','' ,1, 'saisie', $listtotal);	
			//elseif ($btaction == 'Enregistrer' and $error > 0  and empty($arg_idEchange)) 
		elseif (($btaction == 'Enregistrer' and $error > 0  ) or  empty($btaction))
			$waff->AfficheLignedossier($TypeListe,$linerecup,$line_echangerecup ,$var, 'Ajout', $listtotal);			
	
	}	
	while ($i < min($num,$limit))	{
		if ($i>0) {
			$obj = $db->fetch_object($resql); // dans le cas dossier, cela a déjà été lu 
			$line->id 				= $obj->rowid	;
			$line->fk_tiers 		= $obj->fk_soc 	;	
			$line->datec 			= $obj->datec 	;
			$line->tms 				= $obj->tms 	;
			$line->dateAff 			= $obj->dateAff	;
			$line->NomCreateur 		= $obj->NomCreateur 	;
			$line->PrenomCreateur 	= $obj->PrenomCreateur 	;	
			$line->NomCont 			= $obj->NomCont 	;
			$line->PrenomCont 	 	= $obj->PrenomCont 	;
			$line->typedossier 		= $obj->typedossier 	;
			$line->fk_typedossier 	= $obj->fk_typedossier 	;
			$line->dossier 			= $obj->libelle 	;
			$line->titre 			= $obj->titre 	;
			$line->desc 			= $obj->description 	;
			$line->secteur 			= $obj->secteur 	;
			$line->fk_secteur 		= $obj->fk_secteur 	;
			$line->fk_priorite 		= $obj->fk_priorite 	;
			//$line->fk_user_mod 		= $obj->fk_createur 	;
			$line->fk_user_create	= $obj->fk_moduser 	;
			$line->coulsecteur 		= $obj->coulsecteur 	;
			$line->nb 				= $obj->nb 	;
			$line->tms 				= $obj->tms 	;
			$line->priorite 		= $obj->priorite 	;
			$line->coulpriorite 	= $obj->coulpriorite 	;
			$line->action 			= $obj->action 	;
			$line->NomTiers		 	= $obj->nomTiers 	;
			$line->TiersTel			= $obj->TiersTel;
			$line->TiersSupTel		= $obj->TiersSupTel;
			$line->TiersMail		= $obj->email;
			$line->TiersSupMail		= $obj->TiersSupMail;
			if ($obj->fk_soc > 0)
				$line->telmail = $waff->ChercheTelMailTiersContact($line->TiersTel	,$line->TiersSupTel, $line->TiersMail,$line->TiersSupMail	, $obj->fk_soc , $obj->country_code);
			$line->origine		 	= $obj->origine 	;
						
			$line->descriptioncondense 	= $waff->AfficheCondenseEchange($line->id);
			
			$line->action_courante 		= $waff->ActionsARealiser($line->id) 	;
			
			$line_echange->datec	=  $obj->EchDate;
			$line_echange->tms	=  $obj->Echtms;
			$line_echange->id =	$obj->IdEchang	;
			
			$texte=str_replace(array(chr(13).chr(10),chr(10)),'<br />',$obj->description);
			$line_echange->desc =	$texte;
			
			$line_echange->titre 			= $obj->titre 	;				
			
			$texte=str_replace(array(chr(13).chr(10),chr(10)),'<br />',$obj->action);
			$line_echange->action=	$texte;	
			$line_echange->id_interlocuteur	= $obj->idInter 	;

			$line_echange->interlocuteur	= $obj->nomInter 	;
			$line_echange->Interphone		= $obj->InterTel 	;
			$line_echange->InterSupTel 		= $obj->InterSupTel;
			$line_echange->InterSupMail 	= $obj->InterSupMail;
			$line_echange->Interemail 		= $obj->Interemail;	
			$line_echange->NomMod 			= $obj->NomMod 	;
			$line_echange->PrenomMod 		= $obj->PrenomMod 	;	
			$line_echange->NomCreateur 		= $obj->NomCreateurEch 	;
			$line_echange->PrenomCreateur 	= $obj->PrenomCreateurEch 	;
			$line_echange->fk_user_create 	= $obj->fk_CreateurEch 	;
			$line_echange->fk_user_mod 		= $obj->fk_Moduser 	;
			$line_echange->telmail = '';
			if (!empty($obj->idInter) and $obj->idInter > 0) 
				$line_echange->telmail = $waff->ChercheTelMailTiersContact($obj->InterTel, $obj->InterSupTel, $obj->Interemail,  $obj->InterSupMail, $obj->idInter, $obj->Icountry_code );
			else $line_echange->telmail = "";
			$line_echange->fk_user_realise		= $obj->user_realise 	;
			$line_echange->user_realise 		= (!empty($obj->URlastname))?$obj->URlastname:$obj->URfirstname 	;
			$line_echange->date_realise 		= $obj->date_realise 	;			
		}	
		
		if (empty($line_echange->id)) $flgEchangeVide = True;
		else $flgEchangeVide =   false;

		print "<tr ".$bc[$var].">";

		// reprendre la saisie qui n'a pas été enregistrée avec ligneechange_recup
			if ( ($btaction == 'edit'or ($btaction =='Enregistrer' and $error > 0) )
				and !empty($line_echange->id) 	and $arg_idEchange == $line_echange->id )   {
				if ($error > 0)  {  // erreur sur l'enregistrement on reprend les info saisies
						$line->recupInfoDos($TypeListe);
						$line_echange->recupInfoEch();
					}
					$waff->AfficheLignedossier($TypeListe,$line,$line_echange ,$var, 'Saisie', $listtotal);	
			}
			elseif (!$flgEchangeVide )
				$waff->AfficheLignedossier($TypeListe,$line,$line_echange ,$var, '', $listtotal);

		print "</tr>\n";
		$var=!$var;
		$i++;
		
		unset($ligne);
		unset($line_echange);
		$line = new cgl_dossier($db);	
		$line_echange = new cgl_echange($db);
	}	
	unset ($line);	
	unset($line_echange);
	print "</tr></table>\n";
}


 print '</div> <!-- fin de div border = 1 -->';	
$waff->boutons(2, $listtotal, $params) ;
 print '</form>';

// End of page
llxFooter();
// print '</div>'; // fin de la page entière
$db->close();
	ini_set('magic_quotes_gpc', 0);
 
?>