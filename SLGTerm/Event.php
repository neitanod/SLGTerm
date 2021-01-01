<?php
namespace SLGTerm;

class Event {
    protected $type;
    protected $data;

    public function __construct(string $type, $data) {
        $this->type = $type;
        $this->data = $data;
    }

    public function getType() {
        return $this->type;
    }

    public function getData($key = null) {
        return is_null($key) ? $this->data : $this->data[$key];
    }
}
