<?php 
class Forms_Tesiscalificaciones extends Zend_Form { 
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() { 
        $this->setMethod('post'); 
        $this->setName('tesiscalificaciones');

        $calificaciones   = new Zend_Form_Element_Hidden('calificaciones');
        $calificaciones->removeDecorator('label')
           ->removeDecorator('HtmlTag');

          $this->addElements(array($calificaciones
            ));
    }
}