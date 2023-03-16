<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class DownloadController extends Controller
{
    public static function routeName()
    {
        return Str::snake("Download");
    }
    public function index()
    {

        $images = [storage_path() . '\app\public\files\CRlFUIvzRBjFZbwHphiGc03BDPsJcddmZBi49X8a.jpg', storage_path() . '\app\public\files\zIebb7s3ffN71dq0VZ45ESrofuEvXxGow7s6Dohz.jpg'];
        $zipFileName = 'images.zip';
        $zipFile = storage_path('app/' . $zipFileName);
        $zip = new ZipArchive;
        if (file_exists($zipFile)) {
            unlink($zipFile);
        }
        if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
            foreach ($images as $image) {
                $zip->addFile($image, "basename(image).jpg");
            }
            $zip->close();
        } else {
            return response()->json(['message' => 'Failed to create zip file.'], 500);
        }

        $headers = [
            'Content-Type' => 'application/zip',
        ];

        return response()->download($zipFile, $zipFileName, $headers);
    }
    // public function store()
    // {

    //     // $zip = new \ZipArchive();
    //     // $fileName = 'zipFile.zip';
    //     // if ($zip->open(public_path($fileName), \ZipArchive::CREATE) == TRUE) {
    //     //     $files = File::files(public_path('myFiles'));
    //     //     foreach ($files as $key => $value) {
    //     //         $relativeName = basename($value);
    //     //         $zip->addFile($value, $relativeName);
    //     //     }
    //     //     $zip->close();
    //     // }

    //     // return response()->download(public_path($fileName));

    //     $zip_file = 'invoices.zip'; // Name of our archive to download

    //     // Initializing PHP class
    //     $zip = new \ZipArchive();
    //     $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

    //     $invoice_file = 'invoices/aaa001.pdf';

    //     // Adding file: second parameter is what will the path inside of the archive
    //     // So it will create another folder called "storage/" inside ZIP, and put the file there.
    //     $zip->addFile(storage_path($invoice_file), $invoice_file);
    //     $zip->close();

    //     // We return the file immediately after download
    //     return response()->download($zip_file);
    // }
}
