<?php
require_once('config.php');
require_once('view/main.php');

if(!isset($_GET['p'])) {
    $_GET['p'] = 'site';
    $_GET['id'] = 1;
}

$title = 'Страница не существует';
$content = 'Страница не существует';
$description = 'Наша компания занимается сварочными работами в Архангельской области, в городах Няндома, Коноша, Каргополь, Вельск.';
$keywords = 'Няндома, Коноша, Вельск, Каргополь, сварочные работы, металлопродукция, аргонная сварка';

switch($_GET['p']) {
    case 'site':
        if(!preg_match(REGEXP_NUMERIC, @$_GET['id'])) break;
        $content = show_page(intval($_GET['id']));
        break;
    case 'galery':
        $content = go_galery();
        break;
    case 'login':
        if(ADMIN)
            header('Location:http://'.DOMAIN.'/admin');
        $title = 'Вход на сайт';
        $content = show_login();
        break;
    case 'admin':
        if(!ADMIN)
            header('Location:http://'.DOMAIN.'/login');
        include('view/admin.php');
        $title = 'Администрирование';
        $content = show_admin_page();
        break;
}


_header('Сварпром - '.$title);
_logo();
_menuTop();
_center(_left($_GET['p'], intval(@$_GET['id'])), $content);
_footer();

mysql_close();

echo $html;
//phpinfo();