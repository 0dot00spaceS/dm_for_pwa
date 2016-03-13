<?php
/**
 * /assets/ip-addresses.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (c) 2010-2016 Greg Chetcuti <greg@chetcuti.com>
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
include("../_includes/start-session.inc.php");
include("../_includes/init.inc.php");

require_once(DIR_ROOT . "classes/Autoloader.php");
spl_autoload_register('DomainMOD\Autoloader::classAutoloader');

$system = new DomainMOD\System();
$error = new DomainMOD\Error();
$layout = new DomainMOD\Layout();
$time = new DomainMOD\Time();

include(DIR_INC . "head.inc.php");
include(DIR_INC . "config.inc.php");
include(DIR_INC . "software.inc.php");
include(DIR_INC . "settings/assets-ip-addresses.inc.php");
include(DIR_INC . "database.inc.php");

$system->authCheck();

$export_data = $_GET['export_data'];

$sql = "SELECT id, `name`, ip, rdns, notes, insert_time, update_time
        FROM ip_addresses
        ORDER BY `name`";

if ($export_data == '1') {

    $result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

    $export = new DomainMOD\Export();
    $export_file = $export->openFile('ip_address_list', strtotime($time->stamp()));

    $row_contents = array($page_title);
    $export->writeRow($export_file, $row_contents);

    $export->writeBlankRow($export_file);

    $row_contents = array(
        'Status',
        'IP Address Name',
        'IP Address',
        'rDNS',
        'Domains',
        'SSL Certs',
        'Default Domain IP Address?',
        'Default SSL IP Address?',
        'Notes',
        'Inserted',
        'Updated'
    );
    $export->writeRow($export_file, $row_contents);

    if (mysqli_num_rows($result) > 0) {

        while ($row = mysqli_fetch_object($result)) {

            $sql_total_count = "SELECT count(*) AS total_count
                                FROM domains
                                WHERE active NOT IN ('0', '10')
                                  AND ip_id = '" . $row->id . "'";
            $result_total_count = mysqli_query($connection, $sql_total_count);

            while ($row_total_count = mysqli_fetch_object($result_total_count)) {
                $total_domains = $row_total_count->total_count;
            }

            $sql_total_count = "SELECT count(*) AS total_count
                                FROM ssl_certs
                                WHERE active NOT IN ('0')
                                  AND ip_id = '" . $row->id . "'";
            $result_total_count = mysqli_query($connection, $sql_total_count);

            while ($row_total_count = mysqli_fetch_object($result_total_count)) {
                $total_certs = $row_total_count->total_count;
            }

            if ($row->id == $_SESSION['s_default_ip_address_domains']) {

                $is_default_domains = '1';

            } else {

                $is_default_domains = '0';

            }

            if ($row->id == $_SESSION['s_default_ip_address_ssl']) {

                $is_default_ssl = '1';

            } else {

                $is_default_ssl = '0';

            }

            if ($total_domains >= 1 || $total_certs >= 1) {

                $status = 'Active';

            } else {

                $status = 'Inactive';

            }

            $row_contents = array(
                $status,
                $row->name,
                $row->ip,
                $row->rdns,
                $total_domains,
                $total_certs,
                $is_default_domains,
                $is_default_ssl,
                $row->notes,
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
Below is a list of all the IP Addresses that are stored in <?php echo $software_title; ?>.<BR><BR>
<a href="add/ip-address.php"><?php echo $layout->showButton('button', 'Add IP Address'); ?></a>&nbsp;&nbsp;&nbsp;
<a href="ip-addresses.php?export_data=1"><?php echo $layout->showButton('button', 'Export'); ?></a><BR><BR><?php

$result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

if (mysqli_num_rows($result) > 0) { ?>

    <table id="<?php echo $slug; ?>" class="<?php echo $datatable_class; ?>">
        <thead>
        <tr>
            <th width="20px"></th>
            <th>Name</th>
            <th>IP Address</th>
            <th>rDNS</th>
            <th>Domains</th>
            <th>SSL Certs</th>
        </tr>
        </thead>
        <tbody><?php

        while ($row = mysqli_fetch_object($result)) {

            $sql_total_count = "SELECT count(*) AS total_count
                                FROM domains
                                WHERE active NOT IN ('0', '10')
                                  AND ip_id = '" . $row->id . "'";
            $result_total_count = mysqli_query($connection, $sql_total_count);

            while ($row_total_count = mysqli_fetch_object($result_total_count)) {
                $total_domains = $row_total_count->total_count;
            }

            $sql_total_count = "SELECT count(*) AS total_count
                                FROM ssl_certs
                                WHERE active NOT IN ('0')
                                  AND ip_id = '" . $row->id . "'";
            $result_total_count = mysqli_query($connection, $sql_total_count);

            while ($row_total_count = mysqli_fetch_object($result_total_count)) {
                $total_certs = $row_total_count->total_count;
            }

            if (($total_domains >= 1 || $total_certs >= 1) || $_SESSION['s_display_inactive_assets'] == '1') { ?>

                <tr>
                <td></td>
                <td>
                    <a href="edit/ip-address.php?ipid=<?php echo $row->id; ?>"><?php echo $row->name; ?></a><?php if ($_SESSION['s_default_ip_address_domains'] == $row->id) echo '<strong>*</strong>'; ?><?php if ($_SESSION['s_default_ip_address_ssl'] == $row->id) echo '<strong>^</strong>'; ?>
                </td>
                <td>
                    <a href="edit/ip-address.php?ipid=<?php echo $row->id; ?>"><?php echo $row->ip; ?></a>
                </td>
                <td>
                    <a href="edit/ip-address.php?ipid=<?php echo $row->id; ?>"><?php echo $row->rdns; ?></a>
                </td>
                <td><?php

                    if ($total_domains >= 1) { ?>

                        <a href="../domains/index.php?ipid=<?php echo $row->id; ?>"><?php echo number_format($total_domains); ?></a><?php

                    } else {

                        echo "-";

                    } ?>

                </td>
                <td><?php

                    if ($total_certs >= 1) { ?>

                        <a href="../ssl/index.php?sslipid=<?php echo $row->id; ?>"><?php echo number_format($total_certs); ?></a><?php

                    } else {

                        echo "-";

                    } ?>

                </td>
                </tr><?php

            }

        } ?>

        </tbody>
    </table>

    <strong>*</strong> = Default Domain Owner &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>^</strong> = Default SSL Owner (<a href="../settings/defaults/">set defaults</a>)<BR><BR><?php

} else { ?>

    <BR>You don't currently have any IP Addresses. <a href="add/ip-address.php">Click here to add one</a>.<?php

} ?>
<?php include(DIR_INC . "layout/asset-footer.inc.php"); ?>
<?php include(DIR_INC . "layout/footer.inc.php"); //@formatter:on ?>
</body>
</html>
