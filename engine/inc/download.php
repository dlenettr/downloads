<?php
/*
=============================================
 Name      : Downloads v1.7
 Author    : Mehmet Hanoğlu ( MaRZoCHi )
 Site      : https://mehmethanoglu.com.tr
 License   : MIT License
=============================================
*/

if ( !defined( 'DATALIFEENGINE' ) OR !defined( 'LOGGED_IN' ) ) {
	die( "Hacking attempt!" );
}

if ( $member_id['user_group'] != 1 ) {
	msg( "error", $lang['index_denied'], $lang['index_denied'] );
}

include ENGINE_DIR . "/data/download.conf.php";
include ROOT_DIR . "/language/" . $config['langs'] . "/download.admin.lng";

if ( ! is_writable(ENGINE_DIR . '/data/download.conf.php' ) ) {
	$lang['stat_system'] = str_replace( "{file}", "engine/data/download.conf.php", $lang['stat_system'] );
	$fail = "<div class=\"alert alert-error\">{$lang['stat_system']}</div>";
} else $fail = "";

if ( $action == "save" ) {
	if ( $member_id['user_group'] != 1 ) { msg( "error", $lang['opt_denied'], $lang['opt_denied'] ); }
	if ( $_REQUEST['user_hash'] == "" or $_REQUEST['user_hash'] != $dle_login_hash ) { die( "Hacking attempt! User not found" ); }

	$save_con = $_POST['save_con'];
	$save_con['use_news'] = intval($save_con['use_news']);
	$save_con['use_static'] = intval($save_con['use_static']);
	$save_con['sep_page'] = intval($save_con['sep_page']);
	$save_con['prev_control'] = intval($save_con['prev_control']);
	$save_con['get_xfields'] = intval($save_con['get_xfields']);
	$save_con['show_ads'] = intval($save_con['show_ads']);
	$save_con['open_ext'] = intval($save_con['open_ext']);
	$save_con['use_timer'] = intval($save_con['use_timer']);
	$save_con['use_linkas'] = intval($save_con['use_linkas']);
	$save_con['dis_index'] = intval($save_con['dis_index']);

	$find = array(); $replace = array();
	$find[] = "'\r'"; $replace[] = "";
	$find[] = "'\n'"; $replace[] = "";

	$save_con = $save_con + $dset;
	$handler = fopen( ENGINE_DIR . '/data/download.conf.php', "w" );

	fwrite( $handler, "<?PHP \n\n//MWS Downloads Settings\n\n\$dset = array (\n" );
	foreach ( $save_con as $name => $value ) {
		$value = ( is_array( $value ) ) ? implode(",", $value ) : $value;
		$value = trim(strip_tags(stripslashes( $value )));
		$value = htmlspecialchars( $value, ENT_QUOTES, $config['charset']);
		$value = preg_replace( $find, $replace, $value );
		$name = trim(strip_tags(stripslashes( $name )));
		$name = htmlspecialchars( $name, ENT_QUOTES, $config['charset'] );
		$name = preg_replace( $find, $replace, $name );
		$value = str_replace( "$", "&#036;", $value );
		$value = str_replace( "{", "&#123;", $value );
		$value = str_replace( "}", "&#125;", $value );
		$value = str_replace( ".", "", $value );
		$value = str_replace( '/', "", $value );
		$value = str_replace( chr(92), "", $value );
		$value = str_replace( chr(0), "", $value );
		$value = str_replace( '(', "", $value );
		$value = str_replace( ')', "", $value );
		$value = str_ireplace( "base64_decode", "base64_dec&#111;de", $value );
		$name = str_replace( "$", "&#036;", $name );
		$name = str_replace( "{", "&#123;", $name );
		$name = str_replace( "}", "&#125;", $name );
		$name = str_replace( ".", "", $name );
		$name = str_replace( '/', "", $name );
		$name = str_replace( chr(92), "", $name );
		$name = str_replace( chr(0), "", $name );
		$name = str_replace( '(', "", $name );
		$name = str_replace( ')', "", $name );
		$name = str_ireplace( "base64_decode", "base64_dec&#111;de", $name );
		fwrite( $handler, "'{$name}' => '{$value}',\n" );
	}
	fwrite( $handler, ");\n\n?>" );
	fclose( $handler );

	msg( "info", $lang['opt_sysok'], $lang['opt_sysok_1'], "{$PHP_SELF}?mod=download" );

}

