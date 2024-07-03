<?php

class Forms_Changeprofile extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */

    public function init() {

        $this->setMethod('post');
        $this->setName('change_profile');

    	$ci = new Zend_Form_Element_Text('pk_usuario');
        $ci->setLabel('C.I.:')
            ->setRequired(false)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 9)
            ->setAttrib('maxlength', 10)
            ->setAttrib('disable', true)//---------> Asi se deshabilitan campos 
            ->setAttrib('onchange', $changeCI);



    	$this->addElements(array($ci));

    	$primer_nombre = new Zend_Form_Element_Text('primer_nombre');
        $primer_nombre->setLabel('Primer Nombre:')
            ->setRequired(true)
            ->addValidator(new Zend_Validate_StringLength(array('min' => 2,
                                                                'max' => 80)))
            ->addValidator('regex', false , array("/^([a-zA-ZùÙüÜäàáëèéïìíöòóüùúÄÀÁËÈÉÏÌÍÖÒÓÜÚñÑ\s]+)$/"))
            ->addFilter('StripTags')
            ->addFilter('StringTrim')   
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);

            $this->addElements(array($primer_nombre));


        
        $segundo_nombre = new Zend_Form_Element_Text('segundo_nombre');
        $segundo_nombre->setLabel('Segundo Nombre:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('regex', false , array("/^([a-zA-ZùÙüÜäàáëèéïìíöòóüùúÄÀÁËÈÉÏÌÍÖÒÓÜÚñÑ\s]+)$/"))
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);

            $this->addElements(array($segundo_nombre));
        
        $primer_apellido = new Zend_Form_Element_Text('primer_apellido');
        $primer_apellido->setLabel('Primer Apellido:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('regex', false , array("/^([a-zA-ZùÙüÜäàáëèéïìíöòóüùúÄÀÁËÈÉÏÌÍÖÒÓÜÚñÑ\s]+)$/"))
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);        
            

        $this->addElements(array($primer_apellido));

        $segundo_apellido = new Zend_Form_Element_Text('segundo_apellido');
        $segundo_apellido->setLabel('Segundo Apellido:')
            ->addFilter('StripTags') 
            ->addFilter('StringTrim')
            ->addValidator('regex', false , array("/^([a-zA-ZùÙüÜäàáëèéïìíöòóüùúÄÀÁËÈÉÏÌÍÖÒÓÜÚñÑ\s]+)$/"))
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);

           	$this->addElements(array($segundo_apellido));


        $fecha_nacimiento = new Zend_Form_Element_Text('fechanacimiento');
        $fecha_nacimiento->setLabel('Fecha de nacimiento:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 10, 'max' => 10 ) )
            ->setAttrib('size', 11);
    
            $this->addElements(array($fecha_nacimiento));
            

        $sexo = new Zend_Form_Element_Radio('sexo');
        $sexo->setLabel('Genero:')
            ->setRequired(true)
            ->addMultiOptions(array( 0 => ' Femenino',
                                     1 => ' Masculino'))
            ->addErrorMessage('Escoja una opción.');
            
            $this->addElements(array($sexo));



        $nacionalidad = new Zend_Form_Element_Radio('nacionalidad');
        $nacionalidad->setLabel('Nacionalidad:')
            ->setRequired(true)
            ->addMultiOptions(array( 0 => ' Venezolano',
                                     1 => ' Extranjero'))
            ->addErrorMessage('Escoja una opción.');
            
            $this->addElements(array($nacionalidad));

            
            

        $direccion = new Zend_Form_Element_Textarea('direccion');
        $direccion->setLabel('Dirección:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('rows', 4)
            ->setAttrib('cols', 39);

            $this->addElements(array($direccion));

        $telefono = new Zend_Form_Element_Text('telefono');
        $telefono->setLabel('Teléfono:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 16)
            ->setAttrib('maxlength', 15);
            
        
        	$this->addElements(array($telefono));

        $telefono_movil = new Zend_Form_Element_Text('telefono_movil');
        $telefono_movil->setLabel('Teléfono Movil:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim') 
            //->addValidator('StringLength', true, array(15, 15))    
            ->setAttrib('size', 16)
            ->setAttrib('maxlength', 15);
                
                                	
        	$this->addElements(array($telefono_movil));

        $correo = new Zend_Form_Element_Text('correo');
        $correo->setLabel('Correo:')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('NotEmpty')
                ->addValidator('StringLength', true, array(11, 255))
                //->addValidator('regex', false, array("/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)+/"))
                ->setAttrib('size', 35)
                ->setAttrib('maxlength', 255);

                       

            $this->addElements(array($correo));

            // BOTON PARA ACTUALIZAR

            $this->addElement('submit', 'guardar', 
                array(
                    'ignore'=> true, 
                    'label' => 'Actualizar',
                    'class' => 'ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only'
                    )
                );


   
    }
    
}