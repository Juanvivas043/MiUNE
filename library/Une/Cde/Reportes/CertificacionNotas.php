<?php

/**
 *
 * @author Daniel Mendez
 */
class Une_Cde_Reportes_CertificacionNotas {

    var $cm = 28.3;


    public function __construct() {
        ini_set("memory_limit","32M");
        ini_set('display_errors', 0);

    }

    public function generar($Ci, $Asignaturas, $Estudiante, $iIndiceAcum, $iTUCA, $EquivalenciaDefinitivo, $Traslado, $Universidades, $Servicio="",$nuevoPensum = NULL) {
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Cache-Control', 'no-cache');
        //$HTML = $this->HTML($Asignaturas, $Ci, $Estudiante, $iIndiceAcum, $iTUCA, "Certificación De Pensum", $Servicio);
        Zend_Controller_Front::getInstance()->getResponse()->appendBody($this->HTML($Asignaturas, $Ci, $Estudiante, $iIndiceAcum, $iTUCA, "Certificación De Pensum", $EquivalenciaDefinitivo, $Traslado, $Universidades, $Servicio));
        //Zend_Controller_Front::getInstance()->getResponse()->appendBody($this->HTMLequiv($Asignaturas, $Ci, $Estudiante, "Equivalencias", $EquivalenciaDefinitivo, $Traslado, $Universidades));

    }

