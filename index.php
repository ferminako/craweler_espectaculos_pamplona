<?php
	header("Content-Type: text/html; charset=UTF-8");

	include 'phpQuery.php';
	include 'funciones.php';
	include 'evento.php';
	include 'bitly.php';


	$funciones = new Funciones();

	// 1º Baluarte
	$eventosBaluarte = $funciones->traerEventosBaluarte();

?>