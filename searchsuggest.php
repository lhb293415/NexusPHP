<?php
require "include/bittorrent.php";
dbconn();
if (isset($_GET['q']) && $_GET['q'] != '') {
    $searchstr = trim($_GET['q']);
    
    $suggest_query = \NexusPHP\Components\Database::query("SELECT keywords AS suggest, COUNT(*) AS count FROM suggest WHERE keywords LIKE " . \NexusPHP\Components\Database::escape($searchstr . "%")." GROUP BY keywords ORDER BY count DESC, keywords DESC LIMIT 10");
    $result = array($searchstr, array(), array());
    while ($suggest = mysqli_fetch_array($suggest_query)) {
        if (strlen($suggest['suggest']) > 25) {
            continue;
        }
        $result[1][] = $suggest['suggest'];
        $result[2][] = $suggest['count']." times";
        $i++;
        if ($i >= 5) {
            break;
        }
    }
    echo json_encode($result);
}
