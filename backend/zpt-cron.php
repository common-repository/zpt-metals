<?php
include_once ( dirname ( __FILE__ ).'/../../../../wp-load.php' );
$response_tray = get_option('zpt_metal_api_empty_display');
    
$api_attribs_map = array(
    
    "gold"                  =>  "XAU",
    "silver"                =>  "XAG",
    "platinum"              =>  "XPT",
    "palladium"             =>  "XPD",
    "rhodium"               =>  "XRH",
    "ruthenium"             =>  "RUTH",
    "copper"                =>  "XCU",
    "aluminum"              =>  "ALU",
    "nickel"                =>  "NI",
    "zinc"                  =>  "ZNC",
    "tin"                   =>  "TIN",
    "cobalt"                =>  "LCO",
    "iridium"               =>  "IRD",
    "lead"                  =>  "LEAD", 
    "iron ore"              =>  "IRON",
    "lbma gold am"          =>  "LBXAUAM",
    "lbma gold pm"          =>  "LBXAUPM",
    "lbma platinum am"      =>  "LBXPTAM",
    "lbma platinum pm"      =>  "LBXPTPM",
    "lbma palladium am"     =>  "LBXPDAM",
    "lbma palladium pm"     =>  "LBXPDPM",
    "lme aluminium"         =>  "LME-ALU",
    "lme copper"            =>  "LME-XCU",
    "lme zinc"              =>  "LME-ZNC",
    "lme nickel"            =>  "LME-NI",
    "lme lead"              =>  "LME-LEAD",
    "lme tin"               =>  "LME-TIN",
    "uranium"               =>  "URANIUM",
    
);

$attribs = shortcode_atts( array(
    'type'         =>  "gold",
    "currency"     =>  "USD",
    "symbols"      =>  "USD",
    "base"         =>  "GBP",
    "date-format"  =>  "Y-m-d",
    "price-round"  =>  2,
    "date"         =>  null,
    "carat"        =>  "-1"
), $atts );

$access_key = get_option('zpt_metal_api_key');

$endpoint = 'latest';

$metals_api_uri = "https://metals-api.com/api/$endpoint?access_key=$access_key&base=".$attribs["base"]."&symbols=XAU,XAG,XPT,XPD,XRH,RUTH,XCU,ALU,NI,ZNC,TIN,LCO,IRD,LEAD,IRON,LBXAUAM,LBXAUPM,LBXPTAM,LBXPTPM,LBXPDAM,LBXPDPM,LME-ALU,LME-XCU,LME-ZNC,LME-NI,LME-LEAD,LME-TIN,URANIUM&utm=zactonz";

if( isset( $attribs["date"] ) && trim( $attribs["date"] ) !="" ) {
    $endpoint = date("Y-m-d", strtotime($attribs["date"]));
    
    $metals_api_uri = "https://metals-api.com/api/$endpoint?access_key=$access_key&base=".$attribs["base"]."&symbols=XAU,XAG,XPT,XPD,XRH,RUTH,XCU,ALU,NI,ZNC,TIN,LCO,IRD,LEAD,IRON,LBXAUAM,LBXAUPM,LBXPTAM,LBXPTPM,LBXPDAM,LBXPDPM,LME-ALU,LME-XCU,LME-ZNC,LME-NI,LME-LEAD,LME-TIN,URANIUM&utm=zactonz";
}

/*
* Endpoint for get rates in Carat 
*/
$carat = false;
if( isset( $attribs["carat"] ) && trim( $attribs["carat"] ) !="" && trim( $attribs["carat"] ) != '-1') {
    if( trim( $attribs["carat"] ) == 'enable' ){

        $attribs["carat"] = 'Carat 24K';

    }

    $endpoint = 'carat';
    $carat    = true;
    $metals_api_uri = "https://metals-api.com/api/$endpoint?access_key=$access_key&base=".$attribs["base"]."&symbols=XAU,XAG,XPT,XPD,XRH,RUTH,XCU,ALU,NI,ZNC,TIN,LCO,IRD,LEAD,IRON,LBXAUAM,LBXAUPM,LBXPTAM,LBXPTPM,LBXPDAM,LME-ALU,LME-XCU,LME-ZNC,LME-NI,LME-LEAD,LME-TIN,URANIUM&utm=zactonz";
}
print_r($metals_api_uri);
/*
 Respect API request limits. So serve database saved results until database
 saved rates are older(set by admin from plugin admin area)
*/

