<?php
/**
 * Permite crear un Menu dinamico con los datos obtenidos en una base de datos,
 * los datos deben estar alojados en una tabla recursiva.
 *
 * @author Lic. Nicola Strappazzon C.
 */
class SwapBytes_Menu {
    private $menuIndex  = 'pk_acceso';
    private $menuName   = 'nombre';
    private $menuPath   = 'include';
    private $menuParent = 'fk_acceso';

    public function __construct() {
        $this->Request = new Zend_Controller_Request_Http();
    }

    /**
     * Imprime el menu construido en HTML con los valores suministrados por la
     * Base de Datos.
     *
     * @param array $menu
     */
    public function render($menu) {
        echo "<div id=menucontainer>";
        echo "<ul id='navmenu-h'>";
        echo $this->_build($menu);
        echo "</ul>";
        echo "</div>";
        echo "<div id='last'></div>";

    }

    /**
     * Metodo recursivo que construye el menu en HTML con los valores
     * suministrados por la Base de Datos, adicionalmente permite construit desde
     * un punto en especifico ($Key) que es suministrado por el metodo padre
     * cuando es recursivo.
     *
     * @param array $menu
     * @param int   $key
     * @return string
     */
    private function _build($menu, $key = null) {
        $html = '';
        if(isset($menu) && is_array($menu)) {
            foreach($menu as $row) {
                if($row[$this->menuParent] == $key) {
                    $path  = (isset($row[$this->menuPath]))? $this->Request->getBaseUrl() . '/' . $row[$this->menuPath] : '#';
                    $subm  = $this->_build($menu, $row[$this->menuIndex]);
                    $plus  = (!empty($subm))? " +" : "";
                    $html .= "<li>";   
                    if (strpos ($path, 'index') != false )  {
                        $html .= "<a>{$row[$this->menuName]}{$plus}</a>";
                    }else{
                        $html .= "<a href='{$path}'>{$row[$this->menuName]}{$plus}</a>";
                    }
                    //$html .= "<a href='{$path}'>{$row[$this->menuName]}{$plus}</a>";
                    $html .= (!empty($subm))? "<ul>{$subm}</ul>" : "</li>";
                }
            }
        }
        
        return $html;
    }
}
?>
