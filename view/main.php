<?php
function _header($title) {
    global $html, $description, $keywords;
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.
        '<HTML xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">'.
        '<HEAD>'.
            '<meta http-equiv="content-type" content="text/html; charset=utf-8">'.
            (DEBUG == 1 ? '<SCRIPT type="text/javascript" src="http://nyandoma.ru/js/errors.js?'.VERSION.'"></SCRIPT>':'').
            (DEBUG == 1 ? '<SCRIPT type="text/javascript">var T = (new Date()).getTime();</SCRIPT>':'').
            '<SCRIPT type="text/javascript" src="/js/jquery-1.9.1.min.js"></SCRIPT>'.
            '<SCRIPT type="text/javascript">'.
                'var DOMAIN="'.DOMAIN.'",'.
	                'URL = "'.URL.'",'.
                    'VERSION = '.VERSION.';'.
            '</SCRIPT>'.
            '<SCRIPT type="text/javascript" src="/js/global.js?'.VERSION.'"></SCRIPT>'.
            '<LINK href="/css/global.css?'.VERSION.'" rel="stylesheet" type="text/css">'.
            '<TITLE>'.$title.'</TITLE>'.
	        '<meta name="description" content="'.$description.'" />'.
	        '<meta http-equiv="description" content="'.$description.'" />'.
			'<meta name="keywords" content="'.$keywords.'" />'.
			'<meta http-equiv="keywords" content="'.$keywords.'" />'.
	        '<meta name="Resource-type" content="document" />'.
			'<meta name="document-state" content="dynamic" />'.
			'<meta name="Robots" content="index,follow" />'.
        '</HEAD>'.
        '<BODY class="svarprom">'.

        '<DIV id="mainDiv">';
}//_header()

function _logo() {
    global $html;
    $html .= '<table cellpadding="0" cellspacing="0" class="logo">'.
                '<tr><td id="logotext">'.LOGOTEXT.'</td>'.
                    '<td id="logoimg"></td>'.
                '</tr>'.
             '</table>';
}//_logo()

function _menuTop() {
    global $html;
    $html .= '<div id="menu_top">'.
        '<table cellpadding="0" cellspacing="0" width="100%">'.
            '<tr><td class="services_top">Наши услуги</td>'.
                '<td class="bord">'.links_top().'</td>'.
            '</tr>'.
        '</table>'.
    '</div>';
}//menuTop()

