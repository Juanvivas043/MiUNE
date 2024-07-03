<?php

class Forms_Vacante extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */

    public function init() {

        Zend_Loader::loadClass('Forms_Decorators_Basic');

        $this->setMethod('post');
        $this->setName('registro_vacante'); 

        $id_vacante = new Zend_Form_Element_Hidden('id_vacante');
        $id_vacante->removeDecorator('label')
            ->removeDecorator('HtmlTag');
        $this->addElements(array($id_vacante));

        $empresa = new Zend_Form_Element_Text('empresa');
        $empresa->setLabel('Empresa:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setRequired(true)
            ->setAttrib('class','wait_form')
            ->setAttrib('disable', true)
            ->setAttrib('style', 'width: 200px');
        $this->addElements(array($empresa));

        $title = new Zend_Form_Element_Text('title');
        $title->setLabel('Titulo:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 1, 'max' => 255 ) )
            ->setRequired(true)
            ->setAttrib('class','wait_form')
            ->setAttrib('disable', false)
            ->setAttrib('style', 'width: 300px');
        $this->addElements(array($title));

        $fk_contrato = new Zend_Form_Element_Select('fk_contrato');
        $fk_contrato->setLabel('Contrato: ')
            ->setAttrib('style', 'width: 200px');
        $this->addElements(array($fk_contrato));

        $vacantes = new Zend_Form_Element_Text('vacantes');
        $vacantes->setLabel('Vacantes:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 1, 'max' => 2 ) )
            ->setRequired(true)
            ->setAttrib('class','wait_form')
            ->setAttrib('disable', false)
            ->setAttrib('size', 2);
        $this->addElements(array($vacantes));

        $edad = new Zend_Form_Element_Text('edad');
        $edad->setLabel('Edad Minima:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 1, 'max' => 2 ) )
            ->setAttrib('class','wait_form')
            ->setAttrib('disable', false)
            ->setAttrib('size', 2);
        $this->addElements(array($edad));

        $sexo = new Zend_Form_Element_Select('sexo');
        $sexo->setLabel('Sexo: ')
            ->setAttrib('style', 'width: 200px');
        $this->addElements(array($sexo));

        $descripcion = new Zend_Form_Element_Textarea('descripcion');
        $descripcion->setLabel('Descripcion:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->setAttrib('rows', 4)
            ->setAttrib('class','wait_form')
            ->setAttrib('disable', false)
            ->setAttrib('style', 'width: 350px; min-width: 350px; max-width: 350px; min-height: 25px;');
        $this->addElements(array($descripcion));

        $requisitos = new Zend_Form_Element_Textarea('requisitos');
        $requisitos->setLabel('Requisitos:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->setAttrib('rows', 4)
            ->setAttrib('class','wait_form')
            ->setAttrib('disable', false)
            ->setAttrib('style', 'width: 350px; min-width: 350px; max-width: 350px; min-height: 25px;');
        $this->addElements(array($requisitos));

        $beneficios = new Zend_Form_Element_Textarea('beneficios');
        $beneficios->setLabel('Beneficios:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('rows', 4)
            ->setAttrib('class','wait_form')
            ->setAttrib('disable', false)
            ->setAttrib('style', 'width: 350px; min-width: 350px; max-width: 350px; min-height: 25px;');
        $this->addElements(array($beneficios));

        $direccion = new Zend_Form_Element_Textarea('direccion');
        $direccion->setLabel('Dirección:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setRequired(true)
            ->setAttrib('rows', 4)
            ->setAttrib('class','wait_form')
            ->setAttrib('disable', false)
            ->setAttrib('style', 'width: 350px; min-width: 350px; max-width: 350px; min-height: 25px;');
        $this->addElements(array($direccion));

        $culminacion = new Zend_Form_Element_Text('culminacion');
        $culminacion->setLabel('Fecha de Culminacion:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 10, 'max' => 10 ) )
            ->setRequired(true)
            ->setAttrib('class','wait_form')
            ->setAttrib('disable', false)
            ->setAttrib('size', 11);
        $this->addElements(array($culminacion));

    }
}

?>