<?php

namespace App;

use InvalidArgumentException;

class Importe
{
    public static function aCentavos(string $importe): int
    {
        if (! preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $importe)) {
            throw new InvalidArgumentException('El importe debe ser positivo y tener como máximo dos decimales.');
        }

        $partes = explode('.', $importe, 2);

        return ((int) $partes[0] * 100) + (int) str_pad($partes[1] ?? '', 2, '0');
    }

    public static function aDecimal(int $centavos): string
    {
        if ($centavos < 0) {
            throw new InvalidArgumentException('El importe no puede ser negativo.');
        }

        return intdiv($centavos, 100).'.'.str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }
}
