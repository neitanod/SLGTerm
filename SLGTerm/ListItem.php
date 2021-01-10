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

        $this->echoWidth($this->caption, $width);

    }

    protected function echoWidth($string, $width) {
        if (mb_strlen($string) >= $width) {
            Terminal::echo( mb_substr($string, 0, $width) );
        } else {
            Terminal::echo( $string . str_repeat(" ", $width - mb_strlen($string)) );
        }
    }

    public function getValue() {
        return $this->value;
    }

    public function setList(ListInput $list) {
        $this->parentList = $list;
    }

}
