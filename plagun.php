<?php
if ( $_REQUEST['reservation_t'] ) {
    $modx->runSnippet('reservation_t', array(
        'bShowInterfase' => false,
        'sEvent' => $_REQUEST['event'],
        'arrRequestData' => $_REQUEST,
   ));
   die();
}