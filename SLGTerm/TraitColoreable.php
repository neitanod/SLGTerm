<?php

namespace SLGTerm;

trait Coloreable {

    protected $fgColor = "white";
    protected $fgColor256 = null;
    protected $fgColorFocus = "white";
    protected $fgColorFocus256 = null;
    protected $bgColor = "black";
    protected $bgColor256 = null;
    protected $bgColorFocus = "white";
    protected $bgColorFocus256 = null;
    protected $weight  = "normal";
    protected $underline = false;

    public function fgColor($color) {
        $this->fgColor = $color;
        return $this;
    }

    public function fgColor256(int $color) {
        $this->fgColor256 = $color;
        return $this;
    }

    public function bgColor($color) {
        $this->bgColor = $color;
        return $this;
    }

    public function bgColor256(int $color) {
        $this->bgColor256 = $color;
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
        Terminal::normal();

        if ( $this->weight === "bold") {
            Terminal::bold();
        } elseif ( $this->weight === "dim") {
            Terminal::dim();
        }
        if ( $this->underline ) {
            Terminal::underline();
        }

        if( Terminal::colors() == 8 ||
            is_null($this->fgColor256)
        ){
            Terminal::fgColor($this->fgColor);
        } else {
            Terminal::fgColor($this->fgColor256);
        }

        if( Terminal::colors() == 8 ||
            is_null($this->bgColor256)
        ){
            Terminal::bgColor($this->bgColor);
        } else {
            Terminal::bgColor($this->bgColor256);
        }
    }

    public function resetColors() {
        Terminal::normal();
    }

}
