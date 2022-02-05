<?php


namespace App\Traits;


trait Helpers
{
    protected $imagesSizes = [
        'main_slider' => ['width' => 484, 'height' => 441],
        'medium' => ['width' => 268, 'height' => 249],
        'medium2' => ['width' => 208, 'height' => 183],
        'small' => ['width' => 268, 'height' => 134],
        'product_gallery_slider' => ['width' => 84, 'height' => 84],
        'product_gallery_preview' => ['width' => 266, 'height' => 381],
        'cart_thumb' => ['width' => 110, 'height' => 110]
    ];

    function superAdminCheck()
    {
        return auth()->user()->is_super_admin == 1;
    }

    function createProductUploadDirs($product_id, $imagesSizes)
    {
        if (!file_exists(base_path('public') . '/uploads/' . $product_id)) {
            @mkdir(base_path('public') . '/uploads/' . $product_id, 0777);
        }

        foreach ($imagesSizes as $dirName => $imagesSize) {
            if (!file_exists(base_path('public') . '/uploads/' . $product_id . '/' . $dirName)) {
                mkdir(base_path('public') . '/uploads/' . $product_id . '/' . $dirName, 0777);
            }
        }
    }


    function uploadFiles($request, $filename, $destination = null)
    {
        $files_array = [];

        $destination = $destination ? $destination : base_path('public') . '/uploads/';

        if ($request->hasfile($filename)) {

            foreach ($request->file($filename) as $image) {
                $ext = $image->getClientOriginalExtension();
                $file_name = time() . md5(rand(100, 999)) . '.' . $ext;
                $image->move($destination, $file_name);
                @chmod($destination . '/' . $file_name, 777);
                $files_array[] = $file_name;
            }
        }

        return $files_array;
    }

    function resizeImage($imagePath, $savePath, $width, $height)
    {
        Image::make($imagePath)
            ->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            })->save($savePath);
    }

    public function deleteFile($path)
    {
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    public static function slugify($string, $separator = "-")
    {
        // Slug
        $string = mb_strtolower($string);
        $string = @trim($string);
        $replace = "/(\\s|\\" . $separator . ")+/mu";
        $subst = $separator;
        $string = preg_replace($replace, $subst, $string);

        // Remove unwanted punctuation, convert some to '-'
        $puncTable = [
            // remove
            "'"  => '',
            '"'  => '',
            '`'  => '',
            '='  => '',
            '+'  => '',
            '*'  => '',
            '&'  => '',
            '^'  => '',
            ''   => '',
            '%'  => '',
            '$'  => '',
            '#'  => '',
            '@'  => '',
            '!'  => '',
            '<' => '',
            '>'  => '',
            '?'  => '',
            // convert to minus
            '['  => '-',
            ']'  => '-',
            '{'  => '-',
            '}'  => '-',
            '('  => '-',
            ')'  => '-',
            ' '  => '-',
            ','  => '-',
            ';'  => '-',
            ':'  => '-',
            '/'  => '-',
            '|'  => '-',
            '\\' => '-',
        ];
        $string = str_replace(array_keys($puncTable), array_values($puncTable), $string);

        // Clean up multiple '-' characters
        $string = preg_replace('/-{2,}/', '-', $string);

        // Remove trailing '-' character if string not just '-'
        if ($string != '-') {
            $string = rtrim($string, '-');
        }

        return $string;
    }
}
