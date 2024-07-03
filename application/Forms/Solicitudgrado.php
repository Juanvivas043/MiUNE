<?php
class Forms_Solicitudgrado extends Zend_Form {
    
    public function init() {
        
        $this->setMethod('post');
        $this->setName('solicitudgrado');
	    $this->setOptions(array('escape' => true));
        
        $id   = new Zend_Form_Element_Hidden('id');
        $id->removeDecorator('label')
           ->removeDecorator('HtmlTag'); 

           $tecnicoAction = '$.getJSON(urlAjax+"tecnico/id/"+ $("#id").val()+"/tecnico/"+$("#tecnico").attr("checked") , function(data){
            executeCmdsFromJSON(data)});';
        
        $check01 = new Zend_Form_Element_Checkbox('checkbox01');
        $check01->setLabel('Fotocopia fondo negro del título de bachiller con sus respectivas estapillas y debidamente autenticado.');
        $check02 = new Zend_Form_Element_Checkbox('checkbox02');
        $check02->setLabel('Fotocopia fondo negro de las calificaciones de bachillerato con sus respectivas estapillas debidamente autenticado.');
        $check03 = new Zend_Form_Element_Checkbox('checkbox03');
        $check03->setLabel('Constancia de inscripción en el sistema nacional de ingreso a la educación universitaria.');
        $check04 = new Zend_Form_Element_Checkbox('checkbox04');
        $check04->setLabel('Fotocopia de la partida de nacimiento.');
        $check05 = new Zend_Form_Element_Checkbox('checkbox05');
        $check05->setLabel('Fotocopia de la cédula de identidad.');
        $check06 = new Zend_Form_Element_Checkbox('checkbox06');
        $check06->setLabel('Timbre fiscal equivalente al 30% de la unidad tributaria.');

        $check07 = new Zend_Form_Element_Checkbox('tecnico');
        
        $check07->setLabel('Tecnico superior')//omicron => 19759 14.200 => 19683
        ->setAttrib('onchange', $tecnicoAction);
       // ->setAttrib('onchange', $change);
        $tec1 = new Zend_Form_Element_Checkbox('19762');
        $tec1->setLabel('Fotocopia en fondo negro del titulo de tecnico Superior universitario')
        ->setAttrib('disabled', 'true');
        $tec2 = new Zend_Form_Element_Checkbox('19761');
        $tec2->setLabel('Fotocopia sencilla de la certificacion de calificaciones de Tecnico Superior universitario')
        ->setAttrib('disabled', 'true');
        $tec3 = new Zend_Form_Element_Checkbox('19760');
        $tec3->setLabel('Fotocopia del Dictamen de Equivalencias de estudios')
        ->setAttrib('disabled', 'true');

        $this->addElements(array($id,$check01,$check02,$check03,$check04,$check05,$check06,$check07,$tec1,$tec2,$tec3));
        
    }
    
}

?>
