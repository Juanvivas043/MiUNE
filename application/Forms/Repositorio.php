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
        $this->setAttrib('enctype', 'multipart/form-data');


        $id = new Zend_Form_Element_Hidden('id');
        $id->removeDecorator('label')
           ->removeDecorator('HtmlTag');

        $cedula = new Zend_Form_Element_Text('cedula');
        $cedula->setLabel('C.I: ')
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
                                  width: 400px;
                                  wordwrap: break-word;
                                  resize: none;')
            ->setAttrib('maxlength', 150);

        // $resumen = new Zend_Form_Element_Textarea('resumen');
        // $resumen->setLabel('Resumen:')
        //     ->setRequired(true)
        //     ->addFilter('StripTags')
        //     ->addFilter('StringTrim')
        //     ->addValidator('NotEmpty')
        //     ->setAttrib('size', 25)
        //     ->setAttrib('style', 'height: 70px;
        //                           width: 400px;
        //                           wordwrap: break-word;
        //                           resize: none;')
        //     ->setAttrib('maxlength', 250);

        $palabrasclave = new Zend_Form_Element_Textarea('palabrasclave');
        $palabrasclave->setLabel('Palabras Claves:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setAttrib('size', 25)
            ->setAttrib('style', 'height: 70px;
                                  width: 200px;
                                  wordwrap: break-word;
                                  resize: none;')
            ->setAttrib('maxlength', 100);
    
        $cota = new Zend_Form_Element_Text('cota');
        $cota->setLabel('Cota:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->setAttrib('size', 5)
            ->addValidator('StringLength', true, array(2, 5))
            ->setAttrib('maxlength', 5);
        
        $calificacion = new Zend_Form_Element_Text('calificacion');
        $calificacion->setLabel('Nota: ')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('Digits')
            ->setAttrib('size', 2)
            ->setAttrib('maxlength', 2);

        
        $tutor = new Zend_Form_Element_Text('tutor');
        $tutor->setLabel('Tutor: ')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('Digits')
            ->addValidator('StringLength', true, array(4, 8))
            ->setAttrib('size', 9)
            ->setAttrib('maxlength', 8);
        
        $periodo = new Zend_Form_Element_Select('periodo');
        $periodo->setLabel('Periodo: ')
                ->setAttrib('style', 'width: 175px');
    
        // $tiporecurso = new Zend_Form_Element_Select('fk_tiporecurso');
        // $tiporecurso->setLabel('Tipo de recurso: ')
        //             ->setAttrib('style', 'width: 150px');

        $escuela = new Zend_Form_Element_Select('escuela');
        $escuela->setLabel('Escuela: ')
                ->setAttrib('style', 'width: 275px');

        // $lineainvestigacion = new Zend_Form_Element_Select('fk_lineatematesis');
        // $lineainvestigacion->setLabel('Linea: ')
        //         ->setAttrib('style', 'width: 150px');

        $estado = new Zend_Form_Element_Select('publicado');
        $estado->setLabel('Estado: ')
               ->setAttrib('style', 'width: 150px');

//         $rutarecurso = new Zend_Form_Element_File('rutarecurso');
//         $rutarecurso->setLabel('Archivo: ')
//              ->setRequired(true)
//              ->setAttrib('style', 'width: 250px');
// /* 			 ->addValidator('Extension', false, 'pdf')
// 			 ->addValidator('Count', false, 1)
// 			 ->addValidator('Size', false, 15728640) // 200Mb.
//              ->addValidator('NotEmpty'); */

       $this->addElements(array($id,
                                 $cedula,
                                 $titulo,
                                 $palabrasclave,
                                 $cota,
                                 $calificacion,
                                 $tutor,
                                 $periodo,
                                 $escuela,
                                 $estado
                                 ));

    }

}