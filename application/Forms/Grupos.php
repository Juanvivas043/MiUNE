<?php 
class Forms_Grupos extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        $this->setMethod('post');
        $this->setName('cronograma');

        $id     = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
           ->removeDecorator('HtmlTag');
		
        $filtro = new Zend_Form_Element_Hidden('filtro');
		$filtro->removeDecorator('label')
               ->removeDecorator('HtmlTag');
		
        $page   = new Zend_Form_Element_Hidden('page');
		$page->removeDecorator('label')
             ->removeDecorator('HtmlTag');

        $numero = new Zend_Form_Element_Text('numero');
        
        $numero->setLabel('#:')
               ->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('Digits')
               ->addValidator('StringLength', true, array(1, 2))
               ->addValidator('Between', true, array(0, 50))
               ->setAttrib('size', 2)
               ->setAttrib('maxlength', 2);

        $fecha = new Zend_Form_Element_Text('fecha');
        $fecha->setLabel('Fecha:')
              ->setRequired(true)
              ->addFilter('StripTags')
              ->addFilter('StringTrim')
              ->addValidator('Date', true, array('dd/MM/YYYY'))
              ->setAttrib('size', 11)
              ->setAttrib('maxlength', 10);

        $descripcion = new Zend_Form_Element_Textarea('descripcion');
        $descripcion->setLabel('Descripción:')
                    ->setRequired(true)
                    //->addFilter('StripTags')
                    ->addFilter('StringTrim')
                    ->setAttrib('rows', 7)
                    ->setAttrib('cols', 45);

        $contenido = new Zend_Form_Element_Textarea('contenido');
        $contenido->setLabel('Contenido:')
                  ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('StringTrim')
                  ->setAttrib('rows', 7)
                  ->setAttrib('cols', 45);

        $estrategia = new Zend_Form_Element_Select('fk_tipoestrategia');
        $estrategia->setLabel('T. Estra.:')
                   ->setAttrib('style', 'width: 250px');

        $evaluacion = new Zend_Form_Element_Select('fk_tipoevaluacion');
        $evaluacion->setLabel('T. Evalu.:')
                   ->setAttrib('style', 'width: 250px');

        $puntos = new Zend_Form_Element_Text('puntaje');
        $puntos->setLabel('Puntos:')
               ->setRequired(true)
               ->addFilter('StripTags')
               ->addFilter('StringTrim')
               ->addValidator('StringLength', true, array(1, 3))
               ->addValidator('Between', true, array(0, 20))
               ->setAttrib('size', 3)
               ->setAttrib('maxlength', 3);

        $this->addElements(array($id,
			$filtro,
            $page,
            $numero,
            $fecha,
            $descripcion,
            $contenido,
            $estrategia,
            $evaluacion,
            $puntos));
    }
}
