<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * Version CAV - 2.7 - été 2022 - Migration Dolibarr V15
 * Version CAV - 2.8 - hiver 2023 - Pagination 
 * 								  - fiabilisation des foreach
 *								  - refonte de l'écran matériel loue
 *								  - diminution du pavé PaveSuivi de BU/LO
 *								  - affichage différencié pour BU/LO abandonné
 *								  - suppression de l'appel à réservation
 *					 			  - Installation popup Modif/creation Suivi pour Inscription/Location
 *									- reassociation BU/LO à un autre contrat
 * Version CAV - 2.8.2 - printemps 2023
 *		- taille et libellé du lien vers le dossier, à partir d'un BU/LO (html_Affiche_dossier)
 *		- Ajout ... après les trois premiers contacts affichés (ChercheTelMailTiersContact)
 * Version CAV - 2.8.3 - printemps 2023
 *		- ajout suppression echange dans pavesuivi
 *		- reconfiguration ligne échange dans 4saisons (bug 255)
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
 *  \file       dev/skeletons/skeleton_class.class.php
 *  \ingroup    mymodule othermodule1 othermodule2
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 *				Put here some comments
 */
 
 
require_once(DOL_DOCUMENT_ROOT."/core/class/html.form.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions.lib.php");
require_once(DOL_DOCUMENT_ROOT."/custom/cglavt/class/cglFctDolibarrRevues.class.php");
require_once(DOL_DOCUMENT_ROOT."/custom/cglavt/class/html.cglFctCommune.class.php");
require_once(DOL_DOCUMENT_ROOT."/custom/CahierSuivi/class/suivi_client.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/functions.lib.php");


class FormCglSuivi extends Form
{	
	function __construct($db)
	{
		$this->db = $db;
		return 1;
	}
	/*
	* Affiche les information tiers et dossier en entête d'écran
	*/
	function AfficheInfoCummune($TypeListe, $id, $line, $line_echange)
	{
		global $langs, $arg_idtiers, $user;

		
		require_once (DOL_DOCUMENT_ROOT."/custom/cglinscription/class/bulletin.class.php");
		$wbull = new Bulletin($this->db);
		print '<table id=infocommune class="border" width="100%"><tbody>';	
		print '<input type="hidden"  name="arg_id_dossier" value="'.$line->id.'">';
		print '<input type="hidden"  name="arg_idtiersCart" id="arg_id_tiers" value="'.$line->fk_tiers.'">';
		print '<input type="hidden"  name="search_dossier" value="'.$line->id.'">';	
		if ($TypeListe == 'dossier') {
			print '<tr><td width="40%"><table id=infodossier><tbody>';
			// Ref
			print '<tr><td width="10%" style="font-size:medium"><i>'.$langs->trans("Dossier")."</i></td>";
			print '<td width="20%  style="font-size:medium">';
			print '<input type="text" class="flat" size="40" name="arg_dossier" style="font-size:medium; font-weight:bold" value="'.(empty($line->dossier) ? '' : $line->dossier).'">';
			print '</td>';
			print '</tr>';

			// typedossier
			print "<tr><td><i>".$langs->trans("TypeDossier").'</i></td>';
			print '<td>';
			print $this->select_typedossier($line->fk_typedossier,'arg_typedossier', '',1,1,'',0, '', 0, 0);
			print '</td>';
			print '</tr>';
		
			// secteur
			print '<tr><td BGCOLOR="'.$line->coulsecteur.'"><i>'.$langs->trans("Secteur")."</i></td>";
			print '<td>';
			print $this->select_secteur($line->fk_secteur,'arg_secteur','',1,1,1,0, '', 0, 0);
			print '</td>';
			print '</tr>';
			
			// nb
			//print "<tr><td><i>".$langs->trans("Nb")."</i></td>";
			//print '<td>';
			//print '<input type="text" class="flat" size="40" name="arg_nb" value="'.(empty($line->nb) ? '' : $line->nb).'">';
			//print '</td>';
			//print '</tr>';
			
			// priorite
			print '<tr><td id= "TiPriorite" BGCOLOR="'.$line->coulpriorite.'"><i>'.$langs->trans("Priorite")."</i></td>";
			print '<td>';

			//print $this->select_priorite($line->fk_priorite,'arg_priorite',$id,'',1,1,'','', '', 0, 0, 'saisie');
			print $this->select_priorite($line->fk_priorite,'arg_priorite',$line->id,'TiPriorite','',1,1,'','', '', 0, 0, 'saisie','style="width:100%"',1);
			print '</td>';
			print '</tr>';
			
			// référent
			print '<tr><td><i>'.$langs->trans("Referent")."</i></td>";
			print '<td>';
			//if (!empty($line->fk_user_mod)) $line->fk_user_mod = $line->fk_user_create;
			//print $this->select_user($line->fk_user_mod,'arg_referent','',1,1,'',0, '', 0, 0);
			if (empty($line->fk_user_create)) $line->fk_user_create=$user->id;
			print $this->select_user($line->fk_user_create,'arg_referent','',1,1,'',0, '', 0, 0);
			print '</td>';
			print '</tr>';
			
			// date creation dossier
			print "<tr><td><i>".$langs->trans("DateCrea").'</i></td>';
			print '<td width="20%  style="font-size:medium; font-weight:normal">';
			print $line->datec;
			print '</td>';
			print '</tr>';
			
			//BU/LO/RE
			print '<tr><td><i>'.$langs->trans("Activites")."</i></td>";
			print '<td>';
			$wdos = new cgl_dossier($this->db);
			$tabret = $wdos->fetch_activite_by_doss ($line->id);
			if (!empty($tabret)){
				foreach( $tabret as $tabelem) {	
					$attribdeb = ' style="color:black;font-size: 15px;"';
					if ($tabelem->statut >= $wbull->BULL_ABANDON ) 
						$attribdeb = ' style="color:#C0C0C0;font-size: 12px;"';			
					if (substr($tabelem->ref, 0,2) == 'BU')  
						print '<a href="../../cglinscription/inscription.php?id_bull='.$tabelem->rowid.'&idmenu=16899&mainmenu=CglInscription&token='.newtoken().'" '.$attribdeb.' >'.$tabelem->ref.'</a>';
					elseif (substr($tabelem->ref, 0,2) == 'LO')  
					print '<a href="../../cglinscription/location.php?id_contrat='.$tabelem->rowid.'&idmenu=16925&mainmenu=CglLocation&token='.newtoken().'" '.$attribdeb.' >'.$tabelem->ref.'</a>';
					elseif (substr($tabelem->ref, 0,2) == 'RE')  
					print '<a href="../../cglinscription/reservation.php?id_resa='.$tabelem->rowid.'&idmenu=16935&mainmenu=CglResa" '.$attribdeb.' >'.$tabelem->ref.'</a>';
					print '&nbsp&nbsp';
				}
				unset($wbull);
			}
	/*
	
	 style="font-size:medium; font-weight:bold" 
	 
			$attribdeb = ' style="color:black;font-size: 15px;"';
		if ($tabelem->statut >= $bull->BULL_ABANDON ) {
					$attribdeb = ' style="color:#C0C0C0;font-size: 12px;"';			}
				$out .= '<a href="../../cglinscription/inscription.php?id_bull='.$tabelem->rowid.'&idmenu=16899&mainmenu=CglInscription&token='.newtoken().'" '.$attribdeb.' >'.$tabelem->ref.'</a>';

*/	
			print '</td>';
			print '</tr>';

			print '</tbody></table id=infodossier></td>';
		}
		else print '<tr>';
		print '<td width="40%"><table id=infotiers><tbody>';
		// Tiers
	//       if ($TypeListe == 'dossier' and !empty($line->fk_tiers ) and $line->fk_tiers > 0 ) {
		if ($TypeListe == 'dossier' ) {
			print "<tr><td width=10%><i>".$langs->trans("Tiers")."</i></td>";		
			print '<td width=20%>';	
			$event = 'onchange=ReqTiersTelMail(this)';
			print $this->select_client_courte($line->fk_tiers,'arg_idtiersCart',$line->nvtiers,'arg_nvtiersCart','L', 'tiers', '',  0,  '',   0,    $event, '', 0, 0, ''); 

			print '</td>';
			print '</tr>';
		}
		elseif ($TypeListe == 'tiers') {
			print '<tr><td width=10% style="font-size:medium"><i>'.$langs->trans("Tiers")."</i></td>";		
			print '<td width=90%>';				
			//print '<input type="text" class="flat"  name="arg_nomtiersCart" ; style="font-size:medium;font-weight:bold;width:100%" value="'.$line->nomTiers.'">';
			print '<b>'.$line->nomTiers.'</b>';
			print '</td>';
			print '</tr>';
		}	
		// telephone
		print "<tr>";
		print "<td width=10%><i>".$langs->trans("Telephone")."</i></td>";
		print '<td  width=20%>';
		//print '<input type="text" class="flat" size="40" name="arg_telCart" value="'.$line->TiersTel.'"  id="arg_teltiers">';
		print '&nbsp&nbsp&nbsp<span id="spanteltiers">'.dol_print_phone($line->TiersTel,  $line->country_code, $line->fk_tiers, '', 'AC_TEL').'</span>';
		print '</td></tr>';
		print "<tr><td  width=10%><i>".$langs->trans("Telephone Supplementaire")."</i></td>";
		print '<td  width=20%>';
		//print '<input type="text" class="flat" size="40" name="arg_telSupCart" id="arg_telsuptiers" value="'.$line->TiersSupTel.'">';
		print '&nbsp&nbsp&nbsp<span id="spantelsuptiers">'.dol_print_phone($line->TiersSupTel,  $line->country_code, $line->fk_tiers, '', 'AC_TEL').'</span>';
		print '</td>';
		print '<td>&nbsp</td>';
		print '</tr>';

		$wfctDol = new CglFonctionDolibarr ($this->db);		
		// mail
		print "<tr>";
		print "<td><i>".$langs->trans("Mail")."</i></td>";
		print '<td>';
		//print '<input type="text" class="flat" size="40" name="arg_mailCart" id="arg_mailtiers"  value="'.(empty($line->TiersMail) ? '' : $line->TiersMail).'">';
		print $line->TiersMail;
		print '  '.$wfctDol->dol_print_email_image($line->TiersMail,0,$Reftiers,'',1,64,1,1);
		
		print '</td></tr>';
		print "<tr><td><i>".$langs->trans("Mail Supplementaire	")."</i></td>";
		print '<td>';
		print $line->TiersSupMail;
		//print '<input type="text" class="flat" size="40" name="arg_mailSupCart" id="arg_mailsuptiers"  value="'.(empty($line->TiersSupMail) ? '' : $line->TiersSupMail).'">';
		print '  '.$wfctDol->dol_print_email_image($line->TiersSupMail,0,$Reftiers,'',1,64,1,1);
		print '</td>';
		print '</tr>';
		print '</tbody></table id=infotiers>';
		unset ($wfctDol);

		print '</tbody></table id=infocommune ></td></tr>';

		
	   // $linkback = '<a href="'.DOL_URL_ROOT.'/compta/bank/search.php">'.$langs->trans("BackToList").'</a>';

		

	} //AfficheInfoCummune 
	function PrepScript($TypeListe) 
	{

		$out = '';
		$out.= '<script > '."\n";	
		if ($TypeListe <> 'materiel') {
			$out.= 'function creerobjet(fichier)  '; 
			$out.= '{  '; 
				$out.= '	if(window.XMLHttpRequest) ';  
				$out.= '		xhr_object = new XMLHttpRequest();  '; 
				$out.= '	else if(window.ActiveXObject)';  // IE  
				$out.= '		xhr_object = new ActiveXObject("Microsoft.XMLHTTP"); '; 
				$out.= '	else '; 
				$out.= '			return(false); ';
				$out.= '	xhr_object.open("GET", fichier, false);'; 
				$out.= '	xhr_object.send(null); ';
				$out.= '	if(xhr_object.readyState == 4)'; 
				$out.= '		return(xhr_object.responseText); '; 
				$out.= '	else'; 
				$out.=  '		return(false); '; 
			$out.= '	}'; 
			$out.=  "\n";		
		}
		if ($TypeListe == 'generale') {
			// mise en page d'un texte Alt	
				$out.=  'function AffAlt(o) '; 
				$out.=  '{ '; 	
					$out.= '  val = o;';  
					// $out .= ' 	document.getElementById("spantierstel").style.visibility = "hidden";';
					//$out.= '  val = document.images["img_info"].alt;';  			;
					//$out.= '  val = document.getElementById("img_info").alt;';  	
					$out.=  '}';
				$out.=  "\n";				
		}	
		// Enregistrer l'action comme réalisée	
		$out.=  'function EnrReal(o, id_echg, id_dos) '; 
		$out.=  '{ '; 	
			$out.=  '  url="'.DOL_URL_ROOT.'/custom/CahierSuivi/suivi_client/ReqEnrRealise.php?ID=".concat(id_echg);';
			$out.=  '  url=url.concat("&dos=").concat(id_dos);';
			$out.=  '  url=url.concat("&origine=").concat("'.$TypeListe.'");';
			$out.=  "		var	Retour = creerobjet(url); ";
			$out.=  "   var tableau = Retour.split('|');"; 
			if ($TypeListe == 'generale') {		
				$out.=  'document.getElementById("div_titre".concat(id_dos)).innerHTML=tableau[2];';
				$out.=  'document.getElementById("div_action".concat(id_dos)).innerHTML=tableau[0];';
			}
			if ($TypeListe == 'materiel') {	
				// Relancer la recherche des action à réaliser pour affichage dans 
				$out.=  'document.getElementById("IdListeAction".concat(id_dos)).innerHTML=Retour;';

			}
			/*if ($TypeListe == '4saisons') {	
				// Relancer la recherche des action à réaliser pour affichage dans 
				$out.=  'document.getElementById("div_titre".concat(id_dos)).innerHTML=Retour;';
			}
			*/
			else {				
				$out.=  'document.getElementById("div_real".concat(id_echg)).innerHTML=tableau[1];';
			}
			$out.=  '}';
			$out.=  "\n";			
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers' or $TypeListe == '4saisons') {
			// Remettre l'action comme non-réalisée
			$out.=  'function EnrErrReal(o, id_echg, id_dos) '; 
			$out.=  '{ '; 	
				$out.=  '  url="'.DOL_URL_ROOT.'/custom/CahierSuivi/suivi_client/ReqEnrNonRealise.php?ID=".concat(id_echg);';
				$out.=  '  url=url.concat("&dos=").concat(id_dos);';
				$out.=  "	var	Retour = creerobjet(url); ";
				$out.=  'document.getElementById("div_real".concat(id_echg)).innerHTML= Retour ;';
			$out.=  '}';	
		}


