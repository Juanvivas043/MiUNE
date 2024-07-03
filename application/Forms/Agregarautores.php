<?php

class Forms_Agregarautores extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     *
     */
  public function init() {

        $this->setMethod('post');
        $this->setName('agregarautor');
	$this->setOptions(array('escape' => true));

        $addAutor = "$.getJSON(urlAjax + 'autoradd/data/' + escape($('#agregarautor').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";

         $id   = new Zend_Form_Element_Hidden('id');
         $id->removeDecorator('label')
         ->removeDecorator('HtmlTag');


         $pk_autorlibro   = new Zend_Form_Element_Hidden('pk_autorlibro');
         $pk_autorlibro->removeDecorator('label')
         ->removeDecorator('HtmlTag');

        $fk_autor = new Zend_Form_Element_Select('fk_autor');
        $fk_autor->setLabel('Autor:')
                 ->setAttrib('style', 'width: 405px')
                 ->setOrder(1)
                 ->setAttrib('style', 'width: 200px')
                 ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'fk_autor' . '-element'))
                  ->addDecorator('Label', array('tag' => 'dt'));
                  //->setAttrib('onclick', $clikautor);

        $fk_principal = new Zend_Form_Element_Select('fk_principal');
        $fk_principal->setLabel('')
                 ->setAttrib('style', 'width: 405px')
                 ->setOrder(2)
                 ->setAttrib('style', 'width: 200px')

                  ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'fk_principal' . '-element'))
                  ->addDecorator('Label', array('tag' => 'dd'));

        $agregar_autor = new Zend_Form_Element_Button('agregar_autor');
        $agregar_autor->setLabel('+')
        ->setOrder(3)
        ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
        ->setAttrib('onclick', $addAutor);

       $this->addElements(array(
                                    $id,
                                    $pk_autorlibro,
                                    $fk_autor,
                                    $fk_principal,
                                    $agregar_autor

                                ));

    }

     public function addAutor($id,$pos){


           // super variables
           $autortxt = 'fk_autor'.$id;
           $autor = 'fk_autor'.$id;
           $principaltxt = 'fk_principal'.$id;
           $principal = 'fk_principal'.$id;
           $eliminar_autortxt = 'eliminar_autor'.$id;
           $eliminar_autor = 'eliminar_autor'.$id;


           $eli_autor = "$.getJSON(urlAjax + 'deleteautor/id/'+$id , function(data){executeCmdsFromJSON(data)});";
           //$clikautor  = "$.getJSON(urlAjax + 'clikautor/', function(data){executeCmdsFromJSON(data)});";

           $$autor = new Zend_Form_Element_Select($autortxt);
           $$autor->setLabel('')
           ->setAttrib('style', 'width: 200px')
           ->setOrder($pos)
           //->setValue("9978")
           ->addDecorator('HtmlTag', array('tag' => 'dd',
                    'id'  => $autortxt . '-element'))
           ->addDecorator('Label', array('tag' => 'dt'));
           //->setAttrib('onclick', $clikautor);



            $$principal = new Zend_Form_Element_Select($principaltxt);
            $$principal->setLabel('')
                 ->setAttrib('style', 'width: 200px')
                 ->setOrder($pos+1)
                  ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => $principaltxt . '-element'))
                  ->addDecorator('Label', array('tag' => 'dd'));

            $$eliminar_autor = new Zend_Form_Element_Button($eliminar_autortxt);
            $$eliminar_autor->setLabel('-')
            ->setOrder($pos+2)
            ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
            ->setAttrib('onclick', $eli_autor);


             $this->addElements(array($$autor,
                                      $$principal,
                                      $$eliminar_autor
                                ));


          return $this;

   }

}

?>
