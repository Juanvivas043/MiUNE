<?php

class Forms_Agregarpostulacion extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */

    public function init() {

        Zend_Loader::loadClass('Forms_Decorators_Basic');

        $this->setMethod('post');
        $this->setName('agregar_postulacion');
        $this->setAction("");
        $this->setAttrib('enctype', 'multipart/form-data');

        $id_vacante = new Zend_Form_Element_Hidden('id_vacante');
        $id_vacante->removeDecorator('label')
            ->removeDecorator('HtmlTag');
        $this->addElements(array($id_vacante));  

        $foto = new Zend_Form_Element_Image('foto');
        $foto->setAttrib('src', '../images/empresa-not-found.jpg')
            ->setAttrib('disabled', 'disabled')
            ->setAttrib('style', 'border-radius: 5px; box-shadow: 0px 0px 5px #666; max-width: 255px; max-height: 340px;');
        $this->addElements(array($foto));

        $empresa = new Zend_Form_Element_Text('empresa');
        $empresa->setLabel('Empresa:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 1, 'max' => 255 ) )
            ->setRequired(true)
            ->setAttrib('disable', false)
            ->setAttrib('style', 'width: 300px');
        $this->addElements(array($empresa));

        $title = new Zend_Form_Element_Text('title');
        $title->setLabel('Cargo:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 1, 'max' => 255 ) )
            ->setRequired(true)
            ->setAttrib('disable', false)
            ->setAttrib('style', 'width: 300px');
        $this->addElements(array($title));

        $fk_contrato = new Zend_Form_Element_Text('contrato');
        $fk_contrato->setLabel('Contrato: ')
            ->setRequired(true)
            ->setAttrib('style', 'width: 300px');
        $this->addElements(array($fk_contrato));

        $vacantes = new Zend_Form_Element_Text('vacantes');
        $vacantes->setLabel('Vacantes:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 1, 'max' => 2 ) )
            ->setRequired(true)
            ->setAttrib('disable', false)
            ->setAttrib('size', 2);
        $this->addElements(array($vacantes));

        $edad = new Zend_Form_Element_Text('edad');
        $edad->setLabel('Edad Minima:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 1, 'max' => 2 ) )
            ->setAttrib('disable', false)
            ->setAttrib('size', 2); 
        $this->addElements(array($edad));

        $sexo = new Zend_Form_Element_Text('sexo');
        $sexo->setLabel('Genero:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 1, 'max' => 255 ) )
            ->setRequired(true)
            ->setAttrib('disable', false)
            ->setAttrib('style', 'width: 300px');
        $this->addElements(array($sexo));

        $descripcion = new Zend_Form_Element_Textarea('descripcion');
        $descripcion->setLabel('Descripcion:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->setAttrib('disable', false)
            ->setAttrib('style', 'width: 500px, min-width: 500px; max-width: 500px; min-height: 70px;');
        $this->addElements(array($descripcion));

        $requisitos = new Zend_Form_Element_Textarea('requisitos');
        $requisitos->setLabel('Requisitos:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->setAttrib('disable', false)
            ->setAttrib('style', 'width: 500px, min-width: 500px; max-width: 500px; min-height: 70px;');
        $this->addElements(array($requisitos));

        $beneficios = new Zend_Form_Element_Textarea('beneficios');
        $beneficios->setLabel('Beneficios:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('disable', false)
            ->setAttrib('style', 'width: 500px, min-width: 500px; max-width: 500px; min-height: 70px;');
        $this->addElements(array($beneficios));

        $direccion = new Zend_Form_Element_Textarea('direccion');
        $direccion->setLabel('Dirección:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->setAttrib('disable', false)
            ->setAttrib('style', 'width: 500px, min-width: 500px; max-width: 500px; min-height: 70px;');
        $this->addElements(array($direccion));

        $fecha_culminacion = new Zend_Form_Element_Text('culminacion');
        $fecha_culminacion->setLabel('Fecha de Culminacion:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 10, 'max' => 10 ) )
            ->setRequired(true)
            ->setAttrib('disable', false)
            ->setAttrib('size', 11);
        $this->addElements(array($fecha_culminacion));

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
                            'id'     => 'file',
                        )
                    )
                )
            )
        );
        $this->dummy->clearValidators();
    }
}
?>