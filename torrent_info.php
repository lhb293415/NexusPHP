<?php

use Rhilip\Bencode\Bencode;

require "include/bittorrent.php";

if (!function_exists('is_indexed_array')) {
    /** 索引数组：所有键名都为数值型，注意字符串类型的数字键名会被转换为数值型。
     * 判断数组是否为索引数组
     * @param array $arr
     * @return bool
     */
    function is_indexed_array(array $arr): bool
    {
        if (is_array($arr)) {
            return count(array_filter(array_keys($arr), 'is_string')) === 0;
        }
        return false;
    }
}

function torrent_structure_builder($array, $parent = "")
{
    $ret = '';
    foreach ($array as $item => $value) {
        $value_length = strlen(Bencode::encode($value));
        if (is_iterable($value)) {  // It may `dictionary` or `list`
            $type = is_indexed_array($value) ? 'list' : 'dictionary';
            $ret .= "<li><div align='left' class='" . $type . "'><a href='javascript:void(0);' onclick='$(this).parent().next(\"ul\").toggle()'> + <span class=title>[" . $item . "]</span> <span class='icon'>(" . ucfirst($type) . ")</span> <span class=length>[" . $value_length . "]</span></a></div>";
            $ret .= "<ul style='display:none'>" . torrent_structure_builder($value, $item) . "</ul></li>";
        } else { // It may `interger` or `string`
            $type = is_integer($value) ? 'integer' : 'string';
            $value = ($parent == 'info' && $item == 'pieces') ? "0x" . bin2hex(substr($value, 0, 25)) . "..." : $value;  // Cut the info pieces....
            $ret .= "<li><div align=left class=" . $type . "> - <span class=title>[" . $item . "]</span> <span class=icon>(" . ucfirst($type) . ")</span> <span class=length>[" . $value_length . "]</span>: <span class=value>" . $value . "</span></div></li>";
        }
    }
    return $ret;
}

dbconn();

loggedinorreturn();

if (get_user_class() < $torrentstructure_class) {
    permissiondenied();
}

$id = (int)$_GET["id"];

if (!$id) {
    httperr();
}

$res = \NexusPHP\Components\Database::query("SELECT name FROM torrents WHERE id = ".\NexusPHP\Components\Database::escape($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);

$fn = "$torrent_dir/$id.torrent";

if (!$row || !is_file($fn) || !is_readable($fn)) {
    httperr();
}

// Standard html headers
stdhead("Torrent Info");
?>

<style type="text/css">
    #torrent-structure ul{margin-left:15px}
    #torrent-structure ul,li{list-style-type:none;color:#000;padding-inline-start:0}
    #torrent-structure li div.string{padding:3px}
    #torrent-structure li div.integer{padding:3px}
    #torrent-structure li div.dictionary{padding:3px}
    #torrent-structure li div.list{padding:3px}
    #torrent-structure li div.string span.icon{color:#090;padding:2px}
    #torrent-structure li div.integer span.icon{color:#990;padding:2px}
    #torrent-structure li div.dictionary span.icon{color:#909;padding:2px}
    #torrent-structure li div.list span.icon{color:#009;padding:2px}
    #torrent-structure li span.title{font-weight:bold}
</style>

<?php

begin_main_frame();

$dict = Bencode::load($fn);
print("<div align=center><h1>$row[name]</h1>");  // Heading
print("<table width=750 border=1 cellspacing=0 cellpadding=5><td>");  // Start table
echo "<ul id='torrent-structure'>";
echo torrent_structure_builder(['root' => $dict]);
echo "</ul>";
print("</td></table>");  // End table
// Standard html footers
end_main_frame();
stdfoot();
