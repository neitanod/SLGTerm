<?php
namespace SLGTerm;

require_once(__DIR__."/TraitObservable.php");
require_once(__DIR__."/TraitHasColRow.php");
require_once(__DIR__."/TraitHasWidth.php");
require_once(__DIR__."/EditableString.php");

class TextInput {

    use Observable, HasColRow, HasWidth;

    protected $echo = true;
    protected $localBuffer = [];
    protected $str = null;

    protected $posInField = 0;
    protected $posInValue = 0;
    protected $offset = 0;

    protected $hasFocus = false;

    protected $style = null;
    protected $styleFocused = null;

    const DEFAULT_WIDTH = 25;

    public function __construct(int $col = null, int $row = null) {
        $this->col = $col;
        $this->row = $row;
        $this->init_observable();
        $this->width = self::DEFAULT_WIDTH;

        //Terminal::cols() - $this->col;
        $this->str = new EditableString();
    }

    public function setValue(string $value) {
        $this->str->setValue($value);
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
        $result = $this->emit("focus", ["bus"=>$this->bus, "target"=>$this]);
    }

    public function blur() {
        $this->hasFocus = false;
        $this->render();
        $result = $this->emit("blur", ["bus"=>$this->bus, "target"=>$this]);
    }

    protected function advanceCursor() {

        if($this->str->advanceCursor()) {
            if($this->posInField >= $this->getWidth()) { // if pushing right
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
        return str_pad(substr(substr($this->str->getValue(), $this->offset), 0, $this->getWidth()), $this->getWidth());
    }

    public function render() {
        if ( is_null($this->col) ) {
            $this->positionAtCursor();
        }

        if ( is_null($this->width) ) {
            $this->width = min(Terminal::cols() - $this->getCol(), 30);
        }

        Cursor::move($this->getCol(), $this->getRow());

        if ( $this->hasFocus ) {
            $this->underline = true;
        } else {
            $this->underline = false;
        }

        Terminal::applyStyle($this->currentStyle());

        Terminal::echo($this->calculateVisible());

        Cursor::move($this->getCol() + $this->posInField, $this->getRow());
        Terminal::resetStyle();
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

        $result = $this->emit("input", ["value"=>$this->str->getValue(), "bus"=>$this->bus, "target"=>$this]);
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

    public function style(Style $style) {
        $this->style = $style;
        return $this;
    }

    public function styleFocused(Style $style) {
        $this->styleFocused = $style;
        return $this;
    }

    protected function currentStyle() {
        if (
            !$this->hasFocus ||
            is_null($this->styleFocused)
        ) {
            return $this->style;
        } else {
            return $this->styleFocused;
        }
    }

    protected function getWidth() {
        return $this->width;
    }

    public function setWidth(int $width) {
        $this->width = $width;
        return $this;
    }
}
