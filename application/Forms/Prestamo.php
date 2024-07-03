<?php 

class Forms_Prestamo extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
  public function init() {
        
        $SwapBytes_Jquery = new SwapBytes_Jquery();
                
        $this->setMethod('post');
        $this->setName('prestamo');
	$this->setOptions(array('escape' => true));
        		
		$changeCI = "$.getJSON(urlAjax + 'exists/data/' + escape($('#prestamo').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
        	
        $id   = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
                   ->removeDecorator('HtmlTag');
                
                
            
        $fk_grupo = new Zend_Form_Element_Select('fk_grupo'); // cambiar a perfil
        $fk_grupo->setLabel('Grupo:')
                 ->setAttrib('style', 'width: 200px')
                  ->clearValidators()
                  ->removeValidator(NULL);  

        // $fk_sede = new Zend_Form_Element_Select('fk_sede'); // cambiar a perfil
        // $fk_sede->setLabel('Sede:')
        //          ->setAttrib('style', 'width: 200px')
        //           ->clearValidators()
        //           ->removeValidator()
        //           ->setRegisterInArrayValidator(false);   

        $fk_sede   = new Zend_Form_Element_Hidden('fk_sede');
        $fk_sede->removeDecorator('label')
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
                
        $nombre = new Zend_Form_Element_Text('nombre');
        $nombre->setLabel('Nombre:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
        
       
        
        $apellido = new Zend_Form_Element_Text('apellido');
        $apellido->setLabel('Apellido:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
        
     
        $direccion = new Zend_Form_Element_Textarea('direccion');
        $direccion->setLabel('Dirección:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('rows', 4)
            ->setAttrib('cols', 39);
        
        $escuela = new Zend_Form_Element_Text('escuela');
        $escuela->setLabel('Escuela:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);

        $telefono = new Zend_Form_Element_Text('telefono');
        $telefono->setLabel('Teléfono:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 16)
            ->setAttrib('maxlength', 15);
        
        $telefono_movil = new Zend_Form_Element_Text('telefono_movil');
        $telefono_movil->setLabel('Celular:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 16)
            ->setAttrib('maxlength', 15);
        
        $correo = new Zend_Form_Element_Text('correo');
        $correo->setLabel('Correo:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);

        $foto = new Zend_Form_Element_Image('foto');
		$foto->setLabel('Foto:')
		     ->setAttrib('width', 120)
		     ->setAttrib('height', 161)
			 ->setAttrib('src', '../images/empty_profile.jpg')
			 ->setAttrib('disabled', 'disabled');
     
 

        $this->addElements(array($id,
                                 $foto,
                                 $ci,
                                 $nombre,
                                 $apellido,
                                 $direccion,
                                 $escuela,
                                 $telefono,
                                 $telefono_movil,
                                 $correo,
                                 $fk_grupo
                                 ,$fk_sede
                                ));

    }

}
