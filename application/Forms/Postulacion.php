<?php

class Forms_Postulacion extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */

    public function init() {

        Zend_Loader::loadClass('Forms_Decorators_Basic');

        $this->setMethod('post');
        $this->setName('registro_postulacion'); 

        $id_postulacion = new Zend_Form_Element_Hidden('id_postulacion');
        $id_postulacion->removeDecorator('label')
            ->removeDecorator('HtmlTag');
        $this->addElements(array($id_postulacion));

        $cedula = new Zend_Form_Element_Text('cedula');
        $cedula->setLabel('Cedula:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 1, 'max' => 9 ) )
            ->setAttrib('disable', true)
            ->setAttrib('style', 'width: 100px');
        $this->addElements(array($cedula));

        $postulado = new Zend_Form_Element_Text('postulado');
        $postulado->setLabel('Nombre:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 1, 'max' => 255 ) )
            ->setRequired(true)
            ->setAttrib('disable', true)
            ->setAttrib('style', 'width: 300px');
        $this->addElements(array($postulado));

        $correo = new Zend_Form_Element_Text('correo');
        $correo->setLabel('Correo:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setRequired(true)
            ->setAttrib('disable', true)
            ->setAttrib('style', 'width: 300px');
        $this->addElements(array($correo));

        $escuela = new Zend_Form_Element_Text('escuela');
        $escuela->setLabel('Escuela:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setRequired(true)
            ->setAttrib('disable', true)
            ->setAttrib('style', 'width: 300px');
        $this->addElements(array($escuela));

        $sexo = new Zend_Form_Element_Text('sexo');
        $sexo->setLabel('Genero:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setRequired(true)
            ->setAttrib('disable', true)
            ->setAttrib('style', 'width: 100px');
        $this->addElements(array($sexo));

        $telefono = new Zend_Form_Element_Text('telefono');
        $telefono->setLabel('Telefono:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setRequired(true)
            ->setAttrib('disable', true)
            ->setAttrib('style', 'width: 100px');
        $this->addElements(array($telefono));

        $celular = new Zend_Form_Element_Text('celular');
        $celular->setLabel('Celular:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setRequired(true)
            ->setAttrib('disable', true)
            ->setAttrib('style', 'width: 100px');
        $this->addElements(array($celular));

        $fecha_postulacion = new Zend_Form_Element_Text('fecha_postulacion');
        $fecha_postulacion->setLabel('Fecha Postulacion:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array( 'min' => 10, 'max' => 10 ) )
            ->setRequired(true)
            ->setAttrib('disable', true)
            ->setAttrib('style', 'width: 100px');
        $this->addElements(array($fecha_postulacion));

    }
}

?>