		$out .= '</script>';
		return ($out);	
	} //PrepScript
	function AfficheBandeauEntete($TypeListe, $listtotal)
	{	
		global $search_Createur, $search_Mod, $search_tiers, $search_typedossier, $search_secteur, $search_dossier;
		global $search_priorite, $search_interlocuteur, $sortfield, $sortorder, $url, $urlarg, $params, $search_total, $Reftiers;
		global $langs, $conf;


		
		$url=$_SERVER["PHP_SELF"];
		$filtreCompany= '';
		// if (!empty($search_tiers) and  $search_tiers > 0) $filtreCompany .= ' s.rowid = '.$search_tiers. ' AND '; non utilisé afin de pouvoir passer d'un teirs à un autre sans vider les fitlres
		$filtreCompany.= ' (exists (select (1) from ' . MAIN_DB_PREFIX . 'cglavt_dossier  as d ';
		if (empty($listtotal))
			$filtreCompany.= 	' LEFT JOIN ' . MAIN_DB_PREFIX . 'cglavt_c_priorite as spri on fk_priorite = spri.rowid' ;
		$filtreCompany.= 	' WHERE d.fk_soc = s.rowid';
		if (empty($listtotal))
			$filtreCompany.= 	' AND spri.label not like "%CLOTURE%" ) ';
		else $filtreCompany.= 	' ) ';
		$filtreCompany.= ' or (exists (select (1) from ' . MAIN_DB_PREFIX . 'cglavt_dossierdet as dd ';
		if (empty($listtotal)) {
			$filtreCompany.= 	' LEFT JOIN ' . MAIN_DB_PREFIX . 'cglavt_dossier  as d on fk_dossier = d.rowid ';
			$filtreCompany.= 	' LEFT JOIN ' . MAIN_DB_PREFIX . 'cglavt_c_priorite as spri on fk_priorite = spri.rowid ';
		}
		$filtreCompany.= 	' WHERE dd.fk_soc = s.rowid';
		//if (empty($listtotal))
		//	$filtreCompany.= 	' AND spri.label not like "%CLOTURE%" )) ';
		//else 
		$filtreCompany.= 	' ))) ';
		
		if ($TypeListe <> 'generale') {		
			$filterDossier =  ' exists (select (1) from ' . MAIN_DB_PREFIX . 'societe  as ste ';
			if (empty($listtotal))
				$filterDossier.= ','.	 MAIN_DB_PREFIX . 'cglavt_c_priorite as spri' ;
			$filterDossier.= 	' WHERE s.fk_soc = ste.rowid';
			if (empty($listtotal))
				$filterDossier.= 	' AND spri.label not like "%CLOTURE%" and (isnull(s.fk_priorite) or s.fk_priorite = spri.rowid ) ';
			if ($TypeListe == 'tiers')
				$filterDossier.= 	' AND ste.rowid = "'.$Reftiers.'"';
			$filterDossier.= 	' ) ';
			$filterDossier.= ' or exists (select (1) from ' . MAIN_DB_PREFIX . 'societe  as ste ';
				$filterDossier.= 	' LEFT JOIN ' . MAIN_DB_PREFIX . 'cglavt_dossierdet as dd on ste.rowid = dd.fk_soc ';
			if (empty($listtotal)) {
				$filterDossier.= ','.	MAIN_DB_PREFIX . 'cglavt_c_priorite  as spri ';
			}
			$filterDossier.= 	' WHERE dd.fk_dossier = s.rowid';
			if (empty($listtotal)) {
				$filterDossier.= 	' AND spri.label not like "%CLOTURE%" and (isnull(s.fk_priorite) or s.fk_priorite = spri.rowid ) ';
			}
			if ($TypeListe == 'tiers')
				$filterDossier.= 	' AND ste.rowid = "'.$Reftiers.'"';
			$filterDossier.= 	' ) ';
		}
		else	 {
			if (empty($listtotal)) $filterDossier =  'exists (select (1) from ' . MAIN_DB_PREFIX . 'cglavt_c_priorite as spri WHERE  spri.label not like "%CLOTURE%" and (isnull(s.fk_priorite) or s.fk_priorite = spri.rowid )) ' ;
			else 	$filterDossier = '';
		}

		$filtreSecteur= ' exists (select (1) from ' . MAIN_DB_PREFIX . 'cglavt_dossier as d ';
		if (empty($listtotal)) 
			$filtreSecteur.= 	' LEFT JOIN ' . MAIN_DB_PREFIX . 'cglavt_c_priorite as spri on fk_priorite = spri.rowid';
		$filtreSecteur.= 	'  WHERE fk_secteur = ssect.rowid ';
		if (empty($listtotal)) 
			$filtreSecteur.= ' and  spri.label not like "%CLOTURE%" )';
		else $filtreSecteur.= ')';
		
		$filtreContact= ' exists (select (1) from ' . MAIN_DB_PREFIX . 'cglavt_tiers_suivi WHERE fk_socpeople = sp.rowid)';
		$filtreSecteur= ' exists (select (1) from ' . MAIN_DB_PREFIX . 'cglavt_dossier WHERE fk_secteur = ssect.rowid)';

		$form=new Form($this->db);
		// affiche la barre grise de titres des filtres
		print '<tr class="liste_titre">';
		$suivi_url=$urlarg.$params;
		if ($TypeListe == 'dossier') print_liste_field_titre("SC_MAJ", 			$url, "e.datec", "", $suivi_url, 'align="center"', $sortfield, $sortorder);
		else 						print_liste_field_titre("SC_Creation", 		$url, "d.datec", "", $suivi_url, 'align="center"', $sortfield, $sortorder);
		/*
		if ($TypeListe == 'dossier') print_liste_field_titre($langs->trans("SC_Mod", 			$url, 'um.rowid', "", $suivi_url, 'align="center"', $sortfield, $sortorder);
		else						print_liste_field_titre($langs->trans("SC_Createur", 		$url, 'uc.rowid', "", $suivi_url, 'align="center"', $sortfield, $sortorder);
		*/
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers') print_liste_field_titre("SC_Mod", 			$url, 'um.rowid', "", $suivi_url, 'align="center"', $sortfield, $sortorder);
		//if ($TypeListe <> 'dossier' ) print_liste_field_titre("SC_Type", 		$url, 'styd.label', "", $suivi_url,  'align="center"',$sortfield, $sortorder);
		if ($TypeListe <> 'dossier' ) print_liste_field_titre("SC_Dossier", 		$url, 'd.libelle', "", $suivi_url, 'align="center"', $sortfield, $sortorder);
		if ($TypeListe == 'generale') print_liste_field_titre("SC_Tiers", 		$url, 'st.nom',"", $suivi_url, 'align="center"', $sortfield, $sortorder);
		if ($TypeListe <> 'dossier' ) print_liste_field_titre("SC_Secteur", 		$url, 'fk_secteur', "", $suivi_url, 'align="center"', $sortfield, $sortorder);
	//		if ($TypeListe <> 'dossier' ) print_liste_field_titre("SC_NB", 			$url,  '', "",'', '', '', '');
		if ($TypeListe == 'generale')print_liste_field_titre("SC_Action", 		$url,  '', "",'', 'align="center"', '', '');
		print_liste_field_titre("SC_Description", 								$url,  '', "",'', 'align="center"', '', '');
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers'  ) print_liste_field_titre("SC_Interlocuteur", $url, 'e.fk_soc', "", $suivi_url, 'align="center"', $sortfield, $sortorder);
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers' ) 
									print_liste_field_titre("SC_Action", 			$url, "e.date_realise, action", "", $suivi_url, 'align="center"',$sortfield, $sortorder);
	//									print_liste_field_titre("SC_Action", 			$url,  '', "",'', 'align="center"', '', '');	
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers' ) print_liste_field_titre("SC_Realisation", 		$url,  '', "",'', 'align="center"', '', '');
		if ($TypeListe <> 'dossier') print_liste_field_titre("SC_Priorite", 		$url,  'fk_priorite', "",   $suivi_url, 'align="center"',$sortfield, $sortorder);		
		//if ($TypeListe <> 'dossier') print_liste_field_titre("SC_Origine",  	$url,  '', "",'', '', '', '');			
		if ($TypeListe <> 'tiers')print_liste_field_titre("SC_Tel", 				$url, '', "", '', 'align="center"', '','');	
		if ($TypeListe <> 'tiers')print_liste_field_titre("SC_Mail", 				$url, '', "",'', 'align="center"', '', '');	
		print_liste_field_titre('', '', '', "", '', '', $sortfield, $sortorder);

		print "</td></tr>\n";
		
		// affiche la barre grise des filtres
		print '<tr class="liste_titre">';
		print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
		print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
		if ($listtotal == 'total') print '<input type="hidden" name="listtotal" value="total">';
		
		// DATE
		print '<td class="liste_titre" >';
		if ($TypeListe == 'generale') {
			print 'Referent:';
			if (empty($search_Createur)) $search_Createur=$user->id;
			print $this->select_user($search_Createur,'search_Createur',1, 1);	
		}		
		else print 'Recherche';	
		print '</td>';

		// COLLABORATEUR
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers')  {
			print '	<td class="liste_titre"  >';	
			if (empty($search_Createur)) $search_Createur=$user->id;
				print $this->select_user($search_Createur,'search_Createur',1, 1);
			print '</td>';	
		}
		
		//TYPE DOSSIER
		/*
		if ($TypeListe <> 'dossier')  {
			print '<td class="liste_titre" align="center">';
			print $this->select_typedossier($search_typedossier,'search_typedossier','',1,1,'',0, '', 0, 0);
			print '</td>';
		}
		*/
		//DOSSIER
		if ($TypeListe <> 'dossier')  {
			print '<td class="liste_titre" align="center" >';
			print '<input class="flat"  value="'.$search_dossier.'" type="text" id="search_dossier" name="search_dossier" >';
			print '</td>';
		}
		// TIERS PRINCIPAL
		if ($TypeListe == 'generale')  {
			print '<td class="liste_titre" align="center" >';
			//print $this->select_client_courte($search_tiers,'search_tiers','','','L','', $filtreCompany,1,'',0,0, '', 0, 0, '');
			print $this->select_client($search_tiers,'',  'search_tiers','','', '','L','', $filtreCompany,1,1,1,0, '', 0, 0, '');
			print '</td>';
		}
									
		/*print '</td><td class="liste_titre">';
		print $w->select_contacts(0,$seach_contact,'search_contact',$filtreContact, 1,1,'','',0, '', 0, 1, '', false); 
		print '</td>';*/
		// SECTEUR
		if ($TypeListe <> 'dossier' )  {
			print '<td class="liste_titre"  align="center">';		
			print $this->select_secteur( $search_secteur,'search_secteur',$filtreSecteur,1,1,1,0, '', 0, 1, '', false);
			print '</td>';
		}
		// NB
	//		if ($TypeListe <> 'dossier' )  print '<td class="liste_titre"></td>'; // Cellule vide
		
		// ACTION ou DESCRIPTION
		print '</td><td class="liste_titre" ></td>'; // Cellule vide
		
		
		// TIERS INTERLOCUTEUR
		if ($TypeListe == 'dossier')  {
			print '<td class="liste_titre" align="center">';
			print $this->select_client_courte($search_interlocuteur,'search_interlocuteur','','','L','', $filtreCompany,1,'',0,0, '', 0, 0,'');
			
			//print $this->select_client($line->id_saisietiers, $line->NomTiers, 'arg_idtiersLig',$line->nvtiers,'arg_nvtiersLig','R','','',1,'',0, '', 0,  '', 0, '');
			print '</td>';
		}
		// DESCRIPTION ou ACTION
		print '<td class="liste_titre"></td>'; // cellule vide
		
		// REALISATION
		if ($TypeListe == 'tiers')
			print '<td class="liste_titre"></td>'; // cellule vide
		// PRIORITE	
		if ($TypeListe <> 'dossier' )  {
			print '<td class="liste_titre"  align="center" >';	
			//if ($search_priorite == 1) $temppriorite = 0;
			//else $temppriorite = 1;
			print $this->select_priorite($search_priorite,'search_priorite',-1,'','',1,1,'',0, '', 0, 1, 'selection','style="width:100%"',1);
			print '</td>';
		}
		
		// TELEPHONE et MAIL
		if ($TypeListe == 'generale')  {
			print '<td class="liste_titre"  align="center">';	
			print '<input class="flat searchstring maxwidth50" type="text" name="search_phone" value="'.dol_escape_htmltag($search_phone).'">';
			print '</td>'; 
		}
		elseif ($TypeListe <> 'tiers' ) {
			print '<td class="liste_titre"  align="center">';	
			print '</td>'; 
		}
		
		// TELEPHONE et MAIL
		if ($TypeListe == 'dossier')  {
			print '<td class="liste_titre"  align="center">';	
	//			print '<input class="flat searchstring maxwidth50" type="text" name="search_phone" value="'.dol_escape_htmltag($search_phone).'">';
			print '</td>'; 
		}

		//if ($TypeListe == 'generale' ) print '<td class="liste_titre" ></td>'; // cellule vide  
	 
		// boutons de validation et suppression du filtre
		if ($TypeListe == 'generale' ) 		print '<td class="liste_titre" align="right" ></td>';
		if ($TypeListe == 'dossier' ) 		print '<td class="liste_titre" align="right"  ></td><td>';
		else 	print '<td class="liste_titre" align="right" >';
		print '<input type="image" class="liste_titre" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png" value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
		//if ($TypeListe == 'dossier' ) print '</td><td class="liste_titre" >'; else
		print '&nbsp; ';
		print '<input type="image" class="liste_titre" name="button_removefilter" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/searchclear.png" value="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'" title="'.dol_escape_htmltag($langs->trans("RemoveFilter")).'">';
		print '</td>';

		print "</tr>\n";
	} //AfficheBandeauEntete
	/*
	* $param	action 	Saisie: affiche les zones en saisie, vide : affiche les zones, ajout comme saisie, mais empêche l'évènement onchange
	*/
	/*
	* Permet la  saisie de la ligne  d'un échange ou son affichage
	*
	* $param string	TypeListe 		generale, dossier, tiers ou 4saisons
	* $param obj	line 					info du dossier à afficher
	* $param obj	line_echange		info d'un échange à afficher
	* $param obj	var					pour indiquer la couleur de la ligne en cours (pour la deuxième ligne comportant description
	* $param string	action 			Saisie: affiche les zones en saisie, vide : affiche les zones
	* $param int	Idechange 		Identifiant de l'échange
	* $param string listtotal 	1  - Affiche tous les dossiers, même clos - '' affiche les dossiers non clos
	*
	*	return string $out	Code d'affichage de la ligne
	*/
	function AfficheLignedossier($TypeListe, $line, $line_echange, $var, $action, $listtotal) 
	{
		print $this->getLignedossier($TypeListe, $line, $line_echange, $var, $action, $listtotal) ;
	}//AfficheLignedossier
	/*
	* Crée ordrers html pour  la  saisie de la ligne  d'un échange ou son affichage
	*
	* $param string	TypeListe 		generale, dossier, tiers ou 4saisons
	* $param obj	line 					info du dossier à afficher
	* $param obj	line_echange		info d'un échange à afficher
	* $param obj	var					pour indiquer la couleur de la ligne en cours (pour la deuxième ligne comportant description
	* $param string	action 			Saisie: affiche les zones en saisie, vide : affiche les zones
	* $param int	Idechange 		Identifiant de l'échange
	* $param string listtotal 		1  - Affiche tous les dossiers, même clos - '' affiche les dossiers non clos
	* $param int	idbull 			identifant du bulletin/contrat
	*
	*	return string $out	Code d'affichage de la ligne
	*/
	function getLignedossier($TypeListe, $line, $line_echange, $var, $action, $listtotal, $idbull='') 
	{
		global $langs, $bc, $user;
		global $Reftiers, $Refdossier, $search_typedossier;
		if ($action == 'Ajout') {
				$action = 'saisie';
				$flEventOnchange = false;
		}
		else $flEventOnchange = true;

		$out = "";
		$form1 = new Form($this->db);
		require_once(DOL_DOCUMENT_ROOT."/custom/cglinscription/class/bulletin.class.php");	
		$bull = new Bulletin($this);
		// répartition dans l'écran 
		if ($TypeListe == 'generale' or  $TypeListe == 'tiers') {
			$size_date = 'size="4,4%"';
			$size_user = 'size="5,3%"';
			//$size_type = 'size="3,3%"';
			$size_dossier = 'size="10%"';
			$size_tiers = 'size="9,9%"';
			$size_interlocuteur = '';
			$size_secteur = 'size="5,5%"';
			$size_nb = 'size="2,2%"';
			
			$size_action = 'size="8,6%"';
			$size_echange = 'size="43,8%"';
			
			//$size_action = 'size="10,6%"';
			//$size_echange = 'size="41,8%"';
			$size_statut = 'size="1,5%"';
			//$size_origine = 'size="4,4%"';
			$size_telmail = 'size="13,2%"';
			$size_image = 'size="2,2%"';
		}
		else {
			$size_date = 'size="4,8%"';
			$size_user = 'size="5,5%"';
			//$size_type = '';
			$size_dossier = '';
			$size_tiers = '';
			$size_echange = 'size="56%"';
			$size_secteur = '';
			$size_nb = '';
			$size_interlocuteur = 'size="10,5%"';
			$size_action = 'size="10,5%"';
			$size_statut = '';
			$size_origine = '';
			$size_telmail = 'size="10,5%"';
			$size_image = 'size="2,2%"';
		}	
		
		// recuperer l'identifiant d'echange
		if (($TypeListe <> 'generale')  and !empty($action) )
			$out .= '<input type="hidden" id=id_echange name="arg_idEchange" value="'.$line_echange->id.'">';		
		if (($TypeListe == 'tiers') and !empty($action) ) 	
			$out .= '<input type="hidden" id=Reftiers name="Reftiers" value="'.$Reftiers	.'">';	
		if (!empty($action) ) {	$attrib= ' rowspan=2 bgcolor="#FAF0E6" '; $attribtitre = ' bgcolor="#FAF0E6" ';$attribzonecol = ' rowspan=2 '; }
		if (($TypeListe == '4saisons') and empty($action) == true) 	$attrib = '';
		elseif (($TypeListe <> 'generale') and empty($action)== true) 	$attrib = ' rowspan=2 ';
		// DATE
		//$out .=  dol_$out .=_date($line->datec,$langs->trans("FormatDateShortInput"));
		/* pour affichage colonne date dans les 3 écrans 	
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers')  $datetemp =$line_echange->date;
		else $datetemp = $line->dateAff;
		$out .= '<td '.$attrib.' '.$size_date.' ><div id="div_datec">';		
		
			if (empty($datetemp) and !empty($action)) $out .= $langs->trans("CS_TitreSaisie");
			else $out .=  dol_$out .=_date($datetemp,"%d/%m/%Y %H:%M");
		
		$out .= '</div></td>';			
		*/
		// DATE -  pour affichage date dans les seuls écrans dossier et tiers
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers')  {
			$out .= '<td '.$attrib.' '.$size_date.' ><div id="div_datec">';			
			if (empty($datetemp) and !empty($action)) $out .= $langs->trans("CS_TitreSaisie");
			else {	
				//if ($line_echange->tms == "0000-00-00")
					$out .=  $line_echange->datec;
				//else 
					//$out .=  $line_echange->tms;
			}
			$out .= '</div></td>';			
		}
		
		// COLLABORATEUR  - pour affichage date dans les seuls écrans dossier et tiers
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers') {
			$out .= '<td '.$attrib.'  align=left '.$size_user.' >';
			$out .= '<div id="div_user">';
			if ($line_echange->datec < $line_echange->tms) {
				if (empty($line_echange->PrenomMod))$nomtemp = $line_echange->NomMod ;
				else  $nomtemp = $line_echange->PrenomMod ;
			}
			else {
				if (empty($line_echange->PrenomCreateur))$nomtemp = $line_echange->NomCreateur ;  
				else  $nomtemp = $line_echange->PrenomCreateur ;
			}				
			if (empty($action) or $line_echange->fk_user_create > 0) 	
				$out .= $nomtemp ;
			else { 
				if (empty($line->fk_user_create)) $out .= $this->select_user($user->id,'arg_createur','',1,1,'',0, '', 0, 0);
				elseif (empty($line_echange->fk_user_create)) $out .= $this->select_user($user->id,'arg_createur','',1,1,'',0, '', 0, 0);
				else $out .= $this->select_user($line_echange->fk_user_create,'arg_createur','',1,1,'',0, '', 0, 0);
			}
			$out .= '</div></td>';
			
		}
		elseif ($TypeListe == 'generale'){
			$out .= '<td '.$attrib.'  align=left '.$size_user.' >';
			$out .= '<div id="div_user">';
			if (empty($action)  )  
				if (empty($line->PrenomCreateur)) $out .= $line->NomCreateur ;  else  $out .= $line->PrenomCreateur ;			
			else
			{
				if (empty($line->fk_user_create)) $line->fk_user_create=$user->id;
				$out .= $this->select_user($line->fk_user_create,'arg_createur','',1,1,'',0, '', 0, 0);
			}
					
			$out .= '</div></td>';
		}
		// type dossier
		/*
		if ($TypeListe <> 'dossier')  {	
			$out .= '<td '.$attrib.' align=left '.$size_type.' >';

			if (empty($action)) {	
				$out .= '<div id="div_typedossier">';
				$out .= $line->typedossier ; 
				$out .= '</div>';
			}	
			else	{ 
				// ATTENTION A PARAMTRER AVEC CONSTANTE 
				if (isset($line) and !empty($line) and empty($line->fk_typedossier))					$line->fk_typedossier = 2;
				 $out .= '<div id="div_typedossier" style="visibility:visible" ></div>'; $out .= $this->select_typedossier($line->fk_typedossier,'arg_typedossier','',1,1,'',0, '', 0, 0); $out .= '</div>';		
			}
			//$out .= '</div></td>';
			$out .= '</td>';
		}	
		*/

		// DOSSIER		
		if ($TypeListe == 'generale' or $TypeListe == 'tiers')  {	
				
			if (empty($listtotal)) 
				$filtreDossier = ' (exists (select 1 from '.	MAIN_DB_PREFIX . 'cglavt_c_priorite  as spri where (isnull(s.fk_priorite) or s.fk_priorite = spri.rowid ) and spri.label not like "%CLOTURE%" )) ';
			else 
				$filtreDossier = '';
			$out .= '<td '.$attrib.' align=left '.$size_dossier.' >';
			if ($line->id > 0) $out .= $this->getNomUrl("object_company.png", 'Dossier',0,$line->id, $line->fk_tiers, $search_typedossier)."&nbsp";
			if (empty($action)) {
				$out .= $line->dossier ;
				if ($TypeListe == 'tiers')  {
					if (!empty($line_echange->interlocuteur)) $out .= "&nbsp (".$line_echange->interlocuteur.')';
					$out .= "&nbsp"."&nbsp";
					if ( !empty($line->fk_tiers) and $line->fk_tiers <> $Reftiers ) $out .= $this->getNomUrl("object_company.png", 'Tiers',0,'', $line->fk_tiers, $search_typedossier)."&nbsp";
				}				
				// Afficher les BU/LO/RESA associé à ce dossier si on est sur cahier_suivi
					$wdos = new cgl_dossier($this->db);
					$tabret = $wdos->fetch_activite_by_doss ($line->id);
					if (!empty($tabret)){
						foreach( $tabret as $tabelem) {	
								$out .= '<br>';
									$attribdeb = ' style="color:black;font-size: 15px;"';
								if ($tabelem->statut >= $bull->BULL_ABANDON ) {
									$attribdeb = ' style="color:#C0C0C0;font-size: 12px;"';
								}
								$hover=' onmouseover="this.style.font-size=15px;this.style.color=#C0C0C0;" onmouseout="this.style.font-size=12px;this.style.color=black;"';
								if (substr($tabelem->ref, 0,2) == 'BU')   $out .= '<a href="../../cglinscription/inscription.php?id_bull='.$tabelem->rowid.'&idmenu=16899&mainmenu=CglInscription&token='.newtoken().'" '.$attribdeb.' >'.$tabelem->ref.'</a>';
								elseif (substr($tabelem->ref, 0,2) == 'LO')  $out .= '<a href="../../cglinscription/location.php?id_contrat='.$tabelem->rowid.'&idmenu=16925&mainmenu=CglLocation&token='.newtoken().'" '.$attribdeb.$hover.' >'.$tabelem->ref.'</a>';
								//elseif (substr($tabelem->ref, 0,2) == 'RE')  $out .= '<a href="../../cglinscription/reservation.php?id_resa='.$tabelem->rowid.'&idmenu=16935&mainmenu=CglResa&token='.newtoken().'" >'.$tabelem->ref.'</a>';
						}
					}
				
			}
			else {
				$out .= $this->select_dossier($line->id,'arg_id_dossier',$line->nvdossier,'arg_nvdossier','R', $filtreDossier,1,1,1,'', '', 0, 0);
				$out .= '<br><input class="flat" name="arg_dossanstiers" id="arg_dossanstiers"  type="checkbox" value="oui" size="1" ><span name="span_dossanstiers" id="span_dossanstiers" >Dossier sans tiers </span>';
			}
			$out .= '</td>';	
		}	

		if (!empty($arg_idtiers)) $this->id_saisietiers= $arg_idtiers;	
		
		// CLIENT	
		if ($TypeListe == 'generale')  {	
			$out .= '<td '.$attrib.' align=left '.$size_tiers.' >';
			$out .= '<div id="client">';
			// afficher le nom du client -
			// si idTiers non vide afficher le lien avec client Dolibarr	
			if (empty($action)) {
				if ($line->fk_tiers > 0 ) $out .= $this->getNomUrl("object_company.png", 'Tiers',0,'', $line->fk_tiers, $search_typedossier)."&nbsp";
					$out .= '<b>'.$line->NomTiers.'</b>';
			}
			
			else  {	
				if ($line->id_saisietiers > 0 ) $out .= $this->getNomUrl("object_company.png", 'Tiers',0,'', $line->id_saisietiers, $search_typedossier)."&nbsp";
				$events = array();
				$events[] = 'onclick="EffNouvTiers(this);"';
					$out .= $this->select_client($line->id_saisietiers, $line->NomTiers, 'arg_idtiersLig',$line->nvtiers,'arg_nvtiersLig','R','','',1,'',0, '', 0,  '', 0, '');
					$out .= '<br><span name="span_nomprenom" id="span_nomprenom"><i>NOM Prenom</i></span></br>';				
			}	
			$out .= '</div >';
			$out .= '</td>';
		}	
		
		//SECTEUR	
		if ($TypeListe == 'generale'  or $TypeListe == 'tiers')  {				
			if (empty($action)) {
				$out .= '<td BGCOLOR="'.$line->coulsecteur.'" '.$attrib.''.$attribzonecol.'" align=center '.$size_secteur.' >';
				$out .= '<div id="div_secteur">';	
				$out .= $line->secteur.'';
				$out .= '</div>';
			}
			else	{ 			
				$out .= '<td  '.$attrib.'" align=center '.$size_secteur.' >';
				$out .= '<div id="div_secteur" style="visibility:hidden">';
				$out .= $this->select_secteur( $line->fk_secteur,'arg_secteur','',1,1,1,0, '', 0, 1, '', false);	
				$out .= '</div>';
				$out .= '';
			}			
			$out .= '</td>';
		}	
		
		//NB	
		/*if ($TypeListe <> 'dossier')  {	
			$out .= '<td '.$attrib.' align=left '.$size_nb.' >';		
			if (empty($action)) {
					  if (!empty($line->nb) and $line->nb > 0)  	$out .= $line->nb; else $out .= '&nbsp';
			}
			else	{
				$out .= '<div id="div_nb">';
				$out .= '<input class="flat" size=2 value="'.$line->nb.'" type="text" id="arg_nb" name="arg_nb" >';
				$out .= '</div>';
			}
			$out .= '</td>';	
			}
		*/
		// ACTION
		$wfcom = new FormCglFonctionCommune ($this->db);
		if ($TypeListe == 'generale') {	
			$out .= '<td '.$attrib.' align=left  '.$size_action.' ><div id="div_action'.$line->id.'">';
			if (empty($action)) {
				$out .= $line->action_courante;
			}
			else  {
				$wtaille= "95%";
				$out .=$wfcom->Affiche_zone_texte('arg_action', $line->action_courante, '', $wtaille, $attrib, false);
			}	
			$out .= '</div></td>';	
		}
		// TITRE et Echange 
		if (empty($action)) {
			if ($TypeListe == '4saisons') {
				$out .= '<td  align=left '.$attribtitre .' '.$size_echange.' ><div id="div_titre'.$line->id.'"  >';
				$linedesc =  $this->AfficheEchangeImg( $line_echange->id, $line->id, $line_echange->date_realise,  $line_echange->action, $line_echange->datec,
									$line_echange->tms, $line_echange->titre, $line_echange->interlocuteur, $line_echange->id_interlocuteur,  
									$line_echange->URfirstname, $line_echange->URlastname, $line_echange->desc, 1);	
				$out .= '<b><span style="color:Navy" >'.$linedesc.'</span></b>';
			$out .= '</div></td>';	
			}
			else
			{	
				$out .= '<td  align=left '.$attribtitre .' '.$size_echange.' ><div id="div_titre'.$line->id.'"  >';
				if ($TypeListe <> 'generale') {
				$out .= '<b><span style="color:Navy" >'.$line_echange->titre.'</span></b>';
				}			
				else $out .= $line->descriptioncondense;
				$out .= '</div></td>';	
			}
		}
		else  {
			$styleinput='size="35%"';
			//$out .= '<td  '.$size_echange.' '.$attribtitre .' align=left>';
			$out .= '<td  '.$size_echange.' '.$attribtitre .' align=left>';
			$out .= '<input class="flat" '.$styleinput.' value="'.$line_echange->titre.'" type="text" id="arg_titre" name="arg_titre" >';

			$out .= '</td>';	
		}
		
		// INTERLOCUTEUR		
		
