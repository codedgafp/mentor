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
 * Local language pack from http://dgafp.local
 *
 * @package    mod
 * @subpackage assign
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activityoverview'] = 'Vous avez des contributions qui requièrent votre attention';
$string['addnewattempt_help']
        = 'Ceci créera une contribution vide pour vous permettre d\'y travailler.';
$string['addnewattemptfromprevious']
        = 'Ajouter une tentative basée sur le travail de contribution remis précédemment';
$string['addnewattemptfromprevious_help']
        = 'Ceci copiera le contenu du travail de contribution remis précédemment pour vous permettre d\'y travailler.';
$string['addsubmission'] = 'Ajouter une contribution';
$string['addsubmission_help'] = 'Vous n\'avez pas encore remis de contribution.';
$string['allocatedmarker_help'] = 'L\'évaluateur attribué pour ce travail de contribution.';
$string['allowsubmissions']
        = 'Autoriser l\'utilisateur à ajouter ou modifier ses travaux pour cette contribution.';
$string['allowsubmissionsanddescriptionfromdatesummary']
        = 'Les détails de la contribution et le formulaire de remise de document seront disponibles dès le <strong>{$a}</strong>';
$string['allowsubmissionsfromdatesummary']
        = 'Cette contribution acceptera la remise de documents dès le <strong>{$a}</strong';
$string['allowsubmissionsshort'] = 'Autoriser l\'ajout et la modification de contributions';
$string['alwaysshowdescription_help']
        = 'Si ce réglage est désactivé, la description de la contribution ci-dessus ne sera visible qu\'à partir de la date d\'ouverture du formulaire de remise.';
$string['assign:addinstance'] = 'Ajouter une contribution';
$string['assign:editothersubmission'] = 'Modifier le travail de contribution d\'un autre étudiant';
$string['assign:exportownsubmission'] = 'Exporter ses propres contributions remises';
$string['assign:grade'] = 'Évaluer une contribution';
$string['assign:manageallocations'] = 'Gérer les évaluateurs attribués à des contributions remises';
$string['assign:manageoverrides'] = 'Gérer les dérogations de contribution';
$string['assign:submit'] = 'Envoyer une contribution';
$string['assign:view'] = 'Accéder à une contribution';
$string['assignmentisdue'] = 'Contribution à effectuer';
$string['assignmentmail']
        = '{$a->grader} a donné un feedback pour le travail de contribution remis pour « {$a->assignment} ». Vous pouvez le consulter en annexe à votre contribution : {$a->url}';
$string['assignmentmailhtml']
        = '<p>{$a->grader} a donné un feedback pour le travail de contribution remis pour « <em>{$a->assignment}</em> ».</p> <p>Vous pouvez le consulter en annexe à <a href="{$a->url}">votre contribution</a>.</p>';
$string['assignmentmailsmall']
        = '{$a->grader} a donné un feedback pour le travail de contribution remis pour « {$a->assignment} ». Vous pouvez le consulter en annexe à votre contribution';
$string['assignmentname'] = 'Nom de la contribution';
$string['assignmentplugins'] = 'Plugins de contribution';
$string['assignmentsperpage'] = 'Contributions par page';
$string['attemptreopenmethod'] = 'Réouverture des contributions remises remises';
$string['attemptreopenmethod_help']
        = 'Détermine comment les contributions remises par les étudiants sont rouvertes. Les options disponibles sont : * Jamais - Le travail remis ne peut pas être rouvert. * Manuellement - Le travail remis peut être rouvert par un enseignant. * Automatiquement jusqu\'à réussite - Le travail de contribution remis est rouvert automatiquement jusqu\'à ce que l\'étudiant atteigne la note nécessaire pour réussir la contribution ; cette note est indiquée dans le carnet de notes.';
$string['backtoassignment'] = 'Retour à la contribution';
$string['batchoperationconfirmaddattempt']
        = 'Autoriser une autre tentative pour les contributions remises sélectionnées';
$string['batchoperationconfirmdownloadselected'] = 'Télécharger les contributions remises sélectionnées ?';
$string['batchoperationconfirmgrantextension']
        = 'Octroyer une prolongation pour toutes les contributions sélectionnées ?';
$string['batchoperationconfirmlock'] = 'Verrouiller toutes les contributions sélectionnées ?';
$string['batchoperationconfirmremovesubmission'] = 'Supprimer les contributions sélectionnées ?';
$string['batchoperationconfirmreverttodraft']
        = 'Remettre toutes les contributions sélectionnées dans l\'état brouillon ?';
