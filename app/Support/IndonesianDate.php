<?php

namespace App\Support;

use Carbon\CarbonInterface;

class IndonesianDate
{
    /** @var array<int, string> */
    public const DAYS = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];

    /** @var array<int, string> */
    public const MONTHS = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * "Jumat, 10 Oktober 2026, 10:00"
     */
    public static function formatDateTime(CarbonInterface $date): string
    {
        return sprintf(
            '%s, %d %s %d, %s',
            self::DAYS[$date->dayOfWeek] ?? '',
            $date->day,
            self::MONTHS[$date->month] ?? '',
            $date->year,
            $date->format('H:i'),
        );
    }

    /**
     * "Jumat, 10 Oktober 2026"
     */
    public static function formatDate(CarbonInterface $date): string
    {
        return sprintf(
            '%s, %d %s %d',
            self::DAYS[$date->dayOfWeek] ?? '',
            $date->day,
            self::MONTHS[$date->month] ?? '',
            $date->year,
        );
    }

    /**
     * "10 Oktober 2026"
     */
    public static function shortDate(CarbonInterface $date): string
    {
        return sprintf('%d %s %d', $date->day, self::MONTHS[$date->month] ?? '', $date->year);
    }

    /**
     * Keterangan relatif: "besok", "hari ini", "dalam 3 hari".
     */
    public static function relative(int $dayDifference): string
    {
        return match (true) {
            $dayDifference === 0 => 'hari ini',
            $dayDifference === 1 => 'besok',
            $dayDifference === 2 => 'lusa',
            $dayDifference > 2 => "dalam {$dayDifference} hari",
            default => 'telah lewat',
        };
    }

    /**
     * Selisih hari kalender dari sekarang menuju tanggal target.
     */
    public static function daysUntil(CarbonInterface $target): int
    {
        $difference = now()->startOfDay()->diffInDays($target->copy()->startOfDay(), false);

        return (int) round($difference);
    }
}