/* inutile danssaisons  car dans la colonne du titre 
		if ($TypeListe == '4saisons') {	
			if (!empty ($action ) ) {
				$out .= '<td '.$attrib.'  align=left '.$size_interlocuteur.' id = "toto" >';
				// cas ligne normale, les infos sont dans id_interlocuteur et interlocuteur
				$events = 'onclick="RechTelMailTiers(this)';
				$out .= $this->select_client($line_echange->id_interlocuteur, $line_echange->interlocuteur,'arg_idtiersLig','','arg_nvtiersLig','R','inter','',1,'',0,$events, 0,  '', 0, $line_echange->id);	
				$out .= '<br><i>NOM Prenom</i></br>';	
				$out .= '</td>';				
			}
		
			else {
				$out .= '<td '.$attrib.'  align=left '.$size_interlocuteur.' id = "toto" >';
				$out .= $line_echange->interlocuteur;	
				$out .= '</td>';				
			}
		}
		
*/		
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers') {	
			$out .= '<td '.$attrib.'  align=left '.$size_interlocuteur.' id = "toto">';
			if ($line_echange->id_interlocuteur > 0) $out .= $this->getNomUrl("object_company.png", 'Tiers',0,'', $line_echange->id_interlocuteur)."&nbsp";
			if (!empty ($action ) ) {
				// cas ligne normale, les infos sont dans id_interlocuteur et interlocuteur
				$events = 'onclick="RechTelMailTiers(this)';
				$out .= $this->select_client($line_echange->id_interlocuteur, $line_echange->interlocuteur,'arg_idtiersLig','','arg_nvtiersLig','R','inter','',1,'',0,$events, 0,  '', 0, $line_echange->id);	
				$out .= '<br><i>NOM Prenom</i></br>';				
			}
			else {
				$out .= '<b>'.$line_echange->interlocuteur.'</b>';
			}
			$out .= '</td>';	
		}

		/* ACTION
		if ($TypeListe == 'dossier') {	
			$out .= '<td '.$attrib.' align=left  '.$size_action.' >';
			if (empty($action) )	$out .= $line_echange->action;
			else	$out .= '<input class="flat" value="'.$line_echange->action.'" type="text" name="arg_action" id="arg_action">';
			$out .= '</td>';	
		}
			
		*/
		if ($TypeListe <> 'generale' ) {	
			$out .= '<td '.$attrib.' align=left  '.$size_action.' ><span id="span_action'.$line_echange->id.'"  name="span_action">';
			if (empty($action)) 	$out .= $line_echange->action;
			elseif (!empty($action))  {
				if ($TypeListe == "4saisons") $rows = ROWS_1;
				else $rows = ROWS_5;				
				$wtaille= 1;
				$out .= $wfcom->Affiche_zone_texte('arg_action', $line_echange->action, '', $wtaille, $attrib, false, $rows);	
			}
			$out .= '</span></td>';	
		}
		
		// REALISATION
