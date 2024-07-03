<?php
class Forms_listanegra extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {

        $SwapBytes_Jquery = new SwapBytes_Jquery();


        $this->setMethod('post');
        $this->setName('Lista');


        $changePlaca = " var longitud;
                        longitud = $('#Lista').find(':input#cedula').val().length;
            if(longitud >= 7){
                $.getJSON(urlAjax + 'exists/data/' + escape($('#Lista').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});
            }";

        $id     = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $ced     = new Zend_Form_Element_Hidden('ced');
		$ced->removeDecorator('label')
                    ->removeDecorator('HtmlTag');

        $cedula = new Zend_Form_Element_Text('cedula');
        $cedula->setLabel('Cédula:')
               //->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->setAttrib('size', 9)
               ->setAttrib('maxlength', 8)
             ->setAttrib('onblur', $changePlaca);

       $datos = new Zend_Form_Element_Hidden('hidden-datos');
       $datos->setDecorators(array(
                   'ViewHelper',
                   array(array('data'=>'HtmlTag'), array('tag'=>'dd', 'id'=>'datos'))
                ));

       $chkClase     = new Zend_Form_Element_Hidden('chkClase');
                      $chkClase->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $this->addElements(array($id,$ced,$chkClase,
                        $cedula,
                        $datos

                        ));
    }
}

?>
