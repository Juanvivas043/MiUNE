<?php

/**
 * Description of Page
 *
 * @author Daniel Mendez
 */

/** Zend_Pdf_Page */
require_once 'Zend/Pdf/Page.php';

class SwapBytes_Pdf_Page extends Zend_Pdf_Page {

    const SIZE_OFICIO_P                 = '612:1008:';
    const SIZE_OFICIO_L                 = '1008:612:';
    const SIZE_OFICIO2_P                 = '850:1400:';
    const SIZE_OFICIO2_L                 = '1400:850:';
    const margenl                       = -2 ;

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
                    $this->drawText($digit[$i], $x+7.8, $y+3.9);
                    $this->setLineColor(new Zend_Pdf_Color_GrayScale(0.7));
                    $this->setFillColor(new Zend_Pdf_Color_GrayScale(0.7));
                    $this->drawCircle($x+11.25, ($y - 7.8)-(17.355 * $digit[$i]), $radio);
                    $this->setFillColor(new Zend_Pdf_Color_GrayScale(0));
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
                    $this->drawText($digit[$i], $x+7.8, $y+3.9);
                    $this->setFillColor(new Zend_Pdf_Color_GrayScale(0.7));
                    $this->drawCircle($x+11.25, ($y - 7.8)-(17.355 * $digit[$i]), $radio);
                    $this->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                    $x+=20.75;
                }


                $this->drawText($param3, $x+7.8, $y+3.9);
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
                $this->setFillColor(new Zend_Pdf_Color_GrayScale(0.7));
                $this->drawCircle($x+11.25, ($y - 7.8)-(17.355 * $digit[9]), $radio);
                $this->setFillColor(new Zend_Pdf_Color_GrayScale(0));

                break;

            case 'PER': //Periodo
                $digit[0] = (int)($param1/1000)        ;
                $digit[1] = (int)(($param1 % 1000)/100);
                $digit[2] = (int)(($param1 % 100) /10) ;
                $digit[3] = (int)($param1 % 10)        ;

                for($i = 0;$i<4;$i++) {
                    $this->drawText($digit[$i], $x+7.8, $y+3.9);
                    $this->setFillColor(new Zend_Pdf_Color_GrayScale(0.7));
                    $this->drawCircle($x+11.25, ($y - 7.8)-(17.355 * $digit[$i]), $radio);
                    $this->setFillColor(new Zend_Pdf_Color_GrayScale(0));
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

        $this->drawText($count, $x, $y);
        $this->drawText($ci, $x+39, $y);
        $this->drawText($this->myTruncate($nombre,40), $x+180+3.9, $y, 'UTF-8');
        $this->drawText($notatxt, $x+539+11.7+11.7+11.7, $y);
        $this->drawAlumnoLRp(($x0+1113.45), $y, $ci, $notatxt, $num);
        if($calif==0)$calif=21;
        $this->setFillColor(new Zend_Pdf_Color_GrayScale(0.7));
        $this->drawCircle(($x+642.55 - 11.7 -3.9)+(($calif-1)*20.71), $y+6.8, $radio);
        $this->setFillColor(new Zend_Pdf_Color_GrayScale(0));

    }

    public function drawAlumnoLRp($x, $y, $ci, $calif, $num) { //Alumnos de la pestana
        $this->drawText($ci, $x+7.8, $y);
        $this->drawText($calif, 1284.1+7.8, $y);

    }
    public function drawEncabezadoLR($seccion, $escuela, $per, $turno, $semestre, $asig, $coda, $profesor, $x=400, $y=1106) {
        $x0=$x;
        $y0=$y;
        $const=19.6;
        $this->drawText($escuela, $x+19.5, $y, 'UTF-8');
        $y-=$const;
        $this->drawText($per, $x+19.5, $y);
        $y-=$const-3.9;
        $this->drawText($turno, $x+3.9, $y, 'UTF-8');
        $x+=195;
        $this->drawText($semestre, $x, $y);
        $x-=195;
        $y-=$const;
        $this->drawText($this->myTruncate($asig,40), $x +39, $y, 'UTF-8');
        $y-=$const-3.9;
        $this->drawText($coda.$semestre.$seccion, $x +109.2, $y);
        $y-=$const-3.9;
        $this->drawText($this->myTruncate($profesor,40), $x+46.8, $y, 'UTF-8');
        $this->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 12);
        $this->drawEncabezadoLRp($escuela, $per, $turno, $semestre, $asig, $profesor, ($x0+932.1), ($y0-19.6) );
        $this->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES), 15);
    }


    public function drawEncabezadoLRp($escuela, $per, $turno, $semestre, $asig, $profesor, $x=1795, $y=1080) {
        $const=15.6;
        $this->drawText($escuela, $x+46.8, $y, 'UTF-8');
        $y-=$const;
        $this->drawText($per, $x+46.8, $y);
        $y-=$const+3.9;
        $this->drawText($turno, $x+35.1, $y, 'UTF-8');
        $y-=$const+7.8;
        $this->drawText($this->myTruncate($asig,40), $x+3.9, $y-3.9, 'UTF-8');
        $x+=173.9;
        $this->drawText($semestre, $x, $y+7.8);
        $x-=173.9;
        $y-=27.1;
        $this->drawText($this->myTruncate($profesor,40), $x+66.3, $y, 'UTF-8');

    }


    //--Fin-- Listas Rojas

    public function drawEncabezadoCN($facultad, $escuela, $x, $y, $nombre='', $apellido='', $ci='') {
        $this->drawText('UNIVERSIDAD NUEVA ESPARTA', $x, $y);
        $y-=12;
        $this->drawText('    CARACAS - VENEZUELA', $x, $y);
        $y-=12;
        $this->drawText("FACULTAD: $facultad", $x, $y);
        $y-=12;
        $this->drawText("ESCUELA: $escuela", $x, $y);
        If($nombre!='' && $apellido!='' && $ci!='') {
            $this->drawText("BR: $apellido , $nombre", $x, $y-15);
            $this->drawText("C.I.: $ci", $x, $y-27);
        }
        $y-=55;
        $this->drawText("CERTIFICACIÓN", $x+92, $y, 'UTF-8');
    }

    public function drawLogo($x, $y) {
        $path =dirname(__FILE__) .'/logoUNE.jpg';
        $image=Zend_Pdf_Image::imageWithPath($path);
        $this->drawImage($image, $x, $y, $x+130, $y+69);
    }

    public function drawFooterCN($x=610, $y=1080 ,$nombre="DR. JESÚS ALBERTO RAMÍREZ", $cargo="RECTOR") {
        $this->setLineWidth(0.5);
        $this->drawLine($x, $y+10, $x+(strlen($nombre)*8), $y+10);
        $this->drawText($nombre, $x, $y);
        $y-=10;
        $this->drawText($cargo, $x+((strlen($nombre)*8)/2.5), $y);
    }
    public function drawFooter2CN($x=610, $y=1080,$nombre="LIC. HAYDEÉ IRAUSQUÍN ALDAMA", $cargo="SECRETARIA") {
        $this->setLineWidth(0.5);
        $this->drawLine($x, $y+10, $x+(strlen($nombre)*7), $y+10);
        $this->drawText($nombre, $x, $y);
        $y-=10;
        $this->drawText($cargo, $x+20, $y);
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

    public function drawContenido($x, $y, $n) {
        $texto1 = 'LA SUSCRITA, “LIC. HAYDEÉ IRAUSQUÍN ALDAMA” SECRETARIA (E) DE LA UNIVERSIDAD NUEVA ESPARTA, CERTIFICA QUE: LAS CALIFICACIONES QUE ANEXAN CORREPONDIENTES A LA FACULTAD DE: “CIENCIAS ADMINISTRATIVAS” ESCUELA DE: ADMINISTRACION DE EMPRESAS DE DISEÑO SON AUTENTICAS, ESTAN VIGENTES PARA LA FECHA DE SU EXPEDICION, VAN SELLADAS Y FIRMADAS Y PERTENECEN AL (LA) BACHILLER: “ROTUNDO CENTENO, KEILY DEL CARMEN” CEDULA DE IDENTIDAD NUMERO: “17533257”. CERTIFICADO QUE SE EXPIDE A SOLICITUD DE LA PARTE INTERESADA EN CARACAS, A LOS “VEINTITRES” DIAS DEL MES DE “SEPTIEMBRE” DE “DOS MIL NUEVE”';
        $texto2 = 'EN MI CARÁCTER DE RECTOR DE LA UNIVERSIDAD NUEVA ESPARTA CERTIFICO QUE LA FIRMA ANTERIOR ES DE PUÑO Y LETRA DE LA SECRETARIA (E) DE ESTA UNIVERSIDAD. DADO EN CARACAS A LOS “VEINTITRES”  DIAS DEL MES “SEPTIEMBRE” DE “DOS MIL NUEVE”';
        switch($n) {
            case 1:
                $this->drawBloqueTexto($texto1, $x, $y);
                break;
            case 2:
                $this->drawBloqueTexto($texto2, $x, $y);
                break;
        }
    }

    public function drawBloqueTexto($text, $x, $y) {
        $newtexto = wordwrap($text, 80, "|", true);
        $texto= explode('|',$newtexto);

        foreach($texto as $text0) {
            $this->drawText($text0, $x, $y, 'UTF-8');
            $y-=12;
        }
    }

    public function _wrapText($text, $width, $initial_line_offset = 0) {
        $lines = array();
        $line_init = array(
            'words'        => array(),
            'word_lengths' => array(),
            'total_length' => 0
        );
        $line = $line_init;
        $line['total_length'] = $initial_line_offset;

        $text = preg_split('%[\n\r ]+%', $text, -1, PREG_SPLIT_NO_EMPTY);
        $space_length = $this->widthForString(' ');
        foreach ($text as $word) {
            $word_length = $this->widthForString($word);
            if ($word_length > $width) {
                if ($line['words']) {
                    $lines[] = $line;
                }
                $lines[] = array(
                    'words'        => array($word),
                    'word_lengths' => array($word_length),
                    'total_length' => array($word_length)
                );
                $line = $line_init;
                continue;
            }
            if ($line['total_length'] + $word_length > $width) {
                $line['total_length'] -= $space_length;
                $lines[] = $line;
                $line = $line_init;
            }
            $line['words'][]        = $word;
            $line['word_lengths'][] = $word_length;
            $line['total_length']  += $word_length + $space_length;
        }
        if ($line) {
            $line['total_length'] -= $space_length;
            $lines[] = $line;
        }

        return $lines;
    }


    public function prueba ($x, $y) {
        $text = 'EN MI CARÁCTER DE RECTOR DE LA UNIVERSIDAD NUEVA ESPARTA CERTIFICO QUE LA FIRMA ANTERIOR ES DE PUÑO Y LETRA DE LA SECRETARIA (E) DE ESTA UNIVERSIDAD. DADO EN CARACAS A LOS “VEINTITRES”  DIAS DEL MES “SEPTIEMBRE” DE “DOS MIL NUEVE”';
        $lines = $this->_wrapText($text, 40);


        if (count($line['words']) < 2 || $k == count($lines) - 1) {
            $this->drawText(implode(' ', $line['words']), $x, $y);
            break;
        }
        $space_width = (40 - array_sum($line['word_lengths'])) / (count($line['words']) - 1);
        $pos = $x;
        foreach ($line['words'] as $k => $word) {
            $this->drawText($word, $pos, $y);
            $pos += $line['word_lengths'][$k] + $space_width;
        }

    }
}
?>
