--==================================
-- Cahier Accueil - insertion données pour test


-- ajouter les premiers enr déjà enregistrés

 
insert into llx_cglavt_c_secteur 
values (1 ,	'2015-11-04 13:48:53', 	'',	1 ,	1 ,	'VTT', 	'3ed6bf', 	2);
insert into llx_cglavt_c_secteur 
values (2 ,	'2015-11-04 21:20:29', 	'', 	1 ,	1 	,'4 saisons', 	'829aeb ',	1);
insert into llx_cglavt_c_secteur 
values (3 ,	'2015-11-04 21:20:29', 	'' ,	1 ,	1 ,	'VAE ',	'BEBEBE',	3);
	
	
insert into llx_cglavt_c_secteur 
values (4, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP,1,1,'bon cadeau' , '7ac960',4);
insert into llx_cglavt_c_secteur 
values (5, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP,1,1,'multi activité' , '	e0163e',5);
insert into llx_cglavt_c_secteur 
values (6, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP,1,1,'Vélo Route' , '5e1257',4);
insert into llx_cglavt_c_priorite
values (1,	'04/11/15 13:52','04/11/15 13:52',	1,	1,	'Urgent',	'a3e657', 	1);
insert into llx_cglavt_c_priorite
values (2	,'04/11/15 13:52',	'11/11/15 13:52',	1	,1	,'Cloturé',	'b3afb3',	1);
insert into llx_cglavt_c_priorite
values (3	,'04/11/15 21:21',	'04/11/15 21:21',	1,	1	,'Attente',	'C12E3D',	1);
insert into llx_cglavt_c_priorite
values (4,	'04/11/15 21:21',	'04/11/15 21:21',	1,	1,	'Delai',	'A125D2',1);	




insert into  llx_cglavt_dossier values ( '1', '21/10/2015 18:05:11', '1', '01/11/2015',' ', '1', '39', 'Canyon 3/10', '1', '1', '3', 5 )														
insert into  llx_cglavt_dossier values ( '2', '01/11/2015 18:06:09', '1', '01/11/2015', '', '1', '455', 'Velo 5/10','', '3', '2', 4 )														
insert into  llx_cglavt_dossier values ( '3', '04/11/2015 16:56:59', '1','01/11/2015', '', '1', '4521', 'Chasse trésor 6/10' , '2', '2', 5, 3 )														

insert into llx_cglavt_dossierdet 
select NULL,  rowid , '21/10/15 18:05','01/11/15', 1, 1, 2, 39, 'demande info Canyon' , 'Info concernant Tapoul car les autres sont fermées', 'Rappler le client', NULL ,NULL
from llx_cglavt_dossier  where fk_soc = 39
	
insert into llx_cglavt_dossierdet 
select NULL,  rowid , '01/11/15 18:06', '01/11/15', 1, 1, 1, 4257, 'Dispo moniteur', 'Vu dispo pour le 3/10 à 10h', 'Valider si client OK', NULL, NULL	
from llx_cglavt_dossier  where fk_soc = 39
	
insert into llx_cglavt_dossierdet 
select NULL,  rowid , '04/11/15 16:56', '04/11/15', 1, 1, NULL, 39, 'Demande Info taille velo', 'Propose VAE et remorque enfant', 'Attente appel', NULL, NULL		
from llx_cglavt_dossier  where fk_soc = 455

insert into llx_cglavt_dossierdet 
select NULL,  rowid , '09/11/15 18:16', NULL, 1 ,1, NULL, 368, 'Essai de données', 'Ceci est un essai
voir si à la ligne se respecte,
... A voir', NULL ,NULL ,1				
from llx_cglavt_dossier  where fk_soc = 4521
										





update llx_societe set email = 'gwenbricout@gmail.com' where rowid = 3625;

INSERT INTO llx_cglavt_dossier 
(`rowid`, `datec`, `fk_createur`, `tms`, `fk_moduser`, `entity`, `fk_soc`, `libelle`, `fk_secteur`, `fk_priorite`, `nb`, `fk_origine`) 
VALUES (NULL, '11/10/15', '2', NULL, '', 1, '3625',  'Prise de contact', '4', '3', '', 15);

insert into llx_cglavt_dossierdet 
select NULL,  rowid , CURRENT_TIMESTAMP, NULL, 1,2,1, 3625, 'envoyer bon cadeau loc velo + remorque 1/2 journée','','faire bon cadeau  ', NULL, NULL 
from llx_cglavt_dossier  where fk_soc = 3625



