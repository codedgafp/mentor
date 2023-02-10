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
 * Nettoie les comptes du LDAP non présents dans la base Moodle
 */

require_once('../../../config.php');

require_login();

if (!is_siteadmin()) {
    print_error('Vous devez vous connecter en tant qu\'administrateur pour lancer ce script');
}

$confirm = optional_param('confirm', 0, PARAM_INT);

$pluginlib = $CFG->dirroot . '/auth/ldap_syncplus/auth.php';

// Check if the ldap_syncplus plugin exists.
if (!is_file($pluginlib)) {
    echo 'Le plugin LDAP syncplus n\'est pas installé';
    return;
}

require_once($pluginlib);

$config = get_config('auth_ldap_syncplus', 'contexts');

$url = new moodle_url('/local/mentor_specialization/pages/clear_ldap.php');

// Check if the ldap_syncplus plugin has been configured.
if (empty($config)) {
    echo 'Le plugin LDAP syncplus n\'est pas configuré';
    return;
}

// Récupére la liste des emails des utilisateurs Moodle censés exister dans le ldap.
$moodleusers = $DB->get_fieldset_select('user', 'username', 'auth = \'ldap_syncplus\'');

// Open an ldap connection.
$auth = new auth_plugin_ldap_syncplus();
$con  = $auth->ldap_connect();

// Paramètres de la recherche ldap.
$basedn     = $config;
$filter     = "(&(objectClass=person)(cn=*))";
$attributes = ['cn'];

// Cherche des résultats dans le ldap.
$result = ldap_search($con, $basedn, $filter, $attributes);

$PAGE->set_context(context_system::instance());
$PAGE->set_title('Nettoyage du LDAP');
$PAGE->set_url($url);
echo $OUTPUT->header();

echo $OUTPUT->heading('Nettoyage du LDAP');

if (false !== $result) {
    // Récupére les entrées correspondantes au résultat.
    $entries = ldap_get_entries($con, $result);

    $count = $entries['count'];

    echo '<div>Le LDAP contient ' . $count . ' utilisateurs</div>';

    $notexisting = [];

    for ($i = 0; $i < $count; $i++) {
        $cn = $entries[$i]['cn'][0];
        $dn = $entries[$i]['dn'];

        if (!in_array($cn, $moodleusers)) {
            if ($confirm) {
                ldap_delete($con, $dn);
                echo '<div>Utilisateur supprimé : ' . $cn . '</div>';
            } else {
                $notexisting[] = $entries[$i];
            }
        }
    }

    echo '<div>Utilisateurs à supprimer du LDAP (' . count($notexisting) . ') :</div>';

    if (count($notexisting) > 0) {
        echo '<ul>';

        foreach ($notexisting as $notexistinguser) {
            echo '<li>' . $notexistinguser['cn'][0] . '</li>';
        }

        echo '</ul>';

        echo '<div><a href="' . $url . '?confirm=1">Nettoyer les utilisateurs</a></div>';
    } else {
        echo '<div>Aucun</div>';
    }

}

// Close the ldap connection.
$auth->ldap_close();

echo $OUTPUT->footer();