$string['batchoperationconfirmsetmarkingallocation']
        = 'Définir l\'attribution de l\'évaluation pour toutes les contributions remises sélectionnées ?';
$string['batchoperationconfirmsetmarkingworkflowstate']
        = 'Définir le statut de l\'évaluation pour toutes les contributions les remises sélectionnées ?';
$string['batchoperationconfirmunlock'] = 'Déverrouiller toutes les contributions sélectionnées ?';
$string['batchoperationlock'] = 'verrouiller les contributions remises';
$string['batchoperationreverttodraft'] = 'remettre à l\'état de brouillon les contributions remises';
$string['batchoperationunlock'] = 'déverrouiller les contributions remises';
$string['blindmarking_help']
        = 'L\'évaluation à l\'aveugle cache aux évaluateurs l\'identité des étudiants. Les réglages de l\'évaluation à l\'aveugle seront verrouillés dès qu\'une contribution aura été remise ou une note donnée pour cette contribution.';
$string['confirmsubmission']
        = 'Voulez-vous vraiment remettre votre contribution pour évaluation ? Vous ne pourrez plus effectuer de changement.';
$string['conversionexception']
        = 'Impossible de convertir la contribution. Exception retournée : {$a}.';
$string['couldnotconvertgrade']
        = 'Impossible de convertir la note de la contribution de l\'utilisateur {$a}';
$string['couldnotconvertsubmission']
        = 'Impossible de convertir le travail de contribution remis de l\'utilisateur {$a}';
$string['couldnotcreatenewassignmentinstance'] = 'Impossible de créer l\'instance de la nouvelle contribution.';
$string['couldnotfindassignmenttoupgrade']
        = 'Impossible de trouver l\'instance de l\'ancienne contribution à mettre à jour.';
$string['crontask'] = 'Traitement en tâche de fond du module contribution';
$string['currentassigngrade'] = 'Note actuel dans la contribution';
$string['cutoffdate_help']
        = 'Si cette date est indiquée, le travail de contribution n\'autorisera aucune remise de travail après ce délai, sauf octroi d\'une prolongation.';
$string['cutoffdatefromdatevalidation']
        = 'La date limite ne doit pas être antérieure à la date après laquelle la remise des contributions est permise.';
$string['defaultsettings'] = 'Réglages par défaut des contributions';
$string['defaultsettings_help']
        = 'Ces réglages définissent les réglages par défaut de toutes les nouvelles contributions.';
$string['deleteallsubmissions'] = 'Supprimer toutes les contributions remises';
$string['downloadall'] = 'Télécharger toutes les contributions remises';
$string['downloadasfolders'] = 'Télécharger les contributions remises dans des dossiers';
$string['downloadasfolders_help']
        = 'Les contributions remises peuvent être téléchargés dans des dossiers. Les fichiers de chaque étudiant sont placés dans un dossier séparé, avec les éventuels sous-dossiers, et ne sont pas renommés.';
$string['downloadselectedsubmissions'] = 'Télécharger les contributions remises sélectionnées';
$string['duedate_help']
        = 'Cette date est celle du délai de remise de la contribution. La remise des contributions sera permise après cette date, mais les contributions remises après cette date seront marqués en retard. Pour empêcher la remise de travaux après une certaine date, veuillez indiquer une date limite de remise.';
$string['duedatereached'] = 'La date de remise de cette contribution est passée';
$string['editsubmission'] = 'Modifier le travail de contribution';
$string['editsubmission_help']
        = 'Vous pouvez encore faire des modifications à votre contribution remise.';
$string['editsubmissionother'] = 'Modifier le travail de contribution de {$a}';
$string['errornosubmissions'] = 'Il n\'y a pas de contribution remise à télécharger';
$string['errorquickgradingvsadvancedgrading']
        = 'Les notes n\'ont pas été enregistrées, car cette contribution utilise actuellement l\'évaluation avancée';
