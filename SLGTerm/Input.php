<?php

namespace SLGTerm;

class Input {

    protected static $ttyprops;
    protected static $mustPrepare = true;
    protected static $mustCleanup = false;
    protected static $buffer = "";
    protected static $known_sequences = [
        27 => [
            0 => "<ESC>",
            79 => [
                80 => "<F1>",
                81 => "<F2>",
                82 => "<F3>",
                83 => "<F4>",
            ],
            91 => [
                49 => [
                    53 => [ 126 => "<F5>", ],
                    55 => [ 126 => "<F6>", ],
                    56 => [ 126 => "<F7>", ],
                    57 => [ 126 => "<F8>", ],
                ],
                50 => [
                    48 => [ 126 => "<F9>", ],
                    49 => [ 126 => "<F10>", ],
                    51 => [ 126 => "<F11>", ],
                    52 => [ 126 => "<F12>", ],
                    126 => "<INSERT>",
                ],
                53 => [
                    126 => "<PAGEUP>",
                ],
                54 => [
                    126 => "<PAGEDOWN>",
                ],
                65 => "<UP>",
                66 => "<DOWN>",
                67 => "<RIGHT>",
                68 => "<LEFT>",
                68 => "<BEGIN>",
                70 => "<END>",
                72 => "<HOME>",
                51 => [
                    126 => "<DELETE>",
                ],
            ],
        ],
        13 => [
            10 => "<ENTER>",
        ],
        127 => "<BACKSPACE>",
        9 => "<TAB>",
        10 => "<ENTER>",
    ];

    public static function read() {

        if (self::$mustPrepare) {
            self::readPrepare();
            self::$mustCleanup = true;
        }

        $timeout = (microtime(true)+0.01);
        $read_something = false;
        $done_reading = 0;

        do {

            for( $i = 0 ; $i < 10 ; $i++) {
                $read = fgetc(STDIN);
                if (\ord($read) > 0) {
                    $timeout = (microtime(true)+0.01);
                    $read_something = true;
                    $done_reading = 0;
                    static::$buffer = static::$buffer . $read;
                } elseif ($read_something && \ord($read) == 0) {
                    $done_reading++;
                }
            }

            $timed_out = (microtime(true) > $timeout);

        } while ( !$timed_out && $done_reading < 5 );

        if (self::$mustCleanup) {
            self::readCleanup();
        }

        return static::consume($timed_out, $done_reading);
    }

    public static function consume($timed_out = false, $done_reading = false) {

        $input = static::consumeOne(static::$known_sequences, [], $timed_out || $done_reading);
        return $input > -1 ? $input : false;
    }

    public static function consumeOne($known, $previous_chars, $timed_out) {
        if(strlen(static::$buffer) > 0) {
            $current_char = mb_substr(static::$buffer, 0, 1);
            static::$buffer = mb_substr(static::$buffer, 1);
            if(array_key_exists(\ord($current_char), $known)) {
                if (is_string($known[\ord($current_char)])) return [$known[\ord($current_char)]];
                if (is_array($known[\ord($current_char)])) {
                    if ($timed_out && (strlen(static::$buffer) == 0) && array_key_exists(0, $known[\ord($current_char)])) {
                        return [$known[\ord($current_char)][0]];
                    }
                    $found = static::consumeOne($known[\ord($current_char)], array_merge($previous_chars, [\ord($current_char) == 27 ? "<ESC>" : $current_char]), $timed_out);
                    if ($found === false) {
                        return $found;
                    } elseif($found === -1) {
                        static::$buffer = $current_char . static::$buffer;
                        return -1;
                    } elseif($found) {
                        return $found;
                    } else {
                        if(array_key_exists(0, $known[\ord($current_char)]) && strlen(static::$buffer)>0) {
                            return [$known[\ord($current_char)][0]];
                        } elseif($timed_out) {
                            return $current_char;
                        } else {
                            // restore buffer and keep waiting for input
                            static::$buffer = $current_char . static::$buffer;
                            return -1;
                        }
                    }
                }
                $current_char = \ord($current_char) == 27 ? "<ESC>" : $current_char;
                return array_merge($previous_chars, [$current_char]); // shouldn't execute ever
            } else {
                $current_char = \ord($current_char) == 27 ? "<ESC>" : $current_char;
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

