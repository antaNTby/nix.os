<?php
# upload CSV-file

if ( isset( $_FILES["csv"] ) && $_FILES["csv"]["name"] ) {
    if ( preg_match( "/^(.+?)\.csv(\.(bz2|gz))?$/", $_FILES["csv"]["name"], $matches ) ) {
        if ( isset( $matches[2] ) && $matches[3] == 'gz' ) {
            $file_CSV_name = "core/temp/file.csv.gz";
            $method_parse  = 1;
        } else {
            $file_CSV_name = "core/temp/file.csv";
            $method_parse  = 0;
        }
        if ( file_exists( $file_CSV_name ) ) {
            unlink( $file_CSV_name );
        }
        $smarty->assign( "file_loaded_name", $_FILES["csv"]["name"] ); // INFO
        $res = @move_uploaded_file( $_FILES["csv"]["tmp_name"], $file_CSV_name );
        $smarty->assign( "file_CSV_name", $file_CSV_name );
    }
}

if ( isset( $res ) && $res ) {
//uploaded successfully
    SetRightsToUploadedFile( $file_CSV_name );

    //show import configurator
    if ( $method_parse == 1 ) {
        $data = myfgetcsvgz( $file_CSV_name, $delimiter );
    } else {
        $data = myfgetcsv( $file_CSV_name, $delimiter );
    }
    if ( !count( $data ) ) {
        die( ERROR_CANT_READ_FILE );
    }

    if ( !$csv_utf8 ) {
        $data = iconv_all( $data );
    } // UTF8

    $data_size    = count( $data ); // INFO
    $csvfile_info = imCSVpreview( $data, 1, 2, $delimiter );
    $smarty->assign( "csvfile_info", $csvfile_info ); // end INFO

    $excel_configurator = imGetImportConfiguratorHtmlCode( $data );
    $smarty->assign( "CSV_import_configurator", $excel_configurator );
    $smarty->assign( "delimiter", $delimiterv );
} else {
    $smarty->assign( "CSV_import_result", "CSV upload_file_error" );
}
?>