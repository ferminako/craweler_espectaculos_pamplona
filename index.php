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
	$ruta_destino_ftp_imagen_principal = "/httpdocs/images/eventos/principal";
	$ruta_destino_ftp_imagen_pequena = "/httpdocs/images/eventos/principal/thumbs";


	// 	$ruta_imagen = "/home/fermin/Escritorio/htdocs/espectaculos_crawler/imagenes_eventos_origen/baluarte.jpg";
	// 	$image = new ImageResize($ruta_imagen);
	// 	$image->resizeToHeight(196);
	// 	$image->save($ruta_destino.'BaluarteProcess.jpg');


	// 	merge($ruta_destino.'base.jpg', $ruta_destino.'BaluarteProcess.jpg', $ruta_destino.'merge.jpg',400);





	// 	function merge($filename_x, $filename_y, $filename_result,$posicion) {

	// 		 // Get dimensions for specified images

	// 		 list($width_x, $height_x) = getimagesize($filename_x);
	// 		 list($width_y, $height_y) = getimagesize($filename_y);

	// 		 // Create new image with desired dimensions

	// 		 //$image = imagecreatetruecolor($width_x + $width_y, $height_x);
	// 		 $image = imagecreatetruecolor($width_x, $height_x);

	// 		 // Load images and then copy to destination image

	// 		 $image_x = imagecreatefromjpeg($filename_x);
	// 		 $image_y = imagecreatefromjpeg($filename_y);

	// 		 imagecopy($image, $image_x, 0, 0, 0, 0, $width_x, $height_x);
	// 		 imagecopy($image, $image_y, $width_x-$posicion, 0, 0, 0, $width_y, $height_y);

	// 		 // Save the resulting image to disk (as JPEG)

	// 		 imagejpeg($image, $filename_result);

	// 		 // Clean up

	// 		 imagedestroy($image);
	// 		 imagedestroy($image_x);
	// 		 imagedestroy($image_y);

	// 		}
	// exit;

	try {
		$tiempo_inicio=time();

		$funciones = new Funciones();

			//1) Obtengo todos los eventos
			//2) Por cada evento
			//2.0) Compruebo si existe ya ese evento
			//2.1) Genero imagen grande y pequeña
			//2.2) Subo imagenes al ftp
			//2.3) Guardo evento en bbdd

		$link = $funciones->connect_db();

		// 1º Baluarte
		//$eventosBaluarte = $funciones->traerEventosBaluarte();
		// foreach ($eventosBaluarte as $key => $evento) {
		// 	if(!$funciones->existeEvento($link,$evento)){
		// 		//Si no está ya incluido
		// 		$nombre_imagen = $funciones->generateRandomString() . $funciones->slug($evento[0]);
		// 		if(strpos($evento[10], '.jpg') !== false || strpos($evento[10], '.png') !== false ){
		// 			$funciones->guardarImagenBaluarte($evento[10],$ruta_destino,$nombre_imagen);
		// 			//Imagen Grande
		// 			$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Baluarte586x196.jpg',$ruta_destino_ftp_imagen_principal);
		// 			//Imagen Pequeña
		// 			$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Baluarte80x80.jpg',$ruta_destino_ftp_imagen_pequena);
		// 			//Guardar evento
		// 			$funciones->insertarEvento($link,$evento,$nombre_imagen.'Baluarte586x196.jpg',$nombre_imagen.'Baluarte80x80.jpg');
		// 		}else{
		// 			$evento[10] = "";
		// 			$funciones->insertarEvento($link,$evento,'586x196_no_foto.png','80x80_no_foto.png');
		// 		}
		// 	}
		// }


		// 2º Barañain
		$eventosBaranain = $funciones->traerEventosBaranain();
		foreach ($eventosBaranain as $key => $evento) {
			if(!$funciones->existeEvento($link,$evento)){
				//Si no está ya incluido
				$nombre_imagen = $funciones->generateRandomString() . $funciones->slug($evento[0]);
				if(strpos($evento[10], '.jpg') !== false || strpos($evento[10], '.png') !== false ){
					$funciones->guardarImagenBaranain($evento[10],$ruta_destino,$nombre_imagen);
					//Imagen Grande
					//$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Baranain586x196.jpg',$ruta_destino_ftp_imagen_principal);
					//Imagen Pequeña
					//$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Baranain80x80.jpg',$ruta_destino_ftp_imagen_pequena);
					//Guardar evento
					$funciones->insertarEvento($link,$evento,$nombre_imagen.'Baranain586x196.jpg',$nombre_imagen.'Baranain80x80.jpg');
				}else{
					$evento[10] = "";
					$funciones->insertarEvento($link,$evento,'586x196_no_foto.png','80x80_no_foto.png');
				}
			}
		}

		exit;
		// 3º Gayarre
		$eventosGayarre = $funciones->traerEventosGayarre();
		$eventos[] = $eventosGayarre;
		//4º Museo
		$eventosMuseo = $funciones->traerEventosMuseo();
		$eventos[] = $eventosMuseo;
		//5º Zentral
		$eventosZentral = $funciones->traerEventosZentral();
		$eventos[] = $eventosZentral;

		// $eventosMuseo = array();
		// // array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);
		// $eventosBaluarte[] = array('Óyeme con los ojos. María Pagés Compañía','Artístico',1506582000,'28 septiembre','19:30h','Museo Universidad de Navarra, Teatro','https://goo.gl/qeyz8SNOEXISTE','Óyeme con los ojos, único solo de María Pagés, es una reflexión ...','26€ y 20€','http://bit.ly/2s7daSh','http://baluarte.com/imagen_escala2.php?x=340&y=340&imagen=idb/espectaculos/JEAN_EFLAM_BAVOUZET__A13_webdentro.jpg');


		//1) Obtengo todos los eventos
		//2) Por cada evento
			//2.0) Compruebo si existe ya ese evento
			//2.1) Genero imagen grande y pequeña
			//2.2) Subo imagenes al ftp
			//2.3) Guardo evento en bbdd

		// $link = $funciones->connect_db();

		// foreach ($eventosBaluarte as $key => $evento) {
		// 	if(!$funciones->existeEvento($link,$evento)){
		// 		//Si no está ya incluido
		// 		$nombre_imagen = $funciones->generateRandomString() . $funciones->slug($evento[0]);
		// 		$funciones->guardarImagenBaluarte($evento[10],$ruta_destino,$nombre_imagen);
		// 		//Imagen Grande
		// 		$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Baluarte586x196.jpg',$ruta_destino_ftp_imagen_principal);
		// 		//Imagen Pequeña
		// 		$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Baluarte80x80.jpg',$ruta_destino_ftp_imagen_pequena);
		// 		//Guardar evento
		// 		$funciones->insertarEvento($link,$evento,$nombre_imagen.'Baluarte586x196.jpg',$nombre_imagen.'Baluarte80x80.jpg');
		// 	}
		// }




		exit;
		$tiempo_fin=time();
		echo $funciones->time_lapsed($tiempo_inicio,$tiempo_fin)." minutos.";

	} catch (Exception $e) {
		echo $e->getMessage();
	}



?>