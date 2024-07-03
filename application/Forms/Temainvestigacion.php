<?php 
class Forms_Temainvestigacion extends Zend_Form { 
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() { 
        $this->setMethod('post'); 
        $this->setName('temainvestigacion');


        $pk_tema   = new Zend_Form_Element_Hidden('pk_atributo');
        $pk_tema->removeDecorator('label')
           ->removeDecorator('HtmlTag');


        $escuela   = new Zend_Form_Element_Hidden('escuela');
        $escuela->removeDecorator('label')
           ->removeDecorator('HtmlTag');           

        $linea = new Zend_Form_Element_Select('fk_lineainvestigacion');
        $linea->setLabel('Linea de Investigacion:')
            ->setRequired(false)
            ->setRegisterInArrayValidator(false)
            ->addValidator('NotEmpty')
            ->setAttrib('style', 'width: 300px');


        $tema = new Zend_Form_Element_Text('tema');
        $tema->setLabel('Tema:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 50);

           
          $this->addElements(array(
            $pk_tema,
            $escuela,
            $linea,
            $tema
            ));
    }
}