function complete(data){
			$sel.attr("disabled", "disabled");			
			alert($("#section").html());
			//alert(data);			
			var add = $("#section").html();
			 add = add+data;
}

$(function(){
	$(".elem_cb").change(function(){
		if ($(this).attr("checked") == "checked"){
			$(this).parent("td").parent("tr").css("background", "#fff");
		}else{
			$(this).parent("td").parent("tr").css("background", "#ff7575");
		}
	});
	$("#unselect").click(function(){
		$("#sectionX input[type='checkbox']").removeAttr("checked");
		$(this).css("display", "none");
		$("#selectall").css("display", "block");
	});
	$("#selectall").click(function(){
		$("#sectionX input[type='checkbox']").attr("checked", "checked");
		$(this).css("display", "none");
		$("#unselect").css("display", "block");
	});
	$("select.kat").live("change", function(){
	    var navi = $(".navi").text()
		var dLvL = $(this).prev("input[type='hidden']").val();
		var sectID = $(this).val();
		var $sel = $(this);
		//var sectName = $("option value["+$(this).val()+"]").html();
		
		//alert(sectID);
		$.ajax({
			type: "POST",
			url: '/yml/aj_ozon.php',
			data: "dlvl="+dLvL+"&SectID="+sectID+"&navi="+navi,
			success: function(data){
				//$sel.attr("disabled", "disabled");		
				$("#section").html(data);
								
			}
		});	
	});
	// функция замены href в ссылке на просмотр файла
	$(".file_select").change(function(){
		var fileName = $(this).val();
		var Show = $("#show_file").attr("href", "price/"+fileName);
	
	});
});

jQuery.ajax