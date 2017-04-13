<?php
/**
 * /classes/DomainMOD/ResellerClub.php
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

class ResellerClub
{

    public function getApiUrl($reseller_id, $api_key, $command, $domain)
    {
        $base_url = 'https://httpapi.com/api/domains/details-by-name.json?auth-userid=' . $reseller_id . '&api-key=' . $api_key . '&domain-name=' . $domain;
        if ($command == 'info') {
            return $base_url . '&options=OrderDetails';
        } elseif ($command == 'dns') {
            return $base_url . '&options=NsDetails';
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

    public function getFullInfo($reseller_id, $api_key, $domain)
    {
        $api_url = $this->getApiUrl($reseller_id, $api_key, 'info', $domain);
        $api_results = $this->apiCall($api_url);
        $array_results = $this->convertToArray($api_results);

        // confirm that the api call was successful
        if (isset($array_results['domainname'])) {

            // get expiration date
            $expiry_result = $array_results['endtime'];
            $expiration_date = $this->processExpiry($expiry_result);

            // get privacy status
            $privacy_result = $array_results['isprivacyprotected'];
            $privacy_status = $this->processPrivacy($privacy_result);

            // get auto renewal status
            // ResellerClub only offers auto-renewal on one specific payment type, so they don't yet have the auto-renewal
            // status as retrievable information with the API
            $autorenewal_status = '0';

            // get dns servers
            $api_url = $this->getApiUrl($reseller_id, $api_key, 'dns', $domain);
            $api_results = $this->apiCall($api_url);
            $array_results = $this->convertToArray($api_results);

            $dns_list[0] = $array_results['ns1'];
            $dns_list[1] = $array_results['ns2'];
            $dns_list[2] = $array_results['ns3'];
            $dns_list[3] = $array_results['ns4'];
            $dns_list[4] = $array_results['ns5'];
            $dns_list[5] = $array_results['ns6'];
            $dns_list[6] = $array_results['ns7'];
            $dns_list[7] = $array_results['ns8'];
            $dns_list[8] = $array_results['ns9'];
            $dns_list[9] = $array_results['ns10'];

            $dns_servers = $this->processDns($dns_list);

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

    public function processExpiry($expiry_result)
    {
        return gmdate("Y-m-d", $expiry_result);
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
        if ($privacy_result == 'true') {
            $privacy_status = '1';
        } else {
            $privacy_status = '0';
        }
        return $privacy_status;
    }

} //@formatter:on
