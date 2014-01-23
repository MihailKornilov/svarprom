<?php
function show_admin_page() {
    switch(@$_GET['d']) {
        case 'changepass': return show_admin_changepass();
        case 'textlogo': return show_admin_logotext();
        case 'menutop': return show_admin_menutop();
        case 'menuleft': return show_admin_menuleft();
        case 'pageedit':
            if(!preg_match(REGEXP_NUMERIC, @$_GET['id']))
                return 'Страница не существует';
            return show_admin_pageedit($_GET['id']);
        case 'galery': return show_admin_galery();
        case 'galeryedit':
            if(!preg_match(REGEXP_NUMERIC, @$_GET['id']))
                return 'Страница не существует';
            return admin_galery_edit($_GET['id']);
    }
    $send = '<div id="admin_page">'.
        '<div class="head">Администрирование</div>'.
        '<ul>'.
            '<li><a href="'.URL.'/admin/changepass" class="aajax" val="changepass">Изменить пароль</a>'.
            '<li><a href="'.URL.'/admin/textlogo" class="aajax">Изменить текст в шапке</a>'.
            '<li><a href="'.URL.'/admin/menutop" class="aajax">Управление верхним меню</a>'.
            '<li><a href="'.URL.'/admin/menuleft">Управление левым меню</a>'.
            '<li><a href="'.URL.'/admin/galery">Фотогалерея</a>'.
        '</ul>'.
    '</div>';
    return $send;
}//show_admin_page()

function show_admin_changepass() {
    return '<div class="head"><a href="'.URL.'/admin">Администрирование</a> » Изменение пароля</div>'.
        '<table cellspacing="6" id="changepass">'.
            '<tr><td>Старый пароль:</td><td><input type="password" id="old" /></td></tr>'.
            '<tr><td>Новый пароль:</td><td><input type="password" id="new" /></td></tr>'.
            '<tr><td>Повторить новый пароль:</td><td><input type="password" id="repeat" /></td></tr>'.
            '<tr><td></td><td><button>Изменить</button></td></tr>'.
            '<tr><td colspan="2" align="center"><span class="error"></span>&nbsp;</td></tr>'.
        '</table>'.
        '<a href="'.URL.'/admin" class="aajax menu_back" val="admin"><< назад</a>';
}//show_admin_changepass()

function show_admin_logotext() {
    return '<div class="head"><a href="'.URL.'/admin">Администрирование</a> » Изменение текста в шапке</div>'.
        '<SCRIPT type="text/javascript" src="/js/tinymce/tinymce.min.js"></SCRIPT>'.
        '<div id="admin_textlogo">'.
            '<form method="post" action="'.URL.'/view/include/mceSave.php" target="mce_frame" id="logotext_form">'.
            '<textarea name="txt" id="logotext_edit">'.htmlspecialchars_decode(LOGOTEXT).'</textarea> '.
            '<input type="hidden" name="p" value="logotext_save">'.
            '<button id="logotext_save">Сохранить</button> '.
            '<span class="error"></span>'.
            '</form>'.
            '<IFRAME name="mce_frame" id="mce_frame"></IFRAME>'.
        '</div>'.
        '<a href="'.URL.'/admin" class="menu_back"><< назад</a>';
}//show_admin_logotext()

