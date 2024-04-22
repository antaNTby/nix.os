<?php

# setting2_functions.php
function getImageInfo( $path_to_file ) {
    $imgInfo          = array();
    $path_to_file_win = fix_directory_separator( $path_to_file );
    $document_root_win    = fix_directory_separator( $_SERVER["DOCUMENT_ROOT"] );
    //
    $file_rel_path_win = str_replace( $document_root_win. DIRECTORY_SEPARATOR, "", $path_to_file_win );

    if ( file_exists( $path_to_file_win ) ) {

        $parts = pathinfo( $path_to_file_win );

        $attrA    = getimagesize( $path_to_file_win );
        $statA    = stat( $path_to_file );
        $fileSize = formatsize( $statA["size"] );

        $parts["path_to_file_win"] = $path_to_file_win;
        $parts["document_root"]    = $document_root_win;
        $parts["file_rel_path"]    = str_replace(DIRECTORY_SEPARATOR,SLASH,$file_rel_path_win);

        $parts["fileSize"] = $fileSize;
        $parts["width"]    = $attrA[0];
        $parts["height"]   = $attrA[1];
        $parts["mime"]     = $attrA["mime"];
        $parts["bits"]     = $attrA["bits"];

        $imgInfo = $parts;

    } else {

        $imgInfo = array(
            "dirname"      => null,
            "basename"     => null,
            "extension"    => null,
            "filename"     => null,

            "subfolder"    => null,

            "path_to_file" => null,
            "fileSize"     => "0",
            "width"        => 0,
            "height"       => 0,
            "mime"         => "error",
            "bits"         => 0,
        );

    }
/*
"dirname": "D:\\OSPanel\\domains\\new.nixminsk.by\\data\\images\\stamp",
"basename": "transpared256Oleg.png",
"extension": "png",
"filename": "transpared256Oleg",
"subfolder": "stamp",
"image_fullpath": "data\/images\\stamp\\transpared256Oleg.png",
"size": "96.09 Kb",
"width": 256,
"height": 256,
"mime": "image\/png",
"bits": 8
 */
    return $imgInfo;

}

function getSubfolderPathes( $folder_root, $FULL = 1 ): array{
    // $folder_root = '/etc';
    // $folder_root = $folder_root;
    $Results     = [];
    $folder_root = fix_directory_separator( $folder_root );

    $Iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator( $folder_root, RecursiveDirectoryIterator::SKIP_DOTS ),
        RecursiveIteratorIterator::SELF_FIRST,    // при блоке прав чтения не отвалится
        RecursiveIteratorIterator::CATCH_GET_CHILD// Ignore "Permission denied" (>>на которую у него нет прав на чтение)
    );

    $FullPaths = [];
    foreach ( $Iter as $path => $dir ) {
        if ( $dir->isDir() ) {
            $FullPaths[] = fix_directory_separator( $path );
            $pp          = explode( DIRECTORY_SEPARATOR, $path );
            $LastPaths[] = end( $pp );
        }
    }

    $Results = ( $FULL ) ? $FullPaths : $LastPaths;
    return $Results;
}

function setting_IMAGE( $settingID, $path_to_imagesDirectory = ADMIN_IMAGES_DEFAULT_PATH ) {
    $smarty                = new Smarty();
    $TemplatesDir          = getcwd() . "/core/modules/tpl/setting_templates/";
    $smarty->force_compile = true;
    $smarty->template_dir  = $TemplatesDir;
    $smarty->compile_id    = "setting"; // у кэш-файлов разных шаблонов будут разные имена (префиксы).

    $constantName = settingGetConstNameByID( $settingID );

    if ( isset( $_POST['save'] ) && isset( $_POST['setting_' . $constantName] ) ) {
        $post_value = $_POST['setting_' . $constantName];
        _setSettingOptionValue( $constantName, $post_value );
    }

    $btnBrowseIcon = '<i class="bi bi-images"></i>';
    $btnClearIcon  = '<i class="bi bi-x-lg"></i>';
    $aButtonBrowse = "<button type='button' class='btn btn-secondary' title='Browse Server' onclick='BrowseServer(" . $settingID . ")'>" . $btnBrowseIcon . "</button>";
    $aButtonClear  = "<button type='button' class='btn btn-danger' title='Clear Setting' onclick='ClearSetting(" . $settingID . ")'>" . $btnClearIcon . "</button>";

    $constantValue = _getSettingOptionValue( $constantName );

    ## костыль для старого инвойса
    if ( $constantValue == "CONF_LOGO_FILE" ) {
        $constantValue = CONF_LOGO_FILE;
    }

    $path_to_imagesDirectory = $path_to_imagesDirectory;

    $image_fullpath_win = fix_directory_separator( $path_to_imagesDirectory . DIRECTORY_SEPARATOR . ( $constantValue ? $constantValue : ADMIN_EMPTY_IMAGE_NAME ) );

    $image_fullpath = str_replace( DIRECTORY_SEPARATOR, SLASH, $image_fullpath_win );

    $smarty->assign( "btnBrowseIcon", $btnBrowseIcon );
    $smarty->assign( "btnClearIcon", $btnClearIcon );
    $smarty->assign( "aButtonBrowse", $aButtonBrowse );
    $smarty->assign( "aButtonClear", $aButtonClear );

    $smarty->assign( "settingID", $settingID );
    $smarty->assign( "constantName", $constantName );
    $smarty->assign( "constantValue", $constantValue );

    $ImageInfo = [];

    $ImageInfo = getImageInfo( $image_fullpath );

    $smarty->assign( "W", $ImageInfo["width"] );
    $smarty->assign( "H", $ImageInfo["height"] );
    $smarty->assign( "mime", $ImageInfo["mime"] );
    $smarty->assign( "bits", $ImageInfo["bits"] );
    $smarty->assign( "fileSize", $ImageInfo["fileSize"] );
    $smarty->assign( "path", $path_to_imagesDirectory );
    $smarty->assign( "image_fullpath_win", $image_fullpath_win );
    $smarty->assign( "image_fullpath", $image_fullpath );

    $subfolders = array();
    $subfolders = getSubfolderPathes( $path_to_imagesDirectory, 0 );
    $smarty->assign( "subfolders", $subfolders );

    foreach ( $subfolders as $dir ) {
        if ( str_contains( $image_fullpath_win, $dir ) ) {
            $smarty->assign( "dir_is_selected", $dir );
        }
    }

    return $smarty->fetch( $TemplatesDir . "setting_IMAGE.tpl.html" );
}

# setting2_functions.php
