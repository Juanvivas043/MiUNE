<?php
class SwapBytes_tinyMCE {

    protected $_inipath = '/configs/tinymce.ini';
// new Zend_Config_Ini(APPLICATION_PATH . '/configs/tinymce.ini', 'contenido')
    protected $_supported = array(
        'mode'      => array('textareas', 'specific_textareas', 'exact', 'none'),
        'theme'     => array('simple', 'advanced'),
        'format'    => array('html', 'xhtml'),
        'languages' => array('en'),
        'plugins'   => array('style', 'layer', 'table', 'save',
                             'advhr', 'advimage', 'advlink', 'emotions',
                             'iespell', 'insertdatetime', 'preview', 'media',
                             'searchreplace', 'print', 'contextmenu', 'paste',
                             'directionality', 'fullscreen', 'noneditable', 'visualchars',
                             'nonbreaking', 'xhtmlxtras', 'imagemanager', 'filemanager','template'));

    protected $_config = array('mode'  =>'textareas',
                               'theme' => 'simple',
                               'element_format' => 'html');
   function getScript($type){
         
        $config = new Zend_Config_Ini( APPLICATION_PATH . $this->_inipath, $type);

        $script = 'tinyMCE.init({' . PHP_EOL;
        $params = array();
        foreach ($config as $name => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            if (!is_bool($value)) {
                $value = '\'' . $value . '\'';
            }
            $params[] = $name . ': ' . $value;
        }
        $script .= implode(',' . PHP_EOL, $params) . PHP_EOL;
        $script .= '});';

        return $script;
   }
}
?>
