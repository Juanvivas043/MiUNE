<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of qqFileUploader
 *
 * @author nieldm
 */
class SwapBytes_FileUploader_qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 990485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 990485760){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new SwapBytes_FileUploader_qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new SwapBytes_FileUploader_qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
       // if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
        //    $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
        //    die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
       // }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE, $with_unique_id = false){
        if (!is_writable($uploadDirectory)){
            mkdir($uploadDirectory, 0777);
            if (!is_writable($uploadDirectory))
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        if($with_unique_id == true){
            $filename = $pathinfo['filename'].'-'.substr (md5(uniqid(rand(),true)), 0, 5);
        }else{
            $filename = $pathinfo['filename'];
        }
        
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
            return array('success'=>true, 'filename' => $filename, 'filedir' => $uploadDirectory, 'fileext' => $ext);
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }  
}

?>
