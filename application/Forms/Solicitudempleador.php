<?php

class Forms_Solicitudempleador extends Zend_Form {
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

        $this->addElement('button', 'solicitud', 
            array(
                'ignore'=> true, 
                'label' => 'Enviar Solicitud',
                'class' => 'ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only'
                )
        );
    }
}

?>