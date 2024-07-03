<?php 

class Forms_Renuncia extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        $SwapBytes_Jquery = new SwapBytes_Jquery();

        /*acciones para gestionar los botones de guardar y cancelar*/
        $span_guardar = "<span class=\"ui-button-text\">Descargar</span>";
        $span_cancelar = "<span class=\"ui-button-text\">Cancelar</span>";
        $close = "$('#frmModal').dialog('close')";
        $alert = "$( '#cancelar_renuncia' ).clone().appendTo( '#guardar_renuncia-element' );$('#cancelar_renuncia-element').remove();";

        $this->setMethod('get'); 
        $this->setName('renuncia');
        $this->setAttrib('onfocus', $alert);

        $id   = new Zend_Form_Element_Hidden('id');
        $id->removeDecorator('label')
                   ->removeDecorator('HtmlTag');


        $tipo_renuncia   = new Zend_Form_Element_Hidden('tipo_renuncia');
        $tipo_renuncia->removeDecorator('label')
                   ->removeDecorator('HtmlTag');                   

        $cedula   = new Zend_Form_Element_Hidden('cedula');
        $cedula->removeDecorator('label')
                   ->removeDecorator('HtmlTag');                   


        /*botones guardar y cancelar*/
        $guardar = new Zend_Form_Element_Button('guardar_renuncia');
        $guardar->setLabel($span_guardar)
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only')
                ->setAttrib('role', 'button')
                ->setAttrib('aria', 'disable')
                ->setAttrib('escape', false);


        $cancelar = new Zend_Form_Element_Button('cancelar_renuncia');
        $cancelar->setLabel($span_cancelar)
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only')
                ->setAttrib('role', 'button')
                ->setAttrib('aria', 'disable')
                ->setAttrib('onclick', $close)
                ->setAttrib('escape', false);                    

       $this->addElements(array($id,
                $cedula,
                $tipo_renuncia,
                $cancelar,
                $guardar
               ));



    }

}