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

require_once('../../../../../config.php');

require_login();

if (!is_siteadmin()) {
    echo 'Permission denied';
}

$dataurl = $CFG->wwwroot . '/admin/tool/log/store/mentor/data/';

echo '<ul>';
echo '<li><a href="' . $dataurl . 'mentor_log.php">logstore_mentor_log</a></li>';
echo '<li><a href="' . $dataurl . 'mentor_history_log.php">logstore_mentor_history_log</a></li>';
echo '<li><a href="' . $dataurl . 'mentor_user.php">logstore_mentor_user</a></li>';
echo '<li><a href="' . $dataurl . 'mentor_session.php">logstore_mentor_session</a></li>';
echo '<li><a href="' . $dataurl . 'mentor_collection.php">logstore_mentor_collection</a></li>';
echo '</ul>';

$startdate = 1624019934; // Le 18/6/2021 à 12:38:54.
$enddate = 1624543219; // Le 25/6/2021 à 12:38:54.
$status = 'inprogress';
$collection = 'achat';
$entity = 100;

/*
print_object('Agents uniques par période (par collection)');

$request = $DB->get_records_sql('
    (SELECT
        cc.name as Espace, COUNT(DISTINCT(mu.userid)) as NbUtilisateur
    FROM
        {course_categories} cc
    JOIN
        {logstore_mentor_session} ms ON ms.space = cc.id
    JOIN
        {logstore_mentor_log} ml ON ml.sessionlogid = ms.id
    JOIN
        {logstore_mentor_collection} mc ON mc.sessionlogid = ms.id
    JOIN
        {logstore_mentor_user} mu ON ml.userlogid = mu.id
    WHERE
        ml.timecreated > :startdate
        AND
        ml.timecreated < :enddate
        AND
        ms.status = :status
        AND
        mc.name = :collection
    GROUP BY cc.name)
    UNION
    (
        SELECT "Total" as Espace, COUNT(DISTINCT(mu2.userid))
        FROM {logstore_mentor_user} mu2
        JOIN {logstore_mentor_log} ml2 ON ml2.userlogid = mu2.id
        JOIN {logstore_mentor_session} ms2 ON ml2.sessionlogid = ms2.id
        JOIN {logstore_mentor_collection} mc2 ON mc2.sessionlogid = ms2.id
        WHERE
            ml2.timecreated > :startdate2
            AND
            ml2.timecreated < :enddate2
            AND
            ms2.status = :status2
            AND
            mc2.name = :collection2
    )
', [
        'startdate'   => $startdate,
        'enddate'     => $enddate,
        'status'      => $status,
        'collection'  => $collection,
        'startdate2'  => $startdate,
        'enddate2'    => $enddate,
        'status2'     => $status,
        'collection2' => $collection
]);

print_object($request);

// ---------------------------------------

print_object('Agents uniques par période (par entité de rattachement)');

$request = $DB->get_records_sql('
    (SELECT
        cc.name as Espace, COUNT(DISTINCT(mu.userid)) as NbUtilisateur
    FROM
        {course_categories} cc
    JOIN
        {logstore_mentor_session} ms ON ms.space = cc.id
    JOIN
        {logstore_mentor_log} ml ON ml.sessionlogid = ms.id
    JOIN
        {logstore_mentor_user} mu ON ml.userlogid = mu.id
    WHERE
        ml.timecreated > :startdate
        AND
        ml.timecreated < :enddate
        AND
        ms.status = :status
        AND
        mu.entity = :entity
    GROUP BY cc.name)
    UNION
    (
        SELECT "Total" as Espace, COUNT(DISTINCT(mu2.userid))
        FROM {logstore_mentor_user} mu2
        JOIN {logstore_mentor_log} ml2 ON ml2.userlogid = mu2.id
        JOIN {logstore_mentor_session} ms2 ON ml2.sessionlogid = ms2.id
        WHERE
            ml2.timecreated > :startdate2
            AND
            ml2.timecreated < :enddate2
            AND
            ms2.status = :status2
            AND
            mu2.entity = :entity2
    )
', [
        'startdate'  => $startdate,
        'enddate'    => $enddate,
        'status'     => $status,
        'entity'     => $entity,
        'startdate2' => $startdate,
        'enddate2'   => $enddate,
        'status2'    => $status,
        'entity2'    => $entity
]);

print_object($request);

// ---------------------------------------

print_object('Nombre de formation au statut élboration terminée par espace (par collection)');

$request = $DB->get_records_sql("
    SELECT
        cc.name as Espace, COUNT(t.id) as nbFormation
    FROM
        {course_categories} cc
    JOIN
        {course_categories} cc2 ON cc2.path REGEXP CONCAT('^/', cc.id ,'/')
    JOIN
        {course} c ON c.category = cc2.id
    JOIN
        {training} t ON t.courseshortname = c.shortname
    WHERE
        t.status = 'elaboration_completed'
        AND
        cc.parent = 0
        AND
        " . $DB->sql_like('t.collection', ':collection', false, false) . "
    GROUP BY
     cc.name
", array('collection' => '%' . $collection . '%'));

print_object($request);

// ---------------------------------------

print_object('Nombre de session au status "en cours" partagé au moins à 1 autre espace (par collection)');

$request = $DB->get_records_sql("
    SELECT
        cc.name AS 'Espace', COUNT(DISTINCT(lms.sessionid)) AS 'Nombre de sessions'
    FROM
        {logstore_mentor_session} lms
    JOIN
        {session} s ON s.id = lms.sessionid
    JOIN
        {logstore_mentor_log} lml ON lml.sessionlogid = lms.id
    JOIN
        {course_categories} cc ON cc.id = lms.space
    WHERE
        lms.status = 'inprogress' AND
        ( s.opento = 'all' OR
         (s.opento = 'other_entities' AND 0 < (
            SELECT count(ss.id)
            FROM {session_sharing} ss
            JOIN {session} s2 ON s2.id = ss.sessionid
            WHERE s2.id = s.id
         ))) AND
        lml.timecreated > :startdate
        AND
        lml.timecreated < :enddate
", ['startdate' => $startdate, 'enddate' => $enddate]);

print_object($request);

// ---------------------------------------

print_object('Sessions disponibles par espace');

$request = $DB->get_records_sql("
    SELECT
        cc.name as Espace, (
            SELECT COUNT(s.id)
            FROM {session} s
            JOIN {course} c ON s.courseshortname = c.shortname
            JOIN {course_categories} cc2 ON c.category = cc2.id
            WHERE
                (s.status = 'inprogress'
                OR
                s.status = 'openedregistration')
                AND
                cc2.path LIKE CONCAT('\/', cc.id ,'\/%')
        ) AS 'ouvertes',
        (
            SELECT  COUNT(s2.id)
            FROM {session} s2
            JOIN {course} c2 ON s2.courseshortname = c2.shortname
            JOIN {course_categories} cc3 ON c2.category = cc3.id
            WHERE
                (s2.status = 'inprogress'
                OR
                s2.status = 'openedregistration')
                AND
                cc3.path NOT LIKE CONCAT('\/', cc.id ,'\/%')
                AND
                (s2.opento = 'all' OR cc.id IN (
                    SELECT coursecategoryid FROM {session_sharing} WHERE sessionid = s2.id
                ))
        ) AS 'partagees'

    FROM
        {course_categories} cc
    WHERE
        cc.parent = 0
    ORDER BY
        cc.name ASC
");

print_object($request);

// ---------------------------------------

print_object('Nombre de de participant qui se sont connectés ' .
             'au moins 1 fois sur une période à 1 session "en cours" (par collection)');

$request = $DB->get_records_sql("
    SELECT
        cc.name AS 'Espace',
        COALESCE((
            SELECT SUM(nbrparticipant)
            FROM(
                SELECT  COUNT(DISTINCT(lmu.userid)) AS nbrparticipant, lms.space AS space
                        FROM {logstore_mentor_log} lml
                        JOIN {logstore_mentor_user} lmu ON lml.userlogid = lmu.id
                        JOIN {logstore_mentor_session} lms ON lms.id = lml.sessionlogid
                        JOIN {logstore_mentor_collection} lmc ON lmc.sessionlogid = lms.id
                        WHERE lms.status = 'inprogress' AND
                              lmc.name = :collection AND
                              lml.timecreated > :startdate AND
                              lml.timecreated < :enddate
                        GROUP BY lms.sessionid,
                                 lms.space
            ) AS src
            WHERE src.space = cc.id
        ), 0) AS 'Nombre de participant'
    FROM {course_categories} cc
    WHERE cc.parent = 0
    ORDER BY cc.name ASC", ['collection' => 'management',
                            'startdate' => $startdate,
                            'enddate' => $enddate]);

print_object($request);

// ---------------------------------------

print_object('Nombre de de paraticipant qui se sont connectés au moins 1 fois ' .
             'sur une période à 1 session "en cours" (par entité de rattachement)');

$request = $DB->get_records_sql("
    SELECT
        cc.name AS 'Espace',
        COALESCE((
            SELECT SUM(nbrparticipant)
            FROM(
                SELECT  COUNT(DISTINCT(lmu.userid)) AS nbrparticipant, lms.space AS space
                        FROM {logstore_mentor_log} lml
                        JOIN {logstore_mentor_user} lmu ON lml.userlogid = lmu.id
                        JOIN {logstore_mentor_session} lms ON lms.id = lml.sessionlogid
                        WHERE lms.status = 'inprogress' AND
                              lmu.entity = :mainentity AND
                              lml.timecreated > :startdate AND
                              lml.timecreated < :enddate
                        GROUP BY lms.sessionid,
                                 lms.space
            ) AS src
            WHERE src.space = cc.id
        ), 0) AS 'Nombre de participant'
    FROM {course_categories} cc
    WHERE cc.parent = 0
    ORDER BY cc.name ASC", ['mainentity' => 1,
                            'startdate' => $startdate,
                            'enddate' => $enddate]);

print_object($request);

// ---------------------------------------

print_object('Sessions en cours ouvertes à au moins un autre espace');

$request = $DB->get_records_sql("
    SELECT
        cc.name as Espace, (
            SELECT COUNT(DISTINCT s.id)
            FROM {session} s
            JOIN {course} c ON s.courseshortname = c.shortname
            JOIN {course_categories} cc2 ON c.category = cc2.id
            WHERE
                s.status != 'inpreparation'
                AND
                cc2.path LIKE CONCAT('\/', cc.id ,'\/%')
                AND (
                    (s.sessionstartdate <= :startdate AND s.sessionenddate >= :startdate2)
                    OR
                    (s.sessionstartdate <= :enddate AND s.sessionenddate >= :enddate2)
                    OR
                    (s.sessionstartdate >= :startdate3 AND s.sessionenddate <= :enddate3)
                )
                AND (
                    (s.opento = 'all' OR
                    (s.opento = 'other_entities' AND 0 < (
                        SELECT count(ss.id)
                        FROM {session_sharing} ss
                        WHERE ss.sessionid = s.id
                     )))
                )
        ) as 'Nombre de sessions'

    FROM
        {course_categories} cc
    WHERE
        cc.parent = 0
    ORDER BY
        cc.name ASC
", [
        'startdate'  => 1622362636,
        'startdate2' => 1622362636,
        'startdate3' => 1622362636,
        'enddate'    => 1627633036,
        'enddate2'   => 1627633036,
        'enddate3'   => 1627633036
]);

print_object($request);

// ---------------------------------------

print_object('Nombre d’heures de formation dispensées à distance par espaces sur une période ' .
             '( Nombre de participants qui se sont connectés au moins 1 fois pendant la période à une session ' .
             '"en cours"  x  durée  en distanciel de la session  (chaque agent compte 1 fois PAR session différente ' .
             'à laquelle il s"est connecté) . Filtré par collection');

$request = $DB->get_records_sql("
    SELECT
        cc.name AS 'Espace',
        (
            SELECT CONCAT(COALESCE(CEIL(
                (SUM(remotetime) - (SUM(remotetime) % 60)) / 60
            ), 0), 'h ', COALESCE(SUM(remotetime) % 60, 0), 'min')
            FROM(
                SELECT  SUM(s.onlinesessionestimatedtime)  as  remotetime, lms.space AS space
                        FROM {logstore_mentor_log} lml
                        JOIN {logstore_mentor_user} lmu ON lml.userlogid = lmu.id
                        JOIN {logstore_mentor_session} lms ON lms.id = lml.sessionlogid
                        JOIN {session} s ON s.id = lms.sessionid
                        JOIN {logstore_mentor_collection} lmc ON lmc.sessionlogid = lms.id
                        WHERE lms.status = 'inprogress' AND
                              lmc.name = :collection AND
                              lml.timecreated > :startdate AND
                              lml.timecreated < :enddate
                        GROUP BY lms.sessionid,
                                 lmu.userid,
                                 lms.space
            ) AS src
            WHERE src.space = cc.id
        ) AS 'remotetime'
    FROM {course_categories} cc
    WHERE cc.parent = 0
    ORDER BY cc.name ASC", ['collection' => 'management',
                            'startdate' => 1623675562,
                            'enddate' => 1626267562]);

print_object($request);

// ---------------------------------------

print_object('Nombre d’heures de formation dispensées à distance par espaces sur une période' .
             ' ( Nombre de participants qui se sont connectés au moins 1 fois pendant la période à une session' .
             ' "en cours"  x  durée en distanciel de la session  (chaque agent compte 1 fois PAR session différente' .
             ' à laquelle il s\'est connecté) . Filtré par entité de rattachement ');

$request = $DB->get_records_sql("
    SELECT
        cc.name AS 'Espace',
        (
            SELECT CONCAT(COALESCE(CEIL((
                SUM(remotetime) - (SUM(remotetime) % 60)
            ) / 60), 0), 'h ', COALESCE(SUM(remotetime) % 60, 0), 'min')
            FROM(
                SELECT  SUM(s.onlinesessionestimatedtime)  as  remotetime, lms.space AS space
                        FROM {logstore_mentor_log} lml
                        JOIN {logstore_mentor_user} lmu ON lml.userlogid = lmu.id
                        JOIN {logstore_mentor_session} lms ON lms.id = lml.sessionlogid
                        JOIN {session} s ON s.id = lms.sessionid
                        WHERE lms.status = 'inprogress' AND
                              lmu.entity = :mainentity AND
                              lml.timecreated > :startdate AND
                              lml.timecreated < :enddate
                        GROUP BY lms.sessionid,
                                 lmu.userid,
                                 lms.space
            ) AS src
            WHERE src.space = cc.id
        ) AS 'remotetime'
    FROM {course_categories} cc
    WHERE cc.parent = 0
    ORDER BY cc.name ASC", ['mainentity' => 1,
                            'startdate' => 1623675562,
                            'enddate' => 1626267562]);

print_object($request);

// ---------------------------------------

print_object('Nombre d’heures de formation dispensées en présentiel par espaces sur une période' .
             ' ( Nombre de participants qui se sont connectés au moins 1 fois pendant la période à une session' .
             ' "en cours"  x  durée  en présence de la session  (chaque agent compte 1 fois PAR session différente' .
             ' à laquelle il s\'est connecté) . Filtré par collection');

$request = $DB->get_records_sql("
    SELECT
        cc.name AS 'Espace',
        (
            SELECT CONCAT(
                COALESCE(CEIL((SUM(remotetime) - (SUM(remotetime) % 60)) / 60), 0),
                'h ', COALESCE(SUM(remotetime) % 60, 0), 'min'
            )
            FROM(
                SELECT  SUM(s.presencesessionestimatedtime)  as  remotetime, lms.space AS space
                        FROM {logstore_mentor_log} lml
                        JOIN {logstore_mentor_user} lmu ON lml.userlogid = lmu.id
                        JOIN {logstore_mentor_session} lms ON lms.id = lml.sessionlogid
                        JOIN {session} s ON s.id = lms.sessionid
                        JOIN {logstore_mentor_collection} lmc ON lmc.sessionlogid = lms.id
                        WHERE lms.status = 'inprogress' AND
                              lmc.name = :collection AND
                              lml.timecreated > :startdate AND
                              lml.timecreated < :enddate
                        GROUP BY lms.sessionid,
                                 lmu.userid,
                                 lms.space
            ) AS src
            WHERE src.space = cc.id
        ) AS 'remotetime'
    FROM {course_categories} cc
    WHERE cc.parent = 0
    ORDER BY cc.name ASC", ['collection' => 'management',
                            'startdate' => 1623675562,
                            'enddate' => 1626267562]);

print_object($request);

// ---------------------------------------

print_object('Nombre d’heures de formation dispensées à distance par espaces sur une période' .
             ' ( Nombre de participants qui se sont connectés au moins 1 fois pendant la période à une session' .
             ' "en cours"  x  durée en distanciel de la session  (chaque agent compte 1 fois PAR session différente' .
             ' à laquelle il s\'est connecté) . Filtré par entité de rattachement ');

$request = $DB->get_records_sql("
    SELECT
        cc.name AS 'Espace',
        (
            SELECT CONCAT(COALESCE(CEIL((
                SUM(remotetime) - (SUM(remotetime) % 60)) / 60
            ), 0), 'h ', COALESCE(SUM(remotetime) % 60, 0), 'min')
            FROM(
                SELECT  SUM(s.presencesessionestimatedtime)  as  remotetime, lms.space AS space
                        FROM {logstore_mentor_log} lml
                        JOIN {logstore_mentor_user} lmu ON lml.userlogid = lmu.id
                        JOIN {logstore_mentor_session} lms ON lms.id = lml.sessionlogid
                        JOIN {session} s ON s.id = lms.sessionid
                        WHERE lms.status = 'inprogress' AND
                              lmu.entity = :mainentity AND
                              lml.timecreated > :startdate AND
                              lml.timecreated < :enddate
                        GROUP BY lms.sessionid,
                                 lmu.userid,
                                 lms.space
            ) AS src
            WHERE src.space = cc.id
        ) AS 'remotetime'
    FROM {course_categories} cc
    WHERE cc.parent = 0
    ORDER BY cc.name ASC", ['mainentity' => 1,
                            'startdate' => 1623675562,
                            'enddate' => 1626267562]);

print_object($request);

// ---------------------------------------

print_object('Nombre d\'agent uniques formés sur une période triés par entité de rattachement');

$request = $DB->get_records_sql("
    SELECT
        cc.name as Espace, (
            SELECT COUNT(DISTINCT(mu.userid))
            FROM {logstore_mentor_user} mu
            JOIN {logstore_mentor_history_log} ml ON ml.userlogid = mu.id
            JOIN {logstore_mentor_session} ms ON ml.sessionlogid = ms.id
            WHERE
                ms.space = cc.id
                AND ms.status = 'inprogress'
                AND mu.trainer = 0
                ml.timecreated > :startdate AND
                ml.timecreated < :enddate
            ) as agents
        FROM
            prefix_course_categories cc
        WHERE
            cc.parent = 0
        ORDER BY
            cc.name ASC",
        [
                            'startdate' => 1623675562,
                            'enddate' => 1626267562
        ]);

print_object($request);
*/
