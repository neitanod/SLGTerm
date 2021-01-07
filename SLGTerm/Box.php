<?php
namespace SLGTerm;

require_once("TraitColoreable.php");

class Box {

    use Coloreable;

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
        int $width = -1,
        int $height = -1
    ) {
        $this->col = $col;
        $this->row = $row;

        if($width == -1) {
            $this->width = Terminal::cols() - $col;
        } else {
            $this->width = $width;
        }

        if($height == -1) {
            $this->height = Terminal::rows() - $row;
        } else{
            $this->height = $height;
        }

    }

    public function render() {

        Cursor::move($this->col, $this->row);

        $this->setColors();

        Terminal::echo($this->border(0,0).str_repeat($this->border(0,1), $this->width-2). $this->border(0,3));

        for(
            $row = $this->row+2;
            $row < ($this->row+$this->height);
            $row++
            ) {

            Cursor::move($this->col, $row);
            $this->setColors();
            Terminal::echo($this->border(1,0).str_repeat($this->border(1,1), $this->width-2).$this->border(1,3));

        }

            Cursor::move($this->col, $this->row+$this->height);
            $this->setColors();
            Terminal::echo($this->border(3,0).str_repeat($this->border(3,1), $this->width-2).$this->border(3,3));

    }

    protected function border($row, $col) {
        return ($this->set[$this->useSet][$row])[$col];
    }
}
