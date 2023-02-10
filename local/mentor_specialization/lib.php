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
 * PLugin mentor specilization
 *
 * @package    local_mentor_specialization
 * @copyright  2020 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/mentor_core/lib.php');
require_once($CFG->dirroot . '/lib/filterlib.php');

/**
 * List of regions
 *
 * @return string[]
 */
function local_mentor_specialization_get_regions() {
    return [
        '84'  => 'Auvergne-Rhône-Alpes',
        '27'  => 'Bourgogne-Franche-Comté',
        '53'  => 'Bretagne',
        '24'  => 'Centre-Val de Loire',
        '94'  => 'Corse',
        '44'  => 'Grand Est',
        '01'  => 'Guadeloupe',
        '03'  => 'Guyane',
        '32'  => 'Hauts-de-France',
        '11'  => 'Ile-de-France',
        '04'  => 'La Réunion',
        '02'  => 'Martinique',
        '06'  => 'Mayotte',
        '28'  => 'Normandie',
        '75'  => 'Nouvelle-Aquitaine',
        '76'  => 'Occitanie',
        '52'  => 'Pays de la Loire',
        '93'  => 'Provence-Alpes-Côte d\'Azur',
        '900' => 'Collectivités d\'Outremer',
        '988' => 'Nouvelle-Calédonie'
    ];
}

/**
 * List of departments
 *
 * @return string[]
 */
function local_mentor_specialization_get_departments() {
    return [
        // Department is optional so we need to add an empty value.
        ''                         => '',
        'Ain'                      => '01 - Ain',
        'Aisne'                    => '02 - Aisne',
        'Allier'                   => '03 - Allier',
        'Alpes-de-Haute-Provence'  => '04 - Alpes-de-Haute-Provence',
        'Hautes-Alpes'             => '05 - Hautes-Alpes',
        'Alpes-Maritimes'          => '06 - Alpes-Maritimes',
        'Ardèche'                  => '07 - Ardèche',
        'Ardennes'                 => '08 - Ardennes',
        'Ariège'                   => '09 - Ariège',
        'Aube'                     => '10 - Aube',
        'Aude'                     => '11 - Aude',
        'Aveyron'                  => '12 - Aveyron',
        'Bouches-du-Rhône'         => '13 - Bouches-du-Rhône',
        'Calvados'                 => '14 - Calvados',
        'Cantal'                   => '15 - Cantal',
        'Charente'                 => '16 - Charente',
        'Charente-Maritime'        => '17 - Charente-Maritime',
        'Cher'                     => '18 - Cher',
        'Corrèze'                  => '19 - Corrèze',
        'Corse-du-Sud'             => '2A - Corse-du-Sud',
        'Haute-Corse'              => '2B - Haute-Corse',
        'Côte-d\'Or'               => '21 - Côte-d\'Or',
        'Côtes-d\'Armor'           => '22 - Côtes-d\'Armor',
        'Creuse'                   => '23 - Creuse',
        'Dordogne'                 => '24 - Dordogne',
        'Doubs'                    => '25 - Doubs',
        'Drôme'                    => '26 - Drôme',
        'Eure'                     => '27 - Eure',
        'Eure-et-Loir'             => '28 - Eure-et-Loir',
        'Finistère'                => '29 - Finistère',
        'Gard'                     => '30 - Gard',
        'Haute-Garonne'            => '31 - Haute-Garonne',
        'Gers'                     => '32 - Gers',
        'Gironde'                  => '33 - Gironde',
        'Hérault'                  => '34 - Hérault',
        'Ille-et-Vilaine'          => '35 - Ille-et-Vilaine',
        'Indre'                    => '36 - Indre',
        'Indre-et-Loire'           => '37 - Indre-et-Loire',
        'Isère'                    => '38 - Isère',
        'Jura'                     => '39 - Jura',
        'Landes'                   => '40 - Landes',
        'Loir-et-Cher'             => '41 - Loir-et-Cher',
        'Loire'                    => '42 - Loire',
        'Haute-Loire'              => '43 - Haute-Loire',
        'Loire-Atlantique'         => '44 - Loire-Atlantique',
        'Loiret'                   => '45 - Loiret',
        'Lot'                      => '46 - Lot',
        'Lot-et-Garonne'           => '47 - Lot-et-Garonne',
        'Lozère'                   => '48 - Lozère',
        'Maine-et-Loire'           => '49 - Maine-et-Loire',
        'Manche'                   => '50 - Manche',
        'Marne'                    => '51 - Marne',
        'Haute-Marne'              => '52 - Haute-Marne',
        'Mayenne'                  => '53 - Mayenne',
        'Meurthe-et-Moselle'       => '54 - Meurthe-et-Moselle',
        'Meuse'                    => '55 - Meuse',
        'Morbihan'                 => '56 - Morbihan',
        'Moselle'                  => '57 - Moselle',
        'Nièvre'                   => '58 - Nièvre',
        'Nord'                     => '59 - Nord',
        'Oise'                     => '60 - Oise',
        'Orne'                     => '61 - Orne',
        'Pas-de-Calais'            => '62 - Pas-de-Calais',
        'Puy-de-Dôme'              => '63 - Puy-de-Dôme',
        'Pyrénées-Atlantiques'     => '64 - Pyrénées-Atlantiques',
        'Hautes-Pyrénées'          => '65 - Hautes-Pyrénées',
        'Pyrénées-Orientales'      => '66 - Pyrénées-Orientales',
        'Bas-Rhin'                 => '67 - Bas-Rhin',
        'Haut-Rhin'                => '68 - Haut-Rhin',
        'Rhône'                    => '69 - Rhône',
        'Haute-Saône'              => '70 - Haute-Saône',
        'Saône-et-Loire'           => '71 - Saône-et-Loire',
        'Sarthe'                   => '72 - Sarthe',
        'Savoie'                   => '73 - Savoie',
        'Haute-Savoie'             => '74 - Haute-Savoie',
        'Paris'                    => '75 - Paris',
        'Seine-Maritime'           => '76 - Seine-Maritime',
        'Seine-et-Marne'           => '77 - Seine-et-Marne',
        'Yvelines'                 => '78 - Yvelines',
        'Deux-Sèvres'              => '79 - Deux-Sèvres',
        'Somme'                    => '80 - Somme',
        'Tarn'                     => '81 - Tarn',
        'Tarn-et-Garonne'          => '82 - Tarn-et-Garonne',
        'Var'                      => '83 - Var',
        'Vaucluse'                 => '84 - Vaucluse',
        'Vendée'                   => '85 - Vendée',
        'Vienne'                   => '86 - Vienne',
        'Haute-Vienne'             => '87 - Haute-Vienne',
        'Vosges'                   => '88 - Vosges',
        'Yonne'                    => '89 - Yonne',
        'Territoire de Belfort'    => '90 - Territoire de Belfort',
        'Essonne'                  => '91 - Essonne',
        'Hauts-de-Seine'           => '92 - Hauts-de-Seine',
        'Seine-Saint-Denis'        => '93 - Seine-Saint-Denis',
        'Val-de-Marne'             => '94 - Val-de-Marne',
        'Val-d\'Oise'              => '95 - Val-d\'Oise',
        'Guadeloupe'               => '971 - Guadeloupe',
        'Martinique'               => '972 - Martinique',
        'Guyane'                   => '973 - Guyane',
        'La Réunion'               => '974 - La Réunion',
        'Mayotte'                  => '976 - Mayotte',
        'Saint-Pierre-et-Miquelon' => '975 - Saint-Pierre-et-Miquelon',
        'Saint-Barthélemy'         => '977 - Saint-Barthélemy',
        'Saint-Martin'             => '978 - Saint-Martin',
        'Wallis et Futuna'         => '986 - Wallis et Futuna',
        'Polynésie française'      => '987 - Polynésie française',
        'Nouvelle-Calédonie'       => '988 - Nouvelle-Calédonie'
    ];
}

/**
 * Get the list of regions/departments association
 *
 * @return array
 */
function local_mentor_specialization_get_regions_and_departments() {
    return [
        'Auvergne-Rhône-Alpes'        => [
            '01 - Ain',
            '03 - Allier',
            '07 - Ardèche',
            '15 - Cantal',
            '26 - Drôme',
            '38 - Isère',
            '42 - Loire',
            '43 - Haute-Loire',
            '63 - Puy-de-Dôme',
            '69 - Rhône',
            '73 - Savoie',
            '74 - Haute-Savoie'
        ],
        'Bourgogne-Franche-Comté'     => [
            '21 - Côte-d\'Or',
            '25 - Doubs',
            '39 - Jura',
            '58 - Nièvre',
            '70 - Haute-Saône',
            '71 - Saône-et-Loire',
            '89 - Yonne',
            '90 - Territoire de Belfort'
        ],
        'Bretagne'                    => [
            '22 - Côtes-d\'Armor',
            '29 - Finistère',
            '35 - Ille-et-Vilaine',
            '56 - Morbihan'
        ],
        'Centre-Val de Loire'         => [
            '18 - Cher',
            '28 - Eure-et-Loir',
            '36 - Indre',
            '37 - Indre-et-Loire',
            '41 - Loir-et-Cher',
            '45 - Loiret'
        ],
        'Collectivités d\'Outremer'   => [
            '975 - Saint-Pierre-et-Miquelon',
            '977 - Saint-Barthélemy',
            '978 - Saint-Martin',
            '986 - Wallis et Futuna',
            '987 - Polynésie française',
        ],
        'Corse'                       => [
            '2A - Corse-du-Sud',
            '2B - Haute-Corse'
        ],
        'Grand Est'                   => [
            '08 - Ardennes',
            '10 - Aube',
            '51 - Marne',
            '52 - Haute-Marne',
            '54 - Meurthe-et-Moselle',
            '55 - Meuse',
            '57 - Moselle',
            '67 - Bas-Rhin',
            '68 - Haut-Rhin',
            '88 - Vosges'
        ],
        'Guadeloupe'                  => [
            '971 - Guadeloupe'
        ],
        'Guyane'                      => [
            '973 - Guyane'
        ],
        'Hauts-de-France'             => [
            '02 - Aisne',
            '59 - Nord',
            '60 - Oise',
            '62 - Pas-de-Calais',
            '80 - Somme'
        ],
        'Ile-de-France'               => [
            '75 - Paris',
            '77 - Seine-et-Marne',
            '78 - Yvelines',
            '91 - Essonne',
            '92 - Hauts-de-Seine',
            '93 - Seine-Saint-Denis',
            '94 - Val-de-Marne',
            '95 - Val-d\'Oise',
        ],
        'La Réunion'                  => [
            '974 - La Réunion'
        ],
        'Martinique'                  => [
            '972 - Martinique'
        ],
        'Mayotte'                     => [
            '976 - Mayotte'
        ],
        'Normandie'                   => [
            '14 - Calvados',
            '27 - Eure',
            '50 - Manche',
            '61 - Orne',
            '76 - Seine-Maritime'
        ],
        'Nouvelle-Aquitaine'          => [
            '16 - Charente',
            '17 - Charente-Maritime',
            '19 - Corrèze',
            '23 - Creuse',
            '24 - Dordogne',
            '33 - Gironde',
            '40 - Landes',
            '47 - Lot-et-Garonne',
            '64 - Pyrénées-Atlantiques',
            '79 - Deux-Sèvres',
            '86 - Vienne',
            '87 - Haute-Vienne'
        ],
        'Nouvelle-Calédonie'          => [
            '988 - Nouvelle-Calédonie'
        ],
        'Occitanie'                   => [
            '09 - Ariège',
            '11 - Aude',
            '12 - Aveyron',
            '30 - Gard',
            '31 - Haute-Garonne',
            '32 - Gers',
            '34 - Hérault',
            '46 - Lot',
            '48 - Lozère',
            '65 - Hautes-Pyrénées',
            '66 - Pyrénées-Orientales',
            '81 - Tarn',
            '82 - Tarn-et-Garonne'
        ],
        'Pays de la Loire'            => [
            '44 - Loire-Atlantique',
            '49 - Maine-et-Loire',
            '53 - Mayenne',
            '72 - Sarthe',
            '85 - Vendée'
        ],
        'Provence-Alpes-Côte d\'Azur' => [
            '04 - Alpes-de-Haute-Provence',
            '05 - Hautes-Alpes',
            '06 - Alpes-Maritimes',
            '13 - Bouches-du-Rhône',
            '83 - Var',
            '84 - Vaucluse'
        ]
    ];
}

/**
 * List of profile fields
 *
 * @return array[]
 */
