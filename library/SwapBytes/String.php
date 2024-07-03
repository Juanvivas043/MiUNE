<?php

/**
 *    
 * @author Lic. Nicola Strappazzon C.    
 */
class SwapBytes_String {
    function hexToStr($hex) {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i+=2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }
    
    //Funcion para abreviar textos 
    function strBrief($sTexto, $iMaxLength){
		$iAbreviar  = 4;
		$sResultado = "";
		
		if(strlen($sTexto) >= $iMaxLength)
		{
			$aPalabras = explode(" ", trim($sTexto));
		
			// Buscamos abreviaturas que se encuentren solo entre () para sermostradas.
			if(stripos($sTexto, "("))
			{
				$sTemp = substr($sTexto, stripos($sTexto, "("), strlen($sTexto));
				
				$sResultado = substr($sTemp, 0, stripos($sTemp, ")") + 1);
			}
			elseif(count($aPalabras) == 2)
			{
				if(strlen($aPalabras[1]) > 3)
				{
					$aPalabras[0] = substr($aPalabras[0], 0, $iAbreviar) . ".";
					$aPalabras[1] = substr($aPalabras[1], 0, $iAbreviar) . ".";
				}
				
				$sResultado = implode($aPalabras, " ");
			}
			elseif(count($aPalabras) > 2)
			{
				for($i = 0; $i < count($aPalabras); $i++)
				{
					if(strlen($aPalabras[$i]) >= 8)
					{
						$aPalabras[$i] = substr($aPalabras[$i], 0, $iAbreviar) . ".";
					}
				}
				
				$sResultado = implode($aPalabras, " ");
			}
			else
			{
				$sResultado = implode($aPalabras, " ");
			}
			$sTexto = $sResultado;
		}
		
		return $sTexto;
	}

    function arrayDbToArrayPHP($string){

	 	$turno=explode(',', trim(($string),"{}"));
          
		foreach ($turno as  $key => $value) {
			$seted = false;
			if($key%2 == 0 && !isset($key_temp) && !$seted){
			  $key_temp = str_replace('"', '', $value);
			  $seted = true;
			}
			if(!$key%2 == 0 && !isset($value_temp) && !$seted){
			  $value_temp = str_replace('"', '', $value);
			  $seted = true;
			}
			if(isset($value_temp) && isset($key_temp)){
			  $turnos[(int)$key_temp] = $value_temp;
			  unset($key_temp);
			  unset($value_temp);
			}
		}
		
		return $turnos;

    }
 

}
?>
