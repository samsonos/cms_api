<?php
/**
 * Контроллер для вывода базового шаблона CMS
 * Вывод текущего материала
 */
function cmsapi_template()
{		
	
	m()->view('material');	
}

/**
 * Контроллер для вывода списка материалов раздела
 */
function cmsapi_template_material_list()
{
	m()->view('material_list');
}

/**
 * Контроллер для вывода списка подчиненных структур
 */
function cmsapi_template_structure_list()
{
	m()->view('structure_list');
}

/**
 * Контроллер для вывода 404 ошибки
 */
function cmsapi_e404()
{
	// Установим HTTP заголовок что такой страницы нет
	header('HTTP/1.0 404 Not Found');

	// Установим представление
	m()->title('Указанная страница не найдена')->view('e404');
}

/**
 * Контроллер сохранения данных из редактора веб-страницы
 */
function cmsapi_editor_save()
{
	// Ассинхронный режим
	s()->async(true);
	
	// Свормируем массив для клиента
	$responce = array( 'status' => 0 );
	
	// Если переданы все параметры
	if( isset($_POST['__id']) && isset($_POST['__field']) && isset($_POST['__html']) )
	{
		// Обезопасим идентификатор материала
		$material_id = filter_var($_POST['__id'],FILTER_DEFAULT);
		// Обезопасим имя поля материала
		$field_name = filter_var($_POST['__field'],FILTER_DEFAULT);
		// Обезопасим значение поля материала
		$field_value = filter_var($_POST['__html'],FILTER_DEFAULT);
		// Обезопасим значение поля сущности
		$entity = filter_var($_POST['__entity'],FILTER_DEFAULT);
		
		// Указатель на сущность
		$db_entity = null;
		
		// Определим метод для получения сущности
		switch($entity)
		{
			case 'cmsnav'		: ifcmsnav( $material_id, $db_entity, 'id'); break;
			case 'cmsmaterial'	: ifcmsmat( $material_id, $db_entity, 'id'); break;
			default: $responce['error'] = 'Сущность с идентификатором №'.$_POST['__id'].' - Не найден';	
		}
		
		// Попытаемся найти поле сущности
		if( isset($db_entity) && isset( $db_entity[ $field_name ] ) )
		{				
			// Установим новое значение для поля материала
			$db_entity->$field_name = $field_value;
			
			// Запишем изменения в БД
			$db_entity->save();
			
			// Установим положительный статус
			$responce['status'] = '1';
		}
		else $responce['error'] = 'Поле сущности "'.$_POST['__field'].'" - Не найдено';
	}	
	
	// Вернем ответ для клиента
	echo json_encode( $responce );
}
?>