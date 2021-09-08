<?php
require_once __DIR__."/../config/DevelopMode.php";

function devHeader($e){
    if(!ReleaseMode){
        header($e);
    }
}