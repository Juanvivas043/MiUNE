<?php
class Forms_Vehiculos extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {

        $SwapBytes_Jquery = new SwapBytes_Jquery();


        $this->setMethod('post');
        $this->setName('Inscripcion');


        $changeSuper2 = $SwapBytes_Jquery->fillSelect('marca', 'marcas', array('tipo' => 'tipo')) . ";$.getJSON(urlAjax + 'tipo/data/' + escape($('#Inscripcion').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";

        $changeSuper = $SwapBytes_Jquery->fillSelect('marca', 'marcas', array('tipo' => 'tipo'));

        $changeTipo = $SwapBytes_Jquery->fillSelect('modelo', 'modelos', array('marca' => 'marca'));

        $changePlaca = " var longitud;
                        longitud = $('#Inscripcion').find(':input#placa').val().length;
            if(longitud >= 6){
                $.getJSON(urlAjax + 'exists/data/' + escape($('#Inscripcion').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});
            }";

        $id     = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

         $hiden     = new Zend_Form_Element_Hidden('hiden');
		$hiden->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $placa = new Zend_Form_Element_Text('placa');
        $placa->setLabel('Placa:')
               ->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->setAttrib('size', 9)
               ->setAttrib('maxlength', 7)
             ->setAttrib('onkeyup', $changePlaca);


        $tipo = new Zend_Form_Element_Select('tipo');
        $tipo->setLabel('Tipo:')
                   ->setAttrib('style', 'width: 150px')
                   ->setAttrib('onchange', $changeSuper2);
                   //->setAttrib('onchange', $changeSuper2);

        $marca = new Zend_Form_Element_Select('marca');
        $marca->setLabel('Marca:')
                   ->setAttrib('style', 'width: 150px')
                   ->setAttrib('onchange', $changeTipo);

//        $marcamotos = new Zend_Form_Element_Select('marcamotos');
//        $marcamotos->setLabel('Marca:')
//                   ->setAttrib('style', 'width: 150px');
//                   //->setAttrib('onchange', $changeTipo);

        $modelo = new Zend_Form_Element_Select('modelo');
        $modelo->setLabel('Modelo:')
                   ->setAttrib('style', 'width: 150px');

        $ano = new Zend_Form_Element_Text('ano');
        $ano->setLabel('Año:')
               ->addValidator('Digits')
               //->addValidator('StringLength', true, array(1, 4))
               //->addValidator('Between', true, array(1500, 2013))
               ->setAttrib('size', 4)
               ->setAttrib('maxlength', 4);

        

        $this->addElements(array($id,$hiden,
                        $placa,
                        $tipo,
                        //$marcamotos,
                        $marca,
                        $modelo,
                        $ano

                        ));
    }
}

?>
