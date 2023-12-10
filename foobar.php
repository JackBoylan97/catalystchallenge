<?php

//iterate through 1-100
for($i = 1; $i <= 100; $i++){
    $output = '';

    if($i % 3 === 0){
        $output .= 'foo';
    }

    if($i % 5 === 0){
        $output .= 'bar';
    }

    echo $output ?: $i, ', ';
}
echo PHP_EOL;

//Can also be done with a switch statement

/*
 <?php
for ($i = 1; $i <= 100; $i++) {
    $output = '';

    switch (true) {
        case ($i % 3 === 0 && $i % 5 === 0):
            $output = 'foobar';
            break;
        case ($i % 3 === 0):
            $output = 'foo';
            break;
        case ($i % 5 === 0):
            $output = 'bar';
            break;
        default:
            $output = $i;
    }

    echo $output, ', ';
}

echo PHP_EOL;
?>

 */