    function HTML($Asignaturas, $Ci, $Estudiante, $iIndiceAcum, $iTUCA, $nombre, $EquivalenciaDefinitivo, $Traslado, $Universidades, $Servicio){ //
?>

<head>
    <script>

   // set portrait orientation
   jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
   //para q no ajuste a la pagina
   jsPrintSetup.setOption('shrinkToFit', 1);
   //margenes
   jsPrintSetup.setOption('marginTop', 3);
   jsPrintSetup.setOption('marginBottom', 3);
   jsPrintSetup.setOption('marginLeft', 10);
   // set page header
   jsPrintSetup.setOption('headerStrLeft', '');
   jsPrintSetup.setOption('headerStrCenter', '');
   jsPrintSetup.setOption('headerStrRight', '');
   // set empty page footer
   jsPrintSetup.setOption('footerStrLeft', '');
   jsPrintSetup.setOption('footerStrCenter', '');
   jsPrintSetup.setOption('footerStrRight', '');
   jsPrintSetup.print();

    </script>
<style>
.pagebreak{
    page-break-before:always;
}
body{
    font-family: "Helvetica", sans-serif;
}
table{
    font-family: "Helvetica", sans-serif;
    font-size: 10pt
}
p{
    font-size: 12pt;
    line-height: 150%;
}
.SingleText{
    font-size: 5pt;
}
.TextSingle{
    font-size: 6pt;
}
.TableHeader{
    font-size: 5pt;
}
.infoPeriodo{
    font-size: 6pt;
}
    </style>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php $nombre; ?></title>
</head>
<body >
    <table width="620"  border="0" cellpadding="0" cellspacing="0">
        <tbody><tr>
        <td width="240">
               </td>
        <td valign="top" align="LEFT">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size: 10pt">
                <tbody><tr>
                    <td>UNIVERSIDAD NUEVA ESPARTA</td>
                </tr>
                <tr>
                    <td>CARACAS - VENEZUELA</td>
                </tr>
                <tr>
                    <td>Facultad: <?php echo $Estudiante[0]['facultad']; ?> </td>
                </tr>

                <tr>
                    <td>Escuela: <?php echo $Estudiante[0]['escuela']; ?></td>
                </tr>
            </tbody></table>
        </td>
    </tr>
</tbody></table>
    <table width="620" style=" font-size: 12pt">
    <tbody >
        <tr>
            <td colspan="3" height="80">&nbsp; </td>
        </tr>
        <tr>
            <td colspan="3" align="center"><u>CERTIFICACIÓN</u></td>
        </tr>
        <tr>
            <td colspan="3" height="40">&nbsp; </td>
        </tr>
        <tr>
            <td colspan="3"><p align="justify">
                                La suscrita, ABOG. EUGENIA MORA HIDALGO, Secretaria (E) de la Universidad Nueva Esparta, certifica que las calificaciones que se anexan, son auténticas, van selladas y firmadas y corresponden a las asignaturas cursadas y aprobadas en la Escuela de <?php echo $Estudiante[0]['escuela']; ?> de la Facultad de <?php echo $Estudiante[0]['facultad']; ?> de esta institución por <?php echo $Estudiante[0]['sexo']? 'el ciudadano': 'la ciudadana'; ?> <b><?php echo $Estudiante[0]['apellido']; ?>, <?php echo $Estudiante[0]['nombre']; ?></b>, portador de la Cédula de Identidad N°: <b><?php echo $Estudiante[0]['Ci']; ?></b>. Certificación que se expide a solicitud de la parte interesada en Caracas, <?php if($Estudiante[0]['dia']=='01'){ echo "al";}else{ echo "a los";}; ?> <?php echo $Estudiante[0]['diatxt']; ?> <?php if($Estudiante[0]['dia']=='01'){ echo "día";}else{ echo "días";}; ?> del mes de <?php echo $Estudiante[0]['mes']; ?> de <?php echo $Estudiante[0]['añotxt']; ?>.
            </p></td>
        </tr>
        <tr>
            <td  colspan="3" height="40">&nbsp; </td>
        </tr>
        <tr>
            <td  colspan="2" width="85%"></td
            ><td align="center">
                <table width="100%" border="0" style="font-size: 12pt">
              <tr>
                <td align="center">________________________________</td>
              </tr>
              <tr>
                <td align="center">ABOG. EUGENIA MORA HIDALGO</td>
              </tr>
              <tr>
                <td align="center">SECRETARIA (E)</td>
              </tr>
            </table>

            </td>
        </tr>
        <tr>
            <td colspan="3" height="50">&nbsp; </td>
        </tr>
        <tr>
           <td colspan ="3"><p align="justify">
                 En mi carácter de Rector de la Universidad Nueva Esparta certifico que la firma anterior es de puño y letra de la Secretaria (E) de esta Universidad. Dado en Caracas <?php if($Estudiante[0]['dia']=='01'){ echo "al";}else{ echo "a los";}; ?> <?php echo $Estudiante[0]['diatxt']; ?> <?php if($Estudiante[0]['dia']=='01'){ echo "día";}else{ echo "días";}; ?> del mes de <?php echo $Estudiante[0]['mes']; ?> de <?php echo $Estudiante[0]['añotxt']; ?>.
               </p></td>
        </tr>
        <tr>
            <td  colspan="3" height="40">&nbsp; </td>
        </tr>
        <tr>
            <td  colspan="2" width="85%"></td
            ><td align="center">
            <table width="100%" border="0" style="font-size: 12pt">
              <tr>
                <td align="center">________________________________</td>
              </tr>
             <!-- <tr>
                <td align="center">ROSE MARI DÍAZ DEL VALLE</td>
              </tr>
              <tr>
                <td align="center">RECTORA (E)</td>
              </tr>
              <tr>-->
                <td align="center">DR. JESÚS ALBERTO RAMÍREZ</td>
              </tr>
              <tr>
                <td align="center">RECTOR</td>
              </tr>
              

            </table>
            </td>
        </tr>
    </tbody>
</table>

</body>
<?php


//Simulacion para generar un conteo del numero de materias de todos los periodos a imprimir
$primerapag=1;
$t=-1;
       foreach($Asignaturas as $asignatura) {
           if($peri != $asignatura['periodo']){
           $t++;
           $mat =0;
           }
           $periodos[$t]['pos'] = $t;
           if(isset($asignatura['materia'])) $mat ++;
           $periodos[$t]['nmat'] = $mat;
           $periodos[$t]['per'] = $asignatura['periodo'];
           $peri = $asignatura['periodo'];
       }
       $t=0;

        foreach($Asignaturas as $asignatura) {


            if($per == $asignatura['periodo']) {
                $swt = 1;

            }else {
                $swt = 0;

            }
            $per = $asignatura['periodo'];

            if($i == 0 || $i == 2 && $swt == 0) {
                $ultimapag=$totalpag;
                $totalpag = 0;
                $i=0;

                if($primerapag == 0){



?>

        <table width="620">
        <tr>
            <td  colspan="2" width="100%"></td
            ><td align="center">
            <table width="100%" border="0" style="font-size: 12pt">
              <tr>
                <td align="center">________________________________</td>
              </tr>
             <tr>
                <td align="center" style="font-size: 10px !important;">ABOG. EUGENIA MORA HIDALGO - <span style="font-style: italic !important;">SECRETARIA (E)</span></td>
              </tr>
            </table>

            </td>
        </tr>
        </table>
</body>

<?php
                }

                ?>
            <head>

            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <title><?php $nombre; ?></title>
            </head>

            <body>
              <table width="620"  border="0" cellpadding="0" cellspacing="0" class="pagebreak" style="display: block;">
                <tbody>
                  <tr>
                    <td width="240"></td>
                    <td valign="top" align="LEFT">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size: 10pt">
                          <tbody>
                            <tr>
                                <td class="TextSingle">UNIVERSIDAD NUEVA ESPARTA</td>
                            </tr>
                            <tr>
                                <td class="TextSingle">CARACAS - VENEZUELA</td>
                            </tr>
                            <tr>
                                <td class="TextSingle">Facultad: <?php echo $Estudiante[0]['facultad']; ?> </td>
                            </tr>

                            <tr>
                                <td class="TextSingle">Escuela: <?php echo $Estudiante[0]['escuela']; ?></td>
                            </tr>
                            <tr>
                                <td class="TextSingle">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="TextSingle">BR: <?php echo $Estudiante[0]['apellido']; ?>, <?php echo $Estudiante[0]['nombre']; ?></td>
                            </tr>
                            <tr>
                                <td class="TextSingle">C.I.: <?php echo $Estudiante[0]['Ci']; ?></td>
                            </tr>
                            <tr>
                                <td class="TextSingle">&nbsp;</td>
                            </tr>
                          </tbody>
                        </table>
                    </td>
                  </tr>
               </tbody>
              </table>



                <?php
                $primerapag = 0;
            }
            if($swt == 0) {
                ?>

        <td>
            <table width="620" style="font-size: 8pt">
                <tr><td colspan="7" class="TextSingle"><b><?php

                  echo "Período Lectivo:";

                ?> </b><?php echo $asignatura['inicio']." / ".$asignatura['fin']; ?></td></tr>
                <tr>
                    <td width="80" class="TableHeader" style="text-align:center"><b>Código</b></td>
                    <td class="TableHeader" style="text-align:center"><b>Asignatura</b></td>
                    <td class="TableHeader" width="120" style="text-align:center"><b>Calificación</b></td>
                    <td class="TableHeader" width="50" style="text-align:center"><b>U.C.</b></td>
                    <td class="TableHeader" width="100" style="text-align:center"><b>Observaciones</b></td>
                </tr>
                <?php
                $swt=1;
                $i++;
            }

            //MATERIAS
            if(isset($asignatura['materia'])) {
                ?>
            <tr>
            <td class="SingleText" style="text-align:center"><?php echo $asignatura['codigo2']; ?></td>
            <td class="SingleText" style="text-align:left"><?php echo $asignatura['materia']; ?></td>
            <td class="SingleText" style="text-align:left"><?php echo $asignatura['nota']." ".$asignatura['notatxt']; ?></td>
            <td class="SingleText" style="text-align:center"><?php echo $asignatura['uc']; ?></td>
            <td class="SingleText" style="text-align:center"><?php echo $asignatura['observacion']; ?></td>
            </tr>

                <?php
                $count ++;
            }
            if($per == $asignatura['periodo'] && isset($asignatura['uccomputadas'])) {
                $totalpag += ($count+6);
                $count = 0;
                ?>
            <tr><td class="infoPeriodo" colspan="7"><b>Indice acad&eacute;mico del periodo:</b><?php echo $asignatura['promedio']; ?></td></tr>
            <tr><td class="infoPeriodo" colspan="7"><b>Unidades de credito aprobadas:</b><?php echo $asignatura['ucaprobadas']; ?></td></tr>
            <tr><td class="infoPeriodo" colspan="7"><b>Unidades de credito computadas:</b><?php echo $asignatura['uccomputadas']?></td></tr>
            <!--<tr><td class="infoPeriodo" colspan="7">&nbsp <?php //echo $totalpag." - ".$ultimapag." - ".$periodos[$t+1]['nmat']." - ".$t." - ".$periodos[$t+1]['per']?></td></tr>-->
            </table>

        </td>


                <?php
                if(($totalpag + $periodos[$t+1]['nmat']<54) && $i==2)$i-=1;  //REviso si cabe el proximo periodo para disminuri el contador i y que coloco un periodo mas
                                $t++;//contador de periodos dibujados
            }

?>

<?php

        }
        if ($totalpag + 3 <67){//Verifico si cabe la leyenda en esa pagina
?>
        <table width="620" style=" font-size: 6pt">
    <tbody style="font-size: 12px !important; margin: 2px 0px !important;">
        <tr>
            <td colspan="3">&nbsp; </td>
        </tr>
        <tr>
            <td colspan="2">Créditos Acumulados Aprobados: <?php echo $iTUCA; ?></td>
            <td colspan="2">Indice Académico Acumulado: <?php echo $iIndiceAcum; ?></td>
        </tr>
        <?php
        $sc="<tr><td colspan='3' height='20'>$Servicio</td></tr>";
        if($Servicio!=""){
           echo $sc;
        }
        ?>
        <tr>
            <td colspan="3" height="20">- La escala de calificaciones es del cero cero(00) al veinte(20). </td>
        </tr>
        <tr>
            <td colspan="3">- La mínima aprobatoria son diez (10) puntos de calificaciones.</td>
        </tr>
        <tr>
            <td  colspan="3">Se expide la presente Certificación  en Caracas
                <?php if($Estudiante[0]['dia']=='01'){ echo "al";}else{ echo "a los";}; ?> <?php echo $Estudiante[0]['diatxt']; ?> <?php if($Estudiante[0]['dia']=='01'){ echo "día";}else{ echo "dias";}; ?>
                del  mes de <?php echo $Estudiante[0]['mes']; ?> de <?php echo $Estudiante[0]['añotxt']; ?>.</td>
        </tr>
        <tr>
            <td  colspan="2" width="100%"></td
            ><td align="center">
            <table width="100%" border="0" style="font-size: 10pt; margin-top: 10px;">
              <tr>
                <td align="center">________________________________________</td>
              </tr>
              <tr>
                <td align="center" style="font-size: 10px !important;">ABOG. EUGENIA MORA HIDALGO - <span style="font-style: italic !important;">SECRETARIA (E)</span></td>
              </tr>
            </table>

            </td>
        </tr>
    </tbody>
</table>
<?php

        }else{
?>
       <table width="620">
<tr>
            <td  colspan="2" width="100%"></td
            ><td align="center">
            <table width="100%" border="0" style="font-size: 10pt; margin-top: 10px;">
              <tr>
                <td align="center">________________________________</td>
              </tr>
              <tr>
                <td align="center" style="font-size: 10px !important;">ABOG. EUGENIA MORA HIDALGO - <span style="font-style: italic !important;">SECRETARIA (E)</span></td>
              </tr>
            </table>

            </td>
        </tr>
        </table>

</body>

   <body>
    <table width="620"  border="0" cellpadding="0" cellspacing="0"  class="pagebreak">
        <tbody><tr>
        <td width="240">
               </td>
        <td valign="top" align="LEFT">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size: 10pt">
                <tbody><tr>
                                <td class="TextSingle">UNIVERSIDAD NUEVA ESPARTA</td>
                            </tr>
                            <tr>
                                <td class="TextSingle">CARACAS - VENEZUELA</td>
                            </tr>
                            <tr>
                                <td class="TextSingle">Facultad: <?php echo $Estudiante[0]['facultad']; ?> </td>
                            </tr>

                            <tr>
                                <td class="TextSingle">Escuela: <?php echo $Estudiante[0]['escuela']; ?></td>
                            </tr>
                            <tr>
                                <td class="TextSingle">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="TextSingle">BR: <?php echo $Estudiante[0]['apellido']; ?>, <?php echo $Estudiante[0]['nombre']; ?></td>
                            </tr>
                            <tr>
                                <td class="TextSingle">C.I.: <?php echo $Estudiante[0]['Ci']; ?></td>
                            </tr>
                            <tr>
                                <td class="TextSingle">&nbsp;</td>
                            </tr>
            </tbody></table>
        </td>
    </tr>
</tbody></table>
    <table width="620" style=" font-size: 8pt">
    <tbody >
        <tr>
            <td colspan="3" height="80">&nbsp; </td>
        </tr>
        <tr>
            <td colspan="2">Créditos Acumulados Aprobados: <?php echo $iTUCA; ?></td>
            <td colspan="2">Indice Académico Acumulado: <?php echo $iIndiceAcum; ?></td>
        </tr>
        <?php
        $sc="<tr><td colspan='3' height='20'>$Servicio </td></tr>";
        if($Servicio!="")
        {
           echo $sc;
    }
        ?>
        <tr>
            <td colspan="3" height="20">- La escala de calificaciones es del cero cero(00) al veinte(20). </td>
        </tr>
        <tr>
            <td colspan="3">- La mínima aprobatoria son diez (10) puntos de calificaciones.</td>
        </tr>
        <tr>
            <td  colspan="3">Se expide la presente Certificación  en Caracas
                <?php if($Estudiante[0]['dia']=='01'){ echo "al";}else{ echo "a los";}; ?> <?php echo $Estudiante[0]['diatxt']; ?> <?php if($Estudiante[0]['dia']=='01'){ echo "día";}else{ echo "dias";}; ?>
                del  mes de <?php echo $Estudiante[0]['mes']; ?> de <?php echo $Estudiante[0]['añotxt']; ?>.</td>
        </tr>
        <tr>
            <td colspan="3" height="30">&nbsp; </td>
        </tr>
        <tr>
            <td  colspan="2" width="100%"></td
            ><td align="center">
            <table width="100%" border="0" style="font-size: 10pt">
              <tr>
                <td align="center">________________________________</td>
              </tr>
              <tr>
                <td align="center" style="font-size: 10px !important;">ABOG. EUGENIA MORA HIDALGO - <span style="font-style: italic !important;">SECRETARIA (E)</span></td>
              </tr>
            </table>

            </td>
        </tr>
    </tbody>
</table>

</body>
<?php
        }
        $this->HTMLequiv($Asignaturas, $Ci, $Estudiante, "Equivalencias", $EquivalenciaDefinitivo, $Traslado, $Universidades);
}

