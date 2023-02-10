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
 * Plugin strings
 *
 * @package    local_mentor_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Spécialisation Mentor';

$string['region']     = 'Région';
$string['regionsopt'] = 'Région(s)';

// Template.
$string['createsession'] = 'Créer une session';
$string['addtraining']   = 'Ajouter une formation';
$string['trash']         = 'Corbeille';
$string['collection']    = 'Collection';
$string['collections']   = 'Collections';
$string['trainingname']  = 'Intitulé de la formation';
$string['sirhid']        = 'Identifiant SIRH d\'origine';
$string['status']        = 'Status';
$string['sessions']      = 'Sessions';
$string['actions']       = 'Actions';
$string['startdate']     = 'Date de début';
$string['participants']  = 'Participants';
$string['shared']        = 'Partagée';
$string['subentity']     = 'Sous-espace';
$string['reflocal']      = 'Référent local';

// Modalities.
$string['emptychoice'] = '--';
$string['presentiel']  = 'Présentiel';
$string['mixte']       = 'Mixte';
$string['online']      = 'En ligne';

$string['status']    = 'Statut';
$string['fullname']  = 'Libellé de la session';
$string['shortname'] = 'Nom abrégé de la session';

$string['all_user_current_entity']        = 'all_user_current_entity';
$string['all_user_all_entity']            = 'all_user_all_entity';
$string['all_user_current_entity_others'] = 'all_user_current_entity_others';

$string['inpreparation']      = 'En préparation';
$string['openedregistration'] = 'Inscriptions ouvertes';
$string['inprogress']         = 'En cours';
$string['completed']          = 'Terminée';
$string['archived']           = 'Archivée';
$string['reported']           = 'Reportée';
$string['cancelled']          = 'Annulée';

$string['trainingblockfields']             = 'Champs de la fiche formation';
$string['sessionblockfields']              = 'Fiche session';
$string['publiccible']                     = 'Public cible';
$string['allpublic']                       = 'Tout public';
$string['termsregistration']               = 'Modalités de l’inscription';
$string['termsregistration_help']
                                           = 'Inscription libre : la session est accessible en auto-inscription sans intervention d\'un formateur. Autre : l\'inscription dans la session devra être effectuée manuellement ou par import d\'une liste sous forme de fichier CSV. Quand le choix "Autre" est retenu, il est possible de préciser un message sur les raisons de ce choix et/ou les modalités d\'inscription en renvoyant vers un SIRH, par exemple.';
$string['termsregistrationdetail']         = '';
$string['onlinesessionestimatedtime']      = 'Durée en ligne';
$string['presencesessionestimatedtime']    = 'Durée en présentiel';
$string['sessionpermanent']                = 'Session permanente';
$string['sessionpermanent_help']
                                           = 'Une session est dite permanente lorsque la date de fin de la session ne peut pas être déterminée à l\'avance, par opposition aux sessions de formation bornées dans le temps. Il est néanmoins tout à fait possible d\'archiver une session permanente lorsque celle-ci n\'a plus de raison d\'être.';
$string['sessionmodalities']               = 'Modalités de la session';
$string['accompaniment']                   = 'Accompagnement';
$string['placesavailable']                 = 'Places disponibles';
$string['numberparticipants']              = 'Nombre de participants inscrits';
$string['location']                        = 'Lieu(x) de formation';
$string['organizingstructure']             = 'Structure organisatrice';
$string['sessionnumber']                   = 'Numéro de la session';
$string['sessionnumberbis']                = 'N°';
$string['trainingcontent']                 = 'Modalités et/ou contenu de la formation';
$string['hours']                           = 'Heures';
$string['minutes']                         = 'Minutes';
$string['infolifecycle']                   = 'Informations sur le cycle de vie';
$string['wordingsession']                  = 'Libellé de la session : {$a}';
$string['connectingentity']                = 'Entité de rattachement';
$string['connectingmainentity']            = 'Entité de rattachement principale';
$string['connectingsecondaryentities']     = 'Entité(s) de rattachement<br>secondaire(s)';
$string['deleteteaserpicture']             = 'Supprimer l\'image teaser';
$string['currentteaserpicture']            = 'Image teaser actuelle';
$string['currentproducerorganizationlogo'] = 'Logo de l\'organisme producteur actuel';
$string['deleteproducerorganizationlogo']  = 'Supprimer le logo de l’organisme producteur';
$string['optional']                        = ' (optionnel)';
$string['setnotvisible']                   = 'Rendre non visible';
$string['entityvisibility']                = 'Visibilité de l\'espace dédié';

$string['task_close_sessions']   = 'Mentor : Fermeture automatique des sessions';
$string['task_open_sessions']    = 'Mentor : Ouverture automatique des sessions';
$string['task_archive_sessions'] = 'Mentor : Archivage automatique des sessions';

