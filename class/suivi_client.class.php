<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * Version CAV - 2.7 - été 2022 - Migration Dolibarr V15
 * Version CAV - 2.8 - hiver 2023   - refonte de l'écran materiel loué
 * 									- diminution du pavé PaveSuivi de BU/LO
 *								  	- ajout d'info dans fetch_activite_by_doss
 *									- fiabilisation création Tiers + Dossier ==> nom générique Dossier : Dossier Client
 *								    - Nom générique si oublie saisie nom du dossier
 *									- protection des textes Titre, description, action contre " et ' à l'enregistrement
 *					 			  - Installation popup Modif/creation Suivi pour Inscription/Location
 *								   - fiabilisation des foreach
 *									- reassociation BU/LO à un autre contrat
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
 * Modif CCA  en urgence 14/6/2018 Date erronée sur serveur
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

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
require_once(DOL_DOCUMENT_ROOT.'/custom/agefodd/class/agefodd_session_stagiaire.class.php');
require_once(DOL_DOCUMENT_ROOT.'/custom/cglavt/class/cglFctCommune.class.php');
/**
 *	Put here description of your class
 */
class cgl_dossier extends CommonObject
{
	var $db;							//!< To store db handler

	var $id 			;
	var $dossier		;
	var $nvdossier		;
	var $datec 			;
	var $dateAff;
	var $tms 			;
	var $fk_priorite	;
	var $coulpriorite	;
	var $priorite		;
	var $fk_user_create ;
	var $NomCreateur	;
	var $PrenomCreateur	;
	//var $fk_user_mod 	;
	//var $NomMod			;
	//var $PrenomMod		;
	var $fk_typedossier	;
	var $typedossier	;
	var $fk_secteur		;
	var $secteur		;
	var $fk_origine		;
	var $origine		;
	var $descriptioncondense;
	// tiers
	var $fk_tiers 		;
	var $NomTiers		;
	var $nvtiers		;
	var $id_saisietiers	;	// identifiant du tiers saisi a reafficher en cas d'erreur
	var $TiersTel		;
	var $TiersSupTel	;
	var $telmail		; // constitution du libelle d'affichage
	var $TiersMail		;
	var $TiersSupMail	;
	var $country_code	;

