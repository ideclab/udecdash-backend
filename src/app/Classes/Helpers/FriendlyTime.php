<?php

namespace App\Classes\Helpers;

class FriendlyTime {

    public static function fromSeconds(int $seconds) : string {
        if($seconds < 60){
            return "--";
        }
        $friendly = "";
        $time = static::formatTimeFromSeconds($seconds);
        $friendly .= static::formatText($time->days, "Día");
        $friendly .= static::formatText($time->hours, "Hora");
        $friendly .= static::formatText($time->minutes, "Minuto");
        return trim($friendly);
    }

    public static function formatText($quantity, $text) : string {
        $formatted = "";
        if($quantity == 0){
            return $formatted;
        }
        if($quantity >= 0 && $quantity <= 9){
            $formatted = "0{$quantity}";
        }else{
            $formatted = "{$quantity}";
        }
        $plural = $quantity == 1 ? '' : 's';
        $formatted = "$formatted {$text}{$plural} ";
        return $formatted;
    }

    public static function formatTimeFromSeconds(int $seconds){
        $time = new \StdClass();
        $time->days = (int) ($seconds / 86400);
        if($time->days >= 1){
            $time->seconds = $seconds - (86400 * $time->days);
        }
        $time->hours = (int) gmdate("H", $seconds);
        $time->minutes = (int) gmdate("i", $seconds);
        $time->seconds = (int) gmdate("s", $seconds);
        return $time;
    }
}
