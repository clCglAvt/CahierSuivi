-- ===========================================================================
-- Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2007      Laurent Destailleur  <eldy@users.sourceforge.net>
-- Copyright (C) 2007-2012 Regis Houssin        <regis.houssin@capnetworks.com>
-- Copyright (C) 2010      Juanjo Menent        <jmenent@2byte.es>
-- MOD CCA 26/12/2014 Creation table Pour cahier Accueil
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ===========================================================================
DROP TABLE llx_cglavt_tiers_suivi
CREATE TABLE  IF NOT EXISTS  llx_cglavt_dossier (
`rowid` INT NOT NULL AUTO_INCREMENT ,
`datec` TIMESTAMP  NOT NULL DEFAULT NOW(),
`tms` DATE   NOT NULL , 
fk_createur int(11) not null, 
fk_moduser int(11) null, 
`entity` INT NOT NULL DEFAULT '1' ,
fk_soc int (11),
libelle varchar(50)  not null,
`fk_secteur` INT   ,
`fk_priorite` INT   ,
`nb` INT   DEFAULT '0' ,
`fk_origine` int (11) NULL  ,
fk_typedossier int (11) NULL  ,
PRIMARY KEY ( `rowid` )
) ENGINE = InnoDB COMMENT = 'Permet de faire le suivi des dossiers clients';
insert into llx_cglavt_dossier 
select distinct rowid, datec, tms, entity, fk_secteur, fk_urgence, nb, fk_origine 
from llx_cglavt_tiers_suivi


CREATE TABLE IF NOT EXISTS llx_cglavt_dossierdet (
`rowid` INT NOT NULL AUTO_INCREMENT ,
fk_dossier int (11),
`datec` TIMESTAMP  NOT NULL DEFAULT NOW(),
`tms` DATE   NOT NULL  , 
`entity` INT NOT NULL DEFAULT '1' ,
`fk_user_create` INT NOT NULL ,
`fk_user_mod` INT ,
`fk_soc` INT ,
`titre` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`description` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`action` varchar(100)  CHARACTER SET utf8 COLLATE utf8_general_ci NULL  ,
date_action date,
statut_action bool, 
PRIMARY KEY ( `rowid` )
) ENGINE = InnoDB COMMENT = 'Détail des dossiers des clients';