	//
	var $action_courante;


    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }

    /**
     *    	Crée ou met à jour le tiers saisie dans CahierSuivi, qu'il soit le responsable du dossier ou le contact d'un échange relatif au dossier
     *
     *		@param	int		$id		Identifiant du Tiers en cas de Mise à jour
     *		@param	string	$nom	Nom du tiers si création ($id est alors vie)
     *		@param	string	$tel	Teléphone du tiers
     *		@param	string	$telsup	Téléphone supplémentaire du tiers
     *		@param	string	$mail	Mail du tiers si création
     *		@param	string	$mailsup	Mail Supplémentaire du tiers
     *		@param	objet	$user	Utilisateur assurant la saisie
     *		@return	int				>0 : Identifiant du client
	 *								=0 	Tiers recherché et non trouvé
	 *								<0 Erreur
	*/
	function Maj_tiers($id, $nom, $tel, $telsup, $mail,$mailsup, $user)
	{
		global $mysoc,  $db, $langs, $conf;

		$langs->load("errors");
			// Clean parameters
			if (isset($nom)) $nom=trim($nom);
			if ( $nom == 'nouveau tiers') $nom = '';

			$soc= new Societe($db);
			if (empty($id or $id == -1) and empty($nom)) return 0;
			elseif (!empty($id) and $id <> -1) 	{
				$ret = $soc->fetch($id);
				if ($ret == -2) // tiers inexistant - avant mise en place contraintes référentielles
					return 0;
				if (empty($nom)) $wnom = $soc->nom;
				else $wnom = $nom;
			}
			else $wnom = $nom;
			//if (isset($tel)) $tel=trim($tel);
			//if (isset($mail)) $mail=trim($mail);
			$tel=trim($tel);
			 $mail=trim($mail);
			if (isset($mail) and !empty($mail) and !isValidEmail($mail))  {
					 setEventMessages($langs->trans("ErrFmtEmail",$wnom,$mail),'','warnings');
				/*$langs->load("errors");
				$newemail.=img_warning($langs->trans("ErrorBadEMail",$email));*/
			}
			if (isset($mailsup) and !empty($mailsup) and !isValidEmail($mailsup))  {
					 setEventMessages($langs->trans("ErrFmtEmailSup",'',$wnom,$mailsup),'warnings');
			}
				// Check parameters
				// Put here code to add control on parameters values
			if (!empty($nom)) $soc->nom = $nom;
			if (!empty($nom)) $soc->name = $nom;
			if ( !empty($tel)) $soc->phone = $tel;
			if ( !empty($mail)) $soc->email = trim($mail);
			$soc->country_id = $mysoc->country_id;
			if (isset($telsup) and !empty($telsup)) $telsup=trim($telsup);
			$soc->array_options["options_s_tel2"] =$telsup;
			if (isset($mailsup) and !empty($mailsup) ) $mail=trim($mailsup);
			$soc->array_options["options_s_email2"] =$mailsup;

			if ($id == -1 or empty($id)) 	{
				//vérifier qu'un tiers même nom n'existe pas déjà
				$ret = $this->rechercheTiersparNom($nom,  '' , $tel, $telsup,  $mail, $mailsup);
				if ($ret <> -1) {
					setEventMessages($langs->trans("Error").' - '.$langs->trans("ErrExtTiers",$nom),'','errors');
					return(-1);
				}
				$soc->status = 1;
				$soc->client = 2;

				// Donner un code client à ce prospect
				$module=(! empty($conf->global->SOCIETE_CODECLIENT_ADDON)?$conf->global->SOCIETE_CODECLIENT_ADDON:'mod_codeclient_leopard');
				if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
				{
					$module = substr($module, 0, dol_strlen($module)-4);
				}
				$dirsociete=array_merge(array('/core/modules/societe/'),$conf->modules_parts['societe']);
				if (!empty($dirsociete)) {
					foreach ($dirsociete as $dirroot)
					{
						$res=dol_include_once($dirroot.$module.'.php');
						if ($res) break;
					}//foreach
				}
				$modCodeClient = new $module;
				$soc->code_client=$modCodeClient->getNextValue($soc,0);
				unset($modCodeClient);

				$ret = $soc->create($user);
				if ($ret <0) {
					setEventMessages($langs->trans("Error").' - '.$langs->trans("EnrCreatTiers".$ret),'','errors');
					return(-1);
				}
				else  return $soc->id;
			}
			elseif ($id > 0) {
				//if (!$soc->client or !$soc->fournisseur) {
					 //$soc->client = 2;
				//}
				// Donner un code client à ce prospect
				$module=(! empty($conf->global->SOCIETE_CODECLIENT_ADDON)?$conf->global->SOCIETE_CODECLIENT_ADDON:'mod_codeclient_leopard');
				if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
				{
					$module = substr($module, 0, dol_strlen($module)-4);
				}
				$dirsociete=array_merge(array('/core/modules/societe/'),$conf->modules_parts['societe']);
				if (!empty($dirsociete)) {
					foreach ($dirsociete as $dirroot)
					{
						$res=dol_include_once($dirroot.$module.'.php');
						if ($res) break;
					}//foreach
				}

				$modCodeClient = new $module;
				if (empty($soc->code_client) and $soc->client)	$soc->code_client= 'auto';
				if (empty($soc->code_fournisseur) and $soc->fournisseur)	$soc->code_fournisseur= 'auto';
				unset($modCodeClient);

				$ret = $soc->update($id, $user,1,1,1);
				if ($ret <0) {
//					if ($ret == -3) $text = ' => Code Client/Fournisseur Erronné - Modifier la fiche client';
//					else $text ='';
					if  (! empty ($soc->errors) ) {
						foreach ($soc->errors as $error){
							setEventMessages($langs->trans("Error").' - '.$langs->trans("EnrModTiers".$ret). $error,'','errors');
						}
					}
					else 	setEventMessages($langs->trans("Error").' - '.$langs->trans("EnrModTiers".$ret). $text,'','errors');
					return(-1);

				}
				return $soc->id;
			}


	} //Maj_tiers

	function rechercheTiersparNom($nom, $strfilters = '', $tel = '', $telsup = '',  $mail = '', $mailsup = '')
	{
    	// Check parameter
    	if (empty($nom))
    	{
    		$this->errors[]='ErrorBadValueForParameter';
    		return -1;
    	}

    	// Generation requete recherche
    	$sql = "SELECT (1)  FROM ".MAIN_DB_PREFIX."societe";
    	$sql.= " WHERE entity IN (".getEntity('category',1).")";
    	if (! empty($nom))			$sql.= ' AND nom = "'.$nom.'"';
    	if (! empty($strfilters) ) $sql.= " AND ".$strfilters;

    	dol_syslog("rechercheTiersparNom ");
    	$res  = $this->db->query($sql);
		if ( $res  and $res->num_rows > 0)        		return 0;
    	else    	    								return -1;
    }//rechercheTiersparNom
    /**
     *    	Crée ou met à jour un dossier
     *
     *		@param	int		$id_dossier		Identifiant du dossier en cas de Mise à jour, -1 en cas de création
     *		@param	string	$nom			Nom du dossier si création ($id est alors vie)
     *		@param	int	$typedossier		pointe vers fk_typedossier, dictionnaire cglavt_c_typedossier
     *		@param	int	$secteur			pointe vers fk_secteur, dictionnaire cglavt_c_secteur
     *		@param	int	$nb					Nb de participants concernés - Obsolete
     *		@param	int	$priorite			pointe vers fk_priorite, dictionnaire cglavt_c_priorite
     *		@param	int	$origine			origine de la découverte de CAV, pointe vers fk_origine, dictionnaire ???
     *		@param	int	$tiers				Identifiant du tiers
     *		@param	int	$arg_createur		Identifiant de l'utilisateur créateur du dossier
     *		@param	int	$arg_modificateur		Identifiant de l'utilisateur modifiant le dossier
     *		@return	int	 >0 : Identifiant du client
	 *					 <0 Erreur
	*/
	function Maj_dossier($id_dossier, $nom, $typedossier, $secteur, $nb,  $priorite, $origine, $id_tiers, $arg_createur, $arg_modificateur)
	{
			global  $db, $langs, $user, $TypeListe ;

			// Clean parameters
			if (isset($nom)) $nom=trim($nom);
			$dos= new cgl_dossier($db);
			if (!empty($id_dossier) and $id_dossier <> -1)
				$dos->fetch($id_dossier);
				// Check parameters
				// Put here code to add control on parameters values
			if (empty($nom) and $id_dossier == -1)
				$dos->dossier =$langs->trans( 'GenDosCli');
			elseif (!empty($nom)) 		$dos->dossier = $nom;
			//$dos->fk_typedossier = $typedossier;
			$dos->fk_typedossier = 2; // correspond à Client
			$dos->fk_secteur = $secteur;

			$dos->nb = $nb;
			$dos->fk_priorite = $priorite;
			$dos->fk_origine = $origine;
			$dos->fk_user_create = $arg_createur;
			//$dos->fk_user_mod = $arg_modificateur;
			// Dans général, on ne modifie pas le tiers d'un dossier, on peut juste saisir un interlocuteur de l'échange
			if ($TypeListe == 'generale' and !empty($dos->fk_tiers )) $dos->fk_tiers =  '';
			else $dos->fk_tiers = $id_tiers;

			if ($id_dossier == -1 ) 	{

				//vérifier qu'un dossier  même nom , meme type, même secteur, même tiers n'existe pas déjà
				$ret = $this->rechercheDossier($nom,  $typedossier , $secteur,  $origine, $id_tiers);
				if ($ret <> -1) {
					setEventMessages($langs->trans("Error").' - '.$langs->trans("ErrExtDossier",$nom),'','errors');
					return(-1);
				}

				$ret = $dos->create($user, false);
				if ($ret <0) {
					// Créer un dossier au nom 'Dossier Client";
					$dos->dossier =$langs->trans( 'GenDosCli');
					$ret = $dos->create($user, false);
					if ($ret <0)
						dol_syslog($langs->trans("Error").' - '.$langs->transnoentitiesnoconv("EnrCreatDos".$ret),'','errors');
					return($ret);
				}
				else  return $ret;
			}
			else {
				$dos->id = $id_dossier;
				$ret = $dos->update( $user, false);
				if ($ret <0) {
					setEventMessages($langs->trans("Error").' - '.$langs->transnoentitiesnoconv("EnrModDos".$ret),'','errors');
					return($ret);
				}
				return $id_dossier;
			}

	} //Maj_dossier
	function rechercheDossier($nom,  $typedossier=-1 , $secteur=-1 , $origine=-1, $id_tiers=-1, $strfilters = '')
	{
    	// Check parameter
    	if (empty($nom))
    	{
    		$this->errors[]='ErrorBadValueForParameter';
    		return -1;
    	}

    	// Generation requete recherche
    	$sql = "SELECT (1)  FROM ".MAIN_DB_PREFIX."cglavt_dossier";
    	$sql.= " WHERE entity IN (".getEntity('category',1).")";
    	if (! empty($nom))			$sql.= ' AND libelle = "'.$nom.'"';
    	if ($secteur <> -1)			$sql.= ' AND fk_secteur = "'.$secteur.'"';
    	if ($typedossier <> -1)			$sql.= ' AND fk_typedossier = "'.$typedossier.'"';
    	if ($origine <> -1)			$sql.= ' AND fk_origine = "'.$origine.'"';
    	if ($id_tiers <> -1)			$sql.= ' AND fk_soc = "'.$id_tiers.'"';

    	if (! empty($strfilters) ) $sql.= " AND ".$strfilters;

    	dol_syslog("rechercheDossier ");
    	$res  = $this->db->query($sql);
    	if ($res   and $res->num_rows > 0)    	{
    		return 0;
    	}
    	else    	{
    		$this->error=$this->db->lasterror();
    		return -1;
    	}
    }//rechercheDossier
    /**
     *    	Crée ou met à jour un échange du dossier
     *
     *		@param	int		$id_echange		Identifiant de l'échange du dossier en cas de Mise à jour. Si vide, création de l'échange
     *		@param	int		$id_dossier		Identifiant du dossier
     *		@param	string	$action			Action à réaliser
     *		@param	string	$titre			Titre de l'échange
     *		@param	string	$description	Description détaillée de l'échange
     *		@param	int		$id_tiers		Identifiant du tiers
     *		@param	int		$id_tiers		Identifiant du tiers
     *		@param	int	$dateechang			Date modification ou création de l'échange
     *		@param	int	$arg_createur		Identifiant de l'utilisateur créateur du dossier - Obsolete
     *		@return	int				>0 : Identifiant de l'échange
	 *								<0 Erreur
	*/
	/**
	*	Crée ou met à jour un échange
	*
	*		@param	int		$id_echange		Identifiant du dossier en cas de Mise à jour, null en cas de création
	*		@param	int		$id_dossier		Identifiant du dossier
	*		@param	string	$action			Action à réaliser
	*		@param	string  $titre			titre de l'écahnge
	*		@param	string		$description	description
	*		@param	int		$id_tiers		Tiers de l'échange
	*		@param	string	$dateechang		dateechang
	*		@param	int		$arg_createur	arg_createur
	*		@param	int		$arg_modificateur	arg_modificateur
    *		@return	int		>0 : Identifiant du client
	 *						<0 Erreur
	*/
	function Maj_echange($id_echange, $id_dossier, $action, $titre, $description,  $id_tiers, $dateechang, $arg_createur, $arg_modificateur)
	{
			global  $db, $langs, $user;
			
			// Clean parameters
			if (isset($nom)) $nom=trim($nom);
			$echg= new cgl_echange($db);
			if (!empty($id_echange)) 	$echg->fetch($id_echange, true);
			$echg->fk_dossier = $id_dossier;
			$echg->action	= $action;
			$echg->titre	= $titre;
			$echg->desc	= $description;
			if ($id_tiers == 0) $id_tiers = '';
			$echg->id_interlocuteur	= $id_tiers;
			$echg->fk_user_create = $arg_createur;
			$echg->fk_user_mod = $arg_modificateur;
				// Check parameters
				// Put here code to add control on parameters values
			if (empty($id_echange ) ) 	{
				//vérifier qu'un échange  même dossier , meme titre, même tiers, même action n'existe pas déjà
				if (  (!empty($action) or !empty($titre))) {
					$ret = $this->rechercheEchange(  $id_dossier , $titre, $description,  $action, $id_tiers);
					if ($ret <> -1) {
						setEventMessages($langs->trans("Error").' - '.$langs->trans("ErrExtEchange",$titre),'','errors');
						return(-1);
					}
				}

				$ret = $echg->create($user, false);
				if ($ret <0) {
					setEventMessages($langs->trans("Error").' - '.$langs->transnoentitiesnoconv("EnrCreatEchg".$ret),'','errors');
					return($ret);
				}
				else  return $ret;
			}
			else {
				$ret = $echg->update($id_echange, $user, false);
				if ($ret <0) {
					setEventMessages($langs->trans("Error").'-' .$langs->transnoentitiesnoconv("EnrModEchg".$ret),'','errors');
					return ($ret);
				}
				return $ret;
			}
	} //Maj_echange


	function rechercheEchange(  $id_dossier , $titre ='', $description='',  $action ='', $id_tiers=-1, $strfilters ='')
	{
		// proteger les " dans description

    	// Generation requete recherche
    	$sql = "SELECT (1)  FROM ".MAIN_DB_PREFIX."cglavt_dossierdet";
    	$sql.= " WHERE entity IN (".getEntity('category',1).")";
    	if (! empty($titre))			$sql.= ' AND titre = "'.$titre.'"';
    	if (! empty($action))			$sql.= ' AND action = "'.$action.'"';
		if (! empty($description))		$sql.= ' AND description = "'.$description.'"';
    	if ($id_dossier <> -1)			$sql.= ' AND fk_dossier = "'.$id_dossier.'"';
    	if ($id_tiers <> -1)			$sql.= ' AND fk_soc = "'.$id_tiers.'"';

    	if (! empty($strfilters) ) $sql.= " AND ".$strfilters;

    	dol_syslog("rechercheEchange ");
    	$res  = $this->db->query($sql);
    	if ($res   and $res->num_rows > 0)    	{
    		return 0;
    	}
    	else    	{
    		$this->error=$this->db->lasterror();
    		return -1;
    	}
    }//rechercheEchange

	/**
	 * Load all objects in memory from database
	 *
	 * @param int $socid socid filter
	 * @param string $sortorder order
	 * @param string $sortfield field
	 * @param int $limit page
	 * @param int $offset
	 * @param array $filter output
	 * @return int <0 if KO, >0 if OK
	 */
	function fetch($id_dossier)
	{

/* select fk__dossier, case when isnull(MaxTm) then Max_datec else  case when MaxTm > Max_datec then MaxTm else Max_datec end  end as Date Aff
from (select fk__dossier, Max (datec) as Max_datec, max(tms) as MaxTms from dossierdet where fk_dossier = 1 groupo by  fk__dossier) as Tb
*/
		$sql = "";
		$sql = "SELECT DISTINCT d.rowid, d.fk_soc, d.datec, d.tms, d.fk_createur, d.fk_moduser,  d.fk_secteur, s.label as secteur, d.nb";
		$sql .= ", d.libelle as dossier , d.fk_priorite, spri.label as priorite, fk_origine, d.fk_typedossier, d.fk_soc ";
		$sql .= ", spri.color as coulpriorite ";

		$sql .= " FROM " . MAIN_DB_PREFIX . "cglavt_dossier as d ";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cglavt_c_priorite as spri on fk_priorite = spri.rowid";
		$sql .=  " LEFT JOIN " . MAIN_DB_PREFIX . "cglavt_c_secteur as s  ON d.fk_secteur = s.rowid";
		$sql .= " WHERE d.entity IN (" . getEntity('agsession') . ")";
		$sql .= " AND d.rowid ='".$id_dossier."'";

		dol_syslog(get_class($this) . "::fetch_unitaire sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		//$num = $this->db->num_rows($resql);
		if ($resql) {
			$obj = $this->db->fetch_object($resql);

			$this->id 				= $obj->rowid;
			$this->dossier			= $obj->dossier;
			$this->datec 			= $obj->datec;
			$this->tms 				= $obj->tms;
			$this->fk_user_create 	= $obj->fk_createur;
			//$this->fk_user_mod 		= $obj->fk_moduser;
			$this->fk_priorite		= $obj->fk_priorite;
			$this->priorite		= $obj->priorite;
			$this->fk_secteur		= $obj->fk_secteur;
			$this->secteur		= $obj->secteur;
			$this->fk_typedossier	= $obj->fk_typedossier;
			$this->fk_tiers			= $obj->fk_soc;
			$this->nb				= $obj->nb;
			$this->fk_origine		= $obj->fk_origine;
			$this->coulpriorite		= $obj->coulpriorite;
			$this->db->free($resql);
			return 1;
		}
		 else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch" . $this->error, LOG_ERR);
			return - 1;
		}

	} //fetch

	/*
	*
	* création d'un dossier*
	*	@param objet	$user 	utilisateur créateur
	*	@param boolean 	$flgcommit 	true => commiter la création, false ==> le commit est ailleurs
	*
	*	@retour int 	>0 si dossier est correctement créé, -3 erreur  SQL
	*
	*/
	function create( $user, $flgcommit=true)
	{
		$this->tms = dol_now('tzuser');
		$wfcom =  new CglFonctionCommune ($this->db);
		if (!empty($id)) 	$dosl->fetch($id);
		$sql = "INSERT INTO  " . MAIN_DB_PREFIX . "cglavt_dossier ( fk_createur ,  libelle, fk_typedossier, fk_secteur , fk_priorite, fk_origine,";
		$sql .= ' nb,fk_soc, datec, tms  ) VALUES ( "';
		if (empty( $this->fk_user_create)) $sql .= $user->id;  else $sql .= $this->fk_user_create;
		$sql .= '",  "';
		$sql .= $wfcom->cglencode($this->dossier).'"';
		if (!empty($this->fk_typedossier) and $this->fk_typedossier <> -1) $sql .= ', "'.$this->fk_typedossier.'"';
					else $sql .= ', 0';
		if (!empty($this->fk_secteur) and $this->fk_secteur <> -1) $sql .= ', "'.$this->fk_secteur.'"';
					else $sql .= ', 0';
		if (!empty($this->fk_priorite) and $this->fk_priorite <> -1) $sql .= ', "'.$this->fk_priorite.'"';
					else $sql .= ', 0';
		if (!empty($this->fk_origine) and $this->fk_origine <> -1) $sql .= ', "'.$this->fk_origine.'"';
					else $sql .= ', 0';
		if (!empty($this->nb) ) $sql .= ', "'.$this->nb.'"';
					else $sql .= ', 0';
		if (!empty($this->fk_tiers) and $this->fk_tiers <> -1) $sql .= ', "'.$this->fk_tiers.'"';
					else $sql .= ', 0';
		$sql .= ",  '".$this->db->idate( dol_now('tzuser'))."'";
		$sql .= ",  '".$this->db->idate( dol_now('tzuser'))."'";
		$sql .= " )";
		if ($flgcommit) $this->db->begin();// s'il n'est pas fait avant l'appel
		dol_syslog(get_class($this) . "::INSERT ", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
            if ($flgcommit) $this->db->commit();
			$this->db->free($resql);
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."cglavt_dossier");
			return $this->id;
		} else        {
            if ($flgcommit) $this->db->rollback();
            dol_syslog(get_class($this)."::INSERT fails ", LOG_WARNING);
            return -3;
        }
	} // create
	/*
	* Mise à jour de la base à partir de l'objet this
	*
	*	@param	int	$user	Identifiant du user opérant
	*	@param	boolean	$flgcommit True si on doit gérer la transaction en interne, 
	*			false si la gestion se fait ailleurs
	*	@retour	1 si OK, -3 si erreur SQL
	*/
	function update( $user, $flgcommit=true)
	{
		global $arg_idtiers;

		$id = $this->id;
		$dosl= new cgl_dossier($this->db);
		$wfcom =  new CglFonctionCommune ($this->db);
		if (!empty($id)) 	$dosl->fetch($id);
		else $this->fk_tiers= $arg_idtiers;
		$this->tms = dol_now('tzuser');
		$flvirg = false;
		$nbmodif = 0;
		$sql = "UPDATE " . MAIN_DB_PREFIX . "cglavt_dossier ";
		$sql .= "set ";

		/* pour reparer bug ' '==> '_'
		$this->dossier = dol_string_nospecial($this->dossier, ' ',array("_"));
		*/

		if ($dosl->dossier <> $this->dossier) {$sql .= 'libelle = "'.$wfcom->cglencode($this->dossier).'"'; $flvirg = true; $nbmodif++; }
		if (!empty($this->fk_typedossier) and $this->fk_typedossier <> -1 and $dosl->fk_typedossier <> $this->fk_typedossier) {if ($flvirg == true) $sql .= ',  '; $sql .= 'fk_typedossier = "'.$this->fk_typedossier.'"'; $flvirg = true;$nbmodif++;};
		if (!empty($this->fk_secteur) and $this->fk_secteur <> -1 and $dosl->fk_secteur <> $this->fk_secteur) {if ($flvirg == true) $sql .= ',  '; $sql .= ' fk_secteur = "'.$this->fk_secteur.'"'; $flvirg = true;$nbmodif++;};
		if (!empty($this->fk_priorite) and $this->fk_priorite <> -1 and $dosl->fk_priorite <> $this->fk_priorite)  {if ($flvirg == true) $sql .= ',  '; $sql .= ' fk_priorite = "'.$this->fk_priorite.'"'; $flvirg = true;$nbmodif++;};
		if (!empty($this->fk_origine) and $this->fk_origine <> -1 and $dosl->fk_origine <> $this->fk_origine) {if ($flvirg == true) $sql .= ',  '; $sql .= ' fk_origine = "'.$this->fk_origine.'"'; $flvirg = true;$nbmodif++;};
		if (!empty($this->action) ) {if ($flvirg == true) $sql .= ',  '; $sql .= ' action = "'.$this->action.'"'; $flvirg = true;$nbmodif++;};
		if (empty($this->nb) ) $this->nb = 0;
		if ($dosl->nb <> $this->nb) {if ($flvirg == true) $sql .= ',  '; $sql .= ' nb = "'.$this->nb.'"'; $flvirg = true;$nbmodif++;};
		if (!empty($this->fk_tiers) and $this->fk_tiers <> -1 and $dosl->fk_tiers <> $this->fk_tiers) {if ($flvirg == true) $sql .= ',  '; $sql .= ' fk_soc ="'.$this->fk_tiers.'"'; $flvirg = true;$nbmodif++;};
		//if ( $dosl->fk_user_mod <> $this->fk_user_mod) {if ($flvirg == true) $sql .= ',  '; $sql .= ' fk_moduser = "'.$this->fk_user_mod.'"'; $flvirg = true;$nbmodif++;};
		if ( !empty($this->fk_user_create) and $dosl->fk_user_create <> $this->fk_user_create)	{
			if ($flvirg == true) 					$sql .= ',  ';
			$sql .= ' fk_createur = "'.$this->fk_user_create.'"';
			$flvirg = true;
			$nbmodif++;
		}
		if ($flvirg == true) $sql .= ',  ';
		$sql .= " tms = '".$this->db->idate(dol_now('tzuser'))."'";
		$sql .= " WHERE rowid = '".$id."'";

		unset($dosl);
		unset ($wfcom);
		if ($flvirg == true) {
			if ($flgcommit) $this->db->begin();// s'il n'est pas fait avant l'appel
			dol_syslog(get_class($this) . "::update sql=" . $sql, LOG_DEBUG);
			$resql = $this->db->query($sql);

			if ($resql) {
				if ($flgcommit) $this->db->commit();
				$this->db->free($resql);
				return 1;
			} else        {
				if ($flgcommit) $this->db->rollback();
				dol_syslog(get_class($this)."::Update fails ", LOG_WARNING);
				return -3;
			}
		}
		else return 1;

	} // update

	function recupInfoDos ($TypeListe)
	{
		global $arg_idtiers, $arg_priorite,   $id_contact, $arg_typedossier, $arg_id_dossier,  $date_cree, $arg_secteur,$arg_dossier, $arg_createur;
		global 	$arg_nb,   $arg_origine, $arg_teltiers, $arg_mailtiers, $arg_nvtiers, $arg_nomtiers,  $arg_teltiers, $arg_telsuptiers, $arg_mailtiers	;
		global $arg_mailsuptiers, $arg_action;

		$waff = new FormCglSuivi($this->db) ;
		if (!empty($arg_idtiers)) $this->id_saisietiers= $arg_idtiers;
		$this->fk_priorite= $arg_priorite;
		$this->tms = dol_now('tzuser');
		$this->fk_typedossier = $arg_typedossier;
		$this->id = $arg_id_dossier;
		$this->nvdossier = $arg_dossier;
		$this->dossier = $arg_dossier;
		$this->fk_secteur = $arg_secteur;
		$this->nb = $arg_nb;
		$this->fk_origine = $arg_origine;
		$this->fk_user_create = 	$arg_createur;

		$texte=str_replace(array("\r\n","\n"),'<br />',$arg_action);
		$this->action_courante =  $texte;

		$this->TiersTel = $arg_teltiers;
		$this->TiersMail = $arg_mailtiers;
		$this->NomTiers = $arg_nomtiers;

		if (!empty($arg_idtiers) and $arg_idtiers > -1)	{
				$this->telmail=$waff->ChercheTelMailIdTiersContact($this, $arg_idtiers );
		}
		if ($TypeListe == 'generale'){
			if (empty($arg_idtiers) or $arg_idtiers == -1)  	$this->nvtiers = $arg_nvtiers;
		}
		else {
			$this->TiersSupTel = $arg_telsuptiers;
			$this->TiersSupMail=$arg_mailsuptiers;
		}
	} // recupInfoDos


	function RendInfo ($TypeListe)
	{
		global $arg_idtiers, $arg_priorite,  $arg_action, $id_contact, $id_echange, $arg_typedossier, $arg_id_dossier,  $date_cree, $arg_secteur, $arg_dossier;
		global 		$arg_nb,  $arg_nvinterl,   $arg_titre, $arg_origine, $arg_tel, $arg_mail;

		if ( $this->fk_tiers = '') $arg_idtiers == -1;
		else $arg_idtiers = $this->fk_tiers;
		$arg_priorite = $this->priorite ;

		$texte=str_replace(array('<br />', "\n"),$this->action);
		$arg_action = $texte ;
		$id_echange = $this->id_echange ;
		$arg_typedossier = $this->typedossier;
		$arg_id_dossier = $this->id_dossier  ;
		$arg_dossier = $this->dossier ;
		$arg_secteur = $this->secteur ;
		$arg_nb = $this->nb ;
		$arg_nvinterl = $this->interlocuteur;
		$arg_titre = $this->titre ;
		$arg_origine = $this->origine  ;
		$arg_tel = $this->tel ;
		$arg_mail = $this->mail ;

	} // RendInfo

	/*
	* param	$strdate 	=> date au format D/M/Y H:M (H:M optionel)
	$ param	flgh		=> 1 si on doit mettre les h/m/s, 0 sinon
	* param	 retourne 	==> date au format YYYYMMDD000000
	*/
	function transfDateMysql($strdate, $flgheure='')
	{
		$pos1 = 0;
		$pos2= strpos($strdate, '/');
		$strdatedh=$strdate;
		$lg = strpos($strdate, ' ');
		if ($lg>0)
			$strdate = substr($strdatedh,	0,	$lg );
		if (empty($strdate)) return;
		if ($pos2 != strlen($strdate) -1) $pos3 = strpos($strdate, '/', $pos2+1);
		else return $strdate;
		if ($pos3 == 0) return ;

		// on considère qu'un sportif sera agé moins de 95 ans - pour mettre 19 ou 20 comme si裬e de naissance
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

		if ($flgheure)
		{
			if (isset ($temp)) unset($temp);
			$temp = explode ( ' ', $strdatedh);
			//$posh1= strpos($strdatedh, ' ');
			//$strheure=substr($strdatedh, $posh1, strlen($strdatedh));
			$strheure=$temp[1];
			if (isset ($temp)) unset($temp);
			$temp = explode ( ':', $strheure);
			if (isset($temp)) {
				if (strlen($temp[0]) == 1) $heure = '0'.$temp[0] ; else $heure = $temp[0];
				if (strlen($temp[1]) == 1) $min = '0'.$temp[1] ; else $min = $temp[1];
				$datemysql .= ' '.$heure.':'.$min.':00';
			}
		}
		return $datemysql;
	}	/* transfDateMysql */
	function dossiersanstiers($id)
	{

		$this->tms = dol_now('tzuser');
		$sql = "UPDATE " . MAIN_DB_PREFIX . "cglavt_dossier ";
		$sql .= "set fk_soc = 0";
		$sql .= ", tms = ".$this->db->idate($this->tms);
				$sql .= " WHERE rowid =". $id;


		if ($flgcommit) $this->db->begin();// s'il n'est pas fait avant l'appel
		dol_syslog(get_class($this) . "::dossiersanstiers sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {
            if ($flgcommit) $this->db->commit();
			$this->db->free($resql);
			return 1;
		} else        {
            if ($flgcommit) $this->db->rollback();
            dol_syslog(get_class($this)."::dossiersanstiers fails ", LOG_WARNING);
            return -3;
        }

	} // dossiersanstiers
	function update_champs($champ1, $val1, $champ2='', $val2='', $champ3='', $val3='', $champ4='', $val4='' )
	{
    	global $conf, $langs, $user, $bull;
		$error=0;
		// parametres location
	    if (isset($val1))	 	$val1		=trim($val1);
	    if (isset($val2))	 	$val2		=trim($val2);
	    if (isset($val3))	 	$val3		=trim($val3);
	    if (isset($val4))	 	$val4		=trim($val4);
		// Put here code to add control on parameters values
		// le champ action est mis Ã  jour en mÃªme temps que les clÃ©s Ã©trangÃ¨res : AjoutFkDolibarr
		$i=0;

        // Update request
		$sql = "UPDATE ".MAIN_DB_PREFIX."cglavt_dossier SET  ";
		 $sql.= $champ1."= '".$val1."' ";
		if (!empty($champ2)) $sql.= ', '.$champ2.'= "'.$val2.'" ';
		if (!empty($champ3)) $sql.=  ', '.$champ3.'= "'.$val3.'" ';
		if (!empty($champ4)) $sql.=  ', '.$champ4.'= "'.$val4.'" ';
		$sql.= "  Where rowid =  ".$this->id;
		$this->db->begin();
		// liste champ mis à jours
		$lb = "champs:".$champ1;
		if (!empty($champ2)) $lb .= "---".$champ2;
		if (!empty($champ3)) $lb .= "---".$champ3;
		if (!empty($champ4)) $lb .= "---".$champ4;
	   	dol_syslog(get_class($this)."::update_".$lb." sql=".$sql, LOG_DEBUG);

        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
        // Commit or rollback
        if ($error)
		{
			dol_syslog(get_class($this)."::update_champs ".$errmsg, LOG_ERR);
	        $this->error.=($this->error?', '.$errmsg:$errmsg);
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
	} // update_champs
	function update_priorite($val)
	{
		$ret = $this->update_champs ('fk_priorite', $val, 'tms', dol_print_date(dol_now ('gmt'), '%Y-%m-%d'));
		return $ret;
	} // update_priorite
	function fetch_activite_by_doss($id_dossier)
	{
		$sql = "";
		$sql = "SELECT DISTINCT b.rowid, b.ref, b.statut ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "cglinscription_bull as b";
		$sql .= " WHERE b.fk_dossier ='".$id_dossier."'";
		dol_syslog(get_class($this) . "::fetch_activite_by_doss sql=" , LOG_DEBUG);
		$resql = $this->db->query($sql);
		//$num = $this->db->num_rows($resql);
		$tab = array();
		if ($resql) {

			$num = $this->db->num_rows($resql);
			$i = 0;

			if ($num) {
				while ( $i < $num ) {
					$tab[] = $this->db->fetch_object($resql);
					$i++;
				}
				return $tab;
			}
			else return;
		}
		 else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch_activite_by_doss" . $this->error, LOG_ERR);
			return - 1;
		}

	} //fetch_activite_by_doss
	function rechercheColPriorite($id)
	{
    	// Check parameter
    	if (empty($id))
    	{
    		$this->errors[]='ErrorBadValueForParameter';
    		return -1;
    	}

    	// Generation requete recherche
    	$sql = "SELECT color  FROM ".MAIN_DB_PREFIX."cglavt_c_priorite";
    	$sql.= " WHERE rowid = '".$id."'";
    	dol_syslog("rechercheColPriorite ");
    	$res  = $this->db->query($sql);
    	if ($res   and $res->num_rows > 0)    	{
			$obj = $this->db->fetch_object($res);
			$color = $obj->color;
			$this->db->free($res);
    		return $color;
    	}
    	else    	{
    		$this->error=$this->db->lasterror();
    		return -1;
    	}
    }//rechercheColPriorite
	/*
	*
	* 	Retourne la présence d'action à réaliser
	*
	* 	@param	&id	Identifiant du dossier
	*	@retour	Vrai ou false
	*/
	function IsActionDossier($id)
	{
		global $db;

		if (empty($id)) return false;

		// Test si  Actions à réaliser
		$sql .= 'SELECT e.action, e.date_realise, e.user_realise  ';
		$sql .= 'FROM ' . MAIN_DB_PREFIX . 'cglavt_dossierdet as e   ';
		$sql .= "WHERE ";
		$sql .= " e.fk_dossier = ".$id;
		$sql .= " AND isnull(e.date_realise)";

		$resql = $db->query($sql);
		dol_syslog('IsActionDossier::',LOG_DEBUG);

		$num = $db->num_rows($resql);
		if ($num == 0) return false;
		else 	return true;
	} //IsActionDossier

	function  ScriptModale()
	{
		global $langs;
		print '<script>
		function OuvreModListDos(o, id_client, id_dossier, id_bull)				
		{
			let retour =  chargeTiersDoss( id_client);
			let tableau = retour.split("?!@");
			let fmodalDialog = document.getElementById("fmodalDialogChgDoss");
			if (typeof fmodalDialog.showModal === "function") {
				document.getElementById("Mod_iddtiers").innerHTML = id_client;
				document.getElementById("Mod_idddossier").innerHTML = id_dossier;

				document.getElementById("Mod_idbull").innerHTML = id_bull;
				document.getElementById("titreDoss").innerHTML = "'.$langs->trans("DossierSuivi").'";
					// Ajouter les >Elements Radio supplémentaires avec les infos de tableau 
					let trouvechecked = false;
					for ($i=0; $i<tableau.length; $i++) {			
						let tabdos = tableau[$i].split("?@");
						let id_doss = tabdos[0];
						let obj_id_doss=document.getElementById(id_doss);
						if (obj_id_doss === null) {
							let libelle = tabdos[1];
							let colorcss = `style="color:`+tabdos[2]+`"`;
							priorite = tabdos[3];
		       				 $("#BoiteSuiviClient")
							.append(`<tr><td><input type="radio" class="flat" id=`+id_doss+` name="rdselectdoss" value=`+id_doss+`></td>
										<td><label for=`+id_doss+`>`+libelle+`</label></td>
										<td `+colorcss+` >`+priorite+`</td></tr>`);
							if (id_dossier == id_doss) {
								document.getElementById(id_doss).checked = true;
								trouvechecked = true;
								}						
						}
					}							
				document.getElementById("nvdossier").innerHTML = "nouveau dossier";				document.getElementById("nvdossier").value = "nouveau dossier";
				fmodalDialog.showModal();
			};
		};	
		function chargeTiersDoss(id_client)
		{
			let argtiers = "ID="+id_client;
			url = "'.DOL_MAIN_URL_ROOT.'/custom/CahierSuivi/suivi_client/ReqDossiers.php?";
			url = url.concat(argtiers);
			let	Retour = creerobjet(url);
			return Retour;

		};
		function EnrBullDossier(id_bull, iddoss)
		{
			let argbull = "id_bull="+id_bull;
			let argdossier = "&id_dossier="+iddoss;
			url = "'.DOL_MAIN_URL_ROOT.'/custom/cglinscription/ReqEnrBullDoss.php?";
			url = url.concat(argbull);
			url = url.concat(argdossier);
			let	Retour = creerobjet(url);
			return Retour;

		};
		function CreerDossierBull(id_bull, libdoss, id_tiers)
		{
			let argbull = "id_bull="+id_bull;
			let argtiers = "&id_tiers="+id_tiers;
			let token=`'.newtoken().'`;
			let argtoken = "&token="+token;
			let argdossier = "&nom_dossier="+libdoss;
			url = "'.DOL_MAIN_URL_ROOT.'/custom/CahierSuivi/suivi_client/ReqCreerDossBull.php?";
			url = url.concat(argbull);
			url = url.concat(argdossier);
			url = url.concat(argtiers);
			url = url.concat(argtoken);
			let	Retour = creerobjet(url);
			return Retour;

		};
		function ChargePaveSuivi(id_bull, iddoss )
		{
			let argdossier = "id_dossier="+iddoss;
			let origine = "&origine=`4saisons`";
			let argbull = "&id_bull="+id_bull;
			let argfenetre ="&fenetre=`modale`";
			url = "'.DOL_MAIN_URL_ROOT.'/custom/CahierSuivi/suivi_client/ReqAffPaveSuivi.php?";
			url = url.concat(argdossier);
			url = url.concat(origine);
			url = url.concat(argbull);
			url = url.concat(argfenetre);
			let	Retour = creerobjet(url);
			return Retour;

		};
		function	EnrBullDoss(o)
		{
			let retour = "";
			let iddoss = 0;
			let arg_boutons = document.getElementsByName("rdselectdoss");	
			let id_bull = document.getElementById("Mod_idbull").innerHTML;
			let id_dossier = document.getElementById("Mod_idddossier").innerHTML;
			let id_tiers = document.getElementById("Mod_iddtiers").innerHTML;
			let libdoss = document.getElementById("nvdossier").value;
			let num = arg_boutons.length;
			for (i=0;i<num;i++) {
				if (arg_boutons[i].checked==true) { 
					if (arg_boutons[i].value == -1 ) { 
						ret = CreerDossierBull(id_bull, libdoss , id_tiers);
						iddoss=ret;
					}
					else {
						ret =  EnrBullDossier(id_bull, arg_boutons[i].value);
						iddoss = arg_boutons[i].value;
					};					
				};
			};
			retour = ChargePaveSuivi(id_bull, iddoss);
			tab = retour.split( "!@&!",2);
			document.getElementById("PaveSuivi").innerHTML = tab[0];
			let obj_AffDossFondDePage=document.getElementById("AffDossFondDePage");
			/* if (obj_AffDossFondDePage === null) { alert ("obj_AffDossFondDePage absent");} */
			/* else	document.getElementById("AffDossFondDePage").innerHTML = tab[1]; */
			if (obj_AffDossFondDePage != null) document.getElementById("AffDossFondDePage").innerHTML = tab[1];
		}; 
	</script>';
	} //ScriptModale

	/*
	*
	*	 Fonction globale permettant l'enregistrement de dossier/tiers/echange/interlocuteur
	*
	*	@param	int	$mode 	 1  - Dossier-Tiers-Echange (echange non vide) - (général et tiers)
    *					    2 - Echange - Interlocuteur (dossier - BU/LO)
	*	@param	array	$info	("<label>" =>"valeur", ...)
	*						'id_dossier', 'dossier', 'typedossier,'dossanstiers', 'nvtiers', 'idtiers', 
	*						'teltiers', 'telsuptiers', 'mailtiers', 'mailsuptiers'
	*						'secteur', 'nb',  'priorite', 'origine', 'referent'
	*						'action', 'titre', 'description'
	*						'idInterlocuteur','nvinterl', 'telInterl','mailInterl'
	*						'idEchange',  'action','titre', 'description', 'st_dateechg', 'modificateur'
	*
	*	@retour	tableau liste des erreurs
	*
	*/	
	function EnrTiers_Dossier_Echange($mode, $info= array())
	{		 
		global $error, $user, $langs;
		
		//dol_syslog("EnrTiers_Dossier_Echange - Arguments:".print_r(func_get_args(), true));
		
		$error = 0;
		$this->db->begin();
		$line = new cgl_dossier($this->db);
		$line->fetch($info['idDossier']);
		// DETERMINATION ENVIRONNEMENT DOSSIER - ECHANGE - TIERS
		if ( !empty($info['action']) or !empty($info['titre']) or !empty($info['description'])) {
			$fglEnchangevide = false;
		}
		else {
			$fglEnchangevide = true;	
		}

		// GESTIONS DES ERREURS DE SAISIE
	 if ( $mode == 1 ){

		if ($info['id_dossier'] == -1  and empty($info['dossier']) ) {
			$info['dossier'] =$langs->trans( 'GenDosCli');		
		}
		else if (!empty($info['dossier'])) $info['dossier'] = cglencode($info['dossier']);

		//	Pas de type Dossier, Dossier nouveau 
			if ($info['typedossier'] == -1 and $info['id_dossier'] == -1   )  {
				$error++;	
				$labelerror[] = $langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("typedossier"));
			}	
		if (!empty($info['titre'])) $info['titre'] = cglencode($info['titre']);
		if (!empty($info['description'])) $info['description'] = cglencode($info['description']);
		if (!empty($info['action'])) $info['action'] = cglencode($info['action']);

		// TIERS 
			
		if ($error == 0 ) {
			if (!( $info['dossanstiers'] == 'oui' and ($info['id_dossier'] <> -1 or empty($info['dossier']))) 
				and (!( $info['dossanstiers'] == 'oui' and  $info['id_dossier'] > 0 ))) // ne rien faire si on a un nouveau dossier sans tiers 
			{
				if (!empty($info['nvtiers']) or (!empty($info['idtiers']) and $info['idtiers'] <> -1)) {
					$ret = $line->Maj_tiers($info['idtiers'], $info['nvtiers'], $info['teltiers'], $info['telsuptiers'], $info['mailtiers'], $info['mailsuptiers'],  $user);
					if ($ret < 0) {
						$error++;
						$labelerror[] = $langs->trans("ErrorSQL").' - ' .$langs->transnoentitiesnoconv("ErrTiers");
					}	
					elseif ($ret > 0) {
						 $info['idtiers'] = $ret;
						 // il se peut que l'on vienne de changer le tiers du dossier
							$Reftiers = $info['idtiers'] ;	
					}
				}	
				elseif (empty($info['idtiers']) and $Reftiers <> $info['idtiers'] ) {
					// demande de suppression du référent du dossier (sans suppression du tiers) - Confirmation
					$form = new Form($db);
					$wline_echange = new cgl_echange($db);
					$wline_echange->fetch($info['idEchange']);
					$question=$langs->trans('ConfEffaceReferentQuest');
					$titre = $langs->trans('ConfEffaceReferent');
					//
					$formconfirm=$form->formconfirm($_SERVER['PHP_SELF'].'?typeliste='.$TypeListe.'&Refdossier='.$Refdossier.'&btaction=SupReferent'.'&Reftiers='.$Reftiers ,$titre,$question,'ConfSUPReferent','','no',2);
					unset ($form);
					unset ($wline_echange);
					print $formconfirm;	
				}
			}
		}
		
		// DOSSIER
		if ($error == 0 ) {
			$ret = 0;	
			if  ($info['dossanstiers'] == 'oui')  $widtiers = ''; // le cas de l'écran Général, en création de dossier réputé sans tiers
			else $widtiers = $info['idtiers'];
			$ret = $line->Maj_dossier($info['idDossier'], $info['dossier'], $info['typedossier'], $info['secteur'], $info['nb'],  $info['priorite'], $info['origine'], $widtiers, $user->id, $info['referent']);
			if ($ret < 0) {
				$error++;
				$labelerror[] = $langs->trans("ErrorSQL").' - ' .$langs->transnoentitiesnoconv("ErrMAJDossier");
			}	
			elseif ($ret > 0) 	$info['id_dossier'] = $ret;		
		}	
	}
		
	 if ( $mode == 2 ){
			// INTERLOCUTEUR
			if ((( !empty($info['idInterlocuteur']) and $info['idInterlocuteur'] <> -1 and empty($info['idtiers']) ) 
				or ( !empty($info['idInterlocuteur']) and $info['idInterlocuteur'] <> -1 and !empty($info['idtiers']) and $info['idtiers'] <> $info['idInterlocuteur'])
				or !empty($info['nvinterl']))
				and $info['nvtiers'] <> $info['nvinterl']	)	{

					$ret = $line->Maj_tiers($info['idInterlocuteur'], $info['nvinterl'], $info['telInterl'],'', $info['mailInterl'], '',  $user);
					if ($ret < 0) {
						$error++;
						$labelerror[] = $langs->trans("ErrorSQL").' - ' .$langs->transnoentitiesnoconv("ErrMAJInterlocuteur");
					}	
					elseif ($ret > 0) 		$info['idInterlocuteur'] = $ret;		
			}	
		}	
		
	// 	ECHANGE
	if ($error == 0   and !$fglEnchangevide) {
		$st_dateechg = dol_print_date(dol_now('tzuser'), '%Y%m%d');
		$idEchange = $info['idEchange'];
		if (!empty($idEchange)) $idcreateur = $line->fk_user_create;
		else  $idcreateur = $user->id;
		$idModificateur = $user->id;
		$ret = $line->Maj_echange( $idEchange, $info['idDossier'],  $info['action'], $info['titre'], $info['description'], $info['idInterlocuteur'] , $st_dateechg, $idcreateur, $idModificateur);
			if ($ret < 0 ) {
				$error++;
				$labelerror[] = $langs->trans("ErrorSQL").' - ' .$langs->transnoentitiesnoconv("ErrEchg");
			}	
			elseif ($ret > 0) 	$info['idEchange'] = $ret;
		}	
	if ($error == 0) $this->db->commit();
	else $this->db->rollback();
	return $labelerror;
	} //EnrTiers_Dossier_Echange

	/*
	*	Lister les dossiers du tiers $id
	*
	*	@param	int	$id	Identifiant du tiers
	*	@retour	tableau donnat le rowid et le libelle des dossiers de ce tiers
	*
	*/
	function ListDossByTiers($id)
	{
		
		$sql = "SELECT d.rowid, d.libelle , cp.color,  cp.label " ;
		$sql .= " FROM  " .MAIN_DB_PREFIX . "cglavt_dossier as d ";
		$sql .= " LEFT JOIN ". MAIN_DB_PREFIX . "cglavt_c_priorite as cp on fk_priorite = cp.rowid ";
		$sql .= " WHERE d.fk_soc =". $id;
		$sql .= " ORDER BY d.rowid desc ";

		$ListDoss = array();
		dol_syslog(get_class($this) . "::ListDossByTiers ", LOG_DEBUG);
		$resql = $this->db->query($sql);

		if ($resql) {

			$num = $this->db->num_rows($resql);
			$i = 0;
			while ( $i < $num ) {
				$obj = $this->db->fetch_object($resql);
				$ListDoss[$i]["id"] = $obj->rowid; 
				$ListDoss[$i]["libelle"] = $obj->libelle; 
				$ListDoss[$i]["couleur"] = '#'.$obj->color; 
				$ListDoss[$i]["priorite"] = $obj->label; 
				$i++;
			}
        }
		return $ListDoss ;
	} // ListDossByTiers

	function ConstitueModChgDos( $line, $line_echange )
	{
		
		global $langs, $bc, $user, $conf, $bull;
		global $Reftiers, $Refdossier, $search_typedossier;

		print '<style> .tdlabel {width:6em;
								font-weight:700}</style>';
		$ListContenu = array();
		$wf = new FormCglCommun($this->db);

		$out = $wf->html_BoiteSuiviClient($id,  $langs, $bull, 1);

		// recuperer l'identifiant d'echange
		$ListContenu[] = array ( "type"=>"hidden" ,"name"=>"Mod_idbull" , "id"=>"Mod_idbull", "value"=>0);
		$ListContenu[] = array ( "type"=>"hidden" ,"name"=>"Mod_iddtiers" , "id"=>"Mod_iddtiers", "value"=>$Reftiers);
		$ListContenu[] = array ( "type"=>"hidden" ,"name"=>"Mod_idddossier" , "id"=>"Mod_idddossier", "value"=>0);

		$ListContenu[] = array ("type"=>"other", "name"=>"Mod_listDossier",  "id"=>"Mod_listDossier","value" =>$out);
		$ListContenu[] = array ("type"=>"other", "label"=>"<menu>");
		$ListContenu[] = array ("type"=>"other","value" =>'<input type="submit" class="butAction" value="'.$langs->trans("Annuler").'" title="'.$langs->trans("Annuler").'">');
		$ListContenu[] = array ("type"=>"other","value" =>'&nbsp&nbsp&nbsp&nbsp' );
		$ListContenu[] = array ("type"=>"other","value" =>'<input type="submit" id="confirmBtn" class="butAction" value="'.$langs->trans("Enregistrer").'" title="'.$langs->trans("Enregistrer").'"  onclick="EnrBullDoss(this)" >');
		$ListContenu[] = array ("type"=>"other", "label"=>"</menu>");

	return $ListContenu;
	} //ConstitueModChgDos


} // Class
class cgl_echange extends CommonObject
{
	var $db;							//!< To store db handler

