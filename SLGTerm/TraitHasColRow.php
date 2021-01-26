<?php
namespace SLGTerm;

trait HasColRow {

    protected $col;
    protected $row;

    protected function getCol() {
        return $this->col;
    }

    protected function getRow() {
        return $this->row;
    }

    public function setCol(int $col) {
        $this->col = $col;
        return $this;
    }

    public function setRow(int $row) {
        $this->row = $row;
        return $this;
    }

}
