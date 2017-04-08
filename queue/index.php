<?php
/**
 * /queue/index.php
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
include("../_includes/start-session.inc.php");
include("../_includes/init.inc.php");

require_once(DIR_ROOT . "classes/Autoloader.php");
spl_autoload_register('DomainMOD\Autoloader::classAutoloader');

$system = new DomainMOD\System();
$error = new DomainMOD\Error();
$queue = new DomainMOD\DomainQueue();
$layout = new DomainMOD\Layout();
$time = new DomainMOD\Time();

include(DIR_INC . "head.inc.php");
include(DIR_INC . "config.inc.php");
include(DIR_INC . "software.inc.php");
include(DIR_INC . "settings/queue-main.inc.php");
include(DIR_INC . "database.inc.php");

$system->authCheck($web_root);

$list_id = $_GET['list_id'];
$dell = $_GET['dell'];
$really_dell = $_GET['really_dell'];

$domain_id = $_GET['domain_id'];
$deld = $_GET['deld'];
$really_deld = $_GET['really_deld'];

$clear = $_GET['clear'];
$really_clear = $_GET['really_clear'];
$s = $_GET['s'];
$export_data = $_GET['export_data'];

$sql_lists = "SELECT dql.id, dql.api_registrar_id, dql.domain_count, dql.owner_id, dql.registrar_id, dql.account_id,
                  dql.processing, dql.ready_to_import, dql.finished, dql.copied_to_history, dql.created_by,
                  dql.insert_time, r.name AS registrar_name, ra.username AS username, o.name AS owner, ar.name AS api_registrar_name
              FROM domain_queue_list AS dql, registrars AS r, registrar_accounts AS ra, owners AS o, api_registrars AS ar
              WHERE dql.registrar_id = r.id
                AND dql.account_id = ra.id
                AND dql.owner_id = o.id
                AND dql.api_registrar_id = ar.id
              ORDER BY dql.insert_time DESC";
$result_lists = mysqli_query($connection, $sql_lists);

$sql_domains = "SELECT dq.id, dq.api_registrar_id, dq.domain_id, dq.owner_id, dq.registrar_id, dq.account_id, dq.domain,
                    dq.tld, dq.expiry_date, dq.cat_id, dq.dns_id, dq.ip_id, dq.hosting_id, dq.autorenew, dq.privacy,
                    dq.processing, dq.ready_to_import, dq.finished, dq.already_in_domains, dq.already_in_queue,
                    dq.copied_to_history, dq.created_by, dq.insert_time, r.name AS registrar_name,
                    ra.username AS username, o.name AS owner, ar.name AS api_registrar_name
                FROM domain_queue AS dq, registrars AS r, registrar_accounts AS ra, owners AS o, api_registrars AS ar
                WHERE dq.registrar_id = r.id
                  AND dq.account_id = ra.id
                  AND dq.owner_id = o.id
                  AND dq.api_registrar_id = ar.id
                ORDER BY dq.already_in_domains ASC, dq.already_in_queue ASC, dq.insert_time DESC";
$result_domains = mysqli_query($connection, $sql_domains);

if ($export_data == '1') {

    if ($s == 'lists') {

        // list queue
        $export = new DomainMOD\Export();
        $export_file = $export->openFile('domain_list_queue', strtotime($time->stamp()));

        $row_contents = array('Domain List Queue');
        $export->writeRow($export_file, $row_contents);

        $export->writeBlankRow($export_file);

        $row_contents = array(
            'Status',
            'Registrar (API)',
            'Registrar (Name)',
            'Account Owner',
            'Account Username',
            'Domain Count',
            'Ready To Import',
            'Copied To History',
            'Added By',
            'Inserted'
        );
        $export->writeRow($export_file, $row_contents);

        if (mysqli_num_rows($result_lists) > 0) {

            while ($row_lists = mysqli_fetch_object($result_lists)) {

                if ($row_lists->finished == '1') {

                    $export_processing = 'Domains Added To Queue';

                } else {

                    if ($row_lists->processing == '1') {

                        $export_processing = 'Processing';

                    } else {

                        $export_processing = 'Pending';

                    }

                }

                $sql_temp = "SELECT first_name, last_name
                             FROM users
                             WHERE id = '" . $row_lists->created_by . "'";
                $result_temp = mysqli_query($connection, $sql_temp);

                while ($row_temp = mysqli_fetch_object($result_temp)) {

                    $full_name_export = $row_temp->first_name . ' ' . $row_temp->last_name;

                }

                $row_contents = array(
                    $export_processing,
                    $row_lists->api_registrar_name,
                    $row_lists->registrar_name,
                    $row_lists->owner,
                    $row_lists->username,
                    $row_lists->domain_count,
                    $row_lists->ready_to_import,
                    $row_lists->copied_to_history,
                    $full_name_export,
                    $time->toUserTimezone($row_lists->insert_time)
                );
                $export->writeRow($export_file, $row_contents);

            }

        }

    } elseif ($s == 'domains') {

        // domain queue
        $export = new DomainMOD\Export();
        $export_file = $export->openFile('domain_queue', strtotime($time->stamp()));

        $row_contents = array('Domain Queue');
        $export->writeRow($export_file, $row_contents);

        $export->writeBlankRow($export_file);

        $row_contents = array(
            'Status',
            'Domain',
            'Registrar (API)',
            'Registrar (Name)',
            'Account Owner',
            'Account Username',
            'TLD',
            'Expiry Date',
            'DNS Profile',
            'IP Address (Name)',
            'IP Address (IP)',
            'Web Host',
            'Category',
            'Auto Renew',
            'Privacy',
            'Ready To Import',
            'Already in Domains',
            'Already in Queue',
            'Copied To History',
            'Added By',
            'Inserted',
            'Domain ID'
        );
        $export->writeRow($export_file, $row_contents);

        if (mysqli_num_rows($result_domains) > 0) {

            while ($row_domains = mysqli_fetch_object($result_domains)) {

                $already_exists = '';
                if ($row_domains->finished == '1' && $row_domains->processing != '1') {

                    if ($row_domains->already_in_domains == '1') {

                        $export_processing = 'Already in DomainMOD';
                        $already_exists = '1';

                    } elseif ($row_domains->already_in_queue == '1') {

                        $export_processing = 'Already in Domain Queue';
                        $already_exists = '1';

                    } else {

                        $export_processing = 'Successfully Imported';

                    }

                } else {

                    if ($row_domains->processing == '1') {

                        $export_processing = 'Processing';

                    } else {

                        $export_processing = 'Pending';

                    }

                }

                if ($row_domains->expiry_date == '0000-00-00') {

                    if ($already_exists == '1') {

                        $export_expiry_date = '-';

                    } else {

                        $export_expiry_date = "Pending";

                    }

                } else {

                    $export_expiry_date = $row_domains->expiry_date;

                }

                if ($row_domains->dns_id == '0') {

                    if ($already_exists == '1') {

                        $export_dns = '-';

                    } else {

                        $export_dns = "Pending";

                    }

                } else {

                    $sql_temp = "SELECT `name`
                                 FROM dns
                                 WHERE id = '" . $row_domains->dns_id . "'";
                    $result_temp = mysqli_query($connection, $sql_temp);

                    while ($row_temp = mysqli_fetch_object($result_temp)) {

                        $export_dns = $row_temp->name;

                    }

                }

                if ($row_domains->ip_id == '0') {

                    if ($already_exists == '1') {

                        $export_ip_name = '-';
                        $export_ip_address = '-';

                    } else {

                        $export_ip_name = 'Pending';
                        $export_ip_address = 'Pending';

                    }

                } else {

                    $sql_temp = "SELECT `name`, ip
                                 FROM ip_addresses
                                 WHERE id = '" . $row_domains->ip_id . "'";
                    $result_temp = mysqli_query($connection, $sql_temp);

                    while ($row_temp = mysqli_fetch_object($result_temp)) {

                        $export_ip_name = $row_temp->name;
                        $export_ip_address = $row_temp->ip;

                    }
                }

                if ($row_domains->hosting_id == '0') {

                    if ($already_exists == '1') {

                        $export_host = '-';

                    } else {

                        $export_host = 'Pending';

                    }

                } else {

                    $sql_temp = "SELECT `name`
                                 FROM hosting
                                 WHERE id = '" . $row_domains->hosting_id . "'";
                    $result_temp = mysqli_query($connection, $sql_temp);

                    while ($row_temp = mysqli_fetch_object($result_temp)) {

                        $export_host = $row_temp->name;

                    }

                }

                if ($row_domains->cat_id == '0') {

                    if ($already_exists == '1') {

                        $export_category = '-';

                    } else {

                        $export_category = 'Pending';

                    }

                } else {

                    $sql_temp = "SELECT `name`
                                 FROM categories
                                 WHERE id = '" . $row_domains->cat_id . "'";
                    $result_temp = mysqli_query($connection, $sql_temp);

                    while ($row_temp = mysqli_fetch_object($result_temp)) {

                        $export_category = $row_temp->name;

                    }

                }

                if ($row_domains->autorenew == '1') {

                    $export_autorenew = 'Yes';

                } else {

                    if ($row_domains->finished == '1') {

                        $export_autorenew = 'No';

                    } else {

                        $export_autorenew = 'Pending';

                    }

                }

                if ($row_domains->privacy == '1') {

                    $export_privacy = 'Yes';

                } else {

                    if ($row_domains->finished == '1') {

                        $export_privacy = 'No';

                    } else {

                        $export_privacy = 'Pending';

                    }

                }

                $sql_temp = "SELECT `username`
                             FROM registrar_accounts
                             WHERE id = '" . $row_domains->account_id . "'";
                $result_temp = mysqli_query($connection, $sql_temp);

                while ($row_temp = mysqli_fetch_object($result_temp)) {

                    $account_export = $row_temp->username;

                }

                if ($row_domains->created_by == '0') {

                    $full_name_export = '[unknown]';

                } else {

                    $sql_temp = "SELECT first_name, last_name
                                 FROM users
                                 WHERE id = '" . $row_domains->created_by . "'";
                    $result_temp = mysqli_query($connection, $sql_temp);

                    while ($row_temp = mysqli_fetch_object($result_temp)) {

                        $full_name_export = $row_temp->first_name . ' ' . $row_temp->last_name;

                    }

                }

                $row_contents = array(
                    $export_processing,
                    $row_domains->domain,
                    $row_domains->api_registrar_name,
                    $row_domains->registrar_name,
                    $row_domains->owner,
                    $account_export,
                    $row_domains->tld,
                    $export_expiry_date,
                    $export_dns,
                    $export_ip_name,
                    $export_ip_address,
                    $export_host,
                    $export_category,
                    $export_autorenew,
                    $export_privacy,
                    $row_domains->ready_to_import,
                    $row_domains->already_in_domains,
                    $row_domains->already_in_queue,
                    $row_domains->copied_to_history,
                    $full_name_export,
                    $time->toUserTimezone($row_domains->insert_time),
                    $row_domains->domain_id
                );
                $export->writeRow($export_file, $row_contents);

            }

        }

    }

    $export->closeFile($export_file);

}

if ($clear == "1") {

    $_SESSION['s_message_danger'] .= "Are you sure you want to clear completed items from the queue?<BR><BR>Before clearing the queue you should review the results to make sure that everything is correct.<BR><BR><a href=\"index.php?really_clear=1\">YES, REALLY CLEAR COMPLETED ITEMS FROM THE QUEUE</a><BR>";

}

if ($really_clear == "1") {

    $queue->clearFinished($connection);

    $_SESSION['s_message_success'] .= "Completed items cleared from the queue<BR>";

}

if ($dell != '' && $list_id != '') {

    $_SESSION['s_message_danger'] .= "Are you sure you want to delete this Domain List from the Queue?<BR><BR><a href=\"index.php?really_dell=1&list_id=" . $list_id . "\">YES, REALLY DELETE THIS DOMAIN LIST FROM THE QUEUE</a><BR>";

}

if ($really_dell == '1' && $list_id != '') {

    $query = "DELETE FROM domain_queue_list
              WHERE id = ?";
    $q = $conn->stmt_init();

    if ($q->prepare($query)) {

        $q->bind_param('i', $list_id);
        $q->execute();
        $q->close();

    } else $error->outputSqlError($conn, "ERROR");

    $queue->checkListQueue($connection);

    $_SESSION['s_message_success'] .= "Domain List deleted from Queue<BR>";

}

if ($deld != '' && $domain_id != '') {

    $_SESSION['s_message_danger'] .= "Are you sure you want to delete this Domain from the Queue?<BR><BR><a href=\"index.php?really_deld=1&domain_id=" . $domain_id . "\">YES, REALLY DELETE THIS DOMAIN FROM THE QUEUE</a><BR>";

}

if ($really_deld == '1' && $domain_id != '') {

    $query = "DELETE FROM domain_queue
              WHERE id = ?";
    $q = $conn->stmt_init();

    if ($q->prepare($query)) {

        $q->bind_param('i', $domain_id);
        $q->execute();
        $q->close();

    } else $error->outputSqlError($conn, "ERROR");

    $queue->checkDomainQueue($connection);

    $_SESSION['s_message_success'] .= "Domain deleted from Queue<BR>";

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
<?php
$queue->checkProcessingLists($connection);
$queue->checkProcessingDomains($connection);
if ($_SESSION['s_list_queue_processing'] == '1' || $_SESSION['s_domain_queue_processing'] == '1') { ?>

    <button type="button" class="btn btn-default btn-lrg">
        <i class="fa fa-spin fa-refresh"></i>&nbsp;&nbsp;&nbsp;The Queue Is Currently Processing
    </button><BR><BR><?php

} ?>
The Domain Queue relies on your domain registrar's API to import your domains, so they must have an API and support for it must be built into <?php echo $software_title; ?>. For more information please see the <a href="info.php">Domain Queue information page</a>.<BR>
<BR>
<a href="<?php echo $web_root; ?>/queue/add.php"><?php echo $layout->showButton('button', 'Add Domains To Queue'); ?></a>&nbsp;&nbsp;&nbsp;
<?php if ($_SESSION['s_domains_in_queue'] == '1') { ?>
<a href="index.php?clear=1"><?php echo $layout->showButton('button', 'Clear Completed'); ?></a>&nbsp;&nbsp;&nbsp;
<?php } ?>
<a href="info.php"><?php echo $layout->showButton('button', 'More Info'); ?></a>&nbsp;&nbsp;&nbsp;
<?php if ($_SESSION['s_domains_in_list_queue'] == '1') { ?>
<a href="index.php?s=lists&export_data=1"><?php echo $layout->showButton('button', 'Export Lists'); ?></a>&nbsp;&nbsp;&nbsp;
<?php } ?>
<?php if ($_SESSION['s_domains_in_queue'] == '1') { ?>
<a href="index.php?s=domains&export_data=1"><?php echo $layout->showButton('button', 'Export Domains'); ?></a>&nbsp;&nbsp;&nbsp;
<?php } ?>
<BR><BR>
<?php
$result_lists = mysqli_query($connection, $sql_lists);

if (mysqli_num_rows($result_lists) == 0) {

    unset($_SESSION['s_domains_in_list_queue']);

} else { ?>

    <h3>Domain List Queue</h3>
    <table id="<?php echo $slug; ?>-lists" class="<?php echo $datatable_class; ?>">
        <thead>
        <tr>
            <th width="20px"></th>
            <th>Status</th>
            <th>Registrar Account</th>
            <th>Domain Count</th>
            <th>Added By</th>
            <th>Added</th>
            <th>Delete</th>
        </tr>
        </thead>
        <tbody><?php

        while ($row_lists = mysqli_fetch_object($result_lists)) { ?>

            <tr>
            <td></td>
            <td>
                <?php
                if ($row_lists->finished == '1') {

                    echo 'Domains Added To Queue';

                } else {

                    if ($row_lists->processing == '1') {

                        echo 'Processing';

                    } else {

                        echo 'Pending';

                    }

                } ?>
            </td>
            <td>
                <?php echo $row_lists->registrar_name; ?>, <?php echo $row_lists->owner; ?>
                (<?php echo $row_lists->username; ?>)
            </td>
            <td>
                <?php
                if ($row_lists->domain_count == '0') {

                    $to_display = 'Pending';

                } else {

                    $to_display = $row_lists->domain_count;

                }
                echo $to_display; ?>
            </td>
            <td><?php
                if ($row_lists->created_by == '0') {

                    $to_display = '[unknown]';

                } else {

                    $query = "SELECT first_name, last_name
                              FROM users
                              WHERE id = ?";
                    $q = $conn->stmt_init();

                    if ($q->prepare($query)) {

                        $q->bind_param('i', $row_lists->created_by);
                        $q->execute();
                        $q->store_result();
                        $q->bind_result($t_first_name, $t_last_name);

                        while ($q->fetch()) {

                            $to_display = $t_first_name . ' ' . $t_last_name;

                        }

                        $q->close();

                    } else $error->outputSqlError($conn, "ERROR");

                }
                echo $to_display; ?>
            </td>
            <td><?php

                if ($row_lists->insert_time != '0000-00-00 00:00:00') {

                    $to_display = $time->toUserTimezone($row_lists->insert_time);

                } else {

                    $to_display = '[unknown]';

                }
                echo $to_display; ?>
            </td>
            <td>
                <a href="index.php?dell=1&list_id=<?php echo $row_lists->id; ?>"><i class="fa fa-times" style="padding-top: 3px;"></i></a>
            </td>
            </tr><?php

        } ?>

        </tbody>
    </table><?php
}

$result_domains = mysqli_query($connection, $sql_domains);

if (mysqli_num_rows($result_domains) == 0) {

    unset($_SESSION['s_domains_in_queue']);

} else { ?>

    <h3>Domain Queue</h3>
    <table id="<?php echo $slug; ?>-domains" class="<?php echo $datatable_class; ?>">
        <thead>
        <tr>
            <th width="20px"></th>
            <th>Status</th>
            <th>Domain</th>
            <th>Registrar Account</th>
            <th>Expiry Date</th>
            <th>DNS</th>
            <th>IP</th>
            <th>Added By</th>
            <th>Added</th>
            <th>Delete</th>
        </tr>
        </thead>
        <tbody><?php

        while ($row_domains = mysqli_fetch_object($result_domains)) { ?>

            <tr>
            <td></td>
            <td>
                <?php
                $already_exists = '';
                if ($row_domains->finished == '1' && $row_domains->processing != '1') {

                    if ($row_domains->already_in_domains == '1') {

                        echo $layout->highlightText('failure', 'Already in DomainMOD');
                        $already_exists = '1';

                    } elseif ($row_domains->already_in_queue == '1') {

                        echo $layout->highlightText('failure', 'Already in Domain Queue');
                        $already_exists = '1';

                    } else {

                        echo $layout->highlightText('success', 'Successfully Imported');

                    }

                } else {

                    if ($row_domains->processing == '1') {

                        echo 'Processing';

                    } else {

                        echo 'Pending';

                    }

                } ?>
            </td>
            <td>
                <?php echo $row_domains->domain; ?>
            </td>
            <td>
                <?php echo $row_domains->registrar_name; ?>, <?php echo $row_domains->owner; ?>
                (<?php echo $row_domains->username; ?>)
            </td>
            <td><?php
                if ($row_domains->expiry_date == '0000-00-00') {

                    if ($already_exists == '1') {

                        $to_display = '-';

                    } else {

                        $to_display = "Pending";

                    }

                } else {

                    $to_display = $row_domains->expiry_date;

                }
                echo $to_display; ?>
            </td>
            <td><?php
                if ($row_domains->dns_id == '0') {

                    if ($already_exists == '1') {

                        $to_display = '-';

                    } else {

                        $to_display = "Pending";

                    }

                } else {

                    $query = "SELECT `name`
                              FROM dns
                              WHERE id = ?";
                    $q = $conn->stmt_init();

                    if ($q->prepare($query)) {

                        $q->bind_param('i', $row_domains->dns_id);
                        $q->execute();
                        $q->store_result();
                        $q->bind_result($t_name);

                        while ($q->fetch()) {

                            $to_display = $t_name;

                        }

                        $q->close();

                    } else $error->outputSqlError($conn, "ERROR");

                }
                echo $to_display; ?>
            </td>
            <td><?php
                if ($row_domains->ip_id == '0') {

                    if ($already_exists == '1') {

                        $to_display = '-';

                    } else {

                        $to_display = "Pending";

                    }

                } else {

                    $query = "SELECT `name`
                              FROM ip_addresses
                              WHERE id = ?";
                    $q = $conn->stmt_init();

                    if ($q->prepare($query)) {

                        $q->bind_param('i', $row_domains->ip_id);
                        $q->execute();
                        $q->store_result();
                        $q->bind_result($t_name);

                        while ($q->fetch()) {

                            $to_display = $t_name;

                        }

                        $q->close();

                    } else $error->outputSqlError($conn, "ERROR");

                }
                echo $to_display; ?>
            </td>
            <td><?php
                if ($row_domains->created_by == '0') {

                    $to_display = '[unknown]';

                } else {

                    $query = "SELECT first_name, last_name
                              FROM users
                              WHERE id = ?";
                    $q = $conn->stmt_init();

                    if ($q->prepare($query)) {

                        $q->bind_param('i', $row_domains->created_by);
                        $q->execute();
                        $q->store_result();
                        $q->bind_result($t_first_name, $t_last_name);

                        while ($q->fetch()) {

                            $to_display = $t_first_name . ' ' . $t_last_name;

                        }

                        $q->close();

                    } else $error->outputSqlError($conn, "ERROR");

                }
                echo $to_display; ?>
            </td>
            <td><?php
                if ($row_domains->insert_time != '0000-00-00 00:00:00') {

                    $to_display = $time->toUserTimezone($row_domains->insert_time);

                } else {

                    $to_display = '[unknown]';

                }
                echo $to_display; ?>
            </td>
            <td>
                <a href="index.php?deld=1&domain_id=<?php echo $row_domains->id; ?>"><i class="fa fa-times" style="padding-top: 3px;"></i></a>
            </td>
            </tr><?php

        } ?>

        </tbody>
    </table><?php
} ?>
<?php include(DIR_INC . "layout/footer.inc.php"); //@formatter:on ?>
</body>
</html>
