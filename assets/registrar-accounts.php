<?php
/**
 * /assets/registrar-accounts.php
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
<?php //@formatter:off
require_once('../_includes/start-session.inc.php');
require_once('../_includes/init.inc.php');

require_once(DIR_ROOT . 'classes/Autoloader.php');
spl_autoload_register('DomainMOD\Autoloader::classAutoloader');

$system = new DomainMOD\System();
$error = new DomainMOD\Error();
$layout = new DomainMOD\Layout();
$time = new DomainMOD\Time();

require_once(DIR_INC . 'head.inc.php');
require_once(DIR_INC . 'config.inc.php');
require_once(DIR_INC . 'software.inc.php');
require_once(DIR_INC . 'settings/assets-registrar-accounts.inc.php');
require_once(DIR_INC . 'database.inc.php');

$system->authCheck($web_root);

$rid = $_GET['rid'];
$raid = $_GET['raid'];
$oid = $_GET['oid'];
$export_data = $_GET['export_data'];

if ($rid != '') { $rid_string = " AND ra.registrar_id = '$rid' "; } else { $rid_string = ''; }
if ($raid != '') { $raid_string = " AND ra.id = '$raid' "; } else { $raid_string = ''; }
if ($oid != '') { $oid_string = " AND ra.owner_id = '$oid' "; } else { $oid_string = ''; }

$sql = "SELECT ra.id AS raid, ra.email_address, ra.username, ra.password, ra.reseller, ra.reseller_id, ra.api_app_name,
            ra.api_key, ra.api_secret, ra.api_ip_id, ra.owner_id, ra.registrar_id, o.id AS oid, o.name AS oname,
            r.id AS rid, r.name AS rname, ra.notes, ra.creation_type_id, ra.created_by, ra.insert_time, ra.update_time
        FROM registrar_accounts AS ra, owners AS o, registrars AS r
        WHERE ra.owner_id = o.id
          AND ra.registrar_id = r.id
          $rid_string
          $raid_string
          $oid_string
        GROUP BY ra.username, oname, rname
        ORDER BY rname, username, oname";

if ($export_data == '1') {

    $result = mysqli_query($dbcon, $sql) or $error->outputOldSqlError($dbcon);

    $export = new DomainMOD\Export();
    $export_file = $export->openFile('registrar_account_list', strtotime($time->stamp()));

    $row_contents = array($page_title);
    $export->writeRow($export_file, $row_contents);

    $export->writeBlankRow($export_file);

    $row_contents = array(
        'Status',
        'Registrar',
        'Email Address',
        'Username',
        'Password',
        'Reseller Account?',
        'Reseller ID',
        'API App Name',
        'API Key',
        'API Secret',
        'API IP (Name)',
        'API IP (IP)',
        'Owner',
        'Domains',
        'Default Account?',
        'Notes',
        'Creation Type',
        'Created By',
        'Inserted',
        'Updated'
    );
    $export->writeRow($export_file, $row_contents);

    if (mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_object($result)) {

            if ($row->api_ip_id != '0') {

                $sql_temp = "SELECT `name`, ip
                             FROM ip_addresses
                             WHERE id = '" . $row->api_ip_id . "'";
                $result_temp = mysqli_query($dbcon, $sql_temp);

                while ($row_temp = mysqli_fetch_object($result_temp)) {

                    $api_ip_name = $row_temp->name;
                    $api_ip_address = $row_temp->ip;

                }

            } else {

                $api_ip_name = '';
                $api_ip_address = '';

            }

            $sql_domain_count = "SELECT count(*) AS total_domain_count
                                 FROM domains
                                 WHERE account_id = '" . $row->raid . "'
                                   AND active NOT IN ('0', '10')";
            $result_domain_count = mysqli_query($dbcon, $sql_domain_count);

            while ($row_domain_count = mysqli_fetch_object($result_domain_count)) {
                $total_domains = $row_domain_count->total_domain_count;
            }

            if ($row->raid == $_SESSION['s_default_registrar_account']) {

                $is_default = '1';

            } else {

                $is_default = '0';

            }

            if ($row->reseller == '0') {

                $is_reseller = '0';

            } else {

                $is_reseller = '1';

            }

            if ($total_domains >= 1) {

                $status = 'Active';

            } else {

                $status = 'Inactive';

            }

            $creation_type = $system->getCreationType($dbcon, $row->creation_type_id);

            if ($row->created_by == '0') {
                $created_by = 'Unknown';
            } else {
                $user = new DomainMOD\User();
                $created_by = $user->getFullName($dbcon, $row->created_by);
            }

            $row_contents = array(
                $status,
                $row->rname,
                $row->email_address,
                $row->username,
                $row->password,
                $is_reseller,
                $row->reseller_id,
                $row->api_app_name,
                $row->api_key,
                $row->api_secret,
                $api_ip_name,
                $api_ip_address,
                $row->oname,
                $total_domain_count,
                $is_default,
                $row->notes,
                $creation_type,
                $created_by,
                $time->toUserTimezone($row->insert_time),
                $time->toUserTimezone($row->update_time)
            );
            $export->writeRow($export_file, $row_contents);

            $current_raid = $row->raid;

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
<?php require_once(DIR_INC . 'layout/header.inc.php'); ?>
Below is a list of all the Domain Registrar Accounts that are stored in <?php echo $software_title; ?>.<BR><BR>
<a href="add/registrar-account.php"><?php echo $layout->showButton('button', 'Add Registrar Account'); ?></a>&nbsp;&nbsp;&nbsp;
<a href="registrar-accounts.php?export_data=1&rid=<?php echo urlencode($rid); ?>&raid=<?php echo urlencode($raid); ?>&oid=<?php echo urlencode($oid); ?>"><?php echo $layout->showButton('button', 'Export'); ?></a><BR><BR><?php

$result = mysqli_query($dbcon, $sql) or $error->outputOldSqlError($dbcon);

if (mysqli_num_rows($result) > 0) { ?>

    <table id="<?php echo $slug; ?>" class="<?php echo $datatable_class; ?>">
        <thead>
        <tr>
            <th width="20px"></th>
            <th>Registrar</th>
            <th>Account</th>
            <th>Owner</th>
            <th>Domains</th>
        </tr>
        </thead>

        <tbody><?php

        while ($row = mysqli_fetch_object($result)) {

            $sql_domain_count = "SELECT count(*) AS total_domain_count
                                 FROM domains
                                 WHERE account_id = '" . $row->raid . "'
                                   AND active NOT IN ('0', '10')";
            $result_domain_count = mysqli_query($dbcon, $sql_domain_count);

            while ($row_domain_count = mysqli_fetch_object($result_domain_count)) {
                $total_domains = $row_domain_count->total_domain_count;
            }

            if ($total_domains >= 1 || $_SESSION['s_display_inactive_assets'] == '1') { ?>

                <tr>
                <td></td>
                <td>
                    <a href="edit/registrar.php?rid=<?php echo $row->rid; ?>"><?php echo $row->rname; ?></a>
                </td>
                <td>
                    <a href="edit/registrar-account.php?raid=<?php echo $row->raid; ?>"><?php echo $row->username; ?></a><?php
                    if ($_SESSION['s_default_registrar_account'] == $row->raid) echo '<strong>*</strong>'; ?><?php
                    if ($row->reseller == '1') echo '<strong>^</strong>'; ?>
                </td>
                <td>
                    <a href="edit/account-owner.php?oid=<?php echo $row->oid; ?>"><?php echo $row->oname; ?></a>
                </td>
                <td><?php

                    if ($total_domains >= 1) { ?>

                        <a href="../domains/index.php?oid=<?php echo $row->oid; ?>&rid=<?php echo $row->rid; ?>&raid=<?php echo $row->raid; ?>"><?php echo $total_domains; ?></a><?php

                    } else {

                        echo '-';

                    } ?>

                </td>
                </tr><?php

            }

        } ?>

        </tbody>
    </table>

    <strong>*</strong> = Default (<a href="../settings/defaults/">set defaults</a>)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>^</strong> = Reseller<BR><BR><?php

} else {

    $sql = "SELECT id
            FROM registrars
            LIMIT 1";
    $result = mysqli_query($dbcon, $sql);

    if (mysqli_num_rows($result) == 0) { ?>

        <BR>Before adding a Registrar Account you must add at least one Registrar. <a href="add/registrar.php">Click here to add a Registrar</a>.<BR><?php

    } else { ?>

        <BR>You don't currently have any Registrar Accounts. <a href="add/registrar-account.php">Click here to add one</a>.<BR><?php

    }

}
?>
<?php require_once(DIR_INC . 'layout/asset-footer.inc.php'); ?>
<?php require_once(DIR_INC . 'layout/footer.inc.php'); //@formatter:on ?>
</body>
</html>
