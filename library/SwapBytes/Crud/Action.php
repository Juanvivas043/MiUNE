<?php

class SwapBytes_Crud_Action {

    public function __construct() {
        $this->SwapBytes_Jquery = new SwapBytes_Jquery();
        $this->SwapBytes_Jquery_Ui_Form = new SwapBytes_Jquery_Ui_Form();
        $this->properties['display'] = true;
        $this->properties['list']['name'] = 'Listar';
        $this->properties['list']['id'] = 'btnList';
        $this->properties['clear']['name'] = 'Limpiar';
        $this->properties['clear']['id'] = 'btnClear';
        $this->properties['add']['name'] = 'Agregar';
        $this->properties['add']['id'] = 'btnAdd';
        $this->properties['delete']['name'] = 'Eliminar';
        $this->properties['delete']['id'] = 'btnDelete';
        $this->properties['copy']['name'] = 'Copiar';
        $this->properties['copy']['id'] = 'btnCopy';
        $this->properties['paste']['name'] = 'Pegar';
        $this->properties['paste']['id'] = 'btnPaste';
        
        $this->javascript = null;
    }
    public function setAllDisplay($Enable){
        if(!is_bool($Enable)){
         return;
     }else{
        $this->properties['display'] = $Enable;
        }
    }

    /**
     * Muestra en especifico los botones de las acciones.
     *
     * @param boolean $List
     * @param boolean $Clear
     * @param boolean $Add
     * @param boolean $Delete
     * @param boolean $Copy
     * @param boolean $Paste
     */
    public function setDisplay($List = true, $Clear = true, $Add = false, $Delete = false, $Copy = false, $Paste = false) {
        if (!is_bool($List))
            return;
        if (!is_bool($Clear))
            return;
        if (!is_bool($Add))
            return;
        if (!is_bool($Delete))
            return;
        if (!is_bool($Copy))
            return;
        if (!is_bool($Paste))
            return;

        $this->properties['list']['display'] = $List;
        $this->properties['clear']['display'] = $Clear;
        $this->properties['add']['display'] = $Add;
        $this->properties['delete']['display'] = $Delete;
        $this->properties['copy']['display'] = $Copy;
        $this->properties['paste']['display'] = $Paste;
    }

    public function getDisplay() { // Habilita el html de acciones (true), de ser false deshabilita(false)
       return $this->properties['display'];
    }

    /**
     * Habilita en especifico los botones de las acciones. Para poder apreciarlo
     * se debe primero mostrar los botones.
     *
     * @param boolean $List
     * @param boolean $Clear
     * @param boolean $Add
     * @param boolean $Delete
     * @param boolean $Copy
     * @param boolean $Paste
     */
    public function setEnable($List = true, $Clear = true, $Add = false, $Delete = false, $Copy = false, $Paste = false) {
        if (!is_bool($List))
            return;
        if (!is_bool($Clear))
            return;
        if (!is_bool($Add))
            return;
        if (!is_bool($Delete))
            return;
        if (!is_bool($Copy))
            return;
        if (!is_bool($Paste))
            return;

        $this->properties['list']['enable'] = $List;
        $this->properties['clear']['enable'] = $Clear;
        $this->properties['add']['enable'] = $Add;
        $this->properties['delete']['enable'] = $Delete;
        $this->properties['copy']['enable'] = $Copy;
        $this->properties['paste']['enable'] = $Paste;
    }

    /**
     * Agrega una acción personalizada, solo se debe pasar el codigo HTML que se
     * quiere mostrar en la barra.
     *
     * @param string $Html
     */
    public function addCustum($Html) {
        $this->properties['custom'][] = array('html' => $Html);
    }
    
    public function addJavaScript($js){
        $this->javascript = $js;
    }

    public function getJavaScript($RenderDiv) {
        $js = '';

        /*
         * Mostramos los botones:
         */
        if ($this->properties['list']['display']) {
            $function = $this->SwapBytes_Jquery->getJSON('list', null, array('buscar' => $this->SwapBytes_Jquery->getValEncodedUri('txtBuscar'),
                        'filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros')));
            $js .= $this->SwapBytes_Jquery->setClick($this->properties['list']['id'], $function);
        }

