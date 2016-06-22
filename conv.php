<?php
require_once("vendor/autoload.php");
require_once("config.php");
$daten = array();
$geo = array();
$geo["type"] = "FeatureCollection";
$geo["features"] = array();
$con = pg_connect("host=".$db_con["host"]." dbname=".$db_con["db"]." user=".$db_con["username"]." password=".$db_con["password"]." port=".$db_con["port"]) or die("Postgres Error");
$sql = "SELECT ways.id as way_id, nodes.id as node_id, ST_X(nodes.geom) as long, ST_Y(nodes.geom) as lat, ways.tags FROM nodes JOIN way_nodes ON way_nodes.node_id = nodes.id JOIN ways ON way_nodes.way_id = ways.id WHERE ways.tags ? 'historic'";
$r = pg_query($con, $sql) or die(pg_last_error());
while($row = pg_fetch_assoc($r)){
  $row["tags"] = json_decode('{' . str_replace('"=>"', '":"', $row["tags"]) . '}', true);
  $daten[$row["way_id"]][] = $row;
}
foreach($daten as $r){
  $tmp = array();
  $tmp["type"] = "Feature";
  $tmp["geometry"] = array();
  $tmp["geometry"]["type"] = "MultiPoint";
  $tmp["geometry"]["coordinates"] = array();
  $tmp["properties"] = $r[0]["tags"];
  foreach($r as $k){
    $t = array();
    if(isset($k["lat"])){
      $t[0] = $k["lat"];
      $t[1] = $k["long"];
    }
    $tmp["geometry"]["coordinates"][] = $t;
  }
  $geo["features"][] = $tmp;
}
echo json_encode($geo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
