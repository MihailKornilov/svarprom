var imgCatId, // id каталога изображений
    imgNext = 0, //номер следующей картинки в массиве
	imagesJson = {},
    URL_AJAX = 'http://' + DOMAIN + '/view/ajax.php',
    showError = function(msg, callback) {
        var callback = callback || function() {};
        $('.error')
            .stop()
            .html(msg)
            .css('opacity', 1)
            .show()
            .css('display','inline')
            .fadeOut(3000, callback);
    },
    cursorWait = function() { $('body').css('cursor','wait');},
    cursorDefault = function() { $('body').css('cursor','default');},
    setCookie = function(name, value) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate()+1);
        document.cookie = name + "=" + value + "; path=/; expires=" + exdate.toGMTString();
    },
    delCookie = function(name) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate() - 1);
        document.cookie = name + "=; path=/; expires=" + exdate.toGMTString();
    },
    getCookie = function(name) {
        var arr1 = document.cookie.split(name);
        if(arr1.length > 1) {
            var arr2 = arr1[1].split(/;/);
            var arr3 = arr2[0].split(/=/);
            return arr3[0] ? arr3[0] : arr3[1];
        }
        return null;
    },
    imageOpen = function(img) {
        var document_width = $(document).width(),
            window_height = $(window).height(),
            scrollTop = $(document).scrollTop(),
            imgMaxHeight = window_height - 70,// максимальная высота картинки
            img_height = img.y >= imgMaxHeight ? imgMaxHeight : img.y,
            top = Math.round((imgMaxHeight - img_height) / 2) + scrollTop,
            img_width = Math.round(img_height / img.y * img.x),
            left = Math.round((document_width - img_width) / 2),
            imageBox = '<img src="' + img.link + '" width="' + img_width + '" height="' + img_height + '">' +
                '<div class="about">' + img.about + '</div>' +
                '<div class="hidden"></div>';

        $('#image_box').html(imageBox).css({
            top:top + 'px',
            left:left + 'px'
        });
    },
    imageClose = function() {
        $('#backfon').remove();
        $('#image_box').remove();
        $('body')
            .css('position', 'static')
            .css('overflow', 'auto');
    },
    resize = function() {
        var tH = $('#left_td').height(),
            wH = $(window).height() - 354,
            h = 253;
        if(tH < wH)
            h = wH - 20;
        $('#content').css('min-height', h + 'px');

        if($('#backfon').length > 0) {
            var document_width = $(document).width(),
                document_height = $(document).height();
            $('#backfon')
                .width(document_width)
                .height(document_height);
            imageOpen(imagesJson[imgCatId][imgNext - 1]);
        }
    };

$(window).resize(resize);

