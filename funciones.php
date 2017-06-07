<?php
	class Funciones{

		private $urlBaluarte = "http://baluarte.com/cas/espectaculos-y-conciertos/calendario-de-espectaculos";//?tipo=&fecha=2017-6
		private $urlGayarre = "http://teatrogayarre.com/portal/agenda.aspx";//?mesAgenda=6&annoAgenda=2017
		private $urlBaranain = "http://www.auditoriobaranain.com/programacion/";
		private $urlMuseo = "http://museo.unav.edu/artes-escenicas/temporada-actual";
		private $urlZentral = "http://www.zentralpamplona.com/programacion/";



		///////////////////////BALUARTE///////////////////////
		function traerEventosBaluarte(){
			$urlsBaluarte = array();
			$eventosBaluarte = array();
			/*Preparo las urls para las fechas de 3 meses vista*/
			$mesActual = date('Y-n');
			for ($i=0; $i < 3; $i++) {
				$urlsBaluarte = array();
				$auxUrl = $this->urlBaluarte."?tipo=&fecha=".$mesActual;
				$urlsBaluarte = $this->obtenerLinksBaluarte($this->traerHtml($auxUrl));
				foreach ($urlsBaluarte as $key => $url) {
					$eventosBaluarte[] = $this->parseHtmlBaluarte($url);
				}
				//Aumento un mes
				$occDate=$mesActual.'-01';
 				$mesActual= date('Y-n', strtotime("+1 month", strtotime($occDate)));
			}
			return $eventosBaluarte;
		}

		function obtenerLinksBaluarte($html){
			$urlsBaluarte = array();
			//Obtengo div id noticias
			$doc = phpQuery::newDocument($html);
			$divEventos = pq('#noticias');

			//Recorro ese div buscando la url detalle de cada evento
			foreach( $divEventos->find('div.noticia') as $divEvento ) {
				$urlEvento = pq($divEvento)->find('a.bloque')->attr('href');
				$urlsBaluarte[] = "http://baluarte.com/".$urlEvento;
				// echo "http://baluarte.com/".$urlEvento."<br>";
			}

			return $urlsBaluarte;
		}

		function parseHtmlBaluarte($url){

			$urlWeb = bitly::acortarUrl($url);
			$html = $this->traerHtml($url);

			$doc = phpQuery::newDocument($html);
			$divEvento = pq('.ficha_espectaculo');

			$divFoto = pq($divEvento)->find('div#foto1');
			$auxImagen = "http://baluarte.com/" . pq($divFoto)->find('img')->attr('src');
			$imagen = str_replace('340', '586', $auxImagen);

			$divNoticia = pq($divEvento)->find('div#ficha_noticia');

			$titulo = strip_tags(utf8_encode(pq($divNoticia)->find('h1')));

			$tipo = $this->get_string_between(rtrim(trim(utf8_encode(pq($divNoticia)->find('div.definicion')))),'<div class="definicion">','</div>');

			$fechaHora = utf8_encode(pq($divNoticia)->find('div.fecha_espectaculo'));
			$fechaTexto = $this->get_string_between($fechaHora,"<strong>","</strong>");
			$fechaUnix = $this->obtenerUnixTimeBaluarte($fechaTexto);

			$auxHora0 = explode("</strong>", $fechaHora);
			$auxHora1 = explode("·",$auxHora0[1]);
			$hora = rtrim(trim($auxHora1[0]));

			$cadena = str_replace('</div>', '', $auxHora1[1]);
			$lugar = "Baluarte, ". trim(rtrim($cadena));

			$texto = "";
			foreach(pq($divNoticia)->find('p') as $divContenido) {
				$texto .= $divContenido->textContent."<br>";
			}

			$precio = rtrim(trim(strip_tags(utf8_encode(pq($divEvento)->find('span.precios')))))." €";

			$urlCompraEntradas = $this->get_string_between(pq($divEvento)->find('div.compraentrada')->find('a')->attr('onclick'),"compraEntradas('","')");
			$urlCompraEntradas = bitly::acortarUrl($urlCompraEntradas);

			return array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);
			// return new Evento($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);
		}

		function obtenerUnixTimeBaluarte($fechaTexto){
			$fechaSinDiaSemana = explode(",", $fechaTexto);
			$auxFecha=explode(" ",$fechaSinDiaSemana[0]);
			$dia = $auxFecha[0];
			$mes = strtolower($auxFecha[1]);
			$mesNumero = 0;
			switch ($mes) {
				case 'enero':
					$mesNumero = 1;break;
				case 'febrero':
					$mesNumero = 2;break;
				case 'marzo':
					$mesNumero = 3;break;
				case 'abril':
					$mesNumero = 4;break;
				case 'mayo':
					$mesNumero = 5;break;
				case 'junio':
					$mesNumero = 6;break;
				case 'julio':
					$mesNumero = 7;break;
				case 'agosto':
					$mesNumero = 8;break;
				case 'septiembre':
					$mesNumero = 9;break;
				case 'octubre':
					$mesNumero = 10;break;
				case 'noviembre':
					$mesNumero = 11;break;
				case 'diciembre':
					$mesNumero = 12;break;
			}
			return strtotime( date('Y') . "-" . $mesNumero . "-" . $dia );
		}
		/////////////////////////////////////////////////////


		///////////////////////BARAÑAIN///////////////////////
		function traerEventosBaranain(){
			$urls = array();
			$eventos = array();
			$auxUrl = "http://www.auditoriobaranain.com/programacion/";
			$urls = $this->obtenerLinksBaranain($this->traerHtml($auxUrl));
			foreach ($urls as $key => $url) {
				$eventos[] = $this->parseHtmlBaranain($url);
			}
			return $eventos;
		}

		function obtenerLinksBaranain($html){
			$urls = array();
			$doc = phpQuery::newDocument($html);
			$divEventos = pq('div.concept-gallery');
			//Recorro ese div buscando la url detalle de cada evento
			foreach( $divEventos->find('div.isotope-item') as $divEvento ) {
				$urlEvento = pq($divEvento)->find('h4')->find('a')->attr('href');
				$urls[] = $urlEvento;;
			}
			return $urls;
		}

		function parseHtmlBaranain($url){
			// echo $url."<br>";
			$urlWeb = bitly::acortarUrl($url);
			$html = $this->traerHtml($url);
			$doc = phpQuery::newDocument($html);
			$divEvento = pq('#Descripcion_evento');
			$divTitulo = pq('#Titulo');

			$imagen = pq($divEvento)->find('div.vc_single_image-wrapper')->find('img')->attr('src');
			$titulo = strip_tags(pq($divTitulo)->find('h1'));
			$tipo = strip_tags(pq($divTitulo)->find('h5'));

			$aux =  pq($divEvento)->find('p:first');
			$auxFecha = $this->get_string_between($aux,'<p>','<br>');
			$auxFecha = explode('</span>', $aux);
			$auxFecha = explode(',', $auxFecha[1]);
			$fechaTexto = trim(rtrim($auxFecha[0]));
			$fechaUnix = $this->obtenerUnixTimeBaluarte($fechaTexto);
			$hora = rtrim(trim($auxFecha = $this->get_string_between($aux,'<br><span class="text-uppercase"><strong>Horario:</strong></span>','<br>')));
			$lugar ="Auditorio Barañain," . rtrim(trim($this->get_string_between($aux,'<strong>Sala:</strong></span>','</p>')));

			$aux =  pq($divEvento)->find('ul.disc-list');
			$precio = "";
			foreach ($aux->find('li') as $key => $p) {
				$precio .= $p->textContent."<br>";
			}

			$urlCompraEntradas =  pq($divEvento)->find('div.vc_btn3-container')->find('a.vc_general')->attr('href');
			$urlCompraEntradas = bitly::acortarUrl($urlCompraEntradas);

			$texto = "";
			foreach(pq($divEvento)->find('div.vc_tta-panel-body')->find('div.wpb_text_column')->find('div.wpb_wrapper')->find('p') as $contenido) {
				$texto .= $contenido->textContent."<br>";
			}
			return array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);
		}

		function obtenerUnixTimeBaranain($fechaTexto){
			$fechaSinDiaSemana = explode(",", $fechaTexto);
			$auxFecha=explode(" ",$fechaSinDiaSemana[0]);
			$dia = $auxFecha[0];
			$mes = strtolower($auxFecha[1]);
			$mesNumero = 0;
			switch ($mes) {
				case 'enero':
					$mesNumero = 1;break;
				case 'febrero':
					$mesNumero = 2;break;
				case 'marzo':
					$mesNumero = 3;break;
				case 'abril':
					$mesNumero = 4;break;
				case 'mayo':
					$mesNumero = 5;break;
				case 'junio':
					$mesNumero = 6;break;
				case 'julio':
					$mesNumero = 7;break;
				case 'agosto':
					$mesNumero = 8;break;
				case 'septiembre':
					$mesNumero = 9;break;
				case 'octubre':
					$mesNumero = 10;break;
				case 'noviembre':
					$mesNumero = 11;break;
				case 'diciembre':
					$mesNumero = 12;break;
			}
			return strtotime( date('Y') . "-" . $mesNumero . "-" . $dia );
		}
		/////////////////////////////////////////////////////

		///////////////////////GAYARRE///////////////////////
		function traerEventosGayarre(){

			$urlsGayarre = array();
			$eventosGayarre = array();
			/*Preparo las urls para las fechas de 3 meses vista*/
			$mesActual = date('n');
			$anoActual = date('Y');
			for ($i=0; $i < 3; $i++) {
				$urlsGayarre = array();
				$auxUrl = $this->urlGayarre."?mesAgenda=".$mesActual."&annoAgenda=".$anoActual;
				$urlsGayarre = $this->obtenerLinksGayarre($this->traerHtml($auxUrl));
				foreach ($urlsGayarre as $key => $url) {
					$eventosGayarre[] = $this->parseHtmlGayarre($url);
				}
				//Aumento un mes
 				$fecha= $this->aumentar_mes($mesActual,$anoActual);
 				$mesActual = $fecha["mes"];
 				$anoActual = $fecha["ano"];
			}
			return $eventosGayarre;
		}

		function obtenerLinksGayarre($html){
			$urls = array();
			$doc = phpQuery::newDocument($html);
			$divEventos = pq('#listaProgramacion');
			//Recorro ese div buscando la url detalle de cada evento
			foreach( $divEventos->find('tr') as $key=>$divEvento ) {

				if( $key > 0 ){
					$urlEvento = pq($divEvento)->find('td:nth-child(4)')->find('a')->attr('href');
					$urls[] = "http://teatrogayarre.com/portal/". $urlEvento;
				}
			}
			return $urls;
		}

		function parseHtmlGayarre($url){
			$urlWeb = bitly::acortarUrl($url);
			$html = $this->traerHtml($url);

			$doc = phpQuery::newDocument($html);
			$divPreEvento = pq('.cuerpoEvento');
			$divEvento = pq('#op1');
			$divInfoEvento = pq('.infoEvento');

			$imagen = "http://teatrogayarre.com/" . pq($divPreEvento)->find('img')->attr('src');
			$titulo = strip_tags(pq($divEvento)->find('h2'));
			$tipo = pq($divInfoEvento)->find('div#zonaFechas')->find('h3:first');
			$tipo = $this->get_string_between($tipo, '</span>', '</h3>');
			$aux = pq($divInfoEvento)->find('div.fechas')->find('div.fecha');
			$aux = explode("-", $aux);
			$fechaTexto = rtrim(trim(strtolower(str_replace('de', '', $aux[1]))));
			$fechaTexto = str_replace('  ', ' ', $fechaTexto);
			$fechaUnix = $this->obtenerUnixTimeGayarre($fechaTexto);
			$hora = rtrim(trim(strip_tags(pq($divInfoEvento)->find('div.fechas')->find('div.hora'))));
			$lugar ="Teatro Gayarre, Sala Principal";
			$tablaPrecios = pq($divInfoEvento)->find('table.precio');
			$precio = "";
			foreach ($tablaPrecios->find('tr') as $key => $p) {
				$aux_precio = str_replace('Sala', 'Sala ', $p->textContent);
				$aux_precio = str_replace('Palco', 'Palco ', $aux_precio);
				$aux_precio = str_replace('Anfiteatro', 'Anfiteatro ', $aux_precio);
				$precio .= $aux_precio . "<br>";
			}
			$urlCompraEntradas = pq($divInfoEvento)->find('p.compra')->find('a')->attr('href');
			$urlCompraEntradas = bitly::acortarUrl($urlCompraEntradas);

			$texto = "";
			foreach ($divEvento->find('div:first')->find('p') as $key => $p) {
			 	$texto .= $p->textContent."<br>";
			}

			return array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);
		}

		function obtenerUnixTimeGayarre($fechaTexto){

			$aux = explode(" ", $fechaTexto);
			$dia = $aux[0];
			$mes = $aux[2];

			$mesNumero = 0;

			if(strpos($mes, 'enero') !== false){
				$mesNumero = 1;
			}
			if(strpos($mes, 'febrero') !== false){
				$mesNumero = 2;
			}
			if(strpos($mes, 'marzo') !== false){
				$mesNumero = 3;
			}
			if(strpos($mes, 'abril') !== false){
				$mesNumero = 4;
			}
			if(strpos($mes, 'mayo') !== false){
				$mesNumero = 5;
			}
			if(strpos($mes, 'junio') !== false){
				$mesNumero = 6;
			}
			if(strpos($mes, 'julio') !== false){
				$mesNumero = 7;
			}
			if(strpos($mes, 'agosto') !== false){
				$mesNumero = 8;
			}
			if(strpos($mes, 'septiembre') !== false){
				$mesNumero = 9;
			}
			if(strpos($mes, 'octubre') !== false){
				$mesNumero = 10;
			}
			if(strpos($mes, 'noviembre') !== false){
				$mesNumero = 11;
			}
			if(strpos($mes, 'diciembre') !== false){
				$mesNumero = 12;
			}

			return strtotime( date('Y') . "-" . $mesNumero . "-" . $dia );
		}
		/////////////////////////////////////////////////////



		/////////////////////////FUNCIONES CADENA/////////////
		function traerHtml($url){

			//1º
			// $options = array('http' =>
			//     array( 'header' => 'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 6.1; es-CL; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3' . PHP_EOL )
			// );
			// $context = stream_context_create($options);
			// $html = file_get_contents($url);

			//2º
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13');
			$header = array(
			    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			    'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
			    'Accept-Language: en-us;q=0.8,en;q=0.6'
			);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$html = curl_exec($ch);
			curl_close($ch);

			return $html;
		}

		function get_string_between($string, $start, $end){
	    $string = ' ' . $string;
	    $ini = strpos($string, $start);
	    if ($ini == 0) return '';
	    $ini += strlen($start);
	    $len = strpos($string, $end, $ini) - $ini;
	    return substr($string, $ini, $len);
		}

		function limpia_espacios($cadena){
			$cadena = str_replace(' ', '', $cadena);
			return $cadena;
		}

		function aumentar_mes($mesActual,$anoActual){
			if($mesActual == 12){
				return array("mes"=>"1","ano"=>($anoActual+1));
			}else{
				return array("mes"=>($mesActual+1),"ano"=>$anoActual);
			}
		}
		/////////////////////////////////////////////////////


	}//Class Funciones
?>