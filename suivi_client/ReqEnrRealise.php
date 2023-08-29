<?php 
 /*
 * Historique
 * Version CAV - 2.8 - hiver 2023 - refonte de l'écran matériel loue
 *  							  - modification arguement de AfficheCondenseEchange
 * Version CAV - 2.8.3 - printemps 2023
 *		-reconfiguration ligne échange dans 4saisons (bug 255)
 *		- le téléphone/mail des tiers/contacts s'affichent, l'action réalisée ne s'affiche plus (bug 291-92)
 */
 		require_once('../../../main.inc.php');
		require_once(DOL_DOCUMENT_ROOT.'/core/db/mysqli.class.php');
		require_once('../class/html.suivi_client.class.php');
		require_once('../class/suivi_client.class.php');
		
		$line = new cgl_echange($db);
		$ID = $_GET["ID"];	
		$dos = $_GET["dos"];	
		$origine = $_GET["origine"];	
		$w= New FormCglSuivi($db);
		global $TypeListe ;
		$TypeListe = $origine;			
		
		// Mettre à jour action comme action realisée
		$ret = $line->update_realise($ID) ;
		
		$wcglsuivi = new cgl_dossier ($db);
		$wcglsuivi->fetch($dos);
		// Reconstruire le texte condensé echange et action
		if ($origine == 'materiel') {
			// reconstruire la liste des action à réaliser pour materiel loué
			print '<a href="'.DOL_MAIN_URL_ROOT.'/custom/CahierSuivi/suivi_client/list_dossier.php?typeliste=dossier&Refdossier='.$id.'" >' ;
			print '<img border = 0 title="'.$langs->trans("AideModifDossier",$wcglsuivi->nomdossier).'" src="'.DOL_URL_ROOT.'/theme/eldy/img/object_company.png" 7
						alt="'.$langs->trans("AideModifDossier",$wcglsuivi->nomdossier).'">';
			$ret = $wcglsuivi->IsActionDossier($dos);
			if ($ret) { 
				$wformsuivi = new FormCglSuivi($db);
				$texte = $wformsuivi->ConstructAction($dos, 1);
				print $texte;
			}
		}		
		else {
			// Reconstruire les echanges condensée et l'action générale
			$ActionCondence = $w->ActionsARealiser($dos);
			$ligndesc = $w->AfficheCondenseEchange($dos);
			$Realisation = $w->DateRealisation ($ID, $dos, $origine, 
						(!empty($user->firstname))? $user->firstname: $user->lastname);
			print $ActionCondence."|".$Realisation."|".$ligndesc;		
		}
		
?>