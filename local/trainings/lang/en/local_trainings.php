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
 * Lang strings
 *
 * @package    local_trainings
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     Nabil HAMDI <nabil.hamdi@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

$string['pluginname'] = 'Trainings';
$string['name'] = 'Libellé de la formation';
$string['name_help']
    = 'C\'est l\'intitulé qui s\'affiche dans l\'offre de formation. La recherche d\'une formation dans l\'offre s\'appuie notamment sur le texte saisi dans ce champ.';
$string['shortname'] = 'Nom abrégé de la formation';
$string['shortname_help']
    = 'Il doit être court et unique dans toute la plateforme Mentor, tout en restant en lien avec le libellé de la formation.';
$string['content'] = 'Contenu de la formation';
$string['content_help']
    = 'Ce champ permet de préciser les modalités de déroulement de la formation ainsi que les contenus abordés pendant celle-ci. La recherche d\'une formation dans l\'offre s\'appuie notamment aussi sur le texte saisi dans ce champ.';
$string['teaservideo'] = 'Teaser vidéo';
$string['teaservideo_help']
    = 'Il est possible de saisir un lien vers une vidéo d\'accroche déposée sur la plateforme Mentor Vidéo (<a href="https://video.mentor.gouv.fr/">https://video.mentor.gouv.fr/</a>). Cette vidéo sera disponible dans l\'offre de formation.';
$string['prerequisite'] = 'Prérequis';
$string['prerequisite_help']
    = 'Ce champ permet de préciser lorsqu\'une formation nécessite de remplir des conditions préalables à son suivi.';
$string['collection'] = 'Collection';
$string['collection_help_icon'] = 'Aide sur Collection';
$string['collection_help']
    = 'Il s\'agit de choisir parmi les collections existantes la ou les collections correspondant au plus près de l\'objectif de la formation.';
$string['collections'] = 'Collections';
$string['creativestructure'] = 'Structure créatrice';
$string['traininggoal'] = 'Objectifs et/ou problématique de la formation';
$string['traininggoal_help']
    = 'Il est possible de préciser la problématique à laquelle répond la formation. En complément ou si celle-ci ne répond pas à une problématique spécifique, il convient de saisir le ou les objectifs travaillés. La recherche d\'une formation dans l\'offre s\'appuie notamment sur le texte saisi dans ce champ.';
$string['idsirh'] = 'Identifiant SIRH d’origine';
$string['licenseterms'] = 'Termes de la licence';
$string['typicaljob'] = 'Métier(s)';
$string['skills'] = 'Compétences';
$string['certifying'] = 'Formation certifiante';
$string['presenceestimatedtime'] = 'Durée estimée en présence';
$string['presenceestimatedtime_help']
    = 'La durée est fournie ici à titre indicatif. Les sessions qui seront créées à partir de cette formation peuvent avoir une durée différente selon les modalités retenues.';
$string['remoteestimatedtime'] = 'Durée estimée à distance';
$string['remoteestimatedtime_help']
    = 'La durée est fournie ici à titre indicatif. Les sessions qui seront créées à partir de cette formation peuvent avoir une durée différente selon les modalités retenues.';
$string['trainingmodalities'] = 'Modalités envisagées de la formation';
$string['producingorganization'] = 'Libellé de l\'organisme producteur';
$string['producingorganization_help'] = 'Le nom de l\'organisme qui a produit la formation s\'affichera dans la fiche formation.';
$string['producerorganizationlogo'] = 'Logo de l’organisme producteur';
$string['designers'] = 'Concepteurs';
$string['designers_help']
    = 'Il est possible de préciser les concepteurs à l\'origine de cette formation. En complément, il est vivement recommandé de mettre à disposition une page de crédits accessible directement dans la formation, ceci afin de préciser non seulement les auteurs mais aussi tous les éléments de droits d\'auteur liés aux contenus de la formation.';
