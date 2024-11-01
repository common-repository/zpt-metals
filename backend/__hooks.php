<?php

if ( ! defined( 'ABSPATH' ) ) {
    die;
}


add_action( 'admin_menu', 'zpt_metals_menu_hooker' );


add_shortcode( "zpt-metals","zpt_metals_shortcode_func" );


/**
 * @function
 * Display output of shortcode with provided attributes
 * 
 * @atts can be an array with following attributes
        "type"      =>  "gold",
        "currency"  =>  "USD",
        "symbols"   =>  "USD",
        "base"      =>  "GBP",
        "date-format"   =>  "Y-m-d",
        "price-round"   =>  2,
        "date"  =>  null
    
*/
function zpt_metals_shortcode_func( $atts = array() ){
    
    
    $response_tray = get_option('zpt_metal_api_empty_display');
    
    $api_attribs_map = array(
        
        "gold"                          =>  "XAU",
        "silver"                        =>  "XAG",
        "platinum"                      =>  "XPT",
        "palladium"                     =>  "XPD",
        "rhodium"                       =>  "XRH",
        "ruthenium"                     =>  "RUTH",
        "copper"                        =>  "XCU",
        "aluminum"                      =>  "ALU",
        "nickel"                        =>  "NI",
        "zinc"                          =>  "ZNC",
        "tin"                           =>  "TIN",
        "cobalt"                        =>  "LCO",
        "iridium"                       =>  "IRD",
        "lead"                          =>  "LEAD", 
        "iron ore"                      =>  "IRON",
        "lbma gold am"                  =>  "LBXAUAM",
        "lbma gold pm"                  =>  "LBXAUPM",
        "lbma platinum am"              =>  "LBXPTAM",
        "lbma platinum pm"              =>  "LBXPTPM",
        "lbma palladium am"             =>  "LBXPDAM",
        "lbma palladium pm"             =>  "LBXPDPM",
        "lme aluminium"                 =>  "LME-ALU",
        "lme copper"                    =>  "LME-XCU",
        "lme zinc"                      =>  "LME-ZNC",
        "lme nickel"                    =>  "LME-NI",
        "lme lead"                      =>  "LME-LEAD",
        "lme tin"                       =>  "LME-TIN",
        "uranium"                       =>  "URANIUM",
        "lme steel scrap cfr turkey"    =>  "STEEL-SC",
        "lme steel rebar fob turkey"    =>  "STEEL-RE",
        "lme steel hrc fob china"       =>  "STEEL-HR",
        "bronze"                        =>  "BRONZE",
        "magnesium"                     =>  "MG",
        "osmium"                        =>  "OSMIUM",
        "rhenium"                       =>  "RHENIUM",
        "indium"                        =>  "INDIUM",
        "molybdenum"                    =>  "MO",
        "Tungsten"                      =>  "TUNGSTEN",
        
    );

    $attribs = shortcode_atts( array(
        'type'         =>  "gold",
        "currency"     =>  "USD",
        "symbols"      =>  "USD",
        "base"         =>  "GBP",
        "date-format"  =>  "Y-m-d",
        "price-round"  =>  2,
        "date"         =>  null,
        "carat"        =>  "-1",
        "woocommerce"  =>  "-1"
    ), $atts );
    
    $access_key = get_option('zpt_metal_api_key');
    
    $endpoint = 'latest';
    
    $metals_api_uri = "https://metals-api.com/api/$endpoint?access_key=$access_key&base=".$attribs["base"]."&symbols=XAU,XAG,XPT,XPD,XRH,RUTH,XCU,ALU,NI,ZNC,TIN,LCO,IRD,LEAD,IRON,LBXAUAM,LBXAUPM,LBXPTAM,LBXPTPM,LBXPDAM,LBXPDPM,LME-ALU,LME-XCU,LME-ZNC,LME-NI,LME-LEAD,LME-TIN,URANIUM,STEEL-SC,STEEL-RE,STEEL-HR,BRONZE,MG,OSMIUM,RHENIUM,INDIUM,MO,TUNGSTEN&utm=zactonz";
    
    if( isset( $attribs["date"] ) && trim( $attribs["date"] ) !="" ) {
        $endpoint = date("Y-m-d", strtotime($attribs["date"]));
        
        $metals_api_uri = "https://metals-api.com/api/$endpoint?access_key=$access_key&base=".$attribs["base"]."&symbols=XAU,XAG,XPT,XPD,XRH,RUTH,XCU,ALU,NI,ZNC,TIN,LCO,IRD,LEAD,IRON,LBXAUAM,LBXAUPM,LBXPTAM,LBXPTPM,LBXPDAM,LBXPDPM,LME-ALU,LME-XCU,LME-ZNC,LME-NI,LME-LEAD,LME-TIN,URANIUM,STEEL-SC,STEEL-RE,STEEL-HR,BRONZE,MG,OSMIUM,RHENIUM,INDIUM,MO,TUNGSTEN&utm=zactonz";
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
        $metals_api_uri = "https://metals-api.com/api/$endpoint?access_key=$access_key&base=".$attribs["base"]."&symbols=XAU,XAG,XPT,XPD,XRH,RUTH,XCU,ALU,NI,ZNC,TIN,LCO,IRD,LEAD,IRON,LBXAUAM,LBXAUPM,LBXPTAM,LBXPTPM,LBXPDAM,LME-ALU,LME-XCU,LME-ZNC,LME-NI,LME-LEAD,LME-TIN,URANIUM,STEEL-SC,STEEL-RE,STEEL-HR,BRONZE,MG,OSMIUM,RHENIUM,INDIUM,MO,TUNGSTEN&utm=zactonz";
    }

    /*
     Respect API request limits. So serve database saved results until database
     saved rates are older(set by admin from plugin admin area)
    */
    
    $when_last_ran = get_option("zpt_metal_api_last_ran");
    
    $is_data_older_now = false;
    
    if( isset( $when_last_ran ) && trim( $when_last_ran ) !="" ){
        $threshold = strtotime(get_option('zpt_metal_api_date'));
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

                /*woocommerce enable*/
                if( isset( $attribs["woocommerce"] ) && trim( $attribs["woocommerce"] ) !="" && trim( $attribs["woocommerce"] ) != '-1') {
                    if( trim( $attribs["woocommerce"] ) == 'enable' ){
                        
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
    
  
  
    return wpautop( stripslashes($response_tray) );
}


function zpt_metals_menu_hooker() {
     
    add_menu_page('ZPT Metals Settings', 'ZPT Metals', 'administrator', 'zpt-metals-main','zpt_metals_admin_settings_page', 'dashicons-money-alt');
    
    add_submenu_page('zpt-metals-main', 'ZPT Metals Settings', 'Settings', 'administrator', 'zpt-metals-settings','zpt_metals_admin_settings_page');
    
    add_submenu_page('zpt-metals-main', 'Learn Shortcode', 'Shortcode', 'administrator', 'zpt-metals-info','zpt_metals_admin_info_page');
    
    remove_submenu_page("zpt-metals-main", "zpt-metals-main");
}


function zpt_metals_admin_main_page(){
    
}

function zpt_metals_admin_settings_page(){
    
    
    if( isset ( $_POST['zpt_metal_api_settings_update'] ) ) {
        
        if ( ! isset( $_POST['zpt_metals_nonce'] ) 
            || ! wp_verify_nonce( $_POST['zpt_metals_nonce'] ) 
        ) {
            wp_die ( "Invalid Nonce. Reload the page and try again!" );
        }
        
        
        if( isset( $_POST['zpt_metal_api_key'] ) ){
            
            /* Sanitizing API key */
            $zpt_metal_api_key = sanitize_text_field( $_POST['zpt_metal_api_key'] );
            
            update_option( 'zpt_metal_api_key', $zpt_metal_api_key );
            
        }
       
        if( isset( $_POST['zpt_metal_api_date'] ) ){
            
            /* only allowing integers */
            $zpt_zpt_metal_api_date = sanitize_text_field( $_POST['zpt_metal_api_date'] );
            
            update_option( 'zpt_metal_api_date', $zpt_zpt_metal_api_date );
            
        }
        
        if( isset( $_POST['zpt_metal_api_success_display'] ) ) {
            
            /* allow HTML tags to be saved as a part of shortcode layout */
            $metal_api_success_display = wp_kses_post( $_POST['zpt_metal_api_success_display'] );
            
            update_option( 'zpt_metal_api_success_display', $metal_api_success_display );
            
        }
        
        if( isset( $_POST['zpt_metal_api_error_display'] ) ) {
            
            /* allow HTML tags to be saved as a part of shortcode layout */
            $zpt_metal_api_error_display = wp_kses_post( $_POST['zpt_metal_api_error_display'] );
            
            update_option( 'zpt_metal_api_error_display', $zpt_metal_api_error_display );
            
        }
        
        if( isset( $_POST['zpt_metal_api_empty_display'] ) ) {
            
            /* allow HTML tags to be saved as a part of shortcode layout */
            $zpt_metal_api_empty_display = wp_kses_post( $_POST['zpt_metal_api_empty_display'] );
            
            update_option( 'zpt_metal_api_empty_display', $zpt_metal_api_empty_display );
            
        }
        
        if( isset( $_POST['zpt_metals_cpanel_cron_checkbox'] ) ) {
            
            /* cPanelcron job check */
            $zpt_metals_cpanel_cron_checkbox = sanitize_text_field( $_POST['zpt_metals_cpanel_cron_checkbox'] );
            
            update_option( 'zpt_metals_cpanel_cron_checkbox', $zpt_metals_cpanel_cron_checkbox );
            
        }
        else{
            update_option( 'zpt_metals_cpanel_cron_checkbox', '0' );
        }
        
    }
    
    $last_ran_at = get_option("zpt_metal_api_last_ran");
    
    $zpt_api_status = get_option('zpt_metal_api_error_found');
    
    ?>
    <style>
        /* The switch - the box around the slider */
        .switch {
          position: relative;
          display: inline-block;
          width: 60px;
          height: 34px;
        }
        
        /* Hide default HTML checkbox */
        .switch input {
          opacity: 0;
          width: 0;
          height: 0;
        }
        
        /* The slider */
        .slider {
          position: absolute;
          cursor: pointer;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: #ccc;
          -webkit-transition: .4s;
          transition: .4s;
        }
        
        .slider:before {
          position: absolute;
          content: "";
          height: 26px;
          width: 26px;
          left: 4px;
          bottom: 4px;
          background-color: white;
          -webkit-transition: .4s;
          transition: .4s;
        }
        
        input:checked + .slider {
          background-color: #2196F3;
        }
        
        input:focus + .slider {
          box-shadow: 0 0 1px #2196F3;
        }
        
        input:checked + .slider:before {
          -webkit-transform: translateX(26px);
          -ms-transform: translateX(26px);
          transform: translateX(26px);
        }
        
        /* Rounded sliders */
        .slider.round {
          border-radius: 34px;
        }
        
        .slider.round:before {
          border-radius: 50%;
        }
    </style>    
    <div class="wrap zpt-metals">
        <h1>ZPT Metals Settings</h1>
        <hr>
        <form class="" method="post">
            <?php wp_nonce_field(-1, "zpt_metals_nonce");?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th>Metals Api Key</th>
                        <td>
                            <input type="text" name="zpt_metal_api_key" value="<?=get_option('zpt_metal_api_key');?>" class="regular-text">
                            <p class="description">API status: <span><?=isset($zpt_api_status) && trim($zpt_api_status) !="" ? $zpt_api_status : "Not connected!";?></span></p>
                            <p class="description">You can get your API key by registering at <a href="https://metals-api.com" target="_blank">Metals API</a></p>
                        </td>
                    </tr>
                    <tr>
                        <th>Refresh API data(rates) duration(in minutes)</th>
                        <td>
                            <!-- <input type="text" name="zpt_metal_api_date" value="<?=get_option('zpt_metal_api_date')?>" placeholder="1440" class="regular-text"> -->
                            <select class="regular-text zpt_metal_api_date" name="zpt_metal_api_date">
                                <option value="2min">2 Minutes</option>
                                <option value="5min">5 Minutes</option>
                                <option value="10min">10 Minutes</option>
                                <option value="15min">15 Minutes</option>
                                <option value="30min">30 Minutes</option>
                                <option value="45min">45 Minutes</option>
                                <option value="1hour">1 Hour</option>
                                <option value="2our">2 Hours</option>
                                <option value="3hour">3 Hours</option>
                                <option value="4hour">4 Hours</option>
                                <option value="5hour">5 Hours</option>
                                <option value="6hour">6 Hours</option>
                                <option value="10hour">10 Hours</option>
                                <option value="12hour">12 Hours</option>
                                <option value="15hour">15 Hours</option>
                                <option value="20hour">20 Hours</option>
                                <option value="24hour">24 Hours</option>
                                <option value="2days">2 Days</option>
                                <option value="3days">3 Days</option>
                                <option value="5days">5 Days</option>
                            </select>
                            <p class="description">Plugin will request latest rates from API after above set minutes from its last run. <?=(isset($last_ran_at) && trim($last_ran_at)!="" ? "Last ran at: ".date("Y-m-d, H:i:s A", $last_ran_at) : "" ) ;  ?></p>
                            <p class="description button"><a onclick="location.reload(true)">Reload</a></p>
                        </td>
                    </tr>
                    <tr>
                        <th>Enable cPanel cron job</th>
                        <td>
                            <!-- Rounded switch -->
                            <label class="switch">
                                <input type="checkbox" name="zpt_metals_cpanel_cron_checkbox" class="zpt_metals_cpanel_cron_checkbox"
                                    <?php if(get_option('zpt_metals_cpanel_cron_checkbox') == "1"){echo 'value="1" checked';}else{echo 'value="0"';} ?>
                                >
                                <span class="slider round"></span>
                            </label>
                            <p class="zpt_metals_cpanel_cron_url" <?php if(get_option('zpt_metals_cpanel_cron_checkbox') == "0"){echo 'style="display:none;"';} ?>>Copy the following URL and add it into the cPanel Cron Jobs: <br><code><?=home_url()?>/wp-content/plugins/zpt-metals/backend/zpt-cron.php</code></p>
                        </td>    
                    </tr>
                    
                </tbody>
            </table>
            <h3>Shortcode results</h3>
            <p>Use below controls to print your desired content in the short code. API can return with success, failure or no data. So please fill up your desired data for thrice cases. You can also use below provided codes to display dynamic content.</p>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><span class="success">Success</span></th>
                        <td>
                            <?php wp_editor( 
                                        stripslashes ( get_option("zpt_metal_api_success_display" ) ), 
                                        "zpt_metal_api_success_display", 
                                        array( 
                                            "textarea_name" =>"zpt_metal_api_success_display",
                                            "textarea_rows" => 4
                                        )
                                ); 
                            ?>
                            <p class="description">You can use [base], [timestamp], [price], [currency], [unit], [date] in above textarea</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><span class="error">Error</span></th>
                        <td>
                            <?php wp_editor( 
                                        stripslashes( get_option("zpt_metal_api_error_display") ), 
                                        "zpt_metal_api_error_display", 
                                        array( 
                                            "textarea_name" =>"zpt_metal_api_error_display",
                                            "textarea_rows" =>  4
                                        )
                                ); 
                            ?>
                            <p class="description">You can use [error] to display API error to user</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th><span class="empty">No data(N/A)</span></th>
                        <td>
                            <?php wp_editor( 
                                        stripslashes( get_option("zpt_metal_api_empty_display") ), 
                                        "zpt_metal_api_empty_display", 
                                        array( 
                                            "textarea_name" =>"zpt_metal_api_empty_display",
                                            "textarea_rows" =>4
                                        )
                                ); 
                            ?>
                        </td>
                    </tr>
                    
                </tbody> 
            </table>
            <p>
                <button type="submit" class="button button-primary" name="zpt_metal_api_settings_update">Submit</button>
            </p>
        </form>
    </div>
    <script>
        jQuery(document).on("change",".zpt_metals_cpanel_cron_checkbox", function(){
            if(jQuery(this).is(":checked")){
                jQuery(this).val("1");
                jQuery(".zpt_metals_cpanel_cron_url").show();
            }
            else{
                jQuery(".zpt_metals_cpanel_cron_url").hide();
                jQuery(this).val("0");
            }
            
        });
        

        jQuery(document).ready(function(){

            jQuery('select.zpt_metal_api_date').val('<?=get_option( 'zpt_metal_api_date' )?>').prop('checked', true);

        });

    </script>
    <?php
}


function zpt_metals_admin_info_page(){
?><style>.zpt-metals.wrap ul > li{margin-left:15px;}</style>
    <div class="wrap zpt-metals">
        <h2>Short code details</h2>
        <hr>
        <div>
            <p>Use shortcode <code>[zpt-metals]</code> to display metal rates on your wp website.</p>
            <p>Following are the params that you can pass to display your desired shortcode output!</p>
            <ul style="list-style: circle;">
                <li>
                    <strong>type</strong>
                    <p>Its required to display which metal rates you want to display. Possible value can be any of; 
                    Gold
                    , Silver
                    , Platinum
                    , Palladium
                    , Rhodium
                    , Ruthenium
                    , Copper
                    , Aluminum
                    , Nickel
                    , Zinc
                    , Tin
                    , Cobalt
                    , Iridium
                    , Lead
                    , Iron Ore
                    , LBMA GOLD AM
                    , LBMA GOLD PM
                    , LBMA Platinum AM
                    , LBMA Platinum PM
                    , LBMA Palladium AM
                    , LBMA Palladium PM
                    , LME Aluminium
                    , LME Copper
                    , LME Zinc
                    , LME Nickel
                    , LME Lead
                    , LME Tin
                    , Uranium
                    , STEEL-SC
                    , STEEL-RE
                    , STEEL-HR
                    , BRONZE
                    , MG
                    , OSMIUM
                    , RHENIUM
                    , INDIUM
                    , MO
                    , TUNGSTEN.</p>
                    <p>Example: <code>[zpt-metals type="silver"]</code></p>
                    <p></p>
                    <p>Only for Gold: if you want to display <strong>Gold rates by Carat</strong> pass <code>carat</code> attribute to the short code.</p>        
                    <p>Example: <code>[zpt-metals type="gold" carat="enable"]</code> <strong>OR</strong> you can pass the Carat Value like 24k, 23k, 22k Example: <code>[zpt-metals type="gold" carat="Carat 24K"]</code></p>
                    <p>If <code>carat="enable"</code> then default Gold rate is in <strong>24k</strong>.</p>
                </li>
                <li>
                    <strong>date-format</strong>
                    <p>Its optional to display desired date format. Possible value can be any of; Y-m-d, m-d-Y etc. <a target="_blank" href="https://www.w3schools.com/php/func_date_date_format.asp">Find a complete list of date format</a></p>
                    <p>Example: <code>[zpt-metals date-format="Y-m-d"]</code></p>
                </li>
                <li>
                    <strong>base</strong>
                    <p>Its optional to display rates of metal in a specific currency. Possible value can be any of; USD, GBP etc. <a href="https://metals-api.com/currencies" target="_blank">Find Complete list of currency codes</a></p>
                    <p>Example: <code>[zpt-metals base="GBP"]</code></p>
                </li>
                <li>
                    <strong>price-round</strong>
                    <p>Its optional to display desired digits after decimal. Possible value can be any of integer.</p>
                    <p>Example: <code>[zpt-metals price-round="2"]</code></p>
                </li>
                <li>
                    <strong>date</strong>
                    <p>Its optional to display rates for a specific date. Possible value can be a date(YYYY-MM-DD) format.</p>
                    <p>Example: <code>[zpt-metals date="<?=date("Y-m-d", strtotime("yesterday"))?>"]</code></p>
                    <p>Note: <strong>Date</strong> param can not be worked if <strong>carat</strong> param is in use.</p>
                </li>
            </ul>
        </div>
        <hr>
        <h3>Full shortcode example</h3>
        <code>[zpt-metals type="gold" date-format="F jS, Y, H:i a" base="USD" price-round="3"]</code>
    </div>

<?php
}


if(get_option('zpt_metals_cpanel_cron_checkbox') != "1" || get_option('zpt_metals_cpanel_cron_checkbox') == ""){
    
    /* 
    * Wp Cron job custom interval for scheduled tasks 
    */
    function zpt_metals_cron_custom_schedules($schedules){
    
        $interval = get_option( 'zpt_metal_api_date' );
    
        // $interval = ( ! empty( $minutes ) ? absint( $minutes ) * HOUR_IN_SECONDS : DAY_IN_SECONDS );
        
        if( $interval == "2min" ){

            if(!isset($schedules["2min"])){
                $schedules["2min"] = array(
                   'interval' => 2 * MINUTE_IN_SECONDS,
                   'display' => __( 'Once every 2 minutes' )
                );
            }

        }

        else if( $interval == "5min" ){

            if(!isset($schedules["5min"])){
                $schedules["5min"] = array(
                   'interval' => 5 * MINUTE_IN_SECONDS,
                   'display' => __( 'Once every 5 minutes' )
                );
            }

        }

        else if( $interval == "10min" ){

            if(!isset($schedules["10min"])){
                $schedules["10min"] = array(
                   'interval' => 10 * MINUTE_IN_SECONDS,
                   'display' => __( 'Once every 10 minutes' )
                );
            }

        }

        else if( $interval == "15min" ){

            if(!isset($schedules["15min"])){
                $schedules["15min"] = array(
                   'interval' => 15 * MINUTE_IN_SECONDS,
                   'display' => __( 'Once every 15 minutes' )
                );
            }

        }

        else if( $interval == "30min" ){

            if(!isset($schedules["30min"])){
                $schedules["30min"] = array(
                   'interval' => 30 * MINUTE_IN_SECONDS,
                   'display' => __( 'Once every 30 minutes' )
                );
            }

        }

        else if( $interval == "45min" ){

            if(!isset($schedules["45min"])){
                $schedules["45min"] = array(
                   'interval' => 45 * MINUTE_IN_SECONDS,
                   'display' => __( 'Once every 45 minutes' )
                );
            }

        }

        else if( $interval == "1hour" ){

            if(!isset($schedules["1hour"])){
                $schedules["1hour"] = array(
                   'interval' => 1 * HOUR_IN_SECONDS,
                   'display' => __( 'Once every 1 Hour' )
                );
            }

        }

        else if( $interval == "2hour" ){

            if(!isset($schedules["2hour"])){
                $schedules["2hour"] = array(
                   'interval' => 2 * HOUR_IN_SECONDS,
                   'display' => __( 'Once every 2 Hours' )
                );
            }

        }

        else if( $interval == "3hour" ){

            if(!isset($schedules["3hour"])){
                $schedules["3hour"] = array(
                   'interval' => 3 * HOUR_IN_SECONDS,
                   'display' => __( 'Once every 3 Hours' )
                );
            }

        }

        else if( $interval == "4hour" ){

            if(!isset($schedules["4hour"])){
                $schedules["4hour"] = array(
                   'interval' => 4 * HOUR_IN_SECONDS,
                   'display' => __( 'Once every 4 Hours' )
                );
            }

        }

        else if( $interval == "5hour" ){

            if(!isset($schedules["5hour"])){
                $schedules["5hour"] = array(
                   'interval' => 5 * HOUR_IN_SECONDS,
                   'display' => __( 'Once every 5 Hours' )
                );
            }

        }

        else if( $interval == "6hour" ){

            if(!isset($schedules["6hour"])){
                $schedules["6hour"] = array(
                   'interval' => 6 * HOUR_IN_SECONDS,
                   'display' => __( 'Once every 6 Hours' )
                );
            }

        }

        else if( $interval == "10hour" ){

            if(!isset($schedules["10hour"])){
                $schedules["10hour"] = array(
                   'interval' => 10 * HOUR_IN_SECONDS,
                   'display' => __( 'Once every 10 Hours' )
                );
            }

        }

        else if( $interval == "12hour" ){

            if(!isset($schedules["12hour"])){
                $schedules["12hour"] = array(
                   'interval' => 12 * HOUR_IN_SECONDS,
                   'display' => __( 'Once every 12 Hours' )
                );
            }

        }

        else if( $interval == "15hour" ){

            if(!isset($schedules["15hour"])){
                $schedules["15hour"] = array(
                   'interval' => 15 * HOUR_IN_SECONDS,
                   'display' => __( 'Once every 15 Hours' )
                );
            }

        }

        else if( $interval == "20hour" ){

            if(!isset($schedules["20hour"])){
                $schedules["20hour"] = array(
                   'interval' => 20 * HOUR_IN_SECONDS,
                   'display' => __( 'Once every 20 Hours' )
                );
            }

        }

        else if( $interval == "24hour" ){

            if(!isset($schedules["24hour"])){
                $schedules["24hour"] = array(
                   'interval' => 24 * HOUR_IN_SECONDS,
                   'display' => __( 'Once every 24 Hours' )
                );
            }

        }

        else if( $interval == "2days" ){

            if(!isset($schedules["2days"])){
                $schedules["2days"] = array(
                   'interval' => 2 * DAY_IN_SECONDS,
                   'display' => __( 'Once every 2 Days' )
                );
            }

        }

        else if( $interval == "3days" ){

            if(!isset($schedules["3days"])){
                $schedules["3days"] = array(
                   'interval' => 3 * DAY_IN_SECONDS,
                   'display' => __( 'Once every 3 Days' )
                );
            }

        }

        else if( $interval == "5days" ){

            if(!isset($schedules["5days"])){
                $schedules["5days"] = array(
                   'interval' => 5 * DAY_IN_SECONDS,
                   'display' => __( 'Once every 5 Days' )
                );
            }

        }
        
        return $schedules;
    }

    add_filter('cron_schedules','zpt_metals_cron_custom_schedules');
    
    /* 
    * Task scheduler 
    */
    $interval = get_option( 'zpt_metal_api_date' );
        
    if( $interval == "2min" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '2min', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "5min" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '5min', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "10min" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '10min', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "15min" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '15min', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "30min" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '30min', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "45min" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '45min', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "1hour" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '1hour', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "2hour" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '2hour', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "3hour" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '3hour', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "4hour" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '4hour', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "5hour" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '5hour', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "6hour" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '6hour', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "10hour" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '10hour', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "12hour" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '12hour', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "15hour" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '15hour', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "20hour" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '20hour', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "24hour" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '24hour', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "2days" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '2days', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "3days" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '3days', 'zpt_metals_custom_task_hook' );

        }

    }

    else if( $interval == "5days" ){

        if (!wp_next_scheduled('zpt_metals_custom_task_hook')) {

            wp_schedule_event( time(),  '5days', 'zpt_metals_custom_task_hook' );

        }

    }
    
    add_action ( 'zpt_metals_custom_task_hook', 'zpt_metals_shortcode_func' );
    
}    

/**
 * Add a custom product data tab
 */
add_filter( 'woocommerce_product_data_tabs', 'zpt_metals_custom_product_data_tab' );
function zpt_metals_custom_product_data_tab( $product_data_tabs ) {
    $product_data_tabs['zpt_product_auto_pricing'] = array(
        'label' => __( 'Product Auto Pricing', '' ),
        'target' => 'zpt_metals_custom_product_data',
        'priority' => 25
    );
    return $product_data_tabs;
}

add_action( 'woocommerce_product_data_panels', 'zpt_metals_custom_product_data_fields' );
function zpt_metals_custom_product_data_fields($post_id) {
    ?> 
    <style>
        .woocommerce ul.wc-tabs li.zpt_product_auto_pricing_tab a::before {
            font-family: Dashicons;
            content: "\f111" !important;
        }
        /* The switch - the box around the slider */
        .switch {
          position: relative;
          display: inline-block;
          width: 60px !important;
          height: 34px;
          position: absolute;
          top: 10%;
          right: 47%;
        }
        
        /* Hide default HTML checkbox */
        .switch input {
          opacity: 0;
          width: 0;
          height: 0;
        }
        
        /* The slider */
        .slider {
          position: absolute;
          cursor: pointer;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: #ccc;
          -webkit-transition: .4s;
          transition: .4s;
        }
        
        .slider:before {
          position: absolute;
          content: "";
          height: 26px;
          width: 26px;
          left: 4px;
          bottom: 4px;
          background-color: white;
          -webkit-transition: .4s;
          transition: .4s;
        }
        
        input:checked + .slider {
          background-color: #2196F3;
        }
        
        input:focus + .slider {
          box-shadow: 0 0 1px #2196F3;
        }
        
        input:checked + .slider:before {
          -webkit-transform: translateX(26px);
          -ms-transform: translateX(26px);
          transform: translateX(26px);
        }
        
        /* Rounded sliders */
        .slider.round {
          border-radius: 34px;
        }
        
        .slider.round:before {
          border-radius: 50%;
        }
    </style>
    <div id = 'zpt_metals_custom_product_data' class = 'panel woocommerce_options_panel' > 
        <div class="wrap zpt-metals">
            <!-- <h1>Enable Auto Pricing</h1> -->
            <hr>
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th>Enable Auto Pricing</th>
                        <td>
                            <!-- Rounded switch -->
                            <label class="switch">
                                <input type="checkbox" name="zpt_metals_auto_pricing_checkbox" class="zpt_metals_auto_pricing_checkbox"
                                    <?php if(get_post_meta(get_the_ID(),'zpt_metals_auto_pricing_checkbox',true) == "1"){echo 'value="1" checked';}else{echo 'value="0"';} ?>
                                >
                                <span class="slider round"></span>
                            </label>
                        </td>    
                    </tr>
                    <tr class="zpt_metal_connect_tr" <?php if(get_post_meta(get_the_ID(),'zpt_metals_auto_pricing_checkbox',true) == "0"){echo 'style="display:none;"';} ?>>
                        <th>Connect Metal</th>
                        <td>
                            <select class="regular-text zpt_metal_connect" name="zpt_metal_connect">
                                <option disabled selected="selected" value="">Select Metal</option>
                                <option value="XAU">Gold</option>
                                <option value="XAG">Silver</option>
                                <option value="XPT">Platinum</option>
                                <option value="XPD">Palladium</option>
                                <option value="XRH">Rhodium</option>
                                <option value="RUTH">Ruthenium</option>
                                <option value="XCU">Copper</option>
                                <option value="NI">Nickel</option>
                                <option value="ALU">Aluminium</option>
                                <option value="ZNC">Zinc</option>
                                <option value="TIN">Tin</option>
                                <option value="LCO">Cobalt</option>
                                <option value="IRD">Iridium</option>
                                <option value="LEAD">Lead</option>
                                <option value="IRON">Iron Ore</option>
                                <option value="LBXAUAM">LBMA GOLD AM</option>
                                <option value="LBXAUPM">LBMA GOLD PM</option>
                                <option value="LBXPTAM">LBMA Platinum AM</option>
                                <option value="LBXPTPM">LBMA Platinum PM</option>
                                <option value="LBXPDAM">LBMA Palladium AM</option>
                                <option value="LBXPDPM">LBMA Palladium PM</option>
                                <option value="LME-ALU">LME Aluminium</option>
                                <option value="LME-XCU">LME Copper</option>
                                <option value="LME-ZNC">LME Zinc</option>
                                <option value="LME-NI">LME Nickel</option>
                                <option value="LME-LEAD">LME Lead</option>
                                <option value="LME-TIN">LME Tin</option>
                                <option value="URANIUM">Uranium</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div> 
    <script>
        jQuery(document).on("change",".zpt_metals_auto_pricing_checkbox", function(){
            if(jQuery(this).is(":checked")){
                jQuery(this).val("1");
                jQuery(".zpt_metal_connect_tr").show();
            }
            else{
                jQuery(".zpt_metal_connect_tr").hide();
                jQuery(this).val("0");
                jQuery('select.zpt_metal_connect').val('').prop('checked', true);
            }
            
        });
        

        jQuery(document).ready(function(){

            jQuery('select.zpt_metal_connect').val('<?=get_post_meta(get_the_ID(),'zpt_metal_connect',true)?>').prop('checked', true);

        });

    </script>   
<?php
}

/* 
* Hook callback function to save custom fields information 
*/
function zpt_metals_save_proddata_custom_fields($post_id) {

    /* 
    * Save auto_pricing_checkbox Field value
    */
    if( isset( $_POST['zpt_metals_auto_pricing_checkbox'] ) ) {
            
        $zpt_metals_auto_pricing_checkbox = sanitize_text_field( $_POST['zpt_metals_auto_pricing_checkbox'] );
        
        update_post_meta($post_id, 'zpt_metals_auto_pricing_checkbox', $zpt_metals_auto_pricing_checkbox );
        
    }
    else{
        update_post_meta($post_id, 'zpt_metals_auto_pricing_checkbox', '0' );
    }

    /* 
    * Save zpt_metal_connect select tbox value
    */
    if( isset( $_POST['zpt_metal_connect'] ) ) {
            
        $zpt_metals_metal_connect = sanitize_text_field( $_POST['zpt_metal_connect'] );
        
        update_post_meta($post_id, 'zpt_metal_connect', $zpt_metals_metal_connect );
        
    }
    else{
        update_post_meta($post_id, 'zpt_metal_connect', '' );
    }
}
add_action( 'woocommerce_process_product_meta_simple', 'zpt_metals_save_proddata_custom_fields'  );

function zpt_metal_return_price($price, $product) {
    global $post, $blog_id;
    $product = wc_get_product( $post_id );
    $post_id = $post->ID;

    $access_key = get_option('zpt_metal_api_key');
    
    $endpoint = 'latest';

    $metal = get_post_meta(get_the_ID(),'zpt_metal_connect',true);
    
    $metals_api_uri = "https://metals-api.com/api/$endpoint?access_key=$access_key&base=USD&symbols=$metal&utm=zactonz";

    if(get_post_meta($post_id,'zpt_metals_auto_pricing_checkbox',true) == "1"){ 
        
        /*
         Respect API request limits. So serve database saved results until database
         saved rates are older(set by admin from plugin admin area)
        */
        
        $when_last_ran = get_option("zpt_metal_api_last_ran");
        
        $is_data_older_now = false;
        
        if( isset( $when_last_ran ) && trim( $when_last_ran ) !="" ){
            $threshold = strtotime(get_option('zpt_metal_api_date'));
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

        if( isset( $body ) && trim( $body ) != "" ) {
        
            $response_arr = json_decode( $body, true );
            if( isset( $response_arr ) && !empty( $response_arr ) ){
              
                if( isset( $response_arr["success"] ) && $response_arr["success"] == true ){

                    if( isset( $response_arr["rates"][ $metal ]) ) {
                            
                        /* 
                        NOTE: All the metals rates you get need using USD as a base currency need to be divided by 1
                        We return the values based on the base currency. For example, for 1 USD the return is a number like 0.000634 for Gold (XAU).
                        To get the gold rate per troy ounce in USD: 1/0.000634= 1577.28 USD
                        */
                        if( isset( $response_arr["rates"][ $response_arr["base"] ] ) && $response_arr["base"] == "USD"){
                        
                            $price_factor = $response_arr["rates"][ $response_arr["base"] ];
                            
                            $priced = $price_factor /  $response_arr["rates"][ $metal ] ;
                            
                        }else{
                            
                            $priced = $response_arr["rates"][ $metal ] ;
                            
                        }
                    
                    }

                }
                else{
                
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

        $price = $priced;
    }    
    return $price;
}
add_filter('woocommerce_get_price', 'zpt_metal_return_price', 10, 2);

add_action( 'woocommerce_before_calculate_totals', 'zpt_metal_auto_price' );
add_action( 'woocommerce_before_cart', 'zpt_metal_auto_price' );

function zpt_metal_auto_price( $cart_object ) {

    $access_key = get_option('zpt_metal_api_key');
    
    $endpoint = 'latest';
    
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

        if(get_post_meta($cart_item['product_id'],'zpt_metals_auto_pricing_checkbox',true) == "1"){

            $metal = get_post_meta($cart_item['product_id'],'zpt_metal_connect',true);

            $metals_api_uri = "https://metals-api.com/api/$endpoint?access_key=$access_key&base=USD&symbols=$metal&utm=zactonz";
        
            /*
             Respect API request limits. So serve database saved results until database
             saved rates are older(set by admin from plugin admin area)
            */
            
            $when_last_ran = get_option("zpt_metal_api_last_ran");
            
            $is_data_older_now = false;
            
            if( isset( $when_last_ran ) && trim( $when_last_ran ) !="" ){
                $threshold = strtotime(get_option('zpt_metal_api_date'));
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

            if( isset( $body ) && trim( $body ) != "" ) {
            
                $response_arr = json_decode( $body, true );
                if( isset( $response_arr ) && !empty( $response_arr ) ){
                  
                    if( isset( $response_arr["success"] ) && $response_arr["success"] == true ){

                        if( isset( $response_arr["rates"][ $metal ]) ) {
                                
                            /* 
                            NOTE: All the metals rates you get need using USD as a base currency need to be divided by 1
                            We return the values based on the base currency. For example, for 1 USD the return is a number like 0.000634 for Gold (XAU).
                            To get the gold rate per troy ounce in USD: 1/0.000634= 1577.28 USD
                            */
                            if( isset( $response_arr["rates"][ $response_arr["base"] ] ) && $response_arr["base"] == "USD"){
                            
                                $price_factor = $response_arr["rates"][ $response_arr["base"] ];
                                
                                $priced = $price_factor /  $response_arr["rates"][ $metal ] ;
                                
                            }else{
                                
                                $priced = $response_arr["rates"][ $metal ] ;
                                
                            }
                        
                        }

                    }
                    else{
                    
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

            if( isset( $priced ) && $priced != 0){
                
                $cart_item['data']->set_price($priced); 
            }
        }      
    }
}