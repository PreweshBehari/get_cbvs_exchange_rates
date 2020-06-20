<?php
// *******
// This is a simple script that will extract the exchange rates from the CBvS Website (https://www.cbvs.sr/)
// ******

function get_cbvs_rates($base_url) {
    if (!$base_url) return array();

    // Check if PHP CURL module is installed/enabled
    if (!extension_loaded('curl')) {
        scriptPrevious("CURL extension not loaded/installed!");
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_URL, $base_url);
    curl_setopt($curl, CURLOPT_REFERER, $base_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $str = curl_exec($curl);
    curl_close($curl);

    // Create a DOM object
    $html_base = new simple_html_dom();
    // Load HTML from a string
    $html_base->load($str);

    $find = $html_base->find('div[id=accordion1]');

    $table_hd = array();
    $table_td = array();

    // Get all the table th/td elements here and create an array
    foreach($find as $element) {
        // Get all tables from div
        foreach($element->find('table') as $table) {
            // get table headers
            foreach($table->find('th') as $tbody) {
                $table_hd[] = $tbody->plaintext;
            }// foreach
            // get table data
            foreach($table->find('td') as $tbody) {
                $table_td[] = $tbody->plaintext;
            }// foreach
            break;// break out the loop
        }// foreach
    }// foreach

    // Add new line break after every 3 counts
    $x = 1;
    $new_array = array();
    foreach ($table_td as $td) {
        $new_array[] = $td;
        if ($x == 3) {
            $new_array[] = '\n';// add new line
            $x = 0;
        }
        $x += 1;// increment by one
    }// foreach

    // Create array by splitting the new array where new line occures
    $y = 0;
    $currency_array = array();
    foreach ($new_array as $new) {
        if ($new != '\n') {
            $currency_array[$y][] = $new;
        } else {
            $y += 1;
        }
    }// foreach 

    // Create associatieve array
    $curr_array = array();
    $currency = strtolower($table_hd[0]);
    $buying   = strtolower($table_hd[1]);
    $selling  = strtolower($table_hd[2]);
    foreach ($currency_array as $curr) {
        $curr_array[$curr[0]] = array($currency => $curr[0]
                                    , $buying   => $curr[1]
                                    , $selling  => $curr[2]
                                    );
    }// foreach

    // Generate the return list
    $x = 0;
    $return_list = array();

    foreach ($curr_array as $key => $value) {
        $return_list[$x]['date']     = date("Y-m-d");
        $return_list[$x]['currency'] = $curr_array[$key]['currency'];
        $return_list[$x]['buying']   = $curr_array[$key]['buying'];
        $return_list[$x]['selling']  = $curr_array[$key]['selling'];

        $x++;
    }// foreach

    // Clear the objects
    $html_base->clear(); 
    unset($html_base);

    return $return_list;
}// get_url_contents

// Call function and print the exchange rates
$cbvs_exchrates = get_cbvs_rates('https://www.cbvs.sr/');
echo '</pre>'; print_r($cbvs_exchrates); echo '</pre>';

?>
