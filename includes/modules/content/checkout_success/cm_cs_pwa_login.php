<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2014 osCommerce

  Released under the GNU General Public License
*/

  class cm_cs_pwa_login {
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function cm_cs_pwa_login() {
      $this->code = get_class($this);
      $this->group = basename(dirname(__FILE__));

      $this->title = MODULE_CONTENT_CHECKOUT_SUCCESS_PWA_LOGIN_TITLE;
      $this->description = MODULE_CONTENT_CHECKOUT_SUCCESS_PWA_LOGIN_DESCRIPTION;

      if ( defined('MODULE_CONTENT_CHECKOUT_SUCCESS_PWA_LOGIN_STATUS') ) {
        $this->sort_order = MODULE_CONTENT_CHECKOUT_SUCCESS_PWA_LOGIN_SORT_ORDER;
        $this->enabled = (MODULE_CONTENT_CHECKOUT_SUCCESS_PWA_LOGIN_STATUS == 'True');
      }
    }

    function execute() {
      global $HTTP_GET_VARS, $HTTP_POST_VARS, $oscTemplate, $customer_id, $order_id;

	  
      if (tep_session_is_registered('customer_is_guest')){
	  
	    // get product info to diplay
          $products_query = tep_db_query("select products_id, products_name from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "' order by products_name");
          while ($products = tep_db_fetch_array($products_query)) {
            if ( !isset($products_displayed[$products['products_id']]) ) {
              $products_displayed[$products['products_id']] = '<div><label> ' . $products['products_name'] . '</label></div>';
            }
          }

        $products_notifications = implode('', $products_displayed);
		
        tep_db_query("update " . TABLE_ORDERS . " set customers_guest = '1' where customers_id = '" . (int)$customer_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "' and customers_guest = '1'");
        tep_db_query("delete from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int)$customer_id . "'");
  
        tep_session_unregister('customer_default_address_id');
        tep_session_unregister('customer_first_name');
        tep_session_unregister('customer_country_id');
        tep_session_unregister('customer_zone_id');
        tep_session_unregister('customer_is_guest');
  

        ob_start();
        include(DIR_WS_MODULES . 'content/' . $this->group . '/templates/pwa_login.php');
        $template = ob_get_clean();

        $oscTemplate->addContent($template, $this->group);

        if (DOWNLOAD_ENABLED == 'false') {
          // by unregistering customer_id product notifcations will be disabled
          tep_session_unregister('customer_id');
        }

		}
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_CONTENT_CHECKOUT_SUCCESS_PWA_LOGIN_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PWA Checkout Module', 'MODULE_CONTENT_CHECKOUT_SUCCESS_PWA_LOGIN_STATUS', 'True', 'Must enable if PWA Login module is active to integrate within checkout success page.', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_CHECKOUT_SUCCESS_PWA_LOGIN_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.  Due to disabling product notifications, this module requires being installed above said module.', '6', '3', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_CONTENT_CHECKOUT_SUCCESS_PWA_LOGIN_STATUS','MODULE_CONTENT_CHECKOUT_SUCCESS_PWA_LOGIN_SORT_ORDER');
    }
  }
?>
