<?php



function rsska_kuri($template, $data = null){


    $currdir =  dirname( __FILE__ );


    if ($data == null) {
        if (isset($_POST['data']))
            $data = json_decode($_POST['data']);
        else {
            return 'no data';
        }
    }


    if (!defined(RSSKA)) {
        $currdir = dirname(__FILE__).'/';
    }
    else {
        $currdir = RSSKA;
    }

    // add filters

    require $currdir.'libs/filter.php';
    require $currdir.'libs/utf8_ents.php';
    require $currdir.'libs/U2RFC822_modifier.php';
    require $currdir.'libs/CDATA_modifier.php';
    require $currdir.'libs/htmllite.php';
    require $currdir.'libs/date_W3C.php';
    require $currdir.'libs/date_ISO_8601.php';

    $fview = $currdir.'views/'.$view.'.phtml';


    if (file_exists($fview)) {
        $result = view($fview, array('main' => $main, 'items' => $items));
        return $result;
    }


}