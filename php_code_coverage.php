<?php
// Copyright (C) 2018 JLG Technologies, LLC.  This code is licensed under lgpl (and other licenses upon request).  See license.txt
// Written by gr@gr5.org

function phpcc_white_list_contains($str)
{
    global $phpcc_white_list_contains;
    $phpcc_white_list_contains = $str;
}

function phpcc_done()
{
    // save code coverage data in a unique file
    global $phpcc_white_list_contains;
    $phpcc_array = xdebug_get_code_coverage();
    
    // remove non white list items
    if ($phpcc_white_list_contains!="")
    {
        foreach($phpcc_array as $key=>$value)
        {
            if (stripos($key,$phpcc_white_list_contains)===false)
                unset($phpcc_array[$key]);
        }
    }
    
    // save to file in tmp folder
    $f = fopen(tempnam("/tmp","phpcc_"),"w");
    if ($f === false)
    {
        echo "<h1>Fatal error - Couldn't open file in /tmp/ folder</h1>";
        exit;
    }
    
    $bytes = fwrite($f, serialize($phpcc_array));
    if ($bytes === false)
    {
        echo "<h1>Fatal error - Couldn't write to file in /tmp/ folder</h1>";
        exit;
    }
    fclose($f);
    
    xdebug_stop_code_coverage();
}

$phpcc_white_list_contains="";
register_shutdown_function("phpcc_done");

xdebug_start_code_coverage(XDEBUG_CC_UNUSED);

?>
