<?php

require_once __DIR__ . "/../config/Config.php";

class LogClass
{
    protected string $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function Systemlog($message, $value)
    {
        if (is_null($this->filePath)) {
            $this->filePath = get_class($this);
        }
        $fp = fopen(LogFilePath, 'a');
        fputs($fp, "PHP " . phpversion() . " " . date("Y/m/d H:i:s") . " [" . basename($this->filePath) . "] : [$message] :\n");
        if (!is_string($value) && !is_numeric($value)) {
            ob_start();
            var_dump($value);
            ob_end_clean();
            fputs($fp, ob_get_contents() . "\n\n");
        } else {
            fputs($fp, $value . "\n\n");
        }
        fclose($fp);
    }
}
