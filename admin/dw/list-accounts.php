<?php
/**
 * /admin/dw/list-accounts.php
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
require_once('../../_includes/start-session.inc.php');
require_once('../../_includes/init.inc.php');

require_once(DIR_ROOT . 'classes/Autoloader.php');
spl_autoload_register('DomainMOD\Autoloader::classAutoloader');

$system = new DomainMOD\System();
$error = new DomainMOD\Error();
$layout = new DomainMOD\Layout();
$time = new DomainMOD\Time();

require_once(DIR_INC . 'head.inc.php');
require_once(DIR_INC . 'config.inc.php');
require_once(DIR_INC . 'software.inc.php');
require_once(DIR_INC . 'settings/dw-list-accounts.inc.php');
require_once(DIR_INC . 'database.inc.php');

$system->authCheck($web_root);
$system->checkAdminUser($_SESSION['s_is_admin'], $web_root);

$domain = $_GET['domain'];
$export_data = $_GET['export_data'];

if ($_SESSION['s_dw_view_all'] == "1") {

    $where_clause = " ";
    $order_clause = " ORDER BY a.unix_startdate DESC, s.name ASC, a.domain ASC ";

} else {

    $where_clause = " AND a.server_id = '" . $_SESSION['s_dw_server_id'] . "' ";
    $order_clause = " ORDER BY s.name ASC, a.unix_startdate DESC ";

}

if ($domain != "") { //@formatter:off

    $sql = "SELECT a.*, s.id AS dw_server_id, s.name AS dw_server_name, s.host AS dw_server_host
            FROM dw_accounts AS a, dw_servers AS s
            WHERE a.server_id = s.id
              AND a.domain = '" . mysqli_real_escape_string($dbcon, $domain) . "'" .
              $where_clause .
            $order_clause;
} else {

    $sql = "SELECT a.*, s.id AS dw_server_id, s.name AS dw_server_name, s.host AS dw_server_host
            FROM dw_accounts AS a, dw_servers AS s
            WHERE a.server_id = s.id " .
              $where_clause .
            $order_clause;

} //@formatter:on

if ($export_data == "1") {

    $result = mysqli_query($dbcon, $sql) or $error->outputOldSqlError($dbcon);

    $export = new DomainMOD\Export();
    $export_file = $export->openFile('dw_account_list', strtotime($time->stamp()));

    $row_contents = array($page_title);
    $export->writeRow($export_file, $row_contents);

    $export->writeBlankRow($export_file);

    $row_contents = array(
        'Number of Accounts:', number_format(mysqli_num_rows($result))
    );
    $export->writeRow($export_file, $row_contents);

    $export->writeBlankRow($export_file);

    if ($domain != "") {

        $row_contents = array(
            'Domain Filter:',
            $domain
        );
        $export->writeRow($export_file, $row_contents);

        $export->writeBlankRow($export_file);

    }

    $row_contents = array(
        'Server Name',
        'Server Host',
        'Domain',
        'IP Address',
        'Owner',
        'User',
        'Email',
        'Plan',
        'Theme',
        'Shell',
        'Partition',
        'Disk Limit (MB)',
        'Disk Used (MB)',
        'Max Addons',
        'Max FTP',
        'Max Email Lists',
        'Max Parked Domains',
        'Max POP Accounts',
        'Max SQL Databases',
        'Max Subdomains',
        'Start Date',
        'Start Date (Unix)',
        'Suspended?',
        'Suspend Reason',
        'Suspend Time (Unix)',
        'Max Emails Per Hour',
        'Max Email Failure % (For Rate Limiting)',
        'Min Email Failure # (For Rate Limiting)',
        'Inserted (into DW)'
    );
    $export->writeRow($export_file, $row_contents);

    if (mysqli_num_rows($result) > 0) {

        while ($row_dw_account_temp = mysqli_fetch_object($result)) {

            $row_contents = array(
                $row_dw_account_temp->dw_server_name,
                $row_dw_account_temp->dw_server_host,
                $row_dw_account_temp->domain,
                $row_dw_account_temp->ip,
                $row_dw_account_temp->owner,
                $row_dw_account_temp->user,
                $row_dw_account_temp->email,
                $row_dw_account_temp->plan,
                $row_dw_account_temp->theme,
                $row_dw_account_temp->shell,
                $row_dw_account_temp->partition,
                $row_dw_account_temp->disklimit,
                $row_dw_account_temp->diskused,
                $row_dw_account_temp->maxaddons,
                $row_dw_account_temp->maxftp,
                $row_dw_account_temp->maxlst,
                $row_dw_account_temp->maxparked,
                $row_dw_account_temp->maxpop,
                $row_dw_account_temp->maxsql,
                $row_dw_account_temp->maxsub,
                $row_dw_account_temp->startdate,
                $row_dw_account_temp->unix_startdate,
                $row_dw_account_temp->suspended,
                $row_dw_account_temp->suspendreason,
                $row_dw_account_temp->suspendtime,
                $row_dw_account_temp->MAX_EMAIL_PER_HOUR,
                $row_dw_account_temp->MAX_DEFER_FAIL_PERCENTAGE,
                $row_dw_account_temp->MIN_DEFER_FAIL_TO_TRIGGER_PROTECTION,
                $time->toUserTimezone($row_dw_account_temp->insert_time)
            );
            $export->writeRow($export_file, $row_contents);

        }

    }

    $export->closeFile($export_file);

}
?>
<?php require_once(DIR_INC . 'doctype.inc.php'); ?>
<html>
<head>
    <title><?php echo $system->pageTitle($software_title, $page_title); ?></title>
    <?php require_once(DIR_INC . 'layout/head-tags.inc.php'); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
<?php require_once(DIR_INC . 'layout/header.inc.php');
$result = mysqli_query($dbcon, $sql) or $error->outputOldSqlError($dbcon);

if (mysqli_num_rows($result) == 0) {

    echo "Your search returned 0 results.";

} else { ?>

    <a href="list-accounts.php?export_data=1"><?php echo $layout->showButton('button', 'Export'); ?></a><BR><BR><?php

    $dwdisplay = new DomainMOD\DwDisplay(); ?>

    <table id="<?php echo $slug; ?>" class="<?php echo $datatable_class; ?>">
        <thead>
        <tr>
            <th width="20px"></th>
            <th>Account</th>
            <th>Data</th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody><?php

            while ($row = mysqli_fetch_object($result)) { ?>

                <tr>
                    <td></td>
                    <td>
                        <?php echo $dwdisplay->accountSidebar($row->dw_server_name, $row->domain, '1', '1'); ?>
                    </td>

                    <?php echo $dwdisplay->account($dbcon, $row->server_id, $row->domain); ?>

                </tr><?php

            } ?>

        </tbody>
    </table><?php

} ?>
<?php require_once(DIR_INC . 'layout/footer.inc.php'); ?>
</body>
</html>
