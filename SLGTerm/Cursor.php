<?php
namespace SLGTerm;

class Cursor {

    protected static $lastpos;

    public static function home() {
        static::move(0, 0);
    }

    public static function move($col = null, $row = null){
        if ( !is_null($col) && !is_null($row) ) {
            Terminal::err(Terminal::ESCAPE."{$row};{$col}f");
        }
    }

    public static function up($count = 1){
        Terminal::err(Terminal::ESCAPE."{$count}A");
    }

    public static function down($count = 1){
        Terminal::err(Terminal::ESCAPE."{$count}B");
    }

    public static function left($count = 1){
        Terminal::err(Terminal::ESCAPE."{$count}D");
    }

    public static function right($count = 1){
        Terminal::err(Terminal::ESCAPE."{$count}C");
    }

    public static function getPosition() {
        $success = false;
        while(!$success) {
            $ttyprops = trim(`stty -g`);
            system('stty -icanon -echo');

            Terminal::err( Terminal::ESCAPE."6n");

            try {

                $buf = fread(STDIN, 16);

                system("stty '$ttyprops'");

                $matches = [];
                preg_match('/^\033\[(\d+);(\d+)R$/', $buf, $matches);

                if (!empty($matches)) {
                    $row = intval($matches[1]);
                    $col = intval($matches[2]);
                    $success = true;
                }
            } catch (\Exception $e) {
                $success = false;
            }
        }
        return ["col"=>$col, "row"=>$row];
    }

    public function setPosition($position) {
        static::move($position['col'], $position['row']);
    }

	public static function savepos() {
        Terminal::err(Terminal::ESCAPE."s");
	}

	public static function save() {
        Terminal::err("\0337");
	}

	public static function unsave() {
        Terminal::err(Terminal::ESCAPE."u");
	}

	public static function restore() {
        Terminal::err("\0338");
	}

	public static function hide() {
        Terminal::err(Terminal::ESCAPE."?25l");
	}

	public static function show() {
		Terminal::err(Terminal::ESCAPE."?25h".Terminal::ESCAPE."?0c");
	}

	public static function wrap( $wrap = true ) {
		if( $wrap ) {
            Terminal::err(Terminal::ESCAPE."?7h");
		} else {
            Terminal::err(Terminal::ESCAPE."?7l");
		}
    }

    public static function showOnExit(){
        register_shutdown_function(function() { Cursor::show(); });
    }


}
