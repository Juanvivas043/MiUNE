<?php 
class Forms_Nuevotutortesis extends Zend_Form { 
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() { 
        $SwapBytes_Jquery = new SwapBytes_Jquery();
        

        /*acciones para gestionar los botones de guardar y cancelar*/
        $span_guardar = "<span class=\"ui-button-text\">Aceptar</span>";
        $span_cancelar = "<span class=\"ui-button-text\">Cancelar</span>";
        $close = "$('#frmModal').dialog('close')";
        $alert = "$( '#cancelar_nuevotutor' ).clone().appendTo( '#guardar_nuevotutor-element' );$('#cancelar_nuevotutor-element').remove();";

        $this->setMethod('get'); 
        $this->setName('nuevotutor');
        $this->setAttrib('onfocus', $alert);
        
        $pk_tutortesis   = new Zend_Form_Element_Hidden('pk_tutortesis');
        $pk_tutortesis->removeDecorator('label')
           ->removeDecorator('HtmlTag');                   
        



        /*botones guardar y cancelar*/
        $guardar = new Zend_Form_Element_Button('guardar_nuevotutor');
        $guardar->setLabel($span_guardar)
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only')
                ->setAttrib('role', 'button')
                ->setAttrib('aria', 'disable')
                ->setAttrib('escape', false);


        $cancelar = new Zend_Form_Element_Button('cancelar_nuevotutor');
        $cancelar->setLabel($span_cancelar)
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only')
                ->setAttrib('role', 'button')
                ->setAttrib('aria', 'disable')
                ->setAttrib('onclick', $close)
                ->setAttrib('escape', false);        

           
          $this->addElements(array(
              $pk_tutortesis,
              $cancelar,
              $guardar
            ));

    }
}

