<?php
/**
 * /assets/add/ssl-provider.php
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
<?php
include("../../_includes/start-session.inc.php");
include("../../_includes/init.inc.php");

require_once(DIR_ROOT . "classes/Autoloader.php");
spl_autoload_register('DomainMOD\Autoloader::classAutoloader');

$system = new DomainMOD\System();
$error = new DomainMOD\Error();
$time = new DomainMOD\Time();
$form = new DomainMOD\Form();

include(DIR_INC . "head.inc.php");
include(DIR_INC . "config.inc.php");
include(DIR_INC . "software.inc.php");
include(DIR_INC . "settings/assets-add-ssl-provider.inc.php");
include(DIR_INC . "database.inc.php");

$system->authCheck();

$new_ssl_provider = $_POST['new_ssl_provider'];
$new_url = $_POST['new_url'];
$new_notes = $_POST['new_notes'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($new_ssl_provider != "") {

        $query = "INSERT INTO ssl_providers
                  (`name`, url, notes, created_by, insert_time)
                  VALUES
                  (?, ?, ?, ?, ?)";
        $q = $conn->stmt_init();

        if ($q->prepare($query)) {

            $timestamp = $time->stamp();

            $q->bind_param('sssis', $new_ssl_provider, $new_url, $new_notes, $_SESSION['s_user_id'], $timestamp);
            $q->execute();
            $q->close();

        } else {
            $error->outputSqlError($conn, "ERROR");
        }

        $_SESSION['s_message_success'] .= 'SSL Provider ' . $new_ssl_provider . ' Added<BR>';

        if ($_SESSION['s_has_ssl_provider'] != '1') {

            $system->checkExistingAssets($connection);

            header("Location: ../../ssl/index.php");

        } else {

            header("Location: ../ssl-providers.php");

        }
        exit;

    } else {

        if ($new_ssl_provider == "") $_SESSION['s_message_danger'] .= "Enter the SSL provider's name<BR>";

    }

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
echo $form->showFormTop('');
echo $form->showInputText('new_ssl_provider', 'SSL Provider Name (100)', '', $new_ssl_provider, '100', '', '1', '', '');
echo $form->showInputText('new_url', 'SSL Provider\'s URL', '', $new_url, '100', '', '', '', '');
echo $form->showInputTextarea('new_notes', 'Notes', '', $new_notes, '', '', '');
echo $form->showSubmitButton('Add SSL Provider', '', '');
echo $form->showFormBottom('');
?>
<?php include(DIR_INC . "layout/footer.inc.php"); ?>
</body>
</html>
