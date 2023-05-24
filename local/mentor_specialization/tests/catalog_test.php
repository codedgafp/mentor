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
 *
 *
 * @package    local_mentor_specialization
 * @copyright  2021 Edunao SAS (contact@edunao.com)
 * @author     mounir <mounir.ganem@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_mentor_core\specialization;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/local/catalog/lib.php');

class local_mentor_specialization_catalog_testcase extends advanced_testcase {

    /**
     * Init $CFG
     */
    public function init_config() {
        global $CFG;

        $CFG->mentor_specializations = [
                '\\local_mentor_specialization\\mentor_specialization' =>
                        'local/mentor_specialization/classes/mentor_specialization.php'
        ];
    }

    /**
     * Reset the singletons
     *
     * @throws ReflectionException
     */
    public function reset_singletons() {
        // Reset the mentor core specialization singleton.
        $specialization = \local_mentor_core\specialization::get_instance();
        $reflection = new ReflectionClass($specialization);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true); // Now we can modify that :).
        $instance->setValue(null, null); // Instance is gone.
        $instance->setAccessible(false); // Clean up.

        \local_mentor_core\training_api::clear_cache();
    }

    /**
     * Test the string cleaner for the search dictionnary
     */
    public function test_search_string_cleaner() {
        $this->resetAfterTest(false);
        $this->init_config();

        $string = 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝŸ';
        $cleanedstring = local_catalog_clean_string($string);

        self::assertSame('aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUYY', $cleanedstring);
    }

    /**
     * Test the dictionnary return value
     */
    public function test_dictionnary() {
        $this->resetAfterTest(false);
        $this->init_config();
        $this->reset_singletons();

        $training1 = new stdClass();
        $training1->id = 1;
        $training1->name = 'BlaBla';
        $training1->typicaljob = 'déDédèdê';
        $training1->skills = 'blabla,blabla,dadada';
        $training1->content = "<p><span>zéz èzà</span><i id='test'>ù</i></p>";
        $training1->idsirh = 'TeSt';
        $training1->producingorganization = 'producingorganization';
        $training1->producerorganizationshortname = 'producingorg';
        $training1->catchphrase = 'Catch Phrase';
        $training1->entityname = 'Entity name';
        $training1->entityfullname = 'Entity fullname';

        $training2 = new stdClass();
        $training2->id = 2;
        $training2->name = 'BlaBla éèàçç';
        $training2->typicaljob = 'déDédèdê';
        $training2->skills = 'blabla,blabla,dadada';
        $training2->content = "<p><span>zéz èzà</span><i id='test'>ù</i></p>";
        $training2->idsirh = 'TeSt';
        $training2->producingorganization = 'producingorganization';
        $training2->producerorganizationshortname = 'producingorg';
        $training2->catchphrase = 'Catch Phrase';
        $training2->entityname = 'Entity name';
        $training2->entityfullname = 'Entity fullname';

        $dictionnary = local_catalog_get_dictionnary([$training1, $training2]);

        $expected = [
                1 => "blabla dededede blabla,blabla,dadada zez ezau test" .
                     " producingorganization producingorg catch phrase entity name entity fullname",
                2 => "blabla eeacc dededede blabla,blabla,dadada zez ezau test" .
                     " producingorganization producingorg catch phrase entity name entity fullname",
        ];

        self::assertSame($expected, $dictionnary);
    }
}
