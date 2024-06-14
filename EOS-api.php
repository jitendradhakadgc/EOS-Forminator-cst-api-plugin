<?php

/**
 * Plugin Name: Custom Form API
 * Description: Send Forminator form data to EOS CMS API.
 * Version: 1.0
 * Author: Critical Media
 */



session_start();

function enqueue_custom_js() {
    // Enqueue JavaScript file
   // wp_enqueue_script('eos-cms-js', plugin_dir_url(__FILE__) . 'eos-cms.js', array('jquery'), null, true);
	$random_version = mt_rand();

	// Enqueue the script with the random version number
	wp_enqueue_script('eos-cms-js', plugin_dir_url(__FILE__) . 'eos-cms.js', array('jquery'), $random_version, true);
	
	wp_localize_script( 'eos-cms-js', 'myAjax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'my-ajax-nonce' )
    ));
}

add_action('wp_enqueue_scripts', 'enqueue_custom_js');


function ensureJsonFormat($input) {
    if (is_string($input) && (json_decode($input) !== null || $input === 'null')) {
        $decoded = json_decode($input);
        if (is_array($decoded)) {
            return implode(', ', $decoded); 
        } else {
            return $input; 
        }
    } else {
        $jsonData = json_encode($input);
        if ($jsonData !== false) {
            return $jsonData; 
        } else {
            return '{"error": "Invalid input"}';
        }
    }
}



add_action('forminator_custom_form_submit_before_set_fields', 'custom_form_submit', 10, 3);