$(document)
	.on('click', '.aajax', function() {
		var send = {
			p:'aajax',
			val:$(this).attr('val')
		};
		if(!send.val) return true;
		$.post(URL_AJAX, send, function(res) {
			$('#content').html(res.html);
		}, 'json')
		return false;
	})

	.on('click', '#login', function() {
		var send = {
			p:'login',
			pass:$('#login_pass').val()
		};
		if(send.pass.length == 0) return;
		$.post(URL_AJAX, send, function(res) {
			if(res.error == 0)
				location.href = 'http://' + DOMAIN + '/admin';
			else
				showError(res.text);
		}, 'json');
	})
	.on('click', '#logout', function() {
		$.post(URL_AJAX, {p:'logout'}, function(res) {
			location.href = URL;
		}, 'json');
	})

	.on('click', '#changepass button', function() {
		var but = $(this);
		if(but.hasClass('busy')) return;
		var send = {
			p:'changepass',
			old:$('#changepass #old').val(),
			new:$('#changepass #new').val(),
			repeat:$('#changepass #repeat').val()
		};
		if(send.old.length == 0 || send.new.length == 0 || send.repeat.length == 0) {
			showError('Необходимо заполнить все поля');
			return false;
		}
		if(send.old == send.new) {
			showError('Новый и старый пароли не должны совпадать');
			return false;
		}
		if(send.new != send.repeat) {
			showError('Новые пароли не совпадают');
			return false;
		}
		$(this).addClass('busy');
		cursorWait();
		$.post(URL_AJAX, send, function(res) {
			cursorDefault();
			but.removeClass('busy');
			if(res.error == 0) {
				$('.error').css('color','#292');
				showError('Пароль изменён', function() {
					location.href = URL + '/admin';
				});
			} else
				showError(res.text);
		}, 'json');
	})

	.on('click', '#logotext_save', function() {
		var but = $(this);
		if(but.hasClass('busy')) return false;
		$(this).addClass('busy');
		cursorWait();
		$('#logotext_form').submit();
		setCookie('mceSave', 'process');
		var timer = setInterval(sendStart, 400);
		return false;

		function sendStart() {
			var cookie = getCookie('mceSave');
			if (cookie != 'process') {
				delCookie('mceSave');
				but.removeClass('busy');
				cursorDefault();
				clearInterval(timer);
				var arr = cookie.split('_');
				switch (arr[0]) {
					case 'success':
						$('.error').css('color','#080');
						showError('<b> Сохранено</b>');
						break;
					case 'error': pageSaveError(arr[1]); break;
					default: pageSaveError(-1);
				}
			}
		}

		function pageSaveError(id) {
			var msg = 'неизвестная ошибка';
			switch(parseInt(id)) {
				case 1: msg = 'недостаточно прав'; break;
				case 9: msg = 'ошибка данных'; break;
			}
			showError(msg);
		}
	})

	.on('click', '#page_name_add button', function() {
		var but = $(this);
		if(but.hasClass('busy')) return;
		var send = {
			p:'page_name_add',
			name:$('#page_name_add #name').val(),
			place:$('#page_name_add #place').val()
		};
		if(!send.name) return true;
		$(this).addClass('busy');
		cursorWait();
		$.post(URL_AJAX, send, function(res) {
			cursorDefault();
			but.removeClass('busy');
			if(res.error != 0)
				showError(res.text);
			else {
				$('#page_name_add #name').val('');
				$("#admin_menutop .drag").append(res.html);
				$('#menu_top .bord').html(res.links_top);
				$('#left_td').html(res.links_left);
			}
		}, 'json');
	})

	.on('click', '.page_show_check', function() {
		var send = {
			p:'page_show_check',
			id:$(this).attr('val'),
			val:$(this).is(':checked') ? 1 : 0
		};
		cursorWait();
		$.post(URL_AJAX, send, function(res) {
			cursorDefault();
			if(res.error != 1) {
				$('#menu_top .bord').html(res.links_top);
				$('#left_td').html(res.links_left);
			}
		}, 'json');
	})

	.on('click', '#pageedit_save', function() {
		var but = $(this);
		if(but.hasClass('busy')) return false;
		if(!$('#pageedit_name').val()) {
			showError('Не указано название');
			return false;
		}
		$(this).addClass('busy');
		cursorWait();
		$('#pageedit_form').submit();
		setCookie('mceSave', 'process');
		var timer = setInterval(sendStart, 400);
		return false;

		function sendStart() {
			var cookie = getCookie('mceSave');
			if (cookie != 'process') {
				delCookie('mceSave');
				cursorDefault();
				clearInterval(timer);
				var arr = cookie.split('_');
				switch (arr[0]) {
					case 'deleted':
						showError(' Страница удалена');
						break;
					case 'success':
						but.removeClass('busy');
						$('.error').css('color','#080');
						showError('<b> Сохранено</b>');
						break;
					case 'error': pageSaveError(arr[1]); break;
					default: pageSaveError(-1);
				}
			}
		}

		function pageSaveError(id) {
			but.removeClass('busy');
			var msg = 'неизвестная ошибка';
			switch(parseInt(id)) {
				case 1: msg = 'недостаточно прав'; break;
				case 2: msg = 'название содержит недопустимые символы'; break;
				case 9: msg = 'ошибка данных'; break;
			}
			showError(msg);
		}
	})
	.on('click', '#pageedit_del', function() {
		var val = $(this).is(':checked') ? 1 : 0;
		$('#pageedit_save').html(val ? 'Удалить страницу' : 'Сохранить');
	})
	.on('click', '#set_as_galery', function() {
		if($(this).hasClass('busy')) return;
		var send = {
			p:'set_as_galery',
			id:$('#pageedit_id').val()
		};
		$(this).addClass('busy');
		cursorWait();
		$.post(URL_AJAX, send, function(res) {
			cursorDefault();
			if(res.error == 0) {
				location.href = URL + '/admin/galery';
			}
		}, 'json');
	})

	.on('click', '#a-catalog-add', function(){
		obj = $('#galery-catalog-add');
		obj[obj.is(':hidden') ? 'show' : 'hide']();
	})

	.on('click', '#galery-catalog-add-submit', function(){
		var but = $(this);
		if(but.hasClass('busy')) return;
		var send = {
			p:'galery_catalog_add',
			name:$('#galery-catalog-add #name').val(),
			about:$('#galery-catalog-add #about').val()
		};
		if(!send.name) {
			showError('Не указано наименование');
			return;
		}
		$(this).addClass('busy');
		cursorWait();
		$.post(URL_AJAX, send, function(res) {
			cursorDefault();
			but.removeClass('busy');
			if(res.error != 0)
				showError(res.text);
			else {
				$('#galery-catalog-add #name').val('');
				$('#galery-catalog-add #about').val('');
				$('#galery-catalog-add').hide();
				$('#galery_sort').append(res.html);
			}
		}, 'json');
	})

	.on('click', '#admin_galery .check', function() {
		var send = {
			p:'galery_catalog_access',
			id:$(this).attr('val'),
			val:$(this).is(':checked') ? 1 : 0
		};
		cursorWait();
		$.post(URL_AJAX, send, cursorDefault, 'json');
	})
	.on('click', '#galery_del', function() {
		var val = $(this).is(':checked') ? 1 : 0;
		$('#galery-catalog-edit-submit').html(val ? 'Удалить каталог' : 'Сохранить');
	})
	.on('click', '#galery-catalog-edit-submit', function(){
		var but = $(this);
		if(but.hasClass('busy')) return;
		var send = {
			p:'galery_catalog_edit',
			id:but.attr('val'),
			name:$('#galery-catalog-add #name').val(),
			about:$('#galery-catalog-add #about').val(),
			del:$('#galery_del').is(':checked') ? 1 : 0
		};
		if(!send.name) {
			showError('Не указано наименование');
			return;
		}
		$(this).addClass('busy');
		cursorWait();
		$.post(URL_AJAX, send, function(res) {
			cursorDefault();
			if(res.error != 0) {
				but.removeClass('busy');
				showError(res.text);
			} else if (send.del == 1) {
				showError('Каталог удалён');
			} else {
				but.removeClass('busy');
				$('.error').css('color','#080');
				showError('<b> Сохранено</b>');
			}
		}, 'json');
	})

	.on('change', '#galery_upload_file', function() {
		cursorWait();
		$("#upload_form").submit();
		$(this).attr('disabled', 'disabled');
		setCookie('fotoUpload', 'process');
		var timer = setInterval(uploadStart, 400);

		function uploadStart() {
			var cookie = getCookie('fotoUpload');
			$('#upload_input_file').append('.');
			if (cookie != 'process') {
				clearInterval(timer);
				var arr = cookie.split('_');
				switch (arr[0]) {
					case 'uploaded': fotoUploadLastImage(); break;
					case 'error': fotoUploadErrorPrint(arr[1]); break;
					default: fotoUploadErrorPrint(-1);
				}
			}
		}

		function fotoUploadLastImage() {
			$.post(URL_AJAX, {p:'galery_last_image_uploaded'}, function(res) {
				$("#galery_images_sort").append(res.image);
				cursorDefault();
				$('#upload_input_file').html('<input type="file" name="file_name" id="galery_upload_file">');
			}, 'json');
		}

		function fotoUploadErrorPrint(id) {
			var msg = 'неизвестная ошибка';
			switch(parseInt(id)) {
				case 1: msg = 'файл не является избражением'; break;
				case 2: msg = 'размер изображения слишком маленький'; break;
				case 3: msg = 'неверный id каталога'; break;
			}
			showError('Избражение не загружено: ' + msg);
			$('#upload_input_file').html('<input type="file" name="file_name" id="galery_upload_file">');
			cursorDefault();
		}
	})

	.on('blur', '.image_about_edit', function() {
		var send = {
				p:'image_about_edit',
				id:$(this).attr('val'),
				about:$(this).val()
			},
			prev = $('#about_' + send.id);
		if(send.about != prev.val()) {
			cursorWait();
			$.post(URL_AJAX, send, function(res) {
				cursorDefault();
				if(res.error == 0)
					prev.val(send.about);
			}, 'json');
		}
	})
	.on('click', '.image_about_del', function() {
		var send = {
			p:'image_about_del',
			id:$(this).attr('val')
		};
		cursorWait();
		$.post(URL_AJAX, send, function(res) {
			cursorDefault();
			if(res.error == 0)
				$('#sort_' + send.id).remove();
		}, 'json');
	})
	.on('click', '.image_about_access', function() {
		var send = {
			p:'image_about_access',
			id:$(this).attr('id').split('_')[1],
			val:$(this).is(':checked') ? 1 : 0
		};
		cursorWait();
		$.post(URL_AJAX, send, function(res) {
			cursorDefault();
			if(res.error == 0)
				$('#sort_' + send.id)[(send.val ? 'add' : 'remove') + 'Class']('access');
		}, 'json');
	})
	.on('click', '.image_about_pageuse', function() {
		var send = {
			p:'image_about_pageuse',
			id:$(this).attr('id').split('_')[1],
			val:$(this).is(':checked') ? 1 : 0
		};
		cursorWait();
		$.post(URL_AJAX, send, cursorDefault, 'json');
	})

	.on('click', '#backfon', imageClose)

	.on('click', '.image_show', function() {
		$('body')
			.css('position', 'fixed')
			.css('overflow', 'hidden')
			.append('<div id="backfon"></div><div id="image_box"></div>');
		var t = $(this);
		imgCatId = t.attr('val').split('_')[0];
		if(typeof imagesJson[imgCatId] == 'undefined') {
			var send = {
					p:'image_catalog_get',
					catalog_id:imgCatId,
					id:t.attr('val').split('_')[1]
				};
			cursorWait();
			$.post(URL_AJAX, send, function(res) {
				cursorDefault();
				if(res.error == 0) {
					imagesJson[imgCatId] = res.arr;
					imgNext = res.arr[0].num;
					resize();
				}
			}, 'json');
		} else{
			imgNext = 1;
			var id = t.attr('val').split('_')[1];
			if(id)
				for(var n = 0; n < imagesJson[imgCatId].length; n++) {
					var r = imagesJson[imgCatId][n];
					if(r.id == id) {
						imgNext = n + 1;
						break;
					}
				}
			resize();
		}
	})
	.on('click', '#image_box img', function() {
		var len = imagesJson[imgCatId].length
		if(len == 1 || len == imgNext) {
			imageClose();
			return;
		}
		imageOpen(imagesJson[imgCatId][imgNext++]);
		if(imgNext < len) {
			img = imagesJson[imgCatId][imgNext];
			$('#image_box .hidden').html('<img src="' + img.link + '">');
		}
	})
	.ready(function() {
		if(DOMAIN != 'svarprom')
			$('#livecounter').html("<a href='http://www.liveinternet.ru/click' "+
				"target=_blank><img src='//counter.yadro.ru/hit?t23.4;r"+
				escape(document.referrer)+((typeof(screen)=="undefined")?"":
				";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
					screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
				";"+Math.random()+
				"' alt='' title='LiveInternet: показано число посетителей за"+
				" сегодня' "+
				"border='0' width='88' height='15'><\/a>");

		$(".drag").sortable({
			axis:'y',
			update:function() {
				var items = $(this).find('.sort'),
					arr = [];
				for(var n=0; n < items.length; n++)
					arr.push(items.eq(n).attr('val'));
				var send = {
					p:$(this).attr('id'),
					arr:arr.join(':')
				};
				cursorWait();
				$.post(URL_AJAX, send, function(res) {
					cursorDefault();
					if(res.links_top) $('#menu_top .bord').html(res.links_top);
					if(res.links_left) $('#left_td').html(res.links_left);
				}, 'json');
			}
		});

		if($('#pageedit_txt').length > 0) {
			tinymce.init({
				selector: "textarea#pageedit_txt",
				plugins: [
					"autoresize link advlist image fullscreen preview textcolor media table charmap code hr visualblocks"
				],
				toolbar: "undo redo | " +
					"bold italic underline forecolor backcolor | " +
					"alignleft aligncenter alignright alignjustify | " +
					"bullist numlist outdent indent | " +
					"link image media | " +
					"fullscreen preview",
				language : 'ru',
				autoresize_min_height:400,
				autoresize_max_height:700,
				statusbar: false,
				content_css:"/css/content.css?" + VERSION,
				image_list:image_list,
				link_list:link_list
			});
		}
		if($('#logotext_edit').length > 0) {
			tinymce.init({
				width:390,
				selector: "textarea#logotext_edit",
				plugins: [
					"textcolor charmap code"
				],
				toolbar: "bold italic underline forecolor | alignleft aligncenter alignright alignjustify | ",
				language : 'ru',
				statusbar: false,
				content_css:"/css/logotext.css?" + VERSION
			});
		}

		resize();
	});
