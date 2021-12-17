<?php

function dd(...$args)
{
    foreach ($args as $arg) {
        var_dump($arg);
    }

    exit();
}

function dump($var, ...$moreVars)
{
    var_dump($var);

    foreach ($moreVars as $v) {
        var_dump($v);
    }
}