function custom_form_submit($entry, $form_id, $field_data_array){ 
		 if ($form_id == 626) {

            $form_id = 626;

            $field_values = array();
            

            foreach ($field_data_array as $field_info) {

                $field_name = $field_info['name'];

                $field_value = $field_info['value'];

            

                if (is_array($field_value)) {

                    $field_value = json_encode(flattenArray($field_value));

                }

                $field_values[$field_name] = $field_value;
            }

            $userName = json_decode($field_values['name-1'], true);

            $userEmail = $field_values['email-1'];

            $userPhone = $field_values['phone-1'];

            $userGender = ($field_values['select-6'] === 'Male') ? 1 : 2;

            $rawDOB = $field_values['date-1'];

            $userDOB = date('Y-m-d', strtotime($rawDOB));

            $userAddress = json_decode($field_values['address-1'], true);

            $userEducation = $field_values['select-1'];

            $userLangs = $field_values['checkbox-4'];

            $userExp = $field_values['select-9'];

            $countriesWorked = $field_values['checkbox-2'];

            $coverLetter = $field_values['textarea-2'];

            $sectorId = $field_values['select-10'];

            $userFirstName = $userName['last-name'];

            $userLastName = $userName['middle-name'];

            $userStreetAddress = $userAddress['street_address'];

            $userAddress2 = $userAddress['address_line'];

            $userCity = $field_values['text-2'];

            $userState = $field_values['text-1'];

            $userZip = $userAddress['zip'];

            $userCountry = $field_values['select-7'];

            $userSkills = $field_values['checkbox-3'];

            $userProfileImageData = $field_values['upload-3'];

            $userProfileImageData = !empty($userProfileImageData) ? json_decode($userProfileImageData, true) : false;

            $userProfileImage = $userProfileImageData ? $userProfileImageData['file_path'] : false;

            $userCVFile = $field_values['upload-2'];

            $userCVFile = !empty($userCVFile) ? json_decode($userCVFile, true) : false;

            $userCVFile = $userCVFile ? $userCVFile['file_path'] : false;
           
            $jsonUserSkills = ensureJsonFormat($userSkills);

            $userLang = ensureJsonFormat($userLangs);

            $countriesWorked = ensureJsonFormat($countriesWorked);

             // File handdling

            $entries = Forminator_API::get_entries($form_id);

            $entry_id = $entries[0]->entry_id;



            // API POSTING 
			
			$cvfilename = $userCVFile ? basename($userCVFile) : '';
			
			$imagefilename = $userProfileImage ? basename($userProfileImage) : '';

            $token = GenerateToken();

            $api_url = 'https://eosriskadmindev.azurewebsites.net/api/Customer/Create';
			
			$fileCV = $userCVFile ? file_get_contents($userCVFile) : false;
			$base64EncodedCV = $fileCV ? base64_encode($fileCV) : '';
			
			$fileImage = $userProfileImage ? file_get_contents($userProfileImage) : false;
			$base64EncodedImage = $fileImage ? base64_encode($fileImage) : '';

            $post_data = array(

                'FirstName' => $userFirstName,

                'LastName' => $userLastName,

                'Gender' => $userGender,

                'DOB' => $userDOB,

                'ExperienceDetail' => $userExp,

                'Country' => $userCountry,

                'State' => $userState,

                'CountinentId' => '',

                'SectorName' => $sectorId,

                'Address1' => $userStreetAddress,

                'Address2' => $userAddress2,

                'PinCode' => $userZip,

                'City' => $userCity,

                'Phone' => $userPhone,

                'LanguageIds' => $userLang,

                'Email' => $userEmail,

                'SkillIds' => $jsonUserSkills,

                'CountriesWorkedIn' => $countriesWorked,

                'HighestLevelOfEducIdName' => $userEducation,

				'Cv' => $cvfilename,
				
				'CVBase64' => $base64EncodedCV,
				
				'ProfileImage' => $imagefilename,

                'ImageBase64' => $base64EncodedImage,
				
				'StatusId' => 1,
				
				'CoverLetter' => $coverLetter

            );
			update_option('gc_post_data', $post_data);
            $curl = curl_init();

            curl_setopt_array($curl, array(

                CURLOPT_URL => $api_url,

                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_ENCODING => '',

                CURLOPT_MAXREDIRS => 10,

                CURLOPT_TIMEOUT => 0,

                CURLOPT_FOLLOWLOCATION => true,

                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

                CURLOPT_CUSTOMREQUEST => 'POST',

                CURLOPT_POSTFIELDS => $post_data,

                CURLOPT_HTTPHEADER => array(
                    'Content-Type: multipart/form-data',
                    "Authorization: Bearer $token",
                ),

            ));
            

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                $error_message = curl_error($curl);
              
            } else {
            }
            curl_close($curl);
           


        } elseif ($form_id == 797) {

            $field_values = array();

            $token = GenerateToken();
		

            foreach ($field_data_array as $field_info) {

                $field_name = $field_info['name'];

                $field_value = $field_info['value'];

            

                if (is_array($field_value)) {

                    $field_value = json_encode(flattenArray($field_value));

                }

                $field_values[$field_name] = $field_value;
            }


            $fullName = isset($field_values['name-1']) ? $field_values['name-1'] : '';
            $companyName = isset($field_values['text-1']) ? $field_values['text-1'] : '';
            $companyEmail = isset($field_values['email-1']) ? $field_values['email-1'] : '';
                        

            $curl = curl_init();
            
            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://eosriskadmindev.azurewebsites.net/api/Provider/Create',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => array('Email' => "$companyEmail",'CompanyName' => "$companyName",'ContactName' => "$fullName"),
              CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $token"
              ),
            ));
            
            $response = curl_exec($curl);
            
            curl_close($curl);

        }

        return $field_data_array;
}

// Function to recursively flatten an array

function flattenArray($array) {

    $result = array();

    foreach ($array as $key => $value) {

        if (is_array($value)) {

            $result = array_merge($result, flattenArray($value));

        } else {

            $result[$key] = $value;

        }

    }

    return $result;

}


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
  return $response;
}

// Fetching data for user details
function fetchDataAndDecode($endpoint, $method = 'GET', $params = null) {
    $data = MakeAPICall($endpoint, $method, $params);
    return json_decode($data, true);
}

function findItemById($data, $ids, $idKey = 'countryId', $nameKey = 'name') {
    $foundItems = [];

    if (!is_array($ids)) {
        $ids = [$ids];
    }

    foreach ($ids as $id) {
        foreach ($data as $item) {
            if ($item[$idKey] == $id) {
                $foundItems[] = $item[$nameKey];
                break;
            }
        }
    }
    
    return implode(', ', $foundItems);
}

add_filter( 'forminator_custom_form_after_save_entry', 'updateFormEntry', 10, 2);