$string['eventallsubmissionsdownloaded'] = 'Toutes les contributions téléchargées';
$string['eventassessablesubmitted'] = 'Travail de contribution remis';
$string['eventoverridecreated'] = 'Dérogation de contribution créée';
$string['eventoverridedeleted'] = 'Dérogation de contribution supprimée';
$string['eventoverrideupdated'] = 'Dérogation de contribution modifiée';
$string['eventremovesubmissionformviewed'] = 'Confirmation de suppression de travail de contribution consultée';
$string['eventstatementaccepted'] = 'Énoncé du travail de contribution accepté par l\'utilisateur';
$string['eventsubmissioncreated'] = 'Travail de contribution créé';
$string['eventsubmissionduplicated'] = 'L\'utilisateur a dupliqué son travail de contribution';
$string['eventsubmissiongraded'] = 'Travail de contribution évalué';
$string['eventsubmissionlocked'] = 'Contributions d\'un utilisateur verrouillées';
$string['eventsubmissionstatusupdated'] = 'Statut du travail de contribution modifié';
$string['eventsubmissionstatusviewed'] = 'Statut du travail de contribution remis consulté';
$string['eventsubmissionunlocked'] = 'Contributions d\'un utilisateur déverrouillées';
$string['eventsubmissionupdated'] = 'Travail de contribution modifié';
$string['eventsubmissionviewed'] = 'Travail de contribution remis consulté';
$string['extensionnotafterfromdate']
        = 'La date de prolongation doit être ultérieure à la date après laquelle la remise des contributions est permise.';
$string['feedbackavailableanonhtml']
        = 'Vous avez un nouveau feedback pour votre contribution remise pour « {$a->assignment} ».<br /><br />Vous pouvez le voir au-dessous de votre <a href="{$a->url}">contribution remise</a>.';
$string['feedbackavailableanonsmall'] = 'Nouveau feedback pour la contribution {$a->assignment}';
$string['feedbackavailableanontext']
        = 'Vous avez un nouveau feedback pour votre contribution remise pour « {$a->assignment} ». Vous pouvez le voir au-dessous de votre contribution remise : {$a->url}';
$string['feedbackavailablehtml']
        = '{$a->username} a donné un feedback pour la contribution remise pour « <em>{$a->assignment}</em> ».<br /><br />Vous pouvez le consulter en annexe à <a href="{$a->url}">votre contribution</a>.';
$string['feedbackavailablesmall']
        = '{$a->username} a donné un feedback pour la contribution {$a->assignment}';
$string['feedbackavailabletext']
        = '{$a->username} a donné un feedback pour le travail de contribution remis pour « {$a->assignment} ». Vous pouvez le consulter en annexe à votre contribution : {$a->url}';
$string['filtersubmitted'] = 'Contribution rendue';
$string['fixrescalednullgrades']
        = 'Cette contribution comporte des notes erronées. Vous pouvez <a href="{$a->link}">corriger automatiquement ces notes</a>. Ceci pourrait avoir une influence sur les totaux du cours.';
$string['gradeitem:submissions'] = 'Contributions remises';
$string['gradeoutofhelp_help']
        = 'Saisir ici la note pour le travail de contribution de l\'étudiant. On peut indiquer des décimales.';
$string['gradersubmissionupdatedhtml']
        = '{$a->username} a modifié son travail remis pour la contribution « <em>{$a->assignment}</em> » le {$a->timeupdated}.<br /><br />Le travail de contribution remis est <a href="{$a->url}">disponible sur le site web</a>.';
$string['gradersubmissionupdatedsmall']
        = '{$a->username} a modifié son travail remis pour la contribution {$a->assignment}.';
$string['gradersubmissionupdatedtext']
        = '{$a->username} a modifié son travail remis pour la contribution « {$a->assignment} » le {$a->timeupdated}. Ce travail est disponible ici : {$a->url}';
$string['gradingduedate_help']
        = 'Le délai pour l\'évaluation des contributions par l\'enseignant. Cette date est utilisée afin de prioriser les notifications du tableau de bord des enseignants.';
$string['hidegrader_help']
        = 'Si ce réglage est activé, l\'identité de tout utilisateur qui évalue un travail remis dans une contribution ne sera pas affichée, afin que les étudiants ne voient pas qui a évalué leur travail. Remarque : ce réglage n\'a pas d\'effet sur le champ de commentaires sur la page d\'évaluation.';
$string['indicator:cognitivedepth'] = 'Contribution : aspect cognitif';
$string['indicator:cognitivedepth_help']
        = 'Cet indicateur se base sur le niveau cognitif atteint par l\'étudiant dans une activité contribution.';