//		if  ($TypeListe <> 'generale' and $TypeListe <> '4saisons' and  !empty( $line_echange->action)) {
		if  ($TypeListe <> 'generale'  and  !empty( $line_echange->action)) {
			$out .= '<td '.$attrib.' align=left  '.$size_action.'  ><div id="div_real'.$line_echange->id.'"  name="div_real">';	

			if (empty($action))	{
				$out .= '<font size=2 color=grey>';	
				if (!empty($line_echange->date_realise)){				
					$outtemp = dol_print_date($line_echange->date_realise,"%d/%m");
					if (!empty($line_echange->user_realise )) 	
						$outtemp .= ' --- '.$line_echange->user_realise.'  ';
					if ($TypeListe <> '4saisons') {
						$out .= $outtemp.' ';
						$outtemp = '';
					}
					else 
						$outtemp.= '--- ';
					$out .=	img_picto($outtemp.'Cliquer pour action non realisee', DOL_URL_ROOT.'/custom/CahierSuivi/theme/cahiersuivi/img/button_cancel.png', 'onclick="EnrErrReal(this, '.$line_echange->id.', '.$line->id.')" ', true);	
					$out .= '</font>';
				}
				else 
					// $out .=img_picto('Cliquer pour action realisee', DOL_URL_ROOT.'/custom/CahierSuivi/theme/cahiersuivi/img/exclamation.png', 'id="ImgReal" onclick="EnrReal(this, '.$line_echange->id.', '.$line->id.')" ', true);
					$out .= $this->Realisation($line_echange->id, $line->id);
				}
			$out .= '</div></td>';	
		}
		elseif ($TypeListe <> 'generale' ) 
			$out .= '<td  '.$attrib.' align=left  '.$size_action.' ></td>';

		// PRIORITE	
		if ($TypeListe == 'generale'  or $TypeListe == 'tiers' )  {	
			/*if (empty($action)) {
				$out .= '<td BGCOLOR="'.$line->coulpriorite.'"  '.$attrib.' '.$attribzonecol.' align=center '.$size_statut.' >';
				//$out .=  $line->priorite;	
				if (!empty($line->priorite) ) $out .= info_admin ($langs->trans('SC_Aide_Priorite', $line->priorite), 1);
			}
			else	{*/	

				if (empty($line->id)) $wnomObjetColor = 'td0';
					else $wnomObjetColor = 'td'.$line->id;
				if ($TypeListe == 'tiers') {
					$wnomObjetColor .= '-'.$line_echange->id;
					$ClassDossier = 'suivitiers'.$line->id;
				}
				else $ClassDossier = '';

				if (empty($line->coulpriorite)) $bgcolor = "FFFFFF"; else $bgcolor = $line->coulpriorite;
				//if (empty($line->id)) $wtdid = 0; else $wtdid = $line->id;
				if (empty ($line_echange) or empty($line_echange->id)) $line_echange->id = 0;
				$out .= '<td  class="'.$ClassDossier.'" '.$attrib.' align=left '.$size_statut.' style="background-color:#'.$bgcolor.';" id="'.$wnomObjetColor.'">';
				$out .= '<div id="div_priorite">';
				//$wtaction = ($flEventOnchange) ? 'saisie' : 'Ajout';
				$wtaction =  'saisie' ;
				$out .= $this->select_priorite($line->fk_priorite,'arg_priorite',$line->id,$wnomObjetColor,'', 1,1,'',0, '', 0, 1,$wtaction,'style="width:100%;"',1);
				$out .= '</div>';
			//}		
			$out .= '</td>';
		}	
		// origine contact		
		/*if ($TypeListe <> 'dossier')  {	
			$out .= '<td '.$attrib.' align=left '.$size_origine.' >';
			$out .= '<div id="div_origine">';
			if (empty($action)) 	$out .=  $line->origine;
			else	{
				$this->selectInputReason($line->fk_origine,'arg_origine','',1);
			}
			$out .= '</div></td>';
		}*/	
		
		//TELMAIL et TELEPHONE	et MAIL
		// on affiche la saisie de tierstel et tiers mail, dans la ligne en saisie
		//   si interlocuteur <> tiers dossier
			
		if ($TypeListe <> 'tiers') {		
			if ( $TypeListe == '4saisons')  
				$out .= '<td '.$attrib.'    align=left '.$size_telmail.' >';			
			else  {
				$colspan = 2 ;
				$out .= '<td '.$attrib.'  colspan='.$colspan.'  align=left '.$size_telmail.' >';
			}	// si l'interlocuteur est différent que le tiers du dossier, on permet la saisie du téléphone
			if ($TypeListe == 'generale') {
				$valtemptel = $line->TiersTel	;
				 $valtempmail = $line->TiersMail;
				 $valtemptelmail = $line->telmail;
			}
			else {
				$valtemptel = $line_echange->Interphone;
				$valtempmail = $line_echange->Interemail;
				 $valtemptelmail = $line_echange->telmail;					
			}
			if ( ! empty($action) ) {
				// div id=telmail  uniquement en cas de saisie 
				//if ($TypeListe <> 'dossier')	$out .= '<div id="telmail"><span id=spantelmail>'. $line->telmail.'</span></div><br>';
				//else 			$out .= '<div id="telmail"><span id=spantelmail>'.  $line_echange->telmail.'</span></div><br>';
				if ($TypeListe == 'dossier' or  $TypeListe == '4saisons')	
					$out .= '<span id=telmail>'. $line->telmail.'</span><br>';
				else 			
					$out .= '<span id="telmail">'.  $line_echange->telmail.'</span><br>';
				if ((!empty($line->id_saisietiers) and $line->id_saisietiers <> $line_echange->id_interlocuteur) or (empty($line->id_saisietiers) and $line->fk_tiers <> $line_echange->id_interlocuteur)) 
					$visibilite = 'visible';
				else $visibilite = 'hidden';
				if (($TypeListe == 'dossier' or $TypeListe == '4saisons')  and $line_echange->id_interlocuteur <> $line->fk_tiers) $visibility = 'visible';
				else $visibility = 'hidden';
				$out .= '<span id="spantierstel" style="size:100%;visibility:'.$visibility.'" >Tel:</span>';
				$out .= '<input id="tierstel" class="flat"  value="'.$valtemptel.'" type="text" name="arg_telLig"  style="size:100%;visibility:'.$visibilite.'" >';
				$out .= '<span id="spantiersmail" style="size:100%;visibility:'.$visibility.'" >Mail:</span>';
				$out .= '<input id="tiersmail"  class="flat"  value="'.$valtempmail.'" type="text" name="arg_mailLig"  style="width:100%;visibility:'.$visibilite.'">';
			}	
			else {
					$out .=   $valtemptelmail.'</br>';
			}	
									
			$out .= '</td>';			
		}	
		
		//Image Enregistrer
		$out .= '<td '.$attrib.' align=left '.$size_image.' >';
		if ($TypeListe == '4saisons') {
			if ($action == 'saisie' or !empty($action)) {
				$out .= '<input type="image"  title="Enregistrer" src="'.DOL_URL_ROOT.'/custom/CahierSuivi/theme/cahiersuivi/img/save.png" alt="'.$langs->trans("Enregistrer").'">';
				$out .= '</td><td '.$att_color.'>';
			}
		}
	/*
		elseif ( $TypeListe == 'generale' or  $TypeListe == 'dossier'  or $TypeListe == 'tiers') {
			if ($action == 'saisie' or !empty($action)) {
				$out .= '<input type="image"  title="Enregistrer" src="'.DOL_URL_ROOT.'/custom/CahierSuivi/theme/cahiersuivi/img/save.png" alt="'.$langs->trans("Enregistrer").'">';
				$out .= '</td><td '.$att_color.'>';
			}
		}
	*/ 
	//if (empty($line_echange->fk_dossier)) $line_echange->fk_dossier = 0;
		//Image Modifier
	//		if (($TypeListe  == 'dossier'  or $TypeListe == '4saisons' ) and (empty($action)))  // 4saisons ne fonctionnent pas. A remettre et voir les argument de URL si demande utilsiateur
		if ($TypeListe  == 'dossier'   and (empty($action))) 
			$out .= '<a href="'.$_SERVER["PHP_SELF"].'?typeliste='.$TypeListe.'&Reftiers='.$Reftiers.'&ID='.$Reftiers.'&Refdossier='.$Refdossier.'&search_dossier='.$search_dossier.'&arg_id_dossier='.$line->id.'&btaction=edit&arg_idEchange='.$line_echange->id.'">'.img_edit().'</a>';							
		elseif (($TypeListe == '4saisons' ) and (empty($action))) { 
		if (empty($line_echange->fk_dossier)) $wt = 0;
		else $wt = $line_echange->fk_dossier;
			$out .= '<a href="#" onclick="OuvreModEchg_Mod(this, '.$line_echange->id.','.$wt.');">'.img_edit().'</a>';	
		}
		else '&nbsp';
		//Image supprime	
		if (($TypeListe == 'dossier'  or $TypeListe == '4saisons' ) and (empty($action)))   // 4saisons ne fonctionnent pas. A remettre et voir les argument de URL si demande utilsiateur
		{
			$out .= '<a href="'.$_SERVER["PHP_SELF"].'?typeliste='.$TypeListe.'&Reftiers='.$Reftiers.'&ID='.$Reftiers.'&search_tiers='.$search_tiers.'&Refdossier='.$Refdossier.'&btaction=Supprime&arg_idEchange='.$line_echange->id.'&idbull='.$idbull.'">'.img_delete().'</a>';							
		}
		else '&nbsp';
		
		$out .= '</td></tr>';	
		



		// DECRIPTION 	
		if ( $TypeListe <> '4saisons' or (  $TypeListe == '4saisons' and !empty($action))) 
		{
			$out .= '<tr '.$bc[$var].'><td '.$attribtitre.' align=left>';
			if (empty($action))	$out .= $line_echange->desc;
			else  {
				if ($TypeListe <> 'generale') $wtaille= 50;
				else $wtaille= "97%";
				if ($TypeListe == "4saisons") $rows = ROWS_1;
				else $rows = ROWS_5;			
				
				$out .= $wfcom->Affiche_zone_texte('arg_description', $line_echange->desc, '', $wtaille, $attribtitre, false, $rows);	
			}	
			unset ($wfcom);
			$out .= '</td>';
		}	
		return $out;
	} //GetLignedossier

	function InfoCarSpeTexte()
	{
		global $langs;
		
		$out .= '<img  src="/dolibarr/theme/eldy/img/info.png"   alt="'.$langs->trans("CS_InfoCarSpeTexte").'" ';
		$out .= 'title="'.$langs->trans("CS_InfoCarSpeTexte").'" class="hideonsmartphone" border="0"/>';
		return $out;
	} //InfoCarSpeTexte

	/*
	*
	* Permet d'afficher tous les échanges d'un dossier en condensé
	*
	*	@param	int		$id_dossier	Identifiant du dossier
	*	@retour string	texte à afficher	
	*/
	function AfficheCondenseEchange($id_dossier)
	{	
		global $conf, $langs;
		$sql='SELECT DISTINCT  dd.rowid, dd.datec, dd.tms, titre, description, action , date_realise, user_realise, statut_action , dd.fk_soc, nom , lastname, firstname, fk_dossier  ';
		$sql .= " FROM " . MAIN_DB_PREFIX . "cglavt_dossierdet as dd" ;
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s on dd.fk_soc = s.rowid " ;
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "user as u on user_realise = u.rowid " ;
		$sql .= " WHERE fk_dossier ='".$id_dossier."'";
		$sql .= " ORDER BY dd.datec desc";
		//$sql.= $this->db->plimit($conf->liste_limit+1, $offset);
		$resql = $this->db->query($sql);
		
		if ($resql	)   {
			$num = $this->db->num_rows($resql);
			
			$ligndes ="";;
			$i=0;
			while ($i < min($num,$conf->liste_limit))	{		
				$obj = $this->db->fetch_object($resql);
				$ligndes .= $this->AfficheEchangeImg($obj->rowid, $obj->fk_dossier, $obj->date_realise, $obj->action, $obj->datec, $obj->tms, $obj->titre, $obj->nom, $obj->fk_soc,  $obj->firstname, $obj->lastname, $obj->description);			
				//if (!empty($obj->date_realise))  
				$ligndes .= '</br>';
				$i++;
			}			
			return $ligndes;			
		}
	} // AfficheCondenseEchange
	/*
	*
	* Permet d'afficher un échange d'un dossier en condensé
	*
	*	@param	int		$id_echange		Identifiant de l'échange
	*	@param	int		$id_dossier		Identifiant du dossier
	*	@param	date	$date_realise	date de réalisation de l'action
	*	@param	string	$action			action à réaliser
	*	@param	date	$datec			date de création de l'échange
	*	@param	datetime	$tms		date de MAJ de l'échange
	*	@param	string	$titre			titre de l'échange
	*	@param	string	$nom			nom de l'interlocuteur
	*	@param	int		$fk_soc			identifiant de l'interlocuteur
	*	@param	string	$firstname		prénom de l'interlocuteur
	*	@param	string	$lastname		nom de l'interlocuteur
	*	@param	string	$description	contenu de l'échange
	*	@param	boolean	$fl_sansImg		fl 1, pas d'image - 0 avec image
	
	*	@retour string	texte à afficher	
	*/
	function AfficheEchangeImg($id_echange, $id_dossier, $date_realise, $action, $datec, $tms, $titre, $nom, $fk_soc, $firstname, $lastname, $description, $fl_sansImg = 0)
	{		
		global $conf, $langs;


		if (empty($date_realise) and !empty($action))  $ligndes .= '<b>';
		else  $ligndes .= '<span style="color:grey">';
		if ($datec > 0  ) 	$ligndes .= dol_print_date($this->db->jdate($datec),"%d/%m/%Y");
		else $ligndes .= dol_print_date($this->db->jdate($tms),"%d/%m/%Y");
		$ligndes .= '&nbsp '.$titre;
		$ligndes .= '</b>';
		if (! empty($nom)){
			$ligndes .= '  ( avec ';			
			$ligndes .= $this->getNomUrl("object_company.png", 'Tiers',0,'',$fk_soc	);
			$ligndes .= ' '.$nom.')';
		}		
		if (!empty($description)){
			
			$texte = $description;	
			$texte=str_replace( "\n",'<br>',$description);
			$texte=str_replace( "\r",'',$texte);
			$texte=str_replace( "'",' ',$texte);
			//$ligndes .= ' '.img_picto($texte, 'info', 'onclick="AffAlt(this.alt)" id="img_info" name="img_info" ');
			$ligndes .= ' '.$this->info_bulle($texte, 'info', ' id="img_info" name="img_info" ');
		}
		if (empty($fl_sansImg) and  !empty($action)) {
				if (empty($date_realise) )
					//	$ligndes .= img_picto('Cliquer pour action realisee', DOL_URL_ROOT.'/custom/CahierSuivi/theme/cahiersuivi/img/exclamation.png', 'id="ImgReal"  onclick="EnrReal(this, '.$id_echange.', '.$id_dossier.')" ', true);
					$ligndes .= $this->Realisation($id_echange, $id_dossier);
				else  {
					$wdate=new DateTime($date_realise);
					$ligndes .= '<span style="font-size:smaller"><i>Le '.$wdate->format('d/m');
					if (!empty($firstname)) $ligndes .=' par '.$firstname;
					elseif (!empty($lastname)) $ligndes .=' par '.$lastname;
					$ligndes .= '</i></span>';
				}
			}
		
		$ligndes .= '</span>';
		return $ligndes;			
	} // AfficheEchangeImg


	// Copie dans info_picto
	function info_bulle($texte, $picto, $options) {
		
		global $conf;
		
		// Recherche de l'image
		// By default, we search $url/theme/$theme/img/$picto
		$url = DOL_URL_ROOT;
		$theme = $conf->theme;

		$path = 'theme/'.$theme;
		if (! empty($conf->global->MAIN_OVERWRITE_THEME_RES)) $path = $conf->global->MAIN_OVERWRITE_THEME_RES.'/theme/'.$conf->global->MAIN_OVERWRITE_THEME_RES;
		if (preg_match('/^([^@]+)@([^@]+)$/i',$picto,$regs))		{
			$picto = $regs[1];
			$path = $regs[2];	// $path is $mymodule
		}
		// Clean parameters
		if (! preg_match('/(\.png|\.gif)$/i',$picto)) $picto .= '.png';
		// If alt path are defined, define url where img file is, according to physical path
		if (! empty($conf->file->dol_document_root))
			foreach ($conf->file->dol_document_root as $type => $dirroot)	// ex: array(["main"]=>"/home/maindir/htdocs", ["alt0"]=>"/home/moddir/htdocs", ...)
			{
				if ($type == 'main') continue;
				if (file_exists($dirroot.'/'.$path.'/img/'.$picto))
				{
					$url=DOL_URL_ROOT.$conf->file->dol_url_root[$type];
					break;
				}
			}//Foreach
		// $url is '' or '/custom', $path is current theme or
		$fullpathpicto = $url.'/'.$path.'/img/'.$picto;
			
		//$texte = "Ici je met tout le texte que je veux, <b>meme de l\'html<br>et un retour à <br>laa ligne</b> !";
		$out .= '	    <span id="curseur" class="infobulle"></span>';
		$out .= ' <span onmouseover="montre ('."'".$texte."'".');" onmouseout="cache();">';
		$out .= "	<img src='".$fullpathpicto."' ".($options?' '.$options:'').'/>
			</span>	';
		
		return $out;

				
	}//info_bulle

	/*
	*
	* Afficher les actions du dossier  à réaliser
	*
	*	@param	int $id_dossier	Identifiant du dossier*
	*	@retour string 	texte à afficher
	*/
	function ActionsARealiser($id_dossier)
	{		
		global $conf, $langs;
		$sql='SELECT DISTINCT   action, date_realise , user_realise, firstname, lastname  ';
		$sql .= " FROM " . MAIN_DB_PREFIX . "cglavt_dossierdet as dd" ;
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "user as u on u.rowid = user_realise " ;
		$sql .= " WHERE fk_dossier ='".$id_dossier."'";
		$sql .= " ORDER BY dd.datec desc";
		$resql = $this->db->query($sql);		
		if ($resql	)   {
			$num = $this->db->num_rows($resql);
			
			$ActionCondence = "<ul>";
			$i=0;
			while ($i < min($num,$conf->liste_limit))	{	

				$obj = $this->db->fetch_object($resql);
				if (empty($obj->date_realise)  and !empty($obj->action) )  $ActionCondence .= ' <li>'.$obj->action.'</li>'; 
				$i++;
			}
			$ActionCondence .= "</ul>";	
			return $ActionCondence;
		}
	} // ActionsARealiser
	/*
	*	Affiche l' image pour permettre de signaler une action réalisée
	*
	*	@param	int		$id_echange		Identifiant de l'échange
	*	@param	int		$id_dos			Identifiant du dossier
	*
	*	@retour	string	texte html à afficher
	*
	*/

	function Realisation($id_echange, $id_dos)
	{
		//mettre l'image permettant de toper l'action réalise
		return  img_picto('Cliquer pour action realisee', DOL_URL_ROOT.'/custom/CahierSuivi/theme/cahiersuivi/img/exclamation.png', 'id="ImgReal" onclick="EnrReal(this, '.$id_echange.', '.$id_dos.')" ', true);

	} //Realisation

	/*
	*	Affiche la date de réalisation de l'action et le user l'ayant réalisé devant la croix rouge 
	*				sauf si on est en 4 saisons, et au quel cas, ces informations sont sur le hover de l'image
	*				Ajoute la croix image pour permet le retour à Action à réaliser
	*
	*	@param	int		$id_echange		Identifiant de l'échange
	*	@param	int		$id_dos			Identifiant du dossier
	*	@param	string	$origine		4saisons ou autre
	*	@param	string	$user_realise	Nom du user ayant réalisé l'action
	*
	*	@retour	string	texte html à afficher
	*
	*/
	function DateRealisation ($id_echange, $id_dos, $origine, $user_realise = "")
	{
		global $user, $db;
		// donner date et user ayant fait la réalisation 
		//mettre l'image permettant de toper l'action à non realiser en cas d'erreur
			if ($origine <> '4saisons') {
				$texte = date("d/m");
				if (!empty($user->firstname)) $texte .= '<br> par '.$user->firstname.'  ';
				elseif (!empty($user->lastname)) $texte .= '<br> par '.$user->lastname.'  ';
			}
			else  {
				$outtemp = date("d/m"); 
				if (!empty($user_realise )) 	
					$outtemp .= ' --- '.$user_realise.' --- ';

			}
			$texte .=	img_picto($outtemp.'Cliquer pour action non realisee', DOL_URL_ROOT.'/custom/CahierSuivi/theme/cahiersuivi/img/button_cancel.png', 'onclick="EnrErrReal(this, '.$id_echange.', '. $id_dos.')" ', true);		
			return $texte;
	} //DateRealisation
	/*
	*	transforme une date avec un format JJ/MM/AA au format mysql AAAA-MM-JJ
	*
	*	@param	string	$strdate	date au format un format JJ/MM/AA
	*
	*	@retour	string	date au format mysql
	*
	*/
	function transfDateMysql($strdate)
	{
		$pos1 = 0;
		$pos2= strpos($strdate, '/');
		$lg = strlen($strdate);
		if (empty($strdate)) return;
		if ($pos2 != strlen($strdate) -1) $pos3 = strpos($strdate, '/', $pos2+1);
		else return $strdate;
		if ($pos3 == 0) return ;
		
		// on consid-re qu'un sportif sera ag?e moins de 95 ans - pour mettre 19 ou 20 comme si?e de naissance
		$now = dol_now('tzuser');
		
		$annsiecle = strftime("%Y",$now) + 5;

		$text = substr($strdate,$pos3+1 );
		if ( $text <100)
			{
				if ($text > $annsiecle) $annee = '19'.$text;
				else $annee = '20'.$text;
			}
		else $annee = $text;
		$mois = substr($strdate,$pos2+1,$pos3 - $pos2-1);
		if (strlen($mois) == 1) $mois = '0'.$mois;
		$jour = substr($strdate,$pos1, $pos2);
		if (strlen($jour) == 1) $jour = '0'.$jour;		
		$datemysql = $annee.'-'.$mois.'-'.$jour;
		return $datemysql;
	}	/* transfDateMysql */
	/*
	* Récupère les données de contacts du tiers et des contacts de celuici
	* 
	*
	*/
	function ChercheTelMailIdTiersContact ($line,$idTiers)
	{

		$sql = "SELECT st.phone as TiersTel, st.email, stex.s_tel2 as TiersSupTel,  stex.s_email2 as TiersSupMail, p.code as country_code ";
		$sql.= " FROM ".MAIN_DB_PREFIX ."societe as st";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX ."societe_extrafields as stex on fk_object = st.rowid ";
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_country as p ON st.fk_pays = p.rowid';
		$sql.= " WHERE st.rowid ='".$idTiers."'";
		dol_syslog(get_class($this)."::ChercheTelMailIdTiersContact sql=".$sql, LOG_DEBUG);
		$resql = $this->db->query($sql);	
		
		if ($resql)         {
			$num = $this->db->num_rows($resql);
			if ($num)     {;
				$objc = $this->db->fetch_object($resql);
				$line->TiersTel			= $objc->TiersTel;
				$line->TiersSupTel		= $objc->TiersSupTel;
				$line->country_code		= $objc->country_code;
				$line->TiersMail		= $objc->email;
				$line->TiersSupMail		= $objc->TiersSupMail;	
				unset ($objc);
			}
		}
		else    {
			dol_print_error($this->db);
		}
		if ($num > 0)     
			return $this->ChercheTelMailTiersContact($line->TiersTel, $line->TiersSupTel, $line->TiersMail, $line->TiersSupMail, $idTiers,  $objc->country_code);
		return '';
		} // 	ChercheTelMailIdTiersContact

	function ChercheTelMailTiersContact($tel, $telsup, $mail, $mailsup, $idTiers, $country_code = 'FR')
	{	
		global $TypeListe, $conf;
		
		global $Reftiers, $Refdossier, $search_typedossier, $search_tiers;
		$wfctDol = new CglFonctionDolibarr ($this->db);

		// Format 'Tiers':tel1 - tel2 - mail1 - mail2
		// 			'Contact': Nom - tel1 - tel2 - tel3 - mail1 - mail2
		// 			: Nom - tel1 - tel2 - tel3 - mail1 - mail2

		$lib = '<i><u>Tiers</u></i>';		

		if ($i == 0) 
		$wTelMail = '';
		// Ligne Tiers/Interlocuteur : tel ou tel -- mail ou mail
		$wtel ='<font size=2>'. dol_print_phone($tel,  $country_code, $idTiers, '', 'AC_TEL').'</font>';
		if (!empty($tel)) $wTelMail 	= $lib. ': '.$wtel;
		if ( !empty($telsup)) 	{
			if (!empty($wTelMail))  $wTelMail 	.=  ' ou ';
			else  $wTelMail 	=  $lib. ': ';
			$wtelsup = '<font size=2>'.dol_print_phone($telsup,  $country_code, $idTiers, '', 'AC_TEL').'</font>';
			$wTelMail 	.=  $wtelsup;
		}				
		if (!empty($wTelMail))  $wTelMail 	.=  ' -- ';
		else  $wTelMail 	=  $lib. ': ';
		if (!empty($mail)) $wTelMail 	.=  $wfctDol->dol_print_email_image($mail,0,$Reftiers,'',1,20,1,1);
		if ( !empty($mailsup)) 	{
			if (!empty($wTelMail))  {
				if (empty($mail))$wTelMail 	.=  ' -- ';
				else $wTelMail 	.=  ' ou ';		
				}					
			else  $wTelMail	=  $lib. ': ';
			$wTelMail	.=  $wfctDol->dol_print_email_image($mailsup,0,$Reftiers,'',1,20,1,1); 
		}
			
		if (!empty($wTelMail)) $wTelMail .= '<br>';
			
		// CONTACTS recherche les contacts du client et récuperer ses tel et mail
		$sql = "SELECT sp.rowid, sp.lastname, sp.firstname, sp.phone, sp.phone_perso, sp.phone_mobile,  sp.email, spex.s_email2, p.code as country_code ";
		$sql.= " FROM ".MAIN_DB_PREFIX ."socpeople as sp";
		$sql.= " LEFT JOIN ".MAIN_DB_PREFIX ."socpeople_extrafields as spex on fk_object = sp.rowid ";
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_country as p ON sp.fk_pays = p.rowid';
		$sql.= " WHERE sp.fk_soc ='".$idTiers."'";
		if (! empty($conf->global->CONTACT_HIDE_INACTIVE_IN_COMBOBOX)) $sql.= " AND sp.statut <> 0 ";	
	   // $sql.= " ORDER BY  sp.lastname, sp.firstname'";
		dol_syslog(get_class($this)."::ChercheTelMailTiersContact sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)         {
			$num = $this->db->num_rows($resql);
			if ($num)            {
				$lib = '<i><u>Contacts </u></i><br>';
				$wTelMailContTotal='';
				$i = 0; $flgEntete=false;
				while ($i < $num and $i < 3)   {
					$wTelMailCont = '';
					$objc = $this->db->fetch_object($resql);
					$wcountry_code		= $objc->country_code;
					if (!empty($objc->phone) or !empty($objc->phone_perso) or !empty($objc->phone_mobile)) {
							if ($flgEntete == false) {
								$wTelMailCont .= $lib;
								$flgEntete  = true;
							}
							$wTelMailCont .= '<b>'.$objc->firstname.' '.$objc->lastname.'</b>: ';
							if (!empty($objc->phone)) $wTelMailCont .= ' <font size=2>'.dol_print_phone($objc->phone,$wcountry_code,$objc->rowid, '','AC_TEL').'</font>' ;
							if (!empty($objc->phone_perso)) {
								if (!empty($objc->phone)) $wTelMailCont	.=  ' ou ';											
								$wTelMailCont .= '<font size=2>'.dol_print_phone($objc->phone_perso,$wcountry_code,$objc->rowid, '','AC_TEL') .'</font>';
							}		
							if (!empty($objc->phone_mobile)) {
								if (!empty($objc->phone) or !empty($objc->phone_perso) ) $wTelMailCont	.=  ' ou ';	
								$wTelMailCont .=  '<font size=2>'.dol_print_phone($objc->phone_mobile,$wcountry_code,$obj->rowid, '','AC_TEL') .'</font>';
							}										
					}

					if (!empty($objc->email) or !empty($objc->s_email2) ) {
						if (empty($wTelMailCont)) {
							if ($i == 0) $wTelMailCont = $lib;
							$wTelMailCont .= '<b>'.$objc->firstname.' '.$objc->lastname.'</b>: ';
						}	
						else $wTelMailCont	.=  ' -- ';
						if (!empty($objc->mail)) 
							$corps = $objc->firstname.' '.$objc->lastname;
							// $wTelMailCont .= ' '.$objc->email;
								$wTelMailCont .= ' '.$wfctDol->dol_print_email_image($objc->email,0,$Reftiers,$corps,1,20,1,1); 
							if (!empty($objc->s_email2)) {
								if (!empty($objc->mail)) $line->mail .= ' ou ';
									//	$wTelMailCont .= $objc->s_email2;
									$wTelMailCont .= ' '.$wfctDol->dol_print_email_image($objc->s_email2,0,$Reftiers,$corps,1,20,1,1); 
							}
					}
					$wTelMailContTotal  .= $wTelMailCont ;
					if (!empty( $wTelMailCont )) $wTelMailContTotal .= '<br>';				
					$i++;
				} // While
				if ($num >= 3) $wTelMailContTotal .='<b>.....</b>';
			
				$wTelMail.= $wTelMailContTotal;	
			} // if num
			unset($wfctDol);
			
			return $wTelMail;
		} // if resql
		else
		{
			unset($wfctDol);
			dol_print_error($this->db);
		}
	} //ChercheTelMailTiersContact

	 /*
	 * Prépare l'affichage de la ligne Dossier + lien +  Priorité modifiable, dans BU/LO/RE
	 *
	 *  @param int		id_dossier	identifiant du dossier
	 *  @param strig	libelle		nom du dossier
	 *  @param int 		id_propriete	identifiant du la propriété du dossier 
	 *  @param string 	htmlname		nom de la boite de sélection, dans BU/LO/RE, la ligne en haut ou en bas de page
	 
	 */
	function html_AffDossier($id_dossier, $libelle, $id_propriete, $htmlname, $color_priorite = '"#ffffff')
	{
 		// AFFICHAGE DOSSIER 
		// Prepare titre
		// Nom du dossier	
		global $bull, $langs;

		//$out =  '<table><tr><td>';
		$out =  '<td>';
		$out .=  $this->getNomUrl("object_company.png", 'Dossier', 0, $id_dossier, $bull->id_client).' &nbsp;&nbsp;'. $libelle ;
		$out .=  '</td><td>';
		// Nom du Priorité
		$out .= '&nbsp-&nbsp<span style="font-size:12px; font-weight:bold">'.$langs->trans('Statut').'</span>&nbsp&nbsp';
		$wdossier = new cgl_dossier ($this->db);
		$out .=  '</td><td id="td0" style="background-color='.$color_priorite.'">';			
		$out .= $this->select_priorite($id_propriete,$htmlname,$id_dossier,'priorite'.$id_dossier,'', 1,1,'','', '', 0, 1,'saisie', 'saisie','style="width:100%"',1);
		$out .=  '</td><td>';
		if ($bull->type == 'Loc') $titre = $langs->trans("LibChangDossier", 'contrat');
		elseif ($bull->type == 'Insc') $titre = $langs->trans("LibChangDossier", 'bulletin');
		// Bouton Changement de dossier - discretion
		//$out .= '<a class="butAction" href="#" onclick="OuvreModListDos(this,'. $bull->id_client.', '.$id_dossier.', '. $bull->id.');" title="'.dol_escape_htmltag($langs->trans("LibChangDossier")).'">'.$langs->trans('ChangDossier').'</a>';	
		$out .= '&nbsp&nbsp&nbsp<a href="#" onclick="OuvreModListDos(this,'. $bull->id_client.', '.$id_dossier.', '. $bull->id.');" title="'.dol_escape_htmltag($langs->trans("LibChangDossier")).'" style="color:grey;font-size:10px">'.$langs->trans('ChangDossier').'</a>';	

		$out .=  '</td>';
		//$out .=  '</td></tr></table>';
		return ($out);
		unset ($wdossier);
	}//html_AffDossier
 
	/*
	*
	*	construit le tableau des action à réaliser
	*
	* 	@param	int		$id			Identifiant du dossier
	*	@param  chaine	$typeAff	0 pour un affichage texte / 1 pour un affichage sur infobulle	
	*	@retour	Texte à afficher 
	*/
	function ConstructAction($id, $typeAff)
	{
		global $db;
		global $dossiers;
	
		if ($dossiers[$id] == $id) return '';
		// Lecture
		$sql .= 'SELECT e.rowid as IdEchange,  e.action, e.date_realise, e.user_realise , u.login ';
		$sql .= 'FROM ' . MAIN_DB_PREFIX . 'cglavt_dossierdet as e   ';
		$sql .= 'LEFT JOIN  ' . MAIN_DB_PREFIX . 'user as u  on u.rowid = e.user_realise ';
		$sql .= "WHERE e.fk_dossier = ".$id; 
		
		$resql = $db->query($sql);
		dol_syslog('ConstructAction::',LOG_DEBUG);
		
		
		$text = "";
		$num = $db->num_rows($resql);
		$i = 0;
		while ($i < $num ) {
			$obj = $db->fetch_object($resql);
			if ( !empty($obj->action) and empty($obj->date_realise)) {
				$text = $obj->action;	
				if ($typeAff == 1) {
//					$out .= info_admin($text,1);
					$img = DOL_URL_ROOT.'/custom/CahierSuivi/theme/cahiersuivi/img/exclamation.png';
//					$out .=  img_picto($text.'-- Cliquer pour action realisee', $img, 'id="ImgReal" onclick="EnrReal(this, '.$obj->IdEchange.', '.$id.')" ', true);
					$out .= $this->Realisation($obj->IdEchange, $id);
				}
				else $out .=  $text;	
			}
			$i++;
		}
		return $out;
	} //ConstructAction

	/*
	*
	* Affiche les info concernant le dossier dans inscription/location
	*
	*	@param int 		$id_dossier	Identifiant du dossier
	*	@param string 	$TypeListe	'générale','tiers', 'dossier','4saisons'
	*	@param string 	$origine	Précise si on veut une fenêtre modale ou non
	*
	*	@retour	néant
	*/	
	function html_PaveSuivi ($id_dossier, $TypeListe, $origine="Ecran") 
	{
		global $langs, $action, $bull, $bc, $conf;
		global 	$ENR_ECH;
		global $Idechange ;
		
		$out = '';

	/* Tableau affichage général */
		$line = new cgl_dossier($this->db);
		$line_echange = new cgl_echange($this->db);
		$TypeListe = '4saisons';
		
		$wfctcomm = new FormCglFonctionCommune($this->db);
		
		$sql = "SELECT DISTINCT   d.rowid as IdDossier, d.libelle , d.fk_priorite , d.fk_soc,  e.rowid as IdEchang,  e.datec as EchDate, e.tms as Echtms";
		$sql .= " , e.description, e.titre, e.action ";
		$sql .= ", inter.rowid as idInter, inter.nom as nomInter, inter.phone as InterTel, inter.email as Interemail ";
		$sql .= ", interex.s_tel2 as InterSupTel,  interex.s_email2 as InterSupMail,  pinter.code as Icountry_code";
		$sql .= ",   ume.lastname as NomMod, ume.firstname as PrenomMod , ume.rowid as fk_Moduser";
		$sql .= ", uce.lastname as NomCreateurEch, uce.firstname as PrenomCreateurEch, uce.rowid as fk_CreateurEch ";
		$sql .= ", e.date_realise, ur.rowid as user_realise ,ur.lastname as URlastname, ur.firstname as URfirstname";
		$sql .= ", spri.label, spri.color";

		$sql .= " FROM " . MAIN_DB_PREFIX . "cglavt_dossier as d ";	
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cglavt_dossierdet as e on fk_dossier = d.rowid";	
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as inter ON e.fk_soc = inter.rowid  ";	
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_extrafields as interex ON interex.fk_object = inter.rowid  ";		
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_country as pinter ON inter.fk_pays = pinter.rowid';
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "user as ume  ON e.fk_user_mod = ume.rowid";	
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "user as uce  ON e.fk_user_create	 = uce.rowid";	
		$sql .=  " LEFT JOIN " . MAIN_DB_PREFIX . "user as ur  ON user_realise = ur.rowid";		 	
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cglavt_c_priorite as spri on fk_priorite = spri.rowid";
		$sql .= " LEFT JOIN ". MAIN_DB_PREFIX . "user as uc	 on d.fk_createur = uc.rowid ";		
		$sql .= " WHERE d.entity IN (" . getEntity('agsession') . ")";		
		$sql .= " AND d.rowid ='".$id_dossier."' ";			
		$sql .= " ORDER BY e.datec DESC";
		dol_syslog ('PaveSuivi');		
		$resql = $this->db->query($sql);

		if ($resql	)   	$num = $this->db->num_rows($resql);
				
		if ($bull->type == 'Insc') $id_obj = 'id_bull';
		elseif ($bull->type == 'Loc') $id_obj = 'id_contrat';
		elseif ($bull->type == 'Resa') $id_obj = 'id_resa';
		$out .= '<div id="PaveSuivi" >';		
		$out .= '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
		$out .= '<input type="hidden" name="token" value="'.newtoken().'">';
		$out .= '<input type="hidden" name="btEnrEcg" value="'.$ENR_ECH.'">';	
		$out .= '<input type="hidden" name="'.$id_obj.'" value="'.$bull->id.'">';	
		$out .= '<input type="hidden" name="type" value="'.$bull->type.'">';	
		$out .= '<input type=hidden name=dossier value='.$id_dossier.'>';
		$out .= '<input type="hidden" name="typeliste" value="'.$TypeListe.'">';
		$out .= '<input type=hidden name=Refdossier value='.$Refdossier.'>';

		if ($resql	) $obj = $this->db->fetch_object($resql);	
		else {			
			setEventMessages ($langs->trans("Erreur Acces Dossier"),'', 'warnings');
			return;
		}
		
		if ($origine == "") {
		// Préparation fenêtre modale pour changement de dossier		if ($conf->cahiersuivi) {			
			require_once(DOL_DOCUMENT_ROOT.'/custom/CahierSuivi/class/html.suivi_client.class.php');
			$wfDossier= new FormCglSuivi ($this->db);
			$out .= $wfDossier->PrepScript('4saisons'); // la gestion de l'affichage est du type du suivi-dossier

			/* Fenetre modale de modification */
			$wsuivi = new cgl_dossier ($this->db);
/*			[$line, $line_echange] = $wsuivi->Chargement(0,$id_dossier);
			$listchampVal = $wsuivi->ConstitueModlEcranEchg_Mod( $line, $line_echange);
			$wsuivi->AfficheModale($listchampVal, '<p>');

			$wsuivi->ScriptModale();
*/
			unset ($wfDossier);
		}
		$texte = $this->html_AffDossier($obj->IdDossier, $obj->libelle, $obj->fk_priorite, 'priorite', $obj->color);
		
		$out .= $wfctcomm->getParagraphe("TiSuivi", 4, $texte);			

		$out .= '<p>';	
		$out .= '<table border="1" id=Niv0_SuiviClient width=100% ><tbody><tr ><td>';
		$fg_fermediv = false;
		if ($num > 3) {
			$out .= '<div style="width:100%;height:200px;overflow:scroll;" id=DivScroll>'; 
			$fg_fermediv = true;
		}
 	
		$out .= '<table  id=Niv1_SuiviClient width=100% ><tbody><tr class="liste_titre">';
		if ($TypeListe == '4saisons' )
			$out .= getTitleFieldOfList("Titre",0,'','','','','style="width:56%;',0,'','');
		else			
			$out .= getTitleFieldOfList("Titre",0,'','','','','',0,'','');
		if ($TypeListe <> '4saisons' ) 
			$out .= getTitleFieldOfList("Interlocuteur",0,'','','','','',0,'','');
		$out .= getTitleFieldOfList("Action",0,'','','','','',0,'','');
//		if ( $TypeListe <> '4saisons' )
			$out .= getTitleFieldOfList("",0,'','','','','',0,'','');
		$out .= getTitleFieldOfList("Telephone/Mail",0,'','','','','',0,'','');

		if (empty($id_dossier)) $id_dossier = 0;
		$btAdd = '<img  src="'. DOL_URL_ROOT.'\theme\common\fontawesome-5\svgs\solid\plus-circle.svg" alt="Ajouter un échange" onclick="OuvreModEchg_Mod(this, 0, '.$id_dossier.', '. $bull->id.');" style="height: 20px;width: auto;"/> ';
		$out .= getTitleFieldOfList($btAdd,'','','','','align="center"','','');
		
		$out .= '</tr>';
		$out .= '<tr>'; 
				// on affiche  la ligne de saisie 
		//		en début de traitement (btaction vide)
		// 		on affiche une ligne de saisie vide si toute action précédente OK
		// sinon on récupère les information apssée en argument à l'URL
		//GETPOST('btEnrEcg')

/*		if (!empty($btaction ) and ( ($btaction == 'Enregistrer' and $error == 0 )  or $btaction == 'ConfSupprime'))
		{
			$out .=$this->getLignedossier($TypeListe,'','' ,1, 'saisie', $listtotal);	
			//elseif ($btaction == 'Enregistrer' and $error > 0  and empty($arg_idEchange)) 
//		elseif (($btaction == 'Enregistrer' and $error > 0  ) or  empty($btaction))
		}
		elseif ((GETPOST('btEnrEcg', 'alpha') and $error > 0  ) or  empty($btaction))
		{
			$out .=$this->getLignedossier($TypeListe,'','' ,1, 'saisie', $listtotal);			
		}
*/
		$out .= '</tr>';
		$i=0;
		while ($i <= $num ) 
		{			
			$var=!$var;
			$out .= "<tr  ".$bc[$var].">";
			if ($i>0) $obj = $this->db->fetch_object($resql); // dans le cas dossier, cela a déjà été lu 
			$line->id 				= $obj->IdDossier	;
			$line->fk_tiers 		= $obj->fk_soc 	;
			$line->dossier 			= $obj->libelle 	;
			$line->fk_priorite 		= $obj->fk_priorite 	;
			$line->tms 				= $obj->tms 	;				
			$line->action_courante 		= $this->ActionsARealiser($line->id) 	;				
			$line_echange->datec	=  $obj->EchDate;
			$line_echange->tms	=  $obj->Echtms;
			$line_echange->id =	$obj->IdEchang	;
			$line_echange->titre 			= $obj->titre ;
			$texte=str_replace(array(chr(13).chr(10),chr(10)),'<br />',$obj->description);
			$line_echange->desc =	$texte;			
			$texte=str_replace(array(chr(13).chr(10),chr(10)),'<br />',$obj->action);
			$line_echange->action=	$texte;	
			$line_echange->id_interlocuteur		 	= $obj->idInter 	;
			$line_echange->interlocuteur		 	= $obj->nomInter 	;
			$line_echange->Interphone		 	= $obj->InterTel 	;
			$line_echange->InterSupTel 	=  $obj->InterSupTel;
			$line_echange->InterSupMail =  $obj->InterSupMail;
			$line_echange->Interemail = $obj->Interemail;	
			$line_echange->NomMod 			= $obj->NomMod 	;
			$line_echange->PrenomMod 		= $obj->PrenomMod 	;	
			$line_echange->NomCreateur 			= $obj->NomCreateurEch 	;
			$line_echange->PrenomCreateur 		= $obj->PrenomCreateurEch 	;
			$line_echange->fk_user_create 		= $obj->fk_CreateurEch 	;
			$line_echange->fk_user_mod 		= $obj->fk_Moduser 	;
			$line_echange->telmail = '';
			if (!empty($obj->idInter) and $obj->idInter > 0) 
				$line_echange->telmail=$this->ChercheTelMailTiersContact($obj->InterTel, $obj->InterSupTel, $obj->Interemail,  $obj->InterSupMail, $obj->idInter, $obj->Icountry_code );
			else $line_echange->telmail = "";
			$line_echange->fk_user_realise		= $obj->user_realise 	;
			$line_echange->user_realise 		= (!empty($obj->URlastname))?$obj->URlastname:$obj->URfirstname 	;
			$line_echange->date_realise 		= $obj->date_realise 	;			
			
		
			if (empty($line_echange->id)) $flgEchangeVide = True;
			else $flgEchangeVide =   false;

		// reprendre la saisie qui n'a pas été enregistrée avec ligneechange_recup
			if ( ($btaction == 'edit'or ($btaction =='Enregistrer' and $error > 0) )
				and !empty($line_echange->id) 	and $arg_idEchange == $line_echange->id )   {
				if ($error > 0)  {  // erreur sur l'enregistrement on reprend les info saisies
						$line->recupInfoDos($TypeListe);
						$line_echange->recupInfoEch();
				}
//				$out .=$this->getLignedossier($TypeListe,$line,$line_echange ,$var, 'Saisie', $listtotal);	
			}
			elseif (!$flgEchangeVide ) {
				$out .=$this->getLignedossier($TypeListe,$line,$line_echange ,$var, '', $listtotal, $bull->id);
			}
			$out .= '</tr>';
			$i++;
			$var = !$var;
		} // While

		$out .= '</td></tr></tbody></table>';/* id=Niv1_SuiviClient*/
		if ($fg_fermediv == true) $out .= '</div>'; // Fermeture DivScroll
		$out .= '</td></tr></tbody></table>';/* id=Niv0_SuiviClient*/
		$out .= '</form>';
		$out .= '</div>';
		unset ($wfctcomm);
		return $out;
	} // html_PaveSuivi

	/*
	*	Affichages de boutons 
	*
	* @param	$i 			1 pour bouton haut de page, 2 our bouton bas de page
	* @param	$listtotal 	'total' si on affiche les dossiers clos, '' si on ne les affiche pas
	* @param	$params		parametres d'url pour conserver les filtres
	*
	* @retour néant
	*/	
	function boutons($i = 1, $listtotal ='', $paramsSuivi ='') 
	{
		global $langs, $conf, $search_tiers, $TypeListe,  $url, $Reftiers, $Refdossier, $search_typedossier, $Reftiers;
		$CREE_BULL = 'CreeBull';
		$SEL_TIERS = 'SelTiers';		
		// mettre ici un hook, lors de la livraison à Dolibarr
		$urlgen = '/'.substr ($url, 1, strripos($url, '_')).'cahier.php';
		$urlarg = 'typeliste='.$TypeListe;	
		if ($TypeListe == 'dossier')  $urlarg .= '&Refdossier='.$Refdossier;
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers') $urlarg .= '&ID='.$Reftiers;
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers') $urlarg .= '&Reftiers='.$Reftiers;
		if ($TypeListe == 'dossier' or $TypeListe == 'tiers') $urlarg .= '&socid='.$Reftiers;
		if ($TypeListe == 'generale') $urlarg .= '&socid='.$Reftiers;
		if ($TypeListe == 'dossier') $waction = $CREE_BULL;
		elseif ($TypeListe == 'tiers') $waction = $SEL_TIERS;
		$urlarg .= '&token='.newtoken();
		print '<div class="tabsAction">';
		if ($TypeListe <> 'generale') {
			if ($conf->cglinscription) {
				print '<div class="inline-block divButAction"><a class="butAction"  href="../../cglinscription/inscription.php?id_client='.$Reftiers.'&action='.$waction.'&dossier='.$Refdossier.'&BullFacturable=yes&idmenu=16899&mainmenu=CglInscription&token='.newtoken().'">';
					print $langs->trans('NvInscription').'</a></div>';
				print '<div class="inline-block divButAction"><a class="butAction"  href="../../cglinscription/location.php?id_client='.$Reftiers.'&action='.$waction.'&dossier='.$Refdossier.'&BullFacturable=yes&idmenu=16925&mainmenu=CglLocation&token='.newtoken().'">';
				print $langs->trans('NvLocation').'</a></div>';	
				//print '<div class="inline-block divButAction"><a class="butAction"  href="../../cglinscription/reservation.php?id_client='.$Reftiers.'&action='.$waction.'&dossier='.$Refdossier.'&idmenu=16935&mainmenu=CglResa&token='.newtoken().'">';
				//	print $langs->trans('NvReservation').'</a></div>';
			}
		}
		else {
			print '<div class="inline-block divButAction"><a class="butAction"  href="../../cglinscription/listeloc.php?mainmenu=CglLocation&token='.newtoken().'">';
			print $langs->trans('LstCont').'</a></div>';
			print '<div class="inline-block divButAction"><a class="butAction"  href="../../cglinscription/list.php?mainmenu=CglInscription&token='.newtoken().'">';
			print $langs->trans('LstBull').'</a></div>';
		}		
		
		print '<input class="button" name="btactionenr'.$i.'" type="submit" value="'.$langs->trans("Enregistrer").'">';
		//print '<div class="inline-block divButAction"><a class="butAction" href="'.$url.'?'.$urlarg.'">'.$langs->trans('Enregistrer').'</a></div>';
		if ($TypeListe <> 'dossier')  {
		if (empty($listtotal) ) 
				//print '<input class="button" name=type="submit" value="'.$langs->trans("Affichage complet").'"></div>';
				print '<div class="inline-block divButAction"><a class="butAction" href="'.$url.'?'.$urlarg.$paramsSuivi.'&listtotal=total">'.$langs->trans('listtotal').'</a></div>';
			else 
				print '<div class="inline-block divButAction"><a class="butAction" href="'.$url.'?'.$urlarg.$paramsSuivi.'">'.$langs->trans('listrest').'</a></div>';
		}
		if ($TypeListe <> 'generale') 	
		print '<input class="button" name="btactionenrret'.$i.'" type="submit" value="'.$langs->trans("EnrRetCah").'">';
		
		//MDU if ($TypeListe <> 'generale') 	
			//MDU print '<div class="inline-block divButAction"><a class="butAction" href="'. $urlgen .'?typeliste=generale&search_typedossier='.$search_typedossier.'">'.$langs->trans('Retour Cahier').'</a></div>';
		print '</div>';	
	} // boutons

	 
	 /**
     *  Output html form to select a secteur in the list of existing secteur
     *
     *	@param	string	$selected       Preselected type
     *	@param  string	$htmlname       Name of field in form
     *  @param  string	$filter         optional filters criteras (example: 's.rowid <> x')
     *	@param	int		$showempty		Add an empty field
     * 	@param	int		$showtype		Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo		Force to use combo box
     *  @param	array	$events			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *  @param	string	$filterkey		Filter on key value
     *  @param	int		$outputmode		0=HTML select string, 1=Array
     *  @param	int		$limit			Limit number of answers
     * 	@return	string					HTML string with
     */
   	function select_secteur( $selected='',$htmlname='secteur',$filter='',$showempty=0, $showtype=0, $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0)
	{
	    global $conf,$user,$langs;

        $out=''; $num=0;
        $outarray=array();
		// On recherche les societes
		$sql = "SELECT distinct rowid, label as secteur ";
		$sql.= " FROM ".MAIN_DB_PREFIX ."cglavt_c_secteur as ssect ";
		$sql.= " WHERE entity IN (".getEntity('societe', 1).")";
		if ($filter) $sql.= " AND (".$filter.")";
		
		$sql.=$this->db->order("secteur","ASC");
        dol_syslog("::select_secteur sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)        {
            if ($conf->use_javascript_ajax && ! $forcecombo)
            {
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
            	$out.= ajax_combobox($htmlname, $events, 1);
            }
			
            // Construct $out and $outarray
            $out.= '<select id="'.$htmlname.'" class="flat" name="'.$htmlname.'" style="width:100%">'."\n";
            if ($showempty) $out.= '<option value="-1"></option>'."\n";
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)            {
                while ($i < $num)                {
                    $obj = $this->db->fetch_object($resql);                    
                    if ($selected > 0 && $selected == $obj->rowid)     {
                        $out.= '<option value="'.$obj->rowid.'" selected="selected">'.$obj->secteur.'</option>';
                    }
                    else		{
                        $out.= '<option value="'.$obj->rowid.'">'.$obj->secteur.'</option>';
                    }
                    array_push($outarray, array('key'=>$obj->rowid, 'value'=>$obj->rowid, 'label'=>$obj->secteur));
                    $i++;
                    if (($i % 10) == 0) $out.="\n";
                }
            }
            $out.= '</select>'."\n";			
        }
        else
        {
            dol_print_error($this->db);
        }

        $result=array('select_secteur'=>$num);

        if ($outputmode) return $outarray;
        return $out;
		
	} //select_secteur;
