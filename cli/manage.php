<?php


define('CLI_SCRIPT', true);

require(__DIR__.'/../../../../config.php');
require_once("$CFG->libdir/clilib.php");
require_once("$CFG->libdir/adminlib.php");

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array('id'=>'',
                                               'baseurl'=>'', 'clientid'=>'', 'clientsecret'=>'',
                                               'loginscopes'=>'', 'name'=>'', 'showonloginpage'=>true,
                                               'image'=>'', 'list'=>false, 'help'=>false),
                                               array('l'=>'list', 'h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Manage oauth2 settings.
Current status displayed if not option specified.

Options:
--id                  ID to update
--baseurl             Service Base URL
--clientid            Client ID
--clientsecret        Client Secret
--loginscopes         Scopes included in a login request
--name                Name
--image               Logo URL
--showonloginpage     Show on login page
--list                List only
-h, --help            Print out this help

Example:
\$ sudo -u www-data /usr/bin/php admin/tool/foundrysync/cli/manage.php
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

// TODO add option to delete an issuer

cli_heading(get_string('manageoauth2', 'tool_foundrysync')." ($CFG->wwwroot)");

$api = new \tool_foundrysync\oauth2\api();

$issuers = $api->get_all_issuers();

if ($options['list']) {
    foreach ($issuers as $issuer) {
        cli_writeln($issuer->get('id'));
        cli_writeln($issuer->get('name'));
        cli_writeln($issuer->get('baseurl'));
        cli_writeln($issuer->get('clientid'));
        cli_writeln($issuer->get('clientsecret'));
        cli_writeln($issuer->get('loginscopes'));
        cli_writeln($issuer->get('loginscopesoffline'));
        cli_writeln($issuer->get('image'));
        cli_writeln($issuer->get('showonloginpage'));
        cli_separator();
    }
    exit;
}

//TODO check whether we already have the issuer, maybe by adding id to options

// set data
$data = new stdClass();
foreach ($options as $key => $value) {
   if (($key === 'help') || ($key === 'list') || ($key === 'id') && ($value === '')) {
       continue;
   }
   $data->$key = $value;
}

if ($options['id'] === '') {
    cli_writeln("Creating new issuer");
    $api->create_issuer($data);
} else {
    cli_writeln("Updating issuer");
    // TODO we actually would want to only set values that are set on command line
    $api->update_issuer($data);
}

