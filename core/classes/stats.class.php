<?php

class Stats {
    static function getCountryCode ( $ipAddress ) {
        $ip2Country = new Ip2Country ( );
        $ip2Country->load ( $ipAddress );
        
        return $ip2Country->countryCode;
    }
}
