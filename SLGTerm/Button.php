<?php
namespace SLGTerm;

require_once(__DIR__."/TraitObservable.php");
require_once(__DIR__."/TraitColoreable.php");

class Button {

    use Observable;
    use Coloreable;

    protected $col;
    protected $row;
    protected $value = "";
    protected $hasFocus = false;

    public function __construct(string $value = "", int $col = -1, int $row = -1, int $width = -1) {
        $this->value = $value;
        $this->col = $col;
        $this->row = $row;
        $this->width = $width;
        $this->init_observable();
    }

    public function focus() {
        $this->hasFocus = true;
        $this->render();
        $result = $this->emit("focus", ["bus"=>$this->bus, "currentTarget"=>$this]);
    }

    public function blur() {
        $this->hasFocus = false;
        $this->render();
        $result = $this->emit("blur", ["bus"=>$this->bus, "currentTarget"=>$this]);
    }

    public function handleInput( Event $event ) {
        if ( $event->getData("key") == " " || $event->getData("key") == "<ENTER>" ) {
            $this->emit( "pressed", [
                "key"=>$event->getData("key"),
                "currentTarget"=>$this,
                "parentEvent"=>$event
            ]);
        }
    }

    public function render() {

        if ( $this->col == -1 ) {
            $this->positionAtCursor();
        } else {
            Cursor::move($this->col, $this->row);
        }

        if ( $this->hasFocus ) {
            $this->underline = true;
            $this->weight = "bold";
        } else {
            $this->underline = false;
            $this->weight = "normal";
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