function local_mentor_specialization_get_profile_fields_values() {
    // Colonnes: shortname , name, datatype, description, descriptionformat, categoryid, sortorder.
    // required, locked, visible, forceunique, signup, defaultdata, defaultdataformat, param1, param2.
    return [
        ['status', 'Statut', 'menu', '', 1, 1, 4, 1, 0, 1, 0, 0, 'Autre', 0, 'local_mentor_specialization_list_status'],
        ['sexe', 'Sexe', 'menu', '', 1, 1, 2, 1, 0, 1, 0, 0, '', 0, 'local_mentor_specialization_list_sexe'],
        [
            'birthyear', 'Année de naissance', 'menu', '', 1, 1, 3, 1, 0, 1, 0, 0, '', 0,
            'local_mentor_specialization_list_years'
        ],
        ['category', 'Catégorie', 'menu', '', 1, 1, 5, 1, 0, 1, 0, 0, '', 0, 'local_mentor_specialization_list_categories'],
        [
            'mainentity', 'Entité de rattachement principale', 'menu', '', 1, 1, 7, 1, 0, 2, 0, 0, '', 0,
            'local_mentor_specialization_list_entities'
        ],
        [
            'secondaryentities', 'Entité(s) de rattachement secondaire(s)', 'autocomplete', '', 1, 1, 8, 0,
            0, 2, 0, 0, '', 0, 'local_mentor_specialization_list_entities', 1
        ],
        ['attachmentstructure', 'Structure de rattachement ', 'text', '', 1, 1, 9, 0, 0, 2, 0, 0, '', 1],
        ['affectation', 'Affectation', 'text', '', 1, 1, 10, 0, 0, 2, 0, 0, '', 0, '30', '2048', '0', '', ''],
        ['region', 'Région', 'menu', '', 1, 1, 11, 1, 0, 2, 0, 0, '', 0, 'local_mentor_specialization_list_regions'],
        [
            'department', 'Département', 'menu', '', 1, 1, 12, 0, 0, 2, 0, 0, '', 0,
            'local_mentor_specialization_list_departments'
        ],
        ['roleMentor', 'Rôle Mentor', 'text', '', 1, 1, 13, 0, 1, 0, 0, 0, '', 0, '30', '2048', '0', '', '']
    ];
}

/**
 * Create object from array row
 *
 * @param $values
 * @return stdClass
 */
function local_mentor_specialization_create_field_object_to_use($values) {
    $field                    = new stdClass();
    $field->shortname         = array_key_exists(0, $values) ? $values[0] : null;
    $field->name              = array_key_exists(1, $values) ? $values[1] : null;
    $field->datatype          = array_key_exists(2, $values) ? $values[2] : null;
    $field->description       = array_key_exists(3, $values) ? $values[3] : null;
    $field->descriptionformat = array_key_exists(4, $values) ? $values[4] : null;
    $field->categoryid        = array_key_exists(5, $values) ? $values[5] : null;
    $field->sortorder         = array_key_exists(6, $values) ? $values[6] : null;
    $field->required          = array_key_exists(7, $values) ? $values[7] : null;
    $field->locked            = array_key_exists(8, $values) ? $values[8] : null;
    $field->visible           = array_key_exists(9, $values) ? $values[9] : null;
    $field->forceunique       = array_key_exists(10, $values) ? $values[10] : null;
    $field->signup            = array_key_exists(11, $values) ? $values[11] : null;
    $field->defaultdata       = array_key_exists(12, $values) ? $values[12] : null;
    $field->defaultdataformat = array_key_exists(13, $values) ? $values[13] : null;

    // If it begin with "list_", excute associated funtion.
    // Else insert value.
    if (array_key_exists(14, $values)) {
        preg_match('/^local_mentor_specialization_list_/i', $values[14]) ? $field->param1 = call_user_func($values[14]) :
            $values[14];
    } else {
        $field->param1 = null;
    }

    $field->param2 = array_key_exists(15, $values) ? $values[15] : null;
    $field->param3 = array_key_exists(16, $values) ? $values[16] : null;
    $field->param4 = array_key_exists(17, $values) ? $values[17] : null;
    $field->param5 = array_key_exists(18, $values) ? $values[18] : null;

    return $field;
}

/**
 * Get list of categories
 *
 * @return string
 */
function local_mentor_specialization_list_categories() {

    $categories = [
        'Ouvriers d’Etat',
        'A+',
        'A',
        'B',
        'C',
        'Sans objet'
    ];

    return implode("\n", $categories);
}

/**
 * Get list of sexes
 *
 * @return string
 */
function local_mentor_specialization_list_sexe() {
    return "Homme\nFemme\nNe se prononce pas";
}

/**
 * Get list of status
 *
 * @return string
 */
function local_mentor_specialization_list_status() {

    $status = [
        'Fonctionnaire',
        'Contractuel',
        'Ouvriers d’Etat ',
        'Apprenti',
        'Autre'
    ];

    return implode("\n", $status);
}

/**
 * Get years
 */
function local_mentor_specialization_list_years() {
    return implode("\n", range(date('Y'), 1900));
}

/**
 * Get list of entities
 *
 * @return string
 * @throws dml_exception
 * @throws moodle_exception
 */
function local_mentor_specialization_list_entities() {
    global $CFG;
    require_once($CFG->dirroot . '/local/mentor_core/api/entity.php');

    // Get entities list.
    return \local_mentor_core\entity_api::get_entities_list(true);
}

/**
 * Get list of regions
 *
 * @return string
 */
function local_mentor_specialization_list_regions() {
    $regions = local_mentor_specialization_get_regions();
    sort($regions);
    return implode("\n", $regions);
}

/**
 * Get list of departments
 *
 * @return string
 */
function local_mentor_specialization_list_departments() {
    return implode("\n", local_mentor_specialization_get_departments());
}

/**
 * Get collections list
 *
 * @param string $data 'name' or 'color'
 * @return array
 * @throws dml_exception
 */
function local_mentor_specialization_get_collections($data = 'name') {
    // Get collections from config plugins.
    $collectionlist = get_config('local_mentor_specialization', 'collections');

    // If config not found, we return an empty array.
    if (false === $collectionlist || '' === $collectionlist) {
        return [];
    }

    // Convert line breaks into standard line breaks.
    $collectionlist = str_replace(["\r\n", "\r"], "\n", $collectionlist);

    $collections = [];
    foreach (explode("\n", $collectionlist) as $collectionitem) {
        $items                  = explode("|", $collectionitem);
        $collections[$items[0]] = ('color' === $data) ? $items[2] : $items[1];
    }

    return $collections;
}

/**
 * Get licenses list.
 *
 * @return string[]
 * @throws coding_exception
 */
function local_mentor_specialization_get_license_terms() {
    global $CFG;

    require_once($CFG->libdir . '/licenselib.php');

    $licenses = [];
    // Discard licenses without a name or source from enabled licenses.
    foreach (license_manager::get_active_licenses() as $license) {
        if (!empty($license->fullname) && !empty($license->source)) {
            $licenses[$license->shortname] = $license->fullname;
        }
    }

    return $licenses;
}

/***** Useful functions *****/

/**
 * Duplicate a role
 *
 * @param $fromshortname
 * @param $shortname
 * @param $fullname
 * @param $modelname
 * @return mixed|void
 * @throws coding_exception
 * @throws dml_exception
 */
function local_mentor_duplicate_role($fromshortname, $shortname, $fullname, $modelname) {
    global $DB;

    if (!$fromrole = $DB->get_record('role', ['shortname' => $fromshortname])) {
        mtrace('ERROR : role ' . $fromshortname . 'does not exist');
        return;
    }

    $newid = create_role($fullname, $shortname, '', $modelname);

    // Role allow override.
    $oldoverrides = $DB->get_records('role_allow_override', ['roleid' => $fromrole->id]);
    foreach ($oldoverrides as $oldoverride) {
        $oldoverride->roleid = $newid;
        $DB->insert_record('role_allow_override', $oldoverride);
    }

    // Role allow switch.
    $oldswitches = $DB->get_records('role_allow_switch', ['roleid' => $fromrole->id]);
    foreach ($oldswitches as $oldswitch) {
        $oldswitch->roleid = $newid;
        $DB->insert_record('role_allow_switch', $oldswitch);
    }

    // Role allow view.
    $oldviews = $DB->get_records('role_allow_view', ['roleid' => $fromrole->id]);
    foreach ($oldviews as $oldview) {
        $oldview->roleid = $newid;
        $DB->insert_record('role_allow_view', $oldview);
    }

    // Role allow assign.
    $oldassigns = $DB->get_records('role_allow_assign', ['roleid' => $fromrole->id]);
    foreach ($oldassigns as $oldassign) {
        $oldassign->roleid = $newid;
        $DB->insert_record('role_allow_assign', $oldassign);
    }

    // Role context levels.
    $oldcontexts = $DB->get_records('role_context_levels', ['roleid' => $fromrole->id]);
    foreach ($oldcontexts as $oldcontext) {
        $oldcontext->roleid = $newid;
        $DB->insert_record('role_context_levels', $oldcontext);
    }

    // Role capabilities.
    $oldcapabilities = $DB->get_records('role_capabilities', ['roleid' => $fromrole->id]);
    foreach ($oldcapabilities as $oldcapability) {
        $oldcapability->roleid = $newid;
        $DB->insert_record('role_capabilities', $oldcapability);
    }

    return $DB->get_record('role', ['id' => $newid]);
}

/**
 * Generate regions list in database
 *
 * @throws ddl_exception
 * @throws dml_exception
 */
function local_mentor_specialization_generate_regions() {
    global $DB;

    $dbman = $DB->get_manager();

    $table = new xmldb_table('regions');

    // Adding fields to table regions.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('code', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
    $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

    // Adding keys to table regions.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

    $regions = local_mentor_specialization_get_regions();

    foreach ($regions as $key => $value) {
        $region       = new stdClass();
        $region->code = $key;
        $region->name = $value;

        // Test if region exist.
        if (!$DB->record_exists('regions', array('name' => $region->name))) {
            $DB->insert_record('regions', $region);
        }
    }
}

/**
 * Generate all user additional profile fields
 *
 * @throws dml_exception
 */
function local_mentor_specialization_generate_user_fields() {
    global $DB;

    $fields = local_mentor_specialization_get_profile_fields_values();

    foreach ($fields as $value) {
        $field = local_mentor_specialization_create_field_object_to_use($value);

        if ($dbfield = $DB->get_record('user_info_field', ['shortname' => $field->shortname], 'id')) {
            $field->id = $dbfield->id;
            $field->id = $DB->update_record('user_info_field', $field);
        } else {
            $field->id = $DB->insert_record('user_info_field', $field);
        }
    }
}

/**
 * Remove editing capacities for a given role
 *
 * @params string $role
 * @param bool $training
 * @throws dml_exception
 */
function local_mentor_specialization_remove_capabilities_change_fields_for_role($role, $training = true) {
    global $DB;

    if ($training) {
        $capabilities = [
            'local/mentor_core:changefullname',
            'local/mentor_core:changeshortname',
            'local/mentor_core:changecontent',
            'local/mentor_core:changetraininggoal',
            'local/mentor_specialization:changecollection',
            'local/mentor_specialization:changeidsirh',
            'local/mentor_specialization:changeskills'
        ];
    } else {
        $capabilities = [
            'local/entities:manageentity',
            'local/entities:renamesubentity'
        ];
    }

    $userrole = $DB->get_record('role', ['shortname' => $role]);

    if (!$userrole) {
        return;
    }
    foreach ($capabilities as $capability) {
        local_mentor_core_remove_capability($userrole, $capability);
    }

}

/**
 * Remove capabilities for session
 *
 * @param $role
 * @throws dml_exception
 */
function local_mentor_specialization_remove_session_capabilities_change_fields_for_role($role) {
    global $DB;

    $capabilities = [
        'local/mentor_specialization:changesessionfullname',
        'local/mentor_specialization:changesessionshortname',
        'local/mentor_specialization:changesessionopento',
        'local/mentor_specialization:changesessionpubliccible',
        'local/mentor_specialization:changesessiononlinetime',
        'local/mentor_specialization:changesessionpresencetime',
        'local/mentor_specialization:changesessionpermanentsession',
        'local/mentor_specialization:changesessionstartdate',
        'local/mentor_specialization:changesessionenddate',
        'local/mentor_specialization:changesessionaccompaniment',
        'local/mentor_specialization:changesessionsessionmodalities',
        'local/mentor_specialization:changesessiontermsregistration',
        'local/mentor_specialization:changesessionmaxparticipants',
        'local/mentor_specialization:changesessionlocation',
        'local/mentor_specialization:changesessionorganizingstructure'
    ];

    $userrole = $DB->get_record('role', ['shortname' => $role]);

    if (!$userrole) {
        return;
    }
    foreach ($capabilities as $capability) {
        local_mentor_core_remove_capability($userrole, $capability);
    }

}

/**
 * Remove session sharing capabilities for referent local.
 *
 * @throws dml_exception
 */
function local_mentor_specialization_remove_session_sharing_for_referent_local() {
    global $DB;

    $userrole = $DB->get_record('role', ['shortname' => 'referentlocal']);

    if (!$userrole) {
        return;
    }

    local_mentor_core_remove_capability($userrole, 'local/mentor_specialization:changesessionopento');

}

/**
 * Add editing capacities to a given role
 *
 * @param string $role
 * @throws dml_exception
 */
function local_mentor_specialization_add_capabilities_change_fields_to_role($role) {
    global $DB;

    $capabilities = [
        'local/mentor_core:changefullname',
        'local/mentor_core:changeshortname',
        'local/mentor_core:changecontent',
        'local/mentor_core:changetraininggoal',
        'local/mentor_specialization:changecollection',
        'local/mentor_specialization:changeidsirh',
        'local/mentor_specialization:changeskills'
    ];

    $userrole = $DB->get_record('role', ['shortname' => $role]);

    $context = context_system::instance();

    if (!$userrole) {
        return;
    }
    foreach ($capabilities as $capability) {
        local_mentor_specialization_add_capabilities($userrole, $capability, $context->id);
    }

}

