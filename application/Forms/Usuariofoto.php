<?php 

class Forms_Usuariofoto extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        
        $SwapBytes_Jquery = new SwapBytes_Jquery();
                
        $this->setMethod('post');
        $this->setName('usuario');
        $this->setOptions(array('escape' => true));
        		
		    $changeCI = "$.getJSON(urlAjax + 'exists/data/' + escape($('#usuario').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
        	
        $id  = new Zend_Form_Element_Hidden('id');
		    $id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

       $perfil = new Zend_Form_Element_Hidden('perfil');
       $perfil->removeDecorator('label')
              ->removeDecorator('HtmlTag');           
     
       $ci = new Zend_Form_Element_Text('pk_usuario');
       $ci->setLabel('C.I.:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('Digits')
            ->addValidator('StringLength', true, array(4, 8))
            ->setAttrib('size', 9)
            ->setAttrib('maxlength', 8)
            ->setAttrib('onchange', $changeCI);
                
        $primer_nombre = new Zend_Form_Element_Text('primer_nombre');
        $primer_nombre->setLabel('Primer Nombre:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
        
        $segundo_nombre = new Zend_Form_Element_Text('segundo_nombre');
        $segundo_nombre->setLabel('Segundo Nombre:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
        
        $primer_apellido = new Zend_Form_Element_Text('primer_apellido');
        $primer_apellido->setLabel('Primer Apellido:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
        
        $segundo_apellido = new Zend_Form_Element_Text('segundo_apellido');
        $segundo_apellido->setLabel('Segundo Apellido:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
        
        $foto = new Zend_Form_Element_Image('foto');
    		$foto->setLabel('Foto:')
    		     ->setAttrib('width', 120)
    		     ->setAttrib('height', 161)
    			   ->setAttrib('src', '../images/empty_profile.jpg')
    			   ->setAttrib('disabled', 'disabled');

        $update= new Zend_Form_Element_Button('update');
        $update->setLabel('')
                ->setAttrib('data-toggle', 'tooltip')
                ->setAttrib('data-placement', 'right')
                ->setAttrib('title', 'Subir Foto')
                ->setAttrib('onclick', 'show();');

        $this->addElements(array($id,
                                 $foto,
                                 $update));

        $this->addElement(
            'hidden', 'dummy', 
            array(
                'required' => true,
                'autoInsertNotEmptyValidator' => false,
                'decorators' => 
                array(
                    array(
                        'HtmlTag', 
                        array(
                            'tag'    => 'dd',
                            'id'     => 'file-dropzone',
                            'class'  => 'dropzone'
                        )
                    )
                )
            )
        );
        $this->dummy->clearValidators();

        $this->addElements(array($perfil,
                                 $ci,
                                 $primer_nombre,
                                 $segundo_nombre,
                                 $primer_apellido,
                                 $segundo_apellido));
        
    }

}