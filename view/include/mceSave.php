<?php
/*
Ошибки:
0 - неизвестная ошибка
1 - недостаточно прав
2 - название содержит недопустимые символы

9 - ошибка данных
*/
require_once('../../config.php');
if(!ADMIN) {
    setcookie('mceSave', 'error_1', time() + 3600, "/");
    exit;
}

require_once('../admin.php');

$cookie = 'error_0';

switch(@$_POST['p']) {
    case 'pageedit_save':
        if(!preg_match(REGEXP_NUMERIC, @$_POST['id'])) {
            $cookie = 'error_9';
            break;
        }
        if(!preg_match(REGEXP_PAGENAME, @$_POST['name'])) {
            $cookie = 'error_2';
            break;
        }
        if(@$_POST['del'] == 'on') {
            query("DELETE FROM `pages` WHERE `id`=".intval($_POST['id']));
            $cookie = "deleted";
        } else {
            query("UPDATE `pages`
                   SET `name`='".htmlspecialchars($_POST['name'], ENT_QUOTES)."',
                       `txt`='".htmlspecialchars($_POST['txt'], ENT_QUOTES)."'
                   WHERE `id`=".intval($_POST['id']));
            $cookie = "success";
        }
        break;
    case 'logotext_save':
        $txt = htmlspecialchars($_POST['txt'], ENT_QUOTES);
        query("UPDATE `setup` SET `logotext`='".$txt."' LIMIT 1");
        $cookie = "success";
        break;
}

setcookie('mceSave', $cookie, time() + 3600, "/");
