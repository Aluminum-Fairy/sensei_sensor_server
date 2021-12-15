<?php
preg_match('|' . dirname($_SERVER["SCRIPT_NAME"]) . '/([\w%/]*)|', $_SERVER["REQUEST_URI"], $matches);
$paths = explode('/', $matches[1]);
$file = array_shift($paths);

$file_path = './' . $file . '.php';
if(file_exists($file_path)){

}else{
    echo "notfound";
    echo $file_path;
}