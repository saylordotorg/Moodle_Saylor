<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    auth
 * @subpackage mcae
 * @copyright  2011 Andrew "Kama" (kamasutra12@yandex.ru)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_mcaedescription'] = 'Cette méthode fournit un moyen aux utilisateurs automatiquement s\'inscrire dans la cohorte.';
$string['pluginname'] = 'Autoenrol cohorte';
$string['auth_fieldlocks_help'] = ' ';

$string['auth_mainrule_fld'] = 'Modèle principal. Un modèle par ligne.';
$string['auth_secondrule_fld'] = 'Champ vide';
$string['auth_replace_arr'] = 'Remplacer le tableau. Une valeur par ligne, format: ancienne_valeur | nouvelle_valeur';
$string['auth_delim'] = 'Séparateur';
$string['auth_delim_help'] = 'Différentes systèmes d\'exploitation utilisent différente séparateurs de ligne.<br>Sous Windows, c\'est habituellement CR/LF.<br>Dans Linux - LF,<br>etc.<br>Si ce module ne fonction pas, essayer de changer cette valeur.';

$string['auth_donttouchusers'] = 'Ignorer les utilisateurs';
$string['auth_donttouchusers_help'] = 'Noms d\'utilisateur séparé d\'une virgule.';
$string['auth_enableunenrol'] = 'Activer / désactiver désinscription automatique';

$string['auth_tools_help'] = 'La fonction de désinscription fonctionne uniquement avec les cohortes associés au module. Avec <a href="{$a->url}" target="_blank">cet outil</a> vous pouvez convertir / voir / supprimer toutes les cohortes que vous avez.';

$string['auth_cohorttoolmcae'] = 'Opérations de cohorte';
$string['auth_cohortviewmcae'] = 'Observateur de cohorte';

$string['auth_selectcohort'] = 'Sélectionnez une cohorte';

$string['auth_username'] = 'Nom d\'utilisateur';
$string['auth_link'] = 'Lien';
$string['auth_userlink'] = 'Voir les utilisateurs';
$string['auth_userprofile'] = 'Profil d\'utilisateur &gt;&gt;';
$string['auth_emptycohort'] = 'Cohorte vide';
$string['auth_viewcohort'] = 'Vue de cohorte';
$string['auth_total'] = 'Total';
$string['auth_cohortname'] = 'Nom de cohorte';
$string['auth_component'] = 'Composant';
$string['auth_count'] = 'Compteur';
$string['auth_cohortoper_help'] = '<p>Sélectionnez cohortes que vous voulez convertir.</p><p><b>REMARQUE&nbsp;:</b> <i>Vous <b>ne pouvez pas</b> modifier les cohortes convertis manuellement&nbsp;!</i></p><p>Faite une sauvegarde de votre base de données&nbsp;!!!</p>';

$string['auth_profile_help'] = 'Modèles disponibles';
