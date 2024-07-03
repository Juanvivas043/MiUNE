 <?php

class Forms_Actualizaciondatos extends Zend_Form {

    public function init(){

        $this->setMethod('post');
        $this->setName('update_profile');

        $checkCedula = new Zend_Form_Element_Checkbox('checkCedula');
        $checkCedula->setRequired(true)
        			->setAttrib('class','check');
    	$ci = new Zend_Form_Element_Text('pk_usuario');
        $ci->setLabel('C.I.:')
            ->setRequired(false)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 9)
            ->setAttrib('maxlength', 10)
            ->setAttrib('disable', true);
    	$this->addElements(array($ci,$checkCedula));

    	$checkPrimerNombre = new Zend_Form_Element_Checkbox('checkPrimerNombre');
        $checkPrimerNombre->setRequired(true)
        				  ->setAttrib('class','check');
    	$primer_nombre = new Zend_Form_Element_Text('primer_nombre');
        $primer_nombre->setLabel('Primer Nombre:')
            ->setRequired(true)
            ->addValidator(new Zend_Validate_StringLength(array('min' => 2,
                                                                'max' => 80)))
            ->addFilter('StripTags')
            ->addFilter('StringTrim')   
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80)
            ->setAttrib('disable', true);
        $this->addElements(array($primer_nombre,$checkPrimerNombre));

        $checkSegundoNombre = new Zend_Form_Element_Checkbox('checkSegundoNombre');
        $checkSegundoNombre->setRequired(true)
        				   ->setAttrib('class','check');
        $segundo_nombre = new Zend_Form_Element_Text('segundo_nombre');
        $segundo_nombre->setLabel('Segundo Nombre:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80)
            ->setAttrib('disable', true);
        $this->addElements(array($segundo_nombre,$checkSegundoNombre));
        
        $checkPrimerApellido = new Zend_Form_Element_Checkbox('checkPrimerApellido');
        $checkPrimerApellido->setRequired(true)
        					->setAttrib('class','check');
        $primer_apellido = new Zend_Form_Element_Text('primer_apellido');
        $primer_apellido->setLabel('Primer Apellido:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80)
            ->setAttrib('disable', true);        
        $this->addElements(array($primer_apellido,$checkPrimerApellido));

        $checkSegundoApellido = new Zend_Form_Element_Checkbox('checkSegundoApellido');
        $checkSegundoApellido->setRequired(true)
        					 ->setAttrib('class','check');
        $segundo_apellido = new Zend_Form_Element_Text('segundo_apellido');
        $segundo_apellido->setLabel('Segundo Apellido:')
            ->addFilter('StripTags') 
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80)
            ->setAttrib('disable', true);
        $this->addElements(array($segundo_apellido,$checkSegundoApellido));

        $checkFechaNacimiento = new Zend_Form_Element_Checkbox('checkFechaNacimiento');
        $checkFechaNacimiento->setRequired(true)
        					 ->setAttrib('class','check');
        $fecha_nacimiento = new Zend_Form_Element_Text('fechanacimiento');
        $fecha_nacimiento->setLabel('Fecha de nacimiento:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 10, 'max' => 10 ) )
            ->setAttrib('size', 11)
            ->setAttrib('disable', true);
        $this->addElements(array($fecha_nacimiento,$checkFechaNacimiento));  

        $checkSexo = new Zend_Form_Element_Checkbox('checkSexo');
        $checkSexo->setRequired(true)
        		  ->setAttrib('class','check');
        $sexo = new Zend_Form_Element_Radio('sexo');
        $sexo->setLabel('Genero:')
            ->setRequired(true)
            ->addMultiOptions(array( 0 => ' Femenino',
                                     1 => ' Masculino'))
            ->addErrorMessage('Escoja una opción.')
            ->setAttrib('disable', true);
        $this->addElements(array($sexo,$checkSexo));

        $checkNacionalidad = new Zend_Form_Element_Checkbox('checkNacionalidad');
        $checkNacionalidad->setRequired(true)
        				  ->setAttrib('class','check');
        $nacionalidad = new Zend_Form_Element_Radio('nacionalidad');
        $nacionalidad->setLabel('Nacionalidad:')
            ->setRequired(true)
            ->addMultiOptions(array( 0 => ' Venezolano',
                                     1 => ' Extranjero'))
            ->addErrorMessage('Escoja una opción.')
            ->setAttrib('disable', true);
        $this->addElements(array($nacionalidad,$checkNacionalidad));

        $checkDireccion = new Zend_Form_Element_Checkbox('checkDireccion');
        $checkDireccion->setRequired(true)
        			   ->setAttrib('class','check');
        $direccion = new Zend_Form_Element_Textarea('direccion');
        $direccion->setLabel('Dirección:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('style','max-width: 275px; min-width: 275px;')
            ->setAttrib('rows', 4)
            ->setAttrib('cols', 39)
            ->setAttrib('disable', true);
        $this->addElements(array($direccion,$checkDireccion));

        $checkTelefono = new Zend_Form_Element_Checkbox('checkTelefono');
        $checkTelefono->setRequired(true)
        			  ->setAttrib('class','check');
        $telefono = new Zend_Form_Element_Text('telefono');
        $telefono->setLabel('Teléfono:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 16)
            ->setAttrib('maxlength', 15)
            ->setAttrib('disable', true);      
        $this->addElements(array($telefono,$checkTelefono));

        $checkCelular = new Zend_Form_Element_Checkbox('checkCelular');
        $checkCelular->setRequired(true)
        			 ->setAttrib('class','check');
        $telefono_movil = new Zend_Form_Element_Text('telefono_movil');
        $telefono_movil->setLabel('Teléfono Movil:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim') 
            ->setAttrib('size', 16)
            ->setAttrib('maxlength', 15)
            ->setAttrib('disable', true);                   	
        $this->addElements(array($telefono_movil,$checkCelular));

        $checkEmail = new Zend_Form_Element_Checkbox('checkEmail');
        $checkEmail->setRequired(true)
        		   ->setAttrib('class','check');
        $correo = new Zend_Form_Element_Text('correo');
        $correo->setLabel('Correo:')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addValidator('StringLength', true, array(11, 255))
                ->setAttrib('size', 35)
                ->setAttrib('maxlength', 255)
                ->setAttrib('disable', true);
        $this->addElements(array($correo,$checkEmail));

        $this->addElement('button', 'guardar', 
            array(
                'ignore'  => true, 
                'label'   => 'Actualizar',
                'class'   => 'ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only',
                'id'      => 'update'
                )
            );
    }
    
}
?>