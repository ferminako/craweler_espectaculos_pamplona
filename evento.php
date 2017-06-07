<?php
class Evento{

	public $titulo;
	public $tipo;
	public $fechaUnix;
	public $fechaTexto;
	public $hora;
	public $lugar;
	public $urlWeb;
	public $texto;
	public $precio;
	public $urlCompraEntradas;
	public $imagen;

	function __construct($titulo,$tipo,$fechaUnix,$fechaTexto,$hora,$lugar,$urlWeb,$texto,$precio,$urlCompraEntradas,$imagen){
		$this->titulo = $titulo;
		$this->tipo =	$tipo;
		$this->fechaUnix = $fechaUnix;
		$this->fechaTexto =	$fechaTexto;
		$this->hora =	$hora;
		$this->lugar = $lugar;
		$this->urlWeb =	$urlWeb;
		$this->texto = $texto;
		$this->precio =	$precio;
		$this->urlCompraEntradas = $urlCompraEntradas;
		$this->$imagen = $imagen;
	}


}
?>