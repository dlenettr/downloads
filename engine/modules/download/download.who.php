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

if ( $dset['show_log'] ) {

    $tpl2 = new dle_template();
    $tpl2->dir = TEMPLATE_DIR;
    $tpl2->load_template( 'download-who.tpl' );

    $file_id = intval( $row['id'] );

    $file_row_query = $db->query("SELECT s.user_id, s.id as file_id, s.date as download_date, u.name as user_name, u.foto as user_avatar, u.user_group FROM dle_downloads_stats s LEFT JOIN dle_users u ON (s.user_id = u.user_id) WHERE s.id = {$file_id} GROUP BY s.user_id");
    if ( $file_row_query->num_rows > 0 ) {

        while( $file_row = $db->get_row( $file_row_query ) ) {

            if ( date( 'Ymd', $file_row['download_date'] ) == date( 'Ymd', $_TIME ) ) {
                $tpl2->set( '{down-date}', $lang['time_heute'] . langdate( ", H:i", $file_row['download_date'] ) );
            } else if ( date( 'Ymd', $file_row['download_date'] ) == date( 'Ymd', ($_TIME - 86400) ) ) {
                $tpl2->set( '{down-date}', $lang['time_gestern'] . langdate( ", H:i", $file_row['download_date'] ) );
            } else {
                $tpl2->set( '{down-date}', langdate( $config['timestamp_active'], $file_row['download_date'] ) );
            }

            $news_date = $file_row['download_date'];
            $tpl2->copy_template = preg_replace_callback( "#\{down-date=(.+?)\}#i", "formdate", $tpl2->copy_template );

            if ( count( explode( "@", $file_row['user_avatar'] ) ) == 2 ) {
                $tpl2->set( '{user-foto}', 'http://www.gravatar.com/avatar/' . md5( trim( $file_row['user_avatar'] ) ) . '?s=' . intval( $user_group[$file_row['user_group']]['max_foto'] ) );
            } else {
                if ( $file_row['user_avatar'] && $config['version_id'] < "10.5" ) {
                    if ( ( file_exists( ROOT_DIR . "/uploads/fotos/" . $file_row['user_avatar'] ) ) ) {
                        $tpl2->set( '{user-foto}', $config['http_home_url'] . "uploads/fotos/" . $file_row['user_avatar'] );
                    } else {
                        $tpl2->set( '{user-foto}', "{THEME}/dleimages/noavatar.png" );
                    }
                } else if ( $file_row['user_avatar'] && $config['version_id'] >= "10.5" ) {
                    $tpl2->set( '{user-foto}', $file_row['user_avatar'] );
                }
                else $tpl2->set( '{user-foto}', "{THEME}/dleimages/noavatar.png" );
            }

            $tpl2->set( "{user-url}", ( $config['allow_alt_url'] ) ? $config['http_home_url'] . "user/" . urlencode( $file_row['user_name'] ) : $config['http_home_url'] . "index.php?subaction=userinfo&amp;user=" . urlencode( $file_row['user_name'] ) );

            $tpl2->set( "{user-url-popup}", ( $config['allow_alt_url'] ) ? "ShowProfile('" . urlencode( $file_row['user_name'] ) . "', '" . $config['http_home_url'] . "user/" . urlencode( $file_row['user_name'] ) . "/', '1'); return false;" : "ShowProfile('" . urlencode( $file_row['user_name'] ) . "', '" . $config['http_home_url'] . "index.php?subaction=userinfo&amp;user=" . urlencode( $file_row['user_name'] ) . "', '0'); return false;" );

            $group = $user_group[ $file_row['user_group'] ];

            $tpl2->set( '{user-name}', $file_row['user_name']);
            $tpl2->set( '{user-name-colored}', $group['group_prefix'] . $file_row['user_name'] . $group['group_suffix'] );

            $tpl2->set( '{user-group}', $group['group_name']);
            $tpl2->set( '{user-group-colored}', $group['group_prefix'] . $group['group_name'] . $group['group_suffix'] );

            $tpl2->set( '{user-group-icon}', $group['icon'] );

            $tpl2->compile('who-downloaded');
        }

        $tpl->set( '{who-downloaded}', $tpl2->result['who-downloaded'] );

    } else {
        $tpl->set( '{who-downloaded}', '' );
    }

} else {

    $tpl->set( '{who-downloaded}', '' );

}