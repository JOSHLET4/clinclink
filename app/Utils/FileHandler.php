<?php
namespace App\Utils;

use Illuminate\Http\Request;

class FileHandler
{
    public static function handleSingleFileUpload(Request $request, $attributeName = null): Request
    {
        if ($request->hasFile('file')) {
            $fileName = time() . '.' . $request->file->extension();
            $request->file->storeAs('public/images', $fileName);
            $newRequestArray = $request->except(['file']);
            $newRequestArray[$attributeName] = $fileName;
            return new Request($newRequestArray);
        }
        return $request;
    }
}
