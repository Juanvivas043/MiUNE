<?php
class Forms_Ingreso extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {

        $SwapBytes_Jquery = new SwapBytes_Jquery();


        $this->setMethod('post');
        $this->setName('Solicitud');



        $id     = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

         $ced    = new Zend_Form_Element_Hidden('cedulas');
		$ced->removeDecorator('label')
           ->removeDecorator('HtmlTag');




        $this->addElements(array($id,$ced//,
                        //$fechainicio

                        ));
    }
}

?>
