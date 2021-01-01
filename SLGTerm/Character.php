<?php
namespace SLGTerm;

class Character {
    protected $sequence = [];
    protected $name = "";

    public function __construct($name, $sequence) {
        $this->name = $name;
        $this->sequence = $sequence;
    }

    public function getSequence() {
        return $this->sequence;
    }

    public function getName() {
        return $this->name;
    }

    public function __toString() {
        return $this->getName();
    }
}
