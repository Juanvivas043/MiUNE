<?php

class SwapBytes_Crud_Horario {
	public function __construct() {


	}
	 public function makeTable($header , $data){
  		$HTML = $header.$data;
  		return $HTML;
  	}
	public function makeHeader($table_header){
		
		//var_dump($table_header);die;
		//var_dump($this->url);die;
		//$HTML .='<style>  <link rel="stylesheet" type="text/css" href="/css/reports.horariosacademicos.css"></style>

		$HTML .='<!DOCTYPE html>
				<html>
				<head><link rel="stylesheet" type="text/css" href="/MiUNE2/css/reports.horariosacademicos.css"></head>
				<body>
					<table height="115" border="0" cellpadding="0" cellspacing="0">
						<tr valign="top">
						  	<td height="120">
							  	<table width="100%" border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td width="99" rowspan="20" class="horarios_titulo">
											<table width="99" border="0" cellpadding="10" cellspacing="0">
												<tr>
													<td>
														<img src="'.$table_header["url"].'/images/logo_UNE_color.png" width="180" height="75" > 
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td style="font-size: 30px; font-weight: bold;">
											HORARIO DE CLASES
										</td>
									</tr>
									<tr>
										<td class="horarios_titulo_2" style="font-weight: bold;">
											PERIODO: '.strtoupper($table_header["periodo"]).'		</td>
									</tr>
									<tr>
										<td class="horarios_titulo_2" style="font-weight: bold;">
											PENSUM: '.strtoupper($table_header['pensum']).'		</td>
									</tr>
								    <tr>
										<td class="horarios_titulo_2" style="font-weight: bold;">
											SEDE: '.strtoupper($table_header['sede']).'		</td>
									</tr>
									<tr>
										<td class="horarios_titulo_2" style="font-weight: bold;">
											ESCUELA: '.strtoupper($table_header['escuela']).'		</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				';

		return $HTML;
	}

	public function getdata($table_data){
		$pagina = 1;
		$columnas = 1;
		//var_dump($table_data);die;
		$HTML .= '';
		foreach ($table_data as $value1) {//pagina
			//var_dump($value1);
			$HTML .='<div class="pagina'.$pagina.'">';
			foreach ($value1 as $value2) {//grupo
				//var_dump($value2);
				foreach ($value2 as $value3) {//subgrupo
					//var_dump($value3);
					//$HTML .= '<tr>';		
					foreach ($value3 as $value4) {//columna
						//var_dump($value4);die;
						$HTML .= '<div class="columnas">';
						foreach ($value4 as $value5) {
							
							foreach ($value5 as $value6) {
								//var_dump($value6);die;
									switch ($value6["fk_dia"]) {
										case 1:
											$dias = "LUN";
											break;
										case 2:
											$dias = "MAR";
											break;
										case 3:
											$dias = "MIE";
											break;
										case 4:
											$dias = "JUE";
											break;
										case 5:
											$dias = "VIE";
											break;
										case 6:
											$dias = "SAB";
											break;
										case 7:
											$dias = "DOM";
											break;
										case 893:
											$dias = "N/A";
											break;
										default:
											$dias = "NO";
											break;
									}
									$dia = $value6["fk_dia"];
									
									if($columnas > 1){
										$HTML .= '	<div class="dia dia_'.$value6["dia"].'">
														<div>
															<div></div>
														</div>	
														<div class="materiascontenedores">
															<div class="horas" align="center">
																'.substr($value6["horainicio"], 0, -3).'</br>
																'.substr($value6["horafin"], 0, -3).'
															</div>
															<div class="datos">
																<div class="materias" align="center">
																	<h>'.$value6["valor"].'</h>
																</div>
																<div class="profesor" align="center">
																	'.$value6["profesor"].'
																</div>
																<div class="edificioaula" align="center">
																	'.$value6["edificio"].' '.$value6["salon"].'
																</div>
															</div>
															<div class="secciones" align="center">
																	<b>'.$value6["seccion"].'</b>
															</div>
													 	</div>
													</div>';

									}else{
									$HTML .= '	<div class="dia '.$value6["dia"].'">
													<div class="dia_container">
														<div class="dia_value">'.$dias.'</div>
													</div>	
													<div class="materiascontenedores">
														<div class="horas" align="center">
															'.substr($value6["horainicio"], 0, -3).'</br>
															'.substr($value6["horafin"], 0, -3).'
														</div>
														<div class="datos">
															<div class="materias" align="center">
																<h>'.$value6["valor"].'</h>
															</div>
															<div class="profesor" align="center">
																'.$value6["profesor"].'
															</div>
															<div class="edificioaula" align="center">
																'.$value6["edificio"].' '.$value6["salon"].'
															</div>
														</div>
														<div class="secciones" align="center">
																<b>'.$value6["seccion"].'</b>
														</div>
												 	</div>
												</div>';
									}
									
							}
						}
						$HTML .= '</div>';
						$columnas++;
					}
				}

			}
			$HMTL .='</div>';
			$pagina++;
		}
		$HMTL .='<script src="/MiUNE2/js/reports.horariosacademicos.js"></script></body>';
		
		return $HTML;
	}

