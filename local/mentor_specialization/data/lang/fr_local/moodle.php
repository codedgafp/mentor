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
 * Local language pack from http://localhost/mentor
 *
 * @package    core
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

$string['emailonlyallowed'] = 'Cette adresse de courriel ne fait pas partie de celles qui sont autorisées.';

$string['newusernewpasswordtext'] = 'Bonjour {$a->firstname},

Un nouveau compte a été créé pour vous sur <b>la plateforme interministérielle de ' . '
formation Mentor</b>. Un mot de passe temporaire vous a été attribué.

Les informations nécessaires à votre connexion sont maintenant :
    Identifiant : votre adresse e-mail
    Mot de passe: {$a->newpassword}

Vous devrez changer votre mot de passe lors de votre première connexion. Ensuite, vous ' . '
serez invité à <b>compléter votre profil</b>. L\'exactitude des informations saisies permettra ' . '
de <b>garantir l\'accès à toutes les formations qui vous sont réservées</b>.

Pour commencer à travailler sur Mentor, veuillez-vous connecter en cliquant sur le lien ci-dessous.
<br/><a href="{$a->link}">{$a->link}</a>

Dans la plupart des logiciels de courriel, cette adresse devrait apparaître comme un lien ' . '
de couleur bleue qu\'il vous suffit de cliquer. Si cela ne fonctionne pas, copiez ce lien et ' . '
collez-le dans la barre d\'adresse de votre navigateur web.

Si vous avez besoin d\'aide, veuillez consulter la page <a href="' . $CFG->wwwroot . '/local/staticpage/view.php?page=faq">FAQ</a> dans le bas de page.

L\'équipe Mentor

<img src="' . $CFG->wwwroot . '/theme/mentor/pix/logo.png" alt="Logo de Mentor" style="width:200px; height:56px;">';

$string['newpasswordtext'] = 'Bonjour {$a->firstname},

Le mot de passe de votre compte sur <b>la plateforme interministérielle de ' . '
formation Mentor</b> a été remplacé par un nouveau mot de passe temporaire.

Les informations pour vous connecter sont désormais :
    Identifiant : votre adresse e-mail
    Mot de passe : {$a->newpassword}

Merci de visiter cette page afin de changer de mot de passe :
<br/><a href="{$a->link}">{$a->link}</a>

Dans la plupart des logiciels de courriel, cette adresse devrait apparaître comme un lien de couleur ' . '
bleue qu\'il vous suffit de cliquer. Si cela ne fonctionne pas, copiez ce lien et collez-le dans ' . '
la barre d\'adresse de votre navigateur web.

Si vous avez besoin d\'aide, veuillez contacter l\'administrateur du site <b>Mentor</b>,

L\'équipe Mentor

<img src="' . $CFG->wwwroot . '/theme/mentor/pix/logo.png" alt="Logo de Mentor" style="width:200px; height:56px;">';

$string['emailconfirmation'] = 'Bonjour,

Un nouveau compte a été demandé sur « {$a->sitename} » avec votre adresse de courriel.
<br/></br>
Pour confirmer votre nouveau compte, veuillez vous rendre à cette adresse web :

<a href="{$a->link}">{$a->link}</a>
<br/><br/>
Ce lien est valable pendant 7 jours après réception.<br/>
Dans la plupart des programmes de courriel, ce lien devrait apparaître sous la forme d\'un lien bleu sur lequel vous pouvez simplement cliquer. Si cela ne fonctionne pas, veuillez couper et coller l\'adresse dans la barre d\'adresse en haut de la fenêtre de votre navigateur web.
<br/><br/>
Si vous avez besoin d\'aide, veuillez contacter l\'administrateur du site, {$a->admin}';

$string['passwordforgotteninstructions2']
    = 'Pour recevoir un nouveau mot de passe, veuillez indiquer ci-dessous votre adresse de courriel. Si les données correspondantes se trouvent dans la base de données, un message vous sera envoyé par courriel, avec des instructions vous permettant de vous connecter.';

$string['emailpasswordconfirmmaybesent']
    = '<p>Si vous avez fourni une adresse de courriel correcte, un message vous a été envoyé par courriel.</p> <p>Ce message contient de simples instructions pour confirmer et terminer la modification du mot de passe. Si vous n\'arrivez toujours pas à vous connecter, veuillez contacter l\'administrateur du site.</p>';
