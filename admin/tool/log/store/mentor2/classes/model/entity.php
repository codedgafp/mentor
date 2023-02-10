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
 * Entity log table Class
 *
 * @package    logstore_mentor/models
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     adrien <adrien.jamot@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_mentor2\models;

class entity extends abstractlog {

    public function get_required_fields() {
        return [
                'entityid',
                'name',
                'regions'
        ];
    }

    /**
     * if @datas exit in log @table
     *      return id element
     * else
     *      insert @data in log @table and return id element created
     *
     * @param $data data's event
     * @param $table table name where save data's event
     * @return int id element
     * @throws \dml_exception
     * @throws \ReflectionException
     */
    public function get_or_create_record($table = '', $data = null) {
        global $DB;

        // By default the table name is the same as the class name.
        if (empty($table)) {
            $table = 'entity2';
        }

        // Return id if exists.
        $existingentities = $DB->get_records('logstore_mentor_entity2', [
                'entityid' => $this->eventdata['entityid'],
                'name'     => $this->eventdata['name']
        ]);

        // Entity does no exist.
        if (empty($existingentities)) {
            return $this->create($table);
        } else {
            foreach ($existingentities as $existingentity) {
                $regionslogs = $DB->get_records_sql('
                    SELECT lmr.name
                    FROM {logstore_mentor_region2} lmr
                    JOIN {logstore_mentor_entityreg2} lme ON lme.regionlogid = lmr.id
                    WHERE
                        lme.entitylogid = :entitylogid
                    ORDER BY name ASC
                ', ['entitylogid' => $existingentity->id]);

                $regionsnames = [];
                foreach ($regionslogs as $regionslog) {
                    $regionsnames[] = $regionslog->name;
                }

                // Regions selector.
                $dbinterface = \local_mentor_specialization\database_interface::get_instance();
                $regions     = $dbinterface->get_all_regions();

                $entityregions = [];

                foreach ($this->eventdata['regions'] as $regionid) {

                    if ($regionid != 0) {
                        // Add regions to entity data.
                        $entityregions[] = $regions[$regionid]->name;
                    }
                }

                if (empty(array_diff($entityregions, $regionsnames))) {
                    return $existingentity->id;
                }
            }

            // None of the regions arrays are equals, create a new entity.
            return $this->create($table);
        }

    }

    /**
     * Create a new entity log
     *
     * @param string $table
     * @return bool|int
     * @throws \ReflectionException
     * @throws \dml_exception
     */
    private function create($table = '') {
        global $DB;
        // Create entity.
        $this->id = $this->dbinterface->insert_record($table, $this->eventdata);

        // Regions selector.
        $dbinterface = \local_mentor_specialization\database_interface::get_instance();
        $regions     = $dbinterface->get_all_regions();

        foreach ($this->eventdata['regions'] as $regionid) {

            if ($regionid == 0) {
                continue;
            }

            // Get or create region.
            $regionlog   = new \logstore_mentor2\models\region(['name' => $regions[$regionid]->name]);
            $regionlogid = $regionlog->get_or_create_record('region2', ['name' => $regions[$regionid]->name]);

            // Set entity region.
            $entityreg              = new \stdClass();
            $entityreg->entitylogid = $this->id;
            $entityreg->regionlogid = $regionlogid;
            $this->dbinterface->insert_record('entityreg2', $entityreg);
        }

        return $this->id;
    }
}
