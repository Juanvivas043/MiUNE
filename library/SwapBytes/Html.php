<?php
/**
 * Contiene una serie de metodos que permite construir HTML de forma pre-definida.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Html {
    private $Properties = array('table'    => array('id', 'name', 'style', 'class', 'align', 'width', 'title', 'border', 'cellspacing', 'cellpadding'),
                                'td'       => array('id', 'name', 'style', 'class', 'align', 'valign', 'bgcolor', 'rowspan', 'colspan', 'width', 'height'),
                                'th'       => array('id', 'name', 'style', 'class', 'align', 'width'),
                                'tr'       => array('id', 'name', 'style', 'class', 'align', 'valign', 'bgcolor'),
                                'input'    => array('id', 'name','title','style', 'class', 'type', 'maxlength', 'size', 'value', 'checked', 'src', 'disabled', 'readonly','data-valid','data-mask'),
                                'a'        => array('id', 'name', 'style', 'class', 'href', 'onclick'),
                                'label'    => array('id', 'name', 'style', 'class', 'for'),
                                'div'    => array('id', 'name', 'style', 'class', 'for'),
                                'ul'       => array('id', 'name', 'style', 'class'),
                                'img'      => array('id', 'name', 'style', 'class'),
                                'textarea' => array('id', 'name', 'style', 'class', 'rows', 'cols', 'disabled'),
                                'select'   => array('id', 'name', 'style', 'class', 'disabled', 'multiple', 'size'),
                                );

    /**
     * Convierte un arreglo a una cadena de texto, con las propiedades del HTML
     * a un objeto cualquiera, siempre y cuando se especifique el Tag.
     *
     * $Properties = array('id'   => 'example',
     *                     'name' => 'example',
     *                     'type' => 'text',
     *                     'size' => '10');
     *
     * @param string $Tag
     * @param array  $Properties
     * @return string
     */
    public function convertToProperties($Tag, $Properties) {
        if(isset($Properties) && is_array($Properties)) {
            $HtmlProperties = '';

            foreach($Properties as $Index => $Value) {
                if(isset($Index) && is_array($this->Properties[$Tag]) && in_array($Index, $this->Properties[$Tag])) {
                    if($Index <> 'disabled' && isset($Value)) {
                        $HtmlProperties .= " {$Index}=\"{$Value}\"";
                    } else if($Index == 'disabled'){
                        $HtmlProperties .= " {$Index}";
                    }
                }
            }
            return $HtmlProperties;
        }
    }

    /**
     * Convierte el indice de un arreglo y su valor a una propiedad de un objeto
     * de tipo en HTML.
     *
     * @param array $Properties
     * @param string $PropertyName
     * @return string
     */
    public function convertToProperty($Properties, $PropertyName) {
        if(isset($Properties)) {
            return (isset($Properties[$PropertyName]))? ' ' . $PropertyName . '="' . $Properties[$PropertyName] . '"' : null;
        }
    }

    /**
     * Crea el un Objeto HTML que contiene una lista de elementos.
     *
     * @param array $Elemnts
     * @param array $Properties
     * @return string
     */
    public function getList($Elemnts, $Properties = null) {
        if(is_array($Elemnts)) {
            if(is_array($Properties)) {
                $Properties = $this->convertToProperties('ul', $Properties);
            }

            $HTML  = "<ul{$Properties}>";
            foreach($Elemnts as $Elemnt) {
                $HTML .= "<li>{$Elemnt}</li>";
            }
            return $HTML . '</ul>';
        }
    }

    /**
     * Crea el un Objeto HTML que contiene una imagen en especifico.
     *
     * @param sting $UrlImage
     * @param array $Properties
     * @return string
     */
    public function img($UrlImage, $Properties = null) {
        if(is_array($Properties)) {
            $Properties = $this->convertToProperties('img', $Properties);

            return '<img src="' . $UrlImage . '"' . $Properties . '>';
        }else{
            return '<img src="' . $UrlImage . '">';
        }
    }

    /**
     * Crea un Objeto HTML de tipo INPUT.
     *
     * @param array $Properties
     * @return string
     */
    public function input($Properties) {
        if(is_array($Properties)) {
            $Properties = $this->convertToProperties('input', $Properties);

            return "<input{$Properties}/>";
        }
    }

    /**
     * Crea mediante un arreglo una lista de Opciones del Objeto HTML de tipo SELECT.
     *
     * @param array  $Options
     * @param string $Selected Indicamos cual es el valor de la lista que sera preseleccionado.
     * @return string
     */
    public function selectOptions($Options, $Selected = null) {
        if(is_array($Options)) {
            $HTML = '';
            foreach($Options as $OptionIndex => $OptionValue) {
                if(is_string($OptionValue)) {
                    $HTML .= "<option value=\"{$OptionIndex}\"" . (($OptionIndex == $Selected)? ' SELECTED' : '') . ">{$OptionValue}</option>";
                } else if(is_array($OptionValue)) {
                    $keys  = array_keys($OptionValue);
                    $HTML .= "<option value=\"{$OptionValue[$keys[0]]}\"" . (($OptionValue[$keys[0]] == $Selected)? ' SELECTED' : '') . ">{$OptionValue[$keys[1]]}</option>";
                }
            }
            return $HTML;
        }
    }

    /**
     * Crea una tabla basica a partir de un arreglo de datos.
     *
     * @param array $Properties        Propiedades de la tabla.
     * @param array $Data              Datos que seran incorporados en la tabla.
     * @param array $ColumnsProperties Propiedades para cada columna.
     * @return string
     */
    public function table($Properties, $Data, $ColumnsProperties = null) {
        if(is_array($Properties)) {
            $Properties = $this->convertToProperties('table', $Properties);

            $HTML  = "<table{$Properties}>";
            foreach($Data as $Rows) {
                if(is_array($Rows)) {
                    $HTML .= "<tr>";
                    foreach($Rows as $ColumnInde => $ColumnValue) {
                        $Properties = $this->convertToProperties('td', $ColumnsProperties[$ColumnInde]);

                        $HTML .= "<td{$Properties}>{$ColumnValue}</td>";
                    }
                    $HTML .= "</tr>";
                }
            }
            return $HTML . '</table>';
        }
    }

    /**
     * Crea una lista de Objetos de tipo HTML CheckBox mediante un arreglo.
     *
     * @param string $Name
     * @param array  $Elemnts
     * @return string
     */
    public function getCheckBoxList($Name, $Elemnts, $Properties = array()) {
        if(is_array($Elemnts)) {
            $Properties += array('type'  =>'checkbox');

            $HTML = '';
            foreach($Elemnts as $ElemntIndex => $ElemntValue) {
                $keys  = array_keys($ElemntValue);
                $Properties['id']    = $Name . $ElemntValue[$keys[0]];
                $Properties['name']  = $Name . $ElemntValue[$keys[0]];
                $Properties['value'] = $ElemntValue[$keys[0]];

                $HTML .= $this->input($Properties);
                $HTML .= '&nbsp;' . $ElemntValue[$keys[1]];
                $HTML .= '<br>';
            }

            return $HTML;
        }
    }
}
?>
