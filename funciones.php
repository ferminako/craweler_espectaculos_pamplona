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
			$anoActual = date('Y')
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
			return $eventosBaluarte;
		}

		function obtenerLinksGayarre($html){
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

		function parseHtmlGayarre($url){
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

			$texto = "";
			foreach(pq($divEvento)->find('div.vc_tta-panel-body')->find('div.wpb_text_column')->find('div.wpb_wrapper')->find('p') as $contenido) {
				$texto .= $contenido->textContent."<br>";
			}
			return array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);
		}

		function obtenerUnixTimeGayarre($fechaTexto){
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



		/////////////////////////FUNCIONES CADENA/////////////
		function traerHtml($url){
			$ch = curl_init();
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
			if($mes == 12){
				return array("mes"=>"1","ano"=>($anoActual+1));
			}else{
				return array("mes"=>($mes+1),"ano"=>$anoActual);
			}
		}
		/////////////////////////////////////////////////////


	}//Class Funciones
?>