/**
 * Manage roles authorizations
 *
 * @throws dml_exception
 */
function local_mentor_specialization_manage_role_authorization() {
    global $DB;

    $participant           = $DB->get_record('role', ['shortname' => 'participant']);
    $admindedie            = $DB->get_record('role', ['shortname' => 'admindedie']);
    $respformation         = $DB->get_record('role', ['shortname' => 'respformation']);
    $reflocal              = $DB->get_record('role', ['shortname' => 'referentlocal']);
    $concepteur            = $DB->get_record('role', ['shortname' => 'concepteur']);
    $formateur             = $DB->get_record('role', ['shortname' => 'formateur']);
    $tuteur                = $DB->get_record('role', ['shortname' => 'tuteur']);
    $participantnonediteur = $DB->get_record('role', ['shortname' => 'participantnonediteur']);
    $reflocalnonediteur    = $DB->get_record('role', ['shortname' => 'reflocalnonediteur']);
    $coursecreator         = $DB->get_record('role', ['shortname' => 'coursecreator']);
    $teacher               = $DB->get_record('role', ['shortname' => 'teacher']);
    $editingteacher        = $DB->get_record('role', ['shortname' => 'editingteacher']);
    $guest                 = $DB->get_record('role', ['shortname' => 'guest']);
    $user                  = $DB->get_record('role', ['shortname' => 'user']);
    $frontpage             = $DB->get_record('role', ['shortname' => 'frontpage']);

    // Manage roles not assignable.
    $DB->delete_records('role_allow_assign', ['allowassign' => $admindedie->id]);
    $DB->delete_records('role_allow_assign', ['allowassign' => $reflocalnonediteur->id]);
    $DB->delete_records('role_allow_assign', ['allowassign' => $teacher->id]);
    $DB->delete_records('role_allow_assign', ['allowassign' => $editingteacher->id]);
    $DB->delete_records('role_allow_assign', ['allowassign' => $coursecreator->id]);
    $DB->delete_records('role_allow_assign', ['allowassign' => $participantnonediteur->id]);
    $DB->delete_records('role_allow_assign', ['roleid' => $editingteacher->id, 'allowassign' => $participant->id]);
    $DB->delete_records('role_allow_assign', ['roleid' => $reflocalnonediteur->id]);
    $DB->delete_records('role_allow_assign', ['roleid' => $tuteur->id]);

    local_mentor_specialization_assign_roles($respformation->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id
    ]);

    local_mentor_specialization_assign_roles($reflocal->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id
    ]);

    local_mentor_specialization_assign_roles($tuteur->id, [
        $participant->id,
    ]);

    local_mentor_specialization_assign_roles($admindedie->id, [
        $respformation->id,
        $reflocal->id,
        $concepteur->id,
        $formateur->id,
        $tuteur->id,
    ]);

    local_mentor_specialization_assign_roles($tuteur->id, [
        $participant->id,
    ]);

    local_mentor_specialization_assign_roles($formateur->id, [
        $tuteur->id,
    ]);

    local_mentor_specialization_assign_roles($concepteur->id, [
        $tuteur->id,
    ]);

    // Manage role not overridable.
    $DB->delete_records('role_allow_override', ['allowoverride' => $admindedie->id]);
    $DB->delete_records('role_allow_override', ['allowoverride' => $reflocalnonediteur->id]);
    $DB->delete_records('role_allow_override', ['allowoverride' => $participantnonediteur->id]);
    $DB->delete_records('role_allow_override', ['allowoverride' => $coursecreator->id]);
    $DB->delete_records('role_allow_override', ['allowoverride' => $editingteacher->id]);
    $DB->delete_records('role_allow_override', ['allowoverride' => $teacher->id]);
    $DB->delete_records('role_allow_override', ['allowoverride' => $user->id]);
    $DB->delete_records('role_allow_override', ['allowoverride' => $frontpage->id]);
    $DB->delete_records('role_allow_override', ['allowoverride' => $guest->id]);
    $DB->delete_records('role_allow_override', ['roleid' => $tuteur->id]);
    $DB->delete_records('role_allow_override', ['roleid' => $editingteacher->id, 'allowoverride' => $participant->id]);

    local_mentor_specialization_override_roles($respformation->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id
    ]);

    local_mentor_specialization_override_roles($reflocal->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id
    ]);

    local_mentor_specialization_override_roles($admindedie->id, [
        $respformation->id,
        $reflocal->id,
        $concepteur->id,
        $formateur->id,
        $tuteur->id
    ]);

    local_mentor_specialization_override_roles($concepteur->id, [
        $tuteur->id
    ]);

    local_mentor_specialization_override_roles($formateur->id, [
        $tuteur->id
    ]);

    // Manage switchable roles.
    $DB->delete_records('role_allow_switch', ['roleid' => $respformation->id, 'allowswitch' => $admindedie->id]);
    $DB->delete_records('role_allow_switch', ['roleid' => $reflocal->id, 'allowswitch' => $admindedie->id]);
    $DB->delete_records('role_allow_switch', ['roleid' => $admindedie->id, 'allowswitch' => $admindedie->id]);
    $DB->delete_records('role_allow_switch', ['roleid' => $tuteur->id, 'allowswitch' => $participantnonediteur->id]);
    $DB->delete_records('role_allow_switch', ['roleid' => $teacher->id, 'allowswitch' => $participant->id]);
    $DB->delete_records('role_allow_switch', ['roleid' => $editingteacher->id, 'allowswitch' => $participant->id]);
    $DB->delete_records('role_allow_switch', ['roleid' => $reflocalnonediteur->id]);
    $DB->delete_records('role_allow_switch', ['roleid' => $tuteur->id]);
    $DB->delete_records('role_allow_switch', ['allowswitch' => $admindedie->id]);
    $DB->delete_records('role_allow_switch', ['allowswitch' => $reflocalnonediteur->id]);
    $DB->delete_records('role_allow_switch', ['allowswitch' => $participantnonediteur->id]);
    $DB->delete_records('role_allow_switch', ['allowswitch' => $coursecreator->id]);
    $DB->delete_records('role_allow_switch', ['allowswitch' => $editingteacher->id]);
    $DB->delete_records('role_allow_switch', ['allowswitch' => $teacher->id]);
    $DB->delete_records('role_allow_switch', ['allowswitch' => $user->id]);
    $DB->delete_records('role_allow_switch', ['allowswitch' => $frontpage->id]);
    $DB->delete_records('role_allow_switch', ['allowswitch' => $guest->id]);

    local_mentor_specialization_switch_roles($concepteur->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id
    ]);

    local_mentor_specialization_switch_roles($formateur->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id
    ]);

    local_mentor_specialization_switch_roles($respformation->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id
    ]);

    local_mentor_specialization_switch_roles($reflocal->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id
    ]);

    local_mentor_specialization_switch_roles($admindedie->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id,
        $respformation->id,
        $reflocal->id,
        $participantnonediteur->id,
    ]);

    local_mentor_specialization_switch_roles($tuteur->id, [
        $participant->id
    ]);

    // Manage role viewable.
    $DB->delete_records('role_allow_view', ['roleid' => $respformation->id, 'allowview' => $admindedie->id]);
    $DB->delete_records('role_allow_view', ['roleid' => $reflocal->id, 'allowview' => $admindedie->id]);
    $DB->delete_records('role_allow_view', ['roleid' => $admindedie->id, 'allowview' => $admindedie->id]);
    $DB->delete_records('role_allow_view', ['roleid' => $reflocalnonediteur->id]);
    $DB->delete_records('role_allow_view', ['roleid' => $tuteur->id]);
    $DB->delete_records('role_allow_view', ['roleid' => $teacher->id]);
    $DB->delete_records('role_allow_view', ['roleid' => $editingteacher->id]);
    $DB->delete_records('role_allow_view', ['roleid' => $coursecreator->id]);
    $DB->delete_records('role_allow_view', ['allowview' => $admindedie->id]);
    $DB->delete_records('role_allow_view', ['allowview' => $coursecreator->id]);
    $DB->delete_records('role_allow_view', ['allowview' => $editingteacher->id]);
    $DB->delete_records('role_allow_view', ['allowview' => $teacher->id]);
    $DB->delete_records('role_allow_view', ['allowview' => $user->id]);
    $DB->delete_records('role_allow_view', ['allowview' => $frontpage->id]);
    $DB->delete_records('role_allow_view', ['allowview' => $guest->id]);

    local_mentor_specialization_view_roles($reflocalnonediteur->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id,
        $participant->id,
        $participantnonediteur->id
    ]);

    local_mentor_specialization_view_roles($respformation->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id,
        $respformation->id,
        $participantnonediteur->id,
        $reflocal->id
    ]);

    local_mentor_specialization_view_roles($reflocal->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id,
        $respformation->id,
        $participantnonediteur->id,
        $reflocal->id
    ]);

    local_mentor_specialization_view_roles($admindedie->id, [
        $respformation->id,
        $reflocal->id,
        $reflocalnonediteur->id,
        $concepteur->id,
        $formateur->id,
        $tuteur->id,
        $participantnonediteur->id
    ]);

    local_mentor_specialization_view_roles($participant->id, [
        $concepteur->id,
        $formateur->id,
        $tuteur->id,
        $participant->id,
        $participantnonediteur->id
    ]);

    local_mentor_specialization_view_roles($participantnonediteur->id, [
        $formateur->id,
        $tuteur->id,
        $concepteur->id,
        $participantnonediteur->id,
        $participant->id
    ]);

    local_mentor_specialization_view_roles($tuteur->id, [
        $participant->id,
        $participantnonediteur->id,
        $formateur->id,
        $concepteur->id,
        $tuteur->id
    ]);

    local_mentor_specialization_view_roles($formateur->id, [
        $tuteur->id,
        $participantnonediteur->id,
        $formateur->id,
        $concepteur->id,
    ]);

    local_mentor_specialization_view_roles($concepteur->id, [
        $tuteur->id,
        $participantnonediteur->id,
        $formateur->id,
        $concepteur->id,
    ]);

}

/**
 * Allow a role to assign other roles
 *
 * @param int $roleid
 * @param array $roles
 * @throws dml_exception
 */
function local_mentor_specialization_assign_roles($roleid, $roles) {
    global $DB;

    foreach ($roles as $role) {
        if ($DB->record_exists('role_allow_assign', ['roleid' => $roleid, 'allowassign' => $role])) {
            continue;
        }

        $assignobj              = new \stdClass();
        $assignobj->roleid      = $roleid;
        $assignobj->allowassign = $role;
        $DB->insert_record('role_allow_assign', $assignobj);
    }

}

/**
 * Allow a role to override other roles
 *
 * @param int $roleid
 * @param array $roles
 * @throws dml_exception
 */
function local_mentor_specialization_override_roles($roleid, $roles) {
    global $DB;

    foreach ($roles as $role) {
        if ($DB->record_exists('role_allow_override', ['roleid' => $roleid, 'allowoverride' => $role])) {
            continue;
        }
        $overrideobj                = new \stdClass();
        $overrideobj->roleid        = $roleid;
        $overrideobj->allowoverride = $role;
        $DB->insert_record('role_allow_override', $overrideobj);
    }

}

/**
 * Allow a role to switch to other roles
 *
 * @param int $roleid
 * @param array $roles
 * @throws dml_exception
 */
function local_mentor_specialization_switch_roles($roleid, $roles) {
    global $DB;

    foreach ($roles as $role) {
        if ($DB->record_exists('role_allow_switch', ['roleid' => $roleid, 'allowswitch' => $role])) {
            continue;
        }

        $switchobj              = new \stdClass();
        $switchobj->roleid      = $roleid;
        $switchobj->allowswitch = $role;
        $DB->insert_record('role_allow_switch', $switchobj);
    }
}

/**
 * Allow a role to view an other roles
 *
 * @param int $roleid
 * @param array $views
 * @throws dml_exception
 */
function local_mentor_specialization_view_roles($roleid, $views) {
    global $DB;

    foreach ($views as $view) {
        if ($DB->record_exists('role_allow_view', ['roleid' => $roleid, 'allowview' => $view])) {
            continue;
        }

        $viewobj            = new \stdClass();
        $viewobj->roleid    = $roleid;
        $viewobj->allowview = $view;
        $DB->insert_record('role_allow_view', $viewobj);
    }

}

/**
 * Initialize snippets
 */
