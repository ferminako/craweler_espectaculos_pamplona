<?php
	ini_set('max_execution_time', 99999); //300 seconds = 5 minutes
	header("Content-Type: text/html; charset=UTF-8");
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	include 'phpQuery.php';
	include 'funciones.php';
	// include 'evento.php';
	include 'bitly.php';
	include 'ImageResize.php';

	/*LOCAL
	$ruta_destino = "/home/fermin/Escritorio/htdocs/espectaculos_crawler/imagen_eventos_convertidas/";
	*/

	$ruta_destino = "/var/www/vhosts/espectaculospamplona.com/httpdocs/crawler/imagen_eventos_convertidas/";

	$ruta_destino_ftp_imagen_principal = "/httpdocs/images/eventos/principal/";
	$ruta_destino_ftp_imagen_peque = "/httpdocs/images/eventos/principal/thumbs/";

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
		$eventosBaluarte = $funciones->traerEventosBaluarte();
		foreach ($eventosBaluarte as $key => $evento) {
			if(!$funciones->existeEvento($link,$evento)){
				//Si no está ya incluido
				$nombre_imagen = $funciones->generateRandomString() . $funciones->slug($evento[0]);
				if(strpos($evento[10], '.jpg') !== false || strpos($evento[10], '.png') !== false ){
					$tipoImagen =	$funciones->guardarImagenBaluarte($evento[10],$ruta_destino,$nombre_imagen);
					//Imagen Grande
					$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Baluarte586x196'.$tipoImagen,$ruta_destino_ftp_imagen_principal);
					//Imagen Pequeña
					$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Baluarte80x80'.$tipoImagen,$ruta_destino_ftp_imagen_peque);
					//Guardar evento
					$funciones->insertarEvento($link,$evento,$nombre_imagen.'Baluarte586x196'.$tipoImagen,$nombre_imagen.'Baluarte80x80'.$tipoImagen);
				}else{
					$evento[10] = "";
					$funciones->insertarEvento($link,$evento,'586x196_no_foto.png','80x80_no_foto.png');
				}
			}
		}

		// 2º Barañain
		$eventosBaranain = $funciones->traerEventosBaranain();
		foreach ($eventosBaranain as $key => $evento) {
			if(!$funciones->existeEvento($link,$evento)){
				//Si no está ya incluido
				$nombre_imagen = $funciones->generateRandomString() . $funciones->slug($evento[0]);
				if(strpos($evento[10], '.jpg') !== false || strpos($evento[10], '.png') !== false ){
					$tipoImagen =	$funciones->guardarImagenBaranain($evento[10],$ruta_destino,$nombre_imagen);
					//Imagen Grande
					$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Baranain586x196'.$tipoImagen,$ruta_destino_ftp_imagen_principal);
					//Imagen Pequeña
					$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Baranain80x80'.$tipoImagen,$ruta_destino_ftp_imagen_peque);
					//Guardar evento
					$funciones->insertarEvento($link,$evento,$nombre_imagen.'Baranain586x196'.$tipoImagen,$nombre_imagen.'Baranain80x80'.$tipoImagen);
				}else{
					$evento[10] = "";
					$funciones->insertarEvento($link,$evento,'586x196_no_foto.png','80x80_no_foto.png');
				}
			}
		}

		//3º Gayarre
		$eventosGayarre = $funciones->traerEventosGayarre();
		foreach ($eventosGayarre as $key => $evento) {
			if(!$funciones->existeEvento($link,$evento)){
				//Si no está ya incluido
				$nombre_imagen = $funciones->generateRandomString() . $funciones->slug($evento[0]);
				// if(strpos($evento[10], '.jpg') !== false || strpos($evento[10], '.png') !== false ){
				// 	$tipoImagen =	$funciones->guardarImagenGayarre($evento[10],$ruta_destino,$nombre_imagen);
				// 	//Imagen Grande
				// 	//$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Gayarre586x196'.$tipoImagen,$ruta_destino_ftp_imagen_principal);
				// 	//Imagen Pequeña
				// 	//$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Gayarre80x80'.$tipoImagen,$ruta_destino_ftp_imagen_peque);
				// 	//Guardar evento
				// 	$funciones->insertarEvento($link,$evento,$nombre_imagen.'Gayarre586x196'.$tipoImagen,$nombre_imagen.'Gayarre80x80'.$tipoImagen);
				// }else{
					$evento[10] = "";
					$funciones->insertarEvento($link,$evento,'586x196_no_foto.png','80x80_no_foto.png');
				// }
			}
		}

		//4º Museo
		$eventosMuseo = $funciones->traerEventosMuseo();
		foreach ($eventosMuseo as $key => $evento) {
			if(!$funciones->existeEvento($link,$evento)){
				//Si no está ya incluido
				$nombre_imagen = $funciones->generateRandomString() . $funciones->slug($evento[0]);
				if(strpos($evento[10], '.jpg') !== false || strpos($evento[10], '.png') !== false ){
					$tipoImagen =	$funciones->guardarImagenMuseo($evento[10],$ruta_destino,$nombre_imagen);
					//Imagen Grande
					$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Museo586x196'.$tipoImagen,$ruta_destino_ftp_imagen_principal);
					//Imagen Pequeña
					$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Museo80x80'.$tipoImagen,$ruta_destino_ftp_imagen_peque);
					//Guardar evento
					$funciones->insertarEvento($link,$evento,$nombre_imagen.'Museo586x196'.$tipoImagen,$nombre_imagen.'Museo80x80'.$tipoImagen);
				}else{
					$evento[10] = "";
					$funciones->insertarEvento($link,$evento,'586x196_no_foto.png','80x80_no_foto.png');
				}
			}
		}

		//5º Zentral
		$eventosZentral = $funciones->traerEventosZentral();
		foreach ($eventosZentral as $key => $evento) {
			if(!$funciones->existeEvento($link,$evento)){
				//Si no está ya incluido
				$nombre_imagen = $funciones->generateRandomString() . $funciones->slug($evento[0]);
				if(strpos($evento[10], '.jpg') !== false || strpos($evento[10], '.png') !== false ){
					$tipoImagen =	$funciones->guardarImagenZentral($evento[10],$ruta_destino,$nombre_imagen);
					//Imagen Grande
					$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Zentral586x196'.$tipoImagen,$ruta_destino_ftp_imagen_principal);
					//Imagen Pequeña
					$funciones->subir_ftp($ruta_destino,$nombre_imagen.'Zentral80x80'.$tipoImagen,$ruta_destino_ftp_imagen_peque);
					//Guardar evento
					$funciones->insertarEvento($link,$evento,$nombre_imagen.'Zentral586x196'.$tipoImagen,$nombre_imagen.'Zentral80x80'.$tipoImagen);
				}else{
					$evento[10] = "";
					$funciones->insertarEvento($link,$evento,'586x196_no_foto.png','80x80_no_foto.png');
				}
			}
	  }

		$tiempo_fin=time();
		echo $funciones->time_lapsed($tiempo_inicio,$tiempo_fin)." minutos.";

	} catch (Exception $e) {
		echo $e->getMessage();
	}



?>