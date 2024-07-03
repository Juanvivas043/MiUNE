<?php
/**
 * Contiene una serie de metodos que permite construir HTML de forma pre-definida.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Html_Img {
    public function newWindows($UrlImage, $UrlNewWindows, $Name = '') {
        return "<img src='$UrlImage' onclick=\"window.open('{$UrlNewWindows}','$Name','scrollbars=no,menubar=no,height=400,width=300,resizable=yes,toolbar=no,location=no,status=no');return false;\">";
    }
}
?>
