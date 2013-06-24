<?php

require_once('../config.php');
include('main.php');
if(ADMIN)
    include('admin.php');

$send['error'] = 0;

switch(@$_POST['p']) {
    case 'aajax':
        $send['html'] = 'Страница не существует.';
        switch(@$_POST['val']) {
            case 'changepass':
                if(!ADMIN) break;
                $send['html'] = show_admin_changepass();
                break;
            case 'admin':
                if(!ADMIN) break;
                $send['html'] = show_admin_page();
                break;
        }
        break;
    case 'login':
        $ip = $_SERVER['REMOTE_ADDR'];
        $q = query("SELECT * FROM `login_log` WHERE `ip`='".$ip."' LIMIT 1");
        $r = mysql_fetch_assoc($q);
        if(!$r)
            query("INSERT INTO `login_log` (`ip`) values ('".$ip."')");
        else {
            if(time() > (strtotime($r['dtime_last']) + 1800)) {
                query("UPDATE `login_log` SET `count`=1,`dtime_last`=CURRENT_TIMESTAMP");
                $r['count'] = 1;
            } else
                query("UPDATE `login_log` SET `count`=`count`+1");
            if($r['count'] > 4) {
                $send['error'] = 1;
                $send['text'] = 'Превышено количество максимальных попыток. Попробуйте позднее.';
                break;
            }
        }
        $pass = md5(trim(@$_POST['pass']));
        if($pass != PASSWORD) {
            $send['error'] = 1;
            $send['text'] = 'Неверный пароль';
            break;
        }
        $_SESSION['admin'] = 1;
        break;
    case 'logout':
        unset($_SESSION);
        session_destroy();
        break;
    case 'changepass':
        if(!ADMIN) {
            $send['error'] = 1;
            $send['text'] = 'Недостаточно прав';
            break;
        }
        if(!isset($_POST['old']) || !isset($_POST['new']) || !isset($_POST['repeat'])) {
            $send['error'] = 1;
            $send['text'] = 'Необходимо заполнить все поля';
            break;
        }
        $pass = md5(trim($_POST['old']));
        if($pass != PASSWORD) {
            $send['error'] = 1;
            $send['text'] = 'Неверный старый пароль';
            break;
        }
        if($_POST['old'] == $_POST['new']) {
            $send['error'] = 1;
            $send['text'] = 'Новый и старый пароли не должны совпадать';
            break;
        }
        if($_POST['new'] != $_POST['repeat']) {
            $send['error'] = 1;
            $send['text'] = 'Новые пароли не совпадают';
            break;
        }
        query("UPDATE `setup` SET `password`='".md5($_POST['new'])."'");
        break;
    case 'page_name_add':
        if(!ADMIN) {
            $send['error'] = 1;
            $send['text'] = 'Недостаточно прав';
            break;
        }
        if(!preg_match(REGEXP_PAGENAME, @$_POST['name'])) {
            $send['error'] = 1;
            $send['text'] = 'Название содержит недопустимые символы';
            break;
        }
        switch(@$_POST['place']) {
            case 'top': $place = 'top'; break;
            case 'left': $place = 'left'; break;
            default: $place = '';
        }
        $name = htmlspecialchars($_POST['name'], ENT_QUOTES);
        $q = query("SELECT
                        IFNULL(MAX(`sort`)+1,0) AS `sort`
                    FROM `pages`
                    WHERE place='".$place."'");
        $r = mysql_fetch_assoc($q);
        query("INSERT INTO `pages`
                (`name`, `place`,`sort`)
               VALUES
                ('".$name."','".$place."',".$r['sort'].")");
        $q = query("SELECT * FROM `pages` ORDER BY `id` DESC LIMIT 1");
        $send['html'] = get_pageitem_for_spisok(mysql_fetch_assoc($q));
        $send['links_top'] = links_top();
        $send['links_left'] = _left();
        break;
    case 'menutop_sort':
        if(!ADMIN) break;
        $sort = explode(':',$_POST['arr']);
        for($n = 0; $n < count($sort); $n++)
            query("UPDATE `pages` SET `sort`=".$n." WHERE `id`=".intval($sort[$n]));
        $send['links_top'] = links_top();
        $send['links_left'] = _left();
        break;
    case 'page_show_check':
        if(!ADMIN) break;
        if(!preg_match(REGEXP_NUMERIC, @$_POST['id'])) break;
        if(!preg_match(REGEXP_BOOL, @$_POST['val'])) break;
        query("UPDATE `pages` SET `access`=".intval($_POST['val'])." WHERE `id`=".intval($_POST['id']));
        $send['links_top'] = links_top();
        $send['links_left'] = _left();
        break;
    case 'set_as_galery':
        $q = query("SELECT * FROM `pages` WHERE `galery`=1 LIMIT 1");
        if(mysql_num_rows($q))
            break;
        if(!preg_match(REGEXP_NUMERIC, @$_POST['id'])) break;
        $id = intval($_POST['id']);
        $q = query("SELECT * FROM `pages` WHERE `id`=".$id." LIMIT 1");
        $r = mysql_fetch_assoc($q);
        if($r['place'] != 'top')
            break;
        query("UPDATE `pages` SET `galery`=1 WHERE `id`=".$id);
        break;
    case 'galery_catalog_add':
        if(!ADMIN) {
            $send['error'] = 1;
            $send['text'] = 'Недостаточно прав';
            break;
        }
        if(!preg_match(REGEXP_PAGENAME, @$_POST['name'])) {
            $send['error'] = 1;
            $send['text'] = 'Название содержит недопустимые символы';
            break;
        }
        $name = htmlspecialchars($_POST['name'], ENT_QUOTES);
        $about = htmlspecialchars($_POST['about'], ENT_QUOTES);
        $q = query("SELECT IFNULL(MAX(`sort`)+1,0) AS `sort` FROM `galery_catalogs`");
        $r = mysql_fetch_assoc($q);
        query("INSERT INTO `galery_catalogs`
                (`name`,`about`,`sort`,`dtime_add`)
               VALUES
                ('".$name."','".$about."',".$r['sort'].",CURRENT_TIMESTAMP)");
        $q = query("SELECT * FROM `galery_catalogs` ORDER BY `id` DESC LIMIT 1");
        $send['html'] = admin_galery_item(mysql_fetch_assoc($q));
        break;
    case 'galery_sort':
        if(!ADMIN) break;
        $sort = explode(':',$_POST['arr']);
        for($n = 0; $n < count($sort); $n++)
            query("UPDATE `galery_catalogs` SET `sort`=".$n." WHERE `id`=".intval($sort[$n]));
        break;
    case 'galery_catalog_access':
        if(!ADMIN) break;
        if(!preg_match(REGEXP_NUMERIC, @$_POST['id'])) break;
        if(!preg_match(REGEXP_BOOL, @$_POST['val'])) break;
        query("UPDATE `galery_catalogs` SET `access`=".intval($_POST['val'])." WHERE `id`=".intval($_POST['id']));
        break;
    case 'galery_catalog_edit':
        if(!ADMIN) break;
        if(!preg_match(REGEXP_NUMERIC, @$_POST['id'])) break;
        if(!preg_match(REGEXP_PAGENAME, @$_POST['name'])) {
            $send['error'] = 1;
            $send['text'] = 'Название содержит недопустимые символы';
            break;
        }
        if(!preg_match(REGEXP_BOOL, @$_POST['del'])) break;
        if(intval(@$_POST['del']) == 1) {
            query("DELETE FROM `galery_catalogs` WHERE `id`=".intval($_POST['id']));
            query("DELETE FROM `galery_images` WHERE `catalog_id`=".intval($_POST['id']));
        } else {
            $name = htmlspecialchars($_POST['name'], ENT_QUOTES);
            $about = htmlspecialchars($_POST['about'], ENT_QUOTES);
            query("UPDATE `galery_catalogs`
                   SET `name`='".$name."',`about`='".$about."'
                   WHERE `id`=".intval($_POST['id']));
        }
        break;
    case 'galery_images_sort':
        if(!ADMIN) break;
        $sort = explode(':',$_POST['arr']);
        for($n = 0; $n < count($sort); $n++)
            query("UPDATE `galery_images` SET `sort`=".$n." WHERE `id`=".intval($sort[$n]));
        $q = query("SELECT `catalog_id` FROM `galery_images` WHERE `id`=".intval($sort[0])." LIMIT 1");
        $r = mysql_fetch_assoc($q);
        adminGaleryCountAndCoverSet($r['catalog_id']);
        break;
    case 'galery_last_image_uploaded':
        if(!ADMIN) break;
        $q = query("SELECT * FROM `galery_images` ORDER BY `id` DESC");
        $send['image'] = admin_image_unit(mysql_fetch_assoc($q));
        break;
    case 'image_about_edit':
        if(!ADMIN) break;
        if(!preg_match(REGEXP_NUMERIC, @$_POST['id'])) break;
        $about = trim(htmlspecialchars($_POST['about'], ENT_QUOTES));
        query("UPDATE `galery_images` SET `about`='".$about."' WHERE `id`=".$_POST['id']);
        break;
    case 'image_about_del':
        if(!ADMIN) break;
        if(!preg_match(REGEXP_NUMERIC, @$_POST['id'])) break;
        $q = query("SELECT `catalog_id` FROM `galery_images` WHERE `id`=".$_POST['id']." LIMIT 1");
        $r = mysql_fetch_assoc($q);
        $catalog_id = $r['catalog_id'];
        query("DELETE FROM `galery_images` WHERE `id`=".$_POST['id']);
        adminGaleryCountAndCoverSet($catalog_id);
        break;
    case 'image_about_access':
        if(!ADMIN) break;
        if(!preg_match(REGEXP_NUMERIC, @$_POST['id'])) break;
        if(!preg_match(REGEXP_BOOL, @$_POST['val'])) break;
        $q = query("SELECT `catalog_id` FROM `galery_images` WHERE `id`=".$_POST['id']." LIMIT 1");
        $r = mysql_fetch_assoc($q);
        $catalog_id = $r['catalog_id'];
        query("UPDATE `galery_images` SET `access`='".$_POST['val']."' WHERE `id`=".$_POST['id']);
        adminGaleryCountAndCoverSet($catalog_id);
        break;
    case 'image_about_pageuse':
        if(!ADMIN) break;
        if(!preg_match(REGEXP_NUMERIC, @$_POST['id'])) break;
        if(!preg_match(REGEXP_BOOL, @$_POST['val'])) break;
        query("UPDATE `galery_images` SET `pageuse`='".$_POST['val']."' WHERE `id`=".$_POST['id']);
        break;
    default:
        $send['error'] = 1;
}

echo json_encode($send);