/**
     *  Output html form to select a priorite in the list of existing priorite
     *
     *	@param	string	$selected       Preselected type
     *	@param  string	$htmlname       Name of field in form
     *  @param  int		$ident			rowid du dossier 
     *  @param  string	$NomObjColor	nom du Element recevant la couleur du statut 	 
     *  @param  string	$filter         optional filters criteras (example: 's.rowid <> x')
     *	@param	int		$showempty		Add an empty field
     * 	@param	int		$showtype		Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo		Force to use combo box
     *  @param	array	$events			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *  @param	string	$filterkey		Filter on key value
     *  @param	int		$outputmode		0=HTML select string, 1=Array
     *  @param	int		$limit			Limit number of answers
	 *  @param	string	$action			'saisie' permet de lancer l'évènement OnChange
	 *  @param	string	$tailleboite	taille relative de la boite, au format 'style="width:100%"' 
	 *  @param	string	$showcolorstatus  pour montrer la couleur propre au statut
     * 	@return	string					HTML string with
     */
   	function select_priorite( $selected='',$htmlname='priorite',$ident, $NomObjColor = '', $filter='',$showempty=0, $showtype=0, $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0, $action='saisie', $tailleboite = 'style="width:100%"', $showcolorstatus='')
	{
	    global $conf,$user,$langs;
        $out=''; $num=0;
        $outarray=array();
			
		if (stripos ($_SERVER["PHP_SELF"], 'inscription.php') >0) {
			$flEnvSuivi = 'Insc';
		}
		elseif (stripos ($_SERVER["PHP_SELF"], 'location.php') >0) {
			$flEnvSuivi = 'Insc';
		}
		elseif (stripos ($_SERVER["PHP_SELF"], 'dossier.php' ) 	>0)	{	
			$flEnvSuivi = 'dossier';
		}
		elseif (stripos ($_SERVER["PHP_SELF"], 'tiers.php' ) 	>0)	{	
			$flEnvSuivi = 'tiers';
		}
		else 	{ 	
			$flEnvSuivi = 'general';
		}
		// On recherche la priorité à afficher
		$sql1 = "SELECT distinct rowid, label as priorite , color";
		$sql1.= " FROM ".MAIN_DB_PREFIX ."cglavt_c_priorite ";
		$sql1.= " WHERE entity IN (".getEntity('societe', 1).")";
		$sql1.= " AND rowid ='".$selected."'";		

		// On recherche les priorités
		$sql = "SELECT distinct rowid, label as priorite , color";
		$sql.= " FROM ".MAIN_DB_PREFIX ."cglavt_c_priorite ";
		$sql.= " WHERE entity IN (".getEntity('societe', 1).")";
		if ($filter) $sql.= " AND (".$filter.")";

        $sql.=$this->db->order("ordre","DESC");
        dol_syslog("::suivi_priorite ", LOG_DEBUG);

		// Récupération de la couleur de la propriété sélectionnée
		$resql1=$this->db->query($sql1);
		$obj1 = $this->db->fetch_object($resql1); 
		$colorselected = $obj1->color;
		if (!empty($showcolorstatus	)) $attributselected = 'style="background-color:#'.$colorselected.'"';	
		else $attributselected = '';					
			
			
		$out.='<script>
		var tabColorPr = [];';
		$resql=$this->db->query($sql);
		if ($resql)        {
			$num = $this->db->num_rows($resql);
			$i=0;
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);
				$out.='tabColorPr ['.$obj->rowid.'] = "'.$obj->color.'";';	
				$i++;
			}
		}
		$out.='</script>';
		$out .= '<script>
				var PremClick=0;
				
				function MajPriorites(idDos, nomObjId , Color, value) {					
					var oNodeList = document.getElementsByClassName("suivitiers".concat(idDos));

					if (oNodeList.length >0 ){
						for(var h = 0; h < oNodeList.length; h++){
							oNodeList[h].style.backgroundColor=Color;
							oNodeList[h].value=value;							
						}
					}
				};
				function MajPriorite(o, id, nomObjColor) {';
				 $out .= 'val = o.value;
				if (id > -1  && val != -1) { 	
					url="'.DOL_URL_ROOT.'/custom/CahierSuivi/suivi_client/ReqMajDos.php?id=".concat(id).concat ("&priorite=").concat(val);
					var	Retour = creerobjet(url); 						
					ColorRetour=("#").concat(tabColorPr[val]);
				}
				else { 
					url="'.DOL_URL_ROOT.'/custom/CahierSuivi/suivi_client/ReqColorPriorite.php?ID=".concat(val);
					var	Retour = creerobjet(url);
					ColorRetour=("#").concat(Retour);
					if (val == -1) {ColorRetour="#ffffff"};
				};					';
				if ( $flEnvSuivi == 'tiers') 
						$out .= '	MajPriorites(id, nomObjColor, ColorRetour, val);';
				else 
						$out .= '	document.getElementById(nomObjColor).style.backgroundColor=ColorRetour;';

				if ($flEnvSuivi == 'Insc' ) //  on colore la cellule, dans Inscription/location/resa, on colore la valeur affichée du select
						$out .= 'PremClick=1';
				elseif ($flEnvSuivi == 'dossier') // Dans Suivi dossier, on colore le titre
						$out .= '	document.getElementById("priorite").value=val;';								
										
				$out .= '};	</script>';	
				
		$out .= '<script> function SelectionBlanc(o,id) {';
				if ($flEnvSuivi == 'Insc') {
					$out .= 'if (PremClick==0) document.getElementById("priorite".concat(id)).style.backgroundColor="#ffffff";
						else PremClick=0;';
				};
				$out .= '} </script>';

		unset($resql);
        $resql=$this->db->query($sql);
        if ($resql)        {
           /* if ($conf->use_javascript_ajax && $conf->global->COMPANY_USE_SEARCH_TO_SELECT && ! $forcecombo)
            {
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
            	$out.= ajax_combobox($htmlname, $events, $conf->global->COMPANY_USE_SEARCH_TO_SELECT);
            }
			*/
			if ($ident == -1) $ident=0;
            // Construct $out and $outarray
            //$out.= '<select id="'.$htmlname.$ident.'" class="flat" name="'.$htmlname.$ident.'"  style="width:100%"';
			if ($flEnvSuivi == 'Insc' or $flEnvSuivi == 'general' or $flEnvSuivi == 'tiers') 
					$whtmlname = $htmlname.$ident;			
			else 
					$whtmlname = $htmlname;
			//$attributselected ="";
            $out.= '<select id="'.$whtmlname.'" class="flat suivitiers" name="'.$whtmlname.'"  '.$tailleboite. ' '.$attributselected;
			if (empty($ident)) $arg = "this,0,'".$NomObjColor."'";
			else $arg = 'this,'.$ident.",'".$NomObjColor."'";

			if ($action == 'saisie') $out .= 'onfocus="this.defaultValue = this.value" onclick="SelectionBlanc(this,'.$ident.')" onchange="MajPriorite('.$arg.')"';
			$out .='>'."\n";
            if ($showempty) $out.= '<option value="-1"></option>'."\n";



			$num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)            {
                while ($i < $num)                {
                    $obj = $this->db->fetch_object($resql); 
					if (!empty($showcolorstatus	)) $attribut = 'style="color:#'.$obj->color.'"';	
					else $attribut = '';					
                    if ($selected > 0 && $selected == $obj->rowid)     {
                        $out.= '<option value="'.$obj->rowid.'" selected="selected" '.$attribut.'>'.$obj->priorite.'</option>';
                    }
                    else		{
                        $out.= '<option value="'.$obj->rowid.'" '.$attribut.'>'.$obj->priorite.'</option>';
                    }
                    array_push($outarray, array('key'=>$obj->rowid, 'value'=>$obj->rowid, 'label'=>$obj->priorite));
                    $i++;
                    if (($i % 10) == 0) $out.="\n";
                }
            }
             $out.= '</select>'	;	

  //  dol_syslog("::suivi_priorite ".$out, LOG_DEBUG);
        }
        else
        {
            dol_print_error($this->db);
        }
        $result=array('priorite'=>$num);
        if ($outputmode) return $outarray;
        return $out;
		
	} //select_priorite;
