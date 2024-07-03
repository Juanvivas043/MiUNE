<?php 

class Forms_Empresaperfil extends Zend_Form {
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

        $changeRIF = "$.getJSON(urlAjax + 'getRif/rif/' + escape($('#rif').val()) + '/tipo_rif/' + escape($('#tipo_rif').val()), function(data){executeCmdsFromJSON(data)});";

        $id = new Zend_Form_Element_Hidden('id');
        $id->removeDecorator('label')
            ->removeDecorator('HtmlTag');  
        
        $foto = new Zend_Form_Element_Image('foto');
        $foto->setLabel(' ')
            ->setAttrib('disabled', 'disabled')
            ->setAttrib('style', 'border-radius: 5px; box-shadow: 0px 0px 5px #666; margin-right: 0px; margin-bottom: 10px; max-width: 255px; max-height: 340px;');

        $empresa_id = new Zend_Form_Element_Select('empresa_id');
        $empresa_id->setLabel('Empresa: ')
            ->setAttrib('style', 'width: 300px');

        $tipo_rif = new Zend_Form_Element_Select('tipo_rif');
        $tipo_rif->setLabel('Tipo de Rif: ')
            ->setAttrib('style', 'width: 40px')
            ->setAttrib('onchange', $changeRIF);

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
            ->setAttrib('onchange', null);

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

        $location= new Zend_Form_Element_Button('location');
        $location->setLabel('')
                ->setAttrib('title', 'Mapa')
                ->setAttrib('class', 'btn-location')
                ->setAttrib('data-toggle', 'modal')
                ->setAttrib('data-target', '#modalMap')
                ->setAttrib('onclick', 'getLocation();');

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

        $update= new Zend_Form_Element_Button('update');
        $update->setLabel('')
                ->setAttrib('data-toggle', 'tooltip')
                ->setAttrib('data-placement', 'right')
                ->setAttrib('title', 'Subir Foto')
                ->setAttrib('onclick', 'show();');
     
        $this->addElements(array($id,
                                 $foto,
                                 $update));

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
                            'id'     => 'file-dropzone',
                            'class'  => 'dropzone'
                        )
                    )
                )
            )
        );
        $this->dummy->clearValidators();

        $this->addElements(array($empresa_id,
                                 $tipo_rif,
                                 $rif,
                                 $razonsocial,
                                 $nombre,
                                 $direccion,
                                 $location,
                                 $telefono,
                                 $telefono2));

        $this->addElement('submit', 'guardar', 
            array(
                'ignore'=> true, 
                'label' => 'Actualizar',
                'class' => 'ui-button ui-state-default ui-corner-all ui-widget ui-button-text-only'
            )
        );
    }

}

?>