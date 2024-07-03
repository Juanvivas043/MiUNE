<?php

/**
 * Clase que contiene una serie de metodos que permiten la integracion con el
 * framework de javascript llamado jQuery.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Jquery_Mask
{
    public function date($id)
    {
        return "$('#{$id}').mask(\"99/99/9999\")";
    }

    public function dateDash($id)
    {
        return "$('#{$id}').mask(\"9999-99-99\")";
    }

    public function datePicker($id,$date)
    {
        $date = str_replace("-",",",$date);
        return "$(\"#{$id}\").datepicker({changeYear: true, changeMonth: true, dateFormat: 'yy-mm-dd'}).datepicker(new Date({$date}));";
    }

    public function phone($id)
    {
        return "$('#{$id}').mask(\"(9999)999.99.99\")";
    }

    public function unmaskShortPhone($phone)
    {
        return str_replace('.', '', $phone);

    }

    public function shortPhone($id)
    {
        return "$('#{$id}').mask(\"999.99.99\")";
    }

    public function withoutZeroPhone($id)
    {
        return "$('#{$id}').mask(\"(999)999.99.99\")";
    }

    public function unmaskZeroPhone($phone)
    {
        return str_replace('.', '', str_replace('(', '', str_replace(')', '', $phone)));
    }

    public function rif($id)
    {
        return "$('#{$id}').mask(\"999999999\")";
    }

    public function age($id)
    {
        return "$('#{$id}').mask(\"99\")";
    }

    public function singelNumber($id)
    {
        return "$('#{$id}').mask(\"99\",{clearIfNotMatch: false})";
    }
}

?>
