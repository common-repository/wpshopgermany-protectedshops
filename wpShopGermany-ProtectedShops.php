<?php

	/*
	Plugin Name: wpShopGermany - Protected Shops
	Plugin URI: http://wpshopgermany.maennchen1.de/
	Description: Protected shops integration
	Author: maennchen1.de
	Version: 2.2
	Author URI: http://maennchen1.de/
	*/

	error_reporting(E_ERROR);

	class wpsg_protected_shops
	{
		
		var	$SC = false;
		
		public function __construct($SC = false)
		{
			
			$this->SC = $SC;
			
			$arPages = $this->getFormConfig();
			
			foreach ($arPages as $p)
			{
				
				if ($p[5] !== false)
				{
					
					add_shortcode($p[5], array(&$this, 'sc_'.$p[5]));
				
				}
				
			}
			
		} // public function __construct($SC = false)
		
		function dispatch()
		{
			
			if (isset($_REQUEST['form_submit']))
			{
				
				$this->saveForm();
				
			}
			
			?>
<style type="text/css">
	
	table.wpsg_ps_table tr td { text-align:left; }
	table.wpsg_ps_table tr th { text-align:left; width:343px; }
	
	table.wpsg_ps_table input[type="text"] { width:100%; }
	table.wpsg_ps_table select.wpsg_ps_page { width:73%; }
  table.wpsg_ps_table select.wpsg_ps_format { float:right; width:23%; }
  table.wpsg_ps_table input[type="checkbox"] { float:left; }
  
  .wpsg_ps_update { float:right; }
	.wpsg_ps_status { float:left; }
		 
</style>			
			
<form method="POST">
	<table style="width:800px; margin:15px;" class="wpsg_ps_table">
		<div class="wrap">
		<div class="icon32" id="icon-themes"><br></div>
		<h2>WpShopGermany - Protected Shops Integration <a href="http://wpshopgermany.maennchen1.de/2010/06/20/protected-shops/">?</a></h2>
		<?php echo $this->showForm(); ?>
	</table>
</form>
			<?php
			
		} // function dispatch()
		
		function getFormConfig()
		{
			
			// Name, API KEY, OptionField, Format, Page|false, Shortcode
			
			$arPages = array(
				array("AGB", "AGB", "wpsg_agb", "wpsg_agb_format", 'wpsg_page_agb', 'wpsg_ps_agb'),
				array("Datenschutz", "Datenschutz", "wpsg_datenschutz", "wpsg_datenschutz_format", 'wpsg_page_datenschutz', 'wpsg_ps_datenschutz'),				
				array("Widerruf", "Widerruf", "wpsg_widerrufsbelehrung", "wpsg_widerrufsbelehrung_format", 'wpsg_page_widerrufsbelehrung', 'wpsg_ps_widerrufsbelehrung'),
				array("Impressum", "Impressum", "wpsg_impressum", "wpsg_impressum_format", 'wpsg_page_impressum', 'wpsg_ps_impressum'),
				array("Versandinfo", "Versandinfo", "wpsg_versand", "wpsg_versand_format", 'wpsg_page_versand', 'wpsg_ps_versand'),
				array("Batteriegesetz", "Batteriegesetz", "wpsg_battery", "wpsg_battery_format", 'wpsg_page_battery', 'wpsg_ps_battery'),
				array("Rueckgabe", "Rueckgabe", "wpsg_rueckgabe", "wpsg_rueckgabe_format", 'wpsg_page_rueckgabe', 'wpsg_ps_rueckgabe'),
			);
			
			if (get_option('wpsg_installed') > 0)
			{
				
				$arPages[] = array("Widerruf E-Mail (Text)", "Widerruf", "wpsg_ps_mailwiderruf", false, false, 'wpsg_ps_widerruf_mail_text');
				$arPages[] = array("Widerruf E-Mail (HTML)", "Widerruf", "wpsg_ps_widerruf_mail_html", false, false, 'wpsg_ps_widerruf_mail_html');
				
			}
			
			return $arPages;
			
		}
		
		function sc_wpsg_ps_agb($atts) { $arPages = $this->getFormConfig(); return get_option($arPages[0][2]); }
		function sc_wpsg_ps_datenschutz($atts) { $arPages = $this->getFormConfig(); return get_option($arPages[1][2]); }
		function sc_wpsg_ps_widerrufsbelehrung($atts) { $arPages = $this->getFormConfig(); return get_option($arPages[2][2]); }
		function sc_wpsg_ps_impressum($atts) { $arPages = $this->getFormConfig(); return get_option($arPages[3][2]); }
		function sc_wpsg_ps_versand($atts) { $arPages = $this->getFormConfig(); return get_option($arPages[4][2]); }
		function sc_wpsg_ps_battery($atts) { $arPages = $this->getFormConfig(); return get_option($arPages[5][2]); }
		function sc_wpsg_ps_rueckgabe($atts) { $arPages = $this->getFormConfig(); return get_option($arPages[6][2]); }
		function sc_wpsg_ps_mailwiderruf($atts) { $arPages = $this->getFormConfig(); return get_option($arPages[7][2]); }
		function sc_wpsg_ps_widerruf_mail_html($atts) { $arPages = $this->getFormConfig(); return get_option($arPages[8][2]); }
		
		function saveForm()
		{
			
			global $wpdb;
						
			update_option('wpshopgermany_ts_auto', $_REQUEST['wpshopgermany_ts_auto']);
			update_option('wpshopgermany_ts_shopid', $_REQUEST['wpshopgermany_ts_shopid']);
						
			$arPages = $this->getFormConfig();
					 
			foreach ($arPages as $p)
			{
				
				// Seite speichern
				if ($p[4] !== false)
				{
					
					// Seiten anlegen
					if ($_REQUEST['wpsg_ps_page'][$p[4]] == '-1')
					{
					 
						$user_id = 0;
					
						if (function_exists("get_currentuserinfo")) { get_currentuserinfo(); $user_id = $current_user->user_ID; }						
						if ($user_id == 0 && function_exists("get_current_user_id")) { $user_id = get_current_user_id(); }
												
						// Seite anlegen
						$page_uid = $this->ImportQuery($wpdb->prefix."posts", array(
							"post_author" => $user_id,
							"post_date" => "NOW()",
							"post_title" => esc_sql($p[0]),
							"post_date_gmt" => "NOW()",
							"post_name" => esc_sql(strtolower($p[0])),
							"post_status" => "publish",
							"comment_status" => "closed",
							"ping_status" => "neue-seite",
							"post_type" => "page",
							"post_content" => '',
							"ping_status" => "closed",
							"comment_status" => "closed"
						));
						
						update_option($p[4], $page_uid);
						
					}
					else
					{
						
						update_option($p[4], $_REQUEST['wpsg_ps_page'][$p[4]]);
						
					}															
										
				}
				
				// Format speichern
				if ($p[3] !== false)
				{
				
					update_option($p[3], $_REQUEST['wpsg_ps_format'][$p[3]]);
					
				}
				
				// Aktualisieren ?
				if ($_REQUEST['wpshopgermany_ts_refresh'][$p[0]] == '1') 
				{
					
					$this->refresh($p);
										
				}				
				
			} 
			
		} // function saveForm()
		
		function refresh($p)
		{
			
			global $wpdb;
			
			// Format bestimmen
			$format = get_option($p[3]);
			
			if (!is_string($format) || strlen($format) <= 0) 
			{
				
				if ($p[2] == 'wpsg_ps_mailwiderruf') $format = 'Text';
				else if ($p[2] == 'wpsg_ps_widerruf_mail_html') $format = 'Html';
				else die("Kein Format definiert");
				
			}
			
			if (!in_array($format, array("Html", "Text", "HtmlLite"))) die("Falsches Format:".$format." definiert.");
			
			$post = array();
			$post['Request'] = "GetDocument";
			$post['ShopId'] = get_option('wpshopgermany_ts_shopid');
			$post['Document'] = $p[1];					
			$post['Format'] = $format;
			$post['Version'] = '1.5';
			
			$ch = curl_init();
			
			curl_setopt($ch, CURLOPT_URL, 'https://www.protectedshops.de/api/');
			curl_setopt($ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			if (($result = curl_exec($ch)) === false)
			{
		 
				echo 'Curl-Fehler: '.curl_error($ch); die();
				
			}
			else
			{
				 
				$xml = new SimpleXMLElement($result);						
				$content = strval($xml->Document[0]);
				$date = strval($xml->DocumentDate[0]);
				
				update_option($p[2], $content);			
				update_option("wpshopgermany_ts_version_".$p[2], $date);
				update_option("wpshopgermany_ts_time_".$p[2], date("d.m.Y H:i:s", time()));
				
				if ($p[4] !== false && get_option($p[4]) > 0)
				{
					
					$this->UpdateQuery($wpdb->prefix."posts", array(							
						"post_content" => esc_sql($content)	
					), " `ID` = '".esc_sql(get_option($p[4]))."' ");
					
				}
				
			}
			
		}
		
		function showForm()
		{
			
			// Name, API KEY, OptionField, Format, Page|false, Shortcode
			$arPages = $this->getFormConfig();
			$timezone_offet = get_option('gmt_offset');
												
			$content = '
					<tr>
						<th><label for="wpshopgermany_ts_shopid">'.__("Protected Shop ID:", 'wpsg').'</label></th> 
						<td><input type="text" class="regular-text" id="wpshopgermany_ts_shopid" name="wpshopgermany_ts_shopid" value="'.htmlspecialchars(get_option('wpshopgermany_ts_shopid')).'" size="20"></td>
					</tr>
					<tr>
						<th><label for="wpshopgermany_ts_auto">'.__('Automatischer Abgleich', 'wpsg').'</label></th>
						<td>
							<input type="hidden" name="wpshopgermany_ts_auto" value="0" />
							<input value="1" type="checkbox" name="wpshopgermany_ts_auto" id="wpshopgermany_ts_auto" '.((get_option('wpshopgermany_ts_auto') == '1')?'checked="checked"':'').'" />
						</td>
					</tr>
			';
			 
			if (get_option('wpsg_ps_cron') > 0)
			{
				
				$content .= '<tr>';
				$content .= '<td>&nbsp;</td>';
				$content .= '<td>'.__('Letzter Automatischer Abgleich: ', 'wpsg').date('d.m.Y H:i:s', get_option('wpsg_ps_cron') + $timezone_offet * 3600).'</td>'; 
				$content .= '</tr>';
				
			}
			
			if (get_option('wpshopgermany_ts_auto') === '1')
			{
								
				$arWPCron = get_option('cron');
				$time_scheduled = false;
				
				foreach ($arWPCron as $time => $cron)
				{
					
					if (array_key_exists('wpsg_ps_task', $cron)) 
					{
						
						$time_scheduled = $time;
						break;
						
					}
					
				}
					
				$content .= '<tr>';
				$content .= '<td>&nbsp;</td>';
								
				if ($time_scheduled !== false)
				{
					
					$content .= '<td>'.__('Nächster Automatischer Abgleich: ', 'wpsg').date('d.m.Y H:i:s', $time_scheduled + $timezone_offet * 3600).'</td>'; 
										
				}
				else
				{
					
					$content .= '<td>'.__('Automatischer Abgleich noch nicht geplant.', 'wpsg').'</td>';
					
				}
				
				$content .= '</tr>';
				
			}
			
			$content .= '
					<tr><td colspan="2">&nbsp;</td></tr>
			';
			
			if (get_option("wpshopgermany_ts_shopid") != "")
			{
																
				if (!function_exists('curl_init')) {

					$content .= '<tr><th colspan="2"><span style="color:red;">Die Curl Bibliothek muss verfügbar und aktiviert sein.<br/>Informationen darüber finden sie <a href="http://php.net/manual/de/book.curl.php">hier</a>.</span></th> </tr> ';

					return $content;

				}
		 		else 
		 		{

				$url = "https://www.protectedshops.de/api/";
			 	$shop_id = get_option('wpshopgermany_ts_shopid');

				$post['Request'] = "GetDocumentInfo";
			 	$post['ShopId'] = $shop_id;

				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, $url); 
				curl_setopt($ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']); 
				curl_setopt($ch, CURLOPT_POST, 1); curl_setopt($ch, 
				CURLOPT_POSTFIELDS, $post); curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

				$update = Array();

                $result = curl_exec($ch);
                
				if ($result !== FALSE)
				{
 
						//$content_res = preg_replace("/((.*)\<DocumentDate\>)|(\<\/DocumentDate\>(.*))/is", "", $result);						
				 		$xml = new SimpleXMLElement($result);						

						foreach ($arPages as $p)
				 		{
                             
                            $page_key = strval($p[1]);
 
							$arAktuell[$p[1]] = strval($xml->DocumentDate[0]->$page_key);

						}

					}

				}
                 
				foreach ($arPages as $p)
 				{

					$content .= '<tr><th style="height:25px; vertical-align:top; font-weight:normal;"><label><b>'.$p[0].'</b></label><br />Shortcode: <strong>['.$p[5].']</strong></th> <td style="vertical-align:top;"> ';

					$content .= '<div class="wpsg_ps_page">';

					// Name, API KEY, OptionField, Format, Page|false, Shortcode
	
					if ($p[4] !== false) {

						$pages = array_merge(get_pages(array('post_status' => "publish")), get_pages(array('post_status' => "draft")));

						$content .= '<select class="wpsg_ps_page" name="wpsg_ps_page['.$p[4].']">';
				 		$content .= '<option value="0">'.__('Keine Seite verwenden', 'wpsg').'</option>'; 
				 		$content .= '<option value="-1">'.__('Neue Seite anlegen', 'wpsg').'</option>';
				 		
						foreach ((array)$pages as $page) 
						{

							$content .= '<option value="'.$page->ID.'" '.((get_option($p[4]) == $page->ID)?'selected="selected"':'').'>'.$page->post_title.' (ID:'.$page->ID.')</option>';

						}

						$content .= '</select>';

						$content .= '<select name="wpsg_ps_format['.$p[3].']" class="wpsg_ps_format">';
				 		$content .= '<option value="Html" '.((get_option($p[3]) == 'Html')?'selected="selected"':'').'>Html</option>'; 
				 		$content .= '<option value="HtmlLite" '.((get_option($p[3]) == 'HtmlLite')?'selected="selected"':'').'>HtmlLite</option>'; 
				 		$content .= '<option value="Text" '.((get_option($p[3]) == 'Text')?'selected="selected"':'').'>Text</option>'; 
				 		$content .= '</select><div class="wpsg_clear"></div>';

					}

					$content .= '</div>';

					$content .= '<div class="wpsg_ps_status" style="padding-left:8px; padding-top:4px; width:200px;">';

					if (get_option('wpshopgermany_ts_version_'.$p[2]) != "")
			 		{

						$content .= date('d.m.Y H:i:s', strtotime(get_option("wpshopgermany_ts_time_".$p[2])) + $timezone_offet * 3600);

						if (array_key_exists($p[1], $arAktuell) && get_option("wpshopgermany_ts_version_".$p[2]) != $arAktuell[$p[1]])	
						{

							$content .= '<span style="color:red; font-weight:bold;"><br/>Update vorhanden!</span>';

						}

					}
			 		else 
			 		{

						$content .= __('Nicht abgeglichen.', 'wpsg');

					}

					$content .= '</div>';
			 		$content .= '<label class="wpsg_ps_update" style="padding-right:4px; padding-top:4px;">'.__('Jetzt Aktualisieren?', 'wpsg').' <input style="margin-top:3px;" type="checkbox" value="1" name="wpshopgermany_ts_refresh['.$p[0].']"/></label>';	
					$content .= '</td></td></tr><tr><td colspan="2"><hr /></td></tr>';

				} // foreach $arPages

			}

			if (!is_plugin_active('wpshopgermany-free/wpshopgermany.php'))
	 		{ 
	 		
	 			$content .= ' <tr><td colspan="2">&nbsp;</td></tr> <tr><td colspan="2"><input type="submit" value="Einstellungen speichern" name="form_submit" class="button-primary"></td></tr> '; 
	 			
	 		}

			$content .= '<tr><td colspan="2">&nbsp;</td></tr> <tr> <td colspan="2"> <b>Vorgehensweise:</b> 
				<ol> 
					<li>Eingabe der Daten aus ihrem Protected Shops Account</li>
					<li>Auswahl der jeweiligen Seiten in ihrem Blog (Seiten müssen bereits existieren)</li> 
					<li>Markierung der Dokumente die aktuallisiert werden sollen</li> 
				</ol>
				<br/> 
				<b>Hinweise:</b> <ul> <li>Beim Abgleich werden die bestehende Texte der Seite überschrieben.</li> 
			';

			$content .= '<li>Die ShopID erhalten sie in ihrem Protected Shops Account.</li> </ul> </td> </tr>';

			return $content;

		}

		function admin_menu()
 		{
	
			if (!is_plugin_active('wpshopgermany/wpshopgermany.php'))
			{
				 
				add_submenu_page('options-general.php', 'wpShopGermany - Protected Shops',  'Protected Shops', 'administrator', 'wpshopgermany-protectedshops-Admin', array($this, 'dispatch'));
				
			}

		} // function admin_menu()

		/* DB Hilfsfunktionen */
 		function UpdateQuery($table, $data, $where) {

			global $wpdb;

			// Query aufbauen, da wir den kompletten QueryWHERE String als String  übergeben
	 		$strQuery = "UPDATE `".esc_sql($table)."` SET ";

			foreach ($data as $k => $v)
	 			{

				if ($v != "NOW()" && $v != "NULL" && !is_array($v))
			 					$v = 
					"'".$v."'"; else if (is_array($v)) $v = $v[0];

				$strQuery .= "`".$k."` = ".$v.", ";

			}

			$strQuery = substr($strQuery, 0, -2)." WHERE ".$where;

			$res = $wpdb->query($strQuery);

			if ($res === false)
			{
				 
				$this->handleError();
				
			}
			
			return $res;
			
		} // function UpdateQuery($table, $data, $where)
		
		function ImportQuery($table, $data)
		{
			
			global $wpdb;
			
			/**
			 * Wenn diese Option aktiv ist, so werden Spalten nur importiert
			 * wenn sie auch in der Zieltabelle existieren.
			 */
			if ($checkCols === true)
			{
				
				$arFields = $this->fetchAssoc("SHOW COLUMNS FROM `".esc_sql($table)."` ");
				
				$arCols = array();				
				foreach ($arFields as $f) { $arCols[] = $f['Field']; }				
				foreach ($data as $k => $v) { if (!in_array($k, $arCols)) { unset($data[$k]); } }
				
			}
			
			if (!wpsg_isSizedArray($data)) return false;
			
			// Query zusammenbauen
			$strQuery = "INSERT INTO `".esc_sql($table)."` SET ";
			
			foreach ($data as $k => $v)
			{
				
				if ($v != "NOW()" && $v != "NULL" && !is_array($v))
					$v = "'".$v."'";
				else if (is_array($v))
					$v = $v[0];
					
				$strQuery .= "`".$k."` = ".$v.", ";
				
			}
			
			$strQuery = substr($strQuery, 0, -2);
			
			$res = $wpdb->query($strQuery);

			if ($res === false)
			{
				 
				$this->handleError();
				
			}			
			else
			{
						
				return $wpdb->insert_id;
				
			}
			
		} // function ImportQuery($table, $data, $where)
		
		function fetchOne($strQuery)
		{
			
			global $wpdb;

			if ($wpdb->query($strQuery) === false)
			{
				
				$this->handleError();
				
			}
			else
			{
			
				$result = $wpdb->get_var($strQuery);
 
				return $result;
				
			} 
			
		} // function fetchOne($strQuery)
		
		/**
		 * Gibt mehrere Zeilen aus einer Tabelle als Array von Arrays zurück
		 * Ist der Parameter $key ungleich zu false, so ist der Schlüssel des Arrays die in $key übergebene Spalte 
		 */
		function fetchAssoc($strQuery, $key = false)
		{
			
			global $wpdb;
			
			if ($wpdb->query($strQuery) === false)
			{
					
				$this->handleError();
					
			}
			else
			{
			
				$arReturn1 = $wpdb->get_results($strQuery, ARRAY_A);
				
				if ($key != false)
				{
					
					$arReturn = array();
					
					foreach ($arReturn1 as $k => $v)
					{
						
						$arReturn[$v[$key]] = $v;
						
					}
					
					return $arReturn;
					
				}
				else
				{
					
					return $arReturn1;
					
				} 
				
			}
			
		} // function fetchAssoc($strQuery)
		 
		/**
		 * Fehlerbehandlung
		 */
		function handleError()
		{
			
			global $wpdb;
			
			die("Query: ".$wpdb->last_query."\nFehler: ".$wpdb->last_error."\nBacktrace:\n".print_r(debug_backtrace(), 1));
			
		} // function handleError()
		 
	} // class wpsg_protected_shops
	
	function wpshopgermany_protectedshops_install()
	{ 
		
		update_option("mod_ps", "1");
		
	}
	
	function wpshopgermany_protectedshops_uninstall()
	{
		
		wp_clear_scheduled_hook('wpsg_ps_task');
		
		delete_option("mod_ps");
		
	}
		
	$wpsg_ps = new wpsg_protected_shops();	
	 	
	if (is_admin())
	{
		
		add_action('admin_menu', array(&$wpsg_ps, "admin_menu"));
		
	}
	
	if (!wp_next_scheduled('wpsg_ps_task')) 
	{
 
		wp_schedule_event(time(), 'daily', 'wpsg_ps_task');
		
	}
	
	function wpsg_ps_task_function() 
	{
 
 		if (get_option('wpshopgermany_ts_auto') === '1')
 		{
 			
 			global $wpsg_ps;
 			
 			$arPages = $wpsg_ps->getFormConfig();
 			
 			$url = "https://www.protectedshops.de/api/";
			$shop_id = get_option('wpshopgermany_ts_shopid');
						 				
			if ($shop_id !== false && strlen($shop_id) > 0) 
			{
						 					
				$post['Request'] = "GetDocumentInfo";
				$post['ShopId'] = $shop_id;
												
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				
				$update = array();
				$arAkt = array();
				
				if (($result = curl_exec($ch)) !== false)
				{
					
					//$content_res = preg_replace("/((.*)\<DocumentDate\>)|(\<\/DocumentDate\>(.*))/is", "", $result);
					$xml = new SimpleXMLElement($result);						
						 			
					foreach ($arPages as $p)
					{
						
						$akt = strval($xml->DocumentDate[0]->$p[1]);
						
						if ($akt != get_option('wpshopgermany_ts_version_'.$p[2]))
						{
							
							$arAkt[] = $p[0];
							$wpsg_ps->refresh($p);
							
						}
						
					}
					
					if (is_array($arAkt) && sizeof($arAkt) > 0)				
					{
						
						wp_mail(get_settings('admin_email'), "Erfolgreiche Aktualisierung von Rechtstexten", "Hallo, 
						
es wurden folgende Rechtstexte im Shop aktualisiert: ".implode(', ', $arAkt).". 
	
Bitte überprüfen Sie die Texte.
	
------------
	
Dies ist eine auomatisch generierte Mail");
						
					}
					
					update_option('wpsg_ps_cron', time()); 			
								 		
				}
				
			}
 			
 		}
		
	} // function my_task_function()
	
	add_action('wpsg_ps_task', 'wpsg_ps_task_function');
	
	register_activation_hook(__FILE__, 'wpshopgermany_protectedshops_install');
	register_deactivation_hook(__FILE__, 'wpshopgermany_protectedshops_uninstall'); 
	
?>