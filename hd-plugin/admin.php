<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

function health_diagnosis_add_admin_menu()
{
    add_menu_page(
        __('AI Detection', 'health-diagnosis-plugin'),
        __('AI Detection', 'health-diagnosis-plugin'),
        'manage_options',
        'health-section',
        'health_diagnosis_admin_page',
        'dashicons-heart',
        30
    );
}
add_action('admin_menu', 'health_diagnosis_add_admin_menu');

function health_diagnosis_admin_page()
{
    include_once plugin_dir_path(__FILE__) . 'app_vue.php';
    exit;
}




function register_add_plant_endpoint()
{
    register_rest_route('wphd/v2', '/add-plant', array(
        'methods' => 'POST',
        'callback' => 'add_new_plant',
        'permission_callback' => '__return_true'

    ));
}
add_action('rest_api_init', 'register_add_plant_endpoint');
function add_new_plant($request)
{
    global $wpdb;

    // Check if user is authorized (optional)
    // if (!current_user_can('edit_posts')) {
    //     return new WP_Error('unauthorized', __('You are not authorized to add plants.'), array('status' => 401));
    // }

    // $name = sanitize_text_field($request->get_param('name'));
    $image = $_FILES['image'];
    $status = filter_var($request->get_param('status'), FILTER_VALIDATE_BOOLEAN);
    $language_codes = json_decode($request->get_param('language_codes'));
    $translations = json_decode($request->get_param('translations'));


    // Validate required fields
    if (empty($language_codes) || empty($translations) || empty($image['tmp_name'])) {
        return new WP_Error('invalid_data', __('Please provide all required fields.'), array('status' => 400));
    }

    // Upload image
    $upload = wp_upload_bits($image['name'], null, file_get_contents($image['tmp_name']));
    if (!$upload['error']) {
        $image_path = $upload['file'];
        $image_url = $upload['url'];
    } else {
        return new WP_Error('upload_failed', __('Failed to upload image.'), array('status' => 500));
    }

    // Insert data into plant_ai table
    $table_name_plant = $wpdb->prefix . 'plant_ai';
    $insert_result = $wpdb->insert(
        $table_name_plant,
        array(
            'picture' => $image_path,
            'status' => $status,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ),
        array('%s', '%d', '%s', '%s')
    );

    if ($insert_result === false) {
        return new WP_Error('insert_failed', __('Failed to add plant.'), array('status' => 500));
    }

    // Get the ID of the inserted plant
    $plant_id = $wpdb->insert_id;

    // Insert plant name translations
    $table_name_translations = $wpdb->prefix . 'translations';

    foreach ($language_codes as $index => $language_code) {
        $wpdb->insert(
            $table_name_translations,
            array(
                'language_code' => $language_code,
                'translated_text' => $translations[$index],
                'entity_type' => 'plant',
                'entity_id' => $plant_id,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s')
        );
    }


    return rest_ensure_response(array('id' => $plant_id, 'image' => $image_url, "lang" => $language_codes));
}


function register_add_disease_endpoint()
{
    register_rest_route('wphd/v2', '/add-disease', array(
        'methods' => 'POST',
        'callback' => 'add_new_disease',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_add_disease_endpoint');

function add_new_disease($request)
{
    global $wpdb;


    // Retrieve parameters from the request
    $plant_id = intval($request->get_param('plant_id'));
    $language_codes = json_decode($request->get_param('language_codes'));
    $translations_name = json_decode($request->get_param('translations_name'));
    $translations_description = json_decode($request->get_param('translations_description'));
    $translations_health_condition = json_decode($request->get_param('translations_health_condition'));
    $image = $_FILES['image'];
    $status = filter_var($request->get_param('status'), FILTER_VALIDATE_BOOLEAN);
    $key_label = intval($request->get_param('key_label'));
    $product_ids = json_decode($request->get_param('product_ids'));

    // Validate required fields
    if (empty($plant_id) || empty($translations_name)  || empty($translations_description) || empty($translations_health_condition) || empty($image['tmp_name'])) {
        return new WP_Error('invalid_data', __('Please provide all required fields.'), array('status' => 400));
    }

    // Upload image for disease
    $upload = wp_upload_bits($image['name'], null, file_get_contents($image['tmp_name']));
    if (!$upload['error']) {
        $image_path = $upload['file'];
        $image_url = $upload['url'];
    } else {
        return new WP_Error('upload_failed', __('Failed to upload image for disease.'), array('status' => 500));
    }

    // Insert data into disease_ai table
    $table_name = $wpdb->prefix . 'disease_ai';
    $insert_result = $wpdb->insert(
        $table_name,
        array(
            'plant_id' => $plant_id,
            'key_label' => $key_label,
            'pictures' => $image_path,
            'status' => $status,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ),
        array('%d',  '%d', '%s', '%s', '%s', '%s')
    );

    if ($insert_result === false) {
        return new WP_Error('insert_failed', __('Failed to add disease.'), array('status' => 500));
    }

    // Get the ID of the inserted disease
    $disease_id = $wpdb->insert_id;


    // Insert translations for disease name, description, and health condition
    $table_name_translations = $wpdb->prefix . 'translations';

    foreach ($language_codes as $index => $language_code) {
        // English translations
        $wpdb->insert(
            $table_name_translations,
            array(
                'language_code' => $language_code,
                'translated_text' => $translations_name[$index],
                'entity_type' => 'disease',
                'entity_id' => $disease_id,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s')
        );



        $wpdb->insert(
            $table_name_translations,
            array(
                'language_code' => $language_code,
                'translated_text' =>  $translations_health_condition[$index],
                'entity_type' => 'disease_health_condition',
                'entity_id' => $disease_id,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s')
        );



        $wpdb->insert(
            $table_name_translations,
            array(
                'language_code' => $language_code,
                'translated_text' =>  $translations_description[$index],
                'entity_type' => 'disease_description',
                'entity_id' => $disease_id,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s')
        );
    }

      // Insert into product_disease table
      $table_name_product_disease = $wpdb->prefix . 'product_disease';
      foreach ($product_ids as $product_id) {
          $wpdb->insert(
              $table_name_product_disease,
              array(
                  'disease_id' => $disease_id,
                  'product_id' => $product_id,
                  'created_at' => current_time('mysql'),
                  'updated_at' => current_time('mysql')
              ),
              array('%d', '%d', '%s', '%s')
          );
      }

      
    return rest_ensure_response(array('id' => $disease_id,  'image' => $image_url));
}

function register_add_model_ai_endpoint()
{
    register_rest_route('wphd/v2', '/add-model-ai', array(
        'methods' => 'POST',
        'callback' => 'add_new_model_ai',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_add_model_ai_endpoint');
function add_new_model_ai($request)
{
    global $wpdb;
    $plant_id = intval($request->get_param('plant_id'));
    $name = sanitize_text_field($request->get_param('name'));
    $description =  sanitize_textarea_field($request->get_param('description'));
    $github_url = esc_url_raw($request->get_param('github_url'));
    $version = sanitize_text_field($request->get_param('version'));
    $status =  filter_var($request->get_param('status'), FILTER_VALIDATE_BOOLEAN);

    // Validate required fields
    if (empty($plant_id) || empty($name)  || empty($github_url)) {
        return new WP_Error('invalid_data', __('Please provide all required fields.'), array('status' => 400));
    }

    // Download files from GitHub
    $github_url = rtrim($github_url, '/');
    $github_url = str_replace('/blob', '', $github_url);
    // Generate random characters for the folder name
    $random_chars = uniqid('');

    // Generate random folder name starting with "model"
    $random_folder_name = 'model_' . $random_chars;

    // Download files from GitHub and save them in the same random folder
    $model_json_file_path = download_and_save_file($github_url, 'model.json', $random_folder_name);
    if (is_wp_error($model_json_file_path)) {
        return $model_json_file_path;
    }

    $model_weights_file_path = download_and_save_file($github_url, 'weights.bin', $random_folder_name);
    if (is_wp_error($model_weights_file_path)) {
        return $model_weights_file_path;
    }

    $model_meta_file_path = download_and_save_file($github_url, 'metadata.json', $random_folder_name);
    if (is_wp_error($model_meta_file_path)) {
        return $model_meta_file_path;
    }

    $model_class_file_path = download_and_save_file($github_url, 'class.json', $random_folder_name);
    if (is_wp_error($model_class_file_path)) {
        return $model_class_file_path;
    }

    // Insert data into model_ai table
    $table_name = $wpdb->prefix . 'model_ai';
    $insert_result = $wpdb->insert(
        $table_name,
        array(
            'plant_id' => $plant_id,
            'name' => $name,
            'description' => $description,
            'github_url' => $github_url,
            'model_json_file' => $model_json_file_path,
            'model_weights_file' => $model_weights_file_path,
            'model_meta_file' => $model_meta_file_path,
            'model_class_file' => $model_class_file_path,
            'version' => $version,
            'status' => $status,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
    );

    if ($insert_result === false) {
        return new WP_Error('insert_failed', __('Failed to add model AI.'), array('status' => 500));
    }
    $model_ai_id = $wpdb->insert_id;
    $upload_dir = wp_upload_dir();
    $base_upload_url = $upload_dir['baseurl'];

    $model_json_url = $base_upload_url  . $model_json_file_path;
    $model_weights_url = $base_upload_url  . $model_weights_file_path;
    $model_meta_url = $base_upload_url  . $model_meta_file_path;
    $model_class_url = $base_upload_url  . $model_class_file_path;

    return rest_ensure_response(array(
        'id' => $model_ai_id,
        'model_json_file' =>  $model_json_url,
        'model_weights_file' => $model_weights_url,
        'model_meta_file' => $model_meta_url,
        'model_class_file' => $model_class_url
    ));
}



function download_and_save_file($github_url, $filename, $random_folder_name)
{
    $raw_url = "https://raw.githubusercontent.com" .  parse_url($github_url, PHP_URL_PATH) . '/' . $filename;

    $file_content = file_get_contents($raw_url);

    if ($file_content === false) {
        return new WP_Error('file_get_contents_failed', __('Failed to fetch file content.'), array('status' => 500));
    }

    // Get the upload directory
    $upload_dir = wp_upload_dir();

    // Create the random folder if it doesn't exist
    $random_folder_path = $upload_dir['basedir'] . '/' . $random_folder_name;
    if (!file_exists($random_folder_path)) {
        mkdir($random_folder_path, 0777, true); // Create the folder recursively
    }

    // Save file to the random folder
    $upload_path = $random_folder_path . '/' . $filename;
    $file_saved = file_put_contents($upload_path, $file_content);

    if ($file_saved === false) {
        return new WP_Error('file_put_contents_failed', __('Failed to save file.'), array('status' => 500));
    }

    // Return the path to the saved file
    return $upload_dir['dir'] . '/' . $random_folder_name  . '/' . $filename;
}



function register_add_detection_history_endpoint()
{
    register_rest_route('wphd/v2', '/add-detection-history', array(
        'methods' => 'POST',
        'callback' => 'add_detection_history',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_add_detection_history_endpoint');

function add_detection_history($request)
{
    global $wpdb;

    // Retrieve parameters from the request
    // $user_id = get_current_user_id(); 
    $user_id = intval($request->get_param('user_id'));
    $plant_id = intval($request->get_param('plant_id'));
    $correct_disease_id = intval($request->get_param('correct_disease_id'));
    $prediction_result_value  = $request->get_param('prediction_result_value');
    $model_id = intval($request->get_param('model_id'));
    $image = $_FILES['image'];

    // Validate required fields
    if (empty($plant_id) || empty($correct_disease_id) || empty($prediction_result_value) || empty($model_id) || empty($image['tmp_name'])) {
        return new WP_Error('invalid_data', __('Please provide all required fields.'), array('status' => 400));
    }

    // Upload image for detection
    $upload = wp_upload_bits($image['name'], null, file_get_contents($image['tmp_name']));
    if (!$upload['error']) {
        $image_path = $upload['file'];
    } else {
        return new WP_Error('upload_failed', __('Failed to upload detection image.'), array('status' => 500));
    }

    // Insert data into history_user_detection table
    $table_name_history = $wpdb->prefix . 'history_user_detection';
    $insert_result = $wpdb->insert(
        $table_name_history,
        array(
            'id_user' => $user_id,
            'picture' => $image_path,
            'prediction_result_value' => $prediction_result_value,
            'plant_id' => $plant_id,
            'correct_disease_id' => $correct_disease_id,
            'model_id' => $model_id,
            'created_at' => current_time('mysql'),
        ),
        array('%d', '%s', '%s', '%d', '%d', '%d', '%s')
    );

    if ($insert_result === false) {
        return new WP_Error('insert_failed', __('Failed to add detection history.'), array('status' => 500));
    }

    // Get the ID of the inserted detection history
    $history_id = $wpdb->insert_id;

    return rest_ensure_response(array('id' => $history_id));
}


function register_get_detection_history_endpoint()
{
    register_rest_route('wphd/v2', '/detection-history', array(
        'methods' => 'GET',
        'callback' => 'get_detection_history',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_get_detection_history_endpoint');


function get_detection_history($request)
{
    global $wpdb;

    $table_name_history = $wpdb->prefix . 'history_user_detection';
    $table_name_plant = $wpdb->prefix . 'plant_ai';
    $table_name_disease = $wpdb->prefix . 'disease_ai';
    $table_name_translations = $wpdb->prefix . 'translations';

    $upload_dir = wp_get_upload_dir();
    $base_url = $upload_dir['url'];
    $lang = get_locale();
    if($lang == 'ar'){
        $default_language_code = 'ar';
    }else{
        $default_language_code = 'en';
    }
    // Default language code for translations
  

    // Retrieve detection history with associated plant and disease data
    $detection_history = $wpdb->get_results($wpdb->prepare("
        SELECT h.id, h.id_user, h.picture AS picture_filename, h.prediction_result_value, h.created_at,
               COALESCE(tp.translated_text, p.id) AS plant_name, COALESCE(td.translated_text, d.id) AS disease_name
        FROM $table_name_history h
        LEFT JOIN $table_name_plant p ON h.plant_id = p.id
        LEFT JOIN $table_name_translations tp ON p.id = tp.entity_id AND tp.entity_type = 'plant' AND tp.language_code = %s
        LEFT JOIN $table_name_disease d ON h.correct_disease_id = d.id
        LEFT JOIN $table_name_translations td ON d.id = td.entity_id AND td.entity_type = 'disease' AND td.language_code = %s
        ORDER BY h.created_at DESC
    ", $default_language_code, $default_language_code));

    if ($detection_history) {
        // Iterate through each result and add the full URL for the picture
        foreach ($detection_history as &$history) {
            $history->picture_url = $base_url . '/' . basename($history->picture_filename);
        }
        return rest_ensure_response($detection_history);
    } else {
        return array(); // Return an empty array if no history found
    }
}



function register_get_plants_translate_endpoint()
{
    register_rest_route('wphd/v2', '/plants-t', array(
        'methods' => 'GET',
        'callback' => 'get_all_plants_translate',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_get_plants_translate_endpoint');


function get_all_plants_translate($request)
{
    global $wpdb;
    $table_name_plant = $wpdb->prefix . 'plant_ai';
    $table_name_translations = $wpdb->prefix . 'translations';

    // Retrieve all languages from the translations table
    // $languages = $wpdb->get_col("SELECT DISTINCT language_code FROM $table_name_translations");
    $languages = ['en' , 'ar'];
    // Retrieve all plants from the plant_ai table
    $plants_data = $wpdb->get_results("
        SELECT p.id, p.picture, p.status, " . implode(", ", array_map(function ($lang) use ($table_name_translations) {
            return "pt_$lang.translated_text AS name_$lang";
        }, $languages)) . "
        FROM $table_name_plant p
        " . implode("\n", array_map(function ($lang) use ($table_name_translations) {
            return "LEFT JOIN $table_name_translations AS pt_$lang ON p.id = pt_$lang.entity_id AND pt_$lang.entity_type = 'plant' AND pt_$lang.language_code = '$lang'";
        }, $languages))
    );

    // Get the base upload directory URL
    $upload_dir = wp_get_upload_dir();
    $base_url = $upload_dir['url'];

    $plants = array();
    foreach ($plants_data as $plant) {
        $plant_item = array(
            'id' => $plant->id,
            'image' => $base_url . '/' . basename($plant->picture),
            'status' => $plant->status == 1 ? true : false,
            'languageCodes' => $languages,
            'translations' => array_map(function ($lang) use ($plant) {
                return $plant->{"name_$lang"};
            }, $languages)
        );

        $plants[] = $plant_item;
    }

    return rest_ensure_response($plants);
}



function register_get_plants_endpoint()
{
    register_rest_route('wphd/v2', '/plants', array(
        'methods' => 'GET',
        'callback' => 'get_all_plants',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_get_plants_endpoint');


function get_all_plants($request)
{
    
    global $wpdb;
    $table_name_plant = $wpdb->prefix . 'plant_ai';
    $table_name_translations = $wpdb->prefix . 'translations';

    $current = get_locale();
    // Get the user's preferred language
    $user_language =  explode('_', $current)[0];

    // Retrieve all plants from the plant_ai table
    $plants_data = $wpdb->get_results("
        SELECT p.id, pt.translated_text AS name, p.picture, p.status
        FROM $table_name_plant p
        LEFT JOIN $table_name_translations pt ON p.id = pt.entity_id AND pt.entity_type = 'plant' AND pt.language_code = '$user_language'
    ");
    // Get the base upload directory URL
    $upload_dir = wp_get_upload_dir();
    $base_url = $upload_dir['url'];

    if ($plants_data) {
        $plants = array();
        foreach ($plants_data as $plant) {
            $plant_id = $plant->id;
            $plant_name = $plant->name;
            $plant_image = $plant->picture;
            $plant_status =  $plant->status == 1 ? true : false;

            // Construct the full image URL
            $plant_image_url = $base_url . '/' . basename($plant_image);

            $plant_item = array(
                'id' => $plant_id,
                'name' => $plant_name,
                'image' =>  $plant_image_url,
                'status'  => $plant_status,
            );
            $plants[] = $plant_item;
        }
        return rest_ensure_response($plants);
    } else {
        return array();
    }
}


function register_get_diseases_endpoint_translate() {
    register_rest_route('wphd/v2', '/diseases-t', array(
        'methods' => 'GET',
        'callback' => 'get_all_diseases_translate',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_get_diseases_endpoint_translate');

function get_all_diseases_translate($request) {
    global $wpdb;
    $table_name_disease = $wpdb->prefix . 'disease_ai';
    $table_name_translations = $wpdb->prefix . 'translations';
    $table_name_product_disease = $wpdb->prefix . 'product_disease';

    // Get the plant_id from the request
    $plant_id = $request->get_param('plant_id');

    // Define the languages
    $languages = ['en', 'ar'];

    // Construct the SQL query with a WHERE clause to filter by plant_id
    $sql = "
        SELECT d.id, d.plant_id, d.pictures, d.key_label, d.status, GROUP_CONCAT(pd.product_id) AS product_ids, " . implode(", ", array_map(function ($lang) use ($table_name_translations) {
            return "dt_$lang.translated_text AS name_$lang, ddt_$lang.translated_text AS description_$lang, dhct_$lang.translated_text AS health_condition_$lang";
        }, $languages)) . "
        FROM $table_name_disease d
        " . implode("\n", array_map(function ($lang) use ($table_name_translations) {
            return "LEFT JOIN $table_name_translations AS dt_$lang ON d.id = dt_$lang.entity_id AND dt_$lang.entity_type = 'disease' AND dt_$lang.language_code = '$lang'
            LEFT JOIN $table_name_translations AS ddt_$lang ON d.id = ddt_$lang.entity_id AND ddt_$lang.entity_type = 'disease_description' AND ddt_$lang.language_code = '$lang'
            LEFT JOIN $table_name_translations AS dhct_$lang ON d.id = dhct_$lang.entity_id AND dhct_$lang.entity_type = 'disease_health_condition' AND dhct_$lang.language_code = '$lang'";
        }, $languages)) . "
        LEFT JOIN $table_name_product_disease AS pd ON d.id = pd.disease_id";

    if ($plant_id) {
        $sql .= $wpdb->prepare(" WHERE d.plant_id = %d", $plant_id);
    }

    $sql .= " GROUP BY d.id";

    // Retrieve diseases from the disease_ai table
    $diseases_data = $wpdb->get_results($sql);

    $upload_dir = wp_get_upload_dir();
    $base_url = $upload_dir['url'];

    if ($diseases_data) {
        $diseases = array();
        foreach ($diseases_data as $disease) {
            $disease_id = $disease->id;
            $plant_id = $disease->plant_id;
            $disease_image = $disease->pictures;
            $disease_status = $disease->status == 1 ? true : false;
            $disease_key_label = $disease->key_label;
            $product_ids = array_map('intval', explode(',', $disease->product_ids)); // Convert product_ids to an array

            // Fetch product details
            $products = array();
            foreach ($product_ids as $product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    $products[] = array(
                        'id' => $product->get_id(),
                        'name' => $product->get_name(),
                        'image' => wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail')
                    );
                }
            }

            $disease_item = array(
                'id' => $disease_id,
                'plant_id' => $plant_id,
                'image' => $base_url . '/' . basename($disease_image),
                'key_label' => $disease_key_label,
                'status' => $disease_status,
                'languageCodes' => $languages,
                'translationsName' => array_map(function ($lang) use ($disease) {
                    return $disease->{"name_$lang"};
                }, $languages),
                'translationsDescription' => array_map(function ($lang) use ($disease) {
                    return $disease->{"description_$lang"};
                }, $languages),
                'translationsHealthCondition' => array_map(function ($lang) use ($disease) {
                    return $disease->{"health_condition_$lang"};
                }, $languages),
                'product_ids' => $product_ids,
                'products' => $products // Add product details to the response
            );
            $diseases[] = $disease_item;
        }
        return rest_ensure_response($diseases);
    } else {
        return array();
    }
}



function register_get_diseases_endpoint()
{
    register_rest_route('wphd/v2', '/diseases', array(
        'methods' => 'GET',
        'callback' => 'get_all_diseases',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_get_diseases_endpoint');

function get_all_diseases($request)
{
    global $wpdb;
    $table_name_disease = $wpdb->prefix . 'disease_ai';
    $table_name_translations = $wpdb->prefix . 'translations';

    // Get the plant_id from the request
    $plant_id = $request->get_param('plant_id');

    // Get the user's preferred language
    $current = get_locale();
    $user_language = explode('_', $current)[0];

    // Construct the SQL query with a WHERE clause to filter by plant_id
    $sql = "
        SELECT d.id, d.plant_id, dt.translated_text AS name, ddt.translated_text AS description, dhct.translated_text AS health_condition, d.pictures, d.key_label, d.status
        FROM $table_name_disease d
        LEFT JOIN $table_name_translations dt ON d.id = dt.entity_id AND dt.entity_type = 'disease' AND dt.language_code = '$user_language'
        LEFT JOIN $table_name_translations ddt ON d.id = ddt.entity_id AND ddt.entity_type = 'disease_description' AND ddt.language_code = '$user_language'
        LEFT JOIN $table_name_translations dhct ON d.id = dhct.entity_id AND dhct.entity_type = 'disease_health_condition' AND dhct.language_code = '$user_language'
    ";

    if ($plant_id) {
        $sql .= $wpdb->prepare(" WHERE d.plant_id = %d", $plant_id);
    }

    // Retrieve diseases from the disease_ai table
    $diseases_data = $wpdb->get_results($sql);

    $upload_dir = wp_get_upload_dir();
    $base_url = $upload_dir['url'];

    if ($diseases_data) {
        $diseases = array();
        foreach ($diseases_data as $disease) {
            $disease_id = $disease->id;
            $plant_id = $disease->plant_id;
            $disease_name = $disease->name;
            $disease_description = $disease->description;
            $disease_health_condition = $disease->health_condition;
            $disease_image = $disease->pictures;
            $disease_status = $disease->status == 1 ? true : false;
            $disease_key_label = $disease->key_label;

            $disease_item = array(
                'id' => $disease_id,
                'plant_id' => $plant_id,
                'name' => $disease_name,
                'description' => $disease_description,
                'health_condition' => $disease_health_condition,
                'key_label' => $disease_key_label,
                'image' => $base_url . '/' . basename($disease_image),
                'status' => $disease_status,
            );
            $diseases[] = $disease_item;
        }
        return rest_ensure_response($diseases);
    } else {
        return array();
    }
}





// Register REST API endpoint to retrieve AI models data
function register_get_models_endpoint()
{
    register_rest_route('wphd/v2', '/models', array(
        'methods' => 'GET',
        'callback' => 'get_all_models',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_get_models_endpoint');

// Callback function to retrieve all AI models
function get_all_models($request)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'model_ai';

    $upload_dir = wp_get_upload_dir();
    $base_url = $upload_dir['baseurl'];
    // Retrieve all AI models from the model_ai table
    $models_data = $wpdb->get_results("SELECT * FROM $table_name");

    if ($models_data) {
        $models = array();
        foreach ($models_data as $model) {
            $model_id = $model->id;
            $plant_id = $model->plant_id;
            $model_name = $model->name;
            $model_description = $model->description;
            $github_url = $model->github_url;
            $model_json_file = $model->model_json_file;
            $model_weights_file = $model->model_weights_file;
            $model_meta_file = $model->model_meta_file;
            $model_class_file = $model->model_class_file;
            $version = $model->version;
            $model_status = $model->status == 1 ? true : false;

            $model_item = array(
                'id' => $model_id,
                'plant_id' => $plant_id,
                'name' => $model_name,
                'description' => $model_description,
                'github_url' => $github_url,
                'model_json_file' => $base_url . $model_json_file,
                'model_weights_file' => $base_url . $model_weights_file,
                'model_meta_file' => $base_url . $model_meta_file,
                'model_class_file' => $base_url  . $model_class_file,
                'version' => $version,
                'status' => $model_status,
            );
            $models[] = $model_item;
        }
        return rest_ensure_response($models);
    } else {
        return array();
        // return new WP_Error('no_models', __('No AI models found.'), array('status' => 404));
    }
}





// function register_get_all_frontend()
// {
//     register_rest_route('wphd/v2', '/get-info-frontend', array(
//         'methods' => 'GET',
//         'callback' => 'get_info_frontend',
//         'permission_callback' => '__return_true'
//     ));
// }
// add_action('rest_api_init', 'get_info_frontend');


// function get_info_frontend($request)
// {
//     $plants =  get_all_plants_translate($request);
//     $diseases =  get_all_diseases_translate($request);
    
// }






















function register_get_statistics_endpoint()
{
    register_rest_route('wphd/v2', '/statistics', array(
        'methods' => 'GET',
        'callback' => 'get_statistics',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_get_statistics_endpoint');

function get_statistics($request)
{
    global $wpdb;

    $table_name_plant = $wpdb->prefix . 'plant_ai';
    $table_name_disease = $wpdb->prefix . 'disease_ai';
    $table_name_modelai = $wpdb->prefix . 'model_ai';
    $table_name_history = $wpdb->prefix . 'history_user_detection';

    try {
        // Retrieve counts from each table
        $plants_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name_plant");
        $diseases_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name_disease");
        $modelai_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name_modelai");

        // Count history records
        $history_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name_history");

        // Construct response array
        $statistics = array(
            'plants' => intval($plants_count),
            'diseases' => intval($diseases_count),
            'modelAI' => intval($modelai_count),
            'history' => intval($history_count) // Add history count to the response
        );

        // Return statistics as JSON response
        return rest_ensure_response($statistics);
    } catch (Exception $e) {
        // Handle any exceptions or errors
        $error_message = $e->getMessage();
        return new WP_Error('statistics_error', $error_message, array('status' => 500));
    }
}




function register_edit_plant_endpoint()
{
    register_rest_route('wphd/v2', '/edit-plant', array(
        'methods' => 'POST',
        'callback' => 'edit_plant',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_edit_plant_endpoint');
function edit_plant($request)
{
    global $wpdb;

    $plant_id = $request->get_param('id');
    $status = filter_var($request->get_param('status'), FILTER_VALIDATE_BOOLEAN);
    $language_codes = json_decode($request->get_param('language_codes'));
    $translations = json_decode($request->get_param('translations'));

    // Validate required fields
    if (empty($plant_id) || empty($language_codes) || empty($translations)) {
        return new WP_Error('invalid_data', __('Please provide all required fields.'), array('status' => 400));
    }

    $image = isset($_FILES['image']) ? $_FILES['image'] : null; // Check if image is provided

    $image_path = ''; // Initialize image path
    $image_url = ''; // Initialize image URL

    // Upload image if provided
    if ($image) {
        $upload = wp_upload_bits($image['name'], null, file_get_contents($image['tmp_name']));
        if (!$upload['error']) {
            $image_path = $upload['file'];
            $image_url = $upload['url'];
        } else {
            return new WP_Error('upload_failed', __('Failed to upload image.'), array('status' => 500));
        }
    }

    // Update data in plant_ai table
    $table_name = $wpdb->prefix . 'plant_ai';

    $data_to_update = array(
        'status' => $status,
        'updated_at' => current_time('mysql')
    );

    // Include image data if available
    if (!empty($image_path)) {
        $data_to_update['picture'] = $image_path;
    }

    $update_result = $wpdb->update(
        $table_name,
        $data_to_update,
        array('id' => $plant_id), // WHERE clause
        array('%d', '%s', '%s'), // Data format
        array('%d') // WHERE format
    );

    if ($update_result === false) {
        return new WP_Error('update_failed', __('Failed to edit plant.'), array('status' => 500));
    }

    // Update translations in translations table
    $table_name_translations = $wpdb->prefix . 'translations';

    foreach ($language_codes as $index => $language_code) {
        $wpdb->update(
            $table_name_translations,
            array(
                'translated_text' => $translations[$index],
                'updated_at' => current_time('mysql')
            ),
            array(
                'entity_id' => $plant_id,
                'entity_type' => 'plant',
                'language_code' => $language_code
            ),
            array('%s', '%s'),
            array('%d', '%s', '%s')
        );
    }

    // Prepare response data
    $response_data = array(
        'id' => $plant_id,
        'status' => $status == 1 ? true : false,
    );

    // Include image URL if available
    if ($image_url) {
        $response_data['image'] = $image_url;
    }

    return rest_ensure_response($response_data);
}








function register_edit_disease_endpoint()
{
    register_rest_route('wphd/v2', '/edit-disease', array(
        'methods' => 'POST',
        'callback' => 'edit_disease',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_edit_disease_endpoint');

function edit_disease($request) {
    global $wpdb;

    // Retrieve disease ID from the request
    $disease_id = $request->get_param('id');
    $plant_id = $request->get_param('plant_id');

    // Retrieve language codes and translations
    $language_codes = json_decode($request->get_param('language_codes'));
    $translations_name = json_decode($request->get_param('translations_name'));
    $translations_description = json_decode($request->get_param('translations_description'));
    $translations_health_condition = json_decode($request->get_param('translations_health_condition'));
    $product_ids = json_decode($request->get_param('product_ids'));

    $image = isset($_FILES['image']) ? $_FILES['image'] : null;
    $status = filter_var($request->get_param('status'), FILTER_VALIDATE_BOOLEAN);

    // Validate required fields
    if (empty($disease_id) || empty($plant_id)) {
        return new WP_Error('invalid_data', __('Please provide all required fields.'), array('status' => 400));
    }

    // Initialize image path and URL
    $image_path = '';
    $image_url = '';

    // Check if image is provided
    if ($image) {
        $upload = wp_upload_bits($image['name'], null, file_get_contents($image['tmp_name']));
        if (!$upload['error']) {
            $image_path = $upload['file'];
            $image_url = $upload['url'];
        } else {
            return new WP_Error('upload_failed', __('Failed to upload image.'), array('status' => 500));
        }
    }

    // Update data in disease_ai table
    $table_name = $wpdb->prefix . 'disease_ai';
    $data_to_update = array(
        'plant_id' => $plant_id,
        'status' => $status,
        'updated_at' => current_time('mysql')
    );

    // Include image data if available
    if (!empty($image_path)) {
        $data_to_update['pictures'] = $image_path;
    }

    $update_result = $wpdb->update(
        $table_name,
        $data_to_update,
        array('id' => $disease_id),
        array('%d', '%d', '%s', '%s'),
        array('%d')
    );

    if ($update_result === false) {
        return new WP_Error('update_failed', __('Failed to edit disease.'), array('status' => 500));
    }

    // Update translations for disease name, description, and health condition
    $table_name_translations = $wpdb->prefix . 'translations';
    foreach ($language_codes as $index => $language_code) {
        // Update translations
        $wpdb->update(
            $table_name_translations,
            array(
                'translated_text' => $translations_name[$index],
                'updated_at' => current_time('mysql')
            ),
            array(
                'entity_type' => 'disease',
                'entity_id' => $disease_id,
                'language_code' => $language_code
            ),
            array('%s', '%s'),
            array('%s', '%d', '%s')
        );
        $wpdb->update(
            $table_name_translations,
            array(
                'translated_text' => $translations_health_condition[$index],
                'updated_at' => current_time('mysql')
            ),
            array(
                'entity_type' => 'disease_health_condition',
                'entity_id' => $disease_id,
                'language_code' => $language_code
            ),
            array('%s', '%s'),
            array('%s', '%d', '%s')
        );
        $wpdb->update(
            $table_name_translations,
            array(
                'translated_text' => $translations_description[$index],
                'updated_at' => current_time('mysql')
            ),
            array(
                'entity_type' => 'disease_description',
                'entity_id' => $disease_id,
                'language_code' => $language_code
            ),
            array('%s', '%s'),
            array('%s', '%d', '%s')
        );
    }

    // Update product_disease table
    $table_name_product_disease = $wpdb->prefix . 'product_disease';

    // Delete existing product relationships
    $wpdb->delete($table_name_product_disease, array('disease_id' => $disease_id), array('%d'));

    // Insert new product relationships
    foreach ($product_ids as $product_id) {
        $wpdb->insert(
            $table_name_product_disease,
            array(
                'disease_id' => $disease_id,
                'product_id' => $product_id,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s')
        );
    }

    // Prepare response data
    $response_data = array(
        'plant_id' => $plant_id,
        'id' => $disease_id,
        'status' => $status == 1 ? true : false,
    );

    // Include image URL if available
    if (!empty($image_url)) {
        $response_data['image'] = $image_url;
    }

    return rest_ensure_response($response_data);
}











function register_delete_plant_endpoint()
{
    register_rest_route('wphd/v2', '/delete-plant/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_plant',
        'permission_callback' => '__return_true',
        'args' => array(
            'id' => array(
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                }
            ),
        ),
    ));
}
add_action('rest_api_init', 'register_delete_plant_endpoint');

function delete_plant($request)
{
    global $wpdb;

    $plant_id = $request['id'];

    // Validate plant ID
    if (empty($plant_id) || !is_numeric($plant_id)) {
        return new WP_Error('invalid_id', __('Invalid or missing plant ID.'), array('status' => 400));
    }

    // Delete plant from plant_ai table
    $table_name = $wpdb->prefix . 'plant_ai';
    $delete_result = $wpdb->delete(
        $table_name,
        array('id' => $plant_id),
        array('%d')
    );

    if ($delete_result === false) {
        return new WP_Error('delete_failed', __('Failed to delete plant.'), array('status' => 500));
    } elseif ($delete_result === 0) {
        return new WP_Error('not_found', __('Plant not found.'), array('status' => 404));
    }

    // Optionally, you can also delete associated data from other tables (e.g., disease_ai)

    return rest_ensure_response(__('Plant deleted successfully.'));
}


function register_delete_diseases_endpoint()
{
    register_rest_route('wphd/v2', '/delete-disease/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_disease',
        'permission_callback' => '__return_true',
        'args' => array(
            'id' => array(
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                }
            ),
        ),
    ));
}
add_action('rest_api_init', 'register_delete_diseases_endpoint');

function delete_disease($request)
{
    global $wpdb;

    $disease_id = $request['id'];

    // Validate disease ID
    if (empty($disease_id) || !is_numeric($disease_id)) {
        return new WP_Error('invalid_id', __('Invalid or missing disease ID.'), array('status' => 400));
    }

    // Delete disease from disease_ai table
    $table_name = $wpdb->prefix . 'disease_ai';
    $delete_result = $wpdb->delete(
        $table_name,
        array('id' => $disease_id),
        array('%d')
    );

    if ($delete_result === false) {
        return new WP_Error('delete_failed', __('Failed to delete disease.'), array('status' => 500));
    } elseif ($delete_result === 0) {
        return new WP_Error('not_found', __('Disease not found.'), array('status' => 404));
    }

    return rest_ensure_response(__('Disease deleted successfully.'));
}
























function register_setup_data_endpoint()
{
    register_rest_route('wphd/v2', '/setup-data', array(
        'methods' => 'POST',
        'callback' => 'setup_demo_data',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'register_setup_data_endpoint');
function setup_demo_data($request)
{
    global $wpdb;
    $table_name_plant = $wpdb->prefix . 'plant_ai';
    $table_name_disease = $wpdb->prefix . 'disease_ai';
    $table_name_modelai = $wpdb->prefix . 'model_ai';
    $table_name_translations = $wpdb->prefix . 'translations';

    // Read JSON file
    $json_data = file_get_contents('data.json');

    // Decode JSON data
    $data = json_decode($json_data, true);

    // Define plant folders and labels
    $plant_folder = array_keys($data);
    $plant_labels = array();
    foreach ($data as $key => $value) {
        array_push($plant_labels, $value['label']['en']);
    }

    // Insert plants, diseases, and models
    foreach ($plant_labels as $key => $label) {
        // Insert plant
        $wpdb->insert(
            $table_name_plant,
            array(
                'status' => 1,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s')
        );
        $plant_id = $wpdb->insert_id;

        // Insert plant translation
        $wpdb->insert(
            $table_name_translations,
            array(
                'language_code' => 'en',
                'translated_text' => $label,
                'entity_type' => 'plant',
                'entity_id' => $plant_id,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s')
        );

        // Insert Arabic plant translation
        $wpdb->insert(
            $table_name_translations,
            array(
                'language_code' => 'ar',
                'translated_text' => $data[$plant_folder[$key]]['label']['ar'],
                'entity_type' => 'plant',
                'entity_id' => $plant_id,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s', '%s')
        );

        // Insert diseases
        $disease_labels = array_keys($data[$plant_folder[$key]]['diseases']);
        foreach ($disease_labels as $disease_name) {
            $description = $data[$plant_folder[$key]]['diseases'][$disease_name]['description']['en'];
            $health_condition = $data[$plant_folder[$key]]['diseases'][$disease_name]['health_condition']['en'];

            // Insert disease
            $wpdb->insert(
                $table_name_disease,
                array(
                    'plant_id' => $plant_id,
                    'key_label' => 0, // Assuming you have a 'key_label' column
                    'status' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%d', '%d', '%d', '%s', '%s')
            );
            $disease_id = $wpdb->insert_id;

            // Insert disease translation
            $wpdb->insert(
                $table_name_translations,
                array(
                    'language_code' => 'en',
                    'translated_text' => $disease_name,
                    'entity_type' => 'disease',
                    'entity_id' => $disease_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );

            // Insert Arabic disease translation
            $wpdb->insert(
                $table_name_translations,
                array(
                    'language_code' => 'ar',
                    'translated_text' => $data[$plant_folder[$key]]['diseases'][$disease_name]['name']['ar'],
                    'entity_type' => 'disease',
                    'entity_id' => $disease_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );

            // Insert disease description translation
            $wpdb->insert(
                $table_name_translations,
                array(
                    'language_code' => 'en',
                    'translated_text' => $description,
                    'entity_type' => 'disease_description',
                    'entity_id' => $disease_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );

            // Insert Arabic disease description translation
            $wpdb->insert(
                $table_name_translations,
                array(
                    'language_code' => 'ar',
                    'translated_text' => $data[$plant_folder[$key]]['diseases'][$disease_name]['description']['ar'],
                    'entity_type' => 'disease_description',
                    'entity_id' => $disease_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );

            // Insert disease health condition translation
            $wpdb->insert(
                $table_name_translations,
                array(
                    'language_code' => 'en',
                    'translated_text' => $health_condition,
                    'entity_type' => 'disease_health_condition',
                    'entity_id' => $disease_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );

            // Insert Arabic disease health condition translation
            $wpdb->insert(
                $table_name_translations,
                array(
                    'language_code' => 'ar',
                    'translated_text' => $data[$plant_folder[$key]]['diseases'][$disease_name]['health_condition']['ar'],
                    'entity_type' => 'disease_health_condition',
                    'entity_id' => $disease_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%d', '%s', '%s')
            );
        }


        //     $github_url = 'https://github.com/shahd1995913/models_world_of_plants_2022/blob/main/'.$plant_folder[$key].'/model';

        //     $github_url = str_replace('/blob', '', $github_url);
        //    // Generate random characters for the folder name
        //    $random_chars = uniqid('');

        //    // Generate random folder name starting with "model"
        //    $random_folder_name = 'model_' . $random_chars;

        //    // Download files from GitHub and save them in the same random folder
        //    $model_json_file_path = download_and_save_file($github_url, 'model.json', $random_folder_name);
        //    if (is_wp_error($model_json_file_path)) {
        //        return $model_json_file_path; 
        //    }

        //    $model_weights_file_path = download_and_save_file($github_url, 'weights.bin', $random_folder_name);
        //    if (is_wp_error($model_weights_file_path)) {
        //        return $model_weights_file_path; 
        //    }

        //    $model_meta_file_path = download_and_save_file($github_url, 'metadata.json', $random_folder_name);
        //    if (is_wp_error($model_meta_file_path)) {
        //        return $model_meta_file_path; 
        //    }

        //    $model_class_file_path = download_and_save_file($github_url, 'class.json', $random_folder_name);
        //    if (is_wp_error($model_class_file_path)) {
        //        return $model_class_file_path; 
        //    }



        //     $upload_dir = wp_upload_dir();
        //     $base_upload_url = $upload_dir['baseurl'];

        //     $model_json_url = $base_upload_url  . $model_json_file_path;
        //     $model_weights_url = $base_upload_url  . $model_weights_file_path;
        //     $model_meta_url = $base_upload_url  . $model_meta_file_path;
        //     $model_class_url = $base_upload_url  . $model_class_file_path;



        //     // Insert model for the plant
        //     $model_data = array(
        //         'plant_id' => $plant_id,
        //         'name' => $label . ' Model',
        //         'description' => 'A machine learning model trained to identify diseases and pests affecting ' . $label . ' plants.',
        //         'github_url' => $github_url,
        //         'model_json_file' => $model_json_file_path,
        //         'model_weights_file' => $model_weights_file_path,
        //         'model_meta_file' => $model_meta_file_path,
        //         'model_class_file' => $model_class_file_path,
        //         'version' => '1.0',
        //         'status' => 1,
        //         'created_at' => current_time('mysql'),
        //         'updated_at' => current_time('mysql')
        //     );

        //     $wpdb->insert(
        //         $table_name_modelai,
        //         $model_data,
        //         array(
        //             '%d',
        //             '%s',
        //             '%s',
        //             '%s',
        //             '%s',
        //             '%s',
        //             '%s',
        //             '%s',
        //             '%s',
        //             '%d',
        //             '%s',
        //             '%s'
        //         )
        //     );
    }
    return rest_ensure_response(__('Demo data inserted successfully.'));
}
