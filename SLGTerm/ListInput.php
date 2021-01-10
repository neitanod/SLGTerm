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

    protected $cycle = true;

    protected $offset = 0;

    protected $col = null;
    protected $row = null;
    protected $height = null;
    protected $width = null;

    const DEFAULT_WIDTH = 25;
    const DEFAULT_HEIGHT = 5;

    public function __construct(int $col = null, int $row = null, int $width = null, int $height = null) {
        $this->col = $col;
        $this->row = $row;
        $this->width = $width;
        $this->height = $height;
        $this->init_observable();
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
                $this->offset = max(0, count($this->items)-($this->height));
            } else {
                $this->focusedIndex = 0;
            }
        }
        if($this->offset > $this->focusedIndex) {
            $this->offset = min(count($this->items), $this->offset-1);
        }
    }

    public function moveDown() {
        $this->focusedIndex ++;
        end($this->items); // move pointer to end to find out key
        if($this->focusedIndex > key($this->items)) {
            if($this->cycle) {
                $this->focusedIndex = 0;
                $this->offset = 0;
            } else {
                $this->focusedIndex = key($this->items);
            }
        }
        if( ($this->offset + ($this->height - 1) ) < $this->focusedIndex) {
            $this->offset = max(0, $this->focusedIndex-($this->height-1) );
        }
    }

    public function select() {
        $result = $this->emit("input", ["bus"=>$this->bus, "target"=>$this, "item"=>$this->items[$this->focusedIndex], "value"=>$this->items[$this->focusedIndex]->getValue(), "key"=>$this->focusedIndex ]);
    }

    public function add(ListItem $item) {
        $this->items[] = $item;
        $item->setList($this);
    }

    public function render() {
        if ( is_null($this->col) ) {
            $this->positionAtCursor();
        }

        if ( is_null($this->height) ) {
            $this->height = self::DEFAULT_HEIGHT;
        }

        if ( is_null($this->width) ) {
            $this->width = self::DEFAULT_WIDTH;
        }

        $this->setColors();

        for ( $i = 0; $i < $this->height; $i++) {
            Cursor::move( $this->col, $this->row+$i );
            if ( isset($this->items[$i+$this->offset]) ) {
                if ( ($i + $this->offset ) == $this->focusedIndex ) {
                    Terminal::underline();
                }
                $this->items[$i + $this->offset]->render();
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

    public function getWidth() {
        return $this->width;
    }

}