function show_admin_menutop() {
    $q = query("SELECT
                    `id`,`name`,`access`
                FROM `pages`
                WHERE `place`='top'
                ORDER BY `sort`");
    $pages = '';
    while($r = mysql_fetch_assoc($q))
        $pages .= get_pageitem_for_spisok($r);
    return '<div class="head"><a href="'.URL.'/admin">Администрирование</a> » Управление верхним меню</div>'.
        '<div id="admin_menutop">'.
            '<table class="tab-spisok">'.
                '<tr><th class="name">Название</th>'.
                    '<th class="access">Доступ</th>'.
                '</tr>'.
            '</table>'.
            '<div class="drag" id="menutop_sort">'.$pages.'</div>'.
        '</div>'.
        page_name_add('top').
        '<a href="'.URL.'/admin" class="menu_back"><< назад</a>';
}//show_admin_menutop()

function show_admin_menuleft() {
    $q = query("SELECT
                    `id`,`name`,`access`
                FROM `pages`
                WHERE `place`='left'
                ORDER BY `sort` ASC");
    $pages = '';
    while($r = mysql_fetch_assoc($q))
        $pages .= get_pageitem_for_spisok($r);
    return '<div class="head"><a href="'.URL.'/admin">Администрирование</a> » Управление левым меню</div>'.
        '<div id="admin_menutop">'.
            '<table class="tab-spisok">'.
                '<tr><th class="name">Название</th>'.
                    '<th class="access">Доступ</th>'.
                '</tr>'.
            '</table>'.
        '<div class="drag" id="menutop_sort">'.$pages.'</div>'.
        '</div>'.
        page_name_add('left').
        '<a href="'.URL.'/admin" class="menu_back"><< назад</a>';
}//show_admin_menuleft()

function page_name_add($place = '') {
    return '<div id="page_name_add">Новый пункт меню: '.
        '<input type="text" id="name" maxlength="50" /> '.
        '<input type="hidden" id="place" value="'.$place.'" /> '.
        '<button>Добавить</button> '.
        '<span class="error"></span>'.
    '</div>';
}//page_name_add()

function get_pageitem_for_spisok($p) {
    return '<table class="tab-spisok sort" val="'.$p['id'].'">'.
        '<tr><td class="name"><a href="'.URL.'/admin/page'.$p['id'].'/edit">'.$p['name'].'</a></td>'.
            '<td class="access"><input type="checkbox"'.($p['access'] ? 'checked' : '').' class="page_show_check" val="'.$p['id'].'" /></td>'.
        '</tr>'.
    '</table>';
}//get_pageitem_for_spisok()

function show_admin_pageedit($id) {
    $q = query("SELECT * FROM `pages` WHERE `id`=".$id);
    $page = mysql_fetch_assoc($q);
    if(!$page)
        return 'Такой страницы нет';

    // Проверка есть установлена страница как галерея, если да, то переход на галерею
    if($page['galery'] == 1)
        header('Location: '.URL.'/admin/galery');

    // Проверка есть ли вообще страница-гаререя, если нет, то вывод ссылки для установки
    $galery_set = '';
    if($page['place'] == 'top') {
        $q = query("SELECT COUNT(`id`) AS `count` FROM `pages` WHERE `galery`=1");
        $r = mysql_fetch_assoc($q);
        if($r['count'] == 0)
            $galery_set = ' <a id="set_as_galery">Назначить как "Фотогалерея"</a>';
    }

    // Составление списка ссылок на изображения
    $image_list = array();
    $q = query("SELECT * FROM `galery_images` WHERE `pageuse`=1 ORDER BY `id`");
    while($img = mysql_fetch_assoc($q)) {
        $arr = (array)json_decode($img['img']);
        $big = (array)$arr['big'];
        array_push($image_list, '{'.
            'title:"'.($img['about'] ? substr($img['about'], 0, 99) : $img['name']).'",'.
            'value:"'.$big['link'].'"}'
        );
    }

    // Составление списка ссылок на страницы
    $link_list = array();
    $q = query("SELECT `id`,`name` FROM `pages` ORDER BY `place` DESC,`sort` ASC");
    while($p = mysql_fetch_assoc($q)) {
        array_push($link_list, '{'.
            'title:"'.$p['name'].'",'.
            'value:"'.URL.'/page'.$p['id'].'"}'
        );
    }

    $out = '<SCRIPT type="text/javascript">'.
            'var image_list = ['.implode(',',$image_list).'];'.
            'var link_list = ['.implode(',',$link_list).'];'.
        '</SCRIPT>'.
        '<SCRIPT type="text/javascript" src="/js/tinymce/tinymce.min.js"></SCRIPT>'.
        '<div class="head"><a href="'.URL.'/admin">Администрирование</a> » Редактирование страницы</div>'.
        '<div id="admin_pageedit">'.
            '<form method="post" action="'.URL.'/view/include/mceSave.php" target="mce_frame" id="pageedit_form">'.
                'Название: <input type="text" name="name" value="'.htmlspecialchars_decode($page['name']).'" id="pageedit_name" />'.
                $galery_set.
                '<br /><br />'.
                '<textarea id="pageedit_txt" name="txt">'.htmlspecialchars_decode($page['txt']).'</textarea> '.
                '<input type="hidden" name="p" value="pageedit_save">'.
                '<input type="hidden" name="id" value="'.$page['id'].'">'.
                '<div class="del"><input type="checkbox" name="del" id="pageedit_del"> <label for="pageedit_del">удалить<label></div>'.
                '<button id="pageedit_save">Сохранить</button> '.
                '<span class="error"></span>'.
            '</form>'.
            '<IFRAME name="mce_frame" id="mce_frame"></IFRAME>'.
        '</div>'.
        '<a href="'.URL.'/admin/'.($page['place'] ? 'menu'.$page['place'] : '').'" class="menu_back"><< назад</a> :: '.
        '<a href="'.URL.'/page'.$page['id'].'" class="page_preview_link">перейти на страницу</a>';

    return $out;
}//show_admin_pageedit()

function show_admin_galery() {
    $send = '<div class="head"><a href="'.URL.'/admin">Администрирование</a> » Управление фотогалереей</div>';
    $q = query("SELECT * FROM `pages` WHERE `galery`=1 LIMIT 1");
    if(!mysql_num_rows($q))
        return $send.'Страница для фотогалереи не назначена.<br />'.
            'Нужно выбрать любую страницу из <a href="'.URL.'/admin/menutop">верхнего меню</a>.';
//    $galery = mysql_fetch_assoc($q);

    $q = query("SELECT * FROM `galery_catalogs` ORDER BY `sort` ASC");
    $spisok = '';
    while($r = mysql_fetch_assoc($q))
        $spisok .= admin_galery_item($r);
    return $send.
        '<div id="admin_galery">'.
            '<table cellpadding="0" cellspacing=0 class="tab-spisok">'.
                '<tr>'.
                    '<th class="image">Обложка</th>'.
                    '<th class="tdabout">Описание каталога</th>'.
                    '<th class="count">Кол-во<br />фото</th>'.
                    '<th class="access">Доступ</th>'.
                '</tr>'.
            '</table>'.
            '<div class="drag" id="galery_sort">'.$spisok.'</div>'.
            '<a href="'.URL.'/admin" class="menu_back"><< назад</a> :: '.
            '<a id="a-catalog-add">Внести новый каталог</a>'.
            '<div id="galery-catalog-add">'.
                'Наименование: <input type="text" id="name"><br /><br />'.
                'Описание:<textarea id="about"></textarea>'.
                '<button id="galery-catalog-add-submit">Внести</button> '.
                '<span class="error"></span>'.
            '</div>'.
        '</div>';
}//show_admin_galery()

function admin_galery_item($item) {
    $cover = $item['cover'] ? '<img src="'.$item['cover'].'">' : '';
    $count = $item['image_count']
             ?
             '<span class="help" title="Количество доступных для просмотра изображений">'.$item['image_access'].'</span>/'.
             '<span class="help" title="Общее количество изображений в каталоге">'.$item['image_count'].'<span>'
             :
             '';
    return '<table cellpadding="0" cellspacing=0 class="tab-spisok sort" val="'.$item['id'].'">'.
            '<tr><td class="image"><a href="'.URL.'/admin/galery/id'.$item['id'].'">'.$cover.'</a></td>'.
                '<td class="tdabout">'.
                    '<a href="'.URL.'/admin/galery/id'.$item['id'].'">'.$item['name'].'</a>'.
                    '<div class="about">'.$item['about'].'</div>'.
                '</td>'.
                '<td class="count">'.$count.'</td>'.
                '<td class="access"><input type="checkbox"'.($item['access'] ? 'checked="checked"' : '').' class="check" val="'.$item['id'].'"></td>'.
            '</tr>'.
        '</table>';
}//admin_galery_item()

function admin_galery_edit($id) {
    $q = query("SELECT * FROM `galery_catalogs` WHERE `id`=".$id." LIMIT 1");
    $item = mysql_fetch_assoc($q);
    if(!$item)
        $send = 'Каталога не существует или он был удалён.';
    else {
        $q = query("SELECT * FROM `galery_images` WHERE `catalog_id`=".$item['id']." ORDER BY `sort` ASC");
        $images = '';
        while($r = mysql_fetch_assoc($q))
            $images .= admin_image_unit($r);
        $send = 'Каталог "<b>'.$item['name'].'</b>". '.
            '<a id="a-catalog-add">Изменить данные</a>'.
            '<div id="galery-catalog-add">'.
                'Наименование: <input type="text" id="name" value="'.$item['name'].'"><br /><br />'.
                'Описание:<textarea id="about">'.$item['about'].'</textarea>'.
                '<button id="galery-catalog-edit-submit" val="'.$item['id'].'">сохранить</button> '.
                '<span class="error"></span>'.
                '<input type="checkbox" id="galery_del"> <label for="galery_del">удалить<label>'.
            '</div>'.
            '<div id="galery_images_sort" class="drag">'.$images.'</div>'.
            '<form method="post" '.
                  'action="'.URL.'/view/include/fotoUpload.php" '.
                  'enctype="multipart/form-data" '.
                  'target="upload_frame" '.
                  'id="upload_form">'.
                'Загрузить новое изображение: '.
                '<span id="upload_input_file"><input type="file" name="file_name" id="galery_upload_file"></span>'.
                '<span class="error"></span>'.
                '<input type="hidden" name="catalog_id" value="'.$item['id'].'">'.
                '<IFRAME name="upload_frame"></IFRAME>'.
            '</form>';
    }
    return '<div class="head"><a href="'.URL.'/admin">Администрирование</a> » Редактирование каталога фотогалереи</div>'.
            '<div id="admin_galery_edit">'.$send.'</div>'.
        '<a href="'.URL.'/admin/galery" class="menu_back"><< назад</a>';
}//admin_galery_edit()

function admin_image_unit($unit) {
    $img = (array)json_decode($unit['img']);
    $img = (array)$img['small'];
    return '<div class="sort'.($unit['access'] ? ' access"' : '').'" val="'.$unit['id'].'" id="sort_'.$unit['id'].'">'.
        '<table cellpadding="0" cellspacing="0">'.
            '<tr><td class="image"><img src="'.$img['link'].'"></td>'.
                '<td class="about">'.
                    '<input type="text" placeholder="укажите описание изображения.." class="image_about_edit" val="'.$unit['id'].'" value="'.$unit['about'].'" />'.
                    '<input type="hidden" id="about_'.$unit['id'].'" value="'.$unit['about'].'">'.
                    '<a class="image_about_del" val="'.$unit['id'].'">Удалить изображение</a>'.
                    '<input type="checkbox"'.($unit['access'] ? ' checked="checked"' : '').' id="access_'.$unit['id'].'" class="image_about_access"> '.
                    '<label for="access_'.$unit['id'].'">Доступно для просмотра</label>'.
                    '<br /><br />'.
                    '<input type="checkbox"'.($unit['pageuse'] ? ' checked="checked"' : '').' id="pageuse_'.$unit['id'].'" class="image_about_pageuse"> '.
                    '<label for="pageuse_'.$unit['id'].'">Использовать на страницах</label>'.
                '</td>'.
            '<tr>'.
        '</table>'.
    '</div>';
}//admin_image_unit()

function adminGaleryCountAndCoverSet($id) {
    // Общее количество фото в каталоге
    $q = query("SELECT COUNT(`id`) AS `image_count` FROM `galery_images` WHERE `catalog_id`=".$id." LIMIT 1");
    $r = mysql_fetch_assoc($q);
    $image_count = $r['image_count'];

    // Количество доступных фото в каталоге
    $q = query("SELECT COUNT(`id`) AS `image_access` FROM `galery_images` WHERE `access`=1 AND `catalog_id`=".$id." LIMIT 1");
    $r = mysql_fetch_assoc($q);
    $image_access = $r['image_access'];

    $cover = '';
    $q = query("SELECT `img`
                FROM `galery_images`
                WHERE `access`=1 AND `catalog_id`=".$id."
                ORDER BY `sort`
                LIMIT 1");
    if(mysql_num_rows($q)) {
        $r = mysql_fetch_assoc($q);
        $img = (array)json_decode($r['img']);
        $cover = $img['small']->link;
    }
    query("UPDATE `galery_catalogs`
           SET `image_count`=".$image_count.",
               `image_access`=".$image_access.",
               `cover`='".$cover."'
           WHERE `id`=".$id);
}//adminGaleryCountAndCoverSet()
