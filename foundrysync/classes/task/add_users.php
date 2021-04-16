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
 * Version details.
 *
 * @package    tool
 * @subpackage foundrysync
 * @copyright  2020 Carnegie Mellon University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
Foundry Sync Plugin for Moodle
Copyright 2020 Carnegie Mellon University.
NO WARRANTY. THIS CARNEGIE MELLON UNIVERSITY AND SOFTWARE ENGINEERING INSTITUTE MATERIAL IS FURNISHED ON AN "AS-IS" BASIS. CARNEGIE MELLON UNIVERSITY MAKES NO WARRANTIES OF ANY KIND, EITHER EXPRESSED OR IMPLIED, AS TO ANY MATTER INCLUDING, BUT NOT LIMITED TO, WARRANTY OF FITNESS FOR PURPOSE OR MERCHANTABILITY, EXCLUSIVITY, OR RESULTS OBTAINED FROM USE OF THE MATERIAL. CARNEGIE MELLON UNIVERSITY DOES NOT MAKE ANY WARRANTY OF ANY KIND WITH RESPECT TO FREEDOM FROM PATENT, TRADEMARK, OR COPYRIGHT INFRINGEMENT.
Released under a GNU GPL 3.0-style license, please see license.txt or contact permission@sei.cmu.edu for full terms.
[DISTRIBUTION STATEMENT A] This material has been approved for public release and unlimited distribution.  Please see Copyright notice for non-US Government use and distribution.
This Software includes and/or makes use of the following Third-Party Software subject to its own license:
1. Moodle (https://docs.moodle.org/dev/License) Copyright 1999 Martin Dougiamas.
DM20-0198
 */

namespace tool_foundrysync\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/admin/user/lib.php');

class add_users extends \core\task\scheduled_task {

    protected $systemauth;
    protected $sitename;

    /**
     * Get a descriptive name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskadduser', 'tool_foundrysync');
    }

    public function setup() {
        global $SITE;

        $issuerid = get_config('tool_foundrysync', 'issuerid');
        if (!$issuerid) {
                echo "add_user does not have issuerid set<br>";
            return;
        }

        $issuer = \core\oauth2\api::get_issuer($issuerid);
        $this->systemauth = \core\oauth2\api::get_system_oauth_client($issuer);
        if ($this->systemauth === false) {
            $details = 'add_user cannot connect as system account';
            echo "$details<br>";
            throw new \Exception($details);
            return;
        }

        /* TODO strip spaces */
        $this->sitename = $SITE->shortname;
        $this->clientid = $this->systemauth->get_clientid();

        $this->interval = get_config('tool_foundrysync', 'interval');
        if (!$this->interval) {
            $this->interval = 15;
        }
        $this->course = 0;
    }

    /* http://php.net/manual/en/function.com-create-guid.php */
    public function guidv4() {
        if (function_exists('com_create_guid') === true)
            return trim(com_create_guid(), '{}');

        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /* queries for created/updated/deleted content */
    public function execute() {
        global $DB;

        if (!get_config('tool_foundrysync', 'enableusersync')) {
            echo "add_user is disabled in tool_foundrysync config<br>";
            return; // The tool is disabled. Nothing to do.
        }

        $this->setup();

        // prepare query
        $time = time() - $this->interval * 60;

        // get identity server users
        // $identity_users = $DB->get_records_sql("SELECT * from {logstore_standard_log} WHERE eventname REGEXP 'course_(created|updated|deleted)' AND timecreated >= ?", array($time));
        // echo count($identity_users) . " course(s) updated during the last $this->interval minutes<br>";

        // $this->process_users($identity_users);
        echo "<br>DONE PROCESSING USERS<br>";
    }

    public function process_users($identity_users) {
        global $DB, $CFG;
        $data = array();
        $count = 0;

        foreach ($identity_users as $identity_user) {
            echo "#################### processing user " . $count++ . "<br>";
        }
    }

}

