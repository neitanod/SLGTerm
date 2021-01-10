<?php
namespace SLGTerm;

class ListItem {

    protected $caption = "";
    protected $value = null;

    public function __construct( $caption, $value) {
        $this->caption = $caption;
        $this->value = $value;
    }

    public function render() {
        Terminal::echo($this->caption);
    }

    public function getValue() {
        return $this->value;
    }

}
