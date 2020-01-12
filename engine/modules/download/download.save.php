<?php
/*
=============================================
 Name      : Downloads v1.8
 Author    : Mehmet Hanoğlu ( MaRZoCHi )
 Site      : https://mehmethanoglu.com.tr
 License   : MIT License
=============================================
*/

if ( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

require_once ENGINE_DIR . "/data/download.conf.php";

if ( $dset['save_log'] ) {

    $area = $_REQUEST['area'] == "static" ? 0 : 1;

    $user_id = $is_logged ? $member_id['user_id'] : 0;

    $db->query( "INSERT INTO " . PREFIX . "_downloads_stats (id, area, area_id, user_id, date) VALUES ({$id}, {$area}, {$row_news['id']}, {$user_id}, '{$_TIME}')" );

}