INSERT INTO llx_cglavt_dossier 
(`rowid`, `datec`, `fk_createur`, `tms`, `fk_moduser`, `entity`, `fk_soc`, `libelle`, `fk_secteur`, `fk_priorite`, `nb`, `fk_origine`) 
VALUES (NULL, '30/10/15', '3', NULL, '',1,'3635', 'Anniversaire sept 2016', '6', '4', '150', '');

insert into llx_cglavt_dossierdet 
select NULL,  rowid , CURRENT_TIMESTAMP, NULL, 1,2,1, 3635, 'Contact client',"Recherche activité pour 3 et 4 sept 2016 pommerazie anniversaire 100 à 200p. A pousser la porte de la boutique en demandant JP Plaza pour les activité paint bool / tire à l'arc. marti indique que nous faisons des chasse au tresors / velo electrique / canyoning et rando aqua. 
Le client cherche aussi un chapiteau 200p + traiteur mais il le gère luimeme. Marti à demander à Jordi si il connait du monde sur la loc chapiteau.
Le client indique que la pommerai est resercvé.
Il revient sur le teritoire en avril pour les activité.",'Marti faire suivre info chapiteau  ', NULL, NULL
from llx_cglavt_dossier  where fk_soc = 3635

insert into llx_cglavt_dossierdet 
select NULL, rowid , CURRENT_TIMESTAMP, NULL, 1,3,1, 3635, 'Proposition Activité', "Il faut lui faire des proposition concrete dans le mois pour qu'il ne parte pas à la concurence.",'Faire proposition fin nov', '30/11/2015', NULL
from llx_cglavt_dossier  where fk_soc = 3635


INSERT INTO llx_cglavt_dossier 
(`rowid`, `datec`, `fk_createur`, `tms`, `fk_moduser`, `entity`, `fk_soc`, `libelle`, `fk_secteur`, `fk_priorite`, `nb`, `fk_origine`) 
VALUES (NULL, '12/10/15', '3', NULL, '',1,'4166', 'Location Vélo route semaine', '5', '2', '2', '');
insert into llx_cglavt_dossierdet 
select NULL,  rowid , CURRENT_TIMESTAMP, NULL, 1,3,1, 4166, 'Mail reçu', 'mail par site de d.sapel : client hollandais a loué route taille 56 en juin et souhaite le relouer diu 24/12 au 03 ou 04 janvier ','', '12/10/15', 1
from llx_cglavt_dossier  where fk_soc = 4166

insert into llx_cglavt_dossierdet 
select NULL,  rowid , CURRENT_TIMESTAMP, NULL, 1,3,1, 253, 'réponse', 'réponse Mathieu dans la foulée indiquant retour rapide sur dispo vélo ou pas.  ', '','13/10/15', 1
from llx_cglavt_dossier  where fk_soc = 4166

insert into llx_cglavt_dossierdet 
select NULL,  rowid , CURRENT_TIMESTAMP, NULL, 1,3,1, 3635, 'envoi propal', 'Mthieu envoie propo locaiton le 03/11 ', '','15/10/15', 1
from llx_cglavt_dossier  where fk_soc = 4166;


insert into llx_cglavt_dossierdet 
select NULL,  rowid , CURRENT_TIMESTAMP, NULL, 1,3,1, 4166, 'paiement', ' réception paiement en ligne dans la foulée. Mathieu accuse réception par mail.', 'voir D. SApel pour prise vélo (ou dépose ?)', '15/10/15', 1
from llx_cglavt_dossier  where fk_soc = 4166


--------------------------
--pour base prod
UPDATE `dolibarr`.`llx_cglavt_dossier` SET `fk_typedossier` = '1' WHERE `llx_cglavt_dossier`.`rowid` =1 LIMIT 1 ;

UPDATE `dolibarr`.`llx_cglavt_dossier` SET `fk_typedossier` = '2' WHERE `llx_cglavt_dossier`.`rowid` =2 LIMIT 1 ;

UPDATE `dolibarr`.`llx_cglavt_dossier` SET `fk_typedossier` = '3' WHERE `llx_cglavt_dossier`.`rowid` =4 LIMIT 1 ;

UPDATE `dolibarr`.`llx_cglavt_dossier` SET `fk_typedossier` = '1' WHERE `llx_cglavt_dossier`.`rowid` =5 LIMIT 1 ;

UPDATE `dolibarr`.`llx_cglavt_dossier` SET `fk_typedossier` = '1' WHERE `llx_cglavt_dossier`.`rowid` =6 LIMIT 1 ;