	var $id		;
	var $datec	;
	var $tms	;
	var $action			;
	var $interlocuteur	;
	var $id_interlocuteur	;
	var $nvinterl	;
	var $titre			;
	var $desc 			;
	var $fk_dossier	;
	var $fk_user_mod 	;
	var $PrenomMod;
	var $NomMod;
	var $fk_user_create ;
	var $PrenomCreateur	;
	var $NomCreateur;
	var $Interphone;
	var $Interemail;
	var $telmail;
	var $InterSupTel;
	var $InterSupMail;
	var $user_realise;
	var $fk_user_realise;
	var $date_realise;
	var $URfirstname;
	var $URlastname;
	var $NomDossier;



    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }// __construct

	function recupInfoEch ()
	{
		global  $arg_description, $arg_action, $id_contact, $arg_idEchange, $arg_id_dossier;
		global 		  $arg_nvinterl, $arg_idInterlo, $arg_nominterl, $arg_titre, $arg_mail, $arg_tel;

			$this->tms = dol_now('tzuser');

			$texte=str_replace(array("\r\n","\n"),'<br />',$arg_action);
			$this->action = $texte;

			//$this->id = $arg_idEchange;
			$this->nvinterl = $arg_nvinterl;
			$this->id_interlocuteur = $arg_idInterlo;
			$this->interlocuteur = $arg_nominterl;
			$this->titre = $arg_titre;
			$texte=str_replace(array("\r\n","\n"),'<br />',$arg_description);
			$this->desc = $texte;

			$this->Interphone = $arg_tel;
			$this->Interemail = $arg_mail;
			$this->fk_dossier = $arg_id_dossier;


	} // recupInfoEch
	function fetch($id_echange, $flgun = true)
	{
		$sql = "";
		$sql = "SELECT DISTINCT de.rowid, de.fk_dossier, de.datec, de.tms, de.fk_user_create, de.fk_user_mod,  de.titre, de.description, ";
		$sql .= " d.libelle as dossier, d.fk_secteur ,";
		$sql .= " de.action, de.date_realise, ";
		$sql .= " de.fk_soc as fk_interl, inter.rowid as id_interlocuteur, inter.nom as nom_interl, inter.phone as Interphone, inter.email as Interemail, ";
		$sql .= " ur.lastname as URlastname, ur.firstname as URfirstname";
		$sql .= " , cr.firstname as PrenomCreateur,cr.lastname as NomCreateur ";
		$sql .= " , md.firstname as PrenomMod,cr.lastname as NomMod ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "cglavt_dossierdet as de ";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cglavt_dossier as d ON de.fk_dossier = d.rowid";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as inter ON de.fk_soc = inter.rowid  ";
		$sql .=  " LEFT JOIN " . MAIN_DB_PREFIX . "user as ur  ON de.user_realise = ur.rowid";
		$sql .=  " LEFT JOIN " . MAIN_DB_PREFIX . "user as cr  ON de.fk_user_create = cr.rowid";
		$sql .=  " LEFT JOIN " . MAIN_DB_PREFIX . "user as md  ON de.fk_user_mod = md.rowid";
		$sql .= " WHERE de.entity IN (" . getEntity('agsession') . ")";
		$sql .= " AND de.rowid ='".$id_echange."'";

		dol_syslog(get_class($this) . "::fetch_unitaire sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		$num = $this->db->num_rows($resql);
		if ($num) {
				$obj = $this->db->fetch_object($resql);

				$this->id 				= $obj->rowid;
				$this->dossier			= $obj->dossier;
				$this->datec 			= $obj->datec;
				$this->tms 				= $obj->tms;
				$this->fk_user_create 	= $obj->fk_user_create;
				$this->fk_user_mod 		= $obj->fk_user_mod;
				$this->titre		= $obj->titre;
				$texte=str_replace(array("\r\n","\n"),'<br />',$obj->description);
				$this->desc		= $texte;
				if ($flgun == true) $this->action			= $obj->action;
				else $this->action=	$this->ActionCourante($this->fk_dossier);

				$texte=str_replace(array(chr(13).chr(10),chr(10)),'<br />',$this->action);
				$this->action=	$texte;

				$this->fk_dossier		= $obj->fk_dossier;
				$this->id_interlocuteur	= $obj->fk_interl;
				$this->interlocuteur	= $obj->nom_interl;
				$this->date_realise		= $obj->date_realise;
				$this->URfirstname		= $obj->URfirstname;
				$this->URlastname		= $obj->URlastname;
				$this->NomDossier		= $obj->dossier;
				$this->fk_secteur		= $obj->fk_secteur;
				$this->PrenomCreateur	= $obj->PrenomCreateur;
				$this->NomCreateur		= $obj->NomCreateur;
				$this->PrenomMod		= $obj->PrenomMod;
				$this->NomMod			= $obj->NomMod;
				$this->Interphone		= $obj->Interphone;
				$this->Interemail		= $obj->Interemail;
				$this->Interemail		= $obj->Interemail;


			$this->db->free($resql);
			return 1;
		}
		 else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);
			return - 1;
		}

	} //fetch

	function create( $user, $flgcommit=true)
	{
		$wfcom =  new CglFonctionCommune ($this->db);
		$texte = str_replace('<br />', "\n",$this->desc);
		$texte1 = str_replace('<br />', "\n",$this->action);
		$texte = $wfcom->cglencode($texte);
		$texte1 = $wfcom->cglencode($texte1);
		$titre = $wfcom->cglencode($this->titre);
		$sql = "INSERT INTO  " . MAIN_DB_PREFIX . "cglavt_dossierdet (  fk_user_create ,  fk_dossier,action, titre, description , datec, fk_soc, tms";
		$sql .= ' ) VALUES ( "'.$this->fk_user_create.'"';
		$sql .= ', "'.$this->fk_dossier.'"';
		$sql .= ', "'.$texte1.'"';
		$sql .= ', "'.$titre.'"';
		$sql .= ', "'.$texte.'"';
		$sql .= ', "'.$this->db->idate(dol_now('tzuser')).'"';
		if (!empty($this->id_interlocuteur) and $this->id_interlocuteur <> -1) $sql .= ', "'.$this->id_interlocuteur.'"';
					else $sql .= ', 0';
		$sql .= ', "'.$this->db->idate(dol_now('tzuser')).'"';
		$sql .= ' )';

		if ($flgcommit) $this->db->begin();// s'il n'est pas fait avant l'appel
		dol_syslog(get_class($this) . "::INSERT sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
            if ($flgcommit) $this->db->commit();
			$this->db->free($resql);
			$id = $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."cglavt_dossierdet");
			return $id;
		} else        {
            if ($flgcommit) $this->db->rollback();
            dol_syslog(get_class($this)."::INSERT fails ", LOG_WARNING);
            return -3;
        }
	} // create
	function update($id, $user, $flgcommit=true)
	{
		global $arg_idtiers;

		$echgl= new cgl_echange($this->db);
		$wfcom =  new CglFonctionCommune ($this->db);
		if (!empty($id)) 	$echgl->fetch($id);
		else $this->id_interlocuteur= $arg_idtiers;
		$this->tms = dol_now('tzuser');

		$flvirg = false;
		$sql = "UPDATE " . MAIN_DB_PREFIX . "cglavt_dossierdet ";
		$sql .= "set ";
		// inutilisé tant qu'on ne sait pas intercepter ""
		/*$errors = 0;
		if (!empty($this->desc) and $this->TestText($this->desc)) {
			setEventMessage("caractere' slash inverse' interdit dans la description" , "errors");
			$errors++;
		}
		if (!empty($this->action) and $this->TestText($this->action)) {
			setEventMessage("caractere' slash inverse' interdit dans l'action" , "errors");
			$errors++;
		}
		if (!empty($this->titre) and $this->TestText($this->titre)) {
			setEventMessage("caractere' slash inverse' interdit dans le titre" , "errors");
			$errors++;
		}
		if ($errors > 0) return -1;
		*/
		$texte=str_replace('<br />', "\n",$this->desc);
		$texte1 = str_replace('<br />', "\n",$this->action);
		$texte = $wfcom->cglencode($texte);
		$texte1 = $wfcom->cglencode($texte1);
		$titre = $wfcom->cglencode($this->titre);

		/* pour reparer bug ' '==> '_'
		$texte = dol_string_nospecial($texte,' ',array("_"))	;
		$texte1 = dol_string_nospecial($texte1,' ',array("_"))	;
		$titre = dol_string_nospecial($titre,' ',array("_"))	;
		*/

		$texte1=str_replace('<br />', "\n",$this->action);
		if (!empty($texte1)  and (empty($echgl->action) or $echgl->action <> $texte1)) { $sql .= 'action = "'.$texte1.'"'; $flvirg = true;};
		if (!empty($this->titre)  and (empty($echgl->titre) or $echgl->titre <> $titre)) {if ($flvirg == true) $sql .= ',  '; $sql .= 'titre = "'.$titre.'"'; $flvirg = true;};
		if (!empty($texte)  and (empty($echgl->desc) or $echgl->desc <> $texte)) {if ($flvirg == true) $sql .= ',  '; $sql .= 'description = "'.$texte.'"'; $flvirg = true;};
		if (!empty($this->id_interlocuteur) and $this->id_interlocuteur <> -1 and (empty($echgl->id_interlocuteur) or  $echgl->id_interlocuteur <> $this->id_interlocuteur)) {if ($flvirg == true) $sql .= ',  '; $sql .= ' fk_soc ="'.$this->id_interlocuteur.'"'; $flvirg = true;};
		if ( (empty($echgl->fk_user_mod) or $echgl->fk_user_mod <> $user->id)) {if ($flvirg == true) $sql .= ',  '; $sql .= ' fk_user_mod = "'.$user->id.'"'; $flvirg = true;};
		if ($flvirg == true) {
			$sql .= ", tms = '".$this->db->idate($this->tms)."'";
			}
		$sql .= " WHERE rowid = '".$id."'";
		unset($dosl);
		unset ($wfcom);
		if ($flvirg == true) {
			if ($flgcommit) $this->db->begin();// s'il n'est pas fait avant l'appel
			dol_syslog(get_class($this) . "::update sql=" . $sql, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if ($resql) {
				if ($flgcommit) $this->db->commit();
				$this->db->free($resql);
				return 1;
			} else        {
				if ($flgcommit) $this->db->rollback();
				dol_syslog(get_class($this)."::Update fails ", LOG_WARNING);
				return -3;
			}
		}
	} // update
	function update_realise($id)
	{
		global $user;

		$sql = "UPDATE " . MAIN_DB_PREFIX . "cglavt_dossierdet ";
		$sql .= "set date_realise='".$this->db->idate(dol_now('tzuser'))."'";
		$sql .= ", tms ='".$this->db->idate(dol_now('tzuser'))."'";
		$sql .= ", user_realise='".$user->id."'";
		$sql .= " WHERE rowid ='".$id."'";

		dol_syslog(get_class($this) . "::update_realise sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$this->db->commit();
			return 1;
		} else        {
			$this->db->rollback();
			dol_syslog(get_class($this)."::Update fails ", LOG_WARNING);
			return -3;
		}

	}//update_realise
	function update_non_realise($id)
	{
		global $user;

		$sql = "UPDATE " . MAIN_DB_PREFIX . "cglavt_dossierdet ";
		$sql .= "set date_realise=null";
		$sql .= ", user_realise=null";
		$sql .= " WHERE rowid ='".$id."'";


		dol_syslog(get_class($this) . "::update_non_realise sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$this->db->commit();
			return 1;
		} else        {
			$this->db->rollback();
			dol_syslog(get_class($this)."::Update fails ", LOG_WARNING);
			return -3;
		}

	}//update_non_realise
	function delete()
	{
		$this->db->begin();

		$sql = "DELETE FROM  " . MAIN_DB_PREFIX . "cglavt_dossierdet ";
		$sql .= " WHERE rowid = '".$this->id."'";

		$resql = $this->db->query($sql);
		if ($resql) {
			$this->db->free($resql);
			$this->db->commit();
			return 1;
		} else        {
			$this->db->rollback();
            dol_syslog(get_class($this)."::delete fails ", LOG_WARNING);
            return -3;
        }
	} // delete

	function ActionCourante ($id_dossier)
	{
		$sql = "";
		$sql = "SELECT  action ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "cglavt_dossierdet as d ";
		$sql .= " WHERE  d.rowid = (select max(t2.rowid)from " . MAIN_DB_PREFIX . "cglavt_dossierdet as t2  where t2.fk_dossier = '".$id_dossier."')";

		dol_syslog(get_class($this) . "::Action Courante sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			if ($num) {
					$obj = $this->db->fetch_object($resql);

					$ret	= $obj->action;

					$ret=str_replace(array("\r\n","\n"),'<br />',$obj->action);
				$this->db->free($resql);
				return $ret;
			}
		}

	} // ActionCourante
	function TestText($string)
	{
		$char = '\\';
		if (strlen($string) > 0) {
			for ($i=0; $i<strlen($string); $i++) {
				if ($string[$i] == chr(92)) break;
				}
				if ($i < strlen($string)) return true;
				else return false;
		}
		else return false;
	} //NettoieText
