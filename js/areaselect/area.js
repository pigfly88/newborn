var Area = {
	//初始化
	init: function(selects, defaultopt){
		if(typeof(selects)=='undefined'){
			selects = ["province","city","county"];
		}
		if(typeof(defaultopt)=='undefined'){
			defaultopt = ["省份","地级市","区/县级市"];
		}
		this.selects = selects;
		this.defaultopt = defaultopt;
		
		for(i=0;i<this.selects.length-1;i++){
		  document.getElementById(this.selects[i]).onchange=new Function("Area.change("+(i+1)+")");
		}
		this.change(0);

	},
	//选中地区
	select: function(ids){
		if(ids.length>0){
			for(j=0;j<ids.length;j++){
				this.change(j, ids[j]);
			}
		}
	},
	
	change: function(){
		var index = arguments[0] ? arguments[0] : 0; //第几个select
		var activeid = arguments[1] ? arguments[1] : 0; //id选中
		var select=document.getElementById(this.selects[index]);
		var pid = index>0 ? document.getElementById(this.selects[index-1]).value : 0;
		
		//移除子级列表
		if(activeid==0){
			for(i=this.selects.length-1;i>=index;i--){
				document.getElementById(this.selects[i]).options.length=0;
				document.getElementById(this.selects[i]).options.add(new Option(this.defaultopt[i],0));
			}
		}
		if(index!=0 && pid==0){
			return false;
		}
		with(select){
			options[0]=new Option(this.defaultopt[index], 0);
			
			if(typeof(arealist[pid])!='undefined' && arealist[pid].cid.length>0){
				for(i=0;i<arealist[pid].cid.length;i++){
					options[i+1]=new Option(arealist[arealist[pid].cid[i]].name, arealist[pid].cid[i]);
					if(activeid>0 && activeid==arealist[pid].cid[i]){
						options[i+1].selected = true;
					}
				}
			}
		}
	},
	
	//根据id获得地区名称
	getname: function(ids){
		var str='';
		if(ids.length>0){
			for(j=0;j<ids.length;j++){
				str += arealist[ids[j]].name;
			}
		}
		return str;
	},
}