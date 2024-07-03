<?php
/**
 *
 * @author Daniel Mendez
 */
class Une_Cde_Reportes_CertificacionProgramas {
    public function __construct() {
        ini_set("memory_limit","32M");

    }

    public function generar($Ci, $Estudiante) {
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Cache-Control', 'no-cache');
        Zend_Controller_Front::getInstance()->getResponse()->appendBody($this->HTML($Ci, $Estudiante, "Certificación De Programas"));
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
            <td colspan="3" align="center"><u>CERTIFICACIÓN</u></td>
        </tr>
        <tr>
            <td colspan="3" height="40">&nbsp; </td>
        </tr>
        <tr>
            <td colspan="3"><p align="justify">
            La suscrita, ABOG. EUGENIA MORA HIDALGO, Secretaria (E) de la Universidad Nueva Esparta, certifica que los Programas que se anexan, correspondientes al Plan de estudios de la Escuela de <?php echo $Estudiante[0]['escuela']; ?> de la Facultad de: <?php echo $Estudiante[0]['facultad']; ?>, son auténticos, van sellados y firmados y corresponden a las asignaturas cursadas y aprobadas en esta institución por <?php echo $Estudiante[0]['sexo']? 'el ciudadano': 'la ciudadana'; ?>  <b><?php echo $Estudiante[0]['apellido']; ?>, <?php echo $Estudiante[0]['nombre']; ?></b>, portador Cédula de Identidad N°: <b><?php echo $Estudiante[0]['Ci']; ?></b>. Certificado que se expide a solicitud de la parte interesada en Caracas, <?php if($Estudiante[0]['dia']=='01'){ echo "al";}else{ echo "a los";}; ?> <?php echo $Estudiante[0]['diatxt']; ?> <?php if($Estudiante[0]['día']=='01'){ echo "día";}else{ echo "días";}; ?> del mes de <?php echo $Estudiante[0]['mes']; ?> de <?php echo $Estudiante[0]['añotxt']; ?>.
            </p></td>
        </tr>
        <tr>
            <td  colspan="3" height="80">&nbsp; </td>
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
            <td  colspan="3" height="80">&nbsp; </td>
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
