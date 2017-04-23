<?php
/**
 * /reporting/ssl/provider-fees.php
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
$layout = new DomainMOD\Layout;
$time = new DomainMOD\Time();
$reporting = new DomainMOD\Reporting();
$currency = new DomainMOD\Currency();

require_once(DIR_INC . 'head.inc.php');
require_once(DIR_INC . 'config.inc.php');
require_once(DIR_INC . 'software.inc.php');
require_once(DIR_INC . 'settings/reporting-ssl-fees.inc.php');
require_once(DIR_INC . 'database.inc.php');

$system->authCheck($web_root);

$export_data = $_GET['export_data'];
$all = $_GET['all'];

if ($all == "1") {

    $sql = "SELECT sslp.id, sslp.name AS ssl_provider, sslt.id AS type_id, sslt.type, f.id AS fee_id, f.initial_fee,
                f.renewal_fee, f.misc_fee, f.insert_time, f.update_time, c.currency, c.symbol, c.symbol_order,
                c.symbol_space
            FROM ssl_providers AS sslp, ssl_fees AS f, currencies AS c, ssl_cert_types AS sslt
            WHERE sslp.id = f.ssl_provider_id
              AND f.currency_id = c.id
              AND f.type_id = sslt.id
            GROUP BY sslp.name, sslt.type
            ORDER BY sslp.name, sslt.type";

} else {

    $sql = "SELECT sslp.id, sslp.name AS ssl_provider, sslt.id AS type_id, sslt.type, f.id AS fee_id, f.initial_fee,
                f.renewal_fee, f.misc_fee, f.insert_time, f.update_time, c.currency, c.symbol, c.symbol_order,
                c.symbol_space
            FROM ssl_providers AS sslp, ssl_certs AS sslc, ssl_fees AS f, currencies AS c, ssl_cert_types AS sslt
            WHERE sslp.id = sslc.ssl_provider_id
              AND sslc.fee_id = f.id
              AND f.currency_id = c.id
              AND sslc.type_id = sslt.id
              AND sslc.active NOT IN ('0')
            GROUP BY sslp.name, sslt.type
            ORDER BY sslp.name, sslt.type";

}

$result = mysqli_query($dbcon, $sql) or $error->outputSqlError($dbcon, '1', 'ERROR');
$total_rows = mysqli_num_rows($result);

if ($total_rows > 0) {

    if ($export_data == "1") {

        $result = mysqli_query($dbcon, $sql) or $error->outputSqlError($dbcon, '1', 'ERROR');

        $export = new DomainMOD\Export();

        if ($all == "1") {

            $export_file = $export->openFile('ssl_provider_fee_report_all', strtotime($time->stamp()));

        } else {

            $export_file = $export->openFile('ssl_provider_fee_report_active', strtotime($time->stamp()));

        }

        $row_contents = array($page_title);
        $export->writeRow($export_file, $row_contents);

        $export->writeBlankRow($export_file);

        if ($all == "1") {

            $row_contents = array('All SSL Provider Fees');

        } else {

            $row_contents = array('Active SSL Provider Fees');

        }
        $export->writeRow($export_file, $row_contents);

        $export->writeBlankRow($export_file);

        $row_contents = array(
            'SSL Provider',
            'Certificate Type',
            'Initial Fee',
            'Renewal Fee',
            'Misc Fee',
            'Currency',
            'Certs',
            'Inserted',
            'Updated'
        );
        $export->writeRow($export_file, $row_contents);

        $new_ssl_provider = "";
        $last_ssl_provider = "";
        $new_type = "";
        $last_type = "";

        if (mysqli_num_rows($result) > 0) {

            while ($row = mysqli_fetch_object($result)) {

                $new_ssl_provider = $row->ssl_provider;
                $new_type = $row->type;

                $row->initial_fee = $currency->format($row->initial_fee, $row->symbol, $row->symbol_order,
                    $row->symbol_space);

                $row->renewal_fee = $currency->format($row->renewal_fee, $row->symbol, $row->symbol_order,
                    $row->symbol_space);

                $row->misc_fee = $currency->format($row->misc_fee, $row->symbol, $row->symbol_order,
                    $row->symbol_space);

                unset($row_contents);
                $count = 0;

                $row_contents[$count++] = $row->ssl_provider;
                $row_contents[$count++] = $row->type;
                $row_contents[$count++] = $row->initial_fee;
                $row_contents[$count++] = $row->renewal_fee;
                $row_contents[$count++] = $row->misc_fee;
                $row_contents[$count++] = $row->currency;

                $sql_ssl_count = "SELECT count(*) AS total_ssl_count
                                  FROM ssl_certs
                                  WHERE ssl_provider_id = '" . $row->id . "'
                                    AND fee_id = '" . $row->fee_id . "'
                                    AND active NOT IN ('0')";
                $result_ssl_count = mysqli_query($dbcon, $sql_ssl_count);

                while ($row_ssl_count = mysqli_fetch_object($result_ssl_count)) {

                    $row_contents[$count++] = $row_ssl_count->total_ssl_count;

                }

                $row_contents[$count++] = $time->toUserTimezone($row->insert_time);
                $row_contents[$count++] = $time->toUserTimezone($row->update_time);
                $export->writeRow($export_file, $row_contents);

                $last_ssl_provider = $row->ssl_provider;
                $last_type = $row->type;

            }

        }
        $export->closeFile($export_file);

    }

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
<BR>
<a href="provider-fees.php?all=1"><?php echo $layout->showButton('button', 'View All'); ?></a>&nbsp;&nbsp;or&nbsp;<a href="provider-fees.php?all=0"><?php echo $layout->showButton('button', 'Active Only'); ?></a>
<?php if ($total_rows > 0) { //@formatter:off ?>
          <BR><BR><a href="provider-fees.php?export_data=1&all=<?php echo urlencode($all); ?>"><?php echo $layout->showButton('button', 'Export'); ?></a>
<?php } //@formatter:on ?>

<?php if ($total_rows > 0) { ?>

    <table id="<?php echo $slug; ?>" class="<?php echo $datatable_class; ?>">
        <thead>
        <tr>
            <th width="20px"></th>
            <th>Provider</th>
            <th>Type</th>
            <th>Initial</th>
            <th>Renewal</th>
            <th>Misc</th>
            <th>Currency</th>
            <th>Certs</th>
            <th>Last Updated</th>
        </tr>
        </thead>
        <tbody><?php

        $new_ssl_provider = "";
        $last_ssl_provider = "";
        $new_type = "";
        $last_type = "";

        while ($row = mysqli_fetch_object($result)) {

            $new_ssl_provider = $row->ssl_provider;
            $new_type = $row->type;

            if ($row->update_time == "0000-00-00 00:00:00") {
                $row->update_time = $row->insert_time;
            }
            $last_updated = $time->toUserTimezone(date('Y-m-d', strtotime($row->update_time)));

            if ($new_ssl_provider != $last_ssl_provider || $new_ssl_provider == "") { ?>

                <tr>
                    <td></td>
                    <td>
                        <?php echo $row->ssl_provider; ?>
                    </td>
                    <td>
                        <?php echo $row->type; ?>
                    </td>
                    <td><?php
                        $row->initial_fee = $currency->format($row->initial_fee, $row->symbol, $row->symbol_order, $row->symbol_space);
                        echo $row->initial_fee; ?>
                    </td>
                    <td>
                        <?php
                        $row->renewal_fee = $currency->format($row->renewal_fee, $row->symbol, $row->symbol_order, $row->symbol_space);
                        echo $row->renewal_fee;
                        ?>
                    </td>
                    <td>
                        <?php
                        $row->misc_fee = $currency->format($row->misc_fee, $row->symbol, $row->symbol_order, $row->symbol_space);
                        echo $row->misc_fee;
                        ?>
                    </td>
                    <td><?php echo $row->currency; ?></td>
                    <td>
                        <?php
                        $sql_ssl_count = "SELECT count(*) AS total_ssl_count
                                      FROM ssl_certs
                                      WHERE ssl_provider_id = '" . $row->id . "'
                                        AND fee_id = '" . $row->fee_id . "'
                                        AND active NOT IN ('0')";
                        $result_ssl_count = mysqli_query($dbcon, $sql_ssl_count);
                        while ($row_ssl_count = mysqli_fetch_object($result_ssl_count)) {

                            if ($row_ssl_count->total_ssl_count == 0) {

                                echo "-";

                            } else {

                                echo "<a href=\"../../ssl/index.php?sslpid=" . $row->id .
                                    "&ssltid=" . $row->type_id . "\">" . $row_ssl_count->total_ssl_count . "</a>";

                            }

                        } ?>
                    </td>
                    <td><?php echo $last_updated; ?></td>
                </tr>

                <?php
                $last_ssl_provider = $row->ssl_provider;
                $last_type = $row->type;

            } else { ?>

                <tr>
                    <td></td>
                    <td>&nbsp;</td>
                    <td>
                        <?php echo $row->type; ?>
                    </td>
                    <td>
                        <?php
                        $row->initial_fee = $currency->format($row->initial_fee, $row->symbol, $row->symbol_order, $row->symbol_space);
                        echo $row->initial_fee;
                        ?>
                    </td>
                    <td>
                        <?php
                        $row->renewal_fee = $currency->format($row->renewal_fee, $row->symbol, $row->symbol_order, $row->symbol_space);
                        echo $row->renewal_fee;
                        ?>
                    </td>
                    <td>
                        <?php
                        $row->misc_fee = $currency->format($row->misc_fee, $row->symbol, $row->symbol_order, $row->symbol_space);
                        echo $row->misc_fee;
                        ?>
                    </td>
                    <td><?php echo $row->currency; ?></td>
                    <td>
                        <?php
                        $sql_ssl_count = "SELECT count(*) AS total_ssl_count
                                      FROM ssl_certs
                                      WHERE ssl_provider_id = '" . $row->id . "'
                                        AND fee_id = '" . $row->fee_id . "'
                                        AND active NOT IN ('0')";
                        $result_ssl_count = mysqli_query($dbcon, $sql_ssl_count);
                        while ($row_ssl_count = mysqli_fetch_object($result_ssl_count)) {

                            if ($row_ssl_count->total_ssl_count == 0) {

                                echo "-";

                            } else {

                                echo "<a href=\"../../ssl/index.php?sslpid=" . $row->id .
                                    "&ssltid=" . $row->type_id . "\">" . $row_ssl_count->total_ssl_count . "</a>";

                            }

                        } ?>
                    </td>
                    <td><?php echo $last_updated; ?></td>
                </tr><?php

                $last_ssl_provider = $row->ssl_provider;
                $last_type = $row->type;

            }

        } ?>

        </tbody>
    </table><?php

} ?>
<?php require_once(DIR_INC . 'layout/footer.inc.php'); ?>
</body>
</html>
