<?php
	class Funciones{

		private $urlBaluarte = "http://baluarte.com/cas/espectaculos-y-conciertos/calendario-de-espectaculos";//?tipo=&fecha=2017-6
		private $urlGayarre = "http://teatrogayarre.com/portal/agenda.aspx";//?mesAgenda=6&annoAgenda=2017
		private $urlBaranain = "http://www.auditoriobaranain.com/programacion/";
		private $urlMuseo = "http://museo.unav.edu/artes-escenicas/temporada-actual";
		private $urlZentral = "http://www.zentralpamplona.com/programacion/";

		function traerHtml($url){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$html = curl_exec($ch);
			curl_close($ch);
			return $html;
		}

		function traerEventosBaluarte(){
			$urlsBaluarte = array();
			$eventosBaluarte = array();
			/*Preparo las urls para las fechas de 3 meses vista*/
			$mesActual = date('Y-n');
			for ($i=0; $i < 3; $i++) {
				echo "<h1>".$mesActual."</h1>";
				$urlsBaluarte = array();
				$auxUrl = $this->urlBaluarte."?tipo=&fecha=".$mesActual;
				echo $auxUrl."<br>";
				$urlsBaluarte = $this->obtenerLinksBaluarte($this->traerHtml($auxUrl));
				foreach ($urlsBaluarte as $key => $url) {
					$eventosBaluarte[] = $this->parseHtmlBaluarte($url);
				}
				echo '<pre>';var_dump($eventosBaluarte);echo '</pre>';
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
			}

			return $urlsBaluarte;

		}//obtenerLinksBaluarte

		function parseHtmlBaluarte($url){

			$urlWeb = bitly::acortarUrl($url);
			$html = $this->traerHtml($url);

			$doc = phpQuery::newDocument($html);
			$divEvento = pq('.ficha_espectaculo');

			$divFoto = pq($divEvento)->find('div#foto1');
			$imagen = "http://baluarte.com/" . pq($divFoto)->find('img')->attr('src');

			$divNoticia = pq($divEvento)->find('div#ficha_noticia');

			$titulo = strip_tags(utf8_encode(pq($divNoticia)->find('h1')));

			$tipo = $this->get_string_between(rtrim(trim(utf8_encode(pq($divNoticia)->find('div.definicion')))),'<div class="definicion">','</div>');

			$fechaHora = utf8_encode(pq($divNoticia)->find('div.fecha_espectaculo'));
			$fechaTexto = $this->get_string_between($fechaHora,"<strong>","</strong>");
			$fechaUnix = "";

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

			return new Evento($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);

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


	}//Class Funciones
?>