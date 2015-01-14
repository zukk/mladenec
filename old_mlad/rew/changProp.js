$(function(){
	$(".change").click(function(){
		var ElementID = $(this).attr("name");
		var SectionID = $(this).attr("section");
		var Selected = $(this); 
		Selected.prev(".chkProp").css("display", "inline");
		$(this).prev(".chkProp").prev("span.NameProp").css("display", "none");
		$(this).css("display", "none");
		$(this).next(".submitProp").css("display", "inline");
		var ObjData;
		$.ajax
		({
			url: "change.php",
			type: "POST",
			data: "ElementID="+ElementID+"&SectionID="+SectionID,
			success: function(data){
				//alert ();
				ObjData = data;
				Selected.prev(".chkProp").html(data);
			},
		
		});
		
	});
// изменения параметров отзыва.
	$(".submitProp").click(function(){
		
		$(this).prev(".change").css("diplsy","inline");
		$(this).css("diplsy","none");
		var $obj=$(this);
		var ElemId = $(this).prev(".change").attr("name");
		var SecId = $(this).prev(".change").prev(".chkProp").val();
		$(this).prev(".change").css("display", "inline");
		$(this).css("display", "none");
		$(this).prev(".change").prev(".chkProp").css("display", "none");
		var ChangText = $(this).prev(".change").prev(".chkProp").prev(".NameProp");
		var PVID = $(this).prev(".change").attr("paramID");
		'NameProp'
		$.ajax
			({
				url: "change.php",
				type: "GET",
				data: "Ch="+SecId+"&El_id="+ElemId+"&old="+PVID,
				success: function(data)
						{
							ChangText.html(data);
							ChangText.css("display", "inline");
							$obj.next("a").attr("href", "http://www.mladenec-shop.ru/bitrix/admin/iblock_element_edit.php?WF=Y&ID="+SecId+"&type=comments&lang=ru&IBLOCK_ID=1376&find_section_section=0")							
						},		
			});
		});
	// Смена активности.
	$(".priznak_id").live("click", function(){
		var PropId = $(this).val();
		var PropActove = $(this).html();
		var printREsult = $(this)
		
		$.ajax
		({
			url: "change.php",
			type: "GET",
			data: "PropActive="+PropActove+"&PropId="+PropId,
			success: function(data)
				{
					printREsult.html(data);
				},		
		});
	});
		
		
// Изменение Полезности отзыва	
	$(".utility").live("click", function(){
		$(this).parent(".chUtility_result").next(".chUtility").css("display", "block");	
	});
	$(".chUtilityClose").click(function(){
		$(this).parent(".chUtility").css("display", "none");
	});
	
	$(".vote_submit").click(function(){
		var $form = $(this).parent(".chUtility");
		var $yes = $form.find(".yes").val();
		var $no = $form.find(".no").val();
		var $El_ID= $(this).attr("elid");
		$.ajax
			({
				url: "change.php",
				type: "GET",
				data: "Yes="+$yes+"&No="+$no+"&El_id="+$El_ID,
				success: function(data)
						{
							//ChangText.html(data);
							$form.css("display", "none");
							$(".chUtility_result").html(data);
						},		
			});
		});
	
// Изменение активности. 
		$(".active").live("click", function(){
		    $span = $(this).parent(".sactive");
			if ($(this).val() == "Y"){
			$value = "N";
			}else{
			$value = "Y";
			}
			$El_Id = $(this).attr("elid");
			$.ajax
				({
					url: "change.php",
					type: "GET",
					data: "Active="+$value+"&El_id="+$El_Id,
					success: function(data)
							{								
								$span.html(data);
							},		
				});
			});
			
// Добавить признак
	$(".DobavitPriznak").click(function(){
		var $revID = $(this).attr("id");
		var DivAdd = $(this).next(".addPriznak");
		$.ajax
				({
					url: "change.php",
					type: "GET",
					data: "addPriznak="+$revID,
					success: function(data)
							{								
								DivAdd.html(data);
								DivAdd.css("display", "block")
							},		
				});
	});
	
	$(".AddPropClose").live("click", function(){  //удалить информацию из странички и закрыть див.
		 var div = $(this).parent("div").parent("div");	
			div.html("");
			div.css("display", "none");
	});
	
	$(".conformProp").live("click", function(){ 
		var Input = $(this).prev("input").val();
		var SectionID = $(this).parent("div").prev("select").val();
		var RevID = $(this).parent("div").parent("div").attr("rewID");
		 $.ajax
				({
					url: "change.php",
					type: "GET",
					data: "NamePriznak="+Input+"&SectionID="+SectionID+"&RevID="+RevID,
					success: function(data)
							{								
								if ((data*1) > 0){
									alert("Признак добалвен его ID:" + data);
								}else{
									alert("Случилась какая-то бня. "+data);
								}
							},		
				});
	});
//------ Изменение текста отызва.
	$(".detailtext_rew_save").live("click", function(){
		var text = $(this).prev(".detailtext_rew_text").val();
		var RewID = $(this).attr("rewid");
		var DataContainer = $(this).prev(".detailtext_rew_text");
		//alert(text);
		 $.ajax
				({
					url: "change.php",
					type: "post",
					data: "text="+encodeURIComponent(text)+"&RewID="+RewID+"&action=ChangTextRew",
					success: function(data)
							{								
								if (data != 0){
									DataContainer.html(data);
									alert("текст отзыва изменён.");
								}else{
									alert("Случилась какая-то бня."+data);
								}
							},	
				});
	});
	
//------ Изменение текста ответа.
	$(".answertext_rew_save").live("click", function(){
		var text = $(this).prev(".answertext_rew_text").val();
		var RewID = $(this).attr("rewid");
		var DataContainer = $(this).prev(".answertext_rew_text");
		var Html;
		Html_check = $(this).prev(".answertext_rew_text").prev("input");
		if (Html_check.attr("checked")){
			Html = "html";
		}else{
			Html = "text";
		}		
		
		//alert(text);
		 $.ajax
				({
					url: "change.php",
					type: "post",
					data: "text="+encodeURIComponent(text)+"&html="+Html+"&RewID="+RewID+"&action=ChangAnswerRew",
					success: function(data)
							{								
								if (data != 0){
									DataContainer.html(data);
									alert("Текст ответа изменён.");
								}else{
									alert("Ответ удален"+data);
								}
							},	
				}); 
	});
	
//------ Смена имени
$(".chang_name").live("click", function(){
		$(this).parent(".nameIn").next(".NameChang").css("display", "block");	
		$(this).parent(".nameIn").css("display", "none");
	});
$(".chang_it").live("click", function(){
		var name = $(this).prev("input").val();
		var hiddeDiv = $(this).parent("div");
		var div = $(this).parent("div").prev("div")
		var h3 = div.children("h3")
		var DataContainer = $(this).prev(".detailtext_rew_text");
		var RewID =  $(this).attr("rewid")
		$.ajax
				({
					url: "change.php",
					type: "GET",
					data: "name="+encodeURIComponent(name)+"&RewID="+RewID+"&action=ChangNameRew",
					success: function(data)
							{								
								if (data != 0){
									h3.html(data);
									div.css("display", "block");
									hiddeDiv.css("display", "none");
									alert("Название изменено.");
								}else{
									alert("Случилась какая-то бня."+data);
								}
							},	
				}); 
		});
	
//------ Смена рейтинга
	$(".chang_reating_button").live("click", function(){
		$(this).parent("div").css("display", "none");
		$(this).parent("div").next(".reating_change").css("display", "block");
	});
	$(".chang_reating_save").live("change", function(){
		var RewID = $(this).attr("rewid");
		var val = $(this).val();
		var conteiner = $(this).parent("div").prev("div").children("b");
		var div = $(this).parent("div").prev("div");
		var close = $(this).parent("div");
		$.ajax
			({
				url: "change.php",
				type: "GET",
				data: "val="+val+"&RewID="+RewID+"&action=ChangReatRew",
				success: function(data)
						{								
							if (data != 0){
								conteiner.html(data);
								div.css("display", "block");
								close.css("display", "none");
								alert("Рейтинг изменен.");
							}else{
								alert("Случилась какая-то бня " + data);
							}
						},	
			}); 	
	});
	
//------ Выбрать признак
	$(".selectPriznak").live("click", function(){
		var RewID = $(this).attr("rewID");
		var div = $(this).next("div");
		var hide = $(this);
		$.ajax 
		({
			url:"change.php",
			type:"GET",
			data:"RewID="+RewID+"&action=AddPropertyList",
			success: function(data)
					{
						div.html(data);	
						div.css("display", "block");
						hide.css("display", "none");
					}
		});
		
	});
	$(".SelectPropSectionforadd").live("change", function(){
		var SectionID = $(this).val();
		var div = $(this).parent(".SelectPriznakArea");
		$.ajax
		({
			url:"change.php",
			type: "GET",
			data: "SecID="+SectionID+"&action=PriznakElementList",
			success: function(data)
					{
						div.html(data);	
					}
		});
	});
	$(".SelectPropElementforAdd").live("change", function(){
		var ElemId = $(this).val();
		var RewID = $(this).parent(".SelectPriznakArea").prev("div").attr("rewID");
		var div = $(this).parent(".SelectPriznakArea");
		var show = $(this).parent(".SelectPriznakArea").prev("div");
		$.ajax
		({
			url:"change.php",
			type: "GET",
			data: "RewID="+RewID+"&PriznakID="+ElemId+"&action=PriznakAddInRew",
			success: function(data)
					{
						div.html("Признак изменен");
						show.css("display", "block");	
					}
		});
	});
//------ Открепление свойства от отзыва
	$(".unlink").live("click", function(){
		var PropID = $(this).val();
		var RewID = $(this).attr("name");
		var div = $(this).parent("div");
		$.ajax
		({
			url:"change.php",
			type: "GET",
			data: "RewID="+RewID+"&PriznakID="+PropID+"&action=UnLinkPriznak",
			success: function(data)
					{
						div.html("Признак выбран и установлен для отзыва");
					}
		});
	});
// скрыть подсказку 

	$("#howto_close").click(function(){
		$("#howto").css("display","none");
	});	
	
// Показать список редакторов 
	$(".redactor").live("change", function(){
		var RewID = $(this).attr("rewid");
		var RedID = $(this).val();
		var conteiner = $(this).prev("p");
		
		$.ajax
		({
			url:"change.php",
			type: "GET",
			data: "RewID="+RewID+"&RedID="+RedID+"&action=RedactorName",
			success: function(data)
					{
						conteiner.html(data);
					}
		});
	});


});