// Fenetre modale

function AfficheModaleChgDoss($ListContenu)
{	
	$this->AfficheModale( $ListContenu , 'fmodalDialogChgDoss',  'titreDoss', '<p>');
} //AfficheModaleChgDoss

function AfficheModaleEchg( $ListContenu)
{	
	$this->AfficheModale( $ListContenu , 'fmodalDialogEchg',  'titre', '<p>');
}//AfficheModaleEchg
function AfficheModale( $ListContenu , $htmlnameDialog, $id_titre, $SepLigne)
{
	global $db;
	$form = New Form($db);
	
	print ' 
	<dialog id="'.$htmlnameDialog.'" style="background-color:#FAF0E6" close>
	  <form id="form"+'.$htmlnameDialog.' method="dialog" >

	  <h1 id="'.$id_titre.'" > 	 </h1>
	  <hr>';

	if (is_array($ListContenu) && !empty($ListContenu)) {
		// First add hidden fields and value
		foreach ($ListContenu as $key => $input) {
			if (is_array($input) && !empty($input)) {
				if ($input['type'] == 'hidden') {
					print '<input type="hidden" id="'.dol_escape_htmltag($input['name']).'" name="'.dol_escape_htmltag($input['name']).'" value="'.dol_escape_htmltag($input['value']).'">'."\n";
				}
			}
		}
	}

	// Now add questions
	$moreonecolumn = '';
	//$more .= '<div class="tagtable paddingtopbottomonly centpercent noborderspacing">'."\n";
	if (is_array($ListContenu) && !empty($ListContenu)) {
		// First add hidden fields and value
		foreach ($ListContenu as $key => $input) {
			if (is_array($input) && !empty($input)) {
				$size = (!empty($input['size']) ? ' size="'.$input['size'].'"' : '');	// deprecated. Use morecss instead.
				$morecss = (!empty($input['morecss']) ? ' '.$input['morecss'] : '');

				if ($input['type'] == 'text') {
					$moreattr = (!empty($input['moreattr']) ? ' '.$input['moreattr'] : '');
					//print $SepLigne;
					print '<div class="tagtr"><div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">';
					print '<b>'.$input['label'].'</b></div><div class="tagtd">';
					print '<input type="text" class="flat'.$morecss.'" id="'.dol_escape_htmltag($input['name']).'" name="'.dol_escape_htmltag($input['name']).'"'.$size.' value="'.$input['value'].'"'.$moreattr.' /></div></div>';
				}
				elseif ($input['type'] == 'print') {
					if ($input['separateur'] <> "non") print $SepLigne;
					print '<div class="tagtd '.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">'.$input["label"].'<span'.$morecss.'" id="'.dol_escape_htmltag($input['name']).'" name="'.dol_escape_htmltag($input['name']).'"'.$moreattr.'>'.$input["value"].'</span></div>';
				}
				elseif ($input['type'] == 'password')	{
					print '<div class="tagtr"><div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">'.$input['label'].'</div><div class="tagtd"><input type="password" class="flat'.$morecss.'" id="'.dol_escape_htmltag($input['name']).'" name="'.dol_escape_htmltag($input['name']).'"'.$size.' value="'.$input['value'].'"'.$moreattr.' /></div></div>'."\n";
				}
				elseif ($input['type'] == 'textarea') {
					$moreattr = (!empty($input['moreattr']) ? ' '.$input['moreattr'] : '');
					/*$more .= '<div class="tagtr"><div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">'.$input['label'].'</div><div class="tagtd">';
					$more .= '<textarea name="'.$input['name'].'" class="'.$morecss.'"'.$moreattr.'>';
					$more .= $input['value'];
					$more .= '</textarea>';
					$more .= '</div></div>'."\n";*/
					//print '<div class="margintoponly">';
					print $SepLigne;
					print  '<div class="tagtr"><div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">'.$input['label'].'</div><div class="tagtd">';
					print  '<textarea name="'.dol_escape_htmltag($input['name']).'" id="'.dol_escape_htmltag($input['name']).'" class="'.$morecss.'"'.$moreattr.'>';
					print  $input['value'];
					print  '</textarea>';
					print  '</div></div>'."\n";
				}
				elseif ($input['type'] == 'select') {
					if (empty($morecss)) {
						$morecss = 'minwidth100';
					}
					$moreattr = (!empty($input['moreattr']) ? ' '.$input['moreattr'] : '');

					$show_empty = isset($input['select_show_empty']) ? $input['select_show_empty'] : 1;
					$key_in_label = isset($input['select_key_in_label']) ? $input['select_key_in_label'] : 0;
					$value_as_key = isset($input['select_value_as_key']) ? $input['select_value_as_key'] : 0;
					$translate = isset($input['select_translate']) ? $input['select_translate'] : 0;
					$maxlen = isset($input['select_maxlen']) ? $input['select_maxlen'] : 0;
					$disabled = isset($input['select_disabled']) ? $input['select_disabled'] : 0;
					$sort = isset($input['select_sort']) ? $input['select_sort'] : '';

					print '<div class="tagtr"><div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">';
					if (!empty($input['label'])) {
						print $input['label'].'</div><div class="tagtd left">';
					}
					print $form->selectarray($input['name'], $input['values'], $input['default'], $show_empty, $key_in_label, $value_as_key, $moreattr, $translate, $maxlen, $disabled, $sort, $morecss);
					print '</div></div>'."\n";
				}
				elseif ($input['type'] == 'checkbox') {
					print '<div class="tagtr">';
					print '<div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">'.$input['label'].' </div><div class="tagtd">';
					print '<input type="checkbox" class="flat'.$morecss.'" id="'.dol_escape_htmltag($input['name']).'" name="'.dol_escape_htmltag($input['name']).'"'.$moreattr;
					if (!is_bool($input['value']) && $input['value'] != 'false' && $input['value'] != '0' && $input['value'] != '') {
						print ' checked';
					}
					if (is_bool($input['value']) && $input['value']) {
						print ' checked';
					}
					if (isset($input['disabled'])) {
						print ' disabled';
					}
					print ' /></div>';
					print '</div>'."\n";
				}
				elseif ($input['type'] == 'radio') {
					$i = 0;
					if (!empty($input['values'])) {
						foreach ($input['values'] as $selkey => $selval) {
							print '<div class="tagtr">';
							if ($i == 0) {
								print '<div class="tagtd'.(empty($input['tdclass']) ? ' tdtop' : (' tdtop '.$input['tdclass'])).'">'.$input['label'].'</div>';
							} else {
								print '<div clas="tagtd'.(empty($input['tdclass']) ? '' : (' "'.$input['tdclass'])).'">&nbsp;</div>';
							}
							print '<div class="tagtd'.($i == 0 ? ' tdtop' : '').'"><input type="radio" class="flat'.$morecss.'" id="'.dol_escape_htmltag($input['name'].$selkey).'" name="'.dol_escape_htmltag($input['name']).'" value="'.$selkey.'"'.$moreattr;
							if ($input['disabled']) {
								$more .= ' disabled';
							}
							if (isset($input['default']) && $input['default'] === $selkey) {
								$more .= ' checked="checked"';
							}
							print ' /> ';
							print '<label for="'.dol_escape_htmltag($input['name'].$selkey).'">'.$selval.'</label>';
							print '</div></div>'."\n";
							$i++;
						} // foreach
					}
				 }
				elseif ($input['type'] == 'date') {
					print '<div class="tagtr"><div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">'.$input['label'].'</div>';
					print '<div class="tagtd">';
					$addnowlink = (empty($input['datenow']) ? 0 : 1);
					print $this->selectDate($input['value'], $input['name'], 0, 0, 0, '', 1, $addnowlink);
					print' </div></div>'."\n";
					$ListContenu[] = array('name'=>$input['name'].'day');
					$ListContenu[] = array('name'=>$input['name'].'month');
					$ListContenu[] = array('name'=>$input['name'].'year');
					$ListContenu[] = array('name'=>$input['name'].'hour');
					$ListContenu[] = array('name'=>$input['name'].'min');
				}
				elseif ($input['type'] == 'other') {
					print '<div class="tagtd'.(empty($input['tdclass']) ? '' : (' '.$input['tdclass'])).'">';
					if (!empty($input['label'])) {
						print $input['label'].'</div><div class="tagtd" name="'.dol_escape_htmltag($input['name']).'" id="'.dol_escape_htmltag($input['name']).'">';
					}
					print $input['value'];
					print '</div>'."\n";
				}
				elseif ($input['type'] == 'div') {
					print $input['value'];
				}

				elseif ($input['type'] == 'onecolumn') {
					$moreonecolumn .= '<div class="margintoponly">';
					$moreonecolumn .= $input['value'];
					$moreonecolumn .= '</div>'."\n";
				}
				elseif ($input['type'] == 'hidden') {
					// Do nothing more, already added by a previous loop
				}
				elseif ($input['type'] == 'separator') {
					print '<br>';

		//CCA 12/12/2018 - Ancre
				}
				elseif ($input['type'] == 'ancre') {
					$urlmoreancre.='#';
					$urlmoreancre.=$input['value'];
		// Fin Modif CCA 12/12/2018


				}
				else {
					print 'Error type '.$input['type'].' for the confirm box is not a supported type';
				}
			}
		}
	}
	//$more .= '</div>'."\n";
	print $moreonecolumn;

	 print '
	  </form>
	</dialog>
';
} // AfficheModale
 function ScriptModale()
 {
	print   '<script>
 function OuvreModEchg_Mod(o, id_echange, id_dossier, id_bull){
		document.getElementById("fmodalDialogEchg").style.width="70%";
		let dateCrEch;
		let dateModEch ;
		let titreech ;
		let contenu ;
		let action ;
		let id_interlocuteur;
		let interlocuteur;
		let fk_secteur;
		let idEchange ;
		let fk_tiers ;
		let fk_priorite;
		let UserCreEch ;
		let UserModEchPrenom  ;
		let action_realisee ;
		let AffCreateur ;
		let Aff1Createur ;
		let tierstel ;
		let tiersmail ;
		let tableau;
			
		if (id_echange>0) {
			let retour =  chargeEchange( id_echange, id_dossier);
			tableau = retour.split("?@",28);
			dateCrEch = tableau[2];
			dateModEch = tableau[3];
			titreech =  tableau[6];
			contenu = tableau[7];
			action =  tableau[8];
			id_interlocuteur =  tableau[9];
			interlocuteur =  tableau[10];
			fk_secteur =  tableau[13];
			idEchange =  tableau[14];
			fk_tiers =  tableau[15];
			fk_priorite =  tableau[16];
			UserCreEch = tableau[17];
			UserModEchPrenom  =  tableau[18];
			action_realisee =  tableau[20];
			AffCreateur = "Cree le ".concat(dateCrEch).concat(" par ").concat(UserCreEch);
			 Aff1Createur = ", repris par ".concat(UserModEchPrenom);
			 tierstel  =  tableau[21];
			 tiersmail =  tableau[22];
		}
		else if (id_echange == 0) {
			let retour =  chargeEchange( 0, id_dossier);
			tableau = retour.split("?@",27);
			
		}
		let dossier = tableau[0];
		let iddossier = tableau[1];
		let secteur  =  tableau[24];
		let priorite  =  tableau[25];
		let coulpriorite =  tableau[26];
		let fmodalDialog = document.getElementById("fmodalDialogEchg");
		if (typeof fmodalDialog.showModal === "function") {
			if (id_echange > 0)  titre = "Modification échange";
			else 	titre = "Nouvel echange";
				document.getElementById("titre").innerHTML = titre;
			fmodalDialog.showModal();
			if (id_echange > 0) {
				document.getElementById("Mod_idEchange").innerHTML = idEchange;

				document.getElementById("Mod_InfoCreat").innerHTML=AffCreateur+Aff1Createur
				document.getElementById("Mod_InfoCreat").style.visibility="visible";
				document.getElementById("Mod_titreEchg").value=titreech;
				/* remplacer <br /> par \n */	
				let aff_contenu = replaceAll("<br />","\n", contenu);
				document.getElementById("Mod_desc").value=aff_contenu;
				/* document.getElementById("Mod_tiersmail_aff").style.visibility="visible";
				document.getElementById("Mod_tiersmail_aff").innerHTML=tiersmail;
				document.getElementById("Mod_tiersmail_sais").style.visibility="hidden";
				document.getElementById("Mod_tierstel_aff").style.visibility="visible";
				document.getElementById("Mod_tierstel_aff").innerHTML=tierstel;
				document.getElementById("Mod_tierstel_sais").style.visibility="hidden";*/

				document.getElementById("search_Mod_idtiersLig").value=interlocuteur;
				if (action_realisee != "") {
					document.getElementById("Mod_real").innerHTML=action_realisee;
					document.getElementById("Mod_actionReal").innerHTML=action;
					document.getElementById("Mod_actionNonReal").type="hidden";
					document.getElementById("Mod_actionReal").style.visibility="visible";
				}
				else {
					document.getElementById("Mod_real").innerHTML="&nbsp";
					document.getElementById("Mod_actionReal").style.visibility="hidden";
					document.getElementById("Mod_actionNonReal").type="text";
					document.getElementById("Mod_actionNonReal").value=action;
				}
			}
			else{		
				
				document.getElementById("Mod_InfoCreat").style.visibility="hidden";
				document.getElementById("Mod_idEchange").innerHTML = 0;
				document.getElementById("Mod_titreEchg").value="";
				document.getElementById("Mod_desc").value="";
				/* document.getElementById("Mod_tiersmail_aff").style.visibility="hidden";
				document.getElementById("Mod_tiersmail_sais").value="";
				document.getElementById("Mod_tiersmail_sais").style.visibility="visible";
				document.getElementById("Mod_tierstel_aff").style.visibility="hidden";
				document.getElementById("Mod_tierstel_sais").value="";
				document.getElementById("Mod_tierstel_sais").style.visibility="visible"; */
				

				document.getElementById("search_Mod_idtiersLig").value="";
				document.getElementById("Mod_real").innerHTML="&nbsp";
				document.getElementById("Mod_actionReal").style.visibility="hidden";
				document.getElementById("Mod_actionNonReal").type="text";
				document.getElementById("Mod_actionNonReal").value="";

			}			
			document.getElementById("Mod_idtiers").innerHTML=fk_tiers;
			document.getElementById("Mod_priorite").style.color="#"+coulpriorite;
			document.getElementById("Mod_priorite").innerHTML=priorite;
			document.getElementById("Mod_dossier").innerHTML=dossier;
			document.getElementById("Mod_iddossier").innerHTML=iddossier;
			document.getElementById("Mod_idbull").innerHTML=id_bull;
			
			document.getElementById("Mod_secteur").innerHTML=secteur;
			/* document.getElementById("Mod_nvtiersLig").style.color="grey";
			document.getElementById("Mod_nvtiersLig").value="Nouveau tiers"; */
		}
		else 		alert ("erreur");
	};
	function  replaceAll(recherche, remplacement, chaineAModifier)
{
return chaineAModifier.split(recherche).join(remplacement);
}
	</script>';
 print	'<script>
	function  EnrModal (o) {
		let argtitre =	"titre=".concat(document.getElementById("Mod_titreEchg").value);
		let argaction =	"&action=".concat(document.getElementById("Mod_actionNonReal").value);
		let argdesc =	"&description=".concat(document.getElementById("Mod_desc").value);
		//let argnvInterlocuteur =	"&nvInterlocuteur=".concat(document.getElementById("Mod_nvtiersLig").value);
		let argidInterlocuteur =	"&idInterlocuteur=".concat(document.getElementById("Mod_idtiersLig").value);
			
		//let argtelInterl =	"&telInterl=".concat(document.getElementById("Mod_tierstel_sais").value);
		//let argmailInterl =	"&mailInterl=".concat(document.getElementById("Mod_tiersmail_sais").value);
		let argidDossier =	"&idDossier=".concat(document.getElementById("Mod_iddossier").innerHTML);
		let argidEchange =	"&idEchange=".concat(document.getElementById("Mod_idEchange").innerHTML);
		let argidtiers =	"&idtiers=".concat(document.getElementById("Mod_idtiers").innerHTML);
		url="'.DOL_MAIN_URL_ROOT.'/custom/CahierSuivi/suivi_client/ReqMajEchange.php?";
		//url = url.concat(argtitre+argaction+argdesc+argnvInterlocuteur+argidInterlocuteur+argtelInterl+argmailInterl);
		url = url.concat(argtitre+argaction+argdesc+argidInterlocuteur);
		url = url.concat(argidDossier+argidEchange+argidtiers);
		url = encodeURI(url);
		let	Retour = creerobjet(url);		
		
		let iddoss=document.getElementById("Mod_iddossier").innerHTML;
		let id_bull=document.getElementById("Mod_idbull").innerHTML;
		let RetourAff=ChargePaveSuivi(id_bull, iddoss );
		tab = RetourAff.split( "!@&!",2);
		document.getElementById("PaveSuivi").innerHTML = tab[0];
		let obj_AffDossFondDePage=document.getElementById("AffDossFondDePage");
		/* if (obj_AffDossFondDePage === null) { alert ("obj_AffDossFondDePage absent");} */
		/* else	document.getElementById("AffDossFondDePage").innerHTML = tab[1]; */
		if (obj_AffDossFondDePage != null) document.getElementById("AffDossFondDePage").innerHTML = tab[1];

		
	};
	
	</script>';
 print	'<script>
	function creerobjet(fichier)
	{
		if(window.XMLHttpRequest)
			xhr_object = new XMLHttpRequest();
		else if(window.ActiveXObject)
			xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
		else
			return(false);
		xhr_object.open("GET", fichier, false);
		xhr_object.send(null);
		if(xhr_object.readyState == 4)
			return(xhr_object.responseText);
		else
			return(false);

	};
	</script>';
 print	'<script>
	function chargeEchange(id_echange, id_dossier)
	{
 		let url="'.DOL_MAIN_URL_ROOT.'/custom/CahierSuivi/suivi_client/ReqEchange.php?id_echange="+id_echange+"&id_dossier="+id_dossier;
		var	Retour = creerobjet(url);
		return Retour;

	};


	</script>';

} //ScriptModale

