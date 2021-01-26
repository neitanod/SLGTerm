<?php
namespace SLGTerm;

require_once(__DIR__."/TraitObservable.php");
require_once(__DIR__."/TraitHasColRow.php");
require_once(__DIR__."/TraitHasWidth.php");

class Label {

    use Observable, HasColRow, HasWidth;

    protected $value = "";
    protected $style = null;

    public function __construct(string $value = "", int $col = null, int $row = null, int $width = null) {
        $this->value = $value;
        $this->col = $col;
        $this->row = $row;
        $this->width = $width;
        $this->init_observable();
        $this->style = new Style();
    }

    public function render() {

        if ( is_null($this->col) ) {
            $this->positionAtCursor();
        } else {
            Cursor::move($this->getCol(), $this->getRow());
        }

        Terminal::applyStyle($this->style);

        $width = $this->getWidth();

        if( is_null($width) ) {
            $width = mb_strlen($this->value);
        }

        Terminal::echo(str_pad(mb_substr($this->value, 0, $width), $width));

        Terminal::resetStyle();

        return $this;
    }

    public function positionAtCursor() {
        $current_position = Cursor::getPosition();
        $this->col = $current_position["col"];
        $this->row = $current_position["row"];
        return $this;
    }

    public function setValue($value) {
        $this->value = (string)$value;
        return $this;
    }

    public function style(Style $style) {
        $this->style = $style;
        return $this;
    }
}


