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

    public function __construct(string $value = "", int $col = -1, int $row = -1) {
        $this->value = $value;
        $this->col = $col;
        $this->row = $row;
        $this->init_observable();
    }


    public function render() {

        if ( $this->col == -1 ) {
            $this->positionAtCursor();
        } else {
            Cursor::move($this->col, $this->row);
        }

        $this->setColors();

        Terminal::echo($this->value);

        $this->resetColors();

        return $this;
    }

    public function positionAtCursor() {
        $current_position = Cursor::getPosition();
        $this->col = $current_position["col"];
        $this->row = $current_position["row"];
        return $this;
    }

}