function Chargement($id_echange, $id_dossier)
{
	global $db;

	$line = new cgl_dossier ($db);
	$line_echange = new cgl_echange ($db);
	if 	(empty($id_echange)) {
		$line->fetch($id_dossier);
		$line_echange = null;
	}
	else {
		$line_echange->fetch($id_echange);
		$line->fetch($line_echange->fk_dossier);
	}
	$retour=array();
	$retour[] = $line;
	$retour[] = $line_echange;
	return $retour;
} // Chargement

function ConstitueModlEchg_Mod( $line, $line_echange )
{
	
	global $langs, $bc, $user, $conf;
	global $Reftiers, $Refdossier, $search_typedossier;

	print '<style> .tdlabel {width:6em;
							font-weight:700}</style>';
	$ListContenu = array();
	$bull = new Bulletin($this);
	$wformsuivi = new FormCglSuivi ($this->db);

	// recuperer l'identifiant d'echange
	$ListContenu[] = array ( "type"=>"hidden" ,"name"=>"Mod_idEchange" , "id"=>"Mod_idEchange", "value"=>0);
	$ListContenu[] = array ( "type"=>"hidden" ,"name"=>"Mod_idtiers" , "id"=>"Mod_idtiers", "value"=>"");
	$ListContenu[] = array ( "type"=>"hidden" ,"name"=>"Mod_iddossier" , "id"=>"Mod_iddossier", "value");
	$ListContenu[] = array ( "type"=>"hidden" ,"name"=>"Mod_idbull" , "id"=>"Mod_idbull", "value"=>"");

	// Info concernant l'échange
	$ListContenu[] = array ( "type"=>"print" ,"name"=>"Mod_InfoCreat" , "id"=>"Mod_InfoCreat",  "value"=>"");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"<hr>");

	// Info concernant le dossier
	$ListContenu[] = array ( "type" =>"div", "value"=>"<p></p>");
	$ListContenu[] = array ( "type"=>"print" , "tdclass"=>"tdlabel", "value"=>"Dossier:", "separateur"=>"non");
