<?php
/**
 * /_includes/updates/4.02.000-current.inc.php
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

// upgrade database from 4.02.000 to 4.02.001
if ($current_db_version === '4.02.000') {

    $sql = "UPDATE currencies
            SET symbol = '₺'
            WHERE currency = 'TRY'";
    $result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

    $sql = "ALTER TABLE `settings`
            CHANGE `smtp_port` `smtp_port` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '587'";
    $result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

    $sql = "UPDATE settings
            SET db_version = '4.02.001',
                update_time = '" . $time->stamp() . "'";
    $result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

    $current_db_version = '4.02.001';

}

// upgrade database from 4.02.001 to 4.03.000
if ($current_db_version === '4.02.001') {

    $sql = "UPDATE scheduler
            SET description = '" . "<" . "em>Domains:" . "<" . "/em> Converts all domain entries to lowercase." . "<" . "BR>" . "<" . "BR> " . "<" . "em>TLDs:" . "<" . "/em> Updates all TLD entries to ensure their accuracy." . "<" . "BR>" . "<" . "BR> " . "<" . "em>Segments:" . "<" . "/em> Compares the Segment data to the domain database and records the status of each domain. This keeps the Segment filtering data up-to-date and running smoothly." . "<" . "BR>" . "<" . "BR>" . "<" . "em>Fees:" . "<" . "/em> Cross-references the Domain, SSL Certificate, and fee tables, making sure that everything is accurate. It also deletes all unused fees.'
            WHERE `name` = 'System Cleanup'";
    $result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

    $sql = "UPDATE settings
            SET db_version = '4.03.000',
                update_time = '" . $time->stamp() . "'";
    $result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

    $current_db_version = '4.03.000';

}

// upgrade database from 4.03.000 to 4.03.001
if ($current_db_version === '4.03.000') {

    $sql = "UPDATE api_registrars
            SET ret_privacy_status = '1',
                ret_autorenewal_status = '1',
                notes = ''
            WHERE `name` = 'Fabulous'";
    $result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

    $sql = "INSERT INTO api_registrars
            (`name`, req_account_username, req_account_password, req_reseller_id, req_api_app_name, req_api_key,
             req_api_secret, req_ip_address, lists_domains, ret_expiry_date, ret_dns_servers, ret_privacy_status,
             ret_autorenewal_status, notes, insert_time)
             VALUES
            ('Freenom', '1', '1', '0', '0', '0', '0', '0', '1', '1', '1', '1', '1', 'Freenom currently only gives API access to reseller accounts.', '" . $time->stamp() . "')";
    $result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

}

//@formatter:on