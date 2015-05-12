<?php

namespace App\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

/**
 * Base Controller
 *
 * @author Anderson Costa <arcostasi@gmail.com>
 */
abstract class BaseController implements ControllerProviderInterface {

    public function error($app, $message) {
        
        $result = array(
            'success' => false,
            'message' => $message
        );
        return $app->json($result);
    }

    function formatDate($date, $format){

        return date($format, strtotime($date));
    }

    function formatDateMysql($date) {

        return date('Y-m-d', strtotime(str_replace('/', '-', $date)));
    }

    function formatDateTimeMysql($datetime) {

        return date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $datetime)));
    }

    function uploadImage($image, $dir, $name = "image"){

        $file = $image;
        $path = PATH_PUBLIC . "/uploads/{$dir}/";
        $fileNameOriginal = $file->getClientOriginalName();
        $extension = strrchr($fileNameOriginal, '.');
        $fileName = $name .'_'. md5(microtime()) . strtolower($extension);

        $file->move($path, $fileName);

        return $fileName;

    }

    function uploadThumb($image, $dir) {

        $file = $image;
        $fileInfo = getimagesize($file->getFileInfo()->getPathName());

        if($fileInfo[0] == 80 && $fileInfo[1] == 80){

            return $this->uploadImage($image, $dir, 'thumb');


        } else {
            return false;
        }

    }

    function deleteImage($image, $dir) {

        if (empty($image) == false) {
            $imagePath = PATH_PUBLIC . "/uploads/{$dir}/" . $image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

    }

    function moneyFormatDecimal($money){
        return floatval(str_replace(',','.',str_replace('.','',$money)));
    }

    function moneyFormatReal($money){
        return number_format($money, 2, ',', '.');
    }

}