//	$ListContenu[] = array ( "type"=>"other" , "value"=>$wformsuivi->select_dossier($line->id,'Mod_dossier',$line->nvdossier,'Mod_nvdossier','R', $filtreDossier,1,1,1,'', '', 0, 0));
	$ListContenu[] = array ( "type"=>"print" , "id" => "Mod_dossier", "name"=>"Mod_dossier", "value"=>"Dossier", "separateur"=>"non");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");

	$ListContenu[] = array ( "type"=>"print" , "tdclass"=>"tdlabel", "value"=>"Secteur:", "separateur"=>"non");
	$ListContenu[] = array ( "type"=>"print" , "id" => "Mod_secteur", "name"=>"Mod_secteur", "value"=>"", "separateur"=>"non");

//	$ListContenu[] = array ( "type"=>"other" , "value"=>$wformsuivi->select_secteur( $line->fk_secteur,'Mod_secteur','',1,1,1,0, '', 0, 1, '', false));
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");

	$wnomObjetColor = 'td'.$line->id;
	$wnomObjetColor .= '-'.$line_echange->id;
	$ClassDossier = 'suivitiers'.$line->id;
	if (empty($line->coulpriorite)) $bgcolor = "FFFFFF"; else $bgcolor = $line->coulpriorite;
	//$value = '<td  class="'.$ClassDossier.'" '.$attrib.' align=left '.$size_statut.' style="background-color:#'.$bgcolor.';" id="'.$wnomObjetColor.'">';
	//$value =  '<div id="div_priorite">';
	$wtaction =  'saisie' ;
	$value .=  $wformsuivi->select_priorite($line->fk_priorite,'Mod_priorite',0,$wnomObjetColor,'', 1,1,'',0, '', 0, 1,'saisie',"width:100%;",1);
	//$value .=  '</div>';
	$ListContenu[] = array ( "type"=>"print" , "tdclass"=>"tdlabel", "value"=>"Priorité:", "separateur"=>"non");
	$ListContenu[] = array ( "type"=>"print" , "id" => "Mod_priorite", "name"=>"Mod_priorite", "value"=>"", "separateur"=>"non");
