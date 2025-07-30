
<?php
// We need the ABSPATH

if (!defined('ABSPATH')) exit;

// Styles and Scripts
wp_enqueue_style( 'gosmtp-admin' );
wp_enqueue_script( 'gosmtp-admin' );

function gosmtp_reports_table(){
	global $gosmtp;
	
	if(empty($gosmtp->options['logs']['enable_logs']) ){
		echo '<h1>Email report is disabled</h1>
			<div class="error is-dismissiable notice">
			<p>'.__('To view email reports, please enable email logs from GoSMTP').' <a href="'.admin_url('admin.php?page=gosmtp#logs-settings').'">'.__('settings').'</a>.</p>
		</div>';
		return;
	}

	$date = gosmtp_optget('date');
	$custom_start = gosmtp_optget('start-date');
	$custom_end = gosmtp_optget('end-date');
	$search = gosmtp_optget('search_email_reports');
	$multiselect = gosmtp_optget('multiselect');
	$all_data = array();

	$logger = new GOSMTP\Logger();
		
	// Appropriate date setup
	if($date == 'custom_date'){
		$start = $custom_start;
		$end = $custom_end;
	
		if($start != '' && $end == ''){
			$end = date("Y-m-d");
		}
	}else if($date == 'all' || $date == ''){
		$start = '';
		$end = '';
	}else{
		$start = date('Y-m-d', strtotime('-'.$date.' days'));
		$end = date("Y-m-d");
	}

	// Assign all data in option array
	$options = array(
		'interval' => array(
			'start' => $start,
			'end' => $end
		), 
		'search' => $search,
		'multiselect' => $multiselect,
		'pagination' => false,
	);

	$email_logs = $logger->get_logs('records', 0, $options);

	// TODO: Get only records as paged
	$mails = gosmtp_group_by($email_logs);

	// Pagination
	$perpage = 10;
	$curpage = (int) gosmtp_optget('paged', 1);
	$records_ct = count($mails);
	$tpages = ceil($records_ct / $perpage);
	$offset = ($curpage - 1) * $perpage;

	$args = array(
		'base' => '%_%',
		'format' => '?paged=%#%',
		'total' => $tpages,
		'current' => $curpage,
		'show_all' => false,
		'end_size' => 1,
		'mid_size' => 2,
		'prev_next' => true,
		'type' => 'array',
		'add_args' => false
	);

	// Set limit value
	$limit = array(
		'perpage' => $perpage,
		'offset' => $offset,
	);

	$pages = paginate_links( $args );

	$table_cols = array(
		'subject' => __('Subject'),
		'from' => __('From'),
		'to' => __('To'),
		'resent_count' => __('Resent'),
		'retries' => __('Retry'),
		'sent' => __('Sent'),
		'failed' => __('Failed'),
		'total' => __('Total')
	);
?>

<div class="wrap">
	<div class="wrap_header gosmtp-relative">
		<h1><?php _e('Email Reports') ?></h1>
	</div>
	<div class="gosmtp-upper-elements-container">
		<form action="<?php echo admin_url('admin.php'); ?>" method="get">
			<input type="hidden" name="page" value="email_reports">
			<div class="gosmtp-element-container">
				<div class=" gosmtp-report-search-container">
					<div class="gosmtp-search-report-list-icon">
						<span class="dashicons dashicons-search"></span>
						<input type="search" id="gosmtp-search_email" placeholder="Search" name="search_email_reports"/>
					</div>
				</div>
				<div class="gosmtp-fiter-main-container">
					<div class="gosmtp-fiter-container">
						<span class="multiselect"><?php _e('Select Filter') ?></span>
						<ul class="multiselect-options">
							<li><input type='checkbox' class="multiselect-checkbox" id="all" value='all'><label for="all"><?php _e('Select all') ?></label></li>
							<li><input type='checkbox' class="multiselect-checkbox" id="subject" value='subject' name="multiselect[]"><label for="subject"><?php _e('Subject') ?></label></li>
							<li><input type='checkbox' class="multiselect-checkbox" id="from" value='from' name="multiselect[]"><label for="from"><?php _e('From') ?></label></li> 
							<li><input type='checkbox' class="multiselect-checkbox" id="to" value='to' name="multiselect[]"><label for="to"><?php _e('To') ?></label></li>
						</ul>
						<span class="dropdown dashicons dashicons-arrow-down-alt2"></span>
					</div>
				</div>
				<div class="gosmtp-date-option-container">
					<select id="gosmtp-date-option-filter" name='date'>
						<option value="all" selected disabled><?php _e('Select date') ?></option>
						<option value="7"><?php _e('Last 7 days') ?></option>
						<option value="14"><?php _e('Last 14 days') ?></option>
						<option value="30"><?php _e('Last 30 days') ?></option>
						<option value="custom_date"><?php _e('Custom date') ?></option>
					</select>
					<div class='gosmtp-report-date-container'>
						<input type="date" name="start-date" id="gosmtp-start-date" placeholder="Start date" /> 
						<input type="date" name="end-date" id="gosmtp-end-date" placeholder="End date" />
					</div>
				</div>
				<div class=" gosmtp-report-submit-container">
					<input type="submit" class="button button-primary submit-email-reports" id="submit-email-reports" name="submit-email-reports" value="Search"/>
				</div>
			</div>	
		</form>
		<!-- <div class="graph-container">
			<canvas id="myChart"></canvas>
		</div> -->
	</div>	
	<table cellspacing="0" cellpadding="8" border="0" width="100%" class="gosmtp-email-report" id="gosmtp-email-reports">
		<tr>
			<td colspan="9">
				<h2><?php _e('Email Reports') ?></h2>
			</td>
		</tr>
		<tr>
		<?php
		foreach($table_cols as $col){
			echo '<th>'.$col.'</th>';
		}
		?>
			<!-- <th><?php _e('Graph') ?></th> -->
		</tr>
		<?php
		// Mail list array
		$mail_list = gosmtp_group_by($email_logs, $limit);
		
		// Append HTML value
		if(!empty($mail_list)){

			$all_data = array();
			foreach($mail_list as $main_key => $mail){
				
				$mail_array = $mail['total'];
					
				echo "<tr>";
				
				foreach($table_cols as $ck => $col){
					echo '<td>'.$mail_array[$ck].'</td>';
				}
				
				echo '<!-- <td>
						<span class ="dashicons dashicons-chart-area email_report_icon" data-data="'.$main_key.'" onclick="gosmtp_load_graph([\"'.$main_key.'\"])"></span>
					</td> -->
				</tr>';

				// Add by date data in all data
				array_push($all_data, $mail['by_dates']);
			}
		}else{
			// Empty all data when no result found 
			$all_data = array();
		?>
		<tr>
			<td colspan="9" class="gosmtp-empty-row"><?php _e('Records not found!'); ?></td>
		</tr>
		<?php 
		}
	?>
	</table>
	
	<?php 
	// Render pagination HTML
	if( is_array( $pages ) ){
		echo '<div class="gosmtp-pagination"><ul class="gosmtp-pagination-wrap">';
		
		foreach ( $pages as $page ) {
			echo '<li class="gosmtp-pagination-links">'.$page.'</li>';
		}
		
		echo '</ul></div>';
	} 
	?>
</div>
<script>
	var gosmtp_ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>?";
	var gosmtp_ajax_nonce = "<?php echo wp_create_nonce('gosmtp_ajax'); ?>";
</script>

<?php
}

