<?php
// Copyright (C) 2018 JLG Technologies, LLC.  This code is licensed under lgpl (and other licenses upon request).  See license.txt
// Written by gr@gr5.org

// this looks for files called phpcc_* in /tmp/ folder.  It then merges the code
// coverage info together in memory.
// Next it looks for the mentioned files in /inv/php/ and turns them into pretty html
// by color coding tested lines with red/green background
?>

<head><style>
    td {
    	white-space: pre;
    }
</style></head>
<body>

<?php

function merge(&$result, &$input)
{
    foreach($input as $fname=>$lines)
    {
        if (isset($result[$fname]) == false)
        {
            $result[$fname]=$lines;
            continue;
        }
        foreach($lines as $line_number=>$value)
        {
            if (isset($result[$fname][$line_number]) == false)
                $result[$fname][$line_number] = $value;
            else
            {
                // 1 has highest priority, then -1, finally 0
                // save if r==0 or v==1
                
                if ($value==1 || $result[$fname][$line_number]== 0)
                    $result[$fname][$line_number] = $value;
            }
        }
    }
}


$file_names = scandir("/tmp");


$data = null;

foreach($file_names as $file_name)
{
    if (strpos($file_name,"phpcc_") !== 0)
        continue;
    $f = fopen("/tmp/".$file_name, "r");
    if ($f === false)
    {
        echo "<h1>Fatal error - Couldn't open $file_name </h1>";
        exit;
    }
    // 500000 (500kb limit on read) was chosen because I have to pick something
    // and it seems much larger than any serialized file should be (they are 
    // typically 2kb) and small enough not to blow php memory
    $temp_array = unserialize(fread($f, 500000));
    if ($data === null)
        $data = $temp_array;
    else
    {
        merge($data, $temp_array);
    }
}

foreach($data as $fname=>$lines)
{
    echo "<h1>$fname</h1>\n";
    $f = fopen($fname, "r");
    if ($f === false)
    {
        echo "<h1>Fatal error - Couldn't open $fname </h1>";
        exit;
    }
    $line_number=1;

    echo "<code>";
    echo "<table>\n";

    // a max limit of 1000 characters per line of php code seems reasonable.  
    // I'm forced to put something in the second parameter.
    while(($line = fgets($f, 1000)) !== false)
    {
        $line = htmlentities($line); // converts for example < to &lt;
        echo "<tr><td>$line_number</td>";
        if (isset($lines[$line_number])==false || $lines[$line_number]== -2)
            echo "<td>$line</td>";                // white
        else if ($lines[$line_number]==1)
            echo "<td bgcolor='#dfd'>$line</td>"; // light green
        else if ($lines[$line_number]== -1)
            echo "<td bgcolor='#fdd'>$line</td>"; // light red
        echo "</tr>\n";
        $line_number++;
    }
    echo "</table>\n";
    echo "</code>\n";
}



?>
</body>

