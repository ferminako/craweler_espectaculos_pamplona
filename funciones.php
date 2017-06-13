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
				for ($i=0; $i < 4; $i++) {
					$urlsBaluarte = array();
					$auxUrl = $this->urlBaluarte."?tipo=&fecha=".$mesActual;
					$urlsBaluarte = $this->obtenerLinksBaluarte($this->traerHtml($auxUrl));
					// $urlsBaluarte = array("http://baluarte.com/cas/espectaculos-y-conciertos/calendario-de-espectaculos/orchestre-national-bordeaux-aquitaine/fecha=2017-6");
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

				$titulo = strip_tags(pq($divNoticia)->find('h1'));

				$tipo = $this->get_string_between(rtrim(trim(pq($divNoticia)->find('div.definicion'))),'<div class="definicion">','</div>');

				$fechaHora = pq($divNoticia)->find('div.fecha_espectaculo');
				$auxFechaTexto = explode(',',$this->get_string_between($fechaHora,"<strong>","</strong>"));
				$fechaTexto = $auxFechaTexto[0];
				$fechaUnix = $this->obtenerUnixTimeBaluarte($fechaTexto);

				$auxHora0 = explode("</strong>", $fechaHora);
				$auxHora1 = explode("·",$auxHora0[1]);
				$hora = rtrim(trim($auxHora1[0]));

				// $cadena = str_replace('</div>', '', $auxHora1[1]);
				// $lugar = "Baluarte, ". trim(rtrim($cadena));

				$lugar = "Baluarte";

				$texto = "";
				foreach(pq($divNoticia)->find('p') as $divContenido) {
					$texto .= $divContenido->textContent."<br>";
				}

				foreach(pq($divNoticia)->find('div') as $divContenido) {
					$texto .= $divContenido->textContent."<br>";
				}

				if( $texto == "" ){
					//En algunos evento, en vez de meter en <p> la descripción lo dejan directamente en un div y ademas no tiene
					//clase ni id, por lo que debo buscar el div que no contenga las clases del resto de divs contenidos en
					//el div .fecha_espectaculo .
					foreach(pq($divNoticia)->find('div:not(.cuadro .definicion .fecha_espectaculo .descripcion .addthis_inline_share_toolbox 	.info_extra .cuadro)') as $divContenido) {
						$texto .= $divContenido->textContent."<br>";
					}
				}

				$texto = utf8_decode($texto);

				if ( $precio != "" ){
					$precio = utf8_decode(rtrim(trim(strip_tags(pq($divEvento)->find('span.precios'))))) . " euros.";
					$precio = str_replace('?', '', $precio);
					$texto .='<p><span style="font-family: Arial, sans-serif; font-size: 11px;">Precios:</span></p>
									<p><span style="font-family: Arial, sans-serif; font-size: 11px;">' . $precio . '</span></p>';
				}

				$urlCompraEntradas = $this->get_string_between(pq($divEvento)->find('div.compraentrada')->find('a')->attr('onclick'),"compraEntradas('","')");

				if($urlCompraEntradas != ""){
					$urlCompraEntradas = bitly::acortarUrl($urlCompraEntradas);
					$texto .='<p><a title="Entradas Requiem Verdi" href="' . $urlCompraEntradas . '" target="_blank"><span style="font-family: Arial, sans-serif; font-size: 11px;">Compra de entradas</span></a></p';
				}
				// echo '<pre>';var_dump(array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen));echo '</pre>';exit;
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

				$ano = null;
				if($mesNumero < date('m')){
					$ano = date('Y') +1;
				}else{
					$ano = date('Y');
				}

				return strtotime( $ano . "-" . $mesNumero . "-" . $dia );
			}
		/////////////////////////////////////////////////////


		///////////////////////BARAÑAIN///////////////////////
			function traerEventosBaranain(){
				$urls = array();
				$eventos = array();
				$auxUrl = "http://www.auditoriobaranain.com/programacion/";
				$urls = $this->obtenerLinksBaranain($this->traerHtml($auxUrl));
				// $urls = array("http://www.auditoriobaranain.com/programacion/x-aniversario-escuela-de-danza-diana-casas/");
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
					$urls[] = $urlEvento;
				}
				return $urls;
			}

			function parseHtmlBaranain($url){
				// echo $url."<br>";
				// $url = "http://www.auditoriobaranain.com/programacion/el-cascanueces-russian-classical-ballet/";
				$urlWeb = bitly::acortarUrl($url);
				$html = $this->traerHtml($url);
				$doc = phpQuery::newDocument($html);
				$divEvento = pq('#Descripcion_evento');
				$divTitulo = pq('#Titulo');

				$imagen = pq($divEvento)->find('div.vc_single_image-wrapper')->find('img')->attr('src');

				// echo $imagen;exit;
				$titulo = utf8_decode(strip_tags(pq($divTitulo)->find('h1')));
				$titulo = str_replace('?', '', $titulo);

				$tipo = utf8_decode(strip_tags(pq($divTitulo)->find('h5')));

				$aux =  pq($divEvento)->find('p:first');
				$auxFecha = $this->get_string_between($aux,'<p>','<br>');
				$auxFecha = explode('</span>', $aux);
				$auxFecha = explode(',', $auxFecha[1]);
				$fechaTexto = trim(rtrim($auxFecha[0]));

				if( strpos($fechaTexto, "\xC2\xA0") !== false){
					$fechaTexto = trim($fechaTexto, "\xC2\xA0");
					$fechaTexto = str_replace("\xC2\xA0"," ", $fechaTexto);
				}

				if( strpos($fechaTexto, '-') !== false && strpos($fechaTexto, '.') !== false ){
					//más de una fecha, cojo la primera y pista
					$fechaTextoAux = explode('-', $fechaTexto);
					$fechaTextoAux0 = explode(' ', $fechaTextoAux[0]);
					$fechaTexto = $fechaTextoAux0[1] ." ". strtolower($fechaTextoAux0[0]);
				}

				$fechaTexto = utf8_decode($fechaTexto);

				$fechaUnix = $this->obtenerUnixTimeBaranain($fechaTexto);
				$hora = rtrim(trim($auxFecha = $this->get_string_between($aux,'<br><span class="text-uppercase"><strong>Horario:</strong></span>','<br>')));
				$lugar = utf8_decode("Auditorio Barañain," . rtrim(trim($this->get_string_between($aux,'<strong>Sala:</strong></span>','</p>'))));


				$texto = "";
				foreach(pq($divEvento)->find('div.vc_tta-panel-body')->find('div.wpb_text_column')->find('div.wpb_wrapper')->find('p') as $contenido) {
					$texto .= $contenido->textContent."<br>";
				}

				$aux =  pq($divEvento)->find('ul.disc-list');
				$precio = "";
				foreach ($aux->find('li') as $key => $p) {
					$aux_precio_b = str_replace('€', ' euros', $p->textContent);
					$precio .= utf8_decode($aux_precio_b)."<br>";
				}

				if($precio != ""){
					$texto .='<p><span style="font-family: Arial, sans-serif; font-size: 11px;">Precios:</span></p>
									<p><span style="font-family: Arial, sans-serif; font-size: 11px;">' . $precio . '</span></p>';
				}

				$urlCompraEntradas =  pq($divEvento)->find('div.vc_btn3-container')->find('a.vc_general')->attr('href');

				if($urlCompraEntradas != ""){
					$urlCompraEntradas = bitly::acortarUrl($urlCompraEntradas);
					$texto .='<p><a title="Entradas Requiem Verdi" href="' . $urlCompraEntradas . '" target="_blank"><span style="font-family: Arial, sans-serif; font-size: 11px;">Compra de entradas</span></a></p';
				}

				$texto = $this->encodeToIso($texto);

				// echo '<pre>';var_dump(array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen));echo '</pre>';exit;
				return array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);
			}

			public function encodeToUtf8($string) {
			     return mb_convert_encoding($string, "UTF-8", mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true));
			}

			public function encodeToIso($string) {
			     return mb_convert_encoding($string, "ISO-8859-1", mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true));
			}

			function obtenerUnixTimeBaranain($fechaTexto){
				// echo $fechaTexto."<br>";exit;
				// $fechaSinDiaSemana = explode(",", $fechaTexto);
				// $auxFecha=explode(" ",$fechaSinDiaSemana[0]);
				// echo '<pre>';var_dump(trim($auxFecha));echo '</pre>';exit;
				$auxFecha = explode(" ", $fechaTexto);
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
				$ano = null;
				if($mesNumero < date('m')){
					$ano = date('Y') +1;
				}else{
					$ano = date('Y');
				}

				return strtotime( $ano . "-" . $mesNumero . "-" . $dia );
			}
		/////////////////////////////////////////////////////

		///////////////////////GAYARRE///////////////////////
			function traerEventosGayarre(){

				$urlsGayarre = array();
				$eventosGayarre = array();
				/*Preparo las urls para las fechas de 3 meses vista*/
				$mesActual = date('n');
				$anoActual = date('Y');
				for ($i=0; $i < 4; $i++) {
					$urlsGayarre = array();
					$auxUrl = $this->urlGayarre."?mesAgenda=".$mesActual."&annoAgenda=".$anoActual;
					$urlsGayarre = $this->obtenerLinksGayarre($this->traerHtml($auxUrl));
					// $urlsGayarre = array("http://teatrogayarre.com/portal/aE.aspx?id=1277");
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

				$imagen = "http://teatrogayarre.com" . pq($divPreEvento)->find('img')->attr('src');

				$titulo = utf8_decode(strip_tags(pq($divEvento)->find('h2')));
				$tipo = pq($divInfoEvento)->find('div#zonaFechas')->find('h3:first');
				$tipo = utf8_decode($this->get_string_between($tipo, '</span>', '</h3>'));
				$aux = pq($divInfoEvento)->find('div.fechas')->find('div.fecha');
				$aux = explode("-", $aux);
				$fechaTexto = rtrim(trim(strtolower(str_replace('de', '', $aux[1]))));

				if(strpos($fechaTexto, '<div class="fecha">') !== false){
					$pos = strpos($fechaTexto,'<div class="fecha">');
					$fechaTexto = substr($fechaTexto,0,$pos);
				}

				$fechaTexto = str_replace('  ', ' ', $fechaTexto);
				$fechaTexto = utf8_decode(strip_tags($fechaTexto));

				$fechaUnix = $this->obtenerUnixTimeGayarre($fechaTexto);
				$hora = utf8_decode(rtrim(trim(strip_tags(pq($divInfoEvento)->find('div.fechas')->find('div.hora')))));
				$lugar = utf8_decode("Teatro Gayarre, Sala Principal");
				$tablaPrecios = pq($divInfoEvento)->find('table.precio');
				$precio = "";
				foreach ($tablaPrecios->find('tr') as $key => $p) {

					$aux_precio_c = str_replace('€', ' euros', $p->textContent);
					$aux_precio = str_replace('Sala', 'Sala ', $aux_precio_c);
					$aux_precio = str_replace('Palco', 'Palco ', $aux_precio);
					$aux_precio = str_replace('Anfiteatro', 'Anfiteatro ', $aux_precio);
					$precio .= utf8_decode($aux_precio) . "<br>";
				}
				$urlCompraEntradas = pq($divInfoEvento)->find('p.compra')->find('a')->attr('href');
				$urlCompraEntradas = bitly::acortarUrl($urlCompraEntradas);

				$texto = "";
				foreach ($divEvento->find('div:first')->find('p') as $key => $p) {
				 	$texto .= utf8_decode($p->textContent)."<br>";
				}

				$texto .='<p><span style="font-family: Arial, sans-serif; font-size: 11px;">Precios:</span></p>
									<p><span style="font-family: Arial, sans-serif; font-size: 11px;">' . $precio . '</span></p>';
				$texto .='<p><a title="Entradas Requiem Verdi" href="' . $urlCompraEntradas . '" target="_blank"><span style="font-family: Arial, sans-serif; font-size: 11px;">Compra de entradas</span></a></p';

				//echo '<pre>';var_dump(array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen));echo '</pre>';
				return array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);
			}

			function obtenerUnixTimeGayarre($fechaTexto){

				$aux = explode(" ", $fechaTexto);
				$dia = $aux[0];
				$mes = $aux[1];

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

				$ano = null;
				if($mesNumero < date('m')){
					$ano = date('Y') +1;
				}else{
					$ano = date('Y');
				}

				return strtotime( $ano . "-" . $mesNumero . "-" . $dia );
			}
		/////////////////////////////////////////////////////

		///////////////////////MUSEO///////////////////////
			function traerEventosMuseo(){
				$eventosMuseo = array();
				$urlsMuseo = array();
				$auxUrl = $this->urlMuseo;
				$urlsMuseo = $this->obtenerLinksMuseo($this->traerHtml($auxUrl));
				foreach ($urlsMuseo as $key => $url) {
					$eventosMuseo[] = $this->parseHtmlMuseo($url);
				}
				return $eventosMuseo;
			}

			function obtenerLinksMuseo($html){
				$urls = array();
				$doc = phpQuery::newDocument($html);
				$divEventos = pq('#p_p_id_listadoEventos_WAR_listadoEventosportlet_');
				//Recorro ese div buscando la url detalle de cada evento
				foreach( $divEventos->find('div.evento') as $divEvento ) {
						$urls[] = "http://museo.unav.edu" . pq($divEvento)->find('a')->attr('href');
				}
				return $urls;
			}

			function parseHtmlMuseo($url){
				// echo $url."<br>";exit;
				$urlWeb = bitly::acortarUrl($url);
				$html = $this->traerHtml($url);
				$doc = phpQuery::newDocument($html);
				$divEvento = pq('div.detail');
				$imagen = "http://museo.unav.edu" . pq($divEvento)->find('img:first')->attr('src');
				$titulo = utf8_decode(trim(rtrim(strip_tags(pq($divEvento)->find('h4:first')))));
				$tipo = utf8_decode("Artístico");
				$aux = pq($divEvento)->find('div.uppercase')->find('a:first');
				$aux = $this->get_string_between($aux, '">', '</a>');
				$auxFechaHora = explode('de', $aux);

				$auxFechaDia = $auxFechaHora[0];
				$auxFechaMes = $auxFechaHora[1];
				$auxAnoHora = $auxFechaHora[2];

				$auxFechaTexto = explode(',', $auxFechaDia);
				$fechaTexto = utf8_decode(trim(rtrim($auxFechaTexto[1]. $auxFechaMes)));
				$fechaUnix = $this->obtenerUnixTimeMuseo($fechaTexto);
				$fechaTexto = str_replace('  ', ' ', $fechaTexto);
				$auxHora = explode(', a las ', $auxFechaHora[2]);
				$hora = utf8_decode($auxHora[1]);
				$lugar = "Museo Universidad de Navarra, ";
				foreach (pq($divEvento)->find('div.uppercase')->find('a') as $key => $item) {
					if($key == 1){
						$lugar .= utf8_decode($item->textContent);
					}
				}

				$precio = "";
				foreach (pq($divEvento)->find('p') as $key => $item) {
					if( strpos($item->textContent, 'Entrada general') !== false ){
						$precio = str_replace('€', ' euros', $item->textContent);
						$precio = utf8_decode(rtrim(trim($this->get_string_between($precio, 'Entrada general: ', 'A los precios de'))));
					}
				}

				$urlCompraEntradas = pq($divEvento)->find('div.boton-compra-class')->find('a')->attr('href');
				$urlCompraEntradas = bitly::acortarUrl($urlCompraEntradas);

				$texto = "";
				foreach ($divEvento->find('p') as $key => $p) {
				 	$texto .= utf8_decode($p->textContent)."<br>";
				}

				$texto .='<p><span style="font-family: Arial, sans-serif; font-size: 11px;">Precios:</span></p>
									<p><span style="font-family: Arial, sans-serif; font-size: 11px;">' . $precio . '</span></p>';
				$texto .='<p><a title="Entradas Requiem Verdi" href="' . $urlCompraEntradas . '" target="_blank"><span style="font-family: Arial, sans-serif; font-size: 11px;">Compra de entradas</span></a></p';
				//echo '<pre>';var_dump(array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen));echo '</pre>';exit;
				return array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);
			}

			function obtenerUnixTimeMuseo($fechaTexto){

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

				$ano = null;
				if($mesNumero < date('m')){
					$ano = date('Y') +1;
				}else{
					$ano = date('Y');
				}

				return strtotime( $ano . "-" . $mesNumero . "-" . $dia );
			}
		/////////////////////////////////////////////////////

		///////////////////////ZENTRAL///////////////////////
			function traerEventosZentral(){
				$eventosZentral = array();
				$urlsZentral = array();
				$auxUrl = $this->urlZentral;
				$urlsZentral = $this->obtenerLinksZentral($this->traerHtml($auxUrl));
				// $urlsZentral = array("http://www.espectaculospamplona.com/eventos/espectaculos-pamplona/la-historia-de-los-juguetes-toy-s-story");
				foreach ($urlsZentral as $key => $url) {
					$eventosZentral[] = $this->parseHtmlZentral($url);
				}
				return $eventosZentral;
			}

			function obtenerLinksZentral($html){
				$urls = array();
				$doc = phpQuery::newDocument($html);
				$divEventos = pq('#content');

				//Recorro ese div buscando la url detalle de cada evento
				foreach( $divEventos->find('div.event-archive ') as $divEvento ) {
						$urls[] = pq($divEvento)->find('a')->attr('href');
				}
				return $urls;
			}

			function parseHtmlZentral($url){
				$urlWeb = bitly::acortarUrl($url);
				$html = $this->traerHtml($url);
				$doc = phpQuery::newDocument($html);
				$divEvento = pq('div.single-page-col');

				$auxFecha = explode(',',pq($divEvento)->find('div.fecha_evento'));
				$fechaTexto = utf8_decode(strip_tags(strtolower(rtrim(trim($auxFecha[1])))));

				$fechaUnix = $this->obtenerUnixTimeZentral($fechaTexto);
				$hora = utf8_decode(trim(rtrim(strip_tags(pq($divEvento)->find('div.hora')))));
				$imagen = pq($divEvento)->find('div.event-cover-max')->find('img')->attr('src');
				$tipo = utf8_decode(trim(rtrim(strip_tags(pq($divEvento)->find('div.tipo')))));
				$titulo = utf8_decode(trim(rtrim(strip_tags(pq($divEvento)->find('h1.titulo_evento')))));
				$texto = "";
				foreach (pq($divEvento)->find('div.texto_evento_descripcion')->find('p') as $key => $item) {
					$texto .= utf8_decode($item->textContent);
				}

				$lugar = utf8_decode("Zentral, " . trim(rtrim(strip_tags(pq($divEvento)->find('div.ubicacion')))));
				$urlCompraEntradas = pq($divEvento)->find('div.event-tickets')->find('a')->attr('href');
				$urlCompraEntradas = bitly::acortarUrl($urlCompraEntradas);

				$divInfoPrecioEvento = pq('div.info_derecha_evento');

				$precio = "";
				foreach (pq($divInfoPrecioEvento)->find('h5') as $key => $item) {
					if($key == 1){
						$precio = str_replace('€', ' euros', $item->textContent);
						$precio = utf8_decode($precio);
					}
				}

				$texto .='<p><span style="font-family: Arial, sans-serif; font-size: 11px;">Precios:</span></p>
									<p><span style="font-family: Arial, sans-serif; font-size: 11px;">' . $precio . '</span></p>';
				$texto .='<p><a title="Entradas Requiem Verdi" href="' . $urlCompraEntradas . '" target="_blank"><span style="font-family: Arial, sans-serif; font-size: 11px;">Compra de entradas</span></a></p';
				// echo '<pre>';var_dump(array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen));echo '</pre>';exit;
				return array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);
			}

			function obtenerUnixTimeZentral($fechaTexto){

				$aux = explode(" ", $fechaTexto);

				$dia = $aux[0];
				$mes = $aux[1];

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

				$ano = null;
				if($mesNumero < date('m')){
					$ano = date('Y') +1;
				}else{
					$ano = date('Y');
				}
				return mktime(0,0,0,$mesNumero,$dia,$ano);
				//return strtotime( $ano . "-" . $mesNumero . "-" . $dia );
			}
		/////////////////////////////////////////////////////

		/////////////////////////FUNCIONES/////////////
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

			function time_lapsed($inicio,$fin){
				$mins = ($fin - $inicio) / 60;
				return $mins;
			}

			//$ruta_imagen = "/home/fermin/Escritorio/htdocs/espectaculos_crawler/imagenes_eventos_origen/baranain.jpg";

			function guardarImagenGayarre($url_imagen,$ruta_destino,$nombre_imagen){
				echo $url_imagen. "<br>" . $ruta_destino . "<br>" .$nombre_imagen."<br>";
				$tipoImagen = '.jpg';
				if( strpos($url_imagen, ".png") !== false){
					$tipoImagen = '.png';
				}
				echo "a<br>";
				$image = new ImageResize($url_imagen);http://teatrogayarre.com//docs/ImagenesEditor/2017/01/grupo-apaisado_v1_730x400.jpg
				echo "b<br>";
				$image->crop(586,196);
				echo "c<br>";
				$image->save($ruta_destino.$nombre_imagen.'Gayarre586x196'.$tipoImagen);
				echo "d<br>";
				$image->crop(80,80);echo "e<br>";
				$image->save($ruta_destino.$nombre_imagen.'Gayarre80x80'.$tipoImagen);echo "a<br>";

				return $tipoImagen;
			}

			function guardarImagenMuseo($url_imagen,$ruta_destino,$nombre_imagen){
				$tipoImagen = '.jpg';
				if( strpos($url_imagen, ".png") !== false){
					$tipoImagen = '.png';
				}

				$image = new ImageResize($url_imagen);
				$image->crop(586,196);
				$image->save($ruta_destino.$nombre_imagen.'Museo586x196'.$tipoImagen);
				$image->crop(80,80);
				$image->save($ruta_destino.$nombre_imagen.'Museo80x80'.$tipoImagen);
				return $tipoImagen;
			}

			function guardarImagenBaluarte($url_imagen,$ruta_destino,$nombre_imagen){
				// $image = new ImageResize($ruta_imagen);
				// $image->resize(586, 196, $allow_enlarge = True);
				// $image->save($ruta_destino.'Baluarte586x196.jpg');

				$tipoImagen = '.jpg';
				if( strpos($url_imagen, ".png") !== false){
					$tipoImagen = '.png';
				}

				$image = new ImageResize($url_imagen);
				$image->resizeToHeight(196);
				$image->save($ruta_destino.'BaluarteProcess'.$tipoImagen);
				$this->merge($ruta_destino.'base'.$tipoImagen, $ruta_destino.'BaluarteProcess'.$tipoImagen, $ruta_destino.$nombre_imagen.'Baluarte586x196'.$tipoImagen,400);
				unlink( $ruta_destino.'BaluarteProcess'.$tipoImagen );
				$image->resize(80, 80, $allow_enlarge = True);
				$image->save($ruta_destino.$nombre_imagen.'Baluarte80x80'.$tipoImagen);
				return $tipoImagen;
			}

			function guardarImagenZentral($url_imagen,$ruta_destino,$nombre_imagen){
				// $image = new ImageResize($url_imagen);
				// $image->resize(586, 196, $allow_enlarge = True);
				// $image->save($ruta_destino.'Zentral586x196.jpg');

				$tipoImagen = '.jpg';
				if( strpos($url_imagen, ".png") !== false){
					$tipoImagen = '.png';
				}

				$image = new ImageResize($url_imagen);
				$image->resizeToHeight(196);
				$image->save($ruta_destino.'ZentralProcess'.$tipoImagen);
				$this->merge($ruta_destino.'base'.$tipoImagen, $ruta_destino.'ZentralProcess'.$tipoImagen, $ruta_destino.$nombre_imagen.'Zentral586x196'.$tipoImagen,400);
				unlink( $ruta_destino.'ZentralProcess'.$tipoImagen );

				$image->resize(80, 80, $allow_enlarge = True);
				$image->save($ruta_destino.$nombre_imagen.'Zentral80x80'.$tipoImagen);
				return $tipoImagen;
			}

			function guardarImagenBaranain($url_imagen,$ruta_destino,$nombre_imagen){

				// $ruta_imagen = "/home/fermin/Escritorio/htdocs/espectaculos_crawler/imagenes_eventos_origen/baranain.jpg";

				$tipoImagen = '.jpg';
				if( strpos($url_imagen, ".png") !== false){
					$tipoImagen = '.png';
				}

				$image = new ImageResize($url_imagen);
				$image->resizeToHeight(196);
				$image->save($ruta_destino.'BaranainProcess'.$tipoImagen);
				$this->merge($ruta_destino.'base'.$tipoImagen, $ruta_destino.'BaranainProcess'.$tipoImagen, $ruta_destino.$nombre_imagen.'Baranain586x196'.$tipoImagen,360);
				unlink( $ruta_destino.'BaranainProcess'.$tipoImagen );

				// $image = new ImageResize($ruta_imagen);
				// $image->resize(586, 196, $allow_enlarge = True);
				// $image->resizeToHeight(196);
				// $image->save($ruta_destino.'Baranain586x196.jpg');
				$image->resize(80, 80, $allow_enlarge = True);
				$image->save($ruta_destino.$nombre_imagen.'Baranain80x80'.$tipoImagen);
				return $tipoImagen;

			}



			function connect_db(){

				$dbhost = "188.93.73.11";
				$dbname = "bd_espectaculos";
				$dbusername = "especta";
				$dbpassword = "Yuwy19#8";

				// $dbhost = "localhost";
				// $dbname = "bd_espectaculos";
				// $dbusername = "root";
				// $dbpassword = "12345678";

				return new PDO("mysql:host=$dbhost;dbname=$dbname",$dbusername,$dbpassword,array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES => false));
			}

			function existeEvento($link,$evento){
				$statement_exist = $link->prepare("SELECT Id_evento from eventos where Web =:Web");
					$statement_exist->execute(array(':Web' => $evento[6]));
					$row = $statement_exist->fetch(); // Use fetchAll() if you want all results, or just iterate over the statement, since it implements Iterator
					if($row){
						return true;
					}else{
						return false;
					}
			}

			function insertarEvento($link,$evento,$nombre_imagen_grande,$nombre_imagen_pequena){

				$link->beginTransaction();
				try {
					//array($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen);
					$statement = $link->prepare("INSERT INTO eventos(Nombre,Alias,Que_es,Descripcion,Lugar,Cuando,Hora,Imagen_principal,Imagen_calendario,Color,Web,Fecha_creacion,Activo) VALUES(:Nombre,:Alias,:Que_es,:Descripcion,:Lugar,:Cuando,:Hora,:Imagen_principal,:Imagen_calendario,:Color,:Web,:Fecha_creacion,:Activo)");

					$statement->execute(array(
					    "Nombre" =>$evento[0],
					    "Alias" => $this->slug($evento[0]),
					    "Que_es" => $evento[1],
					    "Descripcion"=> $evento[7],
					    "Lugar"=> $evento[5],
					    "Cuando"=> $evento[3],
					    "Hora"=> $evento[4],
					    "Imagen_principal"=> $nombre_imagen_grande,
					    "Imagen_calendario"=> $nombre_imagen_pequena,
					    "Color"=>"#".$this->generateRandomStringColor(6),
					    "Web"=> $evento[6],
					    "Fecha_creacion"=> strtotime(date('Y-m-d')),
					    "Activo"=>'2'
					));

					$evento_id = $link->lastInsertId();

					$statement = $link->prepare("INSERT INTO eventos_fechas(Id_evento,Fecha) VALUES(:evento_id,:fecha)");

					$statement->execute(array(
					    "evento_id" => $evento_id,
					    "fecha" => $evento[2],
					));

					$link->commit();

				} catch (Exception $e) {
					echo $e->getMessage();
				  $link->rollBack();
				}

			}

			function subir_ftp($ruta_imagen,$img,$ruta_destino){
				$host = '188.93.73.11';
				$usr = 'espectaculos';
				$pwd = 'yg35$da/Btw29Je';

				// file to move:
				// $local_file = "/var/www/vhosts/espectaculospamplona.com/httpdocs/crawler/imagen_eventos_convertidas/QDNiBLJSUnoyeme-con-los-ojos-maria-pages-companiaMuseo586x196.jpg";
				// $ftp_path = "/httpdocs/images/eventos/principal/QDNiBLJSUnoyeme-con-los-ojos-maria-pages-companiaMuseo586x196.jpg";

				$local_file = $ruta_imagen . $img;
				$ftp_path = $ruta_destino . $img;

				// echo $local_file."<br>";
				// echo $ftp_path;exit;

				// connect to FTP server (port 21)
				$conn_id = ftp_connect($host, 21) or die ("Cannot connect to host");

				// send access parameters
				ftp_login($conn_id, $usr, $pwd) or die("Cannot login");

				// turn on passive mode transfers (some servers need this)
				// ftp_pasv ($conn_id, true);

				// //Crear folder:
				// if (!ftp_chdir($conn_id, "/httpdocs/nueva_central/images/contenidos/imagenes/".$content_id)) {
				// 	ftp_mkdir($conn_id,"/httpdocs/nueva_central/images/contenidos/imagenes/".$content_id);
				// }else{
				// 	echo "ya existe";
				// }

				// perform file upload
				$upload = ftp_put($conn_id, $ftp_path, $local_file, FTP_ASCII);
				if($upload){
					// echo "subido";
					unlink($local_file);
				}else{
					echo "No subido " . $img;
				}
				// check upload status:
				// print (!$upload) ? 'Cannot upload' : 'Upload complete';
				// print "\n";
			}

			function merge($filename_x, $filename_y, $filename_result,$posicion) {

				// Get dimensions for specified images
				list($width_x, $height_x) = getimagesize($filename_x);
				list($width_y, $height_y) = getimagesize($filename_y);
				// Create new image with desired dimensions
				//$image = imagecreatetruecolor($width_x + $width_y, $height_x);
				$image = imagecreatetruecolor($width_x, $height_x);
				// Load images and then copy to destination image

				if( strpos($filename_y, ".png") !== false){
					$image_x = imagecreatefrompng($filename_x);
					$image_y = imagecreatefrompng($filename_y);
				}else{
					$image_x = imagecreatefromjpeg($filename_x);
					$image_y = imagecreatefromjpeg($filename_y);
				}

				imagecopy($image, $image_x, 0, 0, 0, 0, $width_x, $height_x);
				imagecopy($image, $image_y, $width_x-$posicion, 0, 0, 0, $width_y, $height_y);
				// Save the resulting image to disk (as JPEG)

				if( strpos($filename_y, ".png") !== false){
					imagepng($image, $filename_result);
				}else{
					imagejpeg($image, $filename_result);
				}


				// Clean up
				imagedestroy($image);
				imagedestroy($image_x);
				imagedestroy($image_y);
			}

			function generateRandomString($length = 10) {
			  return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
			}

			function generateRandomStringColor($length = 6) {
			  return substr(str_shuffle("0123456789abcdef"), 0, $length);
			}

			function slug($string) {
		    //Primero definimos nuestro array de caracteres especiales que queremos limpiar en nuestra cadena
		    $characters = array(
		        "Á" => "A", "Ç" => "c", "É" => "e", "Í" => "i", "Ñ" => "n", "Ó" => "o", "Ú" => "u",
		        "á" => "a", "ç" => "c", "é" => "e", "í" => "i", "ñ" => "n", "ó" => "o", "ú" => "u",
		        "à" => "a", "è" => "e", "ì" => "i", "ò" => "o", "ù" => "u"
		     );
		     $string = strtr($string, $characters); //Realiza la conversión de los caracteres
		     $string = strtolower(trim($string)); //Convierte todo a minúsculas
		     $string = preg_replace("/[^a-z0-9-]/", "-", $string);
		     $string = preg_replace("/-+/", "-", $string); //Reemplaza los espacios por guiones medios -
		     //Si el último carácter de la cadena es un guión medio -, lo elimina.
		     if(substr($string, strlen($string) - 1, strlen($string)) === "-") {
		       $string = substr($string, 0, strlen($string) - 1);
		     }
		     return $string;
			}
		/////////////////////////////////////////////////////


	}//Class Funciones
?>