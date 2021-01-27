<?php
namespace SLGTerm;

require_once(__DIR__."/TraitObservable.php");
require_once(__DIR__."/TraitHasColRow.php");
require_once(__DIR__."/TraitHasWidth.php");
require_once(__DIR__."/TraitHasHeight.php");

class ListInput {

    use Observable, HasColRow, HasWidth, HasHeight;

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

    protected $style = null;
    protected $styleFocused = null;
    protected $styleSelectedItem = null;
    protected $styleSelectedItemFocused = null;

    protected $items = [];

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

        // Selected element in list
        $this->styleSelectedItem = new Style('black', 'white', 235, 250);
    }

    public function setKeys(array $upArray = null, array $downArray = null, array $selectArray = null) {
        if (! is_null($upArray) ) {
            $this->upKeys = $upArray;
        }
        if (! is_null($downArray) ) {
            $this->downKeys = $downArray;
        }
        if (! is_null($selectArray) ) {
            $this->selectKeys = $selectArray;
        }
    }

    public function handleInput( $event ) {
        $this->emit("key", $event->getData());

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
                $this->offset = max(0, count($this->items)-($this->getHeight()));
            } else {
                $this->focusedIndex = 0;
            }
        }
        if($this->offset > $this->focusedIndex) {
            $this->offset = min(count($this->items), $this->offset-1);
        }
        $result = $this->emit("input", ["bus"=>$this->bus, "target"=>$this, "item"=>$this->getFocusedItem(), "value"=>$this->getValue(), "key"=>$this->focusedIndex ]);
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
        if( ($this->offset + ($this->getHeight() - 1) ) < $this->focusedIndex) {
            $this->offset = max(0, $this->focusedIndex-($this->getHeight()-1) );
        }
        $result = $this->emit("input", ["bus"=>$this->bus, "target"=>$this, "item"=>$this->getFocusedItem(), "value"=>$this->getValue(), "key"=>$this->focusedIndex ]);
    }

    public function getFocusedItem() {
        return empty($this->items) ? null : $this->items[$this->focusedIndex];
    }

    public function getValue() {
        return empty($this->items) ? null : $this->items[$this->focusedIndex]->getValue();
    }

    public function select() {
        $result = $this->emit("selected", ["bus"=>$this->bus, "target"=>$this, "item"=>$this->getFocusedItem(), "value"=>$this->getValue(), "key"=>$this->focusedIndex ]);
    }

    public function add(ListItem $item) {
        $this->items[] = $item;
        $item->setList($this);
    }

    public function render() {
        if ( is_null($this->getCol()) ) {
            $this->positionAtCursor();
        }

        if ( is_null($this->getHeight()) ) {
            $this->height = self::DEFAULT_HEIGHT;
        }



        for ( $i = 0; $i < $this->getHeight(); $i++) {
            Cursor::move( $this->getCol(), $this->getRow()+$i );
            if ( isset($this->items[$i+$this->offset]) ) {
                if ( ($i + $this->offset ) == $this->focusedIndex ) {
                    Terminal::applyStyle($this->currentStyleSelectedItem());
                } else {
                    Terminal::applyStyle($this->currentStyle());
                }

                $this->items[$i + $this->offset]->render();
            } else {
                Terminal::applyStyle($this->currentStyle());
                Terminal::echo(str_repeat(" ", $this->getWidth()));
            }
        }

        Terminal::resetStyle();
        return $this;
    }


    public function truncate() {
        $this->items = [];
        $this->focusedIndex = 0;
        $result = $this->emit("truncate", ["target"=>$this]);
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

    public function setCycle(bool $cycle) {
        $this->cycle = $cycle;
    }

    public function style(Style $style) {
        $this->style = $style;
        return $this;
    }

    public function styleFocused(Style $style) {
        $this->styleFocused = $style;
        return $this;
    }

    public function styleSelectedItem(Style $style) {
        $this->styleSelectedItem = $style;
        return $this;
    }

    public function styleSelectedItemFocused(Style $style) {
        $this->styleSelectedItemFocused = $style;
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

    protected function currentStyleSelectedItem() {
        if (
            !$this->hasFocus ||
            is_null($this->styleSelectedItemFocused)
        ) {
            return $this->styleSelectedItem;
        } else {
            return $this->styleSelectedItemFocused;
        }
    }

    public function getFocusedIndex() {
        return $this->focusedIndex;
    }

    public function setFocusedIndex(int $index = 0) {
        $this->focusedIndex = $index;
        return $this;
    }
}
