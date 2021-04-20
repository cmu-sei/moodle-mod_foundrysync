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


require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

// Check access permissions.
$PAGE->set_context(context_system::instance());
require_login();

$manageurl = new moodle_url("/admin/tool/foundrysync/manage.php");
$action = optional_param('action', '', PARAM_ALPHA);
$contentsyncstatus = optional_param('contentsyncstatus', 0, PARAM_BOOL);
$endpoint = optional_param('endpoint', '', PARAM_URL);
$issuerid = optional_param('issuerid', '', PARAM_INT);
$interval = optional_param('interval', '', PARAM_RAW);
$usersyncstatus = optional_param('usersyncstatus', 0, PARAM_BOOL);
$usersyncissuerid = optional_param('usersyncissuerid', '', PARAM_INT);
$usersyncendpoint = optional_param('usersyncendpoint', '', PARAM_URL);
$usersyncinterval = optional_param('usersyncinterval', '', PARAM_RAW);

$PAGE->set_url($manageurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_title("Manage Foundry Sync");
$PAGE->set_heading("Foundry Sync Settings");

// toggle status of the user sync.
if (!empty($action) && $action == 'changeusersyncstatus') {
    set_config('enableusersync', $usersyncstatus, 'tool_foundrysync');
    redirect(new moodle_url('/admin/tool/foundrysync/manage.php'));
} else if (!empty($action) && $action == 'changeusersyncvalues') {
    if (!empty($action) && (!empty($usersyncendpoint))) {
        set_config('usersyncendpoint', $usersyncendpoint, 'tool_foundrysync');
    }
    if (!empty($action) && (!empty($usersyncissuerid))) {
        set_config('usersyncissuerid', $usersyncissuerid, 'tool_foundrysync');
    }
    /* query interval */
    if (!empty($action) && (!empty($usersyncinterval))) {
        set_config('usersyncinterval', $usersyncinterval, 'tool_foundrysync');
    }
    /* redirect and update */
    redirect(new moodle_url('/admin/tool/foundrysync/manage.php'));
}

// toggle status of the content sync.
if (!empty($action) && $action == 'changecontentsyncstatus') {
    set_config('enablecontentsync', $contentsyncstatus, 'tool_foundrysync');
    redirect(new moodle_url('/admin/tool/foundrysync/manage.php'));
} else if (!empty($action) && $action == 'changevalues') {
    if (!empty($action) && (!empty($endpoint))) {
        set_config('endpoint', $endpoint, 'tool_foundrysync');
    }
    if (!empty($action) && (!empty($issuerid))) {
        set_config('issuerid', $issuerid, 'tool_foundrysync');
    }
    /* query interval */
    if (!empty($action) && (!empty($interval))) {
        set_config('interval', $interval, 'tool_foundrysync');
    }
    /* redirect and update */
    redirect(new moodle_url('/admin/tool/foundrysync/manage.php'));
}

echo $OUTPUT->header();

$contentsyncstatus = get_config('tool_foundrysync', 'enablecontentsync');
$endpoint = get_config('tool_foundrysync', 'endpoint');
$issuerid = get_config('tool_foundrysync', 'issuerid');
$interval = get_config('tool_foundrysync', 'interval');
$usersyncstatus = get_config('tool_foundrysync', 'enableusersync');
$usersyncissuerid = get_config('tool_foundrysync', 'usersyncissuerid');
$usersyncendpoint = get_config('tool_foundrysync', 'usersyncendpoint');
$usersyncinterval = get_config('tool_foundrysync', 'usersyncinterval');

$options = [];
$issuers = core\oauth2\api::get_all_issuers();
foreach ($issuers as $issuer) {
    $options[$issuer->get('id')] = s($issuer->get('name'));
}

echo "<br>\n";

// create link to toggle user sync
if ($usersyncstatus == 1) {
    $statustext = "Disable User Sync";
    $url = new moodle_url("/admin/tool/foundrysync/manage.php",
                array('action' => 'changeusersyncstatus', 'usersyncstatus' => 0));
    echo ' ' . html_writer::link($url,  $statustext);
    echo "<br>\n";
    echo "<br>\n";
    $output = html_writer::start_tag('form', array('method'=>'post', 'action'=>new moodle_url('/admin/tool/foundrysync/manage.php', array('action' => 'changeusersyncvalues'))));
    $output .= html_writer::tag('span', "Choose the OAUTH2 issuer to be used<br>");
    $output .= html_writer::select($options, 'usersyncissuerid', $usersyncissuerid, false, array('id' => 'usersyncissuerid'));
    $output .= "<br>";
    $output .= html_writer::tag('span', "Enter the interval to query, in minutes<br>");
    $output .= html_writer::empty_tag('input', array('type'=>'text', 'class'=>'form-control', 'name'=>'usersyncinterval', 'value'=>$usersyncinterval));
    $output .= "<br>";
    /* add submit button */
    $output .= html_writer::empty_tag('input', array('type' => 'submit', 'class' => 'btn btn-primary', 'value' =>"Submit"));
    $output .= html_writer::end_tag('form');
    echo $output;
} else if ($usersyncstatus == 0) {
    $statustext = "Enable User Sync";
    $url = new moodle_url("/admin/tool/foundrysync/manage.php",
                array('action' => 'changeusersyncstatus', 'usersyncstatus' => 1));
    echo ' ' . html_writer::link($url,  $statustext);
}

echo "<br>\n";
echo "<hr>\n";

// create link to toggle content sync
if ($contentsyncstatus == 1) {
    $statustext = "Disable Content Sync";
    $url = new moodle_url("/admin/tool/foundrysync/manage.php",
                array('action' => 'changecontentsyncstatus', 'contentsyncstatus' => 0));
    echo ' ' . html_writer::link($url,  $statustext);
} else if ($contentsyncstatus == 0) {
    $statustext = "Enable Content Sync";
    $url = new moodle_url("/admin/tool/foundrysync/manage.php",
                array('action' => 'changecontentsyncstatus', 'contentsyncstatus' => 1));
    echo ' ' . html_writer::link($url,  $statustext);
    echo $OUTPUT->footer();
    exit();
}

echo "<br>\n";
echo "<br>\n";

$output = html_writer::start_tag('form', array('method'=>'post', 'action'=>new moodle_url('/admin/tool/foundrysync/manage.php', array('action' => 'changevalues'))));
/* set endpoint */
$output .= html_writer::tag('span', "Enter the API's base URL (ie: https://api.foundry.com)<br>");
$output .= html_writer::empty_tag('input', array('type'=>'text', 'class'=>'form-control', 'name'=>'endpoint', 'value'=>$endpoint));
$output .= html_writer::tag('span', "Choose the OAUTH2 issuer to be used<br>");
$output .= html_writer::select($options, 'issuerid', $issuerid, false, array('id' => 'issuerid'));
$output .= "<br>";
/* TODO add publisher info*/
/* interval */
$output .= html_writer::tag('span', "Enter the interval to query, in minutes<br>");
$output .= html_writer::empty_tag('input', array('type'=>'text', 'class'=>'form-control', 'name'=>'interval', 'value'=>$interval));
$output .= "<br>";
/* add submit button */
$output .= html_writer::empty_tag('input', array('type' => 'submit', 'class' => 'btn btn-primary', 'value' =>"Submit"));
$output .= html_writer::end_tag('form');

echo $output;

echo $OUTPUT->footer();

