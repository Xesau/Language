<?php

namespace Xesau;

/**
 * Static language helper class
 */
class Language
{
    private static $values = array();
    
    /**
     * Constructor locker
     */
    private function __construct()
    {
    }
    
    /** 
     * Loads a language file
     *
     * @param string $path The path to the language file
     * @return void
     */
    public static function loadFile($path)
    {
        for($tok = strtok(file_get_contents($path), "\r\n"); $tok !== false; $tok = strtok("\r\n")) {
            $parts=explode('=', $tok, 2);
            if (isset($parts[1]))
                self::$values[$parts[0]] = str_replace('\n', PHP_EOL, $parts[1]);
        }
    }
    
    /**
     * Translates the given key using the given parameters
     *
     * @param string $key The key
     * @param mixed... $params The parameters
     */
    public static function translate($key) {
        if (!isset(self::$values[$key]))
            return $key;
        
        $replacement = self::$values[$key];
        
        $args = func_get_args();
        unset($args[0]);
        
        foreach($args as $key => $value)
            $replacement = str_replace('%'. $key, $value, $replacement);
        
        return $replacement;
    }
    
    public static function formatAgo($timestamp, $type = 'full') {
        if ($timestamp == null)
            return self::$values['datetime.never'];
        
        $dif = time() - $timestamp;
        if ($dif < 0) {
            $format = 'datetime.dif.syntax_future';
            $dif = -$dif;
        } elseif ($dif < 10) {
            return self::$values['datetime.dif.just_now'];
        } else {
            $format = 'datetime.dif.syntax_past';
        }
        
        $greatNames = ['second', 'minute', 'hour', 'day', 'week', 'month', 'year'];
        $greatLengths = [1, 60, 3600, 86400, 604800, 2635200, 31557600];

        for($greatIndex = 0; $greatIndex <= 6; $greatIndex ++) {
            if ($dif <= $greatLengths[$greatIndex]) {
                if ($greatIndex > 4)
                    return self::formatTime($timestamp, $type);
                else {
                    $amount = floor($dif / ($greatLengths[$greatIndex - 1]));
                    $index = 'datetime.dif.'. $greatNames[$greatIndex - 1] . ($amount == 1 ? '' : 's');
                    return str_replace(['%v', '%q'], [$amount, self::$values[$index]], self::$values[$format]);
                }
            }
        }
    }
    
    public static function formatTime($timestamp, $type = 'full')
    {
        $syntax = self::$values['datetime.syntax.full'];
        if (isset(self::$values['datetime.syntax.'. $type]))
            $syntax = self::$values['datetime.syntax.'. $type];
        
        /* Syntax
         * %n --> Name of day
         * %d --> Day of month
         * %m --> Name of month
         * %y --> Number of Year
         * %H --> Hour (0-23)
         * %t --> Minute
         * %s --> Second
         * %h --> Hour (1-12)
         * %A --> AM/PM
         */
            
        // daynum, dayOfMonth, month, year, hour, minutes, seconds
        $parts = explode('|', date('N|j|n|Y|H|i|s', $timestamp));
        
        return str_replace([
            '%n',
            '%d',
            '%m',
            '%y',
            '%H',
            '%t',
            '%s',
            '%h',
            '%A'
        ], [
            self::$values['datetime.day.' . $parts[0]],
            $parts[1],
            self::$values['datetime.month.'. $parts[2]],
            $parts[3],
            $parts[4],
            $parts[5],
            $parts[6],
            $parts[4] > 12 ? $parts[4] - 12 : $parts[4],
            self::$values[$parts[4] > 12 ? 'datetime.pm' : 'datetime.am']    
        ], $syntax);
        
    }

}
