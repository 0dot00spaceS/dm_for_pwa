<?php
/**
 * /classes/DomainMOD/Fabulous.php
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
//@formatter:off
namespace DomainMOD;

class Fabulous
{

    public function getApiKey($connection, $account_id)
    {
        $error = new Error();
        $sql = "SELECT username, `password`
                FROM registrar_accounts
                WHERE id = '" . $account_id . "'
                LIMIT 1";
        $result = mysqli_query($connection, $sql) or $error->outputOldSqlError($connection);

        if (mysqli_num_rows($result) > 0) {

            while ($row = mysqli_fetch_object($result)) {

                $account_username = $row->username;
                $account_password = $row->password;

            }

        } else {

            echo "No API Credentials Found";
            exit;

        }

        return array($account_username, $account_password);
    }

    public function getApiUrl($account_username, $account_password, $domain, $command)
    {
        $base_url = 'https://api.fabulous.com/';
        if ($command == 'domainlist') {
            return $base_url . 'listDomains?username=' . $account_username . '&password=' . $account_password;
        } elseif ($command == 'info') {
            return $base_url . 'domainInfo?username=' . $account_username . '&password=' . $account_password . '&domain=' . $domain;
        } else {
            return 'Unable to build API URL';
        }
    }

    public function apiCall($full_url)
    {
        $handle = curl_init($full_url);
        curl_setopt( $handle, CURLOPT_RETURNTRANSFER, TRUE );
        $result = curl_exec($handle);
        curl_close($handle);
        return $result;
    }

    public function getDomainList($account_username, $account_password)
    {
        $api_url = $this->getApiUrl($account_username, $account_password, '', 'domainlist');
        $api_results = $this->apiCall($api_url);
        $array_results = $this->convertToArray($api_results);

        // confirm that the api call was successful
        if ($array_results[0]['response']['reason'] == 'Success') {

            $domain_list = array();
            $domain_count = 0;

            foreach ($array_results[0]['response']['results']['result']['values']['value'] AS $domain) {

                $domain_list[] = $domain;
                $domain_count++;

            }

        } else {

            // if the API call failed assign empty values
            $domain_list = '';
            $domain_count = '';

        }

        return array($domain_count, $domain_list);
    }

    public function getFullInfo($account_username, $account_password, $domain)
    {
        $api_url = $this->getApiUrl($account_username, $account_password, $domain, 'info');
        $api_results = $this->apiCall($api_url);
        $array_results = $this->convertToArray($api_results);

        // confirm that the api call was successful
        if ($array_results[0]['response']['reason'] == 'Success') {

            // get expiration date
            $expiration_date = $array_results[0]['response']['results']['result']['expiry'];

            // get dns servers
            $dns_result = $array_results[0]['response']['results']['result']['nameservers']['nameserver'];
            $dns_servers = $this->processDns($dns_result);

            // get privacy status
            // Fabulous doesn't expose the privacy status with their API, so this information can't be retrieved yet
            $privacy_status = '0';

            // get auto renewal status
            // Fabulous doesn't expose the auto renewal status with their API, so this information can't be retrieved yet
            $autorenewal_status = '0';

        } else {

            // if the API call failed assign empty values
            $expiration_date = '';
            $dns_servers = '';
            $privacy_status = '';
            $autorenewal_status = '';

        }

        return array($expiration_date, $dns_servers, $privacy_status, $autorenewal_status);
    }

    public function convertToArray($api_result)
    {
        $xml = simplexml_load_string($api_result);
        $json = json_encode((array($xml)), TRUE);
        return json_decode($json, TRUE);
    }

    public function processDns($dns_result)
    {
        if (!empty($dns_result)) {
            $dns_servers = array_filter($dns_result);
        } else {
            $dns_servers[0] = 'no.dns-servers.1';
            $dns_servers[1] = 'no.dns-servers.2';
        }
        return $dns_servers;
    }

} //@formatter:on
