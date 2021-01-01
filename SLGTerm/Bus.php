<?php
namespace SLGTerm;

class Bus {
    protected $listeners;

    public function emit(\SLGTerm\Event $event) {
        if ( !empty( $this->listeners[$event->getType()] ) ) {
            foreach( $this->listeners[$event->getType()] as $key => $callable) {
                $callable($event);
            }
        }
    }

    public function addListener(string $eventType, callable $callable) {
        $this->listeners[$eventType][] = $callable;
    }

    public function removeListener(string $eventType, callable $callable) {
        if ( count( $this->listeners[$eventType] ) ) {
            $this->listeners[$eventType] =
                array_filter(
                    $this->listeners[$eventType] ,
                    function($listener) use ($callable) {
                        return $listener !== $callable;
                    }
                );
        }
    }
}
