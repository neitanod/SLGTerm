<?php
namespace SLGTerm;

trait HasHeight {

    protected $height;

    protected function getHeight() {
        if (
            is_null($this->height) &&
            defined('self::DEFAULT_HEIGHT')
        ) {
            $this->height = self::DEFAULT_HEIGHT;
        }
        return $this->height;
    }

    public function setHeight(int $height) {
        $this->height = $height;
        return $this;
    }

}