$string['indicator:cognitivedepthdef'] = 'Contribution : aspect cognitif';
$string['indicator:cognitivedepthdef_help']
        = 'Le participant a atteint durant cet intervalle d\'analyse ce pourcentage d\'engagement cognitif offert par les activités « Contribution » (niveaux : pas de vue, vue, envoi, vue du feedback, commentaire du feedback, nouvel envoi après vue du feedback).';
$string['indicator:socialbreadth'] = 'Contribution : aspect social';
$string['indicator:socialbreadth_help']
        = 'Cet indicateur se base sur l\'interaction sociale atteinte par l\'étudiant dans une activité contribution.';
$string['indicator:socialbreadthdef'] = 'Contribution : aspect social';
$string['indicator:socialbreadthdef_help']
        = 'Le participant a atteint durant cet intervalle d\'analyse ce pourcentage d\'engagement social offert par les activités « Contribution » (niveaux : pas de participation, participant seul, participant avec d\'autres).';
$string['introattachments_help']
        = 'Des fichiers supplémentaires à utiliser dans la contribution peuvent être ajoutés, par exemple des modèles de réponse. Les liens de téléchargement de ces fichiers seront affichés sur la page de la contribution, sous la description.';
$string['lastmodifiedsubmission'] = 'Dernière modification (travail de contribution remis)';
$string['latesubmissions'] = 'Contributions en retard';
$string['locksubmissionforstudent']
        = 'Empêcher la remise d\'autres contributions par l\'étudiant : (id={$a->id}, fullname={$a->fullname}).';
$string['locksubmissions'] = 'Verrouiller la remise des contributions';
$string['manageassignfeedbackplugins'] = 'Gérer les plugins de feedback des contributions';
$string['manageassignsubmissionplugins'] = 'Gérer les plugins de remise de travaux des contributions';
$string['maxattempts_help']
        = 'Le nombre maximal de tentatives de remise pouvant être effectuées par un étudiant. Une fois ce nombre atteint, le travail de contribution remis ne pourra plus être rouvert.';
$string['maxperpage'] = 'Nombre de contributions par page';
$string['maxperpage_help']
        = 'Le nombre maximum de contributions qu\'un évaluateur peut voir dans la page d\'évaluation des contributions. Ce réglage est utile pour éviter des lenteurs d\'affichage dans les cours où il y a de très nombreux participants.';
$string['messageprovider:assign_notification'] = 'Notifications de contributions';
$string['modulename'] = 'Contribution';
$string['modulename_help']
        = 'Le module d\'activité devoir permet à un enseignant de communiquer aux participants des tâches, de récolter des travaux et de leur fournir feedbacks et notes. Les étudiants peuvent remettre des travaux sous forme numérique (fichiers), par exemple des documents traitement de texte, feuilles de calcul, images, sons ou séquences vidéo. En complément ou en plus, le devoir peut demander aux étudiants de saisir directement un texte. Une contribution peut aussi être utilisée pour indiquer aux étudiants des tâches à effectuer dans le monde réel et ne nécessitant pas la remise de fichiers numériques. Les étudiants peuvent remettre un devoir individuellement ou comme membres d\'un groupe. Lors de l\'évaluation des contributions, les enseignants peuvent donner aux étudiants des feedbacks, leur envoyer des fichiers : travaux annotés, documents avec commentaires ou feedbacks audio. Les contributions peuvent être évaluées au moyen d\'une note numérique, d\'un barème spécifique ou d\'une méthode avancée comme une grille d\'évaluation. Les notes définitives sont enregistrées dans le carnet de notes.';
$string['modulenameplural'] = 'Contributions';
$string['multipleteams_desc']
        = 'Cette contribution nécessite la remise des travaux en groupes. Vous faites partie de plusieurs groupes. Pour pouvoir remettre une contribution, vous devez ne faire partie que d\'un seul groupe. Veuillez contacter votre enseignant pour qu\'il change votre appartenance aux groupes.';
$string['multipleteamsgrader']
        = 'Membre de plusieurs groupes ; impossible donc de remettre une contribution.';
$string['mysubmission'] = 'Ma travail de contribution :';
$string['newsubmissions'] = 'Contributions rendues';
$string['nolatesubmissions'] = 'Aucune contribution en retard acceptée';
$string['noonlinesubmissions']
        = 'Cette contribution ne requiert pas de fichier à remettre de votre part';
$string['nooverridedata']
        = 'Vous devez indiquer une dérogation pour au moins un des réglages de la contribution.';
