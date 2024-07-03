<?php 
class Forms_HorarioTesis extends Zend_Form { 
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() { 
        $this->setMethod('post'); 
        $this->setName('horariotesis');

        $changeEdificio = "$.getJSON(MyurlAjax + \'/aula/edificio/\' + $(\'#edificio\').val(), function(data){executeCmdsFromJSON(data)})";
        

        $evaluadores = new Zend_Form_Element_Hidden('evaluadores');
        $evaluadores->removeDecorator('label')
                   ->removeDecorator('HtmlTag'); 

        $pk_horario = new Zend_Form_Element_Hidden('pk_horario');
        $pk_horario->removeDecorator('label')
                   ->removeDecorator('HtmlTag');                        

        $fecha = new Zend_Form_Element_Text('fecha');
        $fecha->setLabel('fecha:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('align','left')
            ->addValidator('Date', true, array('dd/MM/YYYY'))
            ->setAttrib('escape', false);

        $estructura = new Zend_Form_Element_Hidden('estructura');
        $estructura->removeDecorator('label')
                   ->removeDecorator('HtmlTag');
                    
          $this->addElements(array(
            $evaluadores,
            $pk_horario,
            $fecha,
            $estructura

            ));
    }
}