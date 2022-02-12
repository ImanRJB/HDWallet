<?php

if ( ! function_exists('hexToAddress') )
{
    function hexToAddress($hex) {
    $address = hex2bin($hex);
    $hash0 = hash("sha256", $address);
    $hash1 = hash("sha256", hex2bin($hash0));
    $checksum = substr($hash1, 0, 8);
    $string = $address.hex2bin($checksum);

    $alphabet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    $base = strlen($alphabet);
    if (is_string($string) === false) {
        return false;
    }
    if (strlen($string) === 0) {
        return '';
    }
    $bytes = array_values(unpack('C*', $string));
    $decimal = $bytes[0];
    for ($i = 1, $l = count($bytes); $i < $l; $i++) {
        $decimal = bcmul($decimal, 256);
        $decimal = bcadd($decimal, $bytes[$i]);
    }
    $output = '';
    while ($decimal >= $base) {
        $div = bcdiv($decimal, $base, 0);
        $mod = bcmod($decimal, $base);
        $output .= $alphabet[$mod];
        $decimal = $div;
    }
    if ($decimal > 0) {
        $output .= $alphabet[$decimal];
    }
    $output = strrev($output);
    foreach ($bytes as $byte) {
        if ($byte === 0) {
            $output = $alphabet[0] . $output;
            continue;
        }
        break;
    }
    return $output;
}
}