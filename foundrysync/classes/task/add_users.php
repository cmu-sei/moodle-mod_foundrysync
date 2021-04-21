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
    protected $issuer;

    /**
     * Get a descriptive name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskadduser', 'tool_foundrysync');
    }

    public function setup() {

        $issuerid = get_config('tool_foundrysync', 'usersyncissuerid');
        if (!$issuerid) {
                echo "add_user does not have issuerid set<br>";
            return;
        }

        $issuer = \core\oauth2\api::get_issuer($issuerid);
        $this->issuer = $issuer;
        $this->systemauth = \core\oauth2\api::get_system_oauth_client($issuer);
        if ($this->systemauth === false) {
            $details = 'add_user cannot connect as system account';
            echo "$details<br>";
            throw new \Exception($details);
            return;
        }

        $this->interval = get_config('tool_foundrysync', 'usersyncinterval');
        if (!$this->interval) {
            $this->interval = 15;
        }
    }

    /* queries for created/updated/deleted content */
    public function execute() {

        if (!get_config('tool_foundrysync', 'enableusersync')) {
            echo "add_user is disabled in tool_foundrysync config<br>";
            return; // The tool is disabled. Nothing to do.
        }

        // get the system client, base url and execution interval
        $this->setup();

        // get recent identity server users
        $identity_users = $this->get_recent_identity_users();
        echo count($identity_users) . " user(s) created or modified during the last $this->interval minutes<br>";

        $this->process_users($identity_users);
        echo "<br>DONE PROCESSING USERS<br>";
    }

    public function get_recent_identity_users() {

        $isloggedin = $this->systemauth->is_logged_in();
        $accesstoken = \core\oauth2\access_token::get_record(['issuerid' => $this->issuer->get('id')]);

        $time = date(DATE_ATOM, (time() - $this->interval * 60 * 10000));  // interval is in minutes but we need seconds to 2021-04-21T11:12:13-04:00
        $url = $this->issuer->get('baseurl') . '/api/accounts?Since=' . urlencode($time);
        $response = $this->systemauth->get($url);
        if (!$response) {
            debugging("no response received by get_recent_identity_users for $url", DEBUG_DEVELOPER);
        }
        if ($this->systemauth->info['http_code']  !== 200) {
            debugging('response code ' . $this->systemauth->info['http_code'] . " for $url", DEBUG_DEVELOPER);
            return;
        }
        $r = json_decode($response);

        if (!$r) {
            debugging("could not find item by id", DEBUG_DEVELOPER);
            return;
        }
        return $r;
    }

    public function process_users($identity_users) {
        $count = 0;

        foreach ($identity_users as $identity_user) {
            echo "#################### processing user " . $count++ . "<br>";
        }
    }

}

