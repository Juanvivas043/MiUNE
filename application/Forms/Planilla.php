<?php 
class Forms_Planilla extends Zend_Form { 
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
        $alert = "$( '#cancelar_planilla' ).clone().appendTo( '#guardar_planilla-element' );$('#cancelar_planilla-element').remove();";

        
        $this->setMethod('get'); 
        $this->setName('planilla');
        $this->setAttrib('onfocus', $alert);
        
        $pk_datotesis   = new Zend_Form_Element_Hidden('pk_datotesis');
        $pk_datotesis->removeDecorator('label')
           ->removeDecorator('HtmlTag');        
        
        $fase = new Zend_Form_Element_Select('fase');
        $fase->setLabel('Fase:')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->setAttrib('style', 'width: 300px');

        /*botones guardar y cancelar*/
        $guardar = new Zend_Form_Element_Button('guardar_planilla');
        $guardar->setLabel($span_guardar)
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only')
                ->setAttrib('role', 'button')
                ->setAttrib('aria', 'disable')
                ->setAttrib('escape', false);


        $cancelar = new Zend_Form_Element_Button('cancelar_planilla');
        $cancelar->setLabel($span_cancelar)
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only')
                ->setAttrib('role', 'button')
                ->setAttrib('aria', 'disable')
                ->setAttrib('onclick', $close)
                ->setAttrib('escape', false);             

           
          $this->addElements(array(
              $pk_datotesis,
              $fase,
              $cancelar,
              $guardar
            ));
    }
}

