<?php

class WPAM_Click_Tracking {

    public static function record_click() {
        if (isset($_REQUEST[WPAM_PluginConfig::$RefKey]) && !empty($_REQUEST[WPAM_PluginConfig::$RefKey])) {
            global $wpdb;
            $strRefKey = trim(strip_tags($_REQUEST[WPAM_PluginConfig::$RefKey]));
            $aff_id = "";
            if (is_numeric($strRefKey)) {  //wpam_refkey contains affiliate ID. If a record is found save it
                $query = "SELECT * FROM ".WPAM_AFFILIATES_TBL." WHERE affiliateId = %d";        
                $affiliate = $wpdb->get_row($wpdb->prepare($query, $strRefKey));
                if($affiliate != null && $affiliate->status == "active") {
                    $aff_id = $strRefKey;
                }
            } 
            else if (is_email($strRefKey)) {  //wpam_refkey contains email. Find the ID associated with that email and save it
                $query = "SELECT * FROM ".WPAM_AFFILIATES_TBL." WHERE email = %s";        
                $affiliate = $wpdb->get_row($wpdb->prepare($query, $strRefKey));
                if($affiliate != null && $affiliate->status == "active") {
                    $aff_id = $affiliate->affiliateId;
                }
            }
            else{   //TODO start - wpam_refkey contains long tracking key. Find affiliate ID from it and save it. This block of code will just be here for backwards compatibilty
                $refKey = new WPAM_Tracking_TrackingKey();
                $refKey->unpack($strRefKey);
                $db = new WPAM_Data_DataAccess();
                $affiliateRepos = $db->getAffiliateRepository();
                $affiliateId = $affiliateRepos->getAffiliateIdFromRefKey($refKey->getAffiliateRefKey());
                if ($affiliateId === NULL) {
                    
                }
                else{
                    $aff_id = $affiliateId;
                }
            }
            //TODO end
            if(!empty($aff_id)){
                $cookie_life_time = wpam_get_cookie_life_time();
                $domain_url = $_SERVER['SERVER_NAME'];
                $cookie_domain = str_replace("www", "", $domain_url);
                setcookie('wpam_id', $aff_id, $cookie_life_time, "/", $cookie_domain);
            }
        }
    }

}
