<?php
/**
 * /admin/dw/servers.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (c) 2010-2017 Greg Chetcuti <greg@chetcuti.com>
 *
 * Project: http://domainmod.org   Author: http://chetcuti.com
 *
 * DomainMOD is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * DomainMOD is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with DomainMOD. If not, see
 * http://www.gnu.org/licenses/.
 *
 */
?>
<?php
include("../../_includes/start-session.inc.php");
include("../../_includes/init.inc.php");

require_once(DIR_ROOT . "classes/Autoloader.php");
spl_autoload_register('DomainMOD\Autoloader::classAutoloader');

$system = new DomainMOD\System();
$error = new DomainMOD\Error();
$time = new DomainMOD\Time();
$layout = new DomainMOD\Layout();

include(DIR_INC . "head.inc.php");
include(DIR_INC . "config.inc.php");
include(DIR_INC . "software.inc.php");
include(DIR_INC . "settings/dw-servers.inc.php");
include(DIR_INC . "database.inc.php");

$system->authCheck($web_root);
$system->checkAdminUser($_SESSION['s_is_admin'], $web_root);

$export_data = $_GET['export_data'];

$sql = "SELECT id, `name`, `host`, protocol, `port`, username, `hash`, notes, dw_accounts, dw_dns_zones, dw_dns_records, build_end_time, creation_type_id, created_by, insert_time, update_time
        FROM dw_servers
        ORDER BY `name`, `host`";

if ($export_data == "1") {

    $result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

    $export = new DomainMOD\Export();
    $export_file = $export->openFile('dw_servers', strtotime($time->stamp()));

    $row_contents = array($page_title);
    $export->writeRow($export_file, $row_contents);

    $export->writeBlankRow($export_file);

    $row_contents = array(
        'Name',
        'Host',
        'Protocol',
        'Port',
        'Username',
        'Hash',
        'Notes',
        'DW Accounts',
        'DW DNS Zones',
        'DW DNS Records',
        'DW Last Built',
        'Creation Type',
        'Created By',
        'Inserted',
        'Updated'
    );
    $export->writeRow($export_file, $row_contents);

    if (mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_object($result)) {

            $creation_type = $system->getCreationType($connection, $row->creation_type_id);

            if ($row->created_by == '0') {
                $created_by = 'Unknown';
            } else {
                $user = new DomainMOD\User();
                $created_by = $user->getFullName($connection, $row->created_by);
            }

            $row_contents = array(
                $row->name,
                $row->host,
                $row->protocol,
                $row->port,
                $row->username,
                $row->hash,
                $row->notes,
                $row->dw_accounts,
                $row->dw_dns_zones,
                $row->dw_dns_records,
                $time->toUserTimezone($row->build_end_time),
                $creation_type,
                $created_by,
                $time->toUserTimezone($row->insert_time),
                $time->toUserTimezone($row->update_time)
            );
            $export->writeRow($export_file, $row_contents);

        }

    }

    $export->closeFile($export_file);

}
?>
<?php include(DIR_INC . 'doctype.inc.php'); ?>
<html>
<head>
    <title><?php echo $system->pageTitle($software_title, $page_title); ?></title>
    <?php include(DIR_INC . "layout/head-tags.inc.php"); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
<?php include(DIR_INC . "layout/header.inc.php"); ?>
<a href="add-server.php"><?php echo $layout->showButton('button', 'Add Web Server'); ?></a>&nbsp;&nbsp;&nbsp;
<?php
$result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

if (mysqli_num_rows($result) > 0) { ?>

    <a href="servers.php?export_data=1"><?php echo $layout->showButton('button', 'Export'); ?></a><BR><BR>

    <table id="<?php echo $slug; ?>" class="<?php echo $datatable_class; ?>">
        <thead>
        <tr>
            <th width="20px"></th>
            <th>Name</th>
            <th>Host</th>
            <th>Port</th>
            <th>Username</th>
            <th>Inserted</th>
            <th>Updated</th>
        </tr>
        </thead>
    <tbody><?php

    while ($row = mysqli_fetch_object($result)) { ?>

        <tr>
            <td></td>
            <td>
                <a href="edit-server.php?dwsid=<?php echo $row->id; ?>"><?php echo $row->name; ?></a>
            </td>
            <td>
                <?php echo $row->protocol; ?>://<?php echo $row->host; ?>
            </td>
            <td>
                <?php echo $row->port; ?>
            </td>
            <td>
                <?php echo $row->username; ?>
            </td>
            <td><?php

                if ($row->insert_time != "0000-00-00 00:00:00") {

                    $temp_time = $time->toUserTimezone($row->insert_time);

                } else {

                    $temp_time = '-';

                }

                echo $temp_time; ?>
            </td>
            <td><?php

                if ($row->update_time != "0000-00-00 00:00:00") {

                    $temp_time = $time->toUserTimezone($row->update_time);

                } else {

                    $temp_time = '-';

                }

                echo $temp_time; ?>
            </td>
        </tr><?php

    } ?>

    </tbody>
    </table><?php

}
?>
<?php include(DIR_INC . "layout/footer.inc.php"); ?>
</body>
</html>