$string['errornotnumeric']           = 'Le nombre de participant doit être un nombre entier supérieur à zéro';
$string['errorthumbnail']
                                     = 'Ce champ est obligatoire pour enregistrer la formation au statut \'Elaboration Terminée\' et \'Archivée\'';
$string['errorelaborationcompleted'] = 'Ce champ est obligatoire pour enregistrer la formation au statut "Elaboration Terminée"';
$string['errorallstatus']            = 'Ce champ est obligatoire pour enregistrer une fiche formation à tous les statuts';
$string['errorsessionallstatus']     = 'Ce champ est obligatoire pour enregistrer la fiche session à tous les statuts';
$string['errorenpreparation']
                                     = 'Ce champ est obligatoire pour enregistrer la session à tous les statuts sauf "En préparation"';
$string['errorteaser']
                                     = 'Le teaser doit être hébergé sur {$a}. Si vous n\'avez pas accès à ce service, consultez la page Contact.';

$string['erroropentolist'] = 'Vous devez choisir au moins un espace dédié.';
$string['erroromail']      = 'L\'adresse mèl saisie ne respecte pas le format obligatoire.';
$string['errorsessionpermanent']
                           = 'Ce champ est obligatoire pour enregistrer une session non permanente à tous les statuts sauf "En préparation"';

// Used for session form.
$string['mentor_specialization:changesessionfullname']            = 'Mettre à jour le libellé';
$string['mentor_specialization:changesessionshortname']           = 'Mettre à jour le nom abrégé de la session';
$string['mentor_specialization:changesessionopento']              = 'Mettre à jour le champs ouverte à ';
$string['mentor_specialization:changesessionpubliccible']         = 'Mettre à jour le champs public cible';
$string['mentor_specialization:changesessiononlinetime']          = 'Mettre à jour la durée estimée en ligne';
$string['mentor_specialization:changesessionpresencetime']        = 'Mettre à jour la durée estimée en présence';
$string['mentor_specialization:changesessionpermanentsession']    = 'Mettre à jour session permanente';
$string['mentor_specialization:changesessionstartdate']           = 'Mettre à jour la date de début de la session de formation';
$string['mentor_specialization:changesessionenddate']             = 'Mettre à jour la date de fin de la session de formation';
$string['mentor_specialization:changesessionaccompaniment']       = 'Mettre à jour l\'accompagnement';
$string['mentor_specialization:changesessionsessionmodalities']   = 'Mettre à jour la modalités de la session';
$string['mentor_specialization:changesessiontermsregistration']   = 'Mettre à jour la modalités de l\'inscription';
$string['mentor_specialization:changesessionmaxparticipants']     = 'Mettre à jour le nombre maximum de participants';
$string['mentor_specialization:changesessionlocation']            = 'Mettre à jour le lieu de formation';
$string['mentor_specialization:changesessionorganizingstructure'] = 'Mettre à jour la structure organisatrice';

// Used for training form.
$string['mentor_specialization:changefullname']                      = 'Mettre à jour le libellé de la formation';
$string['mentor_specialization:changeshortname']                     = 'Mettre à jour le nom abrégé du cours';
$string['mentor_specialization:changecontent']                       = 'Mettre à jour le contenu de la formation';
$string['mentor_specialization:changecollection']                    = 'Mettre à jour le champs collection';
$string['mentor_specialization:changeidsirh']                        = 'Mettre à jour l\'identifiant SIRH d’origine';
$string['mentor_specialization:changeskills']                        = 'Mettre à jour les compétences';
$string['mentor_specialization:changeteaser']                        = 'Mettre à jour le teaser';
$string['mentor_specialization:changeproducingorganization']         = 'Mettre à jour l\'organisme producteur';
$string['mentor_specialization:changetypicaljob']                    = 'Mettre à jour l\'emploi type';
$string['mentor_specialization:changeproducerorganizationshortname'] = 'Mettre à jour le nom abrégé de l\'organisme producteur';
$string['mentor_specialization:changecontactproducerorganization']   = 'Mettre à jour le contact de l\'organisme producteur';
$string['mentor_specialization:changedesigners']                     = 'Mettre à jour les concepteurs';
$string['mentor_specialization:changecertifying']                    = 'Mettre à jour le champs formation certifiante';
$string['mentor_specialization:changelicenseterms']                  = 'Mettre à jour les termes de la licence';
$string['mentor_specialization:changeprerequisite']                  = 'Mettre à jour les prérequis';
$string['mentor_specialization:changepresenceestimatedtimehours']    = 'Mettre à jour la durée estimée en présence';
$string['mentor_specialization:changeremoteestimatedtimehours']      = 'Mettre à jour la durée estimée à distance';
$string['mentor_specialization:changetrainingmodalities']            = 'Mettre à jour les modalités envisagées de la formation';
$string['mentor_specialization:changeproducerorganizationlogo']      = 'Mettre à jour le logo de l\'organisme producteur';
$string['mentor_specialization:changeteaserpicture']                 = 'Mettre à jour l\'image du teaser';
$string['mentor_specialization:changecatchphrase']                   = 'Mettre à jour la phrase d\'accroche';

