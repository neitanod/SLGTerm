<?php

namespace SLGTerm;

class Spinner {

    protected $step = 0;
    protected $step_count = 6;

        // protected $steps = '|/-\\|/-\\';
        // protected $steps = [
        //     '⣇', '⡏', '⠟', '⠻',
        //     '⢹', '⣸', '⣴', '⣦'
        // ];
        // protected $steps = [
        //     '⡇', '⠏', '⠛', '⠹',
        //     '⢸', '⣰', '⣤', '⣆'
        // ];

        // Preferred spinner: Braille small
    protected $steps = [
        '⠏', '⠛', '⠹',
        '⠼', '⠶', '⠧',
    ];

    public function __construct(int $col, int $row) {
        $this->row = $row;
        $this->col = $col;
        return $this;
    }

    public function advance() {
        $this->step = ($this->step > $this->step_count-2) ? 0 : $this->step+1;
        $this->render();
        return $this;
    }

    public function render() {
        Terminal::echoAt($this->col, $this->row, $this->steps[$this->step]);
        return $this;
    }

}
