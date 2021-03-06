<?php
/**
 * /notice.php
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
require_once('_includes/start-session.inc.php');
require_once('_includes/init.inc.php');

require_once(DIR_ROOT . 'classes/Autoloader.php');
spl_autoload_register('DomainMOD\Autoloader::classAutoloader');

$system = new DomainMOD\System();
$notice = new DomainMOD\Notice();

require_once(DIR_INC . 'head.inc.php');
require_once(DIR_INC . 'config.inc.php');
require_once(DIR_INC . 'software.inc.php');
require_once(DIR_INC . 'settings/system-notice.inc.php');
require_once(DIR_INC . 'database.inc.php');

$system->authCheck($web_root);

$action = $_GET['a'];

// u = Upgrade DomainMOD Database
if ($action = 'u') $notice->dbUpgrade($software_title);
?>
<?php require_once(DIR_INC . 'doctype.inc.php'); ?>
<html>
<head>
    <title><?php echo $system->pageTitle($software_title, $page_title); ?></title>
    <?php require_once(DIR_INC . 'layout/head-tags.inc.php'); ?>
</head>
<body class="hold-transition skin-red sidebar-mini">
<?php
$page_align = 'center';
require_once(DIR_INC . 'layout/header-bare.inc.php'); ?>
<?php
echo '<strong>' . $_SESSION['s_notice_page_title'] . '</strong><BR>';
echo $_SESSION['s_notice'];
?><BR><BR>
<?php require_once(DIR_INC . 'layout/footer-bare.inc.php'); ?>
</body>
</html>
