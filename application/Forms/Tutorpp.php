<?php 

class Forms_Tutorpp extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
  public function init() {
        
        $SwapBytes_Jquery = new SwapBytes_Jquery();
                
        $this->setMethod('post');
        $this->setName('tutorpp');
	$this->setOptions(array('escape' => true));
        		
		$changeCI = "$.getJSON(urlAjax + 'exists/data/' + escape($('#tutorpp').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
        	
        $id   = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
                   ->removeDecorator('HtmlTag');
                
                
            
        $fk_grupo = new Zend_Form_Element_Select('fk_grupo');
        $fk_grupo->setLabel('Grupo:')
             ->setAttrib('style', 'width: 200px');  
        
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
        
        $nacionalidad = new Zend_Form_Element_Radio('nacionalidad');
        $nacionalidad->setLabel('Nacionalidad:')
            ->setRequired(true)
            ->addMultiOptions(array('f' => ' Venezolano',
                                    't' => ' Extranjero'))
            ->addErrorMessage('Escoja una opción.');

        $genero = new Zend_Form_Element_Radio('sexo');
        $genero->setLabel('Genero:')
            ->setRequired(true)
            ->addMultiOptions(array('f' => ' Femenino',
                                    't' => ' Masculino'))
            ->addErrorMessage('Escoja una opción.');

        $direccion = new Zend_Form_Element_Textarea('direccion');
        $direccion->setLabel('Dirección:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('rows', 4)
            ->setAttrib('cols', 39);

        $telefono = new Zend_Form_Element_Text('telefono');
        $telefono->setLabel('Teléfono:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(15, 15))
            ->setAttrib('size', 16)
            ->setAttrib('maxlength', 15);
        
        $telefono_movil = new Zend_Form_Element_Text('telefono_movil');
        $telefono_movil->setLabel('Teléfono Movil:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(15, 15))    
            ->setAttrib('size', 16)
            ->setAttrib('maxlength', 15);
        
        $correo = new Zend_Form_Element_Text('correo');
        $correo->setLabel('e-mail:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(11, 255))
            ->setAttrib('size', 30)
            ->setAttrib('maxlength', 255);

        $fecha_nacimiento = new Zend_Form_Element_Text('fechanacimiento');
        $fecha_nacimiento->setLabel('Fecha de nacimiento:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
			->addValidator('NotEmpty')
            ->addValidator('Date', true, array('dd/MM/YYYY'))
            ->addValidator('StringLength', true, array( 'min' => 10, 'max' => 10 ) )
            ->setAttrib('size', 11);
        
        $foto = new Zend_Form_Element_Image('foto');
		$foto->setLabel('Foto:')
		     ->setAttrib('width', 120)
		     ->setAttrib('height', 161)
			 ->setAttrib('src', '../images/empty_profile.jpg')
			 ->setAttrib('disabled', 'disabled');
     
 

        $this->addElements(array($id,
                                 $foto,
                                 $fk_grupo,
                                 $ci,
                                 $primer_nombre,
                                 $segundo_nombre,
                                 $primer_apellido,
                                 $segundo_apellido,
                                 $fecha_nacimiento,
                                 $genero,
                                 $nacionalidad,
                                 $direccion,
                                 $telefono,
                                 $telefono_movil,
                                 $correo));

    }

}