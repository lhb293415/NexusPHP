<?php
/*
+--------------------------------------------------------------------------
|   MySQL driven FAQ version 1.1 Beta
|   ========================================
|   by avataru
|   (c) 2002 - 2005 avataru
|   http://www.avataru.net
|   ========================================
|   Web: http://www.avataru.net
|   Release: 1/9/2005 1:03 AM
|   Email: avataru@avataru.net
|   Tracker: http://www.sharereactor.ro
+---------------------------------------------------------------------------
|
|   > FAQ Management actions
|   > Written by avataru
|   > Date started: 1/7/2005
|
+--------------------------------------------------------------------------
*/

require "include/bittorrent.php";
dbconn();
loggedinorreturn();

if (get_user_class() < UC_ADMINISTRATOR) {
    stderr("Error", "Only Administrators and above can modify the FAQ, sorry.");
}

//stdhead("FAQ Management");

// ACTION: reorder - reorder sections and items
if ($_GET[action] == "reorder") {
    foreach ($_POST[order] as $id => $position) {
        \NexusPHP\Components\Database::query("UPDATE `faq` SET `order`=".\NexusPHP\Components\Database::escape($position)." WHERE id=".\NexusPHP\Components\Database::escape($id)) or sqlerr();
    }
    header("Location: " . get_protocol_prefix() . "$BASEURL/faqmanage.php");
    die;
}

// ACTION: edit - edit a section or item
elseif ($_GET[action] == "edit" && isset($_GET[id])) {
    stdhead("FAQ Management");
    begin_main_frame();
    print("<h1 align=\"center\">Edit Section or Item</h1>");

    $res = \NexusPHP\Components\Database::query("SELECT * FROM faq WHERE id=".\NexusPHP\Components\Database::escape($_GET[id])." LIMIT 1");
    while ($arr = mysqli_fetch_array($res, MYSQL_BOTH)) {
        $arr[question] = htmlspecialchars($arr[question]);
        $arr[answer] = htmlspecialchars($arr[answer]);
        if ($arr[type] == "item") {
            $lang_id = $arr['lang_id'];
            print("<form method=\"post\" action=\"faqactions.php?action=edititem\">");
            print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"10\" align=\"center\">\n");
            print("<tr><td>ID:</td><td>$arr[id] <input type=\"hidden\" name=\"id\" value=\"$arr[id]\" /></td></tr>\n");
            print("<tr><td>Question:</td><td><input style=\"width: 600px;\" type=\"text\" name=\"question\" value=\"$arr[question]\" /></td></tr>\n");
            print("<tr><td style=\"vertical-align: top;\">Answer:</td><td><textarea rows=20 style=\"width: 600px; height=600px;\" name=\"answer\">$arr[answer]</textarea></td></tr>\n");
            if ($arr[flag] == "0") {
                print("<tr><td>Status:</td><td><select name=\"flag\" style=\"width: 110px;\"><option value=\"0\" style=\"color: #FF0000;\" selected=\"selected\">Hidden</option><option value=\"1\" style=\"color: #000000;\">Normal</option><option value=\"2\" style=\"color: #0000FF;\">Updated</option><option value=\"3\" style=\"color: #008000;\">New</option></select></td></tr>");
            } elseif ($arr[flag] == "2") {
                print("<tr><td>Status:</td><td><select name=\"flag\" style=\"width: 110px;\"><option value=\"0\" style=\"color: #FF0000;\">Hidden</option><option value=\"1\" style=\"color: #000000;\">Normal</option><option value=\"2\" style=\"color: #0000FF;\" selected=\"selected\">Updated</option><option value=\"3\" style=\"color: #008000;\">New</option></select></td></tr>");
            } elseif ($arr[flag] == "3") {
                print("<tr><td>Status:</td><td><select name=\"flag\" style=\"width: 110px;\"><option value=\"0\" style=\"color: #FF0000;\">Hidden</option><option value=\"1\" style=\"color: #000000;\">Normal</option><option value=\"2\" style=\"color: #0000FF;\">Updated</option><option value=\"3\" style=\"color: #008000;\" selected=\"selected\">New</option></select></td></tr>");
            } else {
                print("<tr><td>Status:</td><td><select name=\"flag\" style=\"width: 110px;\"><option value=\"0\" style=\"color: #FF0000;\">Hidden</option><option value=\"1\" style=\"color: #000000;\" selected=\"selected\">Normal</option><option value=\"2\" style=\"color: #0000FF;\">Updated</option><option value=\"3\" style=\"color: #008000;\">New</option></select></td></tr>");
            }
            print("<tr><td>Category:</td><td><select style=\"width: 400px;\" name=\"categ\" />");
            $res2 = \NexusPHP\Components\Database::query("SELECT `id`, `question`, `link_id` FROM `faq` WHERE `type`='categ' AND `lang_id` = ".\NexusPHP\Components\Database::escape($lang_id)." ORDER BY `order` ASC");
            while ($arr2 = mysqli_fetch_array($res2, MYSQL_BOTH)) {
                $selected = ($arr2[link_id] == $arr[categ]) ? " selected=\"selected\"" : "";
                print("<option value=\"$arr2[link_id]\"". $selected .">$arr2[question]</option>");
            }
            print("</td></tr>\n");
            print("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"edit\" value=\"Edit\" style=\"width: 60px;\"></td></tr>\n");
            print("</table>");
        } elseif ($arr[type] == "categ") {
            $lang_res = \NexusPHP\Components\Database::query("SELECT lang_name FROM language WHERE id=".\NexusPHP\Components\Database::escape($arr[lang_id])." LIMIT 1");
            if ($lang_arr = mysqli_fetch_array($lang_res)) {
                $lang_name = $lang_arr['lang_name'];
            }
            print("<form method=\"post\" action=\"faqactions.php?action=editsect\">");
            print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"10\" align=\"center\">\n");
            print("<tr><td>ID:</td><td>$arr[id] <input type=\"hidden\" name=\"id\" value=\"$arr[id]\" /></td></tr>\n");
            print("<tr><td>Language:</td><td>$lang_name</td></tr>\n");
            print("<tr><td>Title:</td><td><input style=\"width: 300px;\" type=\"text\" name=\"title\" value=\"$arr[question]\" /></td></tr>\n");
            if ($arr[flag] == "0") {
                print("<tr><td>Status:</td><td><select name=\"flag\" style=\"width: 110px;\"><option value=\"0\" style=\"color: #FF0000;\" selected=\"selected\">Hidden</option><option value=\"1\" style=\"color: #000000;\">Normal</option></select></td></tr>");
            } else {
                print("<tr><td>Status:</td><td><select name=\"flag\" style=\"width: 110px;\"><option value=\"0\" style=\"color: #FF0000;\">Hidden</option><option value=\"1\" style=\"color: #000000;\" selected=\"selected\">Normal</option></select></td></tr>");
            }
            print("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"edit\" value=\"Edit\" style=\"width: 60px;\"></td></tr>\n");
            print("</table>");
        }
    }

    end_main_frame();
    stdfoot();
}