$when_last_ran = get_option("zpt_metal_api_last_ran");

$is_data_older_now = false;

if( isset( $when_last_ran ) && trim( $when_last_ran ) !="" ){
    $threshold = get_option('zpt_metal_api_date');
    if( $when_last_ran + ( $threshold * 60 ) < time() ) {
        
        $is_data_older_now = true;
        
    }
    
}

$has_last_response = get_option('zpt_metal_api_last_response');

/*
* if carat parameter is enable then get reponse for carat
*/
if($carat == true){
    $has_last_response = get_option('zpt_metal_api_last_response_carat');        
}

if(!$is_data_older_now && isset( $has_last_response ) && trim( $has_last_response ) !="" ){
    
    $body = $has_last_response;
    
}else{
    
    /*
    * Get data from API if we don't have any data saved in the database or
    * saved data is older()
    */
    
    $api_response = wp_remote_get($metals_api_uri);
    
    if ( is_array( $api_response ) && ! is_wp_error( $api_response ) ) {
        $headers = $api_response['headers']; // array of http header lines
        $body    = $api_response['body']; // use the content
    }
}

echo "-------------------------------------------------------------";

$api_response = wp_remote_get($metals_api_uri);

print_r($api_response);


if( isset( $body ) && trim( $body ) != "" ) {
    
    $response_arr = json_decode( $body, true );
    if( isset( $response_arr ) && !empty( $response_arr ) ){
      
        if( isset( $response_arr["success"] ) && $response_arr["success"] == true ){

            if($carat == true){


                update_option('zpt_metal_api_last_response_carat', $body); # save response to DB to make less requests to API
            
                $response_tray = get_option('zpt_metal_api_success_display');
                
                if( isset( $response_tray ) && trim( $response_tray ) != "" ){
                    
                    if( isset( $response_arr["timestamp"] ) ) {
                        
                        $response_tray = str_replace( "[timestamp]", $response_arr["timestamp"], $response_tray );
                        
                    }
                    
                    
                    if( isset( $response_arr["base"] ) ) {
                    
                        $response_tray = str_replace( "[base]", $response_arr["base"], $response_tray );
                        
                    }
                    
                    if( isset( $response_arr["rates"][ $attribs["carat"] ]  ) ) {
                        
                        /* 
                        NOTE: All the metals rates you get need using USD as a base currency need to be divided by 1
                        We return the values based on the base currency.
                        */
                        if( isset( $response_arr["rates"][ $response_arr["base"] ] ) && $response_arr["base"] == "USD"){
                        
                            $price_factor = $response_arr["rates"][ $response_arr["base"] ];
                            
                            $priced = $price_factor /  $response_arr["rates"][ $attribs["carat"] ];
                            
                        }else{
                            
                            $priced = $response_arr["rates"][ $attribs["carat"] ];
                            
                        }
                        
                        if( isset( $attribs["price-round"] ) &&  trim( $attribs["price-round"] ) !="" )
                            $priced = round($priced, $attribs["price-round"]);
                        
                        
                        $response_tray = str_replace( "[price]", $priced, $response_tray );
                    
                    }
                    
                    if( isset( $response_arr["rates"][ $attribs["carat"] ] )){
                        
                        include( ZPTMETALSPATH.'/backend/currency_symbols.php' );
                        
                        if( isset ( $currency_symbols[ $response_arr["base"] ] ) ){
                            
                            $response_tray = str_replace( "[currency]", $currency_symbols[ $response_arr["base"] ], $response_tray );
                            
                        }

                    }
                    
                    if( isset( $response_arr["unit"] ) ){
                    
                        $response_tray = str_replace( "[unit]", $response_arr["unit"], $response_tray );
                        
                    }
                    
                    update_option('zpt_metal_api_error_found', 'Working');
                    update_option('zpt_metal_api_last_ran', current_time( 'timestamp' ));
                }
            }
            else{
                
                update_option('zpt_metal_api_last_response', $body); # save response to DB to make less requests to API
                
                $response_tray = get_option('zpt_metal_api_success_display');
                
                if( isset( $response_tray ) && trim( $response_tray ) != "" ){
                    
                    if( isset( $response_arr["timestamp"] ) ) {
                        
                        $response_tray = str_replace( "[timestamp]", $response_arr["timestamp"], $response_tray );
                        
                    }
                    
                    if( isset( $response_arr["date"] ) ) {
                        
                        if( isset( $attribs["date-format"] ) &&  trim( $attribs["date-format"] ) !="" )
                            $datetime = date( $attribs["date-format"], strtotime($response_arr["date"]) );
                        else
                            $datetime = $response_arr["date"];
                        
                        $response_tray = str_replace( "[date]", $datetime, $response_tray );
                        
                    }
                    
                    if( isset( $response_arr["base"] ) ) {
                    
                        $response_tray = str_replace( "[base]", $response_arr["base"], $response_tray );
                        
                    }
                    
                    if( isset( $response_arr["rates"][ $api_attribs_map[ trim(strtolower($attribs["type"])) ] ] ) ) {
                        
                        /* 
                        NOTE: All the metals rates you get need using USD as a base currency need to be divided by 1
                        We return the values based on the base currency. For example, for 1 USD the return is a number like 0.000634 for Gold (XAU).
                        To get the gold rate per troy ounce in USD: 1/0.000634= 1577.28 USD
                        */
                        if( isset( $response_arr["rates"][ $response_arr["base"] ] ) && $response_arr["base"] == "USD"){
                        
                            $price_factor = $response_arr["rates"][ $response_arr["base"] ];
                            
                            $priced = $price_factor /  $response_arr["rates"][ $api_attribs_map[ trim(strtolower($attribs["type"])) ] ];
                            
                        }else{
                            
                            $priced = $response_arr["rates"][ $api_attribs_map[ trim(strtolower($attribs["type"])) ] ];
                            
                        }
                        
                        if( isset( $attribs["price-round"] ) &&  trim( $attribs["price-round"] ) !="" )
                            $priced = round($priced, $attribs["price-round"]);
                        
                        
                        $response_tray = str_replace( "[price]", $priced, $response_tray );
                    
                    }
                    
                    if( isset( $response_arr["rates"][ $api_attribs_map[ trim(strtolower($attribs["type"])) ] ] )){
                        
                        include( ZPTMETALSPATH.'/backend/currency_symbols.php' );
                        
                        if( isset ( $currency_symbols[ $response_arr["base"] ] ) ){
                            
                            $response_tray = str_replace( "[currency]", $currency_symbols[ $response_arr["base"] ], $response_tray );
                            
                        }
                        
                        
                    }
                    
                    if( isset( $response_arr["rates"][ $api_attribs_map[ trim(strtolower($attribs["type"])) ] ] )){
                    
                        $response_tray = str_replace( "[type]", trim($attribs["type"]), $response_tray );
                        
                    }
                    
                    if( isset( $response_arr["unit"] ) ){
                    
                        $response_tray = str_replace( "[unit]", $response_arr["unit"], $response_tray );
                        
                    }
                    
                    update_option('zpt_metal_api_error_found', 'Working');
                    update_option('zpt_metal_api_last_ran', current_time( 'timestamp' ));
                }
            }
          
        }else{
            
            $response_tray = get_option('zpt_metal_api_error_display');
            
            update_option('zpt_metal_api_error_found', 'stopped');
            
            if( isset( $response_arr["error"]["info"] ) ){
                
                update_option('zpt_metal_api_error_found', "Error: [".$response_arr["error"]["info"]."]");
                
                $response_tray = str_replace( "[error]", $response_arr["error"]["info"], $response_tray);
                
                wp_mail( get_option( 'admin_email' ), "Heads up: Metals API is responding with error", 'System have found that Metals API generated following error: ['.$response_arr["error"]["info"].']' );
                
            }
          
        }
      
    }
    
        
}