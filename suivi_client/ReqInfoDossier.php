<?php 
/*
 * Version CAV - 2.7 - été 2022 - Migration Dolibarr V15
 *
*/
		
 		require_once('../../../main.inc.php');
		require_once(DOL_DOCUMENT_ROOT.'/core/db/mysqli.class.php');
		require_once('../class/suivi_client.class.php');
		require_once('../class/html.suivi_client.class.php');
		$ID = $_GET["ID"];
		//$conn = new DoliDBMysqli('mysqli', 'localhost', 'dolibarrmysql' , '', 'dolibarr', 100);
	
		$sql ="SELECT fk_secteur,  ssect.label as secteur, fk_priorite, nb, d.datec, fk_createur, u.lastname, ";
		$sql .= "u.firstname, fk_typedossier, styd.label as  typedossier, fk_origine, cr.label as origine,  cr.label as origine, st.rowid as idtiers, nom as tiers , spriorite.color  ";
		$sql .= "FROM " . MAIN_DB_PREFIX . "cglavt_dossier as d ";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cglavt_c_typedossier as styd on fk_typedossier = styd.rowid";	
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cglavt_c_secteur as ssect on d.fk_secteur = ssect.rowid";	
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "cglavt_c_priorite as spriorite on d.fk_priorite = spriorite.rowid";	
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."c_input_reason as cr on cr.rowid = fk_origine";	
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as st on d.fk_soc = st.rowid";		
		$sql .= " , " . MAIN_DB_PREFIX . "user as u	";		
		$sql .= "WHERE d.rowid='".$ID."'";
	$sql .= " AND d.fk_createur = u.rowid ";	
	
        $rsql = $db->query($sql); //or die("Erreur SQL.<br>$sql<br>".mysql_error());
if ($rsql) { 
	 $num = $db->num_rows($rsql);
	 $i=0;	 
       //$outarray=array();
		$obj = $db->fetch_object($rsql); 
		$rep = $obj->fk_secteur.'?'.$obj->secteur.'?'.$obj->fk_priorite.'?'.$obj->nb.'?'.$obj->datec.'?'.$obj->firstname;
		$rep .= '?'.$obj->fk_typedossier.'?'.$obj->fk_origine.'?'.$obj->origine;
	}
	
$db->free($rsql); 
	$w= new FormCglSuivi ($db);
	//$actioncourante = $w->ActionCourante ($ID);  pas d'action car on est dans un nouvel echange
	$rep .= '?'.$actioncourante;
	$rep .= '?'.$obj->idtiers.'?'.$obj->tiers.'?'.$obj->typedossier.'?#'.$obj->color;
 		echo( $rep);

/*
APPEL dans HTML pour CE PHP
Problème de lecture d'un tableau pour associé les bonnes valeurs aux différents champs
*/
?>