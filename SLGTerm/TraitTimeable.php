<?php
namespace SLGTerm;

trait Timeable {

    protected $timers = [];

    public function onTimeout(int $milliseconds, callable $callback) {
        $this->bus->addListener("timer", $callback);
        return $this;
    }

    public function onInterval(int $milliseconds, callable $callback) {
        $this->bus->addListener("timer", $callback);
        return $this;
    }

    public function emitIntervals( $data ) {

return;


        foreach( $this->timers as $k => $timer ) {

            // if timer[due] in the past
            $this->emit("timer", $data );

            // if timer[type] = interval
            //   refresh time[due]
            // else
            //   remove timer from this->timers

        }
    }


}

