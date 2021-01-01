<?php
namespace SLGTerm;

require_once(__DIR__."/TraitObservable.php");
require_once(__DIR__."/TraitTimeable.php");
require_once(__DIR__."/TraitColoreable.php");
require_once(__DIR__."/EditableString.php");

class Controller {

   use Observable;
   // use Timeable;

   protected $localBuffer = [];

   public function __construct() {
       $this->init_observable();
   }

   public function loop() {

       $exit = false;

       Terminal::readPrepare();

       while(!$exit) {
           $this->readToBuffer();
           $key = $this->readFromBuffer();
           if(is_object($key) || \ord($key)>0) {
               $this->bus->emit(
                   new Event("key", ["code" => \ord($key), "key"=> $key])
               );
           }
       }

       Terminal::readCleanup();
   }

   public function readFromBuffer() {
       if ( !empty($this->localBuffer) ) {
           return array_shift($this->localBuffer);
       }
   }

   public function readToBuffer() {
       $captured = Terminal::read();
       $this->localBuffer[] = $captured;
   }

}

class TextInput {

    // use Observable;
    // use Coloreable;
    // use Timeable;

    protected $col;
    protected $row;
    protected $width = 0;
    protected $echo = true;
    protected $localBuffer = [];
    protected $str = null;

    protected $posInField = 0;
    protected $posInValue = 0;
    protected $offset = 0;

    public function __construct(int $col = -1, int $row = -1) {
        $this->col = $col;
        $this->row = $row;
        $this->init_observable();
        $this->width = -1;
        //Terminal::cols() - $this->col;
        $this->str = new EditableString();
    }

    public function setValue(string $value) {
        $this->str->setValue($value);
    }

    public function width($width) {
        $this->width = $width;
        return $this;
    }

    public function readFromBuffer() {
        if ( !empty($this->localBuffer) ) {
            return array_shift($this->localBuffer);
        } else {
            return Terminal::read();
        }
    }

    protected function advanceCursor() {

        if($this->str->advanceCursor()) {
            if($this->posInField >= $this->width) { // if pushing right
                $this->offset++;
            } else {
                $this->posInField++;
            }
        }

    }

    protected function retrocedeCursor() {

        if($this->str->retrocedeCursor()) {
            if($this->posInField <= 0) { // if pushing left
                $this->offset--;
            } else {
                $this->posInField--;
            }
        }

    }

    protected function calculateVisible() {
        return str_pad(substr(substr($this->str->getValue(), $this->offset), 0, $this->width), $this->width);
    }

    public function render() {
        if ( $this->col == -1 ) {
            $this->positionAtCursor();
        }

        if ( $this->width == -1 ) {
            $this->width = min(Terminal::cols() - $this->col, 30);
        }

        Cursor::move($this->col, $this->row);
        $this->setColors();

        Terminal::echo($this->calculateVisible());

        Cursor::move($this->col + $this->posInField, $this->row);
        $this->resetColors();
        return $this;
    }

    public function focus() {

        $this->render();

        $escaped = false;

        $exit = false;

        $codes = "";

        while(!$exit) {

            $key = $this->readFromBuffer();

            if($escaped){
                $escaped = false;
                $special = $this->detectSpecial();

                if ($special == "left") {
                    $this->retrocedeCursor();
                    $this->render();
                } elseif ($special == "right") {
                    $this->advanceCursor();
                    $this->render();
                }

                $next = $this->readFromBuffer();
                if($next === "["){
                    $next2 = $this->readFromBuffer();
                    $next3 = $this->readFromBuffer();
                    $next4 = $this->readFromBuffer();
                } else {
                    $key = $next;
                }
            }

            if($key === "h"){
                $this->retrocedeCursor();
                $this->render();
                continue;
            }
            if($key === "l"){
                $this->advanceCursor();
                $this->render();
                continue;
            }

            if(\ord($key) === 0){
                continue;
            } elseif(\ord($key) === 27){
            $codes .= \ord($key)." ";
             Terminal::echoAt(3, 23, $codes);
                $escaped = true;
                continue;
            } elseif(\ord($key) === 10){
                $exit = true;
            } elseif(\ord($key) === 127){
                $this->str->remove(-1);
                $this->retrocedeCursor();
                $this->render();
            } elseif(\ord($key) === 9){
                // should pass focus to next input element
            } else {
                $this->str->insert($key);

                $this->advanceCursor();

                if ($this->echo) {
                    $this->render();
                }
            }

            $result = $this->emit("input", ["value"=>$this->str->getValue(), "bus"=>$this->bus, "currentTarget"=>$this]);

            $codes .= \ord($key)." ";
             Terminal::echoAt(3, 23, $codes);
        }
        return $this->str->getValue();
    }

    public function detectSpecial() {
        ;
    }

    public function positionAtCursor() {
        $current_position = Cursor::getPosition();
        $this->col = $current_position["col"];
        $this->row = $current_position["row"];
        return $this;
    }

    public function getValue() {
        return $this->str->getValue();
    }

}

class Box {

    protected $set = [
        'basic' => [
            "+-++",
            "| ||",
            "+-++",
            "+-++",
        ],
        'single' => [
            '"\u250C\u2500\u252C\u2510"',
            '"\u2502 \u2502\u2502"',
            '"\u251C\u2500\u253C\u2524"',
            '"\u2514\u2500\u2534\u2518"',
        ],
        'double' => [
            "+-++",
            "| ||",
            "+-++",
            "+-++",
        ],
        'single_double' => [
            "+-++",
            "| ||",
            "+-++",
            "+-++",
        ],
        'double_single' => [
            "+-++",
            "| ||",
            "+-++",
            "+-++",
        ],
    ];

    protected $useSet = "single";

    public function __construct(
        int $col = 0,
        int $row = 0,
        int $width = -1,
        int $height = -1
    ) {
        $this->col = $col;
        $this->row = $row;

        if($width == -1) {
            $this->width = Terminal::cols() - $col;
        } else {
            $this->width = $width;
        }

        if($height == -1) {
            $this->height = Terminal::rows() - $row;
        } else{
            $this->height = $height;
        }
    }

    public function render() {
        Terminal::echoAt($this->col, $this->row, $this->border(0,0).str_repeat($this->border(0,1), $this->width-2). $this->border(0,3));

        for(
            $row = $this->row+2;
            $row < ($this->row+$this->height);
            $row++
            ) {
            Terminal::echoAt($this->col, $row, $this->border(1,0).str_repeat($this->border(1,1), $this->width-2).$this->border(1,3));

        }

        Terminal::echoAt($this->col, $this->row+$this->height, $this->border(3,0).str_repeat($this->border(3,1), $this->width-2).$this->border(3,3));
    }

    protected function border($row, $col) {
        return mb_substr(json_decode($this->set[$this->useSet][$row]),$col,1);
    }
}
