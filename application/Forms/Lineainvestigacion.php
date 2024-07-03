<?php 
class Forms_Lineainvestigacion extends Zend_Form { 
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() { 
        $this->setMethod('post'); 
        $this->setName('lineainvestigacion');


        $pk_linea   = new Zend_Form_Element_Hidden('pk_atributo');
        $pk_linea->removeDecorator('label')
           ->removeDecorator('HtmlTag');


        $escuela   = new Zend_Form_Element_Hidden('escuela');
        $escuela->removeDecorator('label')
           ->removeDecorator('HtmlTag');           



        $linea = new Zend_Form_Element_Text('lineainvestigacion');
        $linea->setLabel('Linea:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 50);

           
          $this->addElements(array(
            $pk_linea,
            $tipo,
            $escuela,
            $linea
            ));
    }
}