echoheader( "<i class=\"fa fa-download\"></i> Downloads", $lang['dwn_0'] );

function showRow( $title = "", $description = "", $field = "", $indent = false ) {
	if ( $indent ) { $_in = "<div class=\"ind_div\"></div>"; $_cl = " indented"; } else { $_in = ""; $_cl = ""; }
	echo "<tr><td class=\"col-xs-6 col-sm-6 col-md-7{$_cl}\">{$_in}<h6 class=\"media-heading text-semibold\">{$title}</h6><span class=\"text-muted text-size-small hidden-xs\">{$description}</span></td><td class=\"col-xs-6 col-sm-6 col-md-5{$_cl}\">{$field}</td></tr>";
}

function makeDropDown($options, $name, $selected) {
	$output = "<select class=\"uniform\" style=\"min-width:100px;\" name=\"{$name}\">\r\n";
	foreach ( $options as $value => $description ) {
		$output .= "<option value=\"{$value}\"";
		if( $selected == $value ) {
			$output .= " selected ";
		}
		$output .= ">{$description}</option>\n";
	}
	$output .= "</select>";
	return $output;
}

function makeCheckBox($name, $selected) {
	$selected = $selected ? "checked" : "";
	return "<input class=\"switch\" type=\"checkbox\" name=\"{$name}\" value=\"1\" {$selected}>";
}


function set_selected( $arr, $sel ) {
	if ( ! is_array( $sel ) ) { $sel = explode( ",", $sel ); }
	$html = "";
	foreach( $arr as $key => $val ) {
		$selected = ( in_array( $key, array_values( $sel ) ) ) ? " selected" : "";
		$html .= "<option style=\"color: black\"" . $selected . " value=\"" . $key . "\" >" . $val . "</option>";
	}
	return $html;
}

