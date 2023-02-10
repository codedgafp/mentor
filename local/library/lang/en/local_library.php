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
 * @package    local_library
 * @copyright  2022 Edunao SAS (contact@edunao.com)
 * @author     remi <remi.colet@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname']                      = 'Bibliothèque de formations';
$string['library:view']                    = 'Voir la bibliothèque de formations';
$string['viewrolename']                    = 'Visiteur bibliothèque de formations';
$string['viewroleshortname']               = 'visiteurbiblio';
$string['confirm']                         = 'Confirmer';
$string['publishtraininglibrary']          = 'Publier dans la bibliothèque de formations';
$string['publicationtraininglibrarytitle'] = '{$a} : publication dans la bibliothèque de formations';
$string['publicationtraininglibrary']      = 'Publication dans la bibliothèque de formations';
$string['publicationtraininglibrarymodal']
                                           = 'Votre demande de publication dans la bibliothèque de formations a bien été prise en compte. Vous recevrez une notification par mél dès que l\'opération sera terminée.';
$string['trainingshortname']               = 'Nom abrégé de la formation';
$string['nametrainingpublish']             = '{$a} (publiée)';

$string['publication_library_object_email'] = '[Mentor] Confirmation de publication dans la bibliothèque de formations';
$string['publication_library_email']        = 'Bonjour,

Votre demande de publication dans la bibliothèque de formations a été réalisée avec succès.
Attention, les liens de ce message ne sont valides que si vous avez les droits sur les espaces dédiés concernés.

Formation publiée :
• {$a->newtrainingfullname} - {$a->newtrainingshortname}
• URL : <a href="{$a->newtrainingurlsheet}">{$a->newtrainingurlsheet}</a>
<br><br>
Formation d’origine :
• {$a->oldtrainingfullname} - {$a->oldtrainingshortname}
• URL : <a href="{$a->oldtrainingurlsheet}">{$a->oldtrainingurlsheet}</a>';

$string['no_trainings']      = 'La bibliothèque de formation ne contient pas de formation publiée.';
$string['training_found']    = 'formation trouvée';
$string['trainings_found']   = 'formations trouvées';
$string['no_training_found'] = 'Aucune formation trouvée';
$string['libraryheaderfirsttext']
                             = 'La bibliothèque de formations est un espace de partage au sein de la communauté interministérielle.';
$string['libraryheaderlasttext']
                             = 'Si vous souhaitez mettre en œuvre, dans votre espace dédié, une formation déjà conçue par un autre partenaire et l\'adapter à un contexte ou à un public spécifique, vous êtes au bon endroit.
Dans un premier temps, nous vous recommandons de consulter la formation en mode "démonstration". Attention, dans la version de démonstration, toutes les activités ne sont pas opérationnelles, mais vous pourrez vous faire une représentation du scénario global de la formation et des ressources mises à disposition.
Si la formation vous intéresse et convient à votre besoin, ou si vous souhaitez vous-même partager une formation, contactez votre administrateur Mentor ou un responsable de formation central.';

$string['librarynotaccessible']       = 'Désolé, cette page n\'existe pas ou n\'est pas disponible avec vos permissions actuelles.';
$string['editablerespectcopyright']   = 'Modifiable dans le respect des intentions pédagogiques initiales et du droit d\'auteur';
$string['certifying']                 = 'Certifiante';
$string['suggestedby']                = 'Proposée par {$a}';
$string['producedby']                 = 'Produite par {$a}';
$string['contact']                    = 'Contact : {$a}';
$string['idsirh']                     = 'Identifiant SIRH {$a}';
$string['teaserof']                   = 'Teaser de {$a}';
$string['readmore']                   = 'Lire la suite';
$string['viewless']                   = 'Voir moins';
$string['onlineduration']             = 'Durée en ligne {$a}';
$string['presenceduration']           = 'Durée en présence {$a}';
$string['publicationfirst']           = 'Publications : Première le {$a}';
$string['publicationfirstandlast']    = 'Publications : Première le {$a->timecreated} - Dernière le {$a->timemodified}';
$string['trainingdemonstrationtitle'] = 'Voir la formation en démonstration';
$string['trainingdemonstrationtext']  = 'Visualiser une version de démonstration des contenus de formation (non modifiable)';
$string['access']                     = 'Accéder';
$string['importtrainingtitle']        = 'Importer dans mon espace';
$string['importtrainingtext']         = 'Dupliquer la formation dans votre espace pour l\'adapter à un contexte spécifique';
$string['import']                     = 'importer';
$string['content']                    = 'Contenu';
$string['prerequisites']              = 'Pré-requis';
$string['skills']                     = 'Compétences';
$string['typicaljob']                 = 'Métier(s)';
$string['termsoflicense']             = 'Termes de la licence';
$string['producerorganizationlogo']   = 'Logo de l\'organisme producteur';

$string['importoentity']             = 'Import de la formation dans mon espace dédié';
$string['trainingshortnamenotempty'] = 'Le nom abrégé de la formation ne peut pas être vide';
$string['entitymustbeselected']      = 'Un espace dédié cible doit être selectionné';
$string['trainingnameused']          = 'Ce nom de formation est/sera déjà utilisé.';
$string['trainingnameused']          = 'Ce nom de formation est/sera déjà utilisé.';
$string['confirmimport']
                                     = 'Votre demande d’import de formation dans votre espace a bien été prise en compte. Vous recevrez une notification par mél dès que l’opération sera terminée.';
$string['confirmation']              = 'Confirmation';

$string['import_to_entity_object_email'] = '[Mentor] Confirmation d’import {$a}';
$string['import_to_entity_email']        = 'Bonjour,

Votre demande d\'import depuis la bibliothèque de formations a été réalisée avec succès : la formation a été importée dans votre espace. Vous pouvez dès à présent y accéder et l’adapter à votre contexte spécifique.

Attention :
-Les liens de ce message ne sont valides que si vous avez les droits sur les espaces dédiés concernés
-La formation est modifiable dans le respect des intentions pédagogiques initiales et du droit d’auteur

Formation importée :
• Libellé : {$a->newtrainingfullname}
• Nom abrégé : {$a->newtrainingshortname}
• URL : <a href="{$a->newtrainingurlsheet}">{$a->newtrainingurlsheet}</a>
<br><br>
Formation d’origine :
• Libellé : {$a->oldtrainingfullname}
• URL : <a href="{$a->oldtrainingurlsheet}">{$a->oldtrainingurlsheet}</a>';

$string['logoof'] = 'Logo de {$a}';
