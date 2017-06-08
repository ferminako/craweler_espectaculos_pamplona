<?php
	ini_set('max_execution_time', 99999); //300 seconds = 5 minutes
	header("Content-Type: text/html; charset=UTF-8");
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	include 'phpQuery.php';
	include 'funciones.php';
	include 'evento.php';
	include 'bitly.php';
	include 'ImageResize.php';

	$ruta_destino = "/home/fermin/Escritorio/htdocs/espectaculos_crawler/imagen_eventos_convertidas/";

	try {
		$tiempo_inicio=time();

		$funciones = new Funciones();
		/*
		// 1º Baluarte
		$eventosBaluarte = $funciones->traerEventosBaluarte();
		$eventos[] = $eventosBaluarte;
		// 2º Barañain
		$eventosBaranain = $funciones->traerEventosBaranain();
		$eventos[] = $eventosBaranain;
		// 3º Gayarre
		$eventosGayarre = $funciones->traerEventosGayarre();
		$eventos[] = $eventosGayarre;
		//4º Museo
		$eventosMuseo = $funciones->traerEventosMuseo();
		$eventos[] = $eventosMuseo;
		//5º Zentral
		$eventosZentral = $funciones->traerEventosZentral();
		$eventos[] = $eventosZentral;
		*/
		$eventosMuseo = array();
		// array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);
		$eventosBaluarte[] = array('Óyeme con los ojos. María Pagés Compañía','Artístico',1506582000,'28 septiembre','19:30h','Museo Universidad de Navarra, Teatro','https://goo.gl/qeyz8SNOEXISTE','Óyeme con los ojos, único solo de María Pagés, es una reflexión ...','26€ y 20€','http://bit.ly/2s7daSh','http://museo.unav.edu/image/ximg,qimg_id=14273919,at=1496930620201.pagespeed.ic.CfX8Gbh-4E.jpg');



		//1) Obtengo todos los eventos
		//2) Por cada evento
			//2.0) Compruebo si existe ya ese evento
			//2.1) Genero imagen grande y pequeña
			//2.2) Subo imagenes al ftp
			//2.3) Guardo evento en bbdd

		$link = $funciones->connect_db();

		foreach ($eventosBaluarte as $key => $evento) {
			if(!$funciones->existeEvento($link,$evento)){
				//Si no está ya incluido
				$funciones->guardarImagenBaluarte($evento[10],$ruta_destino);
				//Imagen Grande
				$funciones->subir_ftp($imagen,$nombre);
				//Imagen Pequeña
				$funciones->subir_ftp($imagen,$nombre);

			}
		}




		exit;
		$tiempo_fin=time();
		echo $funciones->time_lapsed($tiempo_inicio,$tiempo_fin)." minutos.";

	} catch (Exception $e) {
		echo $e->getMessage();
	}



?>