<?php
/**
 * /classes/DomainMOD/Currency.php
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
namespace DomainMOD;

class Currency
{

    public function format($amount, $symbol, $order, $space)
    {
        if ($order == "1" && $space == "1") {
            $formatted_output = number_format($amount, 2, '.', ',') . " " . $symbol;
        } elseif ($order == "1" && $space == "0") {
            $formatted_output = number_format($amount, 2, '.', ',') . $symbol;
        } elseif ($order == "0" && $space == "1") {
            $formatted_output = $symbol . " " . number_format($amount, 2, '.', ',');
        } else {
            $formatted_output = $symbol . number_format($amount, 2, '.', ',');
        }

        return $formatted_output;
    }

}
