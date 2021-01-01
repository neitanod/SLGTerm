<?php

namespace SLGTerm;

trait Coloreable {

    protected $fgColor = "white";
    protected $bgColor = "black";
    protected $weight  = "normal";

    public function fgColor($color) {
        $this->fgColor = $color;
        return $this;
    }

    public function bgColor($color) {
        $this->bgColor = $color;
        return $this;
    }

    public function bold() {
        $this->weight = 'bold';
        return $this;
    }

    public function dim() {
        $this->weight = 'dim';
        return $this;
    }

    public function normal() {
        $this->weight = 'normal';
        return $this;
    }

    public function setColors() {
        if ( $this->weight === "normal") {
            Terminal::normal();
        } elseif ( $this->weight === "bold") {
            Terminal::bold();
        } elseif ( $this->weight === "dim") {
            Terminal::dim();
        }
        Terminal::fgColor($this->fgColor);
        Terminal::bgColor($this->bgColor);
    }

    public function resetColors() {
        Terminal::normal();
    }

}
