<?php
/**
 * /classes/DomainMOD/GoDaddy.php
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
//@formatter:off
namespace DomainMOD;

class GoDaddy
{

    public function getApiUrl($domain, $command)
    {
        $base_url = 'https://api.godaddy.com/v1/';
        if ($command == 'domainlist') {
            return $base_url . 'domains?statusGroups=VISIBLE&limit=10000';
        } elseif ($command == 'info') {
            return $base_url . 'domains/' . $domain;
        } else {
            return 'Unable to build API URL';
        }
    }

    public function apiCall($api_key, $api_secret, $full_url)
    {
        $handle = curl_init($full_url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, array(
            'Authorization: sso-key ' . $api_key . ':' . $api_secret,
            'Accept: application/json'));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($handle);
        curl_close($handle);
        return $result;
    }

    public function getDomainList($api_key, $api_secret)
    {
        $api_url = $this->getApiUrl('', 'domainlist');
        $api_results = $this->apiCall($api_key, $api_secret, $api_url);
        $array_results = $this->convertToArray($api_results);

        // confirm that the api call was successful
        if (isset($array_results[0]['domain'])) {

            $domain_list = array();
            $domain_count = 0;

            foreach ($array_results AS $domain) {

                $domain_list[] = $domain['domain'];
                $domain_count++;

            }

        } else {

            // if the API call failed assign empty values
            $domain_list = '';
            $domain_count = '';

        }

        return array($domain_count, $domain_list);
    }

    public function getFullInfo($api_key, $api_secret, $domain)
    {
        $api_url = $this->getApiUrl($domain, 'info');
        $api_results = $this->apiCall($api_key, $api_secret, $api_url);
        $array_results = $this->convertToArray($api_results);

        // confirm that the api call was successful
        if (isset($array_results['domain'])) {

            // get expiration date
            $expiration_date = substr($array_results['expires'], 0, 10);

            // get dns servers
            $dns_result = $array_results['nameServers'];
            $dns_servers = $this->processDns($dns_result);

            // get privacy status
            $privacy_result = (string) $array_results['privacy'];
            $privacy_status = $this->processPrivacy($privacy_result);

            // get auto renewal status
            $autorenewal_result = (string) $array_results['renewAuto'];
            $autorenewal_status = $this->processAutorenew($autorenewal_result);

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
        return json_decode($api_result, TRUE);
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

    public function processPrivacy($privacy_result)
    {
        if ($privacy_result == '') {
            $privacy_status = '0';
        } else {
            $privacy_status = '1';
        }
        return $privacy_status;
    }

    public function processAutorenew($autorenewal_result)
    {
        if ($autorenewal_result == '') {
            $autorenewal_status = '0';
        } else {
            $autorenewal_status = '1';
        }
        return $autorenewal_status;
    }

} //@formatter:on
