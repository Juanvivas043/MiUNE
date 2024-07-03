<?php
/**
 *
 * @author Daniel Mendez
 */
class Une_Cde_Reportes_ConstanciaTramitacionDeTitulo {
    public function __construct() {
        ini_set("memory_limit","32M");

    }

    public function generar($Ci, $Estudiante) {
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Cache-Control', 'no-cache');
        Zend_Controller_Front::getInstance()->getResponse()->appendBody($this->HTML($Ci, $Estudiante, "Constacia de Tramitación de Titulo"));
    }

function HTML($Ci, $Estudiante, $nombre){ //
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
        body{
            font-family: "Helvetica", sans-serif;
}
p{
    font-size: 12pt;
    line-height: 150%;
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
            <td colspan="3" align="center"><u>CONSTANCIA</u></td>
        </tr>
        <tr>
            <td colspan="3" height="40">&nbsp; </td>
        </tr>
        <tr>
            <td colspan="3"><p align="justify">
                La suscrita, ABOG. EUGENIA MORA HIDALGO, Secretaria (E) de la Universidad Nueva Esparta hace constar que el (la) ciudadano (a) Bachiller:
                <b><?php echo $Estudiante[0]['apellido']; ?>, <?php echo $Estudiante[0]['nombre']; ?></b>, con Cédula de Identidad N°: <b><?php echo $Estudiante[0]['Ci']; ?></b>,
                cursó y aprobó en la Facultad de: <?php echo $Estudiante[0]['facultad']; ?>, Escuela de: <?php echo $Estudiante[0]['escuela']; ?>. Todas las
                asignaturas del pensum de estudios. Asimismo se certifica que el (la) mencionado (a) Bachiller tramita ante las autoridades correspondientes la firma
                y entrega del titulo o diploma que lo (la) acredita como
                <?php
                if ($Estudiante[0]['pk_escuela']==14){
                    echo "Ingeniero Civil";
                }
                if ($Estudiante[0]['pk_escuela']==15){
                    echo "Ingeniero Electrónico";
                }
                if($Estudiante[0]['pk_escuela']==11 || $Estudiante[0]['pk_escuela']==12 || $Estudiante[0]['pk_escuela']==13 || $Estudiante[0]['pk_escuela']==16){
                    echo "Licenciado en {$Estudiante[0]['escuela']}";
                }
                ?>.
            </p></td>
        </tr>
        <tr>
            <td colspan="3"><p align="justify">
                Dado en Caracas <?php if($Estudiante[0]['dia']=='01'){ echo "al";}else{ echo "a los";}; ?> <?php echo $Estudiante[0]['diatxt']; ?> <?php if($Estudiante[0]['dia']=='01'){ echo "día";}else{ echo "días";}; ?> del mes de <?php echo $Estudiante[0]['mes']; ?> de <?php echo $Estudiante[0]['añotxt']; ?>.
            </p></td>
        </tr>
        <tr>
            <td  colspan="3" height="60">&nbsp; </td>
        </tr>
        <tr>
            <td  colspan="2" width="85%"></td
            ><td align="center">
            <table width="100%" border="0">
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
            <td  colspan="3" height="60">&nbsp; </td>
        </tr>
        <tr>
            <td  colspan="2" width="85%"></td
            ><td align="center">
            <table width="100%" border="0">
              <tr>
                <td align="center">________________________________</td>
              </tr>
             <!-- 
               <tr>
                <td align="center">ROSE MARI DÍAZ DEL VALLE</td>
              </tr>
              <tr>
                <td align="center">RECTORA (E)</td>
        </tr>
        -->
              <tr>
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
}

}
?>
