<?php

//require_once 'Zend/Pdf/Page.php';

/**
 *
 * @author Lic. Nicola Strappazzon C.
 */
class Une_Cde_Reportes_ActadeCalificaciones {
    const SIZE_OFICIO_P   = '612:1008:';
    const SIZE_OFICIO_L   = '1008:612:';
    const SIZE_OFICIO2_P  = '850:1400:';
    const SIZE_OFICIO2_L  = '1400:850:';
    const margenl         = 10 ;
    const GS              = 0.8;

    public function __construct() {
        ini_set("memory_limit","32M");

        Zend_Loader::loadClass('Zend_Pdf');

        $this->pdf  = new Zend_Pdf();
        $this->page = new Zend_Pdf_Page(Une_Cde_Reportes_ActadeCalificaciones::SIZE_OFICIO2_L);
    }

    public function generar($listasr) {
        $this->page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 15);
        
        $width  = $this->page->getWidth();
        $height = $this->page->getHeight();

        if(isset($listasr)) {
            $i=1;
            $j=1;
            foreach($listasr as $lista) {
                if($i==31) {
                    $this->pdf->pages[] = $this->page;
                    $this->page = new Zend_Pdf_Page(Une_Cde_Reportes_ActadeCalificaciones::SIZE_OFICIO2_L);
                    $this->page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 15);
                    $i=1;
                }
                
                if($i==1) {
                   // $margenx = 54;
                    $this->drawMatrizLR((537.25)+$margenx, ($height-(68.3+$margeny)), $lista['Docente C.I.'], 'CI');
                    $this->drawMatrizLR((745.9)+$margenx, ($height-(68.3+$margeny)), $lista['AsignaturaCodigo'], 'CODA',$lista['Seccion'],$lista['NTurno']);
                    $this->drawMatrizLR((993.55)+$margenx, ($height-(68.3+$margeny)), $lista['Periodo'], 'PER');
                    $this->drawEncabezadoLR($lista['Sede'], $lista['Seccion'], $lista['Escuela'], $lista['Periodo'], $lista['Turno'], $lista['NTurno'], $lista['Semestre'], $lista['AsignaturaNombre'], $lista['AsignaturaCodigo'], $lista['Docente'], (207.7)+$margenx, ($height-(101.4+$margeny)));
                }
                
                $this->drawAlumnoLR((26.35)+$margenx, ($height-(308.5+$margeny)), $j, $lista['Estudiante C.I.'], $lista['Apellido'].' '.$lista['Nombre'], $lista['Calificacion'], $lista['Notatxt'], $i);
                $i++;
                $j++;
            }
        }
        
        $this->pdf->pages[] = $this->page;

        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Cache-Control', 'no-cache');
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', 'application/pdf', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Transfer-Encoding:', 'binary');
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Description', 'File Transfer');
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', 'attachment; filename=actadecalificaciones'.$lista['AsignaturaCodigo'].$lista['Seccion'].'.pdf');
        

        Zend_Controller_Front::getInstance()->getResponse()->appendBody($this->pdf->render());

    }

    //Listas Rojas
    public function drawMatrizLR($x, $y=1146, $param1, $param2, $param3='U', $param4=0) {
    //$y=1148 + margenl;
        $radio = 7 ;
        switch($param2) {
            case 'CI': //Cedula
                $digit[0] = (int)($param1/10000000)            ;
                $digit[1] = (int)(($param1 % 10000000)/1000000);
                $digit[2] = (int)(($param1 % 1000000) /100000) ;
                $digit[3] = (int)(($param1 % 100000)  /10000)  ;
                $digit[4] = (int)(($param1 % 10000)   /1000)   ;
                $digit[5] = (int)(($param1 % 1000)    /100)    ;
                $digit[6] = (int)(($param1 % 100)     /10)     ;
                $digit[7] = (int) ($param1 % 10)               ;

                for($i = 0;$i<8;$i++) {
                    $this->page->drawText($digit[$i], $x+7.8, $y+3.9);
                    $this->page->setLineColor(new Zend_Pdf_Color_GrayScale(0));
                    $this->page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
                    $this->page->drawCircle($x+11.25-3.9, ($y - 7.8)-(17.355 * $digit[$i]), $radio);
                    $this->page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                    $x+=20.75;
                }
                break;
            case 'CODA'://Codigo de Asignatura
                $digit[0] = (int)($param1/10000000)             ;
                $digit[1] = (int)(($param1 % 10000000)/1000000);
                $digit[2] = (int)(($param1 % 1000000) /100000) ;
                $digit[3] = (int)(($param1 % 100000)  /10000)  ;
                $digit[4] = (int)(($param1 % 10000)   /1000)   ;
                $digit[5] = (int)(($param1 % 1000)    /100)    ;
                $digit[6] = (int)(($param1 % 100)     /10)     ;
                $digit[7] = (int)(($param1 % 10)      /1)       ;
                $digit[8] = $param4;

                for($i = 0;$i<9;$i++) {
                    $this->page->drawText($digit[$i], $x+7.8, $y+3.9);
                    $this->page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
                    $this->page->drawCircle($x+11.25-3.9, ($y - 7.8)-(17.355 * $digit[$i]), $radio);
                    $this->page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                    $x+=20.75;
                }

                $this->page->drawText($param3, $x+7.8, $y+3.9);
                switch($param3) {
                    case 'A':
                        $digit[9]=0;
                        break;
                    case 'B':
                        $digit[9]=1;
                        break;
                    case 'C':
                        $digit[9]=2;
                        break;
                    case 'D':
                        $digit[9]=3;
                        break;
                    case 'E':
                        $digit[9]=4;
                        break;
                    case 'R':
                        $digit[9]=5;
                        break;
                    case 'U':
                        $digit[9]=6;
                        break;
                    case 'Z':
                        $digit[9]=7;
                        break;
                }
                
                $this->page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
                $this->page->drawCircle($x+11.25-3.9, ($y - 7.8)-(17.355 * $digit[9]), $radio);
                $this->page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                break;
            case 'PER': //Periodo
                $digit[0] = (int)($param1/1000)        ;
                $digit[1] = (int)(($param1 % 1000)/100);
                $digit[2] = (int)(($param1 % 100) /10) ;
                $digit[3] = (int)($param1 % 10)        ;

                for($i = 0;$i<4;$i++) {
                    $this->page->drawText($digit[$i], $x+7.8, $y+3.9);
                    $this->page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
                    $this->page->drawCircle($x+11.25-3.9, ($y - 7.8)-(17.355 * $digit[$i]), $radio);
                    $this->page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                    $x+=20.75;
                }
                break;
        }
    }

    public function drawAlumnoLR($x, $y, $count, $ci, $nombre, $calif, $notatxt, $num) {
        if($notatxt=='00')$notatxt='NC';

        $x0=$x;
        $radio = 7;
        $y-=(17.355*($num-1));

        $digit[0] = (int)($ci/10000000)            ;
                $digit[1] = (int)(($ci % 10000000)/1000000);
                $digit[2] = (int)(($ci % 1000000) /100000) ;
                $digit[3] = (int)(($ci % 100000)  /10000)  ;
                $digit[4] = (int)(($ci % 10000)   /1000)   ;
                $digit[5] = (int)(($ci % 1000)    /100)    ;
                $digit[6] = (int)(($ci % 100)     /10)     ;
                $digit[7] = (int) ($ci % 10)               ;
        if($digit[0]==0){
            $cinew = "$digit[1].$digit[2]$digit[3]$digit[4].$digit[5]$digit[6]$digit[7]";
            $this->page->drawText($cinew, $x+39+7.8+31.2, $y);
        }else{
            $cinew = "$digit[0]$digit[1].$digit[2]$digit[3]$digit[4].$digit[5]$digit[6]$digit[7]";
            $this->page->drawText($cinew, $x+39+31.2, $y);
        }

        $this->page->drawText($count, $x, $y);
        //$this->page->drawText($ci, $x+39, $y);
        //$this->page->drawText($cinew, $x+39, $y);
        $this->page->drawText($this->myTruncate($nombre,38), $x+180+3.9, $y, 'UTF-8');
        $this->page->drawText($notatxt, $x+539+11.7-3.9+11.7+11.7, $y);
        $this->drawAlumnoLRp(($x0+1113.45), $y, $ci, $notatxt, $num);
        if($calif==0)$calif=21;
        $this->page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
        $this->page->drawCircle(($x+642.55 - 11.7 -3.9-3.9)+(($calif-1)*20.71), $y+6.8, $radio);
        $this->page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

    }

    public function drawAlumnoLRp($x, $y, $ci, $calif, $num) { //Alumnos de la pestana
                $digit[0] = (int)($ci/10000000)            ;
                $digit[1] = (int)(($ci % 10000000)/1000000);
                $digit[2] = (int)(($ci % 1000000) /100000) ;
                $digit[3] = (int)(($ci % 100000)  /10000)  ;
                $digit[4] = (int)(($ci % 10000)   /1000)   ;
                $digit[5] = (int)(($ci % 1000)    /100)    ;
                $digit[6] = (int)(($ci % 100)     /10)     ;
                $digit[7] = (int) ($ci % 10)               ;
        if($digit[0]==0){
            $cinew = "$digit[1].$digit[2]$digit[3]$digit[4].$digit[5]$digit[6]$digit[7]";
            $this->page->drawText($cinew, $x+7.8+7.8+31.2, $y);
        }else{
            $cinew = "$digit[0]$digit[1].$digit[2]$digit[3]$digit[4].$digit[5]$digit[6]$digit[7]";
            $this->page->drawText($cinew, $x+7.8+31.2, $y);
        }
        //$this->page->drawText($cinew, $x+7.8, $y);
        //$this->page->drawText($ci, $x+7.8, $y);
        $this->page->drawText($calif, 1284.1+7.8+7.8, $y);
    }

    public function drawEncabezadoLR($sede, $seccion, $escuela, $per, $turno, $nturno, $semestre, $asig, $coda, $profesor, $x=400, $y=1106) {
        $x0=$x;
        $y0=$y;
        $const=19.6;
        $this->page->drawText("SEDE: $sede", $x, $y+$const, 'UTF-8');
        $this->page->drawText($escuela, $x+19.5, $y, 'UTF-8');
        $y-=$const;
        $this->page->drawText($per, $x+19.5, $y);
        $y-=$const-3.9;
        $this->page->drawText($turno, $x+3.9, $y, 'UTF-8');
        $x+=195;
        $this->page->drawText($semestre, $x, $y);
        $x-=195;
        $y-=$const;
        If(strlen($asig)>29){
            $this->page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 10);
        }
        $this->page->drawText($asig, $x +38, $y, 'UTF-8');
        $this->page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 15);
        $y-=$const-3.9;
        $this->page->drawText($coda.$nturno.$seccion, $x +109.2, $y);
        $y-=$const-3.9;
        $nombre = explode(", ",$profesor);
        $this->page->drawText($this->myTruncate($nombre[0],38), $x+46.8, $y, 'UTF-8');
        $this->page->drawText($this->myTruncate($nombre[1],38), $x+46.8, $y-15, 'UTF-8');
        $this->page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
        $this->drawEncabezadoLRp($sede, $escuela, $per, $turno, $semestre, $asig, $profesor, ($x0+932.1), ($y0-19.6) );
        $this->page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 15);
    }

    public function drawEncabezadoLRp($sede ,$escuela, $per, $turno, $semestre, $asig, $profesor, $x=1795, $y=1080) {
        $const=15.6;
        $this->page->drawText("SEDE: $sede", $x, $y+$const, 'UTF-8');
            if($escuela=="ADMINISTRACIÓN DE EMPRESAS DE DISEÑO") $escuela = 'DISEÑO';
            if($escuela=="ADMINISTRACIÓN DE EMPRESAS TURÍSTICAS") $escuela = 'TURISMO';
        $this->page->drawText($escuela, $x+46.8, $y, 'UTF-8');
        $y-=$const;
        $this->page->drawText($per, $x+46.8, $y);
        $y-=$const+3.9;
        $this->page->drawText($turno, $x+35.1, $y, 'UTF-8');
        $y-=$const+7.8;
        If(strlen($asig)>29){
            $this->page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 8);
        }
        $this->page->drawText($asig, $x+3.9, $y-3.9, 'UTF-8');
        $this->page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
        //$this->page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), );
        $x+=173.9;
        $this->page->drawText($semestre, $x, $y+7.8);
        $x-=173.9;
        $y-=27.1;
        $nombre = explode(", ",$profesor);
        $this->page->drawText($this->myTruncate($nombre[0],22), $x+66.3, $y, 'UTF-8');
        $this->page->drawText($this->myTruncate($nombre[1],22), $x+66.3, $y-10, 'UTF-8');

    }

    // Original PHP code by Chirp Internet: www.chirp.com.au
    // Please acknowledge use of this code by including this header. function
    function myTruncate($string, $limit, $break=" ", $pad=".") {
    // return with no change if string is shorter than $limit
        if(strlen($string) <= $limit) return $string;
        $string = substr($string, 0, $limit);
        if(false !== ($breakpoint = strrpos($string, $break))) {
            $string = substr($string, 0, $breakpoint);
        }
        return $string . $pad;
    }

    function myTruncate2($string, $limit, $break=".", $pad="...") { // return with no change if string is shorter than $limit
        if(strlen($string) <= $limit) return $string;
        // is $break present between $limit and the end of the string?
        if(false !== ($breakpoint = strpos($string, $break, $limit))) {
            if($breakpoint < strlen($string) - 1) {
                $string = substr($string, 0, $breakpoint) . $pad;
            }
        } return $string;

        }

}
?>
