<?php 
# test.php

$api_key = '382d6ccc227c2f8e98360994d00020ec';
$url = 'https://api.openweathermap.org/data/2.5/weather?id=2172797&units=metric&APPID=' . $api_key;
$api_response = file_get_contents( $url );

$weather = json_decode($api_response);


   $wind_direction = $weather->wind->deg;
   if ( $wind_direction > 350 || $wind_direction < 10 ) {
         $win_dir_desc = "N";
   } elseif ( $wind_direction >= 10 && $wind_direction <= 35 ) {
         $win_dir_desc = "NNE";
   } elseif ( $wind_direction > 35 && $wind_direction <= 55 ) {
         $win_dir_desc = "NE";
   } elseif ( $wind_direction > 55 && $wind_direction <= 80 ) {
         $win_dir_desc = "ENE";
   } elseif ( $wind_direction > 80 && $wind_direction <= 100 ) {
      $win_dir_desc = "E";
   } elseif ( $wind_direction > 100 && $wind_direction <= 125 ) {
      $win_dir_desc = "ESE";
   } elseif ( $wind_direction > 125 && $wind_direction <= 145 ) {
      $win_dir_desc = "SE";
   } elseif ( $wind_direction > 145 && $wind_direction <= 170 ) {
      $win_dir_desc = "SSE";
   } elseif ( $wind_direction > 170 && $wind_direction <= 190 ) {
      $win_dir_desc = "S";
   } elseif ( $wind_direction > 190 && $wind_direction <= 215 ) {
      $win_dir_desc = "SSW";
   } elseif ( $wind_direction > 215 && $wind_direction <= 235 ) {
      $win_dir_desc = "SW";
   } elseif ( $wind_direction > 235 && $wind_direction <= 260 ) {
      $win_dir_desc = "WSW";
   } elseif ( $wind_direction > 260 && $wind_direction <= 280 ) {
      $win_dir_desc = "W";
   } elseif ( $wind_direction > 280 && $wind_direction <= 305 ) {
      $win_dir_desc = "WNW";
   } elseif ( $wind_direction > 305 && $wind_direction <= 325 ) {
      $win_dir_desc = "NW";
   } elseif ( $wind_direction > 325 && $wind_direction <= 350 ) {
      $win_dir_desc = "NNW";
   }

   $weather->wind->dir = $win_dir_desc;

   $weather->wind->knots = number_format( $weather->wind->speed / 1.852 , 1 );

var_dump($weather);


?>