// Entity form.
$string['mentor_specialization:changeentityname']   = 'Mettre à jour le nom de l\'espace';
$string['mentor_specialization:changeentityregion'] = 'Mettre à jour la région de l\'espace';
$string['mentor_specialization:changeentitylogo']   = 'Mettre à jour le logo de l\'espace';

$string['statusconfirmmessage'] = 'Vous êtes sur le point de reporter une session de formation ouverte aux inscriptions. Les participants vont être automatiquement informés par une notification par courriel.

Voulez-vous vraiment reporter cette session de formation ?';

// Settings.
$string['collections_help'] = '<b>Ajouter des collections telles que</b> :
                                <br>collection_slug|collection_name|code_couleur
                                <br>collection_slug|collection_name|code_couleur
                                <br>etc...
                                <br><b>Exemple</b> : accompagnement|Accompagnement des transitions professionnelles|#CECECE';

$string['training_found']     = 'formation trouvée';
$string['trainings_found']    = 'formations trouvées';
$string['no_training_found']  = 'Aucune formation trouvée';
$string['not_found']          = 'Aucun résultat';
$string['trainings_trainer']  = 'Proposée par';
$string['collection']         = 'Collection';
$string['entity']             = 'Structure Créatrice';
$string['in-progress']        = 'Embarquement immédiat';
$string['permanent-access']   = 'Accès permanent';
$string['reset']              = 'Réinitialiser les filtres';
$string['submit']             = 'Appliquer les filtres';
$string['search']             = 'Rechercher';
$string['filter_button']      = 'Filtrer';
$string['filter_button_all']  = 'Toutes';
$string['filter_button_all']  = 'Toutes';
$string['filter_button_all']  = 'Toutes';
$string['search_placeholder'] = 'Rechercher un contenu, une compétence, ...';
$string['certifying']         = 'Certifiante';
$string['videodomains']       = 'Domaines vidéo authorisés';
$string['videodomains_help']  = 'Domaines vidéo autorisés, séparés par des sauts de ligne';

// Filter.
$string['sortby']        = "Filtrer par";
$string['none']          = 'Aucun';
$string['applyfilters']  = "Appliquer les filtres";
$string['resetfilters']  = "Réinitialiser les filtres";
$string['sessionperiod'] = "Période début de session";
$string['start']         = "Début";
$string['end']           = "Fin";

$string['draft']                 = 'Brouillon';
$string['template']              = 'Gabarit';
$string['elaboration_completed'] = 'Elaboration terminée';
$string['archived']              = 'Archivée';

// Training.
$string['catchphrase']                   = "Phrase d'accroche";
$string['maxcaracters']                  = ' ({$a} caractères maximum)';
$string['producerorganizationshortname'] = 'Nom abrégé de l\'organisme producteur';
$string['producerorganizationshortname_help']
                                         = 'Le nom abrégé de l\'organisme producteur s\'affichera dans l\'offre de formation à côté du nom abrégé de l\'espace dédié.';

$string['email_disabled_accounts']         = 'Envoyer un email aux comptes désactivés tentant de se connecter';
$string['email_disabled_accounts_object']  = 'Mentor : Suspension de votre compte';
$string['email_disabled_accounts_content'] = 'Bonjour,

Votre compte utilisateur sur Mentor a été suspendu par un administrateur.

Si vous avez besoin d\'accéder de nouveau à Mentor, veuillez contacter votre gestionnaire ou responsable de formation.

L\'équipe du programme Mentor';

$string['email_user_entity_update_object']  = 'Mentor : modification de votre(vos) entité(s) de rattachement';
$string['email_user_entity_update_content'] = 'Bonjour,

Votre compte utilisateur sur Mentor a été modifié par un administrateur.

Voici votre (vos) nouvelle(s) entité(s) de rattachement :
{$a->entitylist}
Vous pouvez dès à présent aller consulter l\'offre Mentor pour découvrir les nouvelles formations auxquelles vous avez dorénavant accès : <a href="{$a->wwwroot}/offre">{$a->wwwroot}/offre</a>

Pour tout renseignement, veuillez contacter votre gestionnaire ou responsable de formation.

L\'équipe du programme Mentor';

$string['publishtimecreated']       = 'Date de première publication';
$string['publishtimecreated_help']  = 'Date de première publication de la formation dans la bibliothèque de formations';
$string['publishtimemodified']      = 'Date de dernière publication';
$string['publishtimemodified_help'] = 'Date de dernière publication de la formation dans la bibliothèque de formations';
