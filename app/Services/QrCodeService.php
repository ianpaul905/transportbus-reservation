<?php

namespace App\Services;

class QrCodeService
{
    public static function grille(string $reference, int $taille = 21): array
    {
        $hash = hash('sha256', $reference, true);
        $bits = '';

        for ($i = 0; $i < strlen($hash); $i++) {
            $bits .= str_pad(decbin(ord($hash[$i])), 8, '0', STR_PAD_LEFT);
        }

        $cases = [];

        for ($y = 0; $y < $taille; $y++) {
            for ($x = 0; $x < $taille; $x++) {
                $index = ($y * $taille + $x) % strlen($bits);
                $cases[$y][$x] = $bits[$index] === '1';
            }
        }

        foreach ([[0, 0], [0, $taille - 7], [$taille - 7, 0]] as [$oy, $ox]) {
            for ($y = 0; $y < 7; $y++) {
                for ($x = 0; $x < 7; $x++) {
                    $bord = $y === 0 || $y === 6 || $x === 0 || $x === 6;
                    $centre = $y >= 2 && $y <= 4 && $x >= 2 && $x <= 4;
                    $cases[$oy + $y][$ox + $x] = $bord || $centre;
                }
            }
        }

        return $cases;
    }
}
