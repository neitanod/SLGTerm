<?php
namespace SLGTerm;

require(__DIR__."/Character.php");

class Terminal {

    const COLOR = [
		'bold'       => '1',    'dim'          => '2',
		'black'      => '0;30', 'dark_gray'    => '1;30',
		'blue'       => '0;34', 'light_blue'   => '1;34',
		'green'      => '0;32', 'light_green'  => '1;32',
		'cyan'       => '0;36', 'light_cyan'   => '1;36',
		'red'        => '0;31', 'light_red'    => '1;31',
		'purple'     => '0;35', 'light_purple' => '1;35',
		'brown'      => '0;33', 'yellow'       => '1;33',
		'light_gray' => '0;37', 'white'        => '1;37',
		'normal'     => '0;39',
    ];

    const BG_COLOR = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue'  => '44',
        'magenta' => '45',
        'cyan'  => '46',
        'white' => '47',
        'grey' => '40;1',
        'red2' => '41;1',
        'green2' => '42;1',
        'yellow2' => '43;1',
        'blue2'  => '44;1',
        'magenta2' => '45;1',
        'cyan2'  => '46;1',
        'white2' => '47;1',
    ];

    public const ESCAPE = "\033[";
    public const ESCAPE256 = "\u{001b}[";

    protected static $mustPrepare = true;
    protected static $mustCleanup = false;
    protected static $ttyprops;
    protected static $maxcolors = null;

    public static function cols() {
        return intval(exec('tput cols'));
    }

    public static function rows() {
        return intval(exec('tput lines'));
    }

    public static function colors() {
        return empty(static::$maxcolors) ? static::$maxcolors = max(8, intval(exec('tput colors'))) : static::$maxcolors;
    }

    public static function fgColor($color) {
        if (array_key_exists($color, static::COLOR)) {
            static::echo(static::ESCAPE.(static::COLOR[$color])."m");
        } elseif(is_numeric($color)) {
            static::echo(static::ESCAPE256."38;5;".$color."m");
        }
    }

    public static function bgColor($color) {
        if (array_key_exists($color, static::BG_COLOR)) {
            static::echo(static::ESCAPE.static::BG_COLOR[$color]."m");
        } elseif(is_numeric($color)) {
            static::echo(static::ESCAPE256."48;5;".$color."m");
        }
    }

    public static function underline() {
        static::echo(static::ESCAPE."4m");
    }

    public static function bold() {
        static::echo(static::ESCAPE."1m");
    }

    public static function dim() {
        static::echo(static::ESCAPE."2m");
    }

    public static function normal() {
        static::echo(static::ESCAPE."0m");
    }

    public static function echo($str) {
        fwrite(STDOUT, $str);
    }

    public static function echoln($str) {
        static::echo($str.PHP_EOL);
    }

    public static function err($str) {
        fwrite(STDERR, $str);
    }

    public static function echoAt($col, $row, $text)
    {
        Cursor::save();
        Cursor::move($col, $row);
        static::echo($text);
        Cursor::restore();
    }

    public static function echoWidth($str, $width, $fill = null)
    {
        if (mb_strlen($str) >= $width) {
            Terminal::echo( mb_substr($str, 0, $width) );
        } else {
            Terminal::echo( $str . str_repeat(is_null($fill)?" ":$fill, $width - mb_strlen($str)) );
        }
    }

    public static function readln() {
        return fgets(STDIN);
    }

    public static function isCli() {
        return substr(php_sapi_name(), 0, 3) == 'cli';
    }

    public static function isTTY() {
        return posix_isatty(STDOUT);
    }

    public static function isPiped() {
        return !posix_isatty(STDOUT);
    }

    public static function clear() {
        static::err(static::ESCAPE."2J");
    }

    public static function home() {
        static::err(Cursor::home());
    }

    public static function bell() {
        static::err(chr(007));
    }

    public static function clearLine($line) {
        if ( is_null($line) ) {
            Terminal::echo(Terminal::ESCAPE."2K");
        } else {
            Cursor::save();
            Cursor::move(0, $line);
            Terminal::echo(Terminal::ESCAPE."2K");
            Cursor::restore();
        }
    }

    public static function clearLineStart() {
        Terminal::echo(Terminal::ESCAPE."1K");
    }

    public static function clearLineEnd() {
        Terminal::echo(Terminal::ESCAPE."K");
    }

    public static function clearUp() {
        Terminal::echo(Terminal::ESCAPE."1J");
    }

    public static function clearDown() {
        Terminal::echo(Terminal::ESCAPE."J");
    }

    public static function saveContents() {
        return passthru('tput smcup');
    }

    public static function restoreContents() {
        return passthru('tput rmcup');
    }

    public static function applyStyle($style) {

        Terminal::normal();

        Terminal::fgColor($style->getFgColorFor(Terminal::colors()));
        Terminal::bgColor($style->getBgColorFor(Terminal::colors()));

        if ( $style->get('bold') ) {
            Terminal::bold();
        } elseif ( $style->get('dim') ) {
            Terminal::dim();
        }
        if ( $style->get('underline') ) {
            Terminal::underline();
        }


    }

    public static function resetStyle() {
        Terminal::normal();
    }

}
