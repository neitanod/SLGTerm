<?php
namespace SLGTerm;

trait HasWidth {

    protected $width;

    protected function getWidth() {
        if (
            is_null($this->width) &&
            defined('self::DEFAULT_WIDTH')
        ) {
            $this->width = self::DEFAULT_WIDTH;
        }
        return $this->width;
    }

    public function setWidth(int $width) {
        $this->width = $width;
        return $this;
    }

}

