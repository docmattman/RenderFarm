var ajaxUrl = 'monitor.php';
var packageRefreshInterval = null;
var packageRefreshTick = 2000;		// 2 Seconds
$(function() 
{
	$('#tabs').tabs();
	
	initProgressBars();
	
	// Package clicked
	$('table.data tbody .packageItem').click(function()
	{
		$('table.data tbody .packageItem').removeClass('active');
		$(this).addClass('active');
		var pid = $(this).attr('data-id');
		
		// Get info for packages
		$.ajax(
		{
			url: ajaxUrl,
			dataType: 'json',
			data:
			{
				action: 'package_details',
				id: pid
			},
			success: function(data) 
			{
				if(!data['html']) return;
				$('.detailsContainer.packageItem').html(data['html']).attr('data-id', pid);	
				initProgressBars();		
			}
		});
	});
	$('table.data tbody .packageItem:first').click();
	
	
	// Worker clicked
	$('table.data tbody .workerItem').click(function()
	{
		$('table.data tbody .workerItem').removeClass('active');
		$(this).addClass('active');
		var wid = $(this).attr('data-id');
		
		// Get info for packages
		$.ajax(
		{
			url: ajaxUrl,
			dataType: 'json',
			data:
			{
				action: 'worker_details',
				id: wid
			},
			success: function(data) 
			{
				if(!data['html']) return;
				$('.detailsContainer.workerItem').html(data['html']).attr('data-id', wid);	
			}
		});
	});
	$('table.data tbody .workerItem:first').click();
	
	
	// Go to a worker from jobs table
	$('.jobsTable .worker').live('click', function()
	{
		$('#tabs').tabs('select', 1);
		$('.workersTable .workerItem[data-id="'+$(this).attr('data-id')+'"]').click();
	});
	
	// Refresh info
	packageRefreshInterval = setInterval(function()
	{
		var ids = [];

		// Get package ids to check
		$('.packageItem').each(function(i)
		{
			if($(this).attr('data-status') != 'complete') ids.push($(this).attr('data-id'));			
		});
//		if(ids.length < 1) return;
		
		// Get the selected package
		var selectedPackage = $('.packageItem.active').attr('data-id');
				
		// Get info for packages
		$.ajax(
		{
			url: ajaxUrl,
			dataType: 'json',
			data:
			{
				action: 'refresh',
				packages: ids,
				active: selectedPackage
			},
			success: function(data) 
			{
				// Update workers
				var resortWorkers = false;
				for(var i in data['workers'])
				{
					var item = $('.workerItem[data-id="'+i+'"]');
					item.attr('data-status', data['workers'][i]['status']);
					$('.ip', item).html(data['workers'][i]['ip']);
					
					// Update status
					if(!resortWorkers && data['workers'][i]['available'] != item.attr('data-available')) resortWorkers = true;
					if(item.hasClass('active') && data['workers'][i]['available'] != item.attr('data-available')) item.click();		// Reload
					item.attr('data-available', data['workers'][i]['available']);
					if(data['workers'][i]['enabled'] != item.attr('data-enabled'))	// Enabled/disabled has changed, move it
					{
						var dataItem = $('.workerItem[data-id="'+i+'"].dataItem');
						if(!resortWorkers) resortWorkers = true;
						if(data['workers'][i]['enabled'] == 1) dataItem.appendTo('.workersTable tbody.enabled');
						else dataItem.appendTo('.workersTable tbody.disabled');
					}
					item.attr('data-enabled', data['workers'][i]['enabled']);
					if(Number(data['workers'][i]['available']) == 1) 	// Available
					{
						$('.status', item).html(ucfirst(data['workers'][i]['status']));
						$('.availabilityIndicator', item).attr('src', 'style/status_available.png');
					}
					else 	// Unavailable
					{
						$('.status', item).html('');
						$('.availabilityIndicator', item).attr('src', 'style/status_unavailable.png');
					}
				}
				if(resortWorkers) workersSort();	// Resort the workers

				
				// Update packages
				if(!data['packages']) return;
				for(var i in data['packages'])
				{
					var item = $('.packageItem[data-id="'+i+'"]');
					item.attr('data-status', data['packages'][i]['status']);
					$('.progress', item).attr('data-progress', data['packages'][i]['progress']);
					updatePackage(i);
					
					// Refresh when complete
					if(i == selectedPackage && Number(data['packages'][i]['progress']) == 100)
					{
						$('.packageItem.active').click();
					}
				}			
				

				// Update jobs
				for(var i in data['jobs'])
				{
					var item = $('.jobItem[data-id="'+i+'"]');
					item.attr('data-status', data['jobs'][i]['status']);
					$('.progress', item).attr('data-progress', data['jobs'][i]['progress']);
					$('.worker', item).html(data['jobs'][i]['worker_name']);
					updateJob(i);
				}

				// Keep jobs table sorted
				if(Number($('.detailsContainer.packageItem .packageStatus').attr('data-progress')) == 100) return;
			    $('.detailsContainer.packageItem .jobsTable .jobItem').sort(function(a, b)
			    {
			    	var aP = Number($('.progress', $(a)).attr('data-progress'));
			    	var aNum = (aP == 100) ? -1 : aP;
			    	var bP = Number($('.progress', $(b)).attr('data-progress'));
			    	var bNum = (bP == 100) ? -1 : bP;
			    	if(aNum == bNum)
			    	{
			    		var aI = Number($(a).attr('data-id'));
			    		var bI = Number($(b).attr('data-id'));
			    		return aI - bI;
			    	}
					return bNum - aNum;
			    }).appendTo('.detailsContainer.packageItem .jobsTable tbody');
			}
		});
	}, 1500);
});