// subACTION: edititem - edit an item
elseif ($_GET[action] == "edititem" && $_POST[id] != null && $_POST[question] != null && $_POST[answer] != null && $_POST[flag] != null && $_POST[categ] != null) {
    $question = $_POST[question];
    $answer = $_POST[answer];
    \NexusPHP\Components\Database::query("UPDATE `faq` SET `question`=".\NexusPHP\Components\Database::escape($question).", `answer`=".\NexusPHP\Components\Database::escape($answer).", `flag`=".\NexusPHP\Components\Database::escape($_POST[flag]).", `categ`=".\NexusPHP\Components\Database::escape($_POST[categ])." WHERE id=".\NexusPHP\Components\Database::escape($_POST[id])) or sqlerr();
    header("Location: " . get_protocol_prefix() . "$BASEURL/faqmanage.php");
    die;
}

// subACTION: editsect - edit a section
elseif ($_GET[action] == "editsect" && $_POST[id] != null && $_POST[title] != null && $_POST[flag] != null) {
    $title = $_POST[title];
    \NexusPHP\Components\Database::query("UPDATE `faq` SET `question`=".\NexusPHP\Components\Database::escape($title).", `answer`='', `flag`=".\NexusPHP\Components\Database::escape($_POST[flag]).", `categ`='0' WHERE id=".\NexusPHP\Components\Database::escape($_POST[id])) or sqlerr();
    header("Location: " . get_protocol_prefix() . "$BASEURL/faqmanage.php");
    die;
}

// ACTION: delete - delete a section or item
elseif ($_GET[action] == "delete" && isset($_GET[id])) {
    if ($_GET[confirm] == "yes") {
        \NexusPHP\Components\Database::query("DELETE FROM `faq` WHERE `id`=".\NexusPHP\Components\Database::escape(0+$_GET[id])." LIMIT 1") or sqlerr();
        header("Location: " . get_protocol_prefix() . "$BASEURL/faqmanage.php");
        die;
    } else {
        stdhead("FAQ Management");
        begin_main_frame();
        print("<h1 align=\"center\">Confirmation required</h1>");
        print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"5\" align=\"center\" width=\"95%\">\n<tr><td align=\"center\">Please click <a href=\"faqactions.php?action=delete&id=$_GET[id]&confirm=yes\">here</a> to confirm.</td></tr>\n</table>\n");
        end_main_frame();
        stdfoot();
    }
}

// ACTION: additem - add a new item
elseif ($_GET[action] == "additem" && $_GET[inid] && $_GET[langid]) {
    stdhead("FAQ Management");
    begin_main_frame();
    print("<h1 align=\"center\">Add Item</h1>");
    print("<form method=\"post\" action=\"faqactions.php?action=addnewitem\">");
    print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"10\" align=\"center\">\n");
    print("<tr><td>Question:</td><td><input style=\"width: 600px;\" type=\"text\" name=\"question\" value=\"\" /></td></tr>\n");
    print("<tr><td style=\"vertical-align: top;\">Answer:</td><td><textarea rows=20 style=\"width: 600px; height=600px;\" name=\"answer\"></textarea></td></tr>\n");
    print("<tr><td>Status:</td><td><select name=\"flag\" style=\"width: 110px;\"><option value=\"0\" style=\"color: #FF0000;\">Hidden</option><option value=\"1\" style=\"color: #000000;\">Normal</option><option value=\"2\" style=\"color: #0000FF;\">Updated</option><option value=\"3\" style=\"color: #008000;\" selected=\"selected\">New</option></select></td></tr>");
    print("<input type=hidden name=categ value=\"".(0+$_GET[inid])."\">");
    print("<input type=hidden name=langid value=\"".(0+$_GET[langid])."\">");
    print("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"Add\" style=\"width: 60px;\"></td></tr>\n");
    print("</table></form>");
    end_main_frame();
    stdfoot();
}

