<?php
function getDistance($latitude1, $longitude1, $latitude2, $longitude2) {  
    $earth_radius = 6371;  // 킬로미터, For miles, divide km by 1.609344


    $dLat = deg2rad($latitude2 - $latitude1);  
    $dLon = deg2rad($longitude2 - $longitude1);  

    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);  
    $c = 2 * asin(sqrt($a));  
    $d = $earth_radius * $c;  

    return $d;  
} 

/*
//To search by kilometers instead of miles, replace 3959 with 6371.
CREATE FUNCTION FN_DISTANCE (
  src_lat DECIMAL(9,6), src_lon DECIMAL(9,6),
  dst_lat DECIMAL(9,6), dst_lon DECIMAL(9,6)
) RETURNS DECIMAL(6,2) DETERMINISTIC
BEGIN
  SET @dist := 6371 * 2 * ASIN(SQRT(
      POWER(SIN((src_lat - ABS(dst_lat)) * PI()/180 / 2), 2) +
      COS(src_lat * PI()/180) *
      COS(ABS(dst_lat) * PI()/180) *
      POWER(SIN((src_lon - dst_lon) * PI()/180 / 2), 2)
    ));
  RETURN @dist;
END


*/

?>