// Initialize progress bars
function initProgressBars()
{
	$('.packageProgressBar, .jobProgressBar').each(function()
	{
		var progress = Number($(this).parents('.progress').attr('data-progress'));
		$(this).progressbar(
		{
			value: progress
		}).attr('title', progress+'% Complete');
	});
}

// Update a package item from values
function updateJob(id)
{
	// Progress
	var item = $('.jobItem[data-id="'+id+'"]');
	var progress = Number($('.progress', item).attr('data-progress'));
	$('.jobProgressBar', item).progressbar('value', progress).attr('title', progress+'% Complete');
	
	if(progress == 100)
	{
		$('.progress', item).html('Complete');
	}
}

// Update a package item from values
function updatePackage(id)
{
	// Progress
	var item = $('.packageItem[data-id="'+id+'"]');
	var progress = Number($('.progress', item).attr('data-progress'));
	$('.packageProgressBar', item).progressbar('value', progress).attr('title', progress+'% Complete');
	
	if(progress == 100)
	{
		$('.progress', item).html('Complete');
	}
}


// Sort workers
function workersSort()
{
	var itemSort = function(a, b)
	{
		var aNum = Number($(a).attr('data-available'));
    	var bNum = Number($(b).attr('data-available'));
    	if(aNum == bNum)
    	{
    		var aI = $(a).attr('data-id');
    		var bI = $(b).attr('data-id');
    		if(aI < bI) return -1;
    		if(aI > bI) return 1;
    		return 0;
    	}
		return bNum - aNum;
    };
	$('.workersTable.data tbody.enabled .workerItem').sort(itemSort).appendTo('.workersTable.data tbody.enabled');
	$('.workersTable.data tbody.disabled .workerItem').sort(itemSort).appendTo('.workersTable.data tbody.disabled');
}


// Reboot a worker
function workerReboot(id)
{
	if(id == undefined)		// Get current selected id
	{
		id = $('table.data tbody .workerItem.active').attr('data-id')
	}
	
	// Confirm reboot
	if(!confirm('Are you sure you want to reboot this machine?')) return;
	
	// Send instruction
	workerInstruct({ id: id, cmd: 'reboot' });
}

// Set a setting value
function setSettingVal(k, v)
{
	// Get info for packages
	$.ajax(
	{
		url: ajaxUrl,
		dataType: 'json',
		data: 
		{
			action: 'set_setting',
			key: k,
			value: v 
		},
		success: function(data) 
		{
		}
	});
}

// Enable/disable worker
function workerEnDisable(enabled, id)
{
	if(id == undefined)		// Get current selected id
	{
		id = $('table.data tbody .workerItem.active').attr('data-id')
	}
	
	// Get info for packages
	$.ajax(
	{
		url: ajaxUrl,
		dataType: 'json',
		data: 
		{
			action: 'worker_set_endisabled',
			id: id,
			enabled: enabled 
		},
		success: function(data) 
		{
			workersSort();
			$('.workerItem.dataItem.active').click();
		}
	});
}

// Instruct a worker
function workerInstruct(dataOptions)
{
	data = { action: 'worker_instruct' };
	for(var i in dataOptions)
	{
		data[i] = dataOptions[i];
	}
	
	// Get info for packages
	$.ajax(
	{
		url: ajaxUrl,
		dataType: 'json',
		data: data,
		success: function(data) 
		{
			if(!data['html']) return;
			$('.detailsContainer.workerItem').html(data['html']).attr('data-id', wid);	
		}
	});
}


function ucfirst(str) 
{
    // Makes a string's first character uppercase  
    // 
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/ucfirst
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: ucfirst('kevin van zonneveld');
    // *     returns 1: 'Kevin van zonneveld'
    str += '';
    var f = str.charAt(0).toUpperCase();
    return f + str.substr(1);
}