// ACTION: addsection - add a new section
elseif ($_GET[action] == "addsection") {
    stdhead("FAQ Management");
    begin_main_frame();
    print("<h1 align=\"center\">Add Section</h1>");
    print("<form method=\"post\" action=\"faqactions.php?action=addnewsect\">");
    print("<table border=\"1\" cellspacing=\"0\" cellpadding=\"10\" align=\"center\">\n");
    print("<tr><td>Title:</td><td><input style=\"width: 300px;\" type=\"text\" name=\"title\" value=\"\" /></td></tr>\n");
    $s = "<select name=language>";
    $langs = langlist("rule_lang");
    foreach ($langs as $row) {
        if ($row["site_lang_folder"] == $deflang) {
            $se = " selected";
        } else {
            $se = "";
        }
        $s .= "<option value=". $row["id"] . $se. ">" . htmlspecialchars($row["lang_name"]) . "</option>\n";
    }
    $s .= "</select>";
    print("<tr><td>Language:</td><td>".$s."</td></tr>");
    print("<tr><td>Status:</td><td><select name=\"flag\" style=\"width: 110px;\"><option value=\"0\" style=\"color: #FF0000;\">Hidden</option><option value=\"1\" style=\"color: #000000;\" selected=\"selected\">Normal</option></select></td></tr>");
    print("<tr><td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"edit\" value=\"Add\" style=\"width: 60px;\"></td></tr>\n");
    print("</table>");
    end_main_frame();
    stdfoot();
}

// subACTION: addnewitem - add a new item to the db
elseif ($_GET[action] == "addnewitem" && $_POST[question] != null && $_POST[answer] != null) {
    $question = $_POST[question];
    $answer = $_POST[answer];
    $categ = 0+$_POST[categ];
    $langid = 0+$_POST[langid];
    $res = \NexusPHP\Components\Database::query("SELECT MAX(`order`) AS maxorder, MAX(`link_id`) AS maxlinkid FROM `faq` WHERE `type`='item' AND `categ`=".\NexusPHP\Components\Database::escape($categ)." AND lang_id=".\NexusPHP\Components\Database::escape($langid));
    while ($arr = mysqli_fetch_array($res, MYSQL_BOTH)) {
        $order = $arr['maxorder'] + 1;
        $link_id = $arr['maxlinkid']+1;
    }
    \NexusPHP\Components\Database::query("INSERT INTO `faq` (`link_id`, `type`, `lang_id`, `question`, `answer`, `flag`, `categ`, `order`) VALUES ('$link_id', 'item', ".\NexusPHP\Components\Database::escape($langid).", ".\NexusPHP\Components\Database::escape($question).", ".\NexusPHP\Components\Database::escape($answer).", " . \NexusPHP\Components\Database::escape(0+$_POST[flag]) . ", ".\NexusPHP\Components\Database::escape($categ).", ".\NexusPHP\Components\Database::escape($order).")") or sqlerr();
    header("Location: " . get_protocol_prefix() . "$BASEURL/faqmanage.php");
    die;
}

// subACTION: addnewsect - add a new section to the db
elseif ($_GET[action] == "addnewsect" && $_POST[title] != null && $_POST[flag] != null) {
    $title = $_POST[title];
    $language = 0+$_POST['language'];
    $res = \NexusPHP\Components\Database::query("SELECT MAX(`order`) AS maxorder, MAX(`link_id`) AS maxlinkid FROM `faq` WHERE `type`='categ' AND `lang_id` = ".\NexusPHP\Components\Database::escape($language));
    while ($arr = mysqli_fetch_array($res, MYSQL_BOTH)) {
        $order = $arr['maxorder'] + 1;
        $link_id = $arr['maxlinkid']+1;
    }
    \NexusPHP\Components\Database::query("INSERT INTO `faq` (`link_id`,`type`,`lang_id`, `question`, `answer`, `flag`, `categ`, `order`) VALUES (".\NexusPHP\Components\Database::escape($link_id).",'categ', ".\NexusPHP\Components\Database::escape($language).", ".\NexusPHP\Components\Database::escape($title).", '', ".\NexusPHP\Components\Database::escape($_POST[flag]).", '0', ".\NexusPHP\Components\Database::escape($order).")") or sqlerr();
    header("Location: " . get_protocol_prefix() . "$BASEURL/faqmanage.php");
    die;
} else {
    header("Location: " . get_protocol_prefix() . "$BASEURL/faqmanage.php");
    die;
}
