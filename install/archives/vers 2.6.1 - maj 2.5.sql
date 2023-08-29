-- 25/01/2022 correction bug erreur enregistrement et création échange dossier
ALTER TABLE `llx_cglavt_dossier` CHANGE `tms` `tms` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP; 
ALTER TABLE `llx_cglavt_dossierdet` CHANGE `tms` `tms` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP; 