<?php

namespace App\Helpers;

class NumberConverter
{
    public static function convertToWord($number)
    {
        $words = [
            '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh',
            'Sebelas', 'Dua Belas', 'Tiga Belas', 'Empat Belas', 'Lima Belas', 'Enam Belas', 'Tujuh Belas', 'Delapan Belas', 'Sembilan Belas'
        ];

        if ($number < 20) {
            return $words[$number];
        } elseif ($number < 100) {
            $tens = floor($number / 10);
            $ones = $number % 10;
            return $words[$tens] . ' Puluh ' . $words[$ones];
        } else {
            return 'Angka terlalu besar untuk dikonversi.';
        }
    }
}