// Set Custom array
function gosmtp_set_custom_array($val){

	$return = array();

	foreach($val as $kk => $vv){
		
		switch($kk){
			case 'to':
				$tos = maybe_unserialize($vv);
				$to_list = [];
				
				foreach($tos as $keys => $to){
					$to_list[] = $to[0];
				}
				
				$return['total']['to'] = implode(',', $to_list);
				break;
			case 'status':
				if($vv == 'sent'){
					$return['total']['sent'] = 1;
					$return['total']['failed'] = 0;
				}elseif($vv == 'failed'){
					$return['total']['sent'] = 0;
					$return['total']['failed'] = 1;
				}
				break;
			case 'subject': 
				$return['total'][$kk] = empty($vv) ? '[No Subject]' : $vv;
	    			break;
			default:
				$return['total'][$kk] = $vv;
		}
		
	}
	
	$return['total']['total'] = $return['total']['sent'] + $return['total']['failed'];

	$return['by_dates'][$val->created_at] = array(
		'sent' => $return['total']['sent'],
		'failed' => $return['total']['failed'],
		'retries' => $return['total']['retries'],
		'resent_count' => $return['total']['resent_count'],
		'total' => $return['total']['total'],
	);
	
	return $return;

}

// Generate Array with group by
function gosmtp_group_by($logs, $limit = array(), $multiselect = array('subject', 'from', 'to')){
	
	$groups = array();
	$i = 0;
	$key_array = array();

	if(empty($logs)){
		return array();
	}
	
	foreach($logs as $val){
		
		$val = gosmtp_set_custom_array($val);
		$total = $val['total'];
		
		$groups_val = array();
		foreach($multiselect as $multi_val){
			array_push($groups_val, $total[$multi_val]);
		}

		// Add new group
		if(!in_array($groups_val, $key_array)){
			
			$key_array[$i] = $groups_val;
			
			$groups[$i]['total'] = $val['total'];
			$groups[$i]['by_dates'] = $val['by_dates'];
			$i++;
		
		}else{
			foreach($key_array as $kk => $vv){
				
				if($groups_val != $vv){
					continue;
				}
				
				$group_total = $groups[$kk]['total'];
				
				$total['sent'] = $group_total['sent'] + $total['sent'];
				$total['failed'] = $group_total['failed'] + $total['failed'];
				$total['retries'] = $group_total['retries'] + $total['retries'];
				$total['resent_count'] = $group_total['resent_count'] + $total['resent_count'];
				$total['total'] = $group_total['total'] + $total['total'];
				
				$groups[$kk]['total'] = $total;
				$groups[$kk]['by_dates'][array_keys($val['by_dates'])[0]] = $val['by_dates'][array_keys($val['by_dates'])[0]];
			}
		}
	}

	// Set limit for Pagination
	if(!empty($limit)){
		
		$limit_res = array();
		
		for($i = 0; $i< $limit['perpage']; $i++){
			$j = $i+$limit['offset'];	

			if(isset($groups[$j])){
				array_push($limit_res, $groups[$j] );
			}
		}
		
		$groups = $limit_res;
	}
	
	return $groups;		   
}
