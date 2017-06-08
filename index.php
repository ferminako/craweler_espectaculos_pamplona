<?php
	header("Content-Type: text/html; charset=UTF-8");
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	include 'phpQuery.php';
	include 'funciones.php';
	include 'evento.php';
	include 'bitly.php';


	$funciones = new Funciones();

	// 1º Baluarte
	// $eventosBaluarte = $funciones->traerEventosBaluarte();
	// echo '<pre>';var_dump($eventosBaluarte);echo '</pre>';exit;
	// 2º Barañain
	// $eventosBaranain = $funciones->traerEventosBaranain();
	// echo '<pre>';var_dump($eventosBaranain);echo '</pre>';exit;
	// 3º Gayarre
	// $eventosGayarre = $funciones->traerEventosGayarre();
	// echo '<pre>';var_dump($eventosGayarre);echo '</pre>';exit;
	//4º Museo
	$eventosMuseo = $funciones->traerEventosMuseo();
	echo '<pre>';var_dump($eventosMuseo);echo '</pre>';exit;
?>