$string['nosubmission'] = 'Rien n\'a été déposé pour cette contribution';
$string['nosubmissionsacceptedafter'] = 'Aucune contribution acceptée après';
$string['noteam_desc']
        = 'Cette contribution nécessite la remise des travaux en groupes. Vous ne faites partie d\'aucun groupe, et ne pouvez donc pas remettre de contribution. Veuillez contacter votre enseignant pour qu\'il vous ajoute à un groupe.';
$string['noteamgrader']
        = 'Membre d\'aucun groupe ; impossible donc de remettre une contribution.';
$string['offline'] = 'Aucune contribution à remettre requis';
$string['overdue']
        = '<span class="flagged-tag">La contribution est en retard de {$a}</span>';
$string['page-mod-assign-view'] = 'Page principale du module contribution';
$string['page-mod-assign-x'] = 'Toute page du module contribution';
$string['pluginadministration'] = 'Administration de la contribution';
$string['pluginname'] = 'Contribution';
$string['preventsubmissionnotingroup'] = 'Requiert un groupe pour remettre une contribution';
$string['preventsubmissionnotingroup_help']
        = 'Si ce réglage est activé, les utilisateurs qui ne sont pas membres d\'un groupe ne pourront pas remettre de contribution.';
$string['preventsubmissions']
        = 'Empêcher l\'utilisateur de déposer ou de modifier des travaux pour cette contribution.';
$string['preventsubmissionsshort'] = 'Empêcher l\'ajout et la modification de contributions';
$string['privacy:metadata:assignfeedbackpluginsummary'] = 'Données de feedback de la contribution.';
$string['privacy:metadata:assigngrades'] = 'Enregistre les notes de l\'utilisateur pour la contribution';
$string['privacy:metadata:assignmentid'] = 'Identifiant de la contribution';
$string['privacy:metadata:assignoverrides'] = 'Enregistre les informations des dérogations de la contribution';
$string['privacy:metadata:assignperpage'] = 'Nombre de contributions affichées par page.';
$string['privacy:metadata:assignsubmissionpluginsummary'] = 'Données des remises de la contribution.';
$string['privacy:metadata:grade']
        = 'La note numérique pour cette contributions remise. Peut être déterminée par un barème, mais est toujours convertie en nombre à virgule.';
$string['privacy:studentpath'] = 'Contributions des étudiants';
$string['privacy:submissionpath'] = 'Contribution remise';
$string['quickgrading_help']
        = 'L\'évaluation rapide vous permet d\'attribuer des notes (et compétences) directement dans le tableau des contributions remises. L\'évaluation rapide n\'est pas compatible avec l\'évaluation avancée et n\'est pas recommandée si plusieurs utilisateurs effectuent l\'évaluation.';
$string['removesubmission'] = 'Supprimer travail de contribution remis';
$string['removesubmissionforstudent']
        = 'Supprimer le travail de contribution remis par l \'étudiant : id={$a->id}, nom complet={$a->fullname}.';
$string['requireallteammemberssubmit_help']
        = 'Si ce réglage est activé, tous les membres du groupe doivent cliquer sur le bouton de remise de contribution pour que le travail du groupe soit considéré comme remis. Dans le cas contraire, le travail de contribution du groupe sera considéré comme remis dès que l\'un de ses membres clique sur le bouton de remise.';
$string['requiresubmissionstatement']
        = 'Demander aux étudiants d\'accepter la déclaration de remise pour toutes les contributions';
$string['requiresubmissionstatement_help']
        = 'Lorsque ce réglage est activé, les étudiants doivent accepter une déclaration pour toutes les contributions de cette plateforme. Le texte de cette déclaration peut être modifié par l\'administrateur. Par défaut, sa teneur est : « Ce document est le fruit de mon propre travail de contribution, excepté les extraits dûment cités de travaux d\'autres personnes.»';
$string['revealidentitiesconfirm']
        = 'Voulez-vous vraiment révéler les identités des étudiants pour cette contribution ? Cette opération ne peut pas être annulée. Une fois les identités révélées, les notes seront transmises au carnet de notes.';
$string['reverttodefaults'] = 'Revenir aux réglages par défaut de la contribution';
$string['reverttodraft'] = 'Remettre les contributions remises en état de brouillon.';
$string['reverttodraftforstudent']
        = 'Remettre à l\'état de brouillon le travail de contribution de l\'étudiant : (id={$a->id}, fullname={$a->fullname}).';
