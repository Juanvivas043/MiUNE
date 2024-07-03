<?php 
class Forms_Observaciones extends Zend_Form { 
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() { 
        $this->setMethod('post'); 
        $this->setName('observaciones');

        $pk_pasotesis   = new Zend_Form_Element_Hidden('pk_pasotesis');
        $pk_pasotesis->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $pk_datotesis   = new Zend_Form_Element_Hidden('pk_datotesis');
        $pk_datotesis->removeDecorator('label')
           ->removeDecorator('HtmlTag');


        $observaciones = new Zend_Form_Element_Textarea('observaciones');
        $observaciones->setLabel('Observaciones:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('rows', 4)
            ->setAttrib('cols', 39);
        
        /*$imprimir = new Zend_Form_Element_Button('imprimir');*/

           
          $this->addElements(array($pk_pasotesis,
            $pk_datotesis,
            $observaciones
            ));
    }
}