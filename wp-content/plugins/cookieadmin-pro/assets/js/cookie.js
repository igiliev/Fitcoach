jQuery(document).ready(function($){
	
	var law = '';
		
	$(".cookieadmin-consent-logs-export").on("click", function(){
		
		$(this).prop("disabled", true);
		
		$.ajax({
			url: cookieadmin_pro_policy.admin_url,
			method: "POST",
			data : {
				action : 'cookieadmin_pro_ajax_handler',
				cookieadmin_act : 'export_logs',
				cookieadmin_export_type : 'consent_logs',
				cookieadmin_pro_security : cookieadmin_pro_policy.cookieadmin_nonce,
			},
			success: function(response){				
				
				// Was the ajax call successful ?
				if(response.substring(0,2) == "-1"){
					
					var err_message = response.substring(2);
					
					if(err_message){
						alert(err_message);
					}else{
						alert("Failed to export data");
					}
					
					return false;
				}
				
				/*
				* Make CSV downloadable
				*/
				var downloadLink = document.createElement("a");
				var fileData = ['\ufeff'+response];

				var blobObject = new Blob(fileData,{
				 type: "text/csv;charset=utf-8;"
				});

				var url = URL.createObjectURL(blobObject);
				downloadLink.href = url;
				downloadLink.download = "cookieadmin-consent-logs.csv";

				/*
				* Actually download CSV
				*/
				document.body.appendChild(downloadLink);
				downloadLink.click();
				document.body.removeChild(downloadLink);
			},	
			error: function(xhr, status, error) {
				
			}
		});
		
		$(this).prop("disabled", false);
	});
	
	// For .cookieadmin-logs-paginate click
	$(".cookieadmin-logs-paginate").on("click", function () {
		cookieadmin_pro_paginate(this);
	});

	// For input change
	$("#current-page-selector").on("change", function () {
		cookieadmin_pro_paginate(this);
	});
	
	function cookieadmin_pro_paginate(el) {
		var $this = $(el);
		
		let pageType = $this.attr("id");
		let currentPageInput = $("#current-page-selector");
		let currentPage = parseInt(currentPageInput.val());
		let totalPages = parseInt($(".total-pages").text());

		// Determine action based on ID
		if (pageType === "cookieadmin-first-consent-logs") {
			currentPage = 1;
		} else if (pageType === "cookieadmin-previous-consent-logs") {
			if (currentPage > 1) currentPage--;
		} else if (pageType === "cookieadmin-next-consent-logs") {
			if (currentPage < totalPages) currentPage++;
		} else if (pageType === "cookieadmin-last-consent-logs") {
			currentPage = totalPages;
		}

		$.ajax({
			url: cookieadmin_pro_policy.admin_url,
			method: "POST",
			data: {
				action: 'cookieadmin_pro_ajax_handler',
				cookieadmin_act: 'get_consent_logs',
				current_page: currentPage,
				cookieadmin_pro_security: cookieadmin_pro_policy.cookieadmin_nonce,
			},
			success: function(response) {
				if (response.success) {
					let data = response.data; // Get response data
					
					// Update the current page input field
					$("#current-page-selector").val(data.current_page);

					// Update the counts
					$(".displaying-num").text(data.min_items+" - "+data.max_items);
					$(".max-num").text(data.total_logs);
					$(".total-pages").text(data.total_pages);

					// Select the table body (excluding headers)
					let logsContainer = $(".cookieadmin-consent-logs-result tbody");

					// Clear all the rows in tbody
					logsContainer.find("tr").remove();
					
					// Append new rows with updated logs
					if (data.logs.length > 0) {  // Fix: Check `data.logs`, not `data`
						$.each(data.logs, function(index, log) {  // Fix: `data.logs`
							var status_badge = "warning";
							if(log.consent_status_raw.toLowerCase() == 'accept'){
								status_badge = "success";
							}else if(log.consent_status_raw.toLowerCase() == 'reject'){
								status_badge = "danger";
							}
							logsContainer.append(`
								<tr>
									<td>${log.consent_id}</td>
									<td><span class="cookieadmin-badge cookieadmin-${status_badge}">${log.consent_status}</td>
									<td>${log.country || '—'}</td>
									<td>${log.user_ip}</td>
									<td>${log.consent_time}</td>
								</tr>
							`);
						});
					} else {
						logsContainer.append(`<tr><td colspan="4">No consent logs recorded yet!</td></tr>`);
					}
					
				} else {
					alert('Error: ' + response.data.message);
				}
			},
			error: function(xhr, status, error) {
				console.log(xhr.responseText); // Check the error message
				console.log('AJAX Error:', status, error);
			}
		});
	}
	
});