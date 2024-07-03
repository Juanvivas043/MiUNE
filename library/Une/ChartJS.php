<?php

/**
*
* Clase para la integracion de Graficos con Chart.js
*
* @category Une
* @package Une_ChartJS
* @version 0.1
* @author Eugenio Fortunato eugenioloxi@gmail.com
* 
*    REQUIRED
*    * Chart.js in view
*    * Custom CSS in view
*    * HTML Tag 
*          <canvas id="chart"></canvas>
*
*
**/

class Une_ChartJS {

     /**
     * Colors
     **/
     protected $colors = array(
          'orange'  => '#E19536',
          'green'   => '#9BBD00',
          'blue'    => '#00787A',
          'wine'    => '#772C46'
     );

     /**
     * Selector
     **/
     protected $selector = 'chart';

	/**
     * Construct
     **/
	function __construct(){
          $this->colors = (object) $this->colors;
	}

     /**
     * Funcion darle formato a la Data para Graficos
     * 
     * @param array $data   
     * @param string $filldata
     * @return string
     **/
     public function fixFormatGraph($data, $filldata = null){
          // Fill
          if (!isset($filldata)) {
               $filldata = '';
          }
          // Labels
          $count  = count($data['labels']) - 1;
          $labels = '[';
          foreach ($data['labels'] as $key => $value) {
               if($key < $count){
                    $labels .= "'" . $value . "', ";
               }
               else {
                    $labels .= "'" . $value . "'";
               }
          }
          $labels .= ']';
          // Datasets
          $count   = count($data['data']);
          $datasets = '[';
          foreach ($data['data'] as $key => $value) {
               // Sub data
               $count    = count($value['data']) - 1;
               $sub_data = '[';
               foreach ($value['data'] as $sub_key => $sub_value) {
                    if($key < $count){
                         $sub_data .= "'" . $sub_value . "', ";
                    }
                    else {
                         $sub_data .= "'" . $sub_value . "'";
                    }
               }
               $sub_data .= ']';
               // Optional Data
                if (isset($value['color'])) {
                    $colordata = "backgroundColor: '" . $value['color'] . "',";
               }else{
                    $colordata = "";
               }
               if (isset($value['width'])) {
                    $widthdata = "borderWidth: '" . $value['width'] . "',";
               }else{
                    $widthdata = "";
               }
               if (isset($value['bordercolor'])) {
                    $borderdata = "borderColor: '" . $value['bordercolor'] . "',";
               }else{
                    $borderdata = "";
               }
               if (isset($value['hovercolor'])) {
                    $hoverdata = "hoverBackgroundColor: '" . $value['hovercolor'] . "',";
               }else{
                    $hoverdata = "";
               }
               // Final Object
               $object = "{" . 
                    'label: ' . "'" . $value['title'] . "'," .
                    'data: ' . $sub_data . "," .
                    $colordata . $widthdata . $borderdata . $hoverdata . $filldata .
               "}";
               // Insert
               if($key < $count){
                    $datasets .= $object . ",";
               }
               else{
                    $datasets .= $object;
               }
          }
          $datasets .= ']';
          // Result
          $result  = "labels: $labels, datasets: $datasets";
          return $result;
     }

     /**
     * Funcion darle formato a las Opciones
     *
     * @param array $title 
     * @param array $legend 
     * @param boolean $BeginAtZerodata   
     * @return string
     **/
     public function fixOptions($title = null, $legend = null, $BeginAtZero = null){

          //Titulo
          if ($title !== Null) {
              $titledata = "display: true,
                            text: '".$title['text']."',
                            fontSize: ".$title['fontSize'].",
                            position: '".$title['position']."'";
          }else{
               $titledata = "";
          }
          //Leyenda
          if ($legend !== Null) {
              $legenddata = "display: true,
                             position: '".$legend['position']."',
                             labels:{
                                      fontColor: '".$legend['fontColor']."',
                                      fontSize:  ".$legend['fontSize']."
                             }";
          }else{
               $legenddata = "";
          }
          //Grafica comienza en cero o no
          if ($BeginAtZero == true) {
               $BeginAtZerodata = " scale: {
                                             ticks: {
                                                 beginAtZero:true
                                             }
                                    }";
          }else{
               $BeginAtZerodata = "";
          }

          //Objeto de opciones
          $result = "title:{" . $titledata . "},
                     legend:{" . $legenddata . "},
                    " . $BeginAtZerodata;
          return $result;
     }

	/**
     * Funcion generar Graficos Basicos
     * Los Graficos de Torta y Dona preferiblemente se tratan con un solo dataset pero pueden tener varios
     * Los graficos de linea usaran el BorderColor y el punto sera backgroundColor
     * 
     * @uses function $this->fixFormatBar()
     * @uses function $this->fixOptions()
     * @param array $data
     * @param array $title 
     * @param array $legend 
     * @param boolean $BeginAtZerodata
     * @param string $type   
     * @return string
     **/
	public function setGraph($data, $title = null, $legend = null, $BeginAtZero = null, $type = null){
          //Graph type
          if ($type !== 'bar' && $type !== 'horizontalBar' && $type !== 'pie' && $type !== 'doughnut' && $type !== 'line' && $type !== 'radar') {
               $type = 'bar';
          }
          if ($type == 'pie' || $type == 'doughnut') {
               $BeginAtZero = false;
          }
          if ($type == 'line') {
               $filldata = "fill : false";
          }        
          // Fix Data
          $data    = $this->fixFormatGraph($data, $filldata);
          $options = $this->fixOptions($title, $legend, $BeginAtZero);
          // Struct JSON
          $json = "var ctx = document.getElementById('$this->selector').getContext('2d');
                    var chart = new Chart(ctx, {
                         type: '$type',
                         data: { $data },
                         options: { $options },
                    });";
                    //echo $json;die;
          return $json;
	}
}

?>