<?php
namespace SLGTerm;

class EditableString {
    protected $value = "";
    protected $cursorPosition = 0;

    public function __construct(string $value = null) {
        if( !is_null($value) ) {
            $this->setValue($value);
        }
        return $this;
    }

    public function setValue(string $value) {
        $this->value = $value;
        return $this;
    }

    public function getValue() {
        return $this->value;
    }

    public function insert(string $chars) {
        $this->value =
            mb_substr($this->value, 0, $this->cursorPosition) .
            $chars .
            mb_substr($this->value, $this->cursorPosition);
        return $this;
    }

    public function remove(int $count) {
        $from = 0;
        $to = $this->cursorPosition;
        $from2 = $this->cursorPosition;
        $to2 = $this->length();

        if($count < 0) {
            $to = max($to-1, 0);
        }

        if($count > 0) {
            $from2 = min($from2+1, $this->length());
        }

        $this->value =
            mb_substr($this->value, $from, $to) .
            mb_substr($this->value, $from2, $to2);
        return $this;
    }

    public function advanceCursor() {
        if ( $this->cursorPosition < $this->length() ) {
            $this->cursorPosition++;
            return true;
        } else {
            return false;
        }
    }

    public function retrocedeCursor() {
        if ( $this->cursorPosition > 0 ) {
            $this->cursorPosition--;
            return true;
        } else {
            return false;
        }
    }

    public function length() {
        return mb_strlen($this->value);
    }
}