//	
	function select_user($selected='',$htmlname='socid',$showempty=0, $forcecombo=0)
    {
        global $conf,$user,$langs;

        $out='';

        // On recherche les societes

		$sql = "SELECT distinct rowid, lastname, firstname ";
		$sql.= " FROM ".MAIN_DB_PREFIX."user ";
		$sql.= " WHERE entity  in (1 , 0) ";
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $out.= '<select id="'.$htmlname.'" class="flat" name="'.$htmlname.'" style="width:100%">';
            if ($showempty) $out.= '<option value="-1"></option>';
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)
            {
                while ($i < $num)
                {
                    $obj = $this->db->fetch_object($resql);
                    $label=$obj->firstname . ' '. $obj->lastname;
                    if ($selected > 0 && $selected == $obj->rowid)
                    {
                        $out.= '<option value="'.$obj->rowid.'" selected="selected" >'.$label.'</option>';
                    }
                    else
                    {
                        $out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
                    }
                    $i++;
                }
            }
            $out.= '</select>';
        }
        else
        {
            dol_print_error($this->db);
        }

        return $out;
    }/* select_user */
 	 /**
     *  Code HTML pour selection d'un dossier
     *
	 *Pour trouver la gestion des dossiers dans une javacombo
	 *
     *	@param	string	$selected       Preselected type
     *	@param  string	$htmlname       Name of field in form
     *	@param  string	$valuenv        Valeur du nouveau dossier
     *	@param  string	$htmlnvname     Name of champ dans le formulaire, recevant le nouveau tiers à creer (si vide rien)
     *	@param  string	$combinaison    '' pour une simple boite de sélection
	 *									L pour affichage de l'icone de lien vers list typeliste=dossier
	 *									R pour affichage sur la ligne de saisie des infos du dossier 
     *  @param  string	$filter         optional filters criteras (example: 's.rowid <> x')
     *	@param	int		$showempty		Add an empty field
     * 	@param	int		$showtype		Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo		Force to use combo box
     *  @param	array	$events			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *  @param	string	$filterkey		Filter on key value
     *  @param	int		$outputmode		0=HTML select string, 1=Array
     *  @param	int		$limit			Limit number of answers
     * 	@return	string					HTML string with
     */
	function select_dossier($selected='',$htmlname='iddossier', $valuenv = "", $htmlnvname='', $combinaison = '' , $filter='',$showempty=0, $showtype=0, $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0)
	{	
		global $user, $langs, $conf;
		global $socid, $TypeListe;
		$out=''; 	
        $outarray=array();
	
        // On recherche les dossier
        $sql = "SELECT distinct rowid, s.libelle as dossier";
        $sql.= " FROM ".MAIN_DB_PREFIX ."cglavt_dossier as s";	
        $sql.= " WHERE s.entity IN (".getEntity('societe', 1).")";
        if ($filter) $sql.= " AND (".$filter.")";
		
        $sql.=$this->db->order("dossier","ASC");
		if ($limit > 0) $sql.=$this->db->plimit($limit);

        //dol_syslog(get_class($this)."::select_dossier sql=".$sql);
        $resql=$this->db->query($sql);	
		$num=0;

        if ($resql)
        {
          if ($conf->use_javascript_ajax && $conf->global->COMPANY_USE_SEARCH_TO_SELECT && ! $forcecombo)         {
				include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
            	$out.= ajax_combobox($htmlname, $events, $conf->global->COMPANY_USE_SEARCH_TO_SELECT);
			}									
            $num = $this->db->num_rows($resql);
            $i = 0;		
		  
			$out.= "\n".'<select id="'.$htmlname.'" class="flat" name="'.$htmlname.'" style="width:100%"';
			if ($combinaison == 'R') if (!empty($htmlnvname)) 	$out .=  ' onchange="AffInfoDos(this)" ';
			$out .= '>';
			if ($showempty) $out.= ' <option value="-1"></option>';
			while ($i < $num)                { 
				$obj = $this->db->fetch_object($resql);                    
				if ($selected > 0 && $selected == $obj->rowid)     {
					$out.= '<option value="'.$obj->rowid.'" selected="selected" >'.$obj->dossier.'</option>';
				}
				else		
					$out.= '<option  value="'.$obj->rowid.'" >'.$obj->dossier.'</option>';
				array_push($outarray, array('key'=>$obj->rowid, 'value'=>$obj->rowid, 'label'=>$obj->dossier));
				$i++;
				if (($i % 10) == 0) $out.="\n";
			}

            $out.= '</select>'."\n";
			if ($combinaison == 'L') {	
					$out.= '<script> '."\n";
					$out.= 'function LienDos(o) '; 
					$out.= '{ '; 
					$out .= '  val = document.getElementById("'.$htmlname.'").value;';  
						$out.= '	if (val > -1) { ';  
						$out .=' 		url="list_dossier.php?typeliste=dossier&search_dossier=".concat(val).concat("&socid="'.$socid.');';
						$out .=' 		window.location.href=url;';
					$out.= '	} ';  
					$out.= '}'  ."\n";
					$out.= '</script>';	
					$out .=  '<a id="liendossier"  onclick="LienDos(this)" >' ;
					$out .= '<img border = 0 title="Choisir" src="../../../theme/eldy/img/object_company.png" alt="'.$langs->trans("Dossier").'"><br>';
				}
			elseif ($combinaison == 'R')  {
				if (!empty($htmlnvname)) {
					$out.= '<script>';	
						$out.= 'function EffaceDos() {';			
							// TypeDOSSIER
							//$out.= ' 		document.getElementById("div_typedossier").style.visibility="hidden"; ';
							//$out.= ' 		document.getElementById("arg_typedossier").style.visibility="visible";';	
			
							// NOUVEAU DOSSIER
								$out.= ' if  (document.getElementById("'.$htmlnvname.'").value=="nouveau dossier"  || (document.getElementById("'.$htmlname.'").value=="-1"  && document.getElementById("'.$htmlnvname.'").value=="")) {';
								// cas où il y a eu une saisie dans htmlname  ou tout est vide
								$out.= ' 	 document.getElementById("'.$htmlnvname.'").value=""; '; 
								$out.= '	 document.getElementById("'.$htmlnvname.'").style.color="#000000"; '; 
								$out.= '	 document.getElementById("'.$htmlname.'").value="-1"; ';
// si arg_id_dossier a une valeur, alors supprimer valeur et le statut						
							// PRIORITE
							//$out .= ' 		document.getElementById("arg_priorite").value = "-1";';
							//$out .= '		document.getElementById("td0").style.backgroundColor="#ffffff";';
							
							// NOMBRE
							//$out .= ' 		document.getElementById("arg_nb").value = "";';
							
							//DATE
							if ($TypeListe <> "generale") $out .= ' 		document.getElementById("div_datec").innerHTML = "'.$langs->trans("CS_TitreSaisie").'";';
							//USER
							$out.= ' 		document.getElementById("div_user").style.visibility="hidden";';
							$out.= ' 		document.getElementById("arg_createur").style.visibility="visible"; ';
							$out.= ' 		document.getElementById("arg_createur").value="'.$user->id.'"; ';						

							// SECTEUR
							$out.= ' 		if (document.getElementById("div_secteur")) document.getElementById("div_secteur").style.visibility="hidden";';
							$out.= ' 		if (document.getElementById("arg_secteur")) document.getElementById("arg_secteur").style.visibility="visible"; ';
							//$out.= ' 		if (document.getElementById("arg_secteur")) document.getElementById("arg_secteur").value="-1"; ';		
							
							// ORIGINE
							//$out.= ' 		document.getElementById("arg_origine").value =	-1; ';						
							
							// ACTION
							//$out .= ' 		document.getElementById("arg_action").value = "";';	
				
							// Dossier sans tiers				
							//$out.= ' 		document.getElementById("arg_dossanstiers").style.visibility="visible"; ';
							//$out.= ' 		document.getElementById("span_dossanstiers").style.visibility="visible"; ';
							////$out.= ' 		document.getElementById("span_dossanstiers").innerHTML="Dossier sans tiers"; ';
							
							$out.= ' }'."\n";
							$out .= '}';
						$out.= '</script>';	
					
				}

					// Routines d'animmations des zones de saisies suivant besoin
					$out.= '<script> '."\n";	 
						// Affiche le secteur valorisé
					$out.= 'function AffSectVal(o) '; 
					$out.= '{ ';
					$out.= '} ';	  	  
						// Affiche la boite de sélection du  secteur 
					$out.= 'function AffSectSelect(o) '; 
					$out.= '{ ';
					$out.= '} ';	  	  
						// Efface valeur et boite de sélection du  secteur 	  
					$out.= 'function EffSect(o) '; 
					$out.= '{ ';
					$out.= '} ';	  	  			
						// Affiche la boite de sélection et la couleur du statut 
					$out.= 'function AffPrio(o) '; 
					$out.= '{ ';
					$out.= '} ';	  	  
						// Efface la boite de sélection et la couleur du statut 	  
					$out.= 'function EffPrio(o) '; 
					$out.= '{ ';
					$out.= '} ';
						// Affiche la boite de sélection et la couleur du statut -1 	  
					$out.= 'function AffSsStatut(o) '; 
					$out.= '{ ';
					$out.= '} ';	  	  	  	  
						// Efface la boite de Dossier sans tiers	  
					$out.= 'function EffBtSsTiers(o) '; 
					$out.= '{ ';
					$out.= '} ';	  	  				
						// Affiche la boite de Dossier sans tiers 
					$out.= 'function AffBtSsTiers(o) '; 
					$out.= '{ ';
					$out.= '} ';	  	  
						// Efface la boite de Dossier sans tiers	  
					$out.= 'function EffBtSsTiers(o) '; 
					$out.= '{ ';
					$out.= '} ';  	  				
						// Affiche Nouveau Dossier 
					$out.= 'function AffNvDoss(o) '; 
					$out.= '{ ';
					$out.= '} ';	  	  
						// Efface Nouveau Dossier	  
					$out.= 'function EffNvDoss(o) '; 
					$out.= '{ ';
					$out.= '} ';	  	  
						// Efface Nouveau Dossier	  
					$out.= 'function EffChoixDoss(o) '; 
					$out.= '{ ';
					$out.= '} ';	  	  
					$out.= '</script> '."\n";						
					
					$out.= '<script> '."\n";	  
					$out.= 'function AffInfoDos(o) '; 
					$out.= '{ '; 	
					
					$out .= '  val = document.getElementById("'.$htmlname.'").value;'; 
					$out.= '	if (val > -1) { ';
 					// Interrogation ifon dossier
					$out .=' 		url="ReqInfoDossier.php?ID=".concat(val);'; 
					$out.= "		var	Retour = creerobjet(url); ";
					$out .= '   	var tableau = Retour.split("?",17);'; 
								
					// PRIORITE
					$out .= ' 		var idpriorite = tableau[13];';
					$out .= ' 		document.getElementById("arg_priorite").value = tableau[2];';
					$out .= '		document.getElementById("td0").style.backgroundColor=idpriorite;';
		
					// NOMBRE
					//$out .= ' 		document.getElementById("arg_nb").value = tableau[3];';
					
					//DATE
					if ($TypeListe <> "generale") $out .= ' 		document.getElementById("div_datec").innerHTML = tableau[4];';
					
					// SECTEUR
					$out.= ' 		if (document.getElementById("arg_secteur")) document.getElementById("arg_secteur").style.visibility="hidden";';
					$out.= ' 		if (document.getElementById("div_secteur")) document.getElementById("div_secteur").style.visibility="visible"; ';
					$out.= ' 		if (document.getElementById("div_secteur")) document.getElementById("div_secteur").innerHTML=tableau[1]; ';
					
					// NOUVEAU DOSSIER					
					$out.= '		document.getElementById("'.$htmlnvname.'").style.color="#C0C0C0"; ';  
					$out.= '		document.getElementById("'.$htmlnvname.'").value="nouveau dossier"; ';
	
						
					// Dossier sans tiers				
					$out.= ' 		document.getElementById("arg_dossanstiers").style.visibility="hidden"; ';
					$out.= ' 		document.getElementById("span_dossanstiers").style.visibility="hidden"; ';	
							
					$out.= '	} '; 
					$out.= '	else  EffaceDos(); 	'; 
					
					$out.= '} '; 	
					$out.= '</script> '."\n";
				
			}	
			if (!empty($htmlnvname)) {				
				if (empty($valuenv)) {
					$valuenv = 'nouveau dossier'; 
					$style='color:#C0C0C0;'; 
				} 
				else $style='color:#000000;'; 
				//$out.= '<br><input id="'.$htmlnvname.'" type="text" name="'.$htmlnvname.'" value="'.$valuenv.'"  style="'.$style.'" onclick="EffaceDos();" onmouseleave="SupDos(this)" >'; 
				$out.= '<br><input id="'.$htmlnvname.'" type="text" name="'.$htmlnvname.'" value="'.$valuenv.'"  style="'.$style.'" onclick="EffaceDos();" >'; 				
				// document.write("<p></p>");
			}	
		}
		return($out);
	}//select_dossier
	
  /**
     *  Output html form to select a third party
	 *
	 * Selection Client dans le bandeau de selection
     *
     *	@param	string	$selected       Preselected type
     *	@param  string	$htmlname       Name of field in form 	 
     *	@param  string	$valuenv        Valeur du nouveau client
     *	@param  string	$htmlnvname     Name of champ dans le formulaire, recevant le nouveau tiers à creer (si vide rien)
	 *	@param  char	$combinaison    '' pour une simple boite de sélection
	 *									L pour affichage de l'icone de lien vers list typeliste=dossier
	 *									R pour affichage sur la ligne de saisie des infos du dossier 
	 *	@param	string	type			Inter pour signaler un Interlocuteur, Tiers pour signaler un Tiers
     *  @param  string	$filter         optional filters criteras (example: 's.rowid <> x')
     *	@param	int		$showempty		Add an empty field
     * 	@param	int		$showtype		Show third party type in combolist (customer, prospect or supplier)
     * 	@param	int		$forcecombo		Force to use combo box
     *  @param	array	$events			Event options. Example: array(array('method'=>'getContacts', 'url'=>dol_buildpath('/core/ajax/contacts.php',1), 'htmlname'=>'contactid', 'params'=>array('add-customer-contact'=>'disabled')))
     *  @param	string	$filterkey		Filter on key value
     *  @param	int		$outputmode		0=HTML select string, 1=Array
     *  @param	int		$limit			Limit number of answers
     * 	@return	string					HTML string with
     */	
