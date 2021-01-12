<?php

namespace SLGTerm;

class Style {

    protected $style = [
        "fg" => null,
        "bg" => null,
        "fg256" => null,
        "bg256" => null,
        "underline" => false,
        "dim" => false,
        "bold" => false,
        "reverse" => false,
    ];

    public function set(string $name, $value) {
        $this->style[$name] = $value;
        return $this;
    }

    public function get(string $name) {
        return $this->style[$name];
    }

    public function underline($status = true) {
        $this->style['underline'] = (bool)$status;
        return $this;
    }

    public function dim($status = true) {
        $this->style['dim'] = (bool)$status;
        return $this;
    }

    public function bold($status = true) {
        $this->style['bold'] = (bool)$status;
        return $this;
    }

    public function reverse($status = true) {
        $this->style['reverse'] = (bool)$status;
        return $this;
    }

    public function __construct($fg = null, $bg = null, $fg256 = null, $bg256 = null) {
        $this->colors($fg, $bg, $fg256, $bg256);
        return $this;
    }

    public function colors($fg = null, $bg = null, $fg256 = null, $bg256 = null) {

        if( !is_null($fg) ) {
            $this->style['fg'] = $fg;
        }

        if( !is_null($bg) ) {
            $this->style['bg'] = $bg;
        }

        if( !is_null($fg256) ) {
            $this->style['fg256'] = $fg256;
        }

        if( !is_null($bg256) ) {
            $this->style['bg256'] = $bg256;
        }

        return $this;
    }

    public function getFgColorFor($max_colors) {
        if($max_colors == 8 || is_null($this->style['fg256'])) {
            return $this->style['fg'];
        } else {
            return $this->style['fg256'] ?? "white";
        }
    }

    public function getBgColorFor($max_colors) {
        if($max_colors == 8 || is_null($this->style['bg256'])) {
            return $this->style['bg'];
        } else {
            return $this->style['bg256'] ?? "white";
        }
    }

    public function toArray() {
        return $this->style;
    }

    public function fromArray(array $style) {
        $allowed = [
            "fg",
            "bg",
            "fg256",
            "bg256",
            "underline",
            "dim",
            "bold",
            "reverse",
        ];
        $this->style = array_filter(
            $style,
            function ($key) use ($allowed) {
                return in_array($key, $allowed);
            },
            ARRAY_FILTER_USE_KEY
        );
        return $this;
    }
}
