<?php


$baseURL = 'https://eosriskadmindev.azurewebsites.net/api/';

function GenerateToken() {
    
global $baseURL;

    $curl = curl_init();
    curl_setopt_array($curl, array(
    
      CURLOPT_URL => "$baseURL/Token/GenerateToken",
    
      CURLOPT_RETURNTRANSFER => true,
    
      CURLOPT_ENCODING => '',
    
      CURLOPT_MAXREDIRS => 10,
    
      CURLOPT_TIMEOUT => 0,
    
      CURLOPT_FOLLOWLOCATION => true,
    
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    
      CURLOPT_CUSTOMREQUEST => 'POST',
    
      CURLOPT_HTTPHEADER => array(
    
        'Content-Type: application/json',
    
      ),
    
    ));
    
    
    
    $response = curl_exec($curl);
    
    
    
    curl_close($curl);
    
    
    
    $data = json_decode($response, true);
    
    
    
    $tokenValue = $data['token'];
    
    
    
    return $tokenValue;
    
    
    
}


function MakeAPICall($url, $method = 'GET', $params = array()) {
  
  global $baseURL;

  $token = GenerateToken();

  $curl = curl_init();

  $headers = array(
      'Content-Type: application/json',
      "Authorization: Bearer $token"
  );

  if ($method === 'GET' && !empty($params)) {
      $url .= '?' . http_build_query($params);
  }

  curl_setopt_array($curl, array(
      CURLOPT_URL => "$baseURL/$url",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => $headers,
  ));

  $response = curl_exec($curl);

  curl_close($curl);

  echo $response;
}

function GetCountries() {

  MakeAPICall('DropDown/GetCountryList');
}

function GetCities($stateId) {

  MakeAPICall('DropDown/GetCityListByStateId', 'GET', array('stateId' => $stateId));
}

function GetState($countryId) {
  MakeAPICall('DropDown/GetStateListByCountryId', 'GET', array('countryId' => $countryId));
}

function GetExpList() {
  MakeAPICall('DropDown/GetYearOfExpirenceList');
}


function GetEduList() {

  MakeAPICall('DropDown/GetHighestLevelOfEducList');
  
}

function GetSectorList() {

  MakeAPICall('DropDown/GetSectorList');
  
}

function GetSkillList() {

  MakeAPICall('DropDown/GetSkillList');
  
}




if (isset($_POST['countryid']) ) {
  $countryId = $_POST['countryid'];
}

if (isset($_POST['action']) ) {
  $action = $_POST['action'];
}

if (isset($_POST['stateId']) ) {
  $stateId = $_POST['stateId'];
}

    
switch ($action) {

  case 'GetCountries': GetCountries(); break;
  
  case 'GetEduList': GetEduList(); break;
  
  case 'GetSectorList': GetSectorList();  break;
  
  case 'GetSkillList': GetSkillList(); break;
  
  case 'GetExpList':  GetExpList(); break;
  
  case 'GetCities': GetCities($stateId); break;
  
  case 'GetLanguages': GetLanguages(); break;
  
  case 'GetState': GetState($countryId); break;
  
  default: break;
}