$string['contactproducerorganization'] = 'Adresse mél organisme producteur';
$string['contactproducerorganization_help']
    = 'Cette adresse doit permettre de prendre contact avec l\'organisme qui a conçu la formation pour répondre à des interrogations concernant l\'esprit de cette formation ou pour remonter toute difficulté rencontrée ou tout  dysfonctionnement identifié.';
$string['thumbnail'] = 'Vignette';
$string['thumbnail_help_icon'] = 'Aide sur Vignette';
$string['thumbnail_help']
    = 'C\'est l\'image qui s\'affiche dans le tableau de bord et dans l\'offre de formation pour illustrer cette formation. Elle est toujours affichée à côté du libellé de la formation, elle n\'a donc pas besoin de répéter le nom de la formation.';
$string['status'] = 'Statut';
$string['createdat'] = 'Date de création';
$string['errorhoursnotnumbers'] = 'Les heures doivent être numériques';
$string['shortnameexist'] = 'Ce nom abrégé de cours existe déjà.';
$string['noselectedskills'] = 'Aucune compétences.';
$string['noselectedcollections'] = 'Aucune collection.';
$string['noprerequisite'] = 'Aucun';
$string['teaserpicture'] = 'Image teaser';
$string['teaserpicture_help_icon'] = 'Aide sur Image teaser';
$string['teaserpicture_help']
    = 'Il est possible de paramétrer une image de teaser. Celle-ci doit être différente de la vignette, elle s\'affichera dans la fiche formation uniquement s\'il n\'y a pas de teaser vidéo paramétré.';
$string['fichetraining'] = 'Fiche formation';
$string['managespaces'] = 'Gérer les espaces';
$string['managetrainings'] = 'Gérer les formations';
$string['trainingmanagement'] = 'Gestion des formations';
$string['debugaddupdatetraining'] = 'Cannot add or update a training: {$a}';
$string['destinationentity'] = 'Espace dédié de destination';
$string['pleasewait'] = 'Veuillez patienter...';
$string['warningsinfo']
    = 'Les champs marqués d\'un <i class="icon fa fa-exclamation-circle text-warning fa-fw "></i>sont requis pour passer au statut "Elaboration terminée".';
$string['requiredelaborationcompleted'] = 'Requis pour passer au statut &quot;Elaboration terminée&quot;';

$string['addtraining'] = 'Ajouter une formation';
$string['updatetraining'] = 'Modifier une formation';
$string['formtrainingtitle'] = 'Formations: gérer une formation';
$string['listtrainingstitle'] = 'Formations';
$string['listtrainingtitle'] = 'Formation';
$string['trash'] = 'Corbeille';
$string['deleteall'] = 'Tout supprimer';

$string['draft'] = 'Brouillon';
$string['template'] = 'Gabarit';
$string['elaboration_completed'] = 'Elaboration terminée';
$string['archived'] = 'Archivée';

$string['presentiel'] = 'Présentiel';
$string['hybride'] = 'Hybride';
$string['emptychoice'] = '--';

$string['edadmintrainingscoursetype'] = 'Formations';
$string['edadmintrainingscoursetitle'] = 'Gestion des formations';

// Permissions.
$string['trainings:manage'] = 'Gérer les formations';
$string['trainings:create'] = 'Créer une formation';
$string['trainings:createinsubentity'] = 'Créer une formation dans une sous-entité';
$string['trainings:update'] = 'Mettre à jour une formation';
$string['trainings:delete'] = 'Supprimer une formation';
$string['trainings:view'] = 'Voir une formation';

$string['trainings:changefullname'] = 'Mettre à jour le libellé de la formation';
$string['trainings:changeshortname'] = 'Mettre à jour le nom abrégé du cours';
$string['trainings:changecontent'] = 'Mettre à jour le contenu de la formation';
$string['trainings:changecollection'] = 'Mettre à jour le champs collection';
$string['trainings:changetraininggoal'] = 'Mettre à jour l\'objectifs de la formation';
$string['trainings:changeidsirh'] = 'Mettre à jour l\'identifiant SIRH d’origine';
$string['trainings:changeskills'] = 'Mettre à jour les compétences';

