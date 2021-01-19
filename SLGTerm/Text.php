<?php
namespace SLGTerm;

require_once(__DIR__."/TraitObservable.php");

class Text {

    use Observable;

    protected $upKeys = [
        "<UP>",
        "k",
    ];

    protected $downKeys = [
        "<DOWN>",
        "j",
    ];

    protected $topKeys = [
        "<HOME>",
    ];

    protected $bottomKeys = [
        "<END>",
    ];

    protected $hasFocus = false;

    protected $offset = 0;

    protected $col = null;
    protected $row = null;
    protected $height = null;
    protected $width = null;

    protected $style = null;
    protected $styleFocused = null;

    protected $contents = [];

    protected $rendering = false;

    const DEFAULT_WIDTH = 25;
    const DEFAULT_HEIGHT = 5;

    public function __construct(int $col = null, int $row = null, int $width = null, int $height = null) {
        $this->col = $col;
        $this->row = $row;
        $this->width = $width;
        $this->height = $height;
        $this->init_observable();

        // Default styles:
        $this->style = new Style('white', 'black', 250, 235);

        // Widget style when focused
        $this->styleFocused = (new Style('white', 'black', 250, 235))->bold();
    }

    public function setKeys(array $upArray = null, array $downArray = null, array $topKeys = null, array $bottomKeys = null) {
        if (! is_null($upArray) ) {
            $this->upKeys = $upArray;
        }
        if (! is_null($downArray) ) {
            $this->downKeys = $downArray;
        }
        if (! is_null($topArray) ) {
            $this->topKeys = $topArray;
        }
        if (! is_null($bottomArray) ) {
            $this->bottomKeys = $bottomArray;
        }
    }

    public function handleInput( $event ) {

        $key = $event->getData("key");

        if (in_array($key, $this->upKeys)) {
            $this->moveUp();
        }

        if (in_array($key, $this->downKeys)) {
            $this->moveDown();
        }

        if (in_array($key, $this->topKeys)) {
            $this->moveTop();
        }

        if (in_array($key, $this->bottomKeys)) {
            $this->moveBottom();
        }
        $this->render();

    }

    public function moveUp() {
        $this->offset = max(0, $this->offset-1);
    }

    public function moveDown() {
        $this->offset = min(count($this->contents)-$this->height, $this->offset+1);
    }

    public function moveTop() {
        $this->offset = 0;
    }

    public function moveBottom() {
        $this->offset = count($this->contents)-$this->height;
    }

    public function render() {
        if ( $this->rendering ) return $this;

        $this->rendering = true;

        if ( is_null($this->col) ) {
            $this->positionAtCursor();
        }

        if ( is_null($this->height) ) {
            $this->height = self::DEFAULT_HEIGHT;
        }

        for ( $i = 0; $i < $this->height; $i++) {
            Cursor::move( $this->col, $this->row+$i );
            if ( isset($this->contents[$i+$this->offset]) ) {
                Terminal::applyStyle($this->currentStyle());
                if (is_object( $this->contents[$i + $this->offset] )) {
                    $this->contents[$i + $this->offset]->render();
                } else {
                    Terminal::echoWidth($this->contents[$i + $this->offset], $this->width);
                }
            } else {
                Terminal::applyStyle($this->currentStyle());
                Terminal::echo(str_repeat(" ", $this->width));
            }
        }

        Terminal::resetStyle();

        $this->rendering = false;

        return $this;
    }

    public function setValue( $text_array ) {
        if (is_string( $text_array )) {
            $this->contents = explode("\n", str_replace("\r","",$text_array));
        }

        $text_array = array_map(function($str) { return str_replace("\n","", str_replace("\r","",$str)); }, $text_array);
        $this->contents = $text_array;

        return $this;
    }

    public function focus() {
        $this->hasFocus = true;
        Cursor::hide();
        $result = $this->emit("focus", ["target"=>$this]);
    }

    public function blur() {
        $this->hasFocus = false;
        $this->render();
        $result = $this->emit("blur", ["target"=>$this]);
    }

    public function positionAtCursor() {
        $current_position = Cursor::getPosition();
        $this->col = $current_position["col"];
        $this->row = $current_position["row"];
        return $this;
    }

    public function getWidth() {
        if ( is_null($this->width) ) {
            $this->width = self::DEFAULT_WIDTH;
        }

        return $this->width;
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

    public function append($line) {
        $this->contents[] = $line;
    }

}