function updateFormEntry($form_id, $field_data_array){ 

    $log_data = "\n\n\n=========\n\n\n";

    $entry_data = $_SESSION['EntryData'];

    // id
    $userCountry = $entry_data['CountryId'];
    $userState = $entry_data['State'];
    $userCity = $entry_data['CityId'];
    $userEducation = $entry_data['HighestLevelOfEducId'];
    $userLanguages = explode(',', $entry_data['LanguageIds']);
    $userExperiance = $entry_data['Experience'];
    $userSector = $entry_data['SectorId'];
    $userSkills = explode(',', $entry_data['SkillIds']);
    $countriesWorkedin = explode(',', $entry_data['CountryIds']);


    // Data
    $data = [
        'Country' => fetchDataAndDecode('DropDown/GetCountryList'),
        'State' => fetchDataAndDecode('DropDown/GetStateListByCountryId', 'GET', ['countryId' => $entry_data['CountryId']]),
        'CityId' => fetchDataAndDecode('DropDown/GetCityListByStateId', 'GET', ['stateId' => $entry_data['State']]),
        'HighestLevelOfEducId' => fetchDataAndDecode('DropDown/GetHighestLevelOfEducList'),
        'LanguageIds' => fetchDataAndDecode('DropDown/GetLanguageList'),
        'Experience' => fetchDataAndDecode('DropDown/GetYearOfExpirenceList'),
        'SectorId' => fetchDataAndDecode('DropDown/GetSectorList'),
        'SkillIds' => fetchDataAndDecode('DropDown/GetSkillList'),
        'CountryIds' => fetchDataAndDecode('DropDown/GetCountryList'),
    ];

    // Keys
    $keys = [
        'Country' => 'countryId',
        'State' => 'value',
        'CityId' => 'value',
        'HighestLevelOfEducId' => 'value',
        'LanguageIds' => 'languageId',
        'Experience' => 'value',
        'SectorId' => 'value',
        'SkillIds' => 'value',
        'CountryIds' => 'value',
    ];

    $userCountry = findItemById($data['Country'], $userCountry, $keys['Country'], 'name');
    $userState = findItemById($data['State'], $userState, $keys['State'], 'text');
    $userCity = findItemById($data['CityId'], $userCity, $keys['CityId'], 'text');
    $userEducation = findItemById($data['HighestLevelOfEducId'], $userEducation, $keys['HighestLevelOfEducId'], 'text');
    $userLanguages = findItemById($data['LanguageIds'], $userLanguages, $keys['LanguageIds'], 'name');
    $userExperiance = findItemById($data['Experience'], $userExperiance, $keys['Experience'], 'text');
    $userSector = findItemById($data['SectorId'], $userSector, $keys['SectorId'], 'text');
    $userSkills = findItemById($data['SkillIds'], $userSkills, $keys['SkillIds'], 'text');
    $countriesWorkedin = findItemById($data['CountryIds'], $countriesWorkedin, $keys['Country'], 'name');

    $entries = Forminator_API::get_entries($form_id);

    $entry_id = $entries[0]->entry_id;

    $entry_meta= array(
        array(
            'name' => 'select-7', //country
            'value' => "$userCountry"
        ),
        array(
            'name' => 'select-11', //state
            'value' => "$userState"
        ),
        array(
            'name' => 'select-8', //city
            'value' => "$userCity"
        ),
        array(
            'name' => 'select-1', //education
            'value' => "$userEducation"
        ),
        array(
            'name' => 'checkbox-4', //language spoken
            'value' => "$userLanguages"
        ),
        array(
            'name' => 'select-9', //experiance
            'value' => "$userExperiance"
        ),
        array(
            'name' => 'select-10', //sector
            'value' => "$userSector"
        ),
        array(
            'name' => 'checkbox-3', //skills
            'value' => "$userSkills"
        ),
        array(
            'name' => 'checkbox-2', //countries worked in
            'value' => "$countriesWorkedin"
        ),
    );

    Forminator_API::update_form_entry($form_id, $entry_id, $entry_meta);


    $log_file = plugin_dir_path(__FILE__) . 'log.txt';

    
            $file_handle = fopen($log_file, 'a');


            if ($file_handle) {

                fwrite($file_handle, $log_data);

                fclose($file_handle);

            } else {


   }

}