//	$ListContenu[] = array ( "type"=>"other" ,  "value"=>$value);
	$ListContenu[] = array ( "type"=>"div" , "value"=>"<p></p>");

	$ListContenu[] = array ( "type"=>'text' , "name"=>"Mod_titreEchg" , "id"=>"Mod_titreEchg","tdclass"=>"tdlabel",  "label"=>"Titre:", "value"=> $line_echange->titre, "moreattr"=>"size=100");
	$ListContenu[] = array ( "type" =>"textarea",   "name"=>"Mod_desc" , "id"=>"Mod_desc","tdclass"=>"tdlabel","label"=>"Description:", "value"=>$line_echange->desc, "moreattr"=>"cols=120  rows=4 wrap='soft'");

	$ListContenu[] = array ( "type" =>"div", "value"=>"<p></p>");
	$ListContenu[] = array ( "type" =>"div", "value"=>"<div><div style='float:left'>");
		$ListContenu[] = array ( "type"=>"print" , "tdclass"=>"tdlabel", "value"=>"Action:", "separateur"=>"non");

	$ListContenu[] = array ( "type" =>"div", "value"=>"</div><div style='float:left'>");
	$ListContenu[] = array ( "type"=>"text" , "name"=>"Mod_actionNonReal" , "id"=>"Mod_actionNonReal", "value"=> $line->action_courante, "moreattr"=>"size=100");
	$ListContenu[] = array ( "type" =>"div", "value"=>"</div><div style='float:left'>");
	$ListContenu[] = array ( "type"=>"print" , "name"=>"Mod_actionReal" , "id"=>"Mod_actionReal", "value"=> $line->action_courante, "moreattr"=>"size=100", "separateur"=>"non");
	$ListContenu[] = array ( "type" =>"div", "value"=>"</div><div>");

	$ListContenu[] = array ( "type"=>"print" , "name"=>"Mod_real" , "id"=>"Mod_real", "value"=>$value, "separateur"=>"non");

	$ListContenu[] = array ( "type" =>"div", "value"=>"</div ></div>");
	$ListContenu[] = array ( "type" =>"div", "value"=>"<p></p>");
	$ListContenu[] = array ( "type" =>"div", "value"=>"<p></p>");

	$ListContenu[] = array ( "type" =>"div", "value"=>"<div><div style='float:left'>");
	$ListContenu[] = array ( "type"=>"print" , "tdclass"=>"tdlabel", "value"=>"Interlocuteur:", "separateur"=>"non");
