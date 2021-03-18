<?php

// 設定 CORS 權限
header("Access-Control-Allow-Origin: http://localhost:8080");
// header("Access-Control-Allow-Origin: https://fe-sp-0001-kapitan.herokuapp.com");
header('Access-Control-Allow-Methods: POST');
header("Content-Type:text/html; charset=utf-8");

// 導入 PDO 以安全連線資練庫
include("../../Lib/PDO.php");

// 接收前端 JSON 字串資料並解析
$json_string = file_get_contents("php://input");
$json_data = json_decode($json_string);

foreach ($json_data as $properity => $value) {
    if ($properity != 'projectPriceBeyond' && $value != '%%') {
        $json_data->$properity = '%' . $value . '%';
    }
}

// print json_encode($json_data);

// 執行：
$sql_query_projects = "SELECT * FROM
(SELECT * FROM
 (SELECT * FROM projects as pj 
JOIN departure_location as dl ON 
pj.FK_LOCATION_ID_for_PJ = dl.LOCATION_ID) as t1 
JOIN category as cg ON t1.FK_CATEGORY_ID_for_PJ = cg.CATEGORY_ID) as t2 
WHERE t2.PROJECT_ID LIKE ? && t2.PROJECT_NAME LIKE ? && t2.PROJECT_STATUS LIKE ? 
&& t2.CATEGORY_NAME LIKE ? && t2.LOCATION_NAME LIKE ? && t2.PROJECT_ORIGINAL_PRICE_PER_PERSON <= ?";

$statement_query_projects = $pdo->prepare($sql_query_projects);
$statement_query_projects->bindParam(1, $json_data->projectID);
$statement_query_projects->bindParam(2, $json_data->projectName);
$statement_query_projects->bindParam(3, $json_data->projectStatus);
$statement_query_projects->bindParam(4, $json_data->projectCategory);
$statement_query_projects->bindParam(5, $json_data->projectDepartureLocation);
$statement_query_projects->bindParam(6, $json_data->projectPriceBeyond);
$statement_query_projects->execute();

$query_result = $statement_query_projects->fetchAll(PDO::FETCH_ASSOC);

if ($query_result == null) {
    $query_result = (array)[];
}

print json_encode($query_result);
