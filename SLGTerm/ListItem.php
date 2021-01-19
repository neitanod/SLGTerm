<?php
namespace SLGTerm;

class ListItem {

    protected $caption = "";
    protected $value = null;
    protected $parentList = null;

    public function __construct( $caption, $value) {
        $this->caption = $caption;
        $this->value = $value;
    }

    public function render() {
        if( $this->parentList ) {
            $width = $this->parentList->getWidth();
        } else {
            $width = mb_strlen($this->caption);
        }

        Terminal::echoWidth($this->caption, $width);

    }

    public function getValue() {
        return $this->value;
    }

    public function setList(ListInput $list) {
        $this->parentList = $list;
    }

}
