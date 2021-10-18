<?php


namespace App\Helpers;


use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileHelper
{
    public static function uploadFiles($files, $path)
    {
        $filesArray = array();
        try {
            if (isset($files) && is_array($files))
                foreach ($files as $file) {
                    $filename = $file->store($path);
                    // $filename = asset('storage/'.$filename);
                    array_push($filesArray, $filename);
                }
        } catch (\Exception $ex) {
            return false;
        }
        return $filesArray;
    }

    public static function removeFiles($images_paths)
    {
        foreach ($images_paths as $image) {
            Storage::delete($image);
        }
    }

    public static function getFiles($directory_path)
    {
        return Storage::files($directory_path);
    }

    public static function getAllFiles($directory_path)
    {
        return Storage::allFiles($directory_path);
    }

    /**
     * @desc update files array and return finall array
     * 
     * @param array $oldFiles
     * @param array $deletedFiles
     * @param array $NewFiles
     * @param string $path :uploaded NewFiles path
     * 
     * @return void
     * @auth yasserkanj
     */
    public static function updateFiles($oldFiles = [], $deletedFiles = [], $NewFiles = [], $path = "files"){
        if(isset($deletedFiles) && is_array($deletedFiles) && is_array($oldFiles)){
            $updatedFiles = array_diff($oldFiles,$deletedFiles);
        }
        else{
            $updatedFiles = $oldFiles;
        }
        $uploadedFiles = self::uploadFiles($NewFiles, $path);
        if( isset($uploadedFiles) && is_array($updatedFiles) ){
            $updatedFiles = array_merge($updatedFiles,$uploadedFiles);
        }
        if (isset($deletedFiles) && is_array($deletedFiles)){
            self::removeFiles($deletedFiles);
        }
        return $updatedFiles;
    } 


    /**
     * @param mixed $projectFiles
     * 
     * @return void
     * @auth yasserkanj
     */
    public static function zipProjectFiles($projectFiles){
        //create zips folder if it does not exists
        if(!File::exists(public_path('zips/'))) {
            File::makeDirectory(public_path('zips/'), $mode = 0777, true, true);
        }
        //fix files path
        $files = collect($projectFiles)->map(function($value){
            return public_path('storage/' . $value);
        })->toArray();
        //make zip file
        $zipName = \Str::uuid().".zip";
        $zipName = public_path('zips/') . $zipName;
        $zip = new ZipArchive;
        if($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE)){
            foreach ($files as $key => $file) {
                $zip->addFile($file,$projectFiles[$key]);
            }
            $zip->close();    
        }
        return $zipName;
    }
    public static function processImage(UploadedFile $image,$path)
    {
        try{
            $imageUrl = null;
            if ($image) {
                $imageName = uniqid("image") . '.' . $image->getClientOriginalExtension();
                $savedImage = $image->storePubliclyAs($path, $imageName);
                $imageUrl = Storage::url($savedImage);

            }
            return $imageUrl;
        }catch (\Exception $exception)
        {
            Log::error($exception->getMessage());
        }
    }
}