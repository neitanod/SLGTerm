<?php
namespace SLGTerm;

require_once(__DIR__."/TraitHasColRow.php");
require_once(__DIR__."/TraitHasWidth.php");
require_once(__DIR__."/TraitHasHeight.php");

class Box {

    use HasColRow, HasWidth, HasHeight;

    protected $set = [
        'none' => [
            "    ",
            "    ",
            "    ",
            "    ",
        ],
        'basic' => [
            '+-++',
            '| ||',
            '+-++',
            '+-++',
        ],
        'single' => [
            [ "\u{250C}", "\u{2500}", "\u{252C}", "\u{2510}" ],
            [ "\u{2502}", " ",        "\u{2502}", "\u{2502}" ],
            [ "\u{251C}", "\u{2500}", "\u{253C}", "\u{2524}" ],
            [ "\u{2514}", "\u{2500}", "\u{2534}", "\u{2518}" ],
        ],
        'double' => [
            "+-++",
            "| ||",
            "+-++",
            "+-++",
        ],
        'single_double' => [
            "+-++",
            "| ||",
            "+-++",
            "+-++",
        ],
        'double_single' => [
            "+-++",
            "| ||",
            "+-++",
            "+-++",
        ],
    ];

    protected $useSet = "none";

    public function __construct(
        int $col = 0,
        int $row = 0,
        int $width = null,
        int $height = null
    ) {
        $this->col = $col;
        $this->row = $row;

        if( is_null($width) ) {
            $this->width = Terminal::cols() - $col;
        } else {
            $this->width = $width;
        }

        if( is_null($height) ) {
            $this->height = Terminal::rows() - $row;
        } else{
            $this->height = $height;
        }

    }

    public function render() {

        Cursor::move($this->col, $this->row);

        Terminal::applyStyle($this->style);

        Terminal::echo($this->border(0,0).str_repeat($this->border(0,1), $this->width-2). $this->border(0,3));

        for(
            $row = $this->row+2;
            $row < ($this->row+$this->height);
            $row++
            ) {

            Cursor::move($this->col, $row);
            Terminal::applyStyle($this->style);
            Terminal::echo($this->border(1,0).str_repeat($this->border(1,1), $this->width-2).$this->border(1,3));

        }

            Cursor::move($this->col, $this->row+$this->height);
            Terminal::applyStyle($this->style);
            Terminal::echo($this->border(3,0).str_repeat($this->border(3,1), $this->width-2).$this->border(3,3));

    }

    protected function border($row, $col) {
        return ($this->set[$this->useSet][$row])[$col];
    }

    public function style(Style $style) {
        $this->style = $style;
        return $this;
    }
}
