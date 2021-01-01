<?php
namespace SLGTerm;

require_once(__DIR__."/TraitObservable.php");
require_once(__DIR__."/TraitColoreable.php");

class Label {

    use Observable;
    use Coloreable;

    protected $col;
    protected $row;
    protected $value = "";

    public function __construct(string $value = "", int $col = -1, int $row = -1, int $width = -1) {
        $this->value = $value;
        $this->col = $col;
        $this->row = $row;
        $this->width = $width;
        $this->init_observable();
    }

    public function render() {

        if ( $this->col == -1 ) {
            $this->positionAtCursor();
        } else {
            Cursor::move($this->col, $this->row);
        }

        $this->setColors();

        $width = $this->width;

        if($width == -1) {
            $width = mb_strlen($this->value);
        }

        Terminal::echo(str_pad(mb_substr($this->value, 0, $width), $width));

        $this->resetColors();

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
}


