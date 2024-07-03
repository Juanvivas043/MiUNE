<?php 
class Forms_Recurso extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        $this->setMethod('post');
        $this->setName('Recursos');
        $this->setAction("");
        $this->setAttrib('enctype', 'multipart/form-data');

        $id     = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');
		
        $filtro = new Zend_Form_Element_Hidden('filtro');
		$filtro->removeDecorator('label')
               ->removeDecorator('HtmlTag');
		
        $page   = new Zend_Form_Element_Hidden('page');
		$page->removeDecorator('label')
             ->removeDecorator('HtmlTag');
                
        $fk_tipo_alt   = new Zend_Form_Element_Hidden('fk_tipo_alt');
		$page->removeDecorator('label')
             ->removeDecorator('HtmlTag');

        $numero = new Zend_Form_Element_Text('ordinal');
        
        $numero->setLabel('#:')
               ->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('Digits')
               ->addValidator('StringLength', true, array(1, 2))
               ->addValidator('Between', true, array(0, 50))
               ->setAttrib('size', 2)
               ->setAttrib('maxlength', 2);

        $descripcion = new Zend_Form_Element_Textarea('descripcion');
        $descripcion->setLabel('Descripción:')
                    ->setRequired(true)
                    //->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->setAttrib('rows', 3)
                    ->setAttrib('cols', 45);

        $contenido = new Zend_Form_Element_Textarea('contenido_html');
        $contenido->setLabel('Contenido:')
                  ->setRequired(false)
                  //->addFilter('StringTrim')
                  ->setAttrib('rows', 7)
                  ->setAttrib('cols', 45);

        $tipo = new Zend_Form_Element_Select('fk_tipo');
        $tipo->setLabel('T. Recurso:')
           ->setAttrib('style', 'width: 250px');
        
        $publico = new Zend_Form_Element_Radio('publico');
        $publico->setLabel('Público:')
            ->setRequired(true)
            ->addMultiOptions(array('t' => ' Si',
                                    'f' => ' No'))
            ->addErrorMessage('Debe escoger el estatus. ');
        

        $this->addElements(array($id,
			$filtro,
            $page,
            $numero,
            $tipo,
            $descripcion,
            $contenido,
            $publico,
            $fk_tipo_alt
         ));
        
        $this->addElement(
            'hidden',
            'dummy',
            array(
                'required' => false,
                'ignore' => true,
                'autoInsertNotEmptyValidator' => false,
                'decorators' => array(
                    array(
                        'HtmlTag', array(
                            'tag'  => 'div',
                            'id'   => 'file-uploader'
                        )
                    )
                )
            )
        );
        $this->dummy->clearValidators();
       

    }

    
}