//	$ListContenu[] = array ( "type"=>"other" , name="select_interlocuteur", id="select_interlocuteur", "value"=>$wformsuivi->select_client( $line_echange->interlocuteur,'','Mod_idtiersLig','Nouvel Interlocuteur','Mod_nvtiersLig','R','inter','',1,'',0,$events, 0,  '', 0, $line_echange->id,0));
	$ListContenu[] = array ( "type"=>"other" , "value"=>$wformsuivi->select_client( $line_echange->interlocuteur,'','Mod_idtiersLig','','','R','inter','',1,'',0,$events, 0,  '', 0, $line_echange->id,0));
	$ListContenu[] = array ( "type" =>"div", "value"=>"</div><div style=';width:20%;'>");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");

	$ListContenu[] = array ( "type" =>"div", "value"=>"</div>");
/*
	$ListContenu[] = array ( "type" =>"div", "value"=>"<div  style='float:left'>");
	$ListContenu[] = array ( "type"=>"print" , "tdclass"=>"tdlabel","name"=>"Mod_tierstel_label" , "id"=>"Mod_tierstel_label", "value"=>"Tel:", "separateur"=>"non");
	$ListContenu[] = array ( "type" =>"div", "value"=>"</div ><div  style='float:left'>");
	$ListContenu[] = array ( "type"=>"print" , "id" => "Mod_tierstel_aff", "name"=>"Mod_tierstel_aff", "value"=>"", "separateur"=>"non", "separateur"=>"non");
	$ListContenu[] = array ( "type" =>"div", "value"=>"</div ><div  style='float:left'>");
	$ListContenu[] = array ( "type"=>"text" , "name"=>"Mod_tierstel_sais" , "id"=>"Mod_tierstel_sais","value"=> $line_echange->Interphone, "separateur"=>"non");
	$ListContenu[] = array ( "type" =>"div", "value"=>"</div ><div  style='float:left'>");
	$ListContenu[] = array ( "type"=>"print" , "tdclass"=>"tdlabel","name"=>"Mod_tiersmail_label" , "id"=>"Mod_tiersmail_label", "value"=>"Mail:", "separateur"=>"non");
	$ListContenu[] = array ( "type" =>"div", "value"=>"</div ><div  style='float:left'>");
	$ListContenu[] = array ( "type"=>"print" , "id" => "Mod_tiersmail_aff", "name"=>"Mod_tiersmail_aff", "value"=>"", "separateur"=>"non");
	$ListContenu[] = array ( "type" =>"div", "value"=>"</div ><div  style='float:left'>");
	$ListContenu[] = array ( "type"=>"text" , "name"=>"Mod_tiersmail_sais" , "id"=>"Mod_tiersmail_sais",  "value"=> $line_echange->Interemail, "separateur"=>"non");
	$ListContenu[] = array ( "type" =>"div", "value"=>"</div ></div>");
*/
	$ListContenu[] = array ( "type" =>"div", "value"=>"<div style=';width:100%;heigth:20px;'><div >");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");
	$ListContenu[] = array ( "type"=>"other" , "value"=>"&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp");

	$ListContenu[] = array ("type"=>"other", "label"=>"<menu>");
	$ListContenu[] = array ("type"=>"other","value" =>'<input type="submit" class="butAction" value="'.$langs->trans("Annuler").'" title="'.$langs->trans("Annuler").'">');
	$ListContenu[] = array ("type"=>"other","value" =>'&nbsp&nbsp&nbsp&nbsp' );
	$ListContenu[] = array ("type"=>"other","value" =>'<input type="submit" id="confirmBtn" class="butAction" value="'.$langs->trans("Enregistrer").'" title="'.$langs->trans("Enregistrer").'"  onclick="EnrModal(this)" >');
	$ListContenu[] = array ("type"=>"other", "label"=>"</menu>");
	$ListContenu[] = array ("type"=>"div", "label"=>"</div>");


return $ListContenu;
} //ConstitueModlEchg_Mod


}
?>
