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
 * Language file.
 *
 * @package    theme_mentor
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

// A description shown in the admin theme selector.
$string['choosereadme']
    = 'Theme Mentor is a child theme of Boost developed for the Mentor project. It adds the possibility of seeing the logo of his entity, redefined the login page ...';

// The name of our plugin.
$string['pluginname'] = 'Mentor';

// We need to include a lang string for each block region.
$string['region-side-pre'] = 'Left';

$string['managemyentities']  = 'Gérer mes espaces';
$string['managemyentity']    = 'Gérer mon espace';
$string['managetrainings']   = 'Gérer les formations';
$string['managesessions']    = 'Gérer les sessions';
$string['trainingcatalog']   = 'Offre de formation';
$string['contact']           = 'Contact';
$string['login']             = 'S\'identifier';
$string['forgotpassword']    = 'Mot de passe oublié ?';
$string['rememberme']        = 'Se souvenir de moi';
$string['signup']            = 'Créer mon compte';
$string['contact']           = 'Contact';
$string['dashboard']         = 'Tableau de bord';
$string['prevstep']          = 'Étape précédente';
$string['prevstepcatalog']   = 'Retour à l\'offre de formation';
$string['prevstepdashboard'] = 'Retour au tableau de bord';

// Mentor theme.
$string['mentorgeneralsettings'] = 'Paramètres thème Mentor';
$string['about']                 = 'En savoir plus';
$string['about_desc']            = 'Lien vers la page "En savoir plus"';
$string['legalnotice']           = 'Mentions légales';
$string['legalnotice_desc']      = 'Lien vers la page "Mentions légales"';
$string['faq']                   = 'FAQ';
$string['faq_desc']              = 'Lien vers la page "FAQ"';
$string['version']               = 'Version {$a}';
$string['versionnumber']         = 'Version du projet Mentor';
$string['versionnumber_desc']    = 'Version du projet Mentor . Ce champs est en général rempli de manière automatique . ';
$string['mentorlicence']         = 'Licence';
$string['mentorlicence_desc']    = 'Licence défini';
$string['mentorlicencedefault']
                                 = 'Sauf mention contraire, tous les contenus de ce site sont sous < a href
    = "https://www.etalab.gouv.fr/" target="_blank"> licence etalab - 2.0 </a > ';
$string['externallinks']         = 'Liens extérieur';
$string['externallinks_desc']    = 'Liens extérieur à Mentor';
$string['textinfofooter']        = 'Information';
$string['textinfofooter_desc']   = 'Information en début de footer';
$string['textinfofooterdefault']
                                 = 'Le programme Mentor est porté par la Direction générale de l\'administration et de la fonction
publique (DGAFP)';
$string['footerlogotitle']       = 'Site du Ministère de la transformation et de la fonction publiques';
$string['accessibility']         = 'Accessibilité : non conforme';
$string['accessibility_desc']    = 'Lien vers la page "Accessibilité"';
$string['personaldata']          = 'Données personnelles';
$string['personaldata_desc']     = 'Lien vers la page "Données personnelles"';
$string['sitemap']               = 'Plan du site';
$string['sitemap_desc']          = 'Lien vers la page "Plan du site"';

$string['connectto']          = 'Connectez vous à votre compte Mentor';
$string['findoutmore']        = 'En savoir plus';
$string['logintitle']         = 'S\'engager en formation avec ';
$string['loginfirstmessage']  = 'Une offre variée et évolutive';
$string['loginsecondmessage'] = 'Disponible à tout moment';
$string['loginthirdmessage']  = 'À l\'appui de vos projets de développement professionnel';
$string['loginmentor']        = 'Identifiez vous à votre compte Mentor';

$string['musthaveemail']       = 'Pour accéder à Mentor vous devez disposer d’une adresse mél professionnelle.';
$string['showdomainlist']      = 'voir liste des domaines pris en charge';
$string['createyouraccount']   = 'Créez votre compte et découvrez l’offre de formation !';
$string['invalidbrowseralert'] = 'Il semblerait que vous utilisiez un navigateur non supporté ou une version trop ancienne.<br/>
Pour une expérience optimale, nous vous encourageons à utiliser un navigateur de la liste ci-dessous :<br/><br/>
<ul>
<li>Edge version 79.0 ou plus</li>
<li>Chrome version 66.0 ou plus</li>
<li>Firefox version 78.0 ou plus</li>
<li>Safari 15 ou plus</li>
<li>Opéra 53 ou plus</li>
</ul>';
$string['invalidbrowsertitle'] = 'Avertissement';
$string['invalidbrowser']      = 'Navigateur non supporté';