$string['reverttodraftshort'] = 'Remettre le travail de contribution à l\'état de brouillon';
$string['search:activity'] = 'Contribution – information sur l\'activité';
$string['sendlatenotifications'] = 'Informer les évaluateurs des contributions en retard';
$string['sendlatenotifications_help']
        = 'Si ce réglage est activé, les évaluateurs (normalement les enseignants) recevront un message lorsque les étudiants remettent une contribution en retard. La façon dont le message est délivré est configurable.';
$string['sendnotifications'] = 'Informer les évaluateurs des contributions remises';
$string['sendnotifications_help']
        = 'Si ce réglage est activé, les évaluateurs (en principe les enseignants) recevront un message chaque fois qu\'un étudiant remet un travail pour cette contribution, qu\'il soit en avance, à temps ou en retard. La méthode d\'envoi des messages est configurable.';
$string['sendstudentnotifications_help']
        = 'Si ce réglage est activé, les étudiants reçoivent un message lors de la modification d\'une note ou d\'un feedback. Si un flux d\'évaluation est activé pour cette contribution, les notifications ne seront pas envoyés avant que la note ne soit « Publiée ».';
$string['sendsubmissionreceipts_help']
        = 'Ce réglage active les accusés de réception pour les étudiants. Les étudiants recevront alors une notification chaque fois qu\'ils remettent un travail pour une contribution.';
$string['settings'] = 'Réglages de la contribution';
$string['submission'] = 'Contribution rendue';
$string['submissioncopiedhtml']
        = '<p>Vous avez copié votre contribution remise précédent pour <em>{$a->assignment}</em>.</p> <p>Vous pouvez consulter l\'état de votre <a href="{$a->url}">contribution remise</a>.</p>';
$string['submissioncopiedsmall']
        = 'Vous avez copié votre contribution remise précédent pour <em>{$a->assignment}</em>.';
$string['submissioncopiedtext']
        = 'Vous avez copié votre contribution remise précédent pour <em>{$a->assignment}</em>. Vous pouvez consulter l\'état de votre contributions remise&nbsp;: {$a->url}';
$string['submissiondrafts']
        = 'Exiger que les étudiants cliquent sur le bouton « Envoyer la contribution »';
$string['submissiondrafts_help']
        = 'Si ce réglage est activé, les étudiants devront explicitement cliquer sur un bouton de remise pour confirmer que leur contribution est terminée. Cela permet aux étudiants de conserver dans le système une version brouillon de leur travail avant de l\'envoyer. Si le réglage est activé après que des étudiants ont déjà remis leur contributions, ceux-ci seront considérés comme définitifs.';
$string['submissioneditable'] = 'L\'étudiant peut modifier ce travail de contribution remis';
$string['submissionnotcopiedinvalidstatus']
        = 'Le travail de contribution remis n\'a pas été copié, car il a été modifié depuis sa réouverture.';
$string['submissionnoteditable'] = 'L\'étudiant ne peut pas modifier ce travail de contribution remis';
$string['submissionnotready'] = 'Ce travail de contribution n\'est pas prêt à être remis :';
$string['submissionreceipthtml']
        = '<p>Vous avez remis un travail pour la contribution « <em>{$a->assignment}</em> »</p> <p>Vous pouvez consulter l\'état de votre <a href="{$a->url}">contribution</a>.</p>';
$string['submissionreceiptotherhtml']
        = 'Votre contribution pour « <em>{$a->assignment}</em> » a été remise.<br /><br /> Vous pouvez consulter le statut de votre <a href="{$a->url}">contribution remise</a>.';
$string['submissionreceiptothersmall'] = 'Votre contribution pour {$a->assignment} a été remise.';
$string['submissionreceiptothertext']
        = 'Votre contribution pour « {$a->assignment} » a été remise. Vous pouvez consulter le statut de votre contribution remise : {$a->url}';
$string['submissionreceiptsmall']
        = 'Vous avez remis votre travail pour la contribution {$a->assignment}';
$string['submissionreceipttext']
        = 'Vous avez remis un travail pour la contribution « {$a->assignment} ». Vous pouvez consulter l\'état de votre travail : {$a->url}';
$string['submissionsclosed'] = 'a remise des contributions est terminée';
$string['submissionsettings'] = 'Réglages de la remise des contributions';
$string['submissionslocked']
        = 'L\'ajout, la modification et la suppression de contributions ont été bloqués';
