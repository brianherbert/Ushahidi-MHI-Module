

	$(function() {

		$(".expand_deployment_search").click(function() {
			expand_window();
		});

		$(".search_submit").click(function() {

			var search_val = $("#search_val").val();

			if(search_val == undefined)
			{
				// Not searching for anything...
			}
			else
			{
				// Normal search
				deployment_search('0','4',search_val);
			}
			return false;
		});
	});

	function expand_window() {
		$(".expand_deployment_search").fadeOut('fast',function() {
			$("#deployment_search_box").animate({height: '740px'}, 1000, "linear",function() {
				$("#deployment_search_box").css('overflow-y','scroll');
			});
		});
	}

	$(document).ready(function() {
		// After the page loads, put the top deployments in the box.
		deployment_search('0','4','');

		// Also add a few of the top deployments without descriptions but with
		//   screenshots at the bottom of the window
		small_img_deployment_search('0','20','');
	});

	function small_img_deployment_search(limit,offset,search_val){
		$.getJSON('<?php echo url::site(); ?>mhi_extras/search.php', {'q' : search_val, 'limit' : limit+','+offset, 'no_description' : 1, 'image_size' : 50}, function(data){

			$("#deployment_search_result_small_img").html('');

			var img = '';

			$.each(data, function(key,val){

				img = '';
				if(val.ss1_t !== '' && val.ss1_t !== 'null' && val.ss1_t !== null) {
					img = '<a href="'+val.url+'"><img src="'+val.ss1_t+'" style="float:left;padding:10px;width:50px;height:50px;" /></a>';
				}
				$("#deployment_search_result_small_img").append(img);

			});

		});
	}

	function deployment_search(limit,offset,search_val){

		$.getJSON('<?php echo url::site(); ?>mhi_extras/search.php', {'q' : search_val, 'limit' : limit+','+offset, 'has_description' : 1}, function(data){

			$("#deployment_search_result").html('');

			var img = '';
			var desc = '';
			var i = 0;

			$.each(data, function(key,val){

				img = '';
				if(val.ss1_t !== '' && val.ss1_t !== 'null' && val.ss1_t !== null) {
					img = '<a href="'+val.url+'"><img src="'+val.ss1_t+'" style="float:left;margin-right:10px;width:75px;height:75px;" /></a>';
				}

				desc = '';
				if(val.description !== '' && val.description !== 'null' && val.description !== null) {
					desc = '<div style="font-size:12px;line-height:15px;">'+val.description+'</div>';
				}

				$("#deployment_search_result").append('<div style="margin-top:20px;"><a href="'+val.url+'" style="display:block;clear:both;">'+val.name+'</a></div>');

				$("#deployment_search_result").append(''+img+''+desc+'');

				$("#deployment_search_result").append('<div style="font-size:10px;color:#5C5C5C;">Go to <a href="'+val.url+'">'+val.name+'</a>.');

				$("#deployment_search_result").append('<div style="clear:both;"></div>');

				i++;

			});


			// Check if we need to show the back button
			var back = '';
			if(limit > 0){
				back = '<span><a onclick="javascript:deployment_search('+(parseInt(limit)-parseInt(offset))+','+parseInt(offset)+',\''+search_val+'\')" style="cursor:pointer">Back</a></span>';
			}else{
				back = '<span style="color:#CCC;">Back</span>';
			}

			// Good chance we need to allow forward pagination
			var forward = '';
			if(i >= offset){
				forward = '<span><a onclick="javascript:deployment_search('+(parseInt(limit)+parseInt(offset))+','+parseInt(offset)+',\''+search_val+'\')" style="cursor:pointer">Forward</a></span>';
			}else{
				forward = '<span style="color:#CCC;">Forward</span>';
			}

			// Show pagination links
			$("#deployment_search_result").append('<div style="float:left;margin:15px 0px 15px 150px;">'+back+'</div><div style="float:right;margin:15px 150px 15px 0px;">'+forward+'</div><div style="clear:both;"></div>');

			if(i == 0){
				$("#deployment_search_result").html('Sorry, we couldn\'t find any public deployments with your search terms.');
			}

		});

	}

	function show_video(){

		// Make the window big so we don't have to scroll
		expand_window();

		$("#show_video").html('<a onclick="javascript:hide_video();" style="float:right;margin-right:45px;cursor:pointer;font-size:10px;">Close Video</a><br/><iframe width="425" height="296" src="https://www.youtube.com/embed/aCDO5DyNt0Q/?autoplay=1" frameborder="0" allowfullscreen></iframe><br/><a onclick="javascript:hide_video();" style="float:right;margin-right:45px;cursor:pointer;font-size:10px;">Close Video</a><div style="clear:both;"></div>');
	}

	function hide_video(){
		$("#show_video").html('');
	}
