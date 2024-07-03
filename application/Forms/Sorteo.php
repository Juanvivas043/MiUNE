<?php
class Forms_Sorteo extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {

        $SwapBytes_Jquery = new SwapBytes_Jquery();


        $this->setMethod('post');
        $this->setName('Solicitud');

        $changePago = " var longitud;
                        longitud = $('#Solicitud').find(':input#pago').val().length;
            if(longitud >= 6){
                $.getJSON(urlAjax + 'exists/data/' + escape($('#Solicitud').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});
            }";

        $id     = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $filtro = new Zend_Form_Element_Hidden('filtro');
		$filtro->removeDecorator('label')
               ->removeDecorator('HtmlTag');

        $page   = new Zend_Form_Element_Hidden('page');
		$page->removeDecorator('label')
             ->removeDecorator('HtmlTag');

       $periodo = new Zend_Form_Element_Hidden('hidden-periodo');
       $periodo->setDecorators(array(
                   'ViewHelper',
                   array(array('data'=>'HtmlTag'), array('tag'=>'DIV', 'id'=>'periodo'))
                ));

       $sede = new Zend_Form_Element_Hidden('hidden-sede');
       $sede->setDecorators(array(
                   'ViewHelper',
                   array(array('data'=>'HtmlTag'), array('tag'=>'DIV', 'id'=>'sede'))
                ));

        $fechainicio = new Zend_Form_Element_Text('fechainicio');
        $fechainicio->setLabel('Fecha de Inicio:')
                 ->setRequired(true)
                 ->addFilter('StripTags')
                 ->addFilter('StringTrim')
                 ->addValidator('Date', true, array('dd/MM/YYYY'))
                 ->setAttrib('size', 11)
                 ->setAttrib('maxlength', 10);

        $fechafin = new Zend_Form_Element_Text('fechafin');
        $fechafin->setLabel('Fecha Fin:')
                ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
//                  ->addPrefixPath('Validator', 'Validator/', Zend_Form_Element::VALIDATE)
//                  ->addValidator('LessThanElement', false, array('token' => 'fechainicio'))
                  ->addValidator('Date', true, array('dd/MM/YYYY'))
                  ->setAttrib('size', 11)
                  ->setAttrib('maxlength', 10);

        $fechatope = new Zend_Form_Element_Text('fechatope');
        $fechatope->setLabel('Fecha de pago:')
                ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  //->addPrefixPath('Validator', 'Validator/', Zend_Form_Element::VALIDATE)
                  //->addValidator('LessThanElement', false, array('token' => 'fechafin'))
                  ->addValidator('Date', true, array('dd/MM/YYYY'))
                  ->setAttrib('size', 11)
                  ->setAttrib('maxlength', 10);

        $descripcion = new Zend_Form_Element_Text('descripcion');
        $descripcion->setLabel('Descripción:')
               ->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->setAttrib('size', 20)
               ->setAttrib('maxlength', 40);

//        $turno = new Zend_Form_Element_Radio('fk_turno');
//        $turno->setLabel('Turno:')
//            ->setRequired(true)
//            ->addMultiOptions(array('8' => '  Mañana',
//                                    '9' => '  Tarde',
//                                    '10' => '  Noche'))
//            ->addErrorMessage('Debe indicar el turno.');

        $tipo = new Zend_Form_Element_Select('fk_tiposorteo');
        $tipo->setLabel('Tipo de Sorteo:')
                   ->setAttrib('style', 'width: 250px');

        $administrativo = new Zend_Form_Element_Radio('administrativo');
        $administrativo->setLabel('Administrativo:')
            ->setRequired(true)
            ->addMultiOptions(array('t' => '  Si',
                                    'f' => ' No'))
            ->addErrorMessage('Debe indicar si es Admnistrativo.');

        $this->addElements(array($id,
			$filtro,
                        $page,
                        $descripcion,
                        $periodo,
                        $sede,
                        $fechainicio,
                        $fechafin,
                        $fechatope,
                        //$turno,
                        $tipo,
                        $administrativo

                        ));
    }
}

?>
