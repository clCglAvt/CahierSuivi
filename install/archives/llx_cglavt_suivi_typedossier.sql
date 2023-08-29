-- ===========================================================================
-- Copyright (C) 2001-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2007      Laurent Destailleur  <eldy@users.sourceforge.net>
-- Copyright (C) 2007-2012 Regis Houssin        <regis.houssin@capnetworks.com>
-- Copyright (C) 2010      Juanjo Menent        <jmenent@2byte.es>
-- MOD CCA 26/12/2014 Creation table Pour Cahier accueil
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

CREATE TABLE  IF NOT EXISTS llx_cglavt_c_typedossier (
`rowid` INT NOT NULL AUTO_INCREMENT ,
`datec` TIMESTAMP  NOT NULL DEFAULT NOW(),
`tms` DATETIME   NOT NULL , 
`entity` INT NOT NULL DEFAULT '1' ,
 label varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
 color varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
active smallint default 1,
ordre smallint default 1,
PRIMARY KEY ( `rowid` )
) ENGINE = InnoDB COMMENT = 'Dictionnaire des types de dossiers';