        if ($this->properties['add']['display']) {
            $function = $this->SwapBytes_Jquery->getJSON('addoreditload', null, array('filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros')));
            $js .= $this->SwapBytes_Jquery->setClick($this->properties['add']['id'], $function);
        }

        if ($this->properties['delete']['display']) {
            $function = $this->SwapBytes_Jquery->getJSON('deleteload', null, array('data' => $this->SwapBytes_Jquery->serializeForm($RenderDiv)));
            $js .= $this->SwapBytes_Jquery->setClick($this->properties['delete']['id'], $function);
        }

        if ($this->properties['copy']['display']) {
            $function = $this->SwapBytes_Jquery->getJSON('copy', null, array('data' => $this->SwapBytes_Jquery->serializeForm($RenderDiv)));
            $js .= $this->SwapBytes_Jquery->setClick($this->properties['copy']['id'], $function);
        }

        if ($this->properties['paste']['display']) {
            $function = $this->SwapBytes_Jquery->getJSON('paste', null, array('filters' => $this->SwapBytes_Jquery->serializeForm('tblFiltros')));
            $js .= $this->SwapBytes_Jquery->setClick($this->properties['paste']['id'], $function);
        }

        /*
         * Habilitamos los botones necesarios definidos:
         */
        if (!$this->properties['list']['enable']) {
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonDisable($this->properties['list']['id'], true);
        }
        if (!$this->properties['clear']['enable']) {
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonDisable($this->properties['clear']['id'], true);
        }
        if (!$this->properties['add']['enable']) {
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonDisable($this->properties['add']['id'], true);
        }
        if (!$this->properties['delete']['enable']) {
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonDisable($this->properties['delete']['id'], true);
        }
        if (!$this->properties['copy']['enable']) {
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonDisable($this->properties['copy']['id'], true);
        }
        if (!$this->properties['paste']['enable']) {
            $js .= $this->SwapBytes_Jquery_Ui_Form->buttonDisable($this->properties['paste']['id'], true);
        }

        /*
         * Asigna el evento al boton de limpiar solo cuando el campo buscar existe.
         */
        if ($this->properties['clear']['display']) {
            $function = $this->SwapBytes_Jquery->setVal('txtBuscar');
            $function .= $this->SwapBytes_Jquery->setHtml($RenderDiv, '');
            $function .= $this->SwapBytes_Jquery->setHide('lblMessage');

            $js .= $this->SwapBytes_Jquery->setClick($this->properties['clear']['id'], $function);
        }

        $js .= $this->SwapBytes_Jquery_Ui_Form->buttonDecorator('actionsForm');
        $js .= $this->javascript;
        return $js;
    }

    /**
     * Obtiene el codigo en HTML de todos los botones que fueron definidos para
     * ser mostrados al GUI.
     *
     * @return string
     */
    public function getHtml() {
        $class = "ui-button ui-state-default ui-corner-all";
        $html = '';

        if ($this->properties['list']['display'])
            $html .= "<button id='{$this->properties['list']['id']}' name='{$this->properties['list']['id']}' class='{$class}'>{$this->properties['list']['name']}</button>";

        if ($this->properties['clear']['display'])
            $html .= "<button id='{$this->properties['clear']['id']}' name='{$this->properties['clear']['id']}' class='{$class}'>{$this->properties['clear']['name']}</button>";
        if ($this->properties['add']['display'])
            $html .= "<button id='{$this->properties['add']['id']}' name='{$this->properties['add']['id']}' class='{$class}'>{$this->properties['add']['name']}</button>";
        if ($this->properties['copy']['display'])
            $html .= "<button id='{$this->properties['copy']['id']}' name='{$this->properties['copy']['id']}' class='{$class}'>{$this->properties['copy']['name']}</button>";
        if ($this->properties['paste']['display'])
            $html .= "<button id='{$this->properties['paste']['id']}' name='{$this->properties['paste']['id']}' class='{$class}'>{$this->properties['paste']['name']}</button>";
        if ($this->properties['delete']['display'])
            $html .= "<button id='{$this->properties['delete']['id']}' name='{$this->properties['delete']['id']}' class='{$class}'>{$this->properties['delete']['name']}</button>";

        if (isset($this->properties['custom']) && is_array($this->properties['custom'])) {
            foreach ($this->properties['custom'] as $custom) {
                $html .= $custom['html'];
            }
        }

        return $html;
    }

}

?>
