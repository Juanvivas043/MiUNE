<?php
/**
 *
 * @author Daniel Mendez
 */
class Une_Cde_Reportes_Equivalencias {

    var $cm = 28.3;


    public function __construct() {
        ini_set("memory_limit","32M");


    }

    public function generar($Ci, $Estudiante, $EquivalenciaDefinitivo, $Traslado, $Universidades) {

        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Cache-Control', 'no-cache');
        Zend_Controller_Front::getInstance()->getResponse()->appendBody($this->HTML($Asignaturas, $Ci, $Estudiante, "Equivalencias", $EquivalenciaDefinitivo, $Traslado, $Universidades));
    }



    private function HTML($Asignaturas, $Ci, $Estudiante, $nombre, $EquivalenciaDefinitivo, $Traslado, $Universidades){ //
?>

<head>
    <script>

   // set portrait orientation
   jsPrintSetup.setOption('orientation', jsPrintSetup.kPortraitOrientation);
   //para q no ajuste a la pagina
   jsPrintSetup.setOption('shrinkToFit', 1);
   //margenes
   jsPrintSetup.setOption('marginTop', 3);
   jsPrintSetup.setOption('marginBottom', 15);
   jsPrintSetup.setOption('marginLeft', 15);
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
    </style>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php $nombre; ?></title>
</head>
<head>

            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <title><?php $nombre; ?></title>
            </head>

            <body>

<?php
$count = 0; //Conteo de las lineas a imprimir
if(!empty($EquivalenciaDefinitivo)){
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
    $this->textoequivalencia($articulo, $Universidad, $EquivalenciaDefinitivo[0]['observacion']);
    $this->footer();
    $this->pagebreak();
}
if(!empty($Traslado)){
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
                if($i>51){
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
            <td  colspan="2" width="85%"></td
            ><td align="center">
            <table width="100%" border="0" style="font-size: 12pt">
              <tr>
                <td align="center">________________________________</td>
              </tr>
              <tr>
                <td align="center">LIC. HAYDEÉ IRAUSQUÍN ALDAMA</td>
              </tr>
              <tr>
                <td align="center">SECRETARIA (E)</td>
              </tr>
            </table>

            </td>
        </tr>
        </table>
<?php
}
private function textotraslado($articulo, $Universidad, $infoacta){
?>
<table width="620" style=" font-size: 12pt">
        <tr>
            <td height="50">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3"><p align="justify">
                                Asignaturas aprobadas en<?php echo "$articulo $Universidad"?>,
                                Reconocidas Académicamente por Traslado para la Universidad Nueva Esparta,
                                según acta de reconocimiento académico de asignaturas por traslado
                                <?php echo $infoacta?>.
            </p></td>
        </tr>
        <tr>
            <td height="50">&nbsp;</td>
        </tr>
</table>
<?php
}
private function textoequivalencia($articulo, $Universidad, $infoacta){
?>
<table width="620" style=" font-size: 12pt">
        <tr>
            <td height="50">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="3"><p align="justify">
                                Asignaturas Reconocidas por Equivalencias de Estudios según dictamen del
                                Consejo Directivo<?php echo "$articulo $Universidad"?>,
                                <?php echo $infoacta?>, por solicitud de estudios aprobados Y
                                de conformidad con el convenio suscrito entre La Universidad Nueva Esparta, <?php echo $Universidad?>

            </p></td>
        </tr>
        <tr>
            <td height="50">&nbsp;</td>
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