jQuery(document).ready(function(){
	
	jQuery('#duplicator_btn').click(function(){
			
		var menu_id = jQuery('#all_menus').val();
	
		var new_name = jQuery('#new_menu_called').val();
		
		if(menu_id == ''){
			alert("Please select menu to be copied");
			return false;
		}else if(new_name == ''){
			alert("Please input menu name");
			return false;
		}else{
			jQuery(this).attr('disabled','disabled');
			jQuery.ajax({
				url:ajaxurl,
				type:'POST',
				data:{
					action:'perform_duplication',
					new_name:new_name,
					menu_id:menu_id
				},
				success:function(response){
					 jQuery('#duplicator_btn').attr("disabled", false); 
					
					if(response){
						var str = 'Duplication Successful, Edit Menu <a href="nav-menus.php?action=edit&menu='+response+'" target="_blank" title="+new_name+" >here</a>';
						jQuery('#response').html(str);
						
					}
				},
				 error: function(errorThrown){
					console.log(errorThrown);
				}
			})
			
		}
	
		
	});
	
	
});