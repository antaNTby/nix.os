<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

//this file indicates listing of all available languages

class Language
{
    public $description; //language name
    public $filename; //language PHP constants file
    public $template; //template filename
}

//a list of languages
$lang_list = array();

//to add new languages add similiar structures

$lang_list[0]              = new Language();
$lang_list[0]->description = "новофашистский";
$lang_list[0]->filename    = "russian.php";
$lang_list[0]->iso2        = "ru";

$lang_list[1]              = new Language();
$lang_list[1]->description = "Belarusian";
$lang_list[1]->filename    = "belarusian.php";
$lang_list[1]->iso2        = "by";


//try add to work_branch