 public function generarequiv($Ci, $Estudiante, $EquivalenciaDefinitivo, $Traslado, $Universidades) {

        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Cache-Control', 'no-cache');
        Zend_Controller_Front::getInstance()->getResponse()->appendBody($this->HTMLequiv($Asignaturas, $Ci, $Estudiante, "Equivalencias", $EquivalenciaDefinitivo, $Traslado, $Universidades));
    }



    private function HTMLequiv($Asignaturas, $Ci, $Estudiante, $nombre, $EquivalenciaDefinitivo, $Traslado, $Universidades){ //
?>
<head>

            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <title><?php $nombre; ?></title>
            </head>

            <body>

<?php
$count = 0; //Conteo de las lineas a imprimir
if(!empty($EquivalenciaDefinitivo)){
    $this->pagebreak();
    $this->encabezado($Estudiante);
    $Universidad = '';
    $observacion = 'Reconocida por Equivalencia';
    $this->contenido($EquivalenciaDefinitivo, $observacion, 'Equivalencias', $Estudiante);
    foreach($Universidades as $Uni){
        $name = strrev(array_pop(explode(' ',strrev($Uni['universidad']))));
        if($name == 'Colegio' && $countuni == 0){
            $articulo = ' del ';
        }elseif($countuni==0){
            $articulo = ' de la ';
        }
        if($Uni['tipo']==1266){
            if($countuni>0){
            if($name == 'Colegio'){
                $art = ' el ';
            }elseif($countuni==0){
                $art = ' la ';
            }
            $Universidad .= "y$art {$Uni['universidad']}";
            }else{
            $Universidad .= $Uni['universidad'];
            }
            $countuni ++;
        }
    }
    if(count($EquivalenciaDefinitivo) >= 60){
?><tr>
            <td colspan="3" height="30">&nbsp; </td>
            </tr><?php
       $this->footer();
       $this->pagebreak();
       $this->encabezado($Estudiante);
       $this->textoequivalencia($articulo, $Universidad, $EquivalenciaDefinitivo[0]['observacion']);
       $this->footer();
    }else{
       $this->textoequivalencia($articulo, $Universidad, $EquivalenciaDefinitivo[0]['observacion']);
       $this->footer();
    }
}
if(!empty($Traslado)){
    $this->pagebreak();
    $this->encabezado($Estudiante);
    $Universidad = '';
    $countuni =0;
    $observacion = 'Reconocida Academicamente';
    $this->contenido($Traslado, $observacion, 'Traslados', $Estudiante);
    foreach($Universidades as $Uni){
        $name = strrev(array_pop(explode(' ',strrev($Uni['universidad']))));
        if($name == 'Colegio' && $countuni == 0){
            $articulo = ' el ';
        }elseif($countuni==0){
            $articulo = ' la ';
        }
        if($Uni['tipo']==1264){
            if($countuni>0){
            if($name == 'Colegio'){
                $art = ' el ';
            }elseif($countuni==0){
                $art = ' la ';
            }
            $Universidad .= "y$art {$Uni['universidad']}";
            }else{
            $Universidad .= $Uni['universidad'];
            }
            $countuni ++;
        }
    }
    $this->textotraslado($articulo, $Universidad, $Traslado[0]['observacion']);
    $this->footer();
}
?>
</body>


<?php
        }

private function contenido($Asignaturas, $observacion, $Titulo, $Estudiante){
////(, $EquivalenciaDefinitivo, $EquivalenciaTransitorio, $EquivalenciaTransitorio)


                ?>



        <td>
            <table width="620" style="font-size: 8pt">
                <!--<tr>
                    <td style="text-align:center; font-size: 12pt" colspan="4"><b><?php echo $Titulo; ?></b></td>
                </tr>-->
                <tr>
                    <td width="80" class="TableHeader" style="text-align:center"><b>Código</b></td>
                    <td class="TableHeader" style="text-align:center"><b>Asignatura</b></td>
                    <td class="TableHeader" width="50" style="text-align:center"><b>U.C.</b></td>
                    <td class="TableHeader" width="160" style="text-align:center"><b>Observaciones</b></td>
                </tr>
                <?php
                foreach($Asignaturas as $asignatura) {
                if(($i%52) == 0 && $i!=0){
                    $i=0;
?>
</tr>
</table>

</td>
<?php
                    $this->pagebreak();
                    $this->encabezado($Estudiante);
?>
<td>
            <table width="620" style="font-size: 8pt">
                <!--<tr>
                    <td style="text-align:center; font-size: 12pt" colspan="4"><b><?php echo $Titulo; ?></b></td>
                </tr>-->
                <tr>
                    <td width="80" class="TableHeader" style="text-align:center"><b>Código</b></td>
                    <td class="TableHeader" style="text-align:center"><b>Asignatura</b></td>
                    <td class="TableHeader" width="50" style="text-align:center"><b>U.C.</b></td>
                    <td class="TableHeader" width="160" style="text-align:center"><b>Observaciones</b></td>
                </tr>
<?php
                }

                $swt=1;
                $i++;
                $tuc += $asignatura['uc'];


            //MATERIAS
                ?>
            <tr>
            <td class="SingleText" style="text-align:center"><?php echo $asignatura['codigopropietario']; ?></td>
            <td class="SingleText" style="text-align:left"><?php echo $asignatura['materia']; ?></td>
            <td class="SingleText" style="text-align:center"><?php echo $asignatura['uc']; ?></td>
            <td class="SingleText" style="text-align:center"><?php echo $observacion; ?></td>
            </tr>

                <?php

        }
                ?>
            <tr>
                <td class="TableHeader" style="text-align:center" colspan="2"><b>Total Unidades de Crédito</b></td>

            <td class="SingleText" style="text-align:center"><?php echo $tuc; ?></td>
            <td class="SingleText" style="text-align:center">&nbsp;</td>
            </tr>
            </table>

        </td>


                <?php
    }
private function footer(){
?>
        <table width="620">
<tr>
            <td  colspan="2" width="100%"></td
            ><td align="center">
            <table width="100%" border="0" style="font-size: 12pt">
              <tr>
                <td align="center">________________________________</td>
              </tr>
              <tr>
                <td align="center" style="font-size: 12px !important;">ABOG. EUGENIA MORA HIDALGO - <span style="font-style: italic !important;">SECRETARIA (E)</span></td>
              </tr>
            </table>

            </td>
        </tr>
        </table>
<?php
}
private function textotraslado($articulo, $Universidad, $infoacta){
?>
<table width="620" style=" font-size: 10px">
        <tr>
            <td colspan="3"><p align="justify" style="font-size: 10.5px !important; margin: 5px 0px !important;">
                                Asignaturas aprobadas en<?php echo "$articulo $Universidad"?>, reconocidas académicamente por Traslado para la Universidad Nueva Esparta, según el acta de reconocimiento académico de asignaturas por traslado <?php echo $infoacta?>.
            </p></td>
        </tr>
</table>
<?php
}
private function textoequivalencia($articulo, $Universidad, $infoacta){
?>
<table width="620" style=" font-size: 10px">
        <tr>
            <td colspan="3"><p align="justify" style="font-size: 10.5px !important; margin: 5px 0px !important;">
                                Asignaturas reconocidas por Equivalencias de Estudios según dictamen del
                                Consejo Directivo<?php echo "$articulo $Universidad"?>,
                                <?php echo $infoacta?>, por solicitud de estudios aprobados Y
                                de conformidad con el convenio suscrito entre La Universidad Nueva Esparta, <?php echo $Universidad?>

            </p></td>
        </tr>
</table>
<?php
}

private function pagebreak(){
?>
        <table width="620" style=" font-size: 12pt" class="pagebreak">
        <tr>
            <td>&nbsp;</td>
        </tr>

</table>
<?php
}
private function encabezado($Estudiante){
?>
                <table width="620"  border="0" cellpadding="0" cellspacing="0">
                    <tbody><tr>
                    <td width="240">
                           </td>
                    <td valign="top" align="LEFT">
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size: 10pt">
                            <tbody><tr>
                                <td class="TextSingle">UNIVERSIDAD NUEVA ESPARTA</td>
                            </tr>
                            <tr>
                                <td class="TextSingle">CARACAS - VENEZUELA</td>
                            </tr>
                            <tr>
                                <td class="TextSingle">Facultad: <?php echo $Estudiante[0]['facultad']; ?> </td>
                            </tr>

                            <tr>
                                <td class="TextSingle">Escuela: <?php echo $Estudiante[0]['escuela']; ?></td>
                            </tr>
                            <tr>
                                <td class="TextSingle">&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="TextSingle">BR: <?php echo $Estudiante[0]['apellido']; ?>, <?php echo $Estudiante[0]['nombre']; ?></td>
                            </tr>
                            <tr>
                                <td class="TextSingle">C.I.: <?php echo $Estudiante[0]['Ci']; ?></td>
                            </tr>
                            <tr>
                                <td class="TextSingle">&nbsp;</td>
                            </tr>
                        </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
                </table>
<?php
}

}
?>
