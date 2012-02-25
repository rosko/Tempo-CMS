<?php

interface ITFilesystemWrapper
{
    /**
     * Connects to filesystem. Use array as parameters
     * @return ITFilesystemWrapper
     */
    public static function connect(array $array);
    
}