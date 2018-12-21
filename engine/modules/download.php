<?php
/*
=============================================
 Name      : MWS Downloads v1.5
 Author    : Mehmet Hanoğlu ( MaRZoCHi )
 Site      : http://dle.net.tr/   (c) 2015
 License   : MIT License
=============================================
*/

if ( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

require_once ENGINE_DIR . "/data/download.conf.php";

if (
	( $dset['use_linkas'] == "0" && isset( $_REQUEST['id'] ) ) ||
	( $dset['use_linkas'] == "1" && isset( $_REQUEST['hash'] ) ) ||
	( $dset['use_linkas'] == "2" && isset( $_REQUEST['name'] ) )
) {

	$sel_map = array( "0" => "id", "1" => "hash", "2" => "name" );

	$sel = $sel_map[ $dset['use_linkas'] ];

	$$sel = $db->safesql( $_REQUEST[ $sel ] );

	$area = $db->safesql( $_REQUEST['area'] );

	$_select = ( $dset['get_xfields'] == '1' ) ? " p.xfields," : "";

	if ( $dset['use_linkas'] == "0" && is_numeric( $id ) ) {
		$where = "f.id = '{$id}'";
	} else if ( $dset['use_linkas'] == "1" && preg_match( "/^[a-f0-9]{32}$/", $hash ) ) {
		$hash_key = $db->safesql( $dset['hash_key'] );
		$where = "MD5(CONCAT('" . $hash_key . "', f.id )) = '{$hash}'";
	} else if ( $dset['use_linkas'] == "2" ) {
		$where = "f.name = '{$name}'";
	} else {
		msgbox( $lang['dwn_5'], "<ul><li>{$lang['dwn_0']}</li></ul>" );
	}

	$_sql = ( $area == "static" ) ? "SELECT f.*, s.descr as title, s.name as alt_name FROM " . PREFIX . "_static_files f LEFT JOIN " . PREFIX . "_static s ON s.id = f.static_id WHERE f.id = '{$id}'" : "SELECT f.*, p.title, p.alt_name,{$_select} p.category, e.related_ids FROM " . PREFIX . "_files f LEFT JOIN " . PREFIX . "_post p ON p.id = f.news_id LEFT JOIN " . PREFIX . "_post_extras e ON p.id = e.news_id WHERE {$where}";

	$info = $db->super_query( $_sql );

	if ( isset( $info ) && count( $info ) > 0 ) {

		if ( $area != "static" ) {
			$info['category'] = intval( $info['category'] );
			if( $config['allow_alt_url'] == "1" ) {
				if( $config['seo_type'] == 1 OR $config['seo_type'] == 2 ) {
					if( $info['category'] and $config['seo_type'] == 2 ) {
						$full_link = $config['http_home_url'] . get_url( $info['category'] ) . "/" . $info['news_id'] . "-" . $info['alt_name'] . ".html";
					} else {
						$full_link = $config['http_home_url'] . $info['news_id'] . "-" . $info['alt_name'] . ".html";
					}
				} else {
					$full_link = $config['http_home_url'] . date( 'Y/m/d/', $info['date'] ) . $info['alt_name'] . ".html";
				}
			} else {
				$full_link = $config['http_home_url'] . "index.php?newsid=" . $info['news_id'];
			}

			if ( ! $info['category'] ) {
				$my_cat = "---";
				$my_cat_link = "---";
			} else {
				if ( ! array_key_exists( "category_separator", $config ) || empty( $config['category_separator'] ) ) $config['category_separator'] = "&raquo;";
				$my_cat = array ();
				$my_cat_link = array ();
				$cat_list = explode( ',', $info['category'] );
				if ( $config['category_separator'] != ',') $config['category_separator'] = ' '.$config['category_separator'];
				if ( count( $cat_list ) == 1 OR ( $view_template == "rss" AND $config['rss_format'] == 2 ) ) {
					$my_cat[] = $cat_info[$cat_list[0]]['name'];
					$my_cat_link = get_categories( $cat_list[0], $config['category_separator']);
				} else {
					foreach ( $cat_list as $element ) {
						if( $element ) {
							$my_cat[] = $cat_info[$element]['name'];
							if( $config['allow_alt_url'] ) $my_cat_link[] = "<a href=\"" . $config['http_home_url'] . get_url( $element ) . "/\">{$cat_info[$element]['name']}</a>";
							else $my_cat_link[] = "<a href=\"$PHP_SELF?do=cat&category={$cat_info[$element]['alt_name']}\">{$cat_info[$element]['name']}</a>";
						}
					}
					$my_cat_link = implode( "{$config['category_separator']} ", $my_cat_link );
				}
				$my_cat = implode( "{$config['category_separator']} ", $my_cat );
			}

			$from_news = false;
			if ( ( isset( $_ENV['HTTP_REFERER'] ) AND ! empty( $_ENV['HTTP_REFERER'] ) ) OR ( isset( $_SERVER['HTTP_REFERER'] ) AND ! empty( $_SERVER['HTTP_REFERER'] ) ) ) {
				$referer = ( empty( $_ENV['HTTP_REFERER'] ) ) ? $_SERVER['HTTP_REFERER'] : $_ENV['HTTP_REFERER'];
				preg_match( "#([0-9]+)\-(.*)\.html#", $referer, $matches );
				if ( $matches ) $referer_id = $matches[1];
				$from_news = ( $referer_id == $info['news_id'] ) ? true : false;
			}
		}

		if ( ! file_exists( ROOT_DIR . "/uploads/files/" . $info['onserver'] ) ) {
			@header( "HTTP/1.0 404 Not Found" );
			$dset['header_title'] = $lang['dwn_1'];
			$dset['name'] = "";
			$dset['title'] = $lang['dwn_2'];
			msgbox( $lang['dwn_5'], "<ul><li>{$lang['dwn_1']}</li></ul>" );
			die();
		}

		$info['size'] = formatsize( @filesize( ROOT_DIR . "/uploads/files/" . $info['onserver'] ) );
		$info['title'] = stripslashes( $info['title'] );
		$info['title'] = str_replace( "{", "&#123;", $info['title'] );

		if ( $dset['sep_page'] == "1" ) {
			$tpl->load_template( 'download_page.tpl' );
		} else {
			$tpl->load_template( 'download.tpl' );
		}

		if ( $dset['prev_control'] == "1" && $area != "static" ) {
			if ( $from_news ) {
				$tpl->set_block( "'\\[not-direct\\](.*?)\\[/not-direct\\]'si", "\\1" );
				$tpl->set_block( "'\\[direct\\](.*?)\\[/direct\\]'si", "" );
			} else {
				$tpl->set_block( "'\\[not-direct\\](.*?)\\[/not-direct\\]'si", "" );
				$tpl->set_block( "'\\[direct\\](.*?)\\[/direct\\]'si", "\\1" );
			}
		} else {
			$tpl->set_block( "'\\[not-direct\\](.*?)\\[/not-direct\\]'si", "\\1" );
			$tpl->set_block( "'\\[direct\\](.*?)\\[/direct\\]'si", "" );
		}

		if ( ! $user_group[$member_id['user_group']]['allow_files'] ) {
			$tpl->set_block( "'\\[not-allowed\\](.*?)\\[/not-allowed\\]'si", "\\1" );
			$tpl->set_block( "'\\[allowed\\](.*?)\\[/allowed\\]'si", "" );
		} else {
			$tpl->set_block( "'\\[not-allowed\\](.*?)\\[/not-allowed\\]'si", "" );
			$tpl->set_block( "'\\[allowed\\](.*?)\\[/allowed\\]'si", "\\1" );
		}

		if ( ! empty( $info['xfields'] ) && $dset['get_xfields'] == '1' && $area != "static" ) {

			$xfields = xfieldsload();

			if( count($xfields) ) {

				$xfieldsdata = xfieldsdataload( $info['xfields'] );

				foreach ( $xfields as $value ) {
					$preg_safe_name = preg_quote( $value[0], "'" );

					if( $value[20] ) {

					  $value[20] = explode( ',', $value[20] );

					  if( $value[20][0] AND !in_array( $member_id['user_group'], $value[20] ) ) {
						$xfieldsdata[$value[0]] = "";
					  }

					}

					if ( $value[3] == "yesorno" ) {

					    if( intval($xfieldsdata[$value[0]]) ) {
							$xfgiven = true;
							$xfieldsdata[$value[0]] = $lang['xfield_xyes'];
						} else {
							$xfgiven = false;
							$xfieldsdata[$value[0]] = $lang['xfield_xno'];
						}

					} else {

						if($xfieldsdata[$value[0]] == "") $xfgiven = false; else $xfgiven = true;

					}

					if( !$xfgiven ) {
						$tpl->copy_template = preg_replace( "'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
						$tpl->copy_template = str_ireplace( "[xfnotgiven_{$value[0]}]", "", $tpl->copy_template );
						$tpl->copy_template = str_ireplace( "[/xfnotgiven_{$value[0]}]", "", $tpl->copy_template );
					} else {
						$tpl->copy_template = preg_replace( "'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is", "", $tpl->copy_template );
						$tpl->copy_template = str_ireplace( "[xfgiven_{$value[0]}]", "", $tpl->copy_template );
						$tpl->copy_template = str_ireplace( "[/xfgiven_{$value[0]}]", "", $tpl->copy_template );
					}

					if(strpos( $tpl->copy_template, "[ifxfvalue" ) !== false ) {
						$tpl->copy_template = preg_replace_callback ( "#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is", "check_xfvalue", $tpl->copy_template );
					}

					if ( $value[6] AND !empty( $xfieldsdata[$value[0]] ) ) {
						$temp_array = explode( ",", $xfieldsdata[$value[0]] );
						$value3 = array();

						foreach ($temp_array as $value2) {

							$value2 = trim($value2);
							$value2 = str_replace("&#039;", "'", $value2);

							if( $config['allow_alt_url'] ) $value3[] = "<a href=\"" . $config['http_home_url'] . "xfsearch/" .$value[0]."/". urlencode( $value2 ) . "/\">" . $value2 . "</a>";
							else $value3[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xfname=".$value[0]."&amp;xf=" . urlencode( $value2 ) . "\">" . $value2 . "</a>";
						}

						$xfieldsdata[$value[0]] = implode(", ", $value3);

						unset($temp_array);
						unset($value2);
						unset($value3);

					}

					if ($config['allow_links'] AND $value[3] == "textarea" AND function_exists('replace_links')) $xfieldsdata[$value[0]] = replace_links ( $xfieldsdata[$value[0]], $replace_links['news'] );

					if($value[3] == "image" AND $xfieldsdata[$value[0]] ) {
						$path_parts = @pathinfo($xfieldsdata[$value[0]]);

						if( $value[12] AND file_exists(ROOT_DIR . "/uploads/posts/" .$path_parts['dirname']."/thumbs/".$path_parts['basename']) ) {
							$thumb_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/thumbs/".$path_parts['basename'];
							$img_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];
						} else {
							$img_url = 	$config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];
							$thumb_url = "";
						}

						if($thumb_url) {
							$xfieldsdata[$value[0]] = "<a href=\"$img_url\" class=\"highslide\" target=\"_blank\"><img class=\"xfieldimage {$value[0]}\" src=\"$thumb_url\" alt=\"\" /></a>";
						} else $xfieldsdata[$value[0]] = "<img class=\"xfieldimage {$value[0]}\" src=\"{$img_url}\" alt=\"\" />";
					}

					if($value[3] == "image") {

						if( $xfieldsdata[$value[0]] ) {
							$tpl->copy_template = str_replace( "[xfvalue_thumb_url_{$value[0]}]", $thumb_url, $tpl->copy_template );
							$tpl->copy_template = str_replace( "[xfvalue_image_url_{$value[0]}]", $img_url, $tpl->copy_template );
						} else {
							$tpl->copy_template = str_replace( "[xfvalue_thumb_url_{$value[0]}]", "", $tpl->copy_template );
							$tpl->copy_template = str_replace( "[xfvalue_image_url_{$value[0]}]", "", $tpl->copy_template );
						}
					}

					if($value[3] == "imagegalery" AND $xfieldsdata[$value[0]] AND stripos ( $tpl->copy_template, "[xfvalue_{$value[0]}" ) !== false) {

						$fieldvalue_arr = explode(',', $xfieldsdata[$value[0]]);
						$gallery_image = array();
						$gallery_single_image = array();
						$xf_image_count = 0;
						$single_need = false;

						if(stripos ( $tpl->copy_template, "[xfvalue_{$value[0]} image=" ) !== false) $single_need = true;

						foreach ($fieldvalue_arr as $temp_value) {
							$xf_image_count ++;

							$temp_value = trim($temp_value);

							if($temp_value == "") continue;

							$path_parts = @pathinfo($temp_value);

							if( $value[12] AND file_exists(ROOT_DIR . "/uploads/posts/" .$path_parts['dirname']."/thumbs/".$path_parts['basename']) ) {
								$thumb_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/thumbs/".$path_parts['basename'];
								$img_url = $config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];
							} else {
								$img_url = 	$config['http_home_url'] . "uploads/posts/" . $path_parts['dirname']."/".$path_parts['basename'];
								$thumb_url = "";
							}

							if($thumb_url) {

								$gallery_image[] = "<li><a href=\"$img_url\" onclick=\"return hs.expand(this, { slideshowGroup: 'xf_{$row['id']}_{$value[0]}' })\" target=\"_blank\"><img src=\"{$thumb_url}\" alt=\"\" /></a></li>";
								$gallery_single_image['[xfvalue_'.$value[0].' image="'.$xf_image_count.'"]'] = "<a href=\"{$img_url}\" class=\"highslide\" target=\"_blank\"><img class=\"xfieldimage {$value[0]}\" src=\"{$thumb_url}\" alt=\"\" /></a>";

							} else {
								$gallery_image[] = "<li><img src=\"{$img_url}\" alt=\"\" /></li>";
								$gallery_single_image['[xfvalue_'.$value[0].' image="'.$xf_image_count.'"]'] = "<img class=\"xfieldimage {$value[0]}\" src=\"{$img_url}\" alt=\"\" />";
							}

						}

						if($single_need AND count($gallery_single_image) ) {
							foreach($gallery_single_image as $temp_key => $temp_value) $tpl->set( $temp_key, $temp_value);
						}

						$xfieldsdata[$value[0]] = "<ul class=\"xfieldimagegallery {$value[0]}\">".implode($gallery_image)."</ul>";
						$uniq_id = "xf_{$row['id']}_{$value[0]}";
						$onload_scripts[$uniq_id] = "hs.addSlideshow({slideshowGroup: '{$uniq_id}', interval: 4000, repeat: false, useControls: true, fixedControls: 'fit', overlayOptions: { opacity: .75, position: 'bottom center', hideOnMouseOut: true } });";

					}

					$tpl->set( "[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]] );

					if ( preg_match( "#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $tpl->copy_template, $matches ) ) {
						$count= intval($matches[1]);

						$xfieldsdata[$value[0]] = str_replace( "</p><p>", " ", $xfieldsdata[$value[0]] );
						$xfieldsdata[$value[0]] = strip_tags( $xfieldsdata[$value[0]], "<br>" );
						$xfieldsdata[$value[0]] = trim(str_replace( "<br>", " ", str_replace( "<br />", " ", str_replace( "\n", " ", str_replace( "\r", "", $xfieldsdata[$value[0]] ) ) ) ));

						if( $count AND dle_strlen( $xfieldsdata[$value[0]], $config['charset'] ) > $count ) {

							$xfieldsdata[$value[0]] = dle_substr( $xfieldsdata[$value[0]], 0, $count, $config['charset'] );

							if( ($temp_dmax = dle_strrpos( $xfieldsdata[$value[0]], ' ', $config['charset'] )) ) $xfieldsdata[$value[0]] = dle_substr( $xfieldsdata[$value[0]], 0, $temp_dmax, $config['charset'] );

						}

						$tpl->set( $matches[0], $xfieldsdata[$value[0]] );

					}
				}
			}

		}

		//$tpl->copy_template = preg_replace( "'\\[xfgiven_(.*?)\\](.*?)\\[/xfgiven_(.*?)\\]'si", "", $tpl->copy_template );

		$tpl->set( '{f-url}', $config['http_home_url'] . "uploads/files/" . $info['onserver'] );
		$_tmp = pathinfo( $info['onserver'] );
		$tpl->set( '{f-ext}', $_tmp['extension'] );
		$tpl->set( '{f-dir}', $_tmp['dirname'] );

		if ( stripos( $tpl->copy_template, "[f-ext=" ) !== false ) {
			preg_match_all( "#\\[f-ext=(.*?)\\](.*?)\\[/f-ext\\]#ies", $tpl->copy_template, $ext_matchs );
			$_len = count( $ext_matchs[0] );
			for( $x = 0; $x < $_len; $x++ ) {
				$_exts = explode( ",", $ext_matchs[1][ $x ] );
				if ( in_array( $_tmp['extension'], $_exts ) ) {
					$tpl->copy_template = str_replace( $ext_matchs[0][ $x ], $ext_matchs[2][ $x ], $tpl->copy_template );
				} else {
					$tpl->copy_template = str_replace( $ext_matchs[0][ $x ], "", $tpl->copy_template );
				}
			}
		}

		$tpl->set( '{f-name}', $info['name'] );
		$tpl->set( '{f-size}', $info['size'] );
		$tpl->set( '{f-author}', $info['author'] );
		$tpl->set( '{f-date}', date( "m.d.Y", $info['date'] ) );
		$tpl->set( '{f-news-title}', $info['title'] );
		$tpl->set( '{f-count}', $info['dcount'] );
		$tpl->set( '{f-counter}', intval( $dset['time_count'] ) );
		$news_date = $info['date'];
		$tpl->copy_template = preg_replace_callback ( "#\{f-date=(.+?)\}#i", "formdate", $tpl->copy_template );

		if ( stripos( $tpl->copy_template, "{custom" ) !== false ) {
			$tpl->copy_template = preg_replace_callback ( "#\\{custom(.+?)\\}#i", "custom_print", $tpl->copy_template );
		}

		if ( ! empty( $info['related_ids'] ) && $area != "static" ) {
			preg_match_all( "#\\{related(.*?)\\}#i", $tpl->copy_template, $rel_matchs );
			foreach( $rel_matchs[1] as $rel_id => $rel_match ) {
				if ( preg_match( "#template=['\"](.+?)['\"]#i", $rel_match, $match ) ) { $_tpl = trim( $match[1] ); }
				else { $_tpl = "relatednews"; }
				if ( preg_match( "#limit=['\"](.+?)['\"]#i", $rel_match, $match ) ) { $_limit = intval( $match[1] ); }
				else { $_limit = $config['related_number']; }
				$tpl->set( $rel_matchs[0][$rel_id], custom_print( array( 0 => "", 1 => "limit=\"{$_limit}\" template=\"{$_tpl}\" id=\"{$info['related_ids']}\"" ) ) );
			}
			$tpl->set( '[related]', "" );
			$tpl->set( '[/related]', "" );
		} else {
			$tpl->set_block( "'\\[related\\](.*?)\\[/related\\]'si", "" );
		}

		if ( $area == "static" ) {
			$full_link = $config['http_home_url'] . $info['alt_name'] . ".html";
			$tpl->set( '{f-link}', $config['http_home_url'] . 'engine/download.php?id=' . $info['id'] . "&amp;area=static" );
			$tpl->set( '{f-seo-link}', $config['http_home_url'] . 'download/' . $info['id'] . "/static" );
			$tpl->set( '{f-news-link}', $full_link );
			if ( $config['allow_alt_url'] ) {
				$tpl->set( '{f-dlink}', $config['http_home_url'] . 'download/' . $info['id'] . "&amp;area=static" );
			} else {
				$tpl->set( '{f-dlink}', $config['http_home_url'] . 'engine/download.php?id=' . $info['id'] . "&amp;area=static" );
			}
			$tpl->set_block( "'\\[static\\](.*?)\\[/static\\]'si", "\\1" );
			$tpl->set_block( "'\\[not-static\\](.*?)\\[/not-static\\]'si", "" );
		} else {
			$tpl->set( '{f-link}', $config['http_home_url'] . 'engine/download.php?id=' . $info['id'] );
			$tpl->set( '{f-seo-link}', $config['http_home_url'] . 'download/' . $info['id'] );
			$tpl->set( '{f-cat-link}', $my_cat_link );
			$tpl->set( '{f-cat-name}', $my_cat );
			$tpl->set( '{f-news-link}', $full_link );
			if ( $config['allow_alt_url'] ) {
				$tpl->set( '{f-dlink}', $config['http_home_url'] . 'download/' . $info['id'] );
			} else {
				$tpl->set( '{f-dlink}', $config['http_home_url'] . 'engine/download.php?id=' . $info['id'] );
			}
			$tpl->set_block( "'\\[static\\](.*?)\\[/static\\]'si", "" );
			$tpl->set_block( "'\\[not-static\\](.*?)\\[/not-static\\]'si", "\\1" );
		}

		if ( !defined('BANNERS') ) {
			if ( $config['allow_banner'] && $dset['show_ads'] ) include_once ENGINE_DIR . '/modules/banners.php';
		}
		foreach ( $banners as $name => $value ) {
			$tpl->copy_template = str_replace ( "{banner_" . $name . "}", $value, $tpl->copy_template );
			if ( $value ) {
				$tpl->copy_template = str_replace ( "[banner_" . $name . "]", "", $tpl->copy_template );
				$tpl->copy_template = str_replace ( "[/banner_" . $name . "]", "", $tpl->copy_template );
			}
		}

		if ( $dset['use_timer'] ) {
			$tpl->set_block( "'\\[timer\\](.*?)\\[/timer\\]'si", "\\1" );
			$tpl->set_block( "'\\[notimer\\](.*?)\\[/notimer\\]'si", "" );
		} else {
			$tpl->set_block( "'\\[timer\\](.*?)\\[/timer\\]'si", "" );
			$tpl->set_block( "'\\[notimer\\](.*?)\\[/notimer\\]'si", "\\1" );
		}

		function set_template( $text ) {
			global $config, $info, $full_link;
			return str_replace(
				array( "%sitename%", "%filename%", "%filesize%", "%fileauthor%", "%filedown%", "%pagelink%" ),
				array( $config['home_title'], $info['name'], $info['size'], $info['author'], $info['dcount'], "<a href=\"". $full_link . "\">" . $info['title'] . "</a>" ),
				$text
			);
		}

		$dset['header_title'] = set_template( $dset['header_title'] );
		$dset['name'] = set_template( $dset['name'] );
		$dset['title'] = set_template( $dset['title'] );
		$metatags['description'] = set_template( $dset['meta_desc'] );
		$metatags['keywords'] = set_template( $dset['meta_key'] );

		if ( $dset['sep_page'] == "1" ) {
			if ( $dset['dis_index'] ) $disable_index = "\n<meta name=\"robots\" content=\"noindex,nofollow\" />"; else $disable_index = "";
			$js_array[] = "engine/classes/js/jquery.js";
			$js_array[] = "engine/classes/js/jqueryui.js";
			$js_array[] = "engine/classes/js/dle_js.js";
			$js_array = build_js( $js_array, $config );
			$metatags = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset={$config['charset']}\" /><title>{$dset['title']}</title><meta name=\"description\" content=\"{$metatags['description']}\" /><meta name=\"keywords\" content=\"{$metatags['keywords']}\" />{$disable_index}{$js_array}";
			$tpl->set( '{headers}', $metatags );
			$tpl->set( '{THEME}', $config['http_home_url'] . "templates/" . $config['skin'] );
			$tpl->compile('download');
			if ( $config['files_allow'] && strpos( $tpl->result['download'], "[attachment=" ) !== false ) {
				$tpl->result['download'] = show_attach( $tpl->result['download'], $info['news_id'] );
			}
			echo $tpl->result['download'];
			die();
		} else {
			if ( $config['files_allow'] && strpos( $tpl->result['content'], "[attachment=" ) !== false ) {
				$tpl->result['content'] = show_attach( $tpl->result['content'], $info['news_id'] );
			}
			$tpl->compile('content');
		}

	} else {
		@header( "HTTP/1.0 404 Not Found" );

		$dset['header_title'] = $lang['dwn_1'];
		$dset['name'] = "";
		$dset['title'] = $lang['dwn_2'];

		msgbox( $lang['dwn_5'], "<ul><li>{$lang['dwn_1']}</li></ul>" );
	}

} else {
	$dset['header_title'] = $lang['dwn_3'];
	$dset['name'] = "";
	$dset['title'] = $lang['dwn_4'];

	msgbox( $lang['dwn_5'], "<ul><li>{$lang['dwn_3']}</li></ul>" );
}

?>