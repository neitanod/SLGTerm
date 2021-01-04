<?php
namespace SLGTerm;

class Box {

    protected $set = [
        'basic' => [
            "+-++",
            "| ||",
            "+-++",
            "+-++",
        ],
        'single' => [
            '"\u250C\u2500\u252C\u2510"',
            '"\u2502 \u2502\u2502"',
            '"\u251C\u2500\u253C\u2524"',
            '"\u2514\u2500\u2534\u2518"',
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

    protected $useSet = "single";

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
        Terminal::echoAt($this->col, $this->row, $this->border(0,0).str_repeat($this->border(0,1), $this->width-2). $this->border(0,3));

        for(
            $row = $this->row+2;
            $row < ($this->row+$this->height);
            $row++
            ) {
            Terminal::echoAt($this->col, $row, $this->border(1,0).str_repeat($this->border(1,1), $this->width-2).$this->border(1,3));

        }

        Terminal::echoAt($this->col, $this->row+$this->height, $this->border(3,0).str_repeat($this->border(3,1), $this->width-2).$this->border(3,3));
    }

    protected function border($row, $col) {
        return mb_substr(json_decode($this->set[$this->useSet][$row]),$col,1);
    }
}
