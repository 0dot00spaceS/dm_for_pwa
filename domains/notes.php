<?php
/**
 * /domains/notes.php
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
require_once('../_includes/start-session.inc.php');
require_once('../_includes/init.inc.php');

require_once(DIR_ROOT . 'classes/Autoloader.php');
spl_autoload_register('DomainMOD\Autoloader::classAutoloader');

$system = new DomainMOD\System();

require_once(DIR_INC . 'head.inc.php');
require_once(DIR_INC . 'config.inc.php');
require_once(DIR_INC . 'software.inc.php');
require_once(DIR_INC . 'database.inc.php');

$system->authCheck($web_root);

$did = (integer) $_GET['did'];

$query = "SELECT domain, notes
          FROM domains
          WHERE id = ?";
$q = $dbcon->stmt_init();

if ($q->prepare($query)) {

    $q->bind_param('i', $did);
    $q->execute();
    $q->store_result();
    $q->bind_result($domain, $notes);

    while ($q->fetch()) {

        $new_domain = $domain;
        $new_notes = $notes;

    }

    $q->close();

} else $error->outputSqlError($dbcon, '1', 'ERROR');

$page_title = "Domain Notes (" . $new_domain . ")";
$software_section = "domains";
?>
<?php require_once(DIR_INC . 'doctype.inc.php'); ?>
<html>
<head>
    <title><?php echo $system->pageTitle($software_title, $page_title); ?></title>
    <?php require_once(DIR_INC . 'layout/head-tags.inc.php'); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
<?php
$page_align = 'left';
require_once(DIR_INC . 'layout/header-bare.inc.php'); ?>
<strong>Notes For <?php echo $new_domain; ?></strong><BR>
<BR>
<?php
$format = new DomainMOD\Format();
echo $format->replaceBreaks($new_notes);
?>
<?php require_once(DIR_INC . 'layout/footer-bare.inc.php'); ?>
</body>
</html>
