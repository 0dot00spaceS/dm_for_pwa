<?php
/**
 * /_includes/layout/date-range-picker-footer.inc.php
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
<script type="text/javascript" src="<?php echo $web_root . '/' . WEBROOT_THEME; ?>/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="<?php echo $web_root . '/' . WEBROOT_THEME; ?>/plugins/daterangepicker/daterangepicker.js"></script>
<script>
    $('input[name="daterange"]').daterangepicker(
{
    locale: {
      format: 'YYYY-MM-DD'
    },
    startDate: '<?php echo $new_start_date; ?>',
    endDate: '<?php echo $new_end_date; ?>'
}
);
</script>
