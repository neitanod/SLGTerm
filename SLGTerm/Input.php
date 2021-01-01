<?php

namespace SLGTerm;

class Input {

    protected static $ttyprops;
    protected static $mustPrepare = true;
    protected static $mustCleanup = false;
    protected static $buffer = "";
    protected static $known_sequences = [
        27 => [
            0 => "esc",
            91 => [
                65 => "up",
                66 => "down",
                67 => "right",
                68 => "left",
                70 => "end",
                72 => "home",
                51 => [
                    126 => "delete",
                ],
            ],
        ],
        127 => "backspace"
    ];

    protected static $spinner;

    public static function read() {

        if(!static::$spinner) static::$spinner = new Spinner(Terminal::cols()-3, 5);

        if (self::$mustPrepare) {
            self::readPrepare();
            self::$mustCleanup = true;
        }

        $timeout = (microtime(true)+0.1);

        do {

            $read = fgetc(STDIN);

            if (\ord($read) > 0) {
                static::$buffer = static::$buffer . $read;
            }

        } while (\ord($read) == 0 && (microtime(true) <= $timeout));

        static::$spinner->advance();

        if (self::$mustCleanup) {
            self::readCleanup();
        }

        return static::consume(microtime(true) > $timeout);
    }

    public static function consume($timeout = false) {
        if(\ord(static::$buffer)==27 && strlen(static::$buffer) == 1) {
            if ($timeout) {
                // read() timed out, so we are going to consider ESC as actual key,
                // not escape character.
                static::$buffer = mb_substr(static::$buffer, 1);
                return "esc";
            } else return false;
        }
        $input = static::consumeOne(static::$known_sequences, []);
        return $input > -1 ? $input : false;
    }

    public static function consumeOne($known, $previous_chars) {
        if(strlen(static::$buffer) > 0) {
            $current_char = mb_substr(static::$buffer, 0, 1);
            static::$buffer = mb_substr(static::$buffer, 1);
            if(array_key_exists(\ord($current_char), $known)) {
                if (is_string($known[\ord($current_char)])) return [$known[\ord($current_char)]];
                if (is_array($known[\ord($current_char)])) {
                        $found = static::consumeOne($known[\ord($current_char)], array_merge($previous_chars, [\ord($current_char) == 27 ? "esc" : $current_char]));
                        if ($found === false) {
                            return $found;
                        } elseif($found === -1) {
                            static::$buffer = $current_char . static::$buffer;
                            Terminal::echoAt(30,10, "RESTORING: [".\ord($current_char)."] (". static::$buffer.")");
                            return -1;
                        } elseif($found) {
                            return $found;
                        } else {
                            if(array_key_exists(0, $known[\ord($current_char)]) && strlen(static::$buffer)>0) {
                                return [$known[\ord($current_char)][0]];
                            } else {
                                // restore buffer and keep waiting for input
                                static::$buffer = $current_char . static::$buffer;
                                return -1;
                            }
                        }
                }
                $current_char = \ord($current_char) == 27 ? "esc" : $current_char;
                return array_merge($previous_chars, [$current_char]); // shouldn't execute ever
            } else {
                $current_char = \ord($current_char) == 27 ? "esc" : $current_char;
                return array_merge($previous_chars, [$current_char]);
            }
        }
        return -1;
    }

    protected static function str_split_unicode($str, $l = 0) {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");
            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l, "UTF-8");
            }
            return $ret;
        }
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    public static function readPrepare(){
        self::$ttyprops = trim(`stty -g`);
        system('stty -icanon -echo');
        stream_set_blocking(STDIN, false);
        self::$mustPrepare = false;
    }

    public static function readCleanup(){
        system("stty '".self::$ttyprops."'");
        self::$mustPrepare = true;
        self::$mustCleanup = false;
    }

}

