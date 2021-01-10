<?php
namespace SLGTerm;

require_once(__DIR__."/TraitObservable.php");

class Form {

    use Observable;

    protected $widgets = [];
    protected $focused_widget = null;
    protected $show = false;
    protected $parent = null;

    public function __construct() {
        $this->init_observable();
    }

    public function show() {
        $this->show = true;
        $this->render();
    }

    public function hide() {
        $this->show = false;
        if( $this->parent && method_exists($this->parent, "render") ) {
            $this->parent->render();
        }
    }

    public function setParent($parent) {
        $this->parent = $parent;
    }

    public function focus() {

        $this->show();

        Input::readPrepare();

        while($this->show) {
            $chars = [];
            if($chars = Input::read()) {
                //if(!is_array($chars)) $chars = [$chars];
                foreach ( $chars as $char ) {
                    $this->emit( "key", [
                        "key"=>$char,
                        "controller"=>$this
                    ]);

                    if(
                        isset($this->widgets[$this->focused_widget])
                        && is_callable([$this->widgets[$this->focused_widget],"handleInput"])
                    ) {
                        $this->widgets[$this->focused_widget]->handleInput(
                            new Event( 'key', [
                                'key'=>$char,
                                'controller'=>$this
                            ]) );
                    }
                }

            } else {
                // perform your processing here
                // $spinner->advance();
                // time_nanosleep(0, 100 * 1000000);
            }
        }

        Input::readCleanup();
    }

    public function focusWidget(int $index) {
        $success = false;
        $old_focused = $this->focused_widget;
        if(method_exists($this->widgets[$index], "focus")){
            if(!is_null($old_focused) && method_exists($this->widgets[$old_focused], "blur")){
                $this->widgets[$old_focused]->blur();
            }
            $this->widgets[$index]->focus();
            $this->focused_widget = $index;
            $success = true;
        }
        return $success;
    }

    public function focusNext() {
        $old_focused = $this->focused_widget;
        $new_focused = $this->focused_widget;
        $done = false;
        while (!$done) {
            $new_focused++;
            if( $old_focused == $new_focused ) break;
            if( count($this->widgets) <= $new_focused ) {
                $new_focused = 0;
            }
            $done = $this->focusWidget($new_focused);
        }
        return $this;
    }

    public function focusPrevious() {
        $old_focused = $this->focused_widget;
        $new_focused = $this->focused_widget;
        $done = false;
        while (!$done) {
            $new_focused--;
            if( $old_focused == $new_focused ) break;
            if( 0 > $new_focused ) {
                $new_focused = count($this->widgets)-1;
            }
            $done = $this->focusWidget($new_focused);
        }
        return $this;
    }

    public function addWidget($widget) {
        $this->widgets[] = $widget;
        if (is_null($this->focused_widget)) {
            $this->focusWidget(count($this->widgets)-1);
        }
    }

    public  function render() {
        if( !$this->show ) exit;

        foreach($this->widgets as $widget ) {
            $widget->render();
        }

        if ( !is_null($this->focused_widget) && $this->focused_widget < (count($this->widgets)-1 ) ) {
            $this->widgets[$this->focused_widget]->render();
        }
    }
}
