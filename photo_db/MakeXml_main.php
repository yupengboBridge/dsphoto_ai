<?php
//
//ini_set( "display_errors", "Off");
error_reporting(E_ALL);

ini_set("display_errors","On");
/*
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_config.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_3letter_p_hei.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_a_p_hatsu_url.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_ab_destination_list.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_ab_destination_list_full.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_ab_hotel_list.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_ab_hotel_list_name.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_ab_hotel_list_full.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_ab_tour_carr_list.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_ab_tour_carr_list01.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_ab_tour_carr_list_easy.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_air_carr_list.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_air_carr_list01.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_air_carr_list_full.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_air_destination_list.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_d_hatsu_dest_url.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_d_hatsu_prefecture_url.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_d_p_dest_url.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_d_p_hatsu_url.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_d_p_hatsu_url_v3.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_d_p_prefecture_url.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_dome_destination_list.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_dome_destination_list_full.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_dome_tour_carr_list.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_i_hatsu_country_url.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_i_p_country_url.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_i_p_hatsu_url.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_i_p_hatsu_url_v3.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_p_city.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_p_city_new.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_p_room_type.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/makexml_a_ap_city.php');
*/
if(!empty($argv[1]) && $argv[1] == 'test'){
	$xRootPath = './';
}
else{
	$xRootPath = dirname(__FILE__) . '/';
}
require_once($xRootPath . 'makexml_config.php');
require_once($xRootPath . 'makexml_3letter_p_hei.php');
require_once($xRootPath . 'makexml_a_p_hatsu_url.php');
require_once($xRootPath . 'makexml_ab_destination_list.php');
require_once($xRootPath . 'makexml_ab_destination_list_full.php');
require_once($xRootPath . 'makexml_ab_hotel_list.php');
require_once($xRootPath . 'makexml_ab_hotel_list_name.php');
require_once($xRootPath . 'makexml_ab_hotel_list_full.php');
require_once($xRootPath . 'makexml_ab_tour_carr_list.php');
require_once($xRootPath . 'makexml_ab_tour_carr_list01.php');
//require_once($xRootPath . 'makexml_ab_tour_carr_list_full.php');
require_once($xRootPath . 'makexml_ab_tour_carr_list_easy.php');
//require_once($xRootPath . 'makexml_air_carr_list.php');
//require_once($xRootPath . 'makexml_air_carr_list01.php');
//require_once($xRootPath . 'makexml_air_destination_list.php');
require_once($xRootPath . 'makexml_d_hatsu_dest_url.php');
require_once($xRootPath . 'makexml_d_hatsu_prefecture_url.php');
require_once($xRootPath . 'makexml_d_p_dest_url.php');
//require_once($xRootPath . 'makexml_d_p_hatsu_url.php');
//equire_once($xRootPath . 'makexml_d_p_hatsu_url_v2.php');
require_once($xRootPath . 'makexml_d_p_hatsu_url_v3.php');
require_once($xRootPath . 'makexml_d_p_prefecture_url.php');
require_once($xRootPath . 'makexml_dome_destination_list.php');
require_once($xRootPath . 'makexml_dome_destination_list_full.php');
require_once($xRootPath . 'makexml_dome_tour_carr_list.php');
require_once($xRootPath . 'makexml_i_hatsu_country_url.php');
require_once($xRootPath . 'makexml_i_p_country_url.php');
//require_once($xRootPath . 'makexml_i_p_hatsu_url.php');
//require_once($xRootPath . 'makexml_i_p_hatsu_url_v2.php');
require_once($xRootPath . 'makexml_i_p_hatsu_url_v3.php');
require_once($xRootPath . 'makexml_p_city.php');
require_once($xRootPath . 'makexml_p_city_new.php');
require_once($xRootPath . 'makexml_p_room_type.php');
require_once($xRootPath . 'makexml_a_ap_city.php');
require_once($xRootPath . 'makexml_dome_tour_boarding_place_list.php');//バス乗車地

Makexml_main();

