function status_change(data,purl,rurl){
	
	layer.confirm('确认更改'+data.id+'状态吗？',function(index){
		$.ajax({
			type:'POST',
			url:purl,
			data:data,
			dataType:'json',
			success:function(e){
				
				if(e.code==1){
					layer.msg('已成功',{icon:1,time:2000},function(){
						location.href=rurl;
					});
					// layer.alert('已成功!',{icon:1},function(){
					// 	location.href=rurl;
					// });
					
				}else{
					layer.alert(e.msg,{icon:2});
					
				}
			}
		});
		
		
	});
}

/*用户-删除*/
function member_del(obj,id,purl){
	layer.confirm('确认要删除吗？',function(index){
		$.ajax({
			type: 'POST',
			url: purl,
			data:{id:id},
			dataType: 'json',
			success: function(e){
				if(e.code==1){
					$(obj).parents("tr").remove();
					layer.msg('已删除!',{icon:1,time:1000});
				}else{
					layer.msg(e.msg,{icon:2,time:3000});
				}
				
				

			}
		});		
	});
}