$string['submissionslockedshort'] = 'Contribution verrouillée';
$string['submissionsnotgraded'] = 'Contributions non évaluées {$a}';
$string['submissionstatement_help']
        = 'Énoncé que chaque étudiant doit accepter avant de remettre son travail de contribution';
$string['submissionstatementdefault']
        = 'Ce document est le fruit de mon propre travail de contribution, excepté les extraits dûment cités de travaux d\'autres personnes.';
$string['submissionstatementteamsubmission_help']
        = 'Énoncé que chaque étudiant doit accepter avant de remettre le travail de contribution de leur groupe';
$string['submissionstatementteamsubmissionallsubmit']
        = 'Énoncé de remise lorsque tous les membres du groupe remettent une contribution';
$string['submissionstatementteamsubmissionallsubmit_help']
        = 'Énoncé que chaque étudiant doit accepter avant de remettre une contribution en tant que membre d\'un groupe.';
$string['submissionstatementteamsubmissionallsubmitdefault']
        = 'Ce document est le fruit de mon propre travail de contribution en tant que membre du groupe, excepté les extraits dûment cités de travaux d\'autres personnes.';
$string['submissionstatementteamsubmissiondefault']
        = 'Ce document est le fruit du travail de contribution de mon groupe, excepté les extraits dûment cités de travaux d\'autres personnes.';
$string['submissionstatus'] = 'Statut des contributions remises';
$string['submissionstatus_'] = 'Pas de travail de contribution remis';
$string['submitassignment'] = 'Envoyer la contribution';
$string['submitassignment_help']
        = 'Une fois cette contribution envoyée, vous ne pourrez plus y effectuer de modification.';
$string['submitted'] = 'Contribution rendue';
$string['submittedearly'] = 'Le travail de contribution a été remis en avance de {$a}';
$string['submittedlate'] = 'Le travail de contribution a été remise en retard de {$a}';
$string['teamsubmission'] = 'Les étudiants remettent leur contribution en groupe';
$string['teamsubmission_help']
        = 'Si ce réglage est activé, les étudiants seront répartis en groupes, sur la base du jeu de groupes par défaut ou d\'un groupement choisi. Une contribution remise en groupe sera partagée par tous les membres du groupe et tous les membres du groupe verront les modifications de la contribution effectuées par les autres membres.';
$string['teamsubmissiongroupingid_help']
        = 'Les groupes de ce groupement seront utilisés pour former les groupes d\'étudiants de cette contribution. Si non renseigné, le jeu de groupes par défaut sera utilisé.';
$string['textinstructions'] = 'Instructions pour la contribution';
$string['ungroupedusers']
        = 'Le réglage « Requiert un groupe pour remettre une contribution » est activé et certains utilisateurs ne sont membres d\'aucun groupe ou membres de plusieurs groupes. Cette situation les empêchera de remettre une contribution.';
$string['ungroupedusersoptional']
        = 'Le réglage « Les étudiants remettent leur contribution en groupe » est activé et certains utilisateurs ne font partie d\'aucun groupe ou sont membres de plusieurs groupes. Veuillez prendre note que ces étudiants remettront leur contribution en tant que membre du « Groupe par défaut ».';
$string['unlocksubmissionforstudent']
        = 'Permettre la remise de contributions pour l\'étudiant : (id={$a->id}, fullname={$a->fullname}).';
$string['userassignmentdefaults'] = 'Réglages par défaut de la contribution pour l\'utilisateur';
$string['useridlistnotcached']
        = 'Les modifications aux notes n\'ont pas été enregistrées : il n\'a pas été possible de déterminer à quel travail de contribution elles avaient été attribuées.';
$string['usersnone'] = 'Aucun étudiant n\'a accès à cette contribution.';
$string['usersubmissioncannotberemoved']
        = 'Le travail de contribution remis par {$a} ne peut pas être supprimé.';
$string['userswhoneedtosubmit'] = 'Utilisateurs devant valider l\'envoi de la contribution : {$a}';
$string['viewgrading'] = 'Consulter toutes les contributions remises';
$string['viewownsubmissionform'] = 'Afficher sa propre page de remise de contributions.';
$string['viewownsubmissionstatus'] = 'Afficher sa propre page de l\'état des contributions remises.';
$string['viewsubmission'] = 'Afficher le travail de contribution remis';
$string['viewsubmissionforuser'] = 'Afficher le travail de contribution remis de l\'étudiant {$a}';
$string['viewsubmissiongradingtable'] = 'Afficher le tableau des notes de la contribution';
