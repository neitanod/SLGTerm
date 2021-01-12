<?php
namespace SLGTerm;

require_once(__DIR__."/TraitObservable.php");

class Label {

    use Observable;

    protected $col;
    protected $row;
    protected $value = "";
    protected $style = null;

    public function __construct(string $value = "", int $col = -1, int $row = -1, int $width = -1) {
        $this->value = $value;
        $this->col = $col;
        $this->row = $row;
        $this->width = $width;
        $this->init_observable();
        $this->style = new Style();
    }

    public function render() {

        if ( $this->col == -1 ) {
            $this->positionAtCursor();
        } else {
            Cursor::move($this->col, $this->row);
        }

        Terminal::applyStyle($this->style);

        $width = $this->width;

        if($width == -1) {
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


