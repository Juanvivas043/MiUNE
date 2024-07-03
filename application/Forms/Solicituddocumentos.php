<?php
class Forms_Solicituddocumentos extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {

        $SwapBytes_Jquery = new SwapBytes_Jquery();


        $this->setMethod('post');
        $this->setName('Solicitud');

        

        $changePago = " var longitud;
                        longitud = $('#Solicitud').find(':input#pago').val().length;
            if(longitud >= 6){
                $.getJSON(urlAjax + 'exists/data/' + escape($('#Solicitud').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});
            }";

        $id     = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $docs_atrs = new Zend_Form_Element_Hidden('doc_atrs');
		$docs_atrs->removeDecorator('label')
               ->removeDecorator('HtmlTag');

        $page   = new Zend_Form_Element_Hidden('page');
		$page->removeDecorator('label')
             ->removeDecorator('HtmlTag');

        $pago = new Zend_Form_Element_Text('pago');
        $pago->setLabel('Numero de Pago:')
               ->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('Digits')
               //->addValidator('StringLength', true, array(1, 2))
               //->addValidator('Between', true, array(0, 50))
               ->setAttrib('size', 10)
               ->setAttrib('maxlength', 10)
               ->setAttrib('onkeyup', $changePago);

       $documentos = new Zend_Form_Element_Hidden('hidden-documentos');
       $documentos->setDecorators(array(
                   'ViewHelper',
                   array(array('data'=>'HtmlTag'), array('tag'=>'dd', 'id'=>'documentos'))
                ));

       $error_helper = new Zend_Form_Element_Hidden('hidden-error_helper');
        $error_helper->setDecorators(array(
                   'ViewHelper',
                   array(array('data'=>'HtmlTag'), array('tag'=>'dd', 'id'=>'error_helper'))
                ));

         

        $this->addElements(array($id,
			$docs_atrs,
                        $page,
                        $pago,$error_helper,
            $documentos
                        ));
    }
}

?>
