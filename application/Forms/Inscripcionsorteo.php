<?php
class Forms_Inscripcionsorteo extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {

        $SwapBytes_Jquery = new SwapBytes_Jquery();


        $this->setMethod('post');
        $this->setName('inssorteo');


        $changeTipo = $SwapBytes_Jquery->fillSelect('vehiculo', 'modelos', array('tipo' => 'tipo'));


       $id     = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');
       $ispago     = new Zend_Form_Element_Hidden('ispago');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');


       $tipo = new Zend_Form_Element_Select('tipo');
       $tipo->setLabel('Tipo de Sorteo:')
                   ->setAttrib('style', 'width: 150px')
                   ->setAttrib('onchange', $changeTipo);

       $vehiculo = new Zend_Form_Element_Select('vehiculo');
       $vehiculo->setLabel('Vehiculo:')
                   ->setAttrib('style', 'width: 250px');

       $datos = new Zend_Form_Element_Hidden('hidden-datos');
       $datos->setDecorators(array(
                   'ViewHelper',
                   array(array('data'=>'HtmlTag'), array('tag'=>'dd', 'id'=>'datos'))
                ));
       $datos1 = new Zend_Form_Element_Hidden('hidden-datos1');
       $datos1->setDecorators(array(
                   'ViewHelper',
                   array(array('data'=>'HtmlTag'), array('tag'=>'dd', 'id'=>'datos1'))
                ));

       $pago = new Zend_Form_Element_Text('placa');
       $pago->setLabel('Numero de Pago:')
               //->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->setAttrib('size', 9)
               ->setAttrib('maxlength', 7)
             ->setAttrib('onkeyup', $changePlaca);
//
//        $administrativo = new Zend_Form_Element_Radio('titulo');
//        $administrativo->setLabel('Administrativo:')
//            ->setRequired(true)
//            ->addMultiOptions(array('t' => '  Si',
//                                    'f' => ' No'))
//            ->addErrorMessage('Debe indicar si es Admnistrativo.');


        $this->addElements(array($id,$ispago,$datos1,
                        $tipo,
                        $vehiculo,
                        $datos//,
                        //$administrativo

                        ));
    }
}

?>