//
//
 //L'évenement onblur permet de remettre  le champ nouveau tiers avec le libellé si on clique en dehors
//
//	 
	// POur une interrogation dynamique des liste longue : ajax_autocompleter - copy de selectCompaniesForNewContact
	// On ajoutera ensuite l'affichage des tel_mail, la saisie  nouveau tiers et le nettoyage des zones
	function select_client($selected='',$nomselected, $htmlname='socid',$valuenv = "", $htmlnvname='', $combinaison = '', $type='',$filter='',$showempty=0, $showtype=0, $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0, $idechange)
	{		
		global $langs, $conf;
		global $price_level, $status, $finished, $TypeListe;
	
					// Use Ajax search
			$minLength = (is_numeric($conf->global->COMPANY_USE_SEARCH_TO_SELECT)?$conf->global->COMPANY_USE_SEARCH_TO_SELECT:2);
			$out ='';
			$socid=0; $name='';
			if ($selected > 0)
			{
				$tmpthirdparty=new Societe($this->db);
				$result = $tmpthirdparty->fetch($selected);
				if ($result > 0)
				{
					$socid = $selected;
					$name = $tmpthirdparty->name;
				}
			}

			$out .= "\n".'<!-- Input text for third party with Ajax.Autocompleter (selectCompaniesForNewContact) -->'."\n";
			$out .= '<input type="text" size="25" id="search_'.$htmlname.'" name="search_'.$htmlname.'" value="'.$name.'" />';
		if ('script' == 'script') {
			//$url = DOL_MAIN_URL_ROOT.'/custom/CahierSuivi/suivi_client/ajaxclient.php';
			$url = DOL_MAIN_URL_ROOT.'/custom/cglavt/ajaxclient.php';
			$out .= '<script type="text/javascript">
		$(document).ready(function() {
					var autoselect = 0;
					var options = [];
					$("#arg_idtiersLig").change(function(origine) {
							var obj = [{"method":"getContacts","url":"'.$url.'","htmlname":"contactid","params":{"add-customer-contact":"disabled"}}];
														
								$.each(obj, function(key,values) {
									if (values.method.length) {
										runJsCodeForEventarg_idtiersLig(values, origine);
									}
								});							 
						});				
								    
					// Function used to execute events when search_htmlname change
					// Interrogatiion pour telephone
					function runJsCodeForEventarg_idtiersLig(obj, origine) {
						if (origine != "") {
							var id = $("#arg_idtiersLig").val();
							var method = obj.method;
							var url = obj.url;
							var htmlname = obj.htmlname;
							var response = "";
							$.getJSON("'.$url.'",
								{
									action: method,
									id: id,
									htmlname: htmlname
								},
								function(response) {
									if (response != null) {
											var num = response.num;	
											$("#telmail").html(response.telmail);
											$("#telmail").css("visibility", "visible");
											$("#arg_nvtiersLig").html("Nouveau tiers");
										};
									$("select#" + htmlname).html(response.value);  
								});
						}
										
					};					
					 
					$("input#search_'.$htmlname.'").on("click", function() {
									if ("'.$htmlnvname.'".length >0) {
										$("#'.$htmlnvname.'").val("nouveau tiers");
										$("#'.$htmlnvname.'").css("color", "#C0C0C0"); 
									
										document.getElementById("spantiersmail").style.visibility = "hidden";
										document.getElementById("tierstel").style.visibility= "hidden";
										document.getElementById("tierstel").value="";
										document.getElementById("spantierstel").style.visibility = "hidden";
										document.getElementById("tiersmail").value=""; 
										document.getElementById("tiersmail").style.visibility = "hidden";
									};										
								});
					
				});
			</script>'."\n";
			$wfct = New CglFonctionDolibarr ($this->db);
//			print $wfct->ajax_autocompleter(($socid?$socid:-1), $htmlname, DOL_MAIN_URL_ROOT.'/custom/CahierSuivi/suivi_client/ajaxcompanies.php', '', $minLength, 0);
			$out .= $wfct->ajax_autocompleter(($socid?$socid:-1), $htmlname, DOL_MAIN_URL_ROOT.'/custom/cglavt/ajaxcompanies.php', '', $minLength, 0);
			unset($wfct);	

		if (!empty($htmlnvname)) { 
			if (empty($valuenv)) {
				$valuenv = 'nouveau tiers'; 
				$style='color:#C0C0C0;'; 
			} 
			else $style='color:#000000;'; 
			$out.= '<br>'."\n".'<input id="'.$htmlnvname.'" type="text" name="'.$htmlnvname.'" value="'.$valuenv.'"  style="'.$style.'" onclick="EffaceTiers'.$htmlname.'(this)" >'; 
							

				$out.= '<script> '."\n";	
				$out.= ' function EffaceTiers'.$htmlname.'(o) {'; 
				$out.= ' 	if ( o.value == "'.$valuenv.'") {';

				if ($TypeListe == 'dossier' or $TypeListe  == '4saisons' ) {
						if ($htmlname == 'arg_idtiersCart') {
							$out.= '	document.getElementById("arg_teltiers").value=""; 
										document.getElementById("spanteltiers").innerHTML=""; 
										document.getElementById("spantelsuptiers").innerHTML=""; 
										document.getElementById("arg_telsuptiers").value=""; 
										document.getElementById("arg_mailtiers").value=""; 
										document.getElementById("arg_mailsuptiers").value=""; 
								';
						}
						elseif ($htmlname == 'arg_idtiersLig') {
							$out .=	'	
								document.getElementById("telmail").innerHTML=""; 
								document.getElementById("telmail").style.visibility = "hidden";
								document.getElementById("spantiersmail").style.visibility = "visible";
								document.getElementById("tierstel").style.visibility= "visible";
								document.getElementById("tierstel").value="";
								document.getElementById("spantierstel").style.visibility = "visible";
								document.getElementById("tiersmail").value=""; 
								document.getElementById("tiersmail").style.visibility = "visible";';
//								document.getElementById("span_nomprenom").style.visibility = "visible";
//								document.getElementById("span_nomprenom").style.display = "block";
						}
						$out .=	'	document.getElementById("'.$htmlnvname.'").style.color="#000000";
								document.getElementById("'.$htmlname.'").value = "-1"; 
						';
				}
				else {
					$out.= '
						document.getElementById("'.$htmlnvname.'").style.color="#000000";
						document.getElementById("telmail").innerHTML=""; 
						document.getElementById("telmail").style.visibility = "hidden";
						document.getElementById("span_nomprenom").style.visibility = "visible";document.getElementById("span_nomprenom").style.display = "block";
						document.getElementById("lientiers").display="none";
						document.getElementById("spantierstel").style.visibility = "visible";
						document.getElementById("tierstel").style.visibility = "visible";
						document.getElementById("tierstel").value="";
						document.getElementById("spantiersmail").style.visibility = "visible";
						document.getElementById("tiersmail").value=""; 
						document.getElementById("tiersmail").style.visibility = "visible";
					';
				};
				$out .= '		document.getElementById("'.$htmlnvname.'").value=""; '; 
				$out .= '		o.value="";';
				$out .= '		document.getElementById("search_'.$htmlname.'").value="";'; 
				$out .= '			document.getElementById("'.$htmlname.'").value=""; '; 
				
				
				$out .= '}';
				$out .= '}'; // Fin fonction EffaceTiers.$htmlname
					
				$out.= '</script>';					
			}
			return $out;
		}
	}
	
	// Selection Tiers sur liste courte - liste dans laquelle on tape une lettre est on st positionner sur la première occurence
   function select_client_courte($selected='',$htmlname='socid',$valuenv = "", $htmlnvname='', $combinaison = '', $type='',$filter='',$showempty=0, $showtype=0, $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0, $idechange='')
    {
  		global $conf,$user,$langs, $socid, $TypeListe; 
		global  $DefLienTiers, $DefRechTiers, $DefEffTiers;
				
		if (empty($DefLienTiers)) $DefLienTiers=1;
		if (empty($DefRechTiers)) $DefRechTiers=1;
		if (empty($DefEffTiers)) $DefEffTiers=1;
		
		$out=''; $num=0;
        $outarray=array();
        // On recherche les societes

//        $sql = "SELECT s.rowid, concat(s.nom,case when  length(s.name_alias)> 0 then concat( ' -/- ',s.name_alias) else ' ' end ) as nom , s.client, s.fournisseur, s.code_client, s.code_fournisseur, s.town";
        $sql = "SELECT s.rowid, s.nom, s.client, s.fournisseur, s.code_client, s.code_fournisseur, s.town";
         $sql.= " FROM ".MAIN_DB_PREFIX ."societe as s";
        $sql.= " WHERE s.entity IN (".getEntity('societe', 1).")";
        if ($filter) $sql.= " AND (".$filter.")";		
		
        if (! empty($conf->global->COMPANY_HIDE_INACTIVE_IN_COMBOBOX)) $sql.= " AND s.status<>0 ";

        $sql.=$this->db->order("s.nom","ASC");
		if ($limit > 0) $sql.=$this->db->plimit($limit);
        dol_syslog(get_class($this)."::select_client_courte sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
			
        if ($resql)        {
			if ($conf->use_javascript_ajax && $conf->global->COMPANY_USE_SEARCH_TO_SELECT && ! $forcecombo)         {
				//include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
			//$wfct = New CglFonctionDolibarr ($this->db);
			//$out.= $wfct->ajax_combobox($htmlname, $events, $conf->global->COMPANY_USE_SEARCH_TO_SELECT ,1, '100% !important');
			//$out.= $wfct->ajax_combobox($htmlname, $events);
			$out.= ajax_combobox($htmlname, $events);
			//unset($wfct);	
			}
						
			// Construct $out and $outarray
			$out.= "\n".'<select id="'.$htmlname.'" name="'.$htmlname.'" style="width:100% !important"';
			//if (!empty($htmlnvname)) 	$out .=  ' onchange="EffNouvTiers(this)" ';
			$out .= '>';
            //if ($showempty) $out.= '<option value="-1">tous tiers</option>'."\n";
			if ($showempty) $out.= '<option value="-1"></option>'."\n";
			 $out.= '<option value="-999">sans Tiers</option>'."\n";
			
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)
            {
                while ($i < $num)    {
                    $obj = $this->db->fetch_object($resql);
                    
                    $label=$obj->nom;
					if (!empty($obj->town)) $label.= ' ('.$obj->town.')';
                    if ($showtype)                    {
                        if ($obj->client || $obj->fournisseur) $label.=' (';
                        if ($obj->client == 1 || $obj->client == 3) $label.=$langs->trans("Customer");
                        if ($obj->client == 2 || $obj->client == 3) $label.=($obj->client==3?', ':'').$langs->trans("Prospect");
                        if ($obj->fournisseur) $label.=($obj->client?', ':'').$langs->trans("Supplier");
                        if ($obj->client || $obj->fournisseur) $label.=')';
                    }
                    if ($selected > 0 && $selected == $obj->rowid)                    {
                        $out.= '<option value="'.$obj->rowid.'" selected="selected">'.$label.'</option>';
                    }
                    else					{
                        $out.= '<option value="'.$obj->rowid.'">'.$label.'</option>';
                    }
                    array_push($outarray, array('key'=>$obj->rowid, 'value'=>$obj->name, 'label'=>$obj->name));
                    $i++;
                    if (($i % 10) == 0) $out.="\n";
                }
            }
            $out.= '</select>'."\n"; 				
								
			if ($combinaison == 'L') {	
					$out .=  '<a id="lientiers"  onclick="LienTiers(this)" >' ;
					$out .= '<img border = 0 title="Choisir" src="'.DOL_DOCUMENT_ROOT.'/theme/eldy/img/object_company.png" alt="'.$langs->trans("Tiers").'"></a><br>';
				
					if ($DefLienTiers == 1 ) {
						$DefLienTiers = 2;
					$out.= '<script> '."\n";			  
					$out.= 'function LienTiers(o, type) '; 
					$out.= '{ '; 
						$out .= '  val = document.getElementById("'.$htmlname.'").value;';  
						$out.= '	if (val > -1) { ';  
						$out .=' 		url="list_tiers.php?typeliste=tiers&search_tiers=".concat(val);';
						$out .=' 		window.location.href=url;';
						
						$out.= '	}  '; 	
					$out.= '}  '; 	
					$out.= '</script> '."\n";
				}
			}
			elseif ($combinaison == 'R') {
				//$out.=  '<input id="cherchetiers" class="button"  value="" onclick="RechTelMailTiers(this,\''.$type.'\');" style="font-size:5px;border-radius:0.9em;padding-left:0px;padding-right:0px;marge-right:0px;">'."\n";
				//$out.=  '<a  onclick="RechTelMailTiers(this);">';
				$out .= '<img  src="/dolibarr/theme/eldy/img/edit.png"  onclick="RechTelMailTiers(this);"';
				$out .='alt="'.$langs->trans("CS_AidTelMailTiers").'" ';
				$out .= 'title="'.$langs->trans("CS_AidTelMailTiers").'" class="hideonsmartphone" border="0"/>';
				
				if ($DefRechTiers == 1 ) {
						$DefRechTiers = 2;
						$out.= '<script> '."\n";	
					//$out.= 'function RechTelMailTiers(o, type) '; 
					$out.= 'function RechTelMailTiers(o) '; 
					$out.= '{ '; 
						$out .= '  val = document.getElementById("'.$htmlname.'").value;';  
						$out.= '	if (val > -1) { ';	
						$out .=' 		url="'.DOL_MAIN_URL_ROOT.'/custom/CahierSuivi/suivi_client/ReqTelMail.php?ID=".concat(val);';	
						$out.= "		Retour = creerobjet(url); "; 	
						$out .= '   	var tableau = Retour.split("?",4);';
						$out.= ' 		document.getElementById("telmail").innerHTML=tableau[0]; '; 
						if ($TypeListe <> 'generale') {
							$out .= '		id_tiers = document.getElementById("arg_id_tiers").value;';
							$out .= 'if (id_tiers != val ) { ';
							$out .= ' 		document.getElementById("spantiersmail").style.visibility = "visible";';
							$out .= ' 		document.getElementById("tierstel").style.visibility = "visible";';
							$out .= ' 		document.getElementById("tierstel").value = tableau[1];';
							$out .= ' 		document.getElementById("spantierstel").style.visibility = "visible";';
							$out.= '		document.getElementById("tiersmail").value = tableau[2]; '; 
							$out .= ' 		document.getElementById("tiersmail").style.visibility = "visible";';
							$out .= ' 		document.getElementById("span_nomprenom").style.visibility = "hidden";';
							$out.= '	}  '; 
						}
						
						else {						
							$out .= ' 		document.getElementById("spantierstel").style.visibility = "visible";';
							$out .= ' 		document.getElementById("tierstel").style.visibility = "visible";';
							$out .= ' 		document.getElementById("tierstel").value = tableau[1];';
							$out .= ' 		document.getElementById("spantiersmail").style.visibility = "visible";';
							$out.= '		document.getElementById("tiersmail").value = tableau[2]; '; 
							$out .= ' 		document.getElementById("tiersmail").style.visibility = "visible";';
							$out .= ' 		document.getElementById("span_nomprenom").style.visibility = "hidden";';
						}
						$out.= '	}  '; 
						$out.= '	else  { '; 
						//$out.= '		alert("'.$langs->trans("CS_LibTelMailSanTiers").'"); ';	
						$out .= ' 		document.getElementById("spantierstel").style.visibility = "hidden";';
						$out .= ' 		document.getElementById("spantiersmail").style.visibility = "hidden";';
						$out .= ' 		document.getElementById("tierstel").style.visibility = "hidden";';	
						$out .= ' 		document.getElementById("tiersmail").style.visibility = "hidden";';
						$out.= ' 		if (document.getElementById("telmail")) document.getElementById("telmail").innerHTML=""; ';
						$out .= ' 		document.getElementById("span_nomprenom").style.visibility = "visible";'; 
						$out.= '	}  '; 
					$out.= '}  '; 	
					
					$out.= ' function EffNouvTiers(o) {';	
					$out .= '	document.getElementById("'.$htmlnvname.'").style.color="#C0C0C0"; ';
					$out .= ' 	document.getElementById("spantierstel").style.visibility = "hidden";';	
					$out .= ' 	document.getElementById("tierstel").style.visibility = "hidden";';
					$out.= '  	document.getElementById("tierstel").style.visibility = "hidden";';
					$out .= ' 	document.getElementById("spantiersmail").style.visibility = "hidden";';
					$out.= '  	document.getElementById("tiersmail").style.visibility = "hidden";';
					$out .= ' 	document.getElementById("tiersmail").style.visibility = "hidden";';
					$out .= '	document.getElementById("'.$htmlnvname.'").value="nouveau tiers"; ';
					$out .= '}';
					$out .= '}';
					
					$out.= '</script> '."\n";
				}
				
			}
				
			if (!empty($htmlnvname)) { 
				if (empty($valuenv)) {
					$valuenv = 'nouveau tiers'; 
					$style='color="#C0C0C0"'; 
				} 
				else $style='color="#000000"'; 
				//$out.= '<br>'."\n".'<input id="'.$htmlnvname.'" type="text" name="'.$htmlnvname.'" value="'.$valuenv.'"  style="'.$style.'" onclick="EffaceTiers(this)"  onmouseleave="SupTiersTel(this)">'; 
				$out.= '<br>'."\n".'<input id="'.$htmlnvname.'" type="text" name="'.$htmlnvname.'" value="'.$valuenv.'"  style="'.$style.'" onclick="EffaceTiers1(this)" >'; 
								
				if ($DefEffTiers == 1 ) {
						$DefEffTiers = 2;
						$out.= '<script> '."\n";	
					$out.= ' function EffaceTiers1(o) {';
					//$out.= ' 	if ( document.getElementById("'.$htmlnvname.'").value == "nouveau tiers")';
					$out.= ' 	if ( o.value == "nouveau tiers") {';				
					$out .= '			document.getElementById("'.$htmlnvname.'").value=""; '; 					
					$out.= ' 	document.getElementById("'.$htmlnvname.'").style.color="#000000"; '; 
					$out.= '	if (document.getElementById("telmail")) document.getElementById("telmail").innerHTML=""; '; 
					$out.= '	if (document.getElementById("inputautocomplete'.$htmlname.'")) document.getElementById("inputautocomplete'.$htmlname.'").value=""; '; 
					$out.= '	if (document.getElementById("'.$htmlname.'")) document.getElementById("'.$htmlname.'").value="-1"; '; 
					$out.= ' 	document.getElementById("lientiers").display="none"; ';
					$out .= ' 	document.getElementById("spantierstel").style.visibility = "visible";';			
					$out .= ' 	document.getElementById("tierstel").style.visibility = "visible";';
					$out.= '  	document.getElementById("tierstel").value=""; '; 
					$out .= ' 	document.getElementById("spantiersmail").style.visibility = "visible";';
					$out.= '  	document.getElementById("tiersmail").value=""; '; 
					$out .= ' 	document.getElementById("tiersmail").style.visibility = "visible";';
					$out .= ' 		document.getElementById("span_nomprenom").style.visibility = "visible";';
					$out .= '}';
					$out .= '}';
					$out.= '</script>';	
					$out.= '<script> '."\n";	
					$out.= ' function SupTiersTel(o) {';	
					$out.= ' val = document.getElementById("'.$htmlnvname.'").value;';	
					$out.= ' if (val == "") {';
					$out .= '	document.getElementById("'.$htmlnvname.'").style.'.$style.'; ';
					$out .= ' 	document.getElementById("spantierstel").style.visibility = "hidden";';	
					$out .= ' 	document.getElementById("tierstel").style.visibility = "hidden";';
					$out.= '  	document.getElementById("tierstel").style.visibility = "hidden";';
					$out .= ' 	document.getElementById("spantiersmail").style.visibility = "hidden";';
					$out.= '  	document.getElementById("tiersmail").style.visibility = "hidden";';
					$out .= ' 	document.getElementById("tiersmail").style.visibility = "hidden";';
					$out .= '	document.getElementById("'.$htmlnvname.'").value="nouveau tiers"; ';
					$out .= '}';
					$out .= '}';
					
					$out.= '</script>';					
				}
			}
						
		}
        else
        {
            dol_print_error($this->db);
        }

        $this->result=array('nbofthirdparties'=>$num);

        if ($outputmode) return $outarray;
        return $out;
    } // select_client_courte
	

	function select_typedossier($selected='',$htmlname='priorite',$filter='',$showempty=0, $showtype=0, $forcecombo=0, $events=array(), $filterkey='', $outputmode=0, $limit=0)
	{
	    global $conf,$user,$langs;

        $out=''; $num=0;
        $outarray=array();

		// On recherche les societes
		$sql = "SELECT distinct rowid, label as typedossier ";
		$sql.= " FROM ".MAIN_DB_PREFIX ."cglavt_c_typedossier ";
		$sql.= " WHERE entity IN (".getEntity('societe', 1).")";
		if ($filter) $sql.= " AND (".$filter.")";

        $sql.=$this->db->order("typedossier","ASC");
        dol_syslog("::suivi_priorite sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)        {
            // Construct $out and $outarray
            $out.= '<select id="'.$htmlname.'" class="flat" name="'.$htmlname.'" style="width:100%">'."\n";
            if ($showempty) $out.= '<option value="-1"></option>'."\n";
            $num = $this->db->num_rows($resql);
            $i = 0;
            if ($num)            {
                while ($i < $num)                {
                    $obj = $this->db->fetch_object($resql);                 
                    if ($selected > 0 && $selected == $obj->rowid)     {
                        $out.= '<option value="'.$obj->rowid.'" selected="selected">'.$obj->typedossier.'</option>';
                    }
                    else		{
                        $out.= '<option value="'.$obj->rowid.'">'.$obj->typedossier.'</option>';
                    }
                    array_push($outarray, array('key'=>$obj->rowid, 'value'=>$obj->rowid, 'label'=>$obj->typedossier));
                    $i++;
                    if (($i % 10) == 0) $out.="\n";
                }
            }
            $out.= '</select>'."\n";		
        }
        else        {
            dol_print_error($this->db);
        }
        $result=array('typedossier'=>$num);
        if ($outputmode) return $outarray;
        return $out;
		
	} //select_typedossier;
	   /**
	 *	Non utilisé - pourra être supprimé aprs V15
	 *	Return list of input reason (events that triggered an object creation, like after sending an emailing, making an advert, ...)
	 *  List found into table c_input_reason loaded by loadCacheInputReason
     *
     *  @param	int		$selected        Id or code of type origin to select by default
     *  @param  string	$htmlname        Nom de la zone select
     *  @param  string	$exclude         To exclude a code value (Example: SRC_PROP)
     *	@param	int		$addempty		 Add an empty entry
	 *  @param  string	$morecss		 Add more css to the HTML select component
	 *  @param	int		$notooltip		 Do not show the tooltip for admin
     *	@return	void
	 *
	 * repris de core/class/htlm.form.Class
     */
    function selectInputReason1($selected='',$htmlname='demandreasonid',$exclude='',$addempty=0, $morecss = '', $notooltip = 0)
    {
        global $langs,$user;

        $this->loadCacheInputReason();

        print '<select class="flat" name="'.$htmlname.'" id="'.$htmlname.'" style="width:80%">';
        if ($addempty) print '<option value="0"'.(empty($selected)?' selected="selected"':'').'></option>';
        if (empty($this->cache_demand_reason)) 
			foreach($this->cache_demand_reason as $id => $arraydemandreason)
			{
				if ($arraydemandreason['code']==$exclude) continue;

				if ($selected && ($selected == $arraydemandreason['id'] || $selected == $arraydemandreason['code']))
				{
					print '<option value="'.$arraydemandreason['id'].'" selected="selected">';
				}
				else
				{
					print '<option value="'.$arraydemandreason['id'].'">';
				}
				print $arraydemandreason['label'];
				print '</option>';
			}//Foreach
        print '</select>';
        if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup"),1);
    }

    /**
     *    	Return a link on thirdparty (with picto)
     *
     *		@param	int	$withpicto	Add picto into link (0=No picto, 1=Include picto with link, 2=Picto only)
     *		@param	string	$option		Target of link ('', 'customer', 'prospect', 'supplier')
     *		@param	int	$maxlen		Max length of text
     *		@param	int	$id		Identifiant de l'objet
     *		@return	string				String with URL
     */
