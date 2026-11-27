<?php

namespace App\Support;

/**
 * Encodage geohash (base 32), sans dépendance externe.
 *
 * Le geohash sert de repli quand les polygones de quartiers ne sont pas
 * encore disponibles : deux points partageant un préfixe de 7 caractères
 * (~150 m) sont considérés comme « dans le même secteur ».
 */
class Geohash
{
    private const BASE32 = '0123456789bcdefghjkmnpqrstuvwxyz';

    public static function encode(float $latitude, float $longitude, int $precision = 7): string
    {
        $precision = max(1, min(12, $precision));

        $latMin = -90.0;
        $latMax = 90.0;
        $lngMin = -180.0;
        $lngMax = 180.0;

        $hash = '';
        $bits = 0;
        $bit = 0;
        $even = true;

        while (strlen($hash) < $precision) {
            if ($even) {
                $mid = ($lngMin + $lngMax) / 2;

                if ($longitude >= $mid) {
                    $bit |= 1 << (4 - $bits);
                    $lngMin = $mid;
                } else {
                    $lngMax = $mid;
                }
            } else {
                $mid = ($latMin + $latMax) / 2;

                if ($latitude >= $mid) {
                    $bit |= 1 << (4 - $bits);
                    $latMin = $mid;
                } else {
                    $latMax = $mid;
                }
            }

            $even = ! $even;
            $bits++;

            if ($bits === 5) {
                $hash .= self::BASE32[$bit];
                $bits = 0;
                $bit = 0;
            }
        }

        return $hash;
    }
}
