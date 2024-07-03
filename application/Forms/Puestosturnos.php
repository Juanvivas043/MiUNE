<?php
class Forms_Puestosturnos extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {

        $SwapBytes_Jquery = new SwapBytes_Jquery();


        $this->setMethod('post');
        $this->setName('Puestos');
      
        $id     = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $puestos = new Zend_Form_Element_Text('cantidad');
        $puestos->setLabel('Cantidad de Puestos:')
               ->setRequired(true)
               ->addValidator('Digits')
               ->addValidator('StringLength', true, array(1, 4))
               ->addValidator('Between', true, array(1, 200))
               ->setAttrib('size', 4)
               ->setAttrib('maxlength', 4);


        $tipo = new Zend_Form_Element_Select('tipo');
        $tipo->setLabel('Tipo:')
                   ->setAttrib('style', 'width: 150px');

        $turno = new Zend_Form_Element_Select('turno');
        $turno->setLabel('Turno:')
                   ->setAttrib('style', 'width: 150px');




        $this->addElements(array($id,
                        $tipo,
                        $turno,
                        $puestos

                        ));
    }
}

?>