//			$ligndes .= $this->getNomUrl("object_company.png", 'Tiers',0,'',$fk_soc	);

     function getNomUrl($withpicto=0,$option='',$maxlen=0, $id, $idtiers='', $search_typedossier='')
    {
        global $conf,$langs, $TypeListe;

        $result='';
		$lienfin='</a>';
		if ($option == 'Tiers')		{
			$result = '<a  id="lientiers" href="'.DOL_MAIN_URL_ROOT.'/custom/CahierSuivi/suivi_client/list_tiers.php?typeliste=tiers&Reftiers='.$idtiers.'&ID='.$idtiers.'&socid='.$idtiers.'&search_typedossier='.$search_typedossier.'&mainmenu=companies&token='.newtoken().'" >' ;
			 $src = DOL_MAIN_URL_ROOT."/theme/eldy/img/".$withpicto;
			$result .= '<img border = 0 title="Choisir" src="'.$src.'" alt="'.$langs->trans("AideModifTiers").'"></a>';
		}	
		elseif ($option == 'Dossier') {
			//$result = '<a href="'.DOL_URL_ROOT.'/custom/agefodd/session/card.php?id='.$id.'" >' ;
			$result = '<a href="'.DOL_MAIN_URL_ROOT.'/custom/CahierSuivi/suivi_client/list_dossier.php?typeliste=dossier&Refdossier='.$id.'&Reftiers='.$idtiers.'&socid='.$idtiers.'&search_typedossier='.$search_typedossier.'" >' ;
			if (empty($id)) return '';
			$result .= '<img border = 0 title="Dossier" src="'.DOL_MAIN_URL_ROOT.'/theme/eldy/img/'.$withpicto.'" alt="'.$langs->trans("AideModifDossier").'">';
		}		
       $result.=$lienfin;
       return $result;
	}//getNomUrl

 	
 } // ClassFormCglSuivi
?>