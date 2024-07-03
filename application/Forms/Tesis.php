<?php 
class Forms_Tesis extends Zend_Form {  
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() { 
        $SwapBytes_Jquery = new SwapBytes_Jquery(); 
        
        
        $changeTema = "$.getJSON(MyurlAjax + '/temas/fk_lineainvestigacion/' + $('#fk_lineainvestigacion').val(),function(data){executeCmdsFromJSON(data)});";

        /*acciones para gestionar los botones de guardar y cancelar*/
        $span_guardar = "<span class=\"ui-button-text\">Guardar</span>";
        $span_cancelar = "<span class=\"ui-button-text\">Cancelar</span>";
        $close = "$('#frmModal').dialog('close')";
        // $alert = "$( '#cancelar_tesis' ).clone().appendTo( '#guardar_tesis-element' );$('#cancelar_tesis-element').remove();";

        $this->setMethod('get'); 
        $this->setName('tesis');
        // $this->setAttrib('onfocus', $alert);
        
        $pk_datotesis   = new Zend_Form_Element_Hidden('pk_datotesis');
        $pk_datotesis->removeDecorator('label')
           ->removeDecorator('HtmlTag');        
        
        $lineainvestigacion = new Zend_Form_Element_Select('fk_lineainvestigacion');
        $lineainvestigacion->setLabel('Linea de Investigacion:')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->setAttrib('style', 'width: 300px')
            ->setAttrib('onchange', $changeTema);
        
        $tema = new Zend_Form_Element_Select('fk_tema');
        $tema->setLabel('Tema:')
            ->setRequired(true)
            ->addValidator('NotEmpty')
            ->setAttrib('style', 'width: 300px');
        
        $titulo = new Zend_Form_Element_Text('titulo');
        $titulo->setLabel('Titulo:')
            ->setAttrib('size', 36)
            ->setAttrib('maxlength', 255);


        /*botones guardar y cancelar*/
        $guardar = new Zend_Form_Element_Button('guardar_tesis');
        $guardar->setLabel($span_guardar)
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only')
                ->setAttrib('role', 'button')
                ->setAttrib('aria', 'disable')
                ->setAttrib('escape', false);


        $cancelar = new Zend_Form_Element_Button('cancelar_tesis');
        $cancelar->setLabel($span_cancelar)
                ->setAttrib('class', 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only')
                ->setAttrib('role', 'button')
                ->setAttrib('aria', 'disable')
                ->setAttrib('onclick', $close)
                ->setAttrib('escape', false);        

           
          $this->addElements(array(
              $pk_datotesis,
              $lineainvestigacion,
              $tema,
              $titulo,
              $cancelar,
              $guardar
            ));

    }
}