function local_mentor_specialization_init_snippets() {

    global $CFG;

    set_config('snippetcount', 18, 'atto_snippet');

    // Consigne.
    set_config('snippetname_1', 'Consigne', 'atto_snippet');
    set_config('snippetkey_1', 'consigne', 'atto_snippet');
    set_config('snippetinstructions_1', '', 'atto_snippet');
    set_config('defaults_1', '', 'atto_snippet');
    set_config('snippet_1', '<div class="mentor-card container-fluid consigne">
    <div class="row">
        <div class="col-md-12 col-lg-2 mentor-card-left">
            <div>A vous de jouer</div>
            <div>[fa-address-book]</div>
        </div>
        <div class="col-lg-10 mentor-card-content">
            <h5>Activité à réaliser</h5>
            <div>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu risus non est imperdiet condimentum.
                Vivamus vel elit posuere purus rhoncus vestibulum non eu est.
            </div>
            <h5>Durée<span class="duration"> 2h</span></h5>
            <h5>Modalités</h5>
            <ul class="modalities row">
                <li class="col-sm-12 col-md-3 col-lg-3">[fa-user] Individuel</li>
                <li class="col-sm-12 col-md-3 col-lg-3">[fa-users] Collectif</li>
                <li class="col-sm-12 col-md-3 col-lg-3">[fa-desktop] Virtuel</li>
                <li class="col-sm-12 col-md-3 col-lg-3">[fa-comments] Présentiel</li>
            </ul>
        </div>
    </div>
</div>', 'atto_snippet');
    // Image / Texte.
    set_config('snippetname_2', 'Image/Texte', 'atto_snippet');
    set_config('snippetkey_2', 'image-texte', 'atto_snippet');
    set_config('snippetinstructions_2', '', 'atto_snippet');
    set_config('defaults_2', '', 'atto_snippet');
    set_config('snippet_2', '<div class="mentor-image-text container-fluid">
    <div class="row">
        <div class="col-md-12 col-lg-6">
           <img src="' . $CFG->wwwroot . '/theme/mentor/pix/mentor-image.png" />
        </div>
        <div class="col-md-12 col-lg-6"><p>
    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
    Donec eu risus non est imperdiet condimentum. Vivamus vel elit posuere purus rhoncus vestibulum non eu est.
    Integer commodo hendrerit quam sed posuere
    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu risus non est imperdiet condimentum.
    Vivamus vel elit posuere purus rhoncus vestibulum non eu est.
        </p></div>
    </div>
</div>', 'atto_snippet');

    // Separator.
    set_config('snippetname_3', 'Séparateur', 'atto_snippet');
    set_config('snippetkey_3', 'separateur', 'atto_snippet');
    set_config('snippetinstructions_3', '', 'atto_snippet');
    set_config('defaults_3', '', 'atto_snippet');
    set_config('snippet_3', '<br><hr class="mentor-separator">', 'atto_snippet');

    // A retenir.
    set_config('snippetname_4', 'À retenir', 'atto_snippet');
    set_config('snippetkey_4', 'a retenir', 'atto_snippet');
    set_config('snippetinstructions_4', '', 'atto_snippet');
    set_config('defaults_4', '', 'atto_snippet');
    set_config('snippet_4', '<div class="mentor-card container-fluid retenir">
    <div class="row">
        <div class="col-md-12 col-lg-2 mentor-card-left">
            <div>À retenir</div>
            <div>[fa-book]</div>
        </div>
        <div class="col-lg-10 mentor-card-content">
            <h5>Sous titre</h5>
            <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu risus non est imperdiet condimentum. ' .
                            'Vivamus vel elit posuere purus rhoncus vestibulum non eu est.</div>
        </div>
    </div>
</div>', 'atto_snippet');

    // Aller plus loin.
    set_config('snippetname_5', 'Aller plus loin', 'atto_snippet');
    set_config('snippetkey_5', 'aller plus loin', 'atto_snippet');
    set_config('snippetinstructions_5', '', 'atto_snippet');
    set_config('defaults_5', '', 'atto_snippet');
    set_config('snippet_5', '<div class="mentor-card container-fluid plus-loin">
    <div class="row">
        <div class="col-md-12 col-lg-2 mentor-card-left">
            <div>Aller plus loin</div>
            <div>[fa-angle-double-right]</div>
        </div>
        <div class="col-lg-10 mentor-card-content">
            <h5>Sous titre</h5>
            <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu risus non est imperdiet condimentum. ' .
                            'Vivamus vel elit posuere purus rhoncus vestibulum non eu est.</div>
        </div>
    </div>
</div>', 'atto_snippet');

    // Dans la pratique.
    set_config('snippetname_6', 'Dans la pratique', 'atto_snippet');
    set_config('snippetkey_6', 'dans la pratique', 'atto_snippet');
    set_config('snippetinstructions_6', '', 'atto_snippet');
    set_config('defaults_6', '', 'atto_snippet');
    set_config('snippet_6', '<div class="mentor-card container-fluid pratique">
    <div class="row">
        <div class="col-md-12 col-lg-2 mentor-card-left">
            <div>Dans la pratique</div>
            <div>[fa-briefcase]</div>
        </div>
        <div class="col-lg-10 mentor-card-content">
            <h5>Sous titre</h5>
            <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu risus non est imperdiet condimentum. ' .
                            'Vivamus vel elit posuere purus rhoncus vestibulum non eu est.</div>
        </div>
    </div>
</div>', 'atto_snippet');

    // Définition.
    set_config('snippetname_7', 'Définition', 'atto_snippet');
    set_config('snippetkey_7', 'definition', 'atto_snippet');
    set_config('snippetinstructions_7', '', 'atto_snippet');
    set_config('defaults_7', '', 'atto_snippet');
    set_config('snippet_7', '<div class="mentor-card container-fluid definition">
    <div class="row">
        <div class="col-md-12 col-lg-2 mentor-card-left">
            <div>Définition</div>
            <div>[fa-info-circle]</div>
        </div>
        <div class="col-lg-10 mentor-card-content">
            <h5>Sous titre</h5>
            <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu risus non est imperdiet condimentum. ' .
                            'Vivamus vel elit posuere purus rhoncus vestibulum non eu est.</div>
        </div>
    </div>
</div>', 'atto_snippet');

    // Important.
    set_config('snippetname_8', 'Important', 'atto_snippet');
    set_config('snippetkey_8', 'important', 'atto_snippet');
    set_config('snippetinstructions_8', '', 'atto_snippet');
    set_config('defaults_8', '', 'atto_snippet');
    set_config('snippet_8', '<div class="mentor-card container-fluid important">
    <div class="row">
        <div class="col-md-12 col-lg-2 mentor-card-left">
            <div>Important</div>
            <div>[fa-exclamation-triangle]</div>
        </div>
        <div class="col-lg-10 mentor-card-content">
            <h5>Sous titre</h5>
            <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu risus non est imperdiet condimentum. ' .
                            'Vivamus vel elit posuere purus rhoncus vestibulum non eu est.</div>
        </div>
    </div>
</div>', 'atto_snippet');

    // Collapse.
    set_config('snippetname_9', 'Collapse', 'atto_snippet');
    set_config('snippetkey_9', 'collapse', 'atto_snippet');
    set_config('snippetinstructions_9', '', 'atto_snippet');
    set_config('defaults_9', '', 'atto_snippet');
    set_config('snippet_9', '<div class="mentor-accordion">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <button class="btn btn-link" data-toggle="collapse" aria-expanded="true">Collapsible</button>
            </h5>
            <p class="header-right">+</p>
        </div>
        <div class="collapse">
            <div class="card-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad ' .
                            'squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck ' .
                            'quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird ' .
                            'on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh ' .
                            'helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan ' .
                            'excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim ' .
                            'aesthetic synth nesciunt you probably haven\'t heard of them accusamus labore sustainable VHS.
            </div>
        </div>
    </div>
  </div>
</div>
<br>', 'atto_snippet');

    // Titre 2 noir.
    set_config('snippetname_10', 'Titre 2 noir', 'atto_snippet');
    set_config('snippetkey_10', 'titre 2 noir', 'atto_snippet');
    set_config('snippetinstructions_10', '', 'atto_snippet');
    set_config('defaults_10', '', 'atto_snippet');
    set_config('snippet_10', '<h2 class="mentor-h2 mentor-h2-black">Titre 2</h2>
<br>', 'atto_snippet');

    // Titre 3 noir.
    set_config('snippetname_11', 'Titre 3 noir', 'atto_snippet');
    set_config('snippetkey_11', 'titre 3 noir', 'atto_snippet');
    set_config('snippetinstructions_11', '', 'atto_snippet');
    set_config('defaults_11', '', 'atto_snippet');
    set_config('snippet_11', '<h3 class="mentor-h3 mentor-h3-black">Titre 3</h3>
<br>', 'atto_snippet');

    // Titre 2 bleu.
    set_config('snippetname_12', 'Titre 2 bleu', 'atto_snippet');
    set_config('snippetkey_12', 'titre 2 bleu', 'atto_snippet');
    set_config('snippetinstructions_12', '', 'atto_snippet');
    set_config('defaults_12', '', 'atto_snippet');
    set_config('snippet_12', '<h2 class="mentor-h2 mentor-h2-blue">Titre 2</h2>
<br>', 'atto_snippet');

    // Titre 3 bleu.
    set_config('snippetname_13', 'Titre 3 bleu', 'atto_snippet');
    set_config('snippetkey_13', 'titre 3 bleu', 'atto_snippet');
    set_config('snippetinstructions_13', '', 'atto_snippet');
    set_config('defaults_13', '', 'atto_snippet');
    set_config('snippet_13', '<h3 class="mentor-h3 mentor-h3-blue">Titre 3</h3>
<br>', 'atto_snippet');

    // Card.
    set_config('snippetname_14', 'Card', 'atto_snippet');
    set_config('snippetkey_14', 'card', 'atto_snippet');
    set_config('snippetinstructions_14', '', 'atto_snippet');
    set_config('defaults_14', '', 'atto_snippet');
    set_config('snippet_14', '<div class="row cards-mentor">
    <div class="card card-mentor" style="width: 18rem;">
        <i class="fa fa-plus add-card" aria-hidden="true"></i>
        <i class="fa fa-trash-o fa-2x remove-card" aria-hidden="true"></i>
        <img class="card-img-top" src="' . $CFG->wwwroot . '/theme/mentor/pix/mentor-image.png" alt="Card image cap">
        <div class="card-body">
            <h5 class="card-title">Card title</h5>
            <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card\'s
                content.</p>
            <a href="#" class="btn btn-primary  card-link">Go somewhere</a>
        </div>
    </div>
</div>', 'atto_snippet');

    // Button.
    set_config('snippetname_15', 'Bouton', 'atto_snippet');
    set_config('snippetkey_15', 'bouton', 'atto_snippet');
    set_config('snippetinstructions_15', '', 'atto_snippet');
    set_config('defaults_15', 'Texte=,Url=', 'atto_snippet');
    set_config('snippet_15', '<div class="btn btn-primary btn-mentor-snippet mt-3 mb-3 card-link" >
     <a href="{{Url}}" role="button">{{Texte}}<br></a>
</div>', 'atto_snippet');

    // Class virtuelle.
    set_config('snippetname_16', 'Classe virtuelle', 'atto_snippet');
    set_config('snippetkey_16', 'classe virtuelle', 'atto_snippet');
    set_config('snippetinstructions_16', '', 'atto_snippet');
    set_config('defaults_16', 'Url=', 'atto_snippet');
    set_config('snippet_16', '<div class="mentor-card container-fluid class-virtuelle">
    <div class="row">
        <div class="col-md-12 col-lg-2 mentor-card-left">
            <div>Classe Virtuelle</div>
            <div>[fa-desktop]</div>
        </div>
        <div class="col-lg-10 mentor-card-content">
            <h5>Sous titre</h5>
            <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu risus non est imperdiet condimentum. ' .
                             'Vivamus vel elit posuere purus rhoncus vestibulum non eu est.</div>
            <div class="date mt-3">[fa-calendar-o] 12 Dec 2020 - 14:00 à 17:00</div>
            <div class="btn btn-primary mt-4 card-link">
                 <a href="{{Url}}" role="button">Accéder à la classe virtuelle<br></a>
            </div>
        </div>
    </div>
</div>', 'atto_snippet');

    // Presentiel.
    set_config('snippetname_17', 'Presentiel', 'atto_snippet');
    set_config('snippetkey_17', 'presentiel', 'atto_snippet');
    set_config('snippetinstructions_17', '', 'atto_snippet');
    set_config('defaults_17', '', 'atto_snippet');
    set_config('snippet_17', '<div class="mentor-card container-fluid presentiel">
    <div class="row">
        <div class="col-md-12 col-lg-2 mentor-card-left">
            <div>Présentiel</div>
            <div>[fa-user-o][fa-user-o]</div>
        </div>
        <div class="col-lg-10 mentor-card-content">
            <h5>Sous titre</h5>
            <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu risus non est imperdiet condimentum. ' .
                             'Vivamus vel elit posuere purus rhoncus vestibulum non eu est.</div>
            <div class="date mt-3">[fa-calendar-o]<span class="ml-2">12 Dec 2020 - 14:00 à 17:00</span></div>
            <div class="adresse mt-3">
                <div class="adresse-title">Adresse</div>
                <div class="adresse-content">[fa-map-marker] <span class="adresse-position ml-2">
                15 rue castagnary - paris 75000
                </span></div>
            </div>
        </div>
    </div>
</div>', 'atto_snippet');

    // Présentation de la formation.
    set_config('snippetname_18', 'Presentation de la formation', 'atto_snippet');
    set_config('snippetkey_18', 'Ppresentation de la formation', 'atto_snippet');
    set_config('snippetinstructions_18', '', 'atto_snippet');
    set_config('defaults_18', '', 'atto_snippet');
    set_config('snippet_18', '<div class="mentor-card container-fluid presentation-formation">
    <div class="row">
        <div class="col-md-12 col-lg-2 mentor-card-left">
            <div>Présentation de la Formation</div>
            <div>[fa-bookmark-o]</div>
        </div>
        <div class="col-lg-10 mentor-card-content">
            <h5 class="mb-1">Déroulement de la formation</h5>
            <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu risus non est imperdiet condimentum. ' .
                             'Vivamus vel elit posuere purus rhoncus vestibulum non eu est.</div>
            <div class="date date-presentiel mt-3">
                [fa-user-o][fa-user-o]<span class="ml-2">Présentiel, 12 Dec 2020 - 14:00 à 17:00
            </span></div>
            <div class="date date-virtuelle mt-1">[fa-desktop]<span class="ml-3">Virtuelle, 12 Dec 2020 - 14:00 à 17:00</span></div>
            <h5 class="mt-4 mb-1">Documents</h5>
            <div class="file mt-3">
                <a href="#" >[fa-file-text-o] Carnet de bord</a>
                <a href="#" >[fa-file-text-o] Documents 2</a>
            </div>
            <h5 class="mt-4 mb-1">Modalités d\'évaluation</h5>
            <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu risus non est imperdiet condimentum.</div>
            <h5 class="mt-4 mb-1">Modalités d\'accompagnement</h5>
            <div>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eu risus non est imperdiet condimentum.</div>
            <div class="formateur mt-2">
                <div class="formateur-title">Formateurs</div>
                <div class="formateur-content mt-1">
                    <img src="' . $CFG->wwwroot . '/theme/mentor/pix/profil.png">Nom Prénom
                    <img src="' . $CFG->wwwroot . '/theme/mentor/pix/profil.png">Nom Prénom
                    <img src="' . $CFG->wwwroot . '/theme/mentor/pix/profil.png">Nom Prénom
                </div>
            </div>
        </div>
    </div>
</div>', 'atto_snippet');

}

/**
 * Initialise the platform configuration
 *
 * @throws coding_exception
 * @throws ddl_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function local_mentor_specialization_init_config() {
    global $CFG, $DB;

    /***** Create roles *****/

    if ($manager = $DB->get_record('role', ['shortname' => 'manager'])) {
        mtrace('Create role: Administrateur d\'espace dédié');
        $manager->name      = 'Administrateur d\'espace dédié';
        $manager->shortname = 'admindedie';
        $DB->update_record('role', $manager);
    }

    $admindedie = $DB->get_record('role', ['shortname' => 'admindedie']);

    if ($student = $DB->get_record('role', ['shortname' => 'student'])) {
        mtrace('Create role: Participant');
        $student->name      = 'Participant';
        $student->shortname = 'participant';
        $DB->update_record('role', $student);
    }

    if (!$participant = $DB->get_record('role', ['shortname' => 'participant'])) {
        mtrace('WARNING : participant role is missing!!!');
    }

    if (!$noteditingstudent = $DB->get_record('role', ['shortname' => 'participantnonediteur'])) {
        mtrace('Create role: Participant non éditeur');
        $noteditingstudent = local_mentor_duplicate_role('participant', 'participantnonediteur', 'Participant non contributeur',
            'student');
    }

    if (!$respformation = $DB->get_record('role', ['shortname' => 'respformation'])) {
        mtrace('Create role: Responsable de formation central');
        $respformation = local_mentor_duplicate_role('admindedie', 'respformation', 'Responsable de formation central', 'manager');
    }

    if (!$reflocal = $DB->get_record('role', ['shortname' => 'referentlocal'])) {
        mtrace('Create role: Référent local de formation');
        $reflocal = local_mentor_duplicate_role('admindedie', 'referentlocal', 'Référent local de formation', 'manager');
    }

    if (!$concepteur = $DB->get_record('role', ['shortname' => 'concepteur'])) {
        mtrace('Create role: Concepteur');
        $concepteur = local_mentor_duplicate_role('editingteacher', 'concepteur', 'Concepteur', 'editingteacher');
    }

    if (!$formateur = $DB->get_record('role', ['shortname' => 'formateur'])) {
        mtrace('Create role: Formateur');
        $formateur = local_mentor_duplicate_role('editingteacher', 'formateur', 'Formateur', 'editingteacher');
    }

    if (!$tuteur = $DB->get_record('role', ['shortname' => 'tuteur'])) {
        mtrace('Create role: Tuteur');
        $tuteur = local_mentor_duplicate_role('teacher', 'tuteur', 'Tuteur', 'teacher');
    }

    if (!$participantdemonstration = $DB->get_record('role', ['shortname' => 'participantdemonstration'])) {
        mtrace('Create role: Participant démonstration');
        $participantdemonstration = local_mentor_duplicate_role('guest', 'participantdemonstration', 'Participant démonstration',
            'guest');
    }

    /***** Disable plugins***/
    // Disable Online users block.
    mtrace('Disable plugin: block_online_users');
    $DB->set_field('block', 'visible', 0, ['name' => 'online_users']);

    /***** Configure site****/
    // Force login.
    local_mentor_core_set_moodle_config('forcelogin', 1);

    // Hide the guest login button.
    local_mentor_core_set_moodle_config('guestloginbutton', 0);

    // Generate user fields.
    mtrace('Generate user fields');
    local_mentor_specialization_generate_user_fields();

    // Generate regions table.
    mtrace('Generate regions');
    local_mentor_specialization_generate_regions();

    // Remove capabilities for a designer.
    mtrace('Remove fields capabilities for a designer');
    local_mentor_specialization_remove_capabilities_change_fields_for_role('concepteur');

    // Remove capabilities for a resp formation.
    mtrace('Remove fields capabilities for a respformation');
    local_mentor_specialization_remove_capabilities_change_fields_for_role('respformation', false);

    // Remove capabilities for a resp formation.
    mtrace('Remove fields capabilities for a referentlocal');
    local_mentor_specialization_remove_capabilities_change_fields_for_role('referentlocal', false);

    // Remove session capabilities for a designer.
    mtrace('Remove session fields capabilities for a designer');
    local_mentor_specialization_remove_session_capabilities_change_fields_for_role('concepteur');

    // Remove session capabilities for a designer.
    mtrace('Remove session fields capabilities for a former');
    local_mentor_specialization_remove_session_capabilities_change_fields_for_role('formateur');

    // Remove session capabilities for a designer.
    mtrace('Remove session sharing capability for a referent local');
    local_mentor_specialization_remove_session_sharing_for_referent_local();

    // Manage sessions.
    local_mentor_core_add_capability($respformation, 'local/session:manage');
    local_mentor_core_add_capability($reflocal, 'local/session:manage');

    // Share trainings.
    local_mentor_core_add_capability($respformation, 'local/mentor_core:sharetrainings');
    local_mentor_core_add_capability($respformation, 'local/mentor_core:sharetrainingssubentities');
    local_mentor_core_remove_capability($reflocal, 'local/mentor_core:sharetrainings');
    local_mentor_core_add_capability($reflocal, 'local/mentor_core:sharetrainingssubentities');

    // Receive certificate issues.
    local_mentor_core_add_capability($formateur, 'mod/customcert:receiveissue');

    // Import users by csv.
    local_mentor_core_add_capability($formateur, 'local/mentor_core:importusers');

    // Entity management.
    local_mentor_core_remove_capability($admindedie, 'local/mentor_specialization:changeentityname');
    local_mentor_core_remove_capability($respformation, 'local/mentor_specialization:changeentityname');
    local_mentor_core_remove_capability($reflocal, 'local/mentor_specialization:changeentityname');

    local_mentor_core_remove_capability($admindedie, 'local/mentor_specialization:changeentityregion');
    local_mentor_core_remove_capability($respformation, 'local/mentor_specialization:changeentityregion');
    local_mentor_core_remove_capability($reflocal, 'local/mentor_specialization:changeentityregion');

    local_mentor_core_add_capability($admindedie, 'local/mentor_specialization:changeentitylogo');
    local_mentor_core_remove_capability($respformation, 'local/mentor_specialization:changeentitylogo');
    local_mentor_core_remove_capability($reflocal, 'local/mentor_specialization:changeentitylogo');

    // Participant non contributeur.
    $noteditingstudentcapabilities = [
        'moodle/course:configurecustomfields',
        'moodle/course:recommendactivity',
        'moodle/competency:evidencedelete',
        'moodle/competency:plancomment',
        'moodle/competency:plancommentown',
        'moodle/competency:planrequestreview',
        'moodle/competency:planrequestreviewown',
        'moodle/competency:planreview',
        'moodle/competency:userevidencemanageown',
        'enrol/self:unenrolself',
        'moodle/comment:post',
        'moodle/comment:view',
        'moodle/competency:coursecompetencygradable',
        'moodle/question:flag',
        'moodle/rating:rate',
        'mod/bigbluebuttonbn:join',
        'mod/chat:chat',
        'mod/choice:choose',
        'mod/choicegroup:choose',
        'mod/coursebadges:choose',
        'mod/coursebadges:deletechoice',
        'mod/data:comment',
        'mod/data:writeentry',
        'mod/feedback:complete',
        'mod/forum:addquestion',
        'mod/forum:allowforcesubscribe',
        'mod/forum:canoverridecutoff',
        'mod/forum:canoverridediscussionlock',
        'mod/forum:canposttomygroups',
        'mod/forum:createattachment',
        'mod/forum:deleteanypost',
        'mod/forum:deleteownpost',
        'mod/forum:exportownpost',
        'mod/forum:replynews',
        'mod/forum:replypost',
        'mod/forum:startdiscussion',
        'mod/glossary:comment',
        'mod/glossary:managecategories',
        'mod/glossary:managecomments',
        'mod/glossary:manageentries',
        'mod/glossary:rate',
        'mod/glossary:write',
        'mod/h5pactivity:submit',
        'mod/lti:view',
        'mod/questionnaire:submit',
        'mod/quiz:attempt',
        'mod/scorm:savetrack',
        'mod/scorm:skipview',
        'mod/survey:participate',
        'mod/wiki:createpage',
        'mod/wiki:editcomment',
        'mod/wiki:editpage',
        'mod/wiki:viewcomment',
        'mod/workshop:exportsubmissions',
        'mod/workshop:peerassess',
        'mod/workshop:submit',
        'mod/workshop:view'
    ];
    local_mentor_core_remove_capabilities($noteditingstudent, $noteditingstudentcapabilities);

    // Participant démonstration.
    $participantdemonstrationcapabilities = [
        'block/completion_progress:showbar',
        'block/summary:canseehiddensections',
        'booktool/print:print',
        'mod/assignment:view',
        'mod/assign:view',
        'mod/bigbluebuttonbn:view',
        'mod/book:read',
        'mod/book:viewhiddenchapters',
        'mod/chat:view',
        'mod/choice:view',
        'mod/customcert:view',
        'mod/data:view',
        'mod/data:viewentry',
        'mod/feedback:view',
        'mod/folder:view',
        'mod/forum:viewdiscussion',
        'mod/glossary:view',
        'mod/h5pactivity:view',
        'mod/imscp:view',
        'mod/label:view',
        'mod/lesson:view',
        'mod/page:view',
        'mod/questionnaire:view',
        'mod/quiz:view',
        'mod/resource:view',
        'mod/url:view',
        'mod/via:view',
        'mod/wiki:viewpage',
        'mod/workshop:view',
        'moodle/block:view',
        'moodle/blog:search',
        'moodle/blog:view',
        'moodle/comment:view',
        'moodle/course:ignoreavailabilityrestrictions',
        'moodle/course:viewhiddenactivities',
        'moodle/course:viewhiddensections',
        'moodle/search:query',
    ];

    local_mentor_core_add_capabilities($participantdemonstration, $participantdemonstrationcapabilities);
    local_mentor_core_prevent_capability($participantdemonstration, 'block/online_users:viewlist');
    local_mentor_core_add_context_levels($participantdemonstration->id, [CONTEXT_COURSE]);

    // Copy lang files.
    if (!is_dir($CFG->dataroot . '/lang')) {
        mkdir($CFG->dataroot . '/lang', 0775);
    }
    $dest = $CFG->dataroot . '/lang/fr_local';
    if (!is_dir($dest)) {
        mkdir($dest, 0775);
    }
    $src = $CFG->dirroot . '/local/mentor_specialization/data/lang/fr_local/*';
    shell_exec("cp -r $src $dest");

    // Init snippets.
    local_mentor_specialization_init_snippets();

    // Disable activity plugins.
    local_mentor_specialization_disable_module('chat');
    local_mentor_specialization_disable_module('imscp');
    local_mentor_specialization_disable_module('lti');
    local_mentor_specialization_disable_module('survey');

    // Allow self registration.
    local_mentor_core_set_moodle_config('registerauth', 'email');
    local_mentor_core_set_moodle_config('authloginviaemail', 1);

    // Hide the lang menu.
    local_mentor_core_set_moodle_config('langmenu', 0);

    // Disable the mobile app.
    local_mentor_core_set_moodle_config('enablemobilewebservice', 0);

    // Add "Formateur" as Teacher in Message My Teacher plugin.
    local_mentor_core_set_moodle_config('roles', $formateur->id . ',' . $tuteur->id, 'block_messageteacher');
    local_mentor_core_set_moodle_config('groups', 1, 'block_messageteacher');
    local_mentor_core_set_moodle_config('showuserpictures', 1, 'block_messageteacher');

    // Add "Formateur" as Teacher in Course Contact configuration.
    local_mentor_core_set_moodle_config('coursecontact', $formateur->id);

    // Init default settings for mod_scorm.
    local_mentor_specialization_init_scorm_settings();

    // Init default collections settings.
    local_mentor_specialization_init_collections_settings();

    // Define allowed email addresses.
    $allowedadresses = get_config('allowemailaddresses');
    if (empty($allowedadresses)) {
        local_mentor_specialization_init_allowed_email_addresses();
    }

    // Do not check allowed email when updating users.
    local_mentor_core_set_moodle_config('verifychangedemail', 0);

    // Mod_bigbluebuttonbn default settings.
    local_mentor_specialization_init_bigbluebutton_settings();

    // Set the support name used by emails.
    local_mentor_core_set_moodle_config('supportname', 'L\'équipe du programme Mentor');

    // Init privacy settings.
    local_mentor_specialization_init_privacy_settings();

    // Set tiles hover color.
    local_mentor_core_set_moodle_config('hovercolour', 'rgba(22, 112, 204, 1)', 'format_tiles');

    // Set courses showgrades to Off.
    local_mentor_core_set_moodle_config('showgrades', '0', 'moodlecourse');

    // Set block_completion_progress default settings.
    local_mentor_specialization_init_block_completion_progress_settings();

    // Init logo.
    local_mentor_specialization_init_logo();

    // Activate the filter fontawesome.
    filter_set_global_state('fontawesome', TEXTFILTER_ON);

    // Set default completion to none.
    local_mentor_core_set_moodle_config('completiondefault', 0);

    // Init static pages.
    // Local_mentor_specialization_init_static_pages();.
    local_mentor_core_set_moodle_config('cleanhtml', 2, 'local_staticpage');

    // Define max session timeout.
    local_mentor_core_set_moodle_config('sessiontimeout', 7200);

    // Set cookie http only.
    local_mentor_core_set_moodle_config('cookiehttponly', 1);

    // Enable mentor logstore.
    local_mentor_core_set_moodle_config('enabled_stores', 'logstore_standard,logstore_mentor,logstore_mentor2', 'tool_log');

    // Create reflocalnonediteur role.
    if (!$noteditingreflocal = $DB->get_record('role', ['shortname' => 'reflocalnonediteur'])) {
        mtrace('Create role: Référent local non éditeur');
        $noteditingreflocal = local_mentor_duplicate_role('referentlocal', 'reflocalnonediteur', 'Référent local non éditeur',
            'manager');
    }

    // Remove role capabilities.
    $noteditingreflocalcapabilities = [
        // Mentor capabilities.
        'local/mentor_core:importusers',
        'local/trainings:manage',
        'local/trainings:create',
        'local/trainings:update',
        'local/trainings:delete',
        'local/session:manage',
        'local/session:create',
        'local/session:update',
        'local/session:delete',
        'local/session:changefullname',
        'local/session:changeshortname',
        'local/mentor_core:changeshortname',
        'local/mentor_core:changefullname',
        'local/mentor_core:changethumbnail',
        'local/mentor_core:changetrainingstatus',
        'local/mentor_core:changecontent',
        'local/mentor_core:changetraininggoal',
        'local/mentor_core:changesessionfullname',
        'local/mentor_core:changesessionshortname',
        'local/mentor_core:sharetrainings',
        'local/mentor_core:importusers',
        'local/mentor_specialization:changecollection',
        'local/mentor_specialization:changeidsirh',
        'local/mentor_specialization:changeskills',
        'local/mentor_specialization:changeteaser',
        'local/mentor_specialization:changeproducingorganization',
        'local/mentor_specialization:changetypicaljob',
        'local/mentor_specialization:changecontactproducerorganization',
        'local/mentor_specialization:changeproducerorganizationshortname',
        'local/mentor_specialization:changedesigners',
        'local/mentor_specialization:changecertifying',
        'local/mentor_specialization:changelicenseterms',
        'local/mentor_specialization:changeprerequisite',
        'local/mentor_specialization:changepresenceestimatedtimehours',
        'local/mentor_specialization:changeremoteestimatedtimehours',
        'local/mentor_specialization:changetrainingmodalities',
        'local/mentor_specialization:changeproducerorganizationlogo',
        'local/mentor_specialization:changeteaserpicture',
        'local/mentor_specialization:changecatchphrase',
        // Student capabilities.
        'moodle/course:configurecustomfields',
        'moodle/course:recommendactivity',
        'moodle/competency:evidencedelete',
        'moodle/competency:plancomment',
        'moodle/competency:plancommentown',
        'moodle/competency:planrequestreview',
        'moodle/competency:planrequestreviewown',
        'moodle/competency:planreview',
        'moodle/competency:userevidencemanageown',
        'enrol/self:unenrolself',
        'moodle/comment:post',
        'moodle/comment:view',
        'moodle/competency:coursecompetencygradable',
        'moodle/question:flag',
        'moodle/rating:rate',
        'mod/bigbluebuttonbn:join',
        'mod/chat:chat',
        'mod/choice:choose',
        'mod/choicegroup:choose',
        'mod/coursebadges:choose',
        'mod/coursebadges:deletechoice',
        'mod/data:comment',
        'mod/data:writeentry',
        'mod/feedback:complete',
        'mod/forum:addquestion',
        'mod/forum:allowforcesubscribe',
        'mod/forum:canoverridecutoff',
        'mod/forum:canoverridediscussionlock',
        'mod/forum:canposttomygroups',
        'mod/forum:createattachment',
        'mod/forum:deleteanypost',
        'mod/forum:deleteownpost',
        'mod/forum:exportownpost',
        'mod/forum:replynews',
        'mod/forum:replypost',
        'mod/forum:startdiscussion',
        'mod/glossary:comment',
        'mod/glossary:managecategories',
        'mod/glossary:managecomments',
        'mod/glossary:manageentries',
        'mod/glossary:rate',
        'mod/glossary:write',
        'mod/h5pactivity:submit',
        'mod/lti:view',
        'mod/questionnaire:submit',
        'mod/quiz:attempt',
        'mod/scorm:savetrack',
        'mod/scorm:skipview',
        'mod/survey:participate',
        'mod/wiki:createpage',
        'mod/wiki:editcomment',
        'mod/wiki:editpage',
        'mod/wiki:viewcomment',
        'mod/workshop:exportsubmissions',
        'mod/workshop:peerassess',
        'mod/workshop:submit',
        'mod/workshop:view',
        // Teacher capabilities.
        'moodle/course:manageactivities',
        'moodle/cohort:assign',
        'enrol/manual:enrol',
        'enrol/manual:manage',
        'enrol/self:config',
        'moodle/course:enrolconfig',
        'moodle/course:sectionvisibility',
        'moodle/course:setcurrentsection',
        'moodle/restore:restoresection',
        'moodle/course:movesections',
        'moodle/site:approvecourse',
        'moodle/competency:coursecompetencymanage',
        'moodle/course:changecategory',
        'moodle/course:changefullname',
        'moodle/course:changeidnumber',
        'moodle/course:changelockedcustomfields',
        'moodle/course:changeshortname',
        'moodle/course:changesummary',
        'moodle/course:delete',
        'moodle/course:enrolreview',
        'moodle/course:managegroups',
        'moodle/course:renameroles',
        'moodle/course:tag',
        'moodle/course:update',
        'moodle/course:activityvisibility',
        'block/course_summary:addinstance',
        'report/courseoverview:view',
        'moodle/course:create',
        'moodle/course:managefiles',
        'moodle/course:managescales',
        'moodle/course:markcomplete',
        'moodle/course:overridecompletion',
        'moodle/course:reset',
        'moodle/course:reviewotherusers',
        'moodle/course:setforcedlanguage',
        'moodle/course:visibility',
        'moodle/restore:restorecourse',
        'moodle/competency:coursecompetencyconfigure',
        'moodle/block:edit',
        'moodle/site:manageblocks',
        'moodle/competency:competencygrade',
        'moodle/grade:edit',
        'moodle/grade:export',
        'moodle/grade:import',
        'moodle/grade:manage',
        'moodle/grade:hide',
        'moodle/badges:manageglobalsettings',
        'moodle/badges:awardbadge',
        'moodle/badges:configurecriteria',
        'moodle/badges:configuredetails',
        'moodle/badges:configuremessages',
        'moodle/badges:createbadge',
        'moodle/badges:deletebadge',
        'moodle/badges:revokebadge',
        'moodle/badges:viewawarded',
        'moodle/competency:planmanage',
        'moodle/competency:planmanagedraft',
        'moodle/competency:planview',
        'moodle/competency:planviewdraft',
        'moodle/competency:usercompetencyreview',
        'moodle/competency:usercompetencyview',
        'moodle/competency:userevidencemanage',
        'moodle/competency:userevidenceview',
        'moodle/competency:competencymanage',
        'moodle/competency:templatemanage',
        'moodle/competency:templateview',
        'moodle/competency:competencygrade',
        'gradereport/grader:view',
        'gradereport/history:view',
        'gradereport/outcomes:view',
        'gradereport/singleview:view',
        'gradereport/user:view',
        'moodle/site:viewreports',
        'report/completion:view',
        'moodle/grade:import',
        'moodle/restore:restoretargetimport',
        'repository/contentbank:accesscoursecontent',
        'moodle/question:add',
        'moodle/question:editall',
        'moodle/question:editmine',
        'moodle/question:managecategory',
        'moodle/question:moveall',
        'moodle/question:config',
        'moodle/site:uploadusers',
        'moodle/backup:backuptargetimport',
        'moodle/grade:import',
        'moodle/restore:restoretargetimport',
    ];
    local_mentor_core_remove_capabilities($noteditingreflocal, $noteditingreflocalcapabilities);

    local_mentor_core_add_capability($noteditingreflocal, 'local/session:createinsubentity');
    local_mentor_core_add_capability($noteditingreflocal, 'local/trainings:createinsubentity');
    local_mentor_core_add_capability($noteditingreflocal, 'local/mentor_core:sharetrainingssubentities');

    // Manage role authorization.
    mtrace('Manage roles assign');
    local_mentor_specialization_manage_role_authorization();

    // Set right context levels to the following roles.
    $rolescontexts = [
        'admindedie'            => [CONTEXT_COURSECAT],
        'editingteacher'        => [],
        'teacher'               => [],
        'formateur'             => [CONTEXT_COURSE, CONTEXT_MODULE],
        'tuteur'                => [CONTEXT_COURSE, CONTEXT_MODULE],
        'concepteur'            => [CONTEXT_COURSE, CONTEXT_MODULE],
        'participant'           => [CONTEXT_COURSE, CONTEXT_MODULE],
        'participantnonediteur' => [CONTEXT_COURSE, CONTEXT_MODULE],
        'referentlocal'         => [CONTEXT_COURSECAT],
        'reflocalnonediteur'    => [CONTEXT_COURSECAT],
        'respformation'         => [CONTEXT_COURSECAT]
    ];

    foreach ($rolescontexts as $rolename => $contexts) {
        // Get role.
        $role = $DB->get_record('role', ['shortname' => $rolename], 'id');

        if (false === $role) {
            throw new dml_exception($rolename . ' not found.');
        }

        // Remove actual role context levels.
        $DB->delete_records('role_context_levels', ['roleid' => $role->id]);

        // Prepare data objects for insert.
        $rolestoinsert = [];
        foreach ($contexts as $context) {
            $rolecontextelevel               = new stdClass();
            $rolecontextelevel->roleid       = $role->id;
            $rolecontextelevel->contextlevel = $context;
            $rolestoinsert[]                 = $rolecontextelevel;
        }

        // Insert new role context levels.
        $DB->insert_records('role_context_levels', $rolestoinsert);
    }

    // Defined enrolment plugins.
    local_mentor_core_set_enrol_plugins_enabled();

    local_mentor_core_set_moodle_config('profileroles',
        $participant->id . ',' . $noteditingstudent->id . ',' . $concepteur->id . ','
        . $formateur->id . ',' . $tuteur->id);

    // Move capabilities.
    local_mentor_core_add_capability($admindedie, 'local/mentor_core:movetrainings');
    local_mentor_core_add_capability($admindedie, 'local/mentor_core:movesessions');
    local_mentor_core_add_capability($admindedie, 'local/mentor_core:movetrainingsinotherentities');
    local_mentor_core_add_capability($admindedie, 'local/mentor_core:movesessionsinotherentities');
    local_mentor_core_add_capability($respformation, 'local/mentor_core:movetrainings');
    local_mentor_core_add_capability($respformation, 'local/mentor_core:movesessions');
    local_mentor_core_add_capability($respformation, 'local/mentor_core:movetrainingsinotherentities');
    local_mentor_core_add_capability($respformation, 'local/mentor_core:movesessionsinotherentities');
    local_mentor_core_add_capability($reflocal, 'local/mentor_core:movetrainings');
    local_mentor_core_add_capability($reflocal, 'local/mentor_core:movesessions');
    local_mentor_core_remove_capability($reflocal, 'local/mentor_core:movetrainingsinotherentities');
    local_mentor_core_remove_capability($reflocal, 'local/mentor_core:movesessionsinotherentities');
    local_mentor_core_add_capability($noteditingreflocal, 'local/mentor_core:movetrainings');
    local_mentor_core_add_capability($noteditingreflocal, 'local/mentor_core:movesessions');
    local_mentor_core_remove_capability($noteditingreflocal, 'local/mentor_core:movetrainingsinotherentities');
    local_mentor_core_remove_capability($noteditingreflocal, 'local/mentor_core:movesessionsinotherentities');

    // Remove guest enrolment method from all roles.
    local_mentor_core_remove_capability($admindedie, 'enrol/guest:config');
    local_mentor_core_remove_capability($reflocal, 'enrol/guest:config');
    local_mentor_core_remove_capability($respformation, 'enrol/guest:config');
    local_mentor_core_remove_capability($reflocal, 'enrol/guest:config');
    local_mentor_core_remove_capability($concepteur, 'enrol/guest:config');
    local_mentor_core_remove_capability($formateur, 'enrol/guest:config');
    local_mentor_core_remove_capability($tuteur, 'enrol/guest:config');
    local_mentor_core_remove_capability($noteditingreflocal, 'enrol/guest:config');

    // Remove Formateur capabilities.
    local_mentor_core_remove_capability($formateur, 'local/mentor_core:changecontent');
    local_mentor_core_remove_capability($formateur, 'local/mentor_core:changefullname');
    local_mentor_core_remove_capability($formateur, 'local/mentor_core:changeshortname');
    local_mentor_core_remove_capability($formateur, 'local/mentor_core:changetraininggoal');
    local_mentor_core_remove_capability($formateur, 'local/mentor_specialization:changecollection');
    local_mentor_core_remove_capability($formateur, 'local/mentor_specialization:changeidsirh');
    local_mentor_core_remove_capability($formateur, 'local/mentor_specialization:changeskills');

    // Remove questionnaire capabilities.
    local_mentor_core_remove_capability($admindedie, 'mod/questionnaire:submissionnotification');
    local_mentor_core_remove_capability($reflocal, 'mod/questionnaire:submissionnotification');
    local_mentor_core_remove_capability($respformation, 'mod/questionnaire:submissionnotification');
    local_mentor_core_remove_capability($noteditingreflocal, 'mod/questionnaire:submissionnotification');

    // Set category bin expiry to 30 days.
    local_mentor_core_set_moodle_config('categorybinexpiry', 2592000, 'tool_recyclebin');

    // Set theme_mentor default settings.
    local_mentor_specialization_init_theme_mentor_settings();

    // Tuteur capabilities.
    local_mentor_core_add_capability($tuteur, 'enrol/manual:manage');
    local_mentor_core_add_capability($tuteur, 'enrol/manual:unenrol');
    local_mentor_core_add_capability($tuteur, 'moodle/course:enrolreview');
    local_mentor_core_add_capability($tuteur, 'mod/customcert:receiveissue');
    local_mentor_core_add_capability($tuteur, 'mod/feedback:viewanalysepage');
    local_mentor_core_remove_capability($tuteur, 'moodle/role:review');
    local_mentor_core_remove_capability($tuteur, 'tool/recyclebin:viewitems');

    // Atto video.
    $attotoolbar = get_config('editor_atto', 'toolbar');
    $attotoolbar = str_replace('media, recordrtc, managefiles, h5p, snippet', 'video, recordrtc, managefiles, h5p, snippet',
        $attotoolbar);
    if (strpos($attotoolbar, 'fontawesomepicker') === false) {
        $attotoolbar = str_replace('emojipicker', 'fontawesomepicker, emojipicker',
            $attotoolbar);
    }
    $attotoolbar = str_replace('fontawesomepicker, fontawesomepicker', 'fontawesomepicker', $attotoolbar);

    local_mentor_core_set_moodle_config('toolbar', $attotoolbar, 'editor_atto');

    // MENTOR_RQM-633.
    local_mentor_core_add_capability($reflocal, 'local/mentor_specialization:changesessionopento');

    // Duplicate session.
    local_mentor_core_add_capability($admindedie, 'local/mentor_core:duplicatesessionintotraining');
    local_mentor_core_add_capability($reflocal, 'local/mentor_core:duplicatesessionintotraining');
    local_mentor_core_add_capability($respformation, 'local/mentor_core:duplicatesessionintotraining');
    local_mentor_core_remove_capability($tuteur, 'local/mentor_core:duplicatesessionintotraining');
    local_mentor_core_remove_capability($formateur, 'local/mentor_core:duplicatesessionintotraining');
    local_mentor_core_remove_capability($concepteur, 'local/mentor_core:duplicatesessionintotraining');

    // MENTOR_RQM-662.
    local_mentor_core_remove_capability($admindedie, 'mod/questionnaire:createpublic');
    local_mentor_core_remove_capability($reflocal, 'mod/questionnaire:createpublic');
    local_mentor_core_remove_capability($respformation, 'mod/questionnaire:createpublic');
    local_mentor_core_remove_capability($tuteur, 'mod/questionnaire:createpublic');
    local_mentor_core_remove_capability($formateur, 'mod/questionnaire:createpublic');
    local_mentor_core_remove_capability($concepteur, 'mod/questionnaire:createpublic');
    local_mentor_core_remove_capability($noteditingreflocal, 'mod/questionnaire:createpublic');

    // MENTOR_RQM-715.
    $sitecourse            = $DB->get_record('course', ['format' => 'site']);
    $sitecourse->fullname  = 'Mentor';
    $sitecourse->shortname = 'Mentor';
    $DB->update_record('course', $sitecourse);

    local_mentor_core_set_moodle_config('forcelogin', 2, 'local_staticpage');
    local_mentor_core_set_moodle_config('hiddenuserfields', 'lastaccess');
    local_mentor_core_set_moodle_config('defaultpreference_maildisplay', 0);

    // Invisible hidden section.
    local_mentor_core_set_moodle_config('hiddensections', 1, 'moodlecourse');

    // Disable device detection config.
    local_mentor_core_set_moodle_config('enabledevicedetection', 0);

    // Set activitynames filter to disabled but.
    filter_set_global_state('activitynames', TEXTFILTER_OFF);
    filter_set_applies_to_strings('activitynames', false);

    // MENTOR_RQM-1015.
    local_mentor_core_add_capability($admindedie, 'local/mentor_core:changesessionopentoexternal');
    local_mentor_core_add_capability($respformation, 'local/mentor_core:changesessionopentoexternal');
    local_mentor_core_remove_capability($reflocal, 'local/mentor_core:changesessionopentoexternal');

    // Site security config.
    local_mentor_core_set_moodle_config('lockoutthreshold', 5);
    local_mentor_core_set_moodle_config('lockoutwindow', 120);
    local_mentor_core_set_moodle_config('lockoutduration', 900);

    // Suspend users by csv.
    local_mentor_core_add_capability($admindedie, 'local/mentor_core:suspendusers');
    local_mentor_core_remove_capability($reflocal, 'local/mentor_core:suspendusers');
    local_mentor_core_remove_capability($respformation, 'local/mentor_core:suspendusers');
    local_mentor_core_remove_capability($formateur, 'local/mentor_core:suspendusers');

    // User menu items.
    local_mentor_core_set_moodle_config('customusermenuitems',
        'messages,message|/message/index.php|t/message
preferences,moodle|/user/preferences.php|t/preferences');

    // Remove capabilities to all role.
    local_mentor_core_remove_capability_for_all('enrol/cohort:config');
    local_mentor_core_remove_capability_for_all('enrol/cohort:unenrol');
}

/**
 * Init static pages
 */
function local_mentor_specialization_init_static_pages() {
    global $CFG;

    local_mentor_core_set_moodle_config('forcelogin', 2, 'local_staticpages');

    $fs = get_file_storage();

    $filedir = $CFG->dirroot . '/local/mentor_specialization/data/staticpages';

    $files = array_diff(scandir($filedir), ['..', '.']);

    $filerecord = [
        'contextid' => 1,
        'component' => 'local_staticpage',
        'filearea'  => 'documents',
        'itemid'    => 0,
        'filepath'  => '/'
    ];

    // Read all static page files.
    foreach ($files as $file) {

        $filepath = $filedir . '/' . $file;

        $filerecord['filename'] = $file;

        // Create new documents.
        $fs->create_file_from_pathname($filerecord, $filepath);
    }
}

/**
 * Import all user tours
 */
function local_mentor_specialization_import_usertours() {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/admin/tool/usertours/classes/manager.php');

    $jsondir = $CFG->dirroot . '/local/mentor_specialization/data/usertours';

    $files = array_diff(scandir($jsondir), ['..', '.']);

    // Delete existing tours.
    $DB->delete_records('tool_usertours_steps');
    $DB->delete_records('tool_usertours_tours');

    // Read all user tour files.
    foreach ($files as $file) {
        $filepath = $jsondir . '/' . $file;
        $json     = file_get_contents($filepath);

        // Create the user tour in database.
        if ($tour = \tool_usertours\manager::import_tour_from_json($json)) {
            mtrace('User tour importé : ' . $file);
        }
    }

}

/**
 * Initialise mentor logo
 */
function local_mentor_specialization_init_logo() {
    global $CFG;

    $filename = 'logo_mentor.png';

    $pathname = $CFG->dirroot . '/local/mentor_specialization/data/pix/' . $filename;

    $fs = get_file_storage();

    // Remove old logo.
    $fs->delete_area_files(1, 'core_admin', 'logo');
    $fs->delete_area_files(1, 'core_admin', 'logocompact');

    // Store file.
    $file            = new stdClass();
    $file->contextid = 1;
    $file->component = 'core_admin';
    $file->filearea  = 'logo';
    $file->itemid    = 0;
    $file->filepath  = '/';
    $file->filename  = $filename;

    $fs->create_file_from_pathname($file, $pathname);

    $file->filearea = 'logocompact';

    $fs->create_file_from_pathname($file, $pathname);

    // Set moodle config.
    local_mentor_core_set_moodle_config('logo', '/' . $filename, 'core_admin');
    local_mentor_core_set_moodle_config('logocompact', '/' . $filename, 'core_admin');
}

/**
 * Set block_completion_progress default settings
 */
function local_mentor_specialization_init_block_completion_progress_settings() {
    local_mentor_core_set_moodle_config('completed_colour', '#169b62', 'block_completion_progress');
    local_mentor_core_set_moodle_config('submittednotcomplete_colour', '#fdcf41', 'block_completion_progress');
    local_mentor_core_set_moodle_config('futureNotCompleted_colour', '#ff6f4c', 'block_completion_progress');
    local_mentor_core_set_moodle_config('notCompleted_colour', '#000091', 'block_completion_progress');
}

/**
 * Init privacy settings
 */
function local_mentor_specialization_init_privacy_settings() {
    local_mentor_core_set_moodle_config('exportlog', 1, 'tool_log');
    local_mentor_core_set_moodle_config('contactdataprotectionofficer', 1, 'tool_dataprivacy');
    local_mentor_core_set_moodle_config('automaticdataexportapproval', 1, 'tool_dataprivacy');
    local_mentor_core_set_moodle_config('automaticdatadeletionapproval', 0, 'tool_dataprivacy');
    local_mentor_core_set_moodle_config('automaticdeletionrequests', 1, 'tool_dataprivacy');
    local_mentor_core_set_moodle_config('requireallenddatesforuserdeletion', 1, 'tool_dataprivacy');
    local_mentor_core_set_moodle_config('showdataretentionsummary', 0, 'tool_dataprivacy');

    // TODO import dpo role.
    // TODO : setting dporoles.
}

/**
 * Initialise default bbb settings
 */
function local_mentor_specialization_init_bigbluebutton_settings() {
    global $CFG, $DB;

    // Enable a default presentation.
    local_mentor_core_set_moodle_config('bigbluebuttonbn_preuploadpresentation_enabled', 1);

    // Set the default presentation file.
    $fs = get_file_storage();

    $file            = new stdClass();
    $file->contextid = context_system::instance()->id;
    $file->component = 'mod_bigbluebuttonbn';
    $file->filearea  = 'presentationdefault';
    $file->itemid    = '0';
    $file->filepath  = '/';
    $file->filename  = 'background.pptx';

    if (!$fs->file_exists($file->contextid, $file->component, $file->filearea, $file->itemid, $file->filepath, $file->filename)) {

        $fs->create_file_from_pathname($file,
            $CFG->dirroot . '/local/mentor_specialization/data/mod_bigbluebuttonbn/background.pptx');

        local_mentor_core_set_moodle_config('presentationdefault', '/background.pptx', 'mod_bigbluebuttonbn');
    }

    // Set the default moderator.
    $formateur  = $DB->get_record('role', ['shortname' => 'formateur']);
    $concepteur = $DB->get_record('role', ['shortname' => 'concepteur']);
    $tuteur     = $DB->get_record('role', ['shortname' => 'tuteur']);
    $roles      = $formateur->id . ',' . $concepteur->id . ',' . $tuteur->id;

    local_mentor_core_set_moodle_config('bigbluebuttonbn_participant_moderator_default', $roles, 'mod_bigbluebuttonbn');
    local_mentor_core_set_moodle_config('bigbluebuttonbn_participant_moderator_default', $roles);
}

/**
 * Define allowed email addresses
 */
function local_mentor_specialization_init_allowed_email_addresses() {
    set_config('allowemailaddresses',
        'agriculture.gouv.fr onf.fr inao.gouv.fr anses.fr agrocampus-ouest.fr educagri.fr' .
        ' agroparistech.fr engees.unistra.fr ensfea.fr ecole-paysage.fr oniris-nantes.fr' .
        ' vetagro-sup.fr agrosupdijon.fr agencebio.org asp-public.fr cnpf.fr cerema.fr enpc.fr' .
        ' franceagrimer.fr ifce.fr inra.fr irstea.fr odeadom.fr oncfs.gouv.fr eridan.social.gouv.fr' .
        ' ars.sante.fr cab.formation.gouv.fr cab.social-sante.gouv.fr travail.gouv.fr .travail.gouv.fr' .
        ' cbcm.social.gouv.fr creps-dijon.sports.gouv.fr creps-pap.sports.gouv.fr dcstep.gouv.fr' .
        ' dieccte.gouv.fr direccte.gouv.fr drjscs.gouv.fr emploi.gouv.fr externes.cbcm.social.gouv.fr' .
        ' externes.sg.social.gouv.fr externes.social.gouv.fr famille.gouv.fr formation.gouv.fr igas.gouv.fr' .
        ' injep.fr jeunesse-sports.gouv.fr miprof.gouv.fr orion.gouv.to.res precarite.gouv.fr sante.gouv.fr' .
        ' sante.gouv.fr.to.res sante-jeunesse-sports.gouv.fr sante-travail.gouv.fr service-civique.gouv.fr' .
        ' sg.social.gouv.fr social.gouv.fr solidarite.gouv.fr ville.gouv.fr externes.sante.gouv.fr' .
        ' sports.gouv.fr cnefop.gouv.fr externes.emploi.gouv.fr ddc.social.gouv.fr externes.ddc.social.gouv.fr' .
        ' cab.social.gouv.fr ci.handicap.gouv.fr diffusion.jeunesse-sports.gouv.fr feddf.gouv.fr femmes.gouv.fr' .
        ' fvjs.gouv.fr social-sante.gouv.fr ville-jeunesse-sports.gouv.fr externes.ville-jeunesse-sports.gouv.fr' .
        ' externes.social-sante.gouv.fr externes.fvjs.gouv.fr externes.femmes.gouv.fr externes.feddf.gouv.fr' .
        ' externes.diffusion.ville-jeunesse-sports.gouv.fr externes.diffusion.jeunesse-sports.gouv.fr ' .
        'externes.ci.handicap.gouv.fr externes.cab.social.gouv.fr externes.sante-travail.gouv.fr ' .
        'externes.ville.gouv.fr externes.sports.gouv.fr diges.gouv.fr concours.social.gouv.fr associations.gouv.fr ' .
        'sante.fr externes.diges.gouv.fr externes.concours.social.gouv.fr externes.associations.gouv.fr ' .
        'externes.sante.fr cohesionsociale.gouv.fr externes.cohesionsociale.gouv.fr filieresport.sports.gouv.fr' .
        ' externes.filieresport.sports.gouv.fr externes.lafrancesengage.fr lafrancesengage.fr' .
        ' engagement-civique.gouv.fr externes.ars.sante.fr externes.igas.gouv.fr cnpe.social.gouv.fr ' .
        'externes.cnpe.social.gouv.fr retraites.gouv.fr externes.retraites.gouv.fr diffusion.sports.gouv.fr' .
        ' externes.diffusion.sports.gouv.fr guichet-unique.sante.fr travail-emploi.gouv.fr association.gouv.fr' .
        ' cnaop.gouv.fr cncp.gouv.fr cnle.gouv.fr cyberveille-sante.gouv.fr designation-prudhommes.gouv.fr' .
        ' egalite-citoyennete-participez.gouv.fr hosp-eelections2018.fr ivg.gouv.fr jeunes.gouv.fr' .
        ' mesdroitssociaux.gouv.fr moncompteformation.gouv.fr onpes.gouv.fr personnes-agees.gouv.fr' .
        ' reforme-retraite.gouv.fr solidarites-sante.gouv.fr stop-violences-femmes.gouv.fr travailler-mieux.gouv.fr' .
        ' agencedusport.fr cnds.sports.gouv.fr externes.cnds.sports.gouv.fr externes.agencedusport.fr arsoc.fax ' .
        'externes.service-civique.gouv.fr externes.engagement-civique.gouv.fr geodae.sante.gouv.fr finances.gouv.fr' .
        ' dgfip.finances.gouv.fr');
}

/**
 * Init default collections settings for local_mentor_specialization
 */
function local_mentor_specialization_init_collections_settings() {
    $collections = <<<EOT
achat|Achat public|rgba(166, 57, 80, 0.2)
communication|Communication et service aux usagers|rgba(127, 127, 200, 0.4)
finances|Finances publiques, gestion budgétaire et financière|rgba(22, 155, 98, 0.3)
formations|Formations spécifiques aux missions des ministères|rgba(0, 172, 140, 0.3)
langues|Langues|rgba(225, 6, 0, 0.2)
management|Management|rgba(11, 107, 168, 0.3)
numerique|Numérique et système d'information et de communication|rgba(106, 106, 106, 0.28)
politique|Valeurs de la république|rgba(87, 112, 190, 0.4)
preparation|Préparation aux épreuves de concours et d'examens professionnels|rgba(255, 153, 64, 0.4)
ressources|Ressources humaines|rgba(7, 98, 200, 0.3)
techniques|Techniques et affaires juridiques|rgba(253, 207, 65, 0.4)
transformation|Transformation de l'action publique|rgba(255, 141, 126, 0.4)
EOT;

    if (empty(get_config('local_mentor_specialization', 'collections'))) {
        local_mentor_core_set_moodle_config('collections', $collections, 'local_mentor_specialization');
    }

}

/**
 * Init default settings for mod_scorm
 */
function local_mentor_specialization_init_scorm_settings() {
    local_mentor_core_set_moodle_config('popup', 1, 'scorm');
    local_mentor_core_set_moodle_config('framewidth', '100%', 'scorm');
    local_mentor_core_set_moodle_config('frameheight', '100%', 'scorm');
    local_mentor_core_set_moodle_config('displaycoursestructure', 0, 'scorm');
    local_mentor_core_set_moodle_config('skipview', 2, 'scorm');
    local_mentor_core_set_moodle_config('hidebrowse', 1, 'scorm');
    local_mentor_core_set_moodle_config('hidetoc', 3, 'scorm');
    local_mentor_core_set_moodle_config('displayattemptstatus', 0, 'scorm');
}

/**
 * Disable a module
 *
 * @param string $modulename
 * @throws moodle_exception
 */
function local_mentor_specialization_disable_module($modulename) {
    global $DB;

    if (!$module = $DB->get_record("modules", array("name" => $modulename))) {
        print_error('moduledoesnotexist', 'error');
    }
    $DB->set_field("modules", "visible", "0", array("id" => $module->id)); // Hide main module
    // Remember the visibility status in visibleold
    // and hide...
    $sql = "UPDATE {course_modules}
                   SET visibleold=visible, visible=0
                 WHERE module=?";

    try {
        $DB->execute($sql, array($module->id));
    } catch (\dml_exception $e) {
        mtrace('ERROR : Disable a module ' . $modulename);
        return;
    }

    // Increment course.cacherev for courses where we just made something invisible.
    // This will force cache rebuilding on the next request.
    increment_revision_number('course', 'cacherev',
        "id IN (SELECT DISTINCT course
                                FROM {course_modules}
                               WHERE visibleold=1 AND module=?)",
        array($module->id));
    core_plugin_manager::reset_caches();
}

/**
 * Extend the nav drawer entries
 *
 * @param global_navigation $navigation
 * @throws coding_exception
 */
function local_mentor_specialization_extend_navigation(global_navigation $navigation) {
    global $PAGE;

    $courseid = $PAGE->course->id;

    $hassummaryblock = false;

    // Remove elements on course context.
    if ($PAGE->context->contextlevel == CONTEXT_COURSE ||
        $PAGE->context->contextlevel == CONTEXT_MODULE) {

        // Load all blocks if it's not already done.
        $PAGE->blocks->load_blocks();

        $regions = $PAGE->blocks->get_regions();

        // Check if the block_summary exists in the side-pre region.
        if (in_array('side-pre', $regions)) {
            $preblocks = $PAGE->blocks->get_blocks_for_region('side-pre');
            foreach ($preblocks as $preblock) {
                if (get_class($preblock) === 'block_summary') {
                    $hassummaryblock = true;
                    break;
                }
            }
        }

    }

    $mycourses = $navigation->get('mycourses');

    // Hide the "My courses" entry.
    $mycourses->showinflatnavigation = false;

    // Get all chidren from My courses entry.
    $mycoursechildren = $mycourses->get_children_key_list();

    $isenrolled = array_key_exists($courseid, $mycoursechildren);

    foreach ($mycoursechildren as $mycoursechildkey) {

        $child = $mycourses->get($mycoursechildkey);

        // Remove other courses links.
        if ($mycoursechildkey != $courseid) {
            $child->showinflatnavigation = false;
        } else {
            $coursechildren = $child->get_children_key_list();

            // Hide the current course name.
            $child->showinflatnavigation = false;

            // Remove activites links if the summary block is active on the page.
            if ($hassummaryblock) {

                foreach ($coursechildren as $coursechildkey) {

                    $mycoursechild = $child->get($coursechildkey);

                    // Hide sections and activities entries.
                    if (is_numeric($coursechildkey)) {
                        $mycoursechild->add_class('hidden');
                    }
                }
            }
        }
    }

    // Remove sections entries when the user is not enrolled into the course.
    if (!$isenrolled && $hassummaryblock) {
        $mycourses = $navigation->get('courses');

        $keys = $mycourses->get_children_key_list();

        foreach ($keys as $key) {
            $elem            = $mycourses->get($key);
            $subchildrenkeys = $elem->get_children_key_list();

            foreach ($subchildrenkeys as $subchildrenkey) {

                // Hide sections and activities entries.
                if (is_numeric($subchildrenkey)) {
                    $mycoursechild = $elem->get($subchildrenkey);
                    $mycoursechild->add_class('hidden');
                }
            }

        }
    }

}

/**
 * Set theme_mentor default settings
 */
function local_mentor_specialization_init_theme_mentor_settings() {
    global $CFG;

    if (empty(get_config('theme_mentor', 'textinfofooter'))) {
        local_mentor_core_set_moodle_config('textinfofooter',
            'Le site n\'a pas fait l\'objet d\'un audit accessibilité. Celui-ci est prévu courant novembre', 'theme_mentor');
    }

    if (empty(get_config('theme_mentor', 'about'))) {
        local_mentor_core_set_moodle_config('about', $CFG->wwwroot . '/local/staticpage/view.php?page=apropos', 'theme_mentor');
    }

    if (empty(get_config('theme_mentor', 'legalnotice'))) {
        local_mentor_core_set_moodle_config('legalnotice', $CFG->wwwroot . '/local/staticpage/view.php?page=mentionslegales',
            'theme_mentor');
    }

    if (empty(get_config('theme_mentor', 'faq'))) {
        local_mentor_core_set_moodle_config('faq', $CFG->wwwroot . '/local/staticpage/view.php?page=faq', 'theme_mentor');
    }

    if (empty(get_config('theme_mentor', 'sitemap'))) {
        local_mentor_core_set_moodle_config('sitemap', $CFG->wwwroot . '/local/staticpage/view.php?page=plandusite',
            'theme_mentor');
    }

    if (empty(get_config('theme_mentor', 'externallinks'))) {
        local_mentor_core_set_moodle_config('externallinks', 'legifrance.gouv.fr|gouvernement.fr|service-public.fr|data.gouv.fr',
            'theme_mentor');
    }

    if (empty(get_config('theme_mentor', 'mentorlicence'))) {
        local_mentor_core_set_moodle_config('mentorlicence',
            'Sauf mention contraire, tous les contenus de ce site sont sous ' .
            '<a href="https://www.etalab.gouv.fr/">licence etalab-2.0</a>',
            'theme_mentor');
    }

    if (empty(get_config('theme_mentor', 'personaldata'))) {
        local_mentor_core_set_moodle_config('personaldata',
            $CFG->wwwroot . '/local/staticpage/view.php?page=donneespersonnelles',
            'theme_mentor');
    }
}

/**
 * Set role order.
 *
 * @param String[] $roleorder
 * @return void
 * @throws dml_exception
 */
function local_mentor_specialization_set_role_order($roleorder) {
    global $DB;

    mtrace('Set role order.');

    $maxsortorderexisting = 1000;

    // Get list of current roles.
    $listroles   = $DB->get_records('role', [], '', 'shortname, id, sortorder');
    $defineroles = [];

    foreach ($roleorder as $role) {
        if (isset($listroles[$role])) {
            $defineroles[] = $listroles[$role];
            unset($listroles[$role]);
        }
    }

    $roles = array_values(array_merge($defineroles, $listroles));

    foreach ($roles as $key => $role) {
        $role->sortorder = $key + $maxsortorderexisting;
        $DB->update_record('role', $role);
    }

    // Remove the value of the old maximum order from all the roles.
    try {
        $DB->execute('UPDATE {role}
        SET sortorder = sortorder - :maxorderexisting',
            ['maxorderexisting' => $maxsortorderexisting - 1]
        );
    } catch (\dml_exception $e) {
        mtrace('ERROR : Set role order');
    }
}

/**
 * Set enrol plugins enabled to config.
 *
 * @return void
 */
function local_mentor_core_set_enrol_plugins_enabled() {
    local_mentor_core_set_moodle_config('enrol_plugins_enabled', 'manual,guest,self,meta,sirh,cohort');
}