$string['checkeligibity']                    = 'Vérifier l\'éligibilité de mon adresse mél professionnelle';
$string['createaccount']                     = 'Créer mon compte';
$string['formemail']                         = 'Adresse mél professionnelle';
$string['formemailconfirm']                  = 'Adresse mél professionnelle (Confirmation)';
$string['formemptyemail']                    = 'L\'adresse mél professionnelle ne peut pas être vide.';
$string['formnotallowedemail']
                                             = 'Votre adresse mél ne correspond pas à un domaine de messagerie autorisé actuellement. Pour plus d’informations, consultez la page « <a href="{$a}" target="_blank" rel="help opener">En savoir plus</a> ».';
$string['formexistemail']
                                             = 'Cette adresse est déjà enregistrée. Vous avez peut-être créé un compte auparavant ? <a href="{$a}">Récupérer un nom ou un mot de passe oublié.</a>';
$string['formpassword']                      = 'Mot de passe';
$string['formemptypassword']                 = 'Le mot de passe ne peut pas être vide.';
$string['formpasswordinformation']
                                             = 'Le mot de passe doit comporter au moins 12 caractère(s), au moins 1 chiffre(s), au moins 1 minuscule(s), au moins 1 majuscule(s), au moins 1 caractère(s) non-alphanumérique(s) tels que *,- ou #';
$string['formpasswordnotmatch']              = 'Ces adresses méls professionnelles ne correspondent pas. Veuillez réessayer.';
$string['formfirstname']                     = 'Prénom';
$string['formemptyfirstname']                = 'Le prénom ne peut pas être vide.';
$string['formlastname']                      = 'Nom';
$string['formemptylastname']                 = 'Le nom ne peut pas être vide.';
$string['formemptyprofile_field_sexe']       = 'Le sexe ne peut pas être vide.';
$string['formemptyprofile_field_birthyear']  = 'L\'année de naissance ne peut pas être vide.';
$string['formemptyprofile_field_status']     = 'Le statut ne peut pas être vide.';
$string['formemptyprofile_field_category']   = 'La catégorie ne peut pas être vide.';
$string['formemptyprofile_field_mainentity'] = 'L\'entité de rattachement ne peut pas être vide.';
$string['formemptyprofile_field_region']     = 'La région ne peut pas être vide.';
$string['formsexe']                          = 'Sexe**';
$string['formbirthyear']                     = 'Année de naissance**';
$string['formstatus']                        = 'Statut';
$string['formcategory']                      = 'Catégorie**';
$string['formmainentity']                    = 'Entité de rattachement principale';
$string['formmainentityinformation']
                                             = 'Le choix d\'une entité de rattachement principale vous permettra d\'accéder à des formations dédiées aux agents de votre entité. Si aucune entité ne correspond à votre rattachement administratif actuel, il est conseillé de choisir « Autre » dans un premier temps.';
$string['formsecondaryentities']             = 'Entité(s) de rattachement secondaire(s)';
$string['formsecondaryentitiesinformation']
                                             = 'Pour enrichir votre offre de formation, vous pouvez choisir une ou plusieurs entités de rattachement secondaires qui correspondent à vos problématiques professionnelles actuelles. Ces choix peuvent être modifiés à tout moment en éditant votre profil.';
$string['formattachmentstructure']           = 'Structure de rattachement';
$string['formaffectation']                   = 'Affectation';
$string['formregion']                        = 'Région';
$string['formdepartment']                    = 'Département';
$string['formtown']                          = 'Ville';
$string['formtsignupinformation']
                                             = '<p class="signup-form-information">Ce formulaire comprend des champs requis, marqués <i class="icon fa fa-exclamation-circle text-danger fa-fw " title="Requis" aria-label="Requis"></i><br>Les données marquées** seront exploitées uniquement pour un usage statistique anonyme de manière à permettre l\'amélioration continue des contenus de la plateforme.<br>En cas d\'évolution de votre situation professionnelle, vous pourrez modifier ultérieurement toutes les données saisies ci-dessous en éditant votre profil Mentor.</p>';

$string['or']                          = 'Ou';
$string['agentconnectidentifier']      = 'Plugin Agent connect';
$string['agentconnectidentifier_desc'] = 'Identifiant du plugin d\'Agent connect';
$string['unavailablesession']
                                       = 'La session n\'a pas encore débuté. Vous n\'êtes pas encore autorisé à accéder au contenu de la session.';

$string['libraryreturn'] = 'Retour à la bibliothèque de formations';
