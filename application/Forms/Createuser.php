<?php

class Forms_Createuser extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */

    public function init() {

        Zend_Loader::loadClass('Forms_Decorators_Basic');

        $this->setMethod('post');
        $this->setName('registro_empresa');

        $tipo_rif = new Zend_Form_Element_Select('tipo_rif');
        $tipo_rif->setLabel('Tipo de Rif: ')
            ->setRequired(true)
            ->setAttrib('style', 'width: 40px')
            ->setAttrib('disable', false);
        $this->addElements(array($tipo_rif));

        $rif = new Zend_Form_Element_Text('rif');
        $rif->setLabel('RIF: ')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 9)
            ->setAttrib('maxlength', 10)
            ->setAttrib('disable', false);
        $this->addElements(array($rif));

        $razon_social = new Zend_Form_Element_Text('razon_social');
        $razon_social->setLabel('Razon Social: ')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 70)
            ->setAttrib('maxlength',10)
            ->setAttrib('disable', true);
        $this->addElements(array($razon_social));

        $pk_usuario = new Zend_Form_Element_Text('pk_usuario');
        $pk_usuario->setLabel('C.I.: ')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('regex', false , array("/^([0-9]+)$/"))
            ->setAttrib('size', 9)
            ->setAttrib('maxlength', 10)
            ->setAttrib('disable', true)
            ->setAttrib('class','wait_form')
            ->setAttrib('style','display: none;');
        $this->addElements(array($pk_usuario));

        $primer_nombre = new Zend_Form_Element_Text('primer_nombre');
        $primer_nombre->setLabel('Primer Nombre:')
            ->setRequired(true)
            ->addValidator(new Zend_Validate_StringLength(array('min' => 2,
                                                                'max' => 80)))
            ->addValidator('regex', false , array("/^([a-zA-ZùÙüÜäàáëèéïìíöòóüùúÄÀÁËÈÉÏÌÍÖÒÓÜÚñÑ\s]+)$/"))
            ->addFilter('StripTags')
            ->addFilter('StringTrim')   
            ->setAttrib('size', 35)
            ->setAttrib('class','wait_form')
            ->setAttrib('style','display: none;')
            ->setAttrib('disable', true)
            ->setAttrib('maxlength', 80);
        $this->addElements(array($primer_nombre));

        $segundo_nombre = new Zend_Form_Element_Text('segundo_nombre');
        $segundo_nombre->setLabel('Segundo Nombre:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('regex', false , array("/^([a-zA-ZùÙüÜäàáëèéïìíöòóüùúÄÀÁËÈÉÏÌÍÖÒÓÜÚñÑ\s]+)$/"))
            ->setAttrib('size', 35)
            ->setAttrib('class','wait_form')
            ->setAttrib('style','display: none;')
            ->setAttrib('disable', true)
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
            ->setAttrib('class','wait_form')
            ->setAttrib('style','display: none;')
            ->setAttrib('disable', true)
            ->setAttrib('maxlength', 80);        
        $this->addElements(array($primer_apellido));

        $segundo_apellido = new Zend_Form_Element_Text('segundo_apellido');
        $segundo_apellido->setLabel('Segundo Apellido: ')
            ->addFilter('StripTags') 
            ->addFilter('StringTrim')
            ->addValidator('regex', false , array("/^([a-zA-ZùÙüÜäàáëèéïìíöòóüùúÄÀÁËÈÉÏÌÍÖÒÓÜÚñÑ\s]+)$/"))
            ->setAttrib('size', 35)
            ->setAttrib('class','wait_form')
            ->setAttrib('style','display: none;')
            ->setAttrib('disable', true)
            ->setAttrib('maxlength', 80);
        $this->addElements(array($segundo_apellido));

        $fecha_nacimiento = new Zend_Form_Element_Text('fechanacimiento');
        $fecha_nacimiento->setLabel('Fecha de nacimiento:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 10, 'max' => 10 ) )
            ->setAttrib('class','wait_form')
            ->setAttrib('style','display: none;')
            ->setAttrib('disable', true)
            ->setAttrib('size', 11);
        $this->addElements(array($fecha_nacimiento));
        
        $sexo = new Zend_Form_Element_Radio('sexo');
        $sexo->setLabel('Genero:')
            ->setRequired(true)
            ->addMultiOptions(array( 0 => ' Femenino',
                                     1 => ' Masculino'))
            ->setAttrib('class','wait_form')
            ->setAttrib('style','display: none;') 
            ->setAttrib('disable', true)
            ->addErrorMessage('Escoja una opción.');
        $this->addElements(array($sexo));

        $nacionalidad = new Zend_Form_Element_Radio('nacionalidad');
        $nacionalidad->setLabel('Nacionalidad:')
            ->setRequired(true)
            ->addMultiOptions(array( 0 => ' Venezolano',
                                     1 => ' Extranjero'))
            ->setAttrib('class','wait_form')
            ->setAttrib('style','display: none;')
            ->setAttrib('disable', true)
            ->addErrorMessage('Escoja una opción.');
        $this->addElements(array($nacionalidad));

        $direccion = new Zend_Form_Element_Textarea('direccion');
        $direccion->setLabel('Dirección:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('rows', 4)
            ->setAttrib('class','wait_form')
            ->setAttrib('style','display: none;')
            ->setAttrib('disable', true)
            ->setAttrib('cols', 39);
        $this->addElements(array($direccion));

        $telefono = new Zend_Form_Element_Text('telefono');
        $telefono->setLabel('Teléfono:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 16)
            ->setAttrib('class','wait_form')
            ->setAttrib('style','display: none;')
            ->setAttrib('disable', true)
            ->setAttrib('maxlength', 15);
        $this->addElements(array($telefono));

        $telefono_movil = new Zend_Form_Element_Text('telefono_movil');
        $telefono_movil->setLabel('Teléfono Movil:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim') 
            //->addValidator('StringLength', true, array(15, 15))    
            ->setAttrib('size', 16)
            ->setAttrib('class','wait_form')
            ->setAttrib('style','display: none;')
            ->setAttrib('disable', true)
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
                ->setAttrib('class','wait_form')
                ->setAttrib('style','display: none;')
                ->setAttrib('disable', true)
                ->setAttrib('maxlength', 255);
        $this->addElements(array($correo));

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
                            'tag'          => 'div',
                            'id'           => 'captcha',
                            'class'        => 'g-recaptcha wait_form',
                            'style'        => 'display: block;',
                            'data-sitekey' => '6Le2pR8TAAAAAOgkMMHpwruVRwVDJ4ghU3LqWjaM'
                        )
                    )
                )
            )
        );
        $this->dummy->clearValidators();

        $this->addElement('button', 'checkRif', 
            array(
                'ignore'=> true, 
                'label' => 'Verificar R.I.F.',
                'class' => 'ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only'
                )
        );

        $this->addElement('button', 'checkCI', 
            array(
                'ignore'=> true, 
                'label' => 'Verificar Cedula',
                'class' => 'ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only',
                'style' => 'display: none;'
                )
        );
    }
}

?>