// Template.
$string['collection'] = 'Collection';

$string['trainingname'] = 'Intitulé de la formation';
$string['sirhid'] = 'Identifiant SIRH d\'origine';
$string['status'] = 'Statut';
$string['actions'] = 'Actions';
$string['abbreviatedname'] = 'Le nom abrégé de la formation :';
$string['dedicateddtargetdspace'] = 'L\'espace dédié cible :';
$string['dedicateddtargetdsubentityoptional'] = 'Le sous-espace dédié cible (facultatif) :';
$string['dedicateddtargetdsubentity'] = 'Le sous-espace dédié cible :';
$string['trainingwasduplicated'] = 'La formation a bien été dupliquée.';

$string['assignuserstooltip'] = 'Gérer les utilisateurs';
$string['duplicatetrainingtooltip'] = 'Dupliquer la formation';
$string['movetrainingtooltip'] = 'Déplacer la formation';
$string['trainingsheettooltip'] = 'Accéder à la fiche de formation ';
$string['createsessionstooltip'] = 'Créer des sessions pour la formation';
$string['deletetrainingtooltip'] = 'Supprimer la formation';
$string['removetrainingdialogtitle'] = 'Suppression d\'une formation';
$string['removetrainingdialogcontent']
    = 'Attention! Vous êtes sur le point de supprimer une formation, sa fiche et son contenu. Confirmez-vous la suppression de ces éléments ?';
$string['addtrainingcontent'] = 'Vous allez créer une formation. Veuillez préciser :';
$string['move'] = 'Déplacer';
$string['movetrainingdialogtitle'] = 'Déplacement d\'une formation';
$string['duplicatetrainingdialogtitle'] = 'Duplication de la formation';
$string['duplicatetrainingdialogcontent'] = 'Vous allez dupliquer cette formation. Veuillez préciser : ';
$string['duplicatetrainingsharingdialogcontent']
    = 'Vous allez dupliquer une formation et la totalité de son contenu. Il vous appartient de transmettre le libellé exact de la formation dupliquée au référent formation de l\'espace dédié cible. Veuillez préciser : ';
$string['createsessiondialogtitle'] = 'Création de session';
$string['createsessiondialogcontent'] = 'Vous allez créer une session. Veuillez préciser :';
$string['duplicatetoaddhoc']
    = 'Votre demande de duplication de formation a bien été prise en compte. Vous recevrez une notification par mél dès que l\'opération sera terminée';
$string['createtoaddhoc']
    = 'Votre demande de création de session a bien été prise en compte. Vous recevrez une notification par mél dès que l\'opération sera terminée';
$string['sessionanmeused']
    = 'Ce nom abrégé de session existe déjà';
$string['trainingnanmeused']
    = 'Ce nom de formation est/sera déjà utilisé.';

$string['movetrainingmessage']
    = 'Vous allez déplacer une formation. Attention, si cette formation possède des sessions, la formation sera déplacée sans ses sessions. Veuillez préciser :';

$string['creattrainingpopuptitle'] = 'Création de la formation';
$string['none'] = 'Aucun';
$string['destination'] = 'La destination :';
$string['shortnamesession'] = 'Le nom abrégé de la session :';

/*****************DataTable***********************/

$string['langfile'] = 'French';

$string['rime_link'] = 'Lien RIME';
$string['rmm_link'] = 'Lien RMM';

$string['ref_link_help'] = 'Lien d\'aide affiché dans l\'édition de la fiche formation';

$string['recyclebin'] = 'Corbeille';
$string['recyclebintitlepage'] = 'Espace de gestion de la corbeille des formations supprimées';

$string['deleteallpopin'] = 'Voulez-vous vraiment supprimer définitivement tout le contenu de la corbeille ?';

$string['closetrainingpreview'] = 'Retour à l\'édition de la fiche de formation';
$string['preview'] = 'prévisualisation';
