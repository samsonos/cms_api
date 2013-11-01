/** Выполним инициализацию редакторов если они присутствуют на странице */
s(document).pageInit(function(o)
{		
	// Указатель на открытый редактор
	var openedEditor = null;
	
	// Сохраним хем значение открытого редактора
	var md5OpenedValue = null;
	
	// Создадим форму для отправки изменений
	var editorForm = s('<form id="__editor_form" method="POST" action="'+SamsonPHP.url_base('cmsapi/editor_save')+'"></form>');
	
	/** Закрыть открытый редактор */
	var closeEditor = function( _editor )
	{	
		// Если передана ссылка на редактор
		if( _editor )
		{
			// Получим блок с содержимым
			var valueArea = s('.__value', _editor );
			
			// Пометим редактор как активный
			_editor.removeClass('__active');
			
			// Сделаем блок с содержимым - редактируемым
			valueArea.DOMElement.contentEditable = false;
				
			// Сохраним хем значение открытого редактора
			if( s.md5(valueArea.html()) != md5OpenedValue ) saveValue( _editor );
			
			// Уберем указатель на открытый редактор
			openedEditor = null;
		}
	};				
	
	/** Записать изменения */
	var saveValue = function( _editor )
	{
		// Если передана ссылка на редактор
		if( _editor )
		{
			// Получим блок с содержимым
			var valueArea = s('.__value', _editor );
			
			// Получим блок с содержимым
			var valueField = s('.__html', _editor );
				
			// Попросим подтверждение
			if( confirm('Сохранить изменения?') )
			{					
				// Получим новое значение поля
				valueField.val( valueArea.html() );
				
				// Скопируем инпуты
				s('input, textarea',_editor).each(function(input)
				{
					editorForm.append(input);
				});				
				
				// Выполним ассинхронный запрос на сохранение данных
				editorForm.ajaxForm();
			}
			// Вернем оригинальное значение
			else
			{
				valueArea.html(valueField.val());
			}
		}
	};
	
	/** Открыть редактор */
	var openEditor = function( _editor )
	{
		// Если мы пытаемся открыть еще не открытый редактор
		if( _editor != openedEditor )			
		{
			// Закроем открытый редактор, если он есть
			if( openedEditor ) closeEditor( openedEditor );
			
			// Сохраним указатель на открытый редактор
			openedEditor = _editor;
			
			// Получим блок с содержимым
			var valueArea = s('.__value', _editor );
			
			// Сохраним хеш значение открытого редактора
			md5OpenedValue = s.md5(valueArea.html());
			
			// Сделаем блок с содержимым - редактируемым
			valueArea.DOMElement.contentEditable = true;	
			
			// Установим фокус на элемент
			valueArea.focus();	
			
			// Пометим редактор как активный
			_editor.addClass('__active');
		}		
	};
	
	// Получим все редакторы на странице
	var editors = s('.__editor');
	
	// Если на странице найдены редакторы
	if( editors.length )
	{
		var url = SamsonPHP._uri;
		
		/* Панель управления редактором */
		var panel = s('<div class="__editor_panel"><a class="__logo" href="'+SamsonPHP.url_base('cms')+'"></a><div class="__editable">режим редактирования</div></div>');
		/* Кнопка выйти */
		var btnExit = s('<a href="'+SamsonPHP.url_base('cms/control/logout/'+url)+'" class="__exit">Выйти</a>');
		/* Кнопка перейти в SamsonCMS */
		var btnBack = s('<a href="'+SamsonPHP.url_base('cms')+'" class="__tocms">Перейти в SamsonCMS</a>');
		
		
		/** Обработчик выхода из режима редактора */
		btnExit.click(function(){s.ajax( SamsonPHP.url_base('cms/control/logout/'), function(){ window.location.reload(); });}, true, true );
		
		// Добавим кнопки на панель
		panel.append( btnExit ).append(btnBack).append(editorForm);	
		
		// Добавим панель управления редакторами
		s(document.body).append(panel).css('margin-top','20px');
		
		// Повесим обработчик для начала редактирования содержимого
		editors.click( openEditor,true, true );			
		
		// Повесим обработчик специальных клавиш
		editors.keyup(function( o, opt,e ){
			 //if (e.keyCode == 13) { closeEditor(openedEditor); }     // enter
			 if (e.keyCode == 27) { closeEditor(openedEditor); }   // esc
		});
		
		// Повесим обработчик закрытия редактора при клике мимо него
		s('html').click(function(){	closeEditor(openedEditor);});	
	}
});