echo <<<HTML
{$fail}
<style>
.indented { background: #eee !important; padding: 0px !important; padding: 10px 0 !important; }
.ind_div { margin-left: 10px; display: block; background: #AAAAAA; width: 15px; height: 50px; float: left; margin-right: 5px;  }
</style>
<script>
$(document).ready( function() {
	$("select[name='save_con[use_linkas]']").change( function() {
		var selected = $(this).val();
		if ( selected == "0" ) {
			$("div#hash_text").fadeOut();
			$("div#hash_input").fadeOut();
			$("div#name_text").fadeOut();
			$("div#id_text").fadeIn();
		} else if ( selected == "1" ) {
			$("div#id_text").fadeOut();
			$("div#name_text").fadeOut();
			$("div#hash_text").fadeIn();
			$("div#hash_input").fadeIn();
		} else if ( selected == "2" ) {
			$("div#id_text").fadeOut();
			$("div#hash_input").fadeOut();
			$("div#hash_text").fadeOut();
			$("div#name_text").fadeIn();
		}
		//console.log( selected );
	}).change();
});
</script>
<form action="{$PHP_SELF}?mod=download&action=save" class="systemsettings" name="conf" id="conf" method="post">

<div class="panel panel-default">
	<div class="panel-heading">
		{$lang['dwn_1']}
	</div>
	<table class="table table-normal">
HTML;

	showRow( $lang['dwn_4'], $lang['dwn_5'], makeCheckBox( "save_con[use_news]", "{$dset['use_news']}" ) );
	showRow( $lang['dwn_6'], $lang['dwn_7'], makeCheckBox( "save_con[use_static]", "{$dset['use_static']}" ) );
	showRow( $lang['dwn_8'], $lang['dwn_9'], makeCheckBox( "save_con[sep_page]", "{$dset['sep_page']}" ) );
	showRow( $lang['dwn_10'], "{$lang['dwn_11']}<br /><br /><div id=\"id_text\" style=\"display: none\">{$lang['dwn_12']} <br /><b>{$lang['dwn_13']}:</b> http:site.com/file/243</div><div id=\"hash_text\" style=\"display: none\">{$lang['dwn_14']} <br /><b>{$lang['dwn_13']}:</b> http:site.com/file/6947deff65de0caf0b842c79509a6478</div><div id=\"name_text\" style=\"display: none\">{$lang['dwn_15']}<br /><b>{$lang['dwn_13']}:</b> http:site.com/file/Ornek_Dosya.rar</div>", makeDropDown( array( "0" => $lang['dwn_47'], "1" => $lang['dwn_48'], "2" => $lang['dwn_49'] ), "save_con[use_linkas]", "{$dset['use_linkas']}" ) . "<div id=\"hash_input\" style=\"display: none\"><br /><br /><input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[hash_key]\" value=\"{$dset['hash_key']}\" maxlength=\"12\" size=\"20\"></div>", false, true );
	showRow( $lang['dwn_16'], $lang['dwn_17'], makeCheckBox( "save_con[prev_control]", "{$dset['prev_control']}" ) );
	showRow( $lang['dwn_18'], $lang['dwn_19'], makeCheckBox( "save_con[get_xfields]", "{$dset['get_xfields']}" ) );
	showRow( $lang['dwn_20'], "{$lang['dwn_21']} {$lang['dwn_13']}: {banner_reklam}", makeCheckBox( "save_con[show_ads]", "{$dset['show_ads']}" ) );
	showRow( $lang['dwn_22'], $lang['dwn_23'], makeCheckBox( "save_con[open_ext]", "{$dset['open_ext']}" ) );
	showRow( $lang['dwn_24'], $lang['dwn_25'], makeCheckBox( "save_con[dis_index]", "{$dset['dis_index']}" ) );
	showRow( $lang['dwn_26'], $lang['dwn_27'], makeCheckBox( "save_con[use_timer]", "{$dset['use_timer']}" ), false, false );
	showRow( $lang['dwn_28'], $lang['dwn_29'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[time_count]\" value=\"{$dset['time_count']}\" size=\"10\">", true );

	echo "<tr>
       <td class=\"col-xs-10 col-sm-6 col-md-7\"><h6>{$lang['dwn_30']}</h6><span class=\"note large\">{$lang['dwn_31']}</span><br /><img src=\"engine/skins/images/download_hint.png\" style=\"border: 1px solid #ccc;\" alt=\"{$lang['dwn_32']}\"></td>
       <td class=\"col-xs-2 col-md-5 settingstd\">
       <b>%pagelink%</b> {$lang['dwn_33']}<br />
       <b>%sitename%</b> {$lang['dwn_34']}<br />
       <b>%filename%</b> {$lang['dwn_35']}<br />
       <b>%filesize%</b> {$lang['dwn_36']}<br />
       <b>%fileauthor%</b> {$lang['dwn_37']}<br />
       <b>%filedown%</b> {$lang['dwn_38']}<br />
       </td>
    </tr>";

	showRow( $lang['dwn_39'], $lang['dwn_40'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[header_title]\" value=\"{$dset['header_title']}\" size=\"50\">" );
	showRow( $lang['dwn_41'], $lang['dwn_42'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[name]\" value=\"{$dset['name']}\" size=\"50\">" );
	showRow( $lang['dwn_43'], $lang['dwn_44'], "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[title]\" value=\"{$dset['title']}\" size=\"50\">" );
	showRow( $lang['dwn_45'], "", "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[meta_desc]\" value=\"{$dset['meta_desc']}\" size=\"50\">" );
	showRow( $lang['dwn_46'], "", "<input type=\"text\" class=\"form-control\" style=\"text-align: center;\" name=\"save_con[meta_key]\" value=\"{$dset['meta_key']}\" size=\"50\">" );

echo <<<HTML
	</table>
	<div class="panel-footer">
		<input type="hidden" name="user_hash" value="{$dle_login_hash}" />
		<button type="submit" class="btn bg-teal btn-raised pull-right"><i class="fa fa-floppy-o position-left"></i>{$lang['user_save']}</button>
	</div>
</form>
HTML;

echofooter();
?>