<?php

class Forms_Repositorio extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {

        $SwapBytes_Jquery = new SwapBytes_Jquery();

        $this->setMethod('post');
        $this->setName('recurso');
	    $this->setOptions(array('escape' => true));


        $id = new Zend_Form_Element_Hidden('pk_recurso');
        $id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $ci = new Zend_Form_Element_Text('fk_estudiante');
        $ci->setLabel('C.I: ')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('Digits')
            ->addValidator('StringLength', true, array(4, 8))
            ->addValidator('CedulaMatch', true)
            ->setAttrib('size', 9)
            ->setAttrib('maxlength', 8);

        $titulo = new Zend_Form_Element_Textarea('titulo');
        $titulo->setLabel('Titulo:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setAttrib('size', 25)
            ->setAttrib('style', 'height: 70px;
                                  width: 500px;
                                  wordwrap: break-word;
                                  resize: none;')
            ->setAttrib('maxlength', 150);

        $resumen = new Zend_Form_Element_Textarea('resumen');
        $resumen->setLabel('Resumen:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setAttrib('size', 25)
            ->setAttrib('style', 'height: 70px;
                                  width: 500px;
                                  wordwrap: break-word;
                                  resize: none;')
            ->setAttrib('maxlength', 400);
        
        $cota = new Zend_Form_Element_Text('cota');
        $cota->setLabel('Cota:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setAttrib('size', 20)
            ->setAttrib('maxlength', 10);
        
        $nota = new Zend_Form_Element_Text('nota');
        $nota->setLabel('Nota: ')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array(2, 5))
            ->setAttrib('size', 20)
            ->setAttrib('maxlength', 5);

        
        $tutor = new Zend_Form_Element_Select('fk_tutor');
        $tutor->setLabel('Tutor: ')
              ->setAttrib('style', 'width: 150px');
        
        $periodo = new Zend_Form_Element_Select('fk_periodo');
        $periodo->setLabel('Periodo: ')
                ->setAttrib('style', 'width: 150px');
    
        $tiporecurso = new Zend_Form_Element_Select('fk_tiporecurso');
        $tiporecurso->setLabel('Tipo de recurso: ')
                    ->setAttrib('style', 'width: 150px');

        $escuela = new Zend_Form_Element_Select('fk_escuela');
        $escuela->setLabel('Escuela: ')
                ->setAttrib('style', 'width: 150px');

        $lineainvestigacion = new Zend_Form_Element_Select('fk_lineainvestigacion');
        $lineainvestigacion->setLabel('Linea de Investigacion: ')
                ->setAttrib('style', 'width: 150px');

        $estado = new Zend_Form_Element_Select('fk_estado');
        $estado->setLabel('Estado: ')
                  ->setAttrib('style', 'width: 150px');

        $rutarecurso = new Zend_Form_Element_Hidden('rutarecurso');
        $rutarecurso->removeDecorator('label')
                    ->removeDecorator('HtmlTag');
         
        // $rutarecurso->setLabel('Recurso: ')
        //            ->setAttrib('style', 'width: 200px');

       $this->addElements(array($id,
                                 $titulo,
                                 $descripcion,
                                 $ci,
                                 $rutarecurso,
                                 $tiporecurso,
                                 $coleccion,
                                 $estado
                                 ));

    }

}