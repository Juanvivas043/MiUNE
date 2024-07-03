<?php 

class Forms_Empresa extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        
        $SwapBytes_Jquery = new SwapBytes_Jquery();

        $this->setMethod('post');
        $this->setName('empresa');
        $this->setOptions(array('escape' => true));

        $changeRIF = "$.getJSON(urlAjax + 'exists/data/' + escape($('#rif').serialize()) + '&' + escape($('#id').serialize()) + '&' + escape($('#tipo_rif').serialize()), function(data){executeCmdsFromJSON(data)});";

        $id = new Zend_Form_Element_Hidden('id');
        $id->removeDecorator('label')
            ->removeDecorator('HtmlTag');
                  
        $fk_tipopasantia = new Zend_Form_Element_Select('fk_tipopasantia');
        $fk_tipopasantia->setLabel('Tipo:')
            ->setAttrib('style', 'width: 200px');  
        
        $tipo_rif = new Zend_Form_Element_Select('tipo_rif');
        $tipo_rif->setLabel('Tipo de Rif: ')
            ->setAttrib('style', 'width: 40px');

        $rif = new Zend_Form_Element_Text('rif');
        $rif->setLabel('RIF: ')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 9)
            ->setAttrib('maxlength', 9)
            ->setAttrib('disable', false)
            ->setAttrib('onchange', $changeRIF);
            
        $razonsocial = new Zend_Form_Element_Text('razonsocial');
        $razonsocial->setLabel('Razon Social: ')
            ->setRequired(false)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 30)
            ->setAttrib('maxlength', 25 )
            ->setAttrib('disable', true)
            ->setAttrib('onchange', NULL);

        $nombre = new Zend_Form_Element_Text('nombre');
        $nombre->setLabel('Nombre:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);

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
        
        $telefono2 = new Zend_Form_Element_Text('telefono2');
        $telefono2->setLabel('Otro Teléfono:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(15, 15))    
            ->setAttrib('size', 16)
            ->setAttrib('maxlength', 15);
     
        $this->addElements(array($id,
                                 $tipo_rif,
                                 $rif,
                                 $razonsocial,
                                 $fk_tipopasantia,
                                 $nombre,
                                 $direccion,
                                 $telefono,
                                 $telefono2));  
    }

}