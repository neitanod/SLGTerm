<?php
namespace SLGTerm;

require_once(__DIR__."/TraitObservable.php");
require_once(__DIR__."/TraitColoreable.php");

class ListInput {

    use Observable;
    use Coloreable;

    protected $upKeys = [
        "<UP>",
    ];

    protected $downKeys = [
        "<DOWN>",
    ];

    protected $selectKeys = [
        "<ENTER>",
        " ",
    ];

    protected $hasFocus = false;

    protected $focusedIndex = 0;

    protected $cycle = false;

    protected $col = -1;
    protected $row = -1;
    protected $height = -1;
    protected $width = -1;

    public function __construct(int $col = -1, int $row = -1) {
        $this->col = $col;
        $this->row = $row;
        $this->init_observable();
        $this->width = -1;
    }

    public function setKeys(array $upArray, array $downArray, array $selectArray) {
        $this->upKeys = $upArray;
        $this->downKeys = $downArray;
        $this->selectKeys = $selectArray;
    }

    public function handleInput( $event ) {

        $this->render();
        $key = $event->getData("key");

        if (in_array($key, $this->upKeys)) {
            $this->moveUp();
            $this->render();
        }

        if (in_array($key, $this->downKeys)) {
            $this->moveDown();
            $this->render();
        }

        if (in_array($key, $this->selectKeys)) {
            $this->select();
            $this->render();
        }
    }

    public function moveUp() {
        $this->focusedIndex --;
        if($this->focusedIndex < 0) {
            if($this->cycle) {
                end($this->items); // move pointer to end to find out key
                $this->focusedIndex = key($this->items);
            } else {
                $this->focusedIndex = 0;
            }
        }
    }

    public function moveDown() {
        $this->focusedIndex ++;
        end($this->items); // move pointer to end to find out key
        if($this->focusedIndex > key($this->items)) {
            if($this->cycle) {
                $this->focusedIndex = 0;
            } else {
                $this->focusedIndex = key($this->items);
            }
        }
    }

    public function select() {
        $result = $this->emit("input", ["bus"=>$this->bus, "target"=>$this, "item"=>$this->items[$this->focusedIndex], "value"=>$this->items[$this->focusedIndex]->getValue(), "key"=>$this->focusedIndex ]);
    }

    public function add(ListItem $item) {
        $this->items[] = $item;
    }

    public function render() {
        if ( $this->col == -1 ) {
            $this->positionAtCursor();
        }

        if ( $this->height == -1 ) {
            $this->height = 10;
        }

        if ( $this->width == -1 ) {
            $this->width = 25;
        }

        $this->setColors();

        for ( $i = 0; $i <= $this->height; $i++) {
            Cursor::move( $this->col, $this->row+$i );
            if ( isset($this->items[$i]) ) {
                if ( $i == $this->focusedIndex ) {
                    Terminal::underline();
                }
                $this->items[$i]->render();
                Terminal::normal();

            }
        }

        $this->resetColors();
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

}

/*
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
        $result = $this->emit("focus", ["bus"=>$this->bus, "target"=>$this]);
    }

    public function blur() {
        $this->hasFocus = false;
        $this->render();
        $result = $this->emit("blur", ["bus"=>$this->bus, "target"=>$this]);
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

    public function handleInput( $event ) {

        $this->render();
        $key = $event->getData("key");

        if ($key == "<DOWN>") {
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

    public function getValue() {
        return $this->str->getValue();
    }

}

 */