function Makexml_main()
{
	global $csvdir;


	$dossier = opendir($csvdir);
	$file_name = "";
	while ($Fichier = readdir($dossier))
	{
		if ($Fichier != "." && $Fichier != ".." && $Fichier != "Thumbs.db")
		{
			$file_name = strtolower($Fichier);
			if (!is_dir ($csvdir.$file_name))
			{
				//print $csvdir.$file_name."\r\n";
				//ＣＳＶファイル名よ割り振り
				switch($file_name)
				{
					case "3letter_p_hei.csv":
						MakeXml_3letter_p_hei("3letter_p_hei.csv");
						break;
					case "a_p_hatsu_url.csv":
						MakeXml_a_p_hatsu_url("a_p_hatsu_url.csv");
						break;
					case "ab_destination_list.csv":
						MakeXml_ab_destination_list("ab_destination_list.csv");
						MakeXml_ab_destination_list_full("ab_destination_list.csv");
						break;
					case "ab_hotel_list.csv":
						MakeXml_ab_h_list_name("ab_hotel_list.csv");
						MakeXml_ab_hotel_list("ab_hotel_list.csv");
						MakeXml_ab_h_list_full("ab_hotel_list.csv");
						break;
					case "ab_tour_carr_list.csv":
						MakeXml_ab_tour_carr_list("ab_tour_carr_list.csv");
						MakeXml_ab_tour_carr_list01("ab_tour_carr_list.csv");
						MakeXml_ab_tour_carr_list_easy("ab_tour_carr_list.csv");
						break;
//					case "air_carr_list.csv":
//						MakeXml_air_carr_list("air_carr_list.csv");
//						MakeXml_air_carr_list01("air_carr_list.csv");
//						MakeXml_air_carr_list_full("air_carr_list.csv");
//						break;
//					case "air_destination_list.csv":
//						MakeXml_air_destination_list("air_destination_list.csv");
//						break;
					case "d_hatsu_dest_url.csv":
						MakeXml_d_hatsu_dest_url("d_hatsu_dest_url.csv");
						break;
//					case "d_p_hatsu_url.csv":
//						MakeXml_d_p_hatsu_url("d_p_hatsu_url.csv");
//						break;
//					case "d_p_hatsu_url_v2.csv":
//						MakeXml_d_p_hatsu_url_v2("d_p_hatsu_url_v2.csv");
//						break;
//					case "i_p_hatsu_url_v2.csv":
//						MakeXml_i_p_hatsu_url_v2("i_p_hatsu_url_v2.csv");
//						break;
					case "d_p_hatsu_url_v3.csv":
						MakeXml_d_p_hatsu_url_v3("d_p_hatsu_url_v3.csv");
						break;
					case "i_p_hatsu_url_v3.csv":
						MakeXml_i_p_hatsu_url_v3("i_p_hatsu_url_v3.csv");
						break;
					case "dome_destination_list.csv":
						MakeXml_dome_destination_list("dome_destination_list.csv");
						MakeXml_dome_destination_list_full("dome_destination_list.csv");
						break;
					case "dome_tour_carr_list.csv":
						MakeXml_dome_tour_carr_list("dome_tour_carr_list.csv");
						break;
					case "i_hatsu_country_url.csv":
						MakeXml_i_hatsu_country_url("i_hatsu_country_url.csv");
						break;
					case "i_p_country_url.csv":
						MakeXml_i_p_country_url("i_p_country_url.csv");
						break;
//					case "i_p_hatsu_url.csv":
//						MakeXml_i_p_hatsu_url("i_p_hatsu_url.csv");
//						break;
					case "d_hatsu_prefecture_url.csv":
						MakeXml_d_hatsu_prefe_url("d_hatsu_prefecture_url.csv");
						break;
					case "d_p_prefecture_url.csv":
						MakeXml_d_p_prefecture_url("d_p_prefecture_url.csv");
						break;
					case "p_city.csv":
						MakeXml_p_city("p_city.csv");
					case "p_room_type.csv":
						MakeXml_p_room_typel("p_room_type.csv");
						break;
					case "d_p_dest_url.csv":
						MakeXml_d_p_dest_url("d_p_dest_url.csv");
						break;
					case "p_city_new.csv":
						MakeXml_p_city_new("p_city_new.csv");
						break;
					case "a_ap_city.csv":
						MakeXml_a_ap_city("a_ap_city.csv");
						break;
					case "dome_tour_boarding_place_list.csv":
						MakeXml_dome_tour_boarding_place_list("dome_tour_boarding_place_list.csv");
						break;
					default:break;
				}
			}
		}
	}
}



?>