# SLGTerm
Simple to use graphical widgets for the Linux terminal written in PHP.

Beware: This project is in its early stages.  It has not been used in production yet.

This library helps you create simple screens ("forms") with widgets to be used in the Linux console (locally or via SSH).

Example:

![Demo](https://publish.ip1.cc/storage/uploads/rArc6b9ZBwhDDiqgQWyGRsf6eMmXT2hodejSnszF.gif)

Another example:

![Demo](https://publish.ip1.cc/storage/uploads/DBdfRfBnXV1uZgNJEIDlJjIfQtEvVjQe43C3sZ73.gif)

<source>
    return main();

    function main() {
        // Save screen and restore at the end.
        $original_cursor_position = Cursor::getPosition();
        Terminal::saveContents();

        $app = create_name_form();

        $app->focus();

        $user_name = $app->nameField->getValue();

        Terminal::restoreContents();
        Cursor::setPosition($original_cursor_position);

        echo "Hello ".$user_name."!!\n";

        return 0;
    }

    function create_name_form() {

        $app = new Form();

        $StatusBar = (new Label("", 0, Terminal::rows(), Terminal::cols(), Terminal::cols()))
            ->bgColor("green")
            ->fgColor("black");

        $StatusBar2 = (new Label("", 0, Terminal::rows()-1, Terminal::cols(), Terminal::cols()))
            ->bgColor("white")
            ->fgColor("green");

        $app->on("key", function( $event ) use ($StatusBar) {
            $key = $event->getData('key');
            $StatusBar->setValue("key: ".$key." ".json_encode($event->getData()))
                ->render();
        });

        $app->on("key", function( $event ) {
            $key = $event->getData('key');
            if($key == "<esc>") {
                $event->getData('controller')->exit = true;
                // setting exit to true will break the loop on next iteration
            }
        });

        $Label1 = (new Label(" Enter your name: ", 3, 3))
            ->bgColor("green")
            ->fgColor("black");

        $TextInput1 = (new TextInput( 22, 3))
            ->bgColor("cyan");
            //->width(25);

        $TextInput1->on("input", function( $event ) use ($StatusBar2, $TextInput1) {
            $StatusBar2->setValue($event->getData('value'))->render();
            $TextInput1->render();
        });

        $app->addWidget($TextInput1);
        $app->addWidget($Label1);

        $app->nameField = $TextInput1;

        return $app;

    }

</source>
