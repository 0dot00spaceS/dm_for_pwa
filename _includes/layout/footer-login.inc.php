<?php
/**
 * /_includes/layout/footer-login.inc.php
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
  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->
<?php
$full_filename = DIR_INC . "layout/footer.DEMO.inc.php";

if (file_exists($full_filename)) {

    include(DIR_INC . "layout/footer.DEMO.inc.php");

}
$_SESSION['s_redirect'] = $_SERVER["REQUEST_URI"];
?>
<!-- jQuery 2.2.0 -->
<script src="<?php echo $web_root . '/' . WEBROOT_THEME; ?>/plugins/jQuery/jQuery-2.2.0.min.js"></script>
<!-- Bootstrap 3.3.5 -->
<script src="<?php echo $web_root . '/' . WEBROOT_THEME; ?>/bootstrap/js/bootstrap.min.js"></script>