function links_top() {
    $q = query("SELECT
                    `id`,`name`,`galery`
                FROM `pages`
                WHERE
                    `place`='top' AND
                    `access`=1
                ORDER BY `sort`");
    $items = '';
    while($r = mysql_fetch_assoc($q)) {
        $pname = $r['galery'] ? 'galery' : 'page'.$r['id'];
        $items .= '<td><a href="http://'.DOMAIN.'/'.$pname.'">'.$r['name'].'</a></td>';
    }
    return '<table cellpadding="0" cellspacing="0" class="buttons"><tr>'.($items ? $items : '<td><a></a></td>').'</tr></table>';
}//links_top()

function _center($menu_left='', $content='') {
    global $html;
    $html .= '<table cellpadding="0" cellspacing="0">'.
        '<tr><td id="left_td">'.$menu_left.'</td>'.
            '<td id="content_td"><div id="content">'.$content.'</div></td></tr>'.
    '</table>';
}//_center()

function _left($p='', $id=0) {
    $q = query("SELECT
                    `id`,`name`
                FROM `pages`
                WHERE
                    `place`='left' AND
                    `access`=1
                ORDER BY `sort`");
    if($p != 'site') $id = 0;
    $send = '<div id="buttons_left">';
    while($r = mysql_fetch_assoc($q))
        $send .= '<a href="'.URL.'/page'.$r['id'].'"'.($r['id'] == $id ? 'class="active"' : '').'>'.$r['name'].'</a>';
    $send .= '</div>';
    return $send;
}//_left()

function _footer() {
    global $html, $sqlQuery;
    $time = DEBUG == 1
                ?
            '<span class="php">'.
                'sql '.$sqlQuery.' :: '.
                'php '.round(microtime(true) - TIME, 3).' :: '.
                'js <span id="js_time"></span>'.
            '</span>'.
            '<SCRIPT type="text/javascript">$("#js_time").html(((new Date()).getTime()-T)/1000);</SCRIPT>'
                :
            '';
    if(ADMIN) {
        $login = '<span class="logined">'.
            '<a href="http://'.DOMAIN.'/admin">Администрирование</a> :: '.
            (@$_GET['p'] == 'site' && intval(@$_GET['id']) > 0 ? '<a href="http://'.DOMAIN.'/admin/page'.intval($_GET['id']).'/edit">Редактировать страницу</a> :: ' : '').
            '<a id="logout">Выход</a> '.
        '</span>';
    } else $login = '<a href="http://'.DOMAIN.'/login" class="admin_login">Вход</a>';
    $html .=
		'<div id="footer">'.
			'<span>сварпром.рф 2013</span> '.
		    '<span id="livecounter"></span>'.
			$login.
			$time.
		'</div>'.
    '</div></BODY></HTML>';
}//_footer()

function show_login() {
    $send = '<div class="login_head">Вход на сайт</div>'.
        '<input type="password" id="login_pass"> '.
        '<button id="login">Войти</button> '.
        '<span class="error"></span>';
    return $send;
}//show_login()

function show_page($id) {
    global $title, $description, $keywords;
    $q = query("SELECT * FROM `pages` WHERE `id`=".$id);
    $r = mysql_fetch_assoc($q);
    if(!$r || !ADMIN && !$r['access'])
        return 'Такой страницы нет.';
    $title = $r['name'];
    if($r['galery'])
        return show_galery($r['name']);
	$txt = htmlspecialchars_decode($r['txt']);
	$strip = strip_tags(nl2br($txt));
	$strip = str_replace('&nbsp;', ' ', $strip);
	$strip = str_replace("\n", ' ', $strip);
	$strip = str_replace("\r", ' ', $strip);
	$strip = str_replace('"', '', $strip);
	$ex = explode('.', $strip);
	$description = preg_replace ('/\s+/', ' ', $r['name'].'. '.$ex[0].'.');
    return '<div id="show_page">'.
                (!$r['access'] ? '<div class="noaccess">Внимание, эта страница доступна только администратору. <a href="http://'.DOMAIN.'/admin/page'.$id.'/edit">Редактировать страницу</a></div>' : '').
                $txt.
           '</div>';
}//show_page()

function go_galery() {
    global $title;
    $q = query("SELECT `id`,`name` FROM `pages` WHERE `access`=1 AND `galery`=1 LIMIT 1");
    $r = mysql_fetch_assoc($q);
    if(!$r)
        return 'Такой страницы нет';
    $_GET['id'] = $r['id'];
    return show_galery($r['name']);
}//go_galery()

function show_galery($name) {
    $q = query("SELECT * FROM `galery_catalogs` WHERE `access`=1 AND `image_access`>0 ORDER BY `sort` ASC");
    $spisok = '';
    while($item = mysql_fetch_assoc($q))
        $spisok .= '<table cellpadding="0" cellspacing=0>'.
            '<tr><td class="image"><img src="'.$item['cover'].'" class="image_show" val="'.$item['id'].'"></td>'.
                '<td>'.
                    '<h2>'.$item['name'].'</h2>'.
                    '<div class="about">'.$item['about'].'</div>'.
                    '<div class="count">Количество изображений: <b>'.$item['image_access'].'</b></div>'.
                    '<div class="update">Последнее обновление: '.FullDataTime($item['dtime_update']).'</div>'.
                '</td>'.
            '</tr>'.
            '</table>';

    $q = query("SELECT * FROM `galery_images` WHERE `access`=1 ORDER BY `sort` ASC");
    $json = array();
    while($r = mysql_fetch_assoc($q)) {
        $arr = (array)json_decode($r['img']);
        $arr['big']->about = $r['about'] ? $r['about'] : $r['name'];
        $json[$r['catalog_id']][] = $arr['big'];
    }
    return '<SCRIPT type="text/javascript">imagesJson = '.json_encode($json).';</SCRIPT>'.
        '<div id="show_galery">'.
            '<h1>'.$name.'</h1>'.
            $spisok.
        '</div>';
}//show_galery()