  	public function makeplantilla($table_header){
  	$HTML .='<!DOCTYPE html>
				<html>
				<head><link rel="stylesheet" type="text/css" href="/MiUNE2/css/reports.horariosacademicos_plantilla.css"></head>
				<body>
					<table height="115" border="0" cellpadding="0" cellspacing="0">
						<tr valign="top">
						  	<td height="120">
							  	<table width="100%" border="0" cellpadding="0" cellspacing="0">
									<tr>
										<td width="99" rowspan="20" class="horarios_titulo">
											<table width="99" border="0" cellpadding="10" cellspacing="0">
												<tr>
													<td>
														<img src="'.$table_header["url"].'/images/logo_UNE_color.png" width="180" height="75" > 
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td style="font-size: 30px; font-weight: bold;">
											HORARIO DE CLASES
										</td>
									</tr>
									<tr>
										<td class="horarios_titulo_2" style="font-weight: bold;">
											PERIODO: '.strtoupper($table_header["periodo"]).'		</td>
									</tr>
									<tr>
										<td class="horarios_titulo_2" style="font-weight: bold;">
											PENSUM: '.strtoupper($table_header['pensum']).'		</td>
									</tr>
								    <tr>
										<td class="horarios_titulo_2" style="font-weight: bold;">
											SEDE: '.strtoupper($table_header['sede']).'		</td>
									</tr>
									<tr>
										<td class="horarios_titulo_2" style="font-weight: bold;">
											ESCUELA: '.strtoupper($table_header['escuela']).'		</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<div class="pagina1">
						<div class="columnas">
							<div class="dia_container">
								<div class="dia_value">LUN</div>
							</div>
							<div class="dia">
								<div class="info">
									<div class="nombre_hora">HORA</div>
									<div class="esc_pen">0501</div>
								</div>
								<div class="dias_container">	
									<div class="materiascontenedores">
										<div class="horas" align="center">
											07:00</br>
											08:30
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											08:40</br>
											10:10
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											10:15</br>
											11:45
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								</div>
							</div>

							<div class="dia">
								<div class="info">
									<div class="nombre_hora">HORA</div>
									<div class="esc_pen">0501</div>
								</div>
								<div class="dias_container">	
									<div class="materiascontenedores">
										<div class="horas" align="center">
											07:00</br>
											08:30
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											08:40</br>
											10:10
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											10:15</br>
											11:45
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								</div>
							</div>
						</div>


						<div class="columnas">
							<div class="dia_container">
								<div class="dia_value">MAR</div>
							</div>
							<div class="dia">
								<div class="info">
									<div class="nombre_hora">HORA</div>
									<div class="esc_pen">0501</div>
								</div>
								<div class="dias_container">	
									<div class="materiascontenedores">
										<div class="horas" align="center">
											07:00</br>
											08:30
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											08:40</br>
											10:10
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											10:15</br>
											11:45
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								</div>
							</div>

							<div class="dia">
								<div class="info">
									<div class="nombre_hora">HORA</div>
									<div class="esc_pen">0501</div>
								</div>
								<div class="dias_container">	
									<div class="materiascontenedores">
										<div class="horas" align="center">
											07:00</br>
											08:30
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											08:40</br>
											10:10
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											10:15</br>
											11:45
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								</div>
							</div>
						</div>


						<div class="columnas">
							<div class="dia_container">
								<div class="dia_value">MIE</div>
							</div>
							<div class="dia">
								<div class="info">
									<div class="nombre_hora">HORA</div>
									<div class="esc_pen">0501</div>
								</div>
								<div class="dias_container">	
									<div class="materiascontenedores">
										<div class="horas" align="center">
											07:00</br>
											08:30
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											08:40</br>
											10:10
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											10:15</br>
											11:45
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								</div>
							</div>

							<div class="dia">
								<div class="info">
									<div class="nombre_hora">HORA</div>
									<div class="esc_pen">0501</div>
								</div>
								<div class="dias_container">	
									<div class="materiascontenedores">
										<div class="horas" align="center">
											07:00</br>
											08:30
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											08:40</br>
											10:10
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											10:15</br>
											11:45
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								</div>
							</div>
						</div>


						<div class="columnas">
							<div class="dia_container">
								<div class="dia_value">JUE</div>
							</div>
							<div class="dia">
								<div class="info">
									<div class="nombre_hora">HORA</div>
									<div class="esc_pen">0501</div>
								</div>
								<div class="dias_container">	
									<div class="materiascontenedores">
										<div class="horas" align="center">
											07:00</br>
											08:30
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											08:40</br>
											10:10
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											10:15</br>
											11:45
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								</div>
							</div>

							<div class="dia">
								<div class="info">
									<div class="nombre_hora">HORA</div>
									<div class="esc_pen">0501</div>
								</div>
								<div class="dias_container">	
									<div class="materiascontenedores">
										<div class="horas" align="center">
											07:00</br>
											08:30
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											08:40</br>
											10:10
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											10:15</br>
											11:45
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								</div>
							</div>
						</div>


						<div class="columnas">
							<div class="dia_container">
								<div class="dia_value">VIE</div>
							</div>
							<div class="dia">
								<div class="info">
									<div class="nombre_hora">HORA</div>
									<div class="esc_pen">0501</div>
								</div>
								<div class="dias_container">	
									<div class="materiascontenedores">
										<div class="horas" align="center">
											07:00</br>
											08:30
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											08:40</br>
											10:10
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											10:15</br>
											11:45
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								</div>
							</div>

							<div class="dia">
								<div class="info">
									<div class="nombre_hora">HORA</div>
									<div class="esc_pen">0501</div>
								</div>
								<div class="dias_container">	
									<div class="materiascontenedores">
										<div class="horas" align="center">
											07:00</br>
											08:30
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											08:40</br>
											10:10
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								 	<div class="materiascontenedores">
										<div class="horas" align="center">
											10:15</br>
											11:45
										</div>
										<div class="datos">
											<div class="materias" align="center">
												<h>INGLES I</h>
											</div>
											<div class="profesor" align="center">
												RAMOS, YERICA
											</div>
											<div class="edificioaula" align="center">
												Edif. 2    Aula: 21 
											</div>
										</div>
										<div class="secciones" align="center">
												<b>U</b>
										</div>
								 	</div>
								</div>
							</div>
						</div>

					</div>
				';

		return $HTML;
	}
}
?>
