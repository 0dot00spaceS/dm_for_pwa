<?php
/**
 * /classes/DomainMOD/Reporting.php
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

class Reporting
{

    public function getRangeString($all, $column, $new_start_date, $new_end_date)
    {

        if ($all == '1') {

            $range_string = '';

        } else {

            $date = new Date();
            if (!$date->checkDateFormat($new_start_date)) $new_start_date = '1900-01-01';
            if (!$date->checkDateFormat($new_end_date)) $new_end_date = '2300-01-01';

            $range_string = " AND " . $column . " between '" . $new_start_date . "' AND '" . $new_end_date . "' ";

        }

        return $range_string;

    }

} //@formatter:on