add_action( 'wp_ajax_get_language_background', 'get_language_background' );
add_action( 'wp_ajax_nopriv_get_language_background', 'get_language_background' );
function get_language_background() {
    // Check the nonce for security
    check_ajax_referer( 'my-ajax-nonce', 'nonce' );
	$url ='DropDown/GetLanguageList';
    // Check if the post_id is set
    if ( isset($_POST['post_id']) ) {
        $post_id = intval($_POST['post_id']);
        
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
		$data = json_decode($response, true);
		$form_meta = get_post_meta(626, 'forminator_form_meta', true);
		$new_options = [];

		// Iterate through the array to extract name and code
		foreach ($data as $item) {
			// Create an option array for each language
			$option = [
				'label' => $item['name'],
				'value' => $item['name'],
				'limit' => '',
				'key' => $item['languageId'],
				'error' => '',
				'default' => ''
			];

			// Add the option to the new options array
			$new_options[] = $option;
		}
		// Check if form_meta contains 'fields'
		if (isset($form_meta['fields'])) {
			// Add options to the select field
			addOptionsToField($form_meta['fields'], 'checkbox-4', $new_options);

			// Output the updated data structure

			$form_meta = update_post_meta(626, 'forminator_form_meta', $form_meta);

		} else {
			echo 'Fields not found in form metadata.';
		}
  	
    } else {
        wp_send_json_error('Post ID not set.');
    }
	echo $response;	
	exit;
}


add_action( 'wp_ajax_get_country_background', 'get_country_background' );
add_action( 'wp_ajax_nopriv_get_country_background', 'get_country_background' );
/**
 * Handles the AJAX request to fetch language options and update the form meta.
 */
function get_country_background() {
    // Check the nonce for security
    check_ajax_referer( 'my-ajax-nonce', 'nonce' );
	$url ='DropDown/GetCountryList';
    // Check if the post_id is set
    if ( isset($_POST['post_id']) ) {
        $post_id = intval($_POST['post_id']);
        
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
		$data = json_decode($response, true);
		$form_meta = get_post_meta(626, 'forminator_form_meta', true);
		$new_options = [];

		// Iterate through the array to extract name and code
		foreach ($data as $item) {
			// Create an option array for each language
			$option = [
				'label' => $item['name'],
				'value' => $item['name'],
				'limit' => '',
				'key' => $item['countryId'],
				'error' => '',
				'default' => ''
			];

			// Add the option to the new options array
			$new_options[] = $option;
		}
		// Check if form_meta contains 'fields'
		if (isset($form_meta['fields'])) {
			// Add options to the select field
			addOptionsToField($form_meta['fields'], 'checkbox-2', $new_options);

			// Output the updated data structure

			$form_meta = update_post_meta(626, 'forminator_form_meta', $form_meta);

		} else {
			echo 'Fields not found in form metadata.';
		}
  	
    } else {
        wp_send_json_error('Post ID not set.');
    }
	echo $response;	
	exit;
}



/**
 * Registers AJAX actions for authenticated and non-authenticated users.
 */
add_action( 'wp_ajax_get_sector_background', 'get_sector_background' );
add_action( 'wp_ajax_nopriv_get_sector_background', 'get_sector_background' );
/**
 * Handles the AJAX request to fetch language options and update the form meta.
 */
function get_sector_background() {
    // Check the nonce for security
    check_ajax_referer( 'my-ajax-nonce', 'nonce' );
	$url ='DropDown/GetSectorList';
    // Check if the post_id is set
    if ( isset($_POST['post_id']) ) {
        $post_id = intval($_POST['post_id']);
        
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
		$data = json_decode($response, true);
		$form_meta = get_post_meta(626, 'forminator_form_meta', true);
		$new_options = [];

		// Iterate through the array to extract name and code
		foreach ($data as $item) {
			// Create an option array for each language
			$option = [
				'label' => $item['text'],
				'value' => $item['text'],
				'limit' => '',
				'key' => $item['value'],
				'error' => '',
				'default' => ''
			];

			// Add the option to the new options array
			$new_options[] = $option;
		}
		// Check if form_meta contains 'fields'
		if (isset($form_meta['fields'])) {
			// Add options to the select field
			addOptionsToField($form_meta['fields'], 'select-10', $new_options);

			// Output the updated data structure

			$form_meta = update_post_meta(626, 'forminator_form_meta', $form_meta);

		} else {
			echo 'Fields not found in form metadata.';
		}
  	
    } else {
        wp_send_json_error('Post ID not set.');
    }
	echo $response;	
	exit;
}
// Function to add options to a specific field
function addOptionsToField(&$fields, $fieldId, $options) {
    foreach ($fields as &$field) {
        if ($field['id'] === $fieldId) {
            $field['options'] = $options;
            break;
        }
    }
}