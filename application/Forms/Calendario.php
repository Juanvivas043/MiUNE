<?php

class Forms_Calendario extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {

        $this->setMethod('post');
        $this->setName('Calendario');

        $SwapBytes_Jquery = new SwapBytes_Jquery();

        $changeRenglon = $SwapBytes_Jquery->fillSelect('fk_actividad', 'actividad', array('renglon' => 'fk_renglon'));
        $changeAct = "$.getJSON(urlAjax + 'displayact/data/' + escape($('#calendario').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";

        $this->setMethod('post');
        $this->setName('calendario');

        $id     = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $chkClase     = new Zend_Form_Element_Hidden('chkClase');
                      $chkClase->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $filtro = new Zend_Form_Element_Hidden('filtro');
		$filtro->removeDecorator('label')
               ->removeDecorator('HtmlTag');

        $consecutivo   = new Zend_Form_Element_Hidden('seq');
		$consecutivo->removeDecorator('label')
             ->removeDecorator('HtmlTag');

        $numero = new Zend_Form_Element_Text('consecutivo');

        $numero->setLabel('#:')
               ->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               //->addValidator('Digits')
               ->addValidator('StringLength', true, array(1, 4))
               //->addValidator('Between', true, array(0, 50))
               ->setAttrib('size', 4)
               ->setAttrib('maxlength', 4);

        $renglon = new Zend_Form_Element_Select('fk_renglon');
        $renglon->setLabel('Renglon:')
                   ->setAttrib('onchange', $changeRenglon)
                   ->setAttrib('style', 'width: 250px');

        $actividad = new Zend_Form_Element_Select('fk_actividad');
        $actividad->setLabel('Actividad:')
                   ->setAttrib('style', 'width: 250px')
                      ->setAttrib('onchange', $changeAct)
                    ->setAttrib('onshow', $changeAct);

        $actividadplus = new Zend_Form_Element_Textarea('extendida');
        $actividadplus->setLabel('Actividad:')
                      ->setAttrib('disabled', 'disabled')
                      ->setAttrib('rezisable', 'false')
                      ->setAttrib('style', 'width: 250px;height:80px;resize:none;');

        $titulo = new Zend_Form_Element_Radio('titulo');
        $titulo->setLabel('Título:')
            ->setRequired(true)
            ->addMultiOptions(array('t' => '  Si',
                                    'f' => ' No'))
            ->addErrorMessage('Debe indicar si es un título.');

        $destacada = new Zend_Form_Element_Radio('destacada');
        $destacada->setLabel('Destacar:')
            ->setRequired(true)
            ->addMultiOptions(array('t' => '  Si',
                                    'f' => ' No'))
            ->addErrorMessage('Debe indicar si desea destacar.');

        $fechainicio = new Zend_Form_Element_Text('fechainicio');
        $fechainicio->setLabel('Fecha de Inicio:')
                    //->setRequired(true)
                    ->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->addValidator('Date', true, array('dd/MM/YYYY'))
                    ->setAttrib('size', 11)
                    ->setAttrib('maxlength', 10);

        $fechafin = new Zend_Form_Element_Text('fechafin');
        $fechafin->setLabel('Fecha de Culminacion:')
                  //->setRequired(true)
                  //->setAllowEmpty(false)
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->addPrefixPath('Validator', 'Validator/', Zend_Form_Element::VALIDATE)
                  //->addValidator('ChildRequired', false, array('titulo' , 'f'))
                  ->addValidator('LessThanElement', false, array('token' => 'fechainicio'))
                  ->addValidator('Date', true, array('dd/MM/YYYY'))
                  ->setAttrib('size', 11)
                  ->setAttrib('maxlength', 10);

        


        $this->addElements(array($id,
                        $chkClase,
			$filtro,
                        $consecutivo,
                        $numero,
                        $renglon,
                        $actividad,
                        $actividadplus,
                        $titulo,
                        $destacada,
                        $fechainicio,
                        $fechafin,
                        ));
    }
}


?>
