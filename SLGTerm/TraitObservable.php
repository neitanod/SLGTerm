<?php
namespace SLGTerm;

require_once(__DIR__."/Bus.php");
require_once(__DIR__."/Event.php");

trait Observable {

    protected $bus;

    protected function init_observable() {
        $this->bus = new \SLGTerm\Bus();
    }

    public function on(string $eventType, callable $callback) {
        $this->bus->addListener($eventType, $callback);
        return $this;
    }

    public function off(string $eventType, callable $callback) {
        $this->bus->removeListener($eventType, $callback);
        return $this;
    }

    public function emit($eventType, $data) {
        return $this->bus->emit(new Event($eventType, $data));
    }

}
