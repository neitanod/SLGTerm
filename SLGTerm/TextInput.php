<?php
namespace SLGTerm;

require_once(__DIR__."/TraitObservable.php");
require_once(__DIR__."/TraitTimeable.php");
require_once(__DIR__."/TraitColoreable.php");
require_once(__DIR__."/EditableString.php");

class TextInput {

    use Observable;
    use Coloreable;
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

    protected $hasFocus = false;

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

    public function focus() {
        $this->hasFocus = true;
        Cursor::show();
        $result = $this->emit("focus", ["bus"=>$this->bus, "currentTarget"=>$this]);
    }

    public function blur() {
        $this->hasFocus = false;
        $this->render();
        $result = $this->emit("blur", ["bus"=>$this->bus, "currentTarget"=>$this]);
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

        if ( $this->hasFocus ) {
            $this->underline = true;
        } else {
            $this->underline = false;
        }

        $this->setColors();

        Terminal::echo($this->calculateVisible());

        Cursor::move($this->col + $this->posInField, $this->row);
        $this->resetColors();
        return $this;
    }

    public function handleInput( $event ) {

        $this->render();
        $key = $event->getData("key");

        if ($key == "<LEFT>") {
            $this->retrocedeCursor();
            $this->render();
        } elseif ($key == "<ENTER>") {
            // Ignore
        } elseif ($key == "<RIGHT>") {
            $this->advanceCursor();
            $this->render();
        } elseif($key === '<BACKSPACE>'){
            $this->str->remove(-1);
            $this->retrocedeCursor();
            $this->render();
        } elseif($key === '<DELETE>'){
            $this->str->remove(1);
            $this->render();
        } elseif($key === '<TAB>'){
            // should pass focus to next input element
        } else {
            if (mb_strlen($key) == 1) {

                $this->str->insert($key);

                $this->advanceCursor();

                if ($this->echo) {
                    $this->render();
                }
            }
        }

        $result = $this->emit("input", ["value"=>$this->str->getValue(), "bus"=>$this->bus, "currentTarget"=>$this]);
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
