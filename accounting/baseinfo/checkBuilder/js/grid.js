Ext.namespace("Ext.ux.form");
Ext.ux.form.SimpleCombo=Ext.extend(Ext.form.ComboBox,{
	mode:"local",
	triggerAction:"all",
	typeAhead:true,
	valueField:"value",
	displayField:"name",
	forceSelection:true,
	editable:true,
	minChars:0,
	customProperties:false,
	initComponent:function(){
		Ext.ux.form.SimpleCombo.superclass.initComponent.call(this);if(!this.store&&this.data){
			this.store=new Ext.data.SimpleStore({
				fields:["value","name","cls"],
				data:this.data
			})
		}this.tpl='<tpl for="."><div class="x-combo-list-item {cls}">{'+this.displayField+"}</div></tpl>"
	},
	setList:function(B){
		data=[];if(B&&B instanceof Array){
			for(var A=0;A<B.length;A++){
				data.push([B[A],B[A],null])
			}
		}this.store.loadData(data,false)
	},
	getValue:function(){
		var A=Ext.ux.form.SimpleCombo.superclass.getValue.call(this);if(typeof (A)=="undefined"){
			A=""
		}var B=this.getRawValue()||"";if(!this.customProperties||typeof (A)!="string"||(typeof (A)=="string"&&A.toLowerCase().indexOf(B.toLowerCase())==0)){
			return A
		}return B
	}
});
Ext.reg("simplecombo",Ext.ux.form.SimpleCombo);

Ext.namespace("Ext.ux.form");
Ext.ux.form.CodeField=Ext.extend(Ext.form.TriggerField,{
	invalidText:"'{0}' is not a valid code",
	triggerClass:"x-form-codefield-trigger",
	language:"javascript",
	codePress:true,
	codePressPath:undefined,
	initComponent:function(){
		this.defaultAutoCreate={
			tag:"textarea",
			rows:1,
			style:"width:100px;height:1.8em;overflow:hidden;",
			autocomplete:"off",
			wrap:"off"
		};Ext.ux.form.CodeField.superclass.initComponent.call(this)
	},
	initEvents:function(){
		Ext.ux.form.CodeField.superclass.initEvents.call(this);this.el.on("dblclick",this.onTriggerClick,this)
	},
	getValue:function(){
		return Ext.ux.form.CodeField.superclass.getValue.call(this)||this.defaultValue||" "
	},
	setValue:function(A){
		Ext.ux.form.CodeField.superclass.setValue.call(this,this.formatCode(A));this.setCode(A)
	},
	setCode:function(A){},
	formatCode:function(A){
		return String(A).replace(/\s+$/,"")
	},
	parseCode:function(A){
		return true
	},
	initValue:function(){
		Ext.ux.form.CodeField.superclass.initValue.call(this);this.on("focus",function(){
			this.setValue(this.getValue())
		},this)
	},
	validateValue:function(B){
		if(!Ext.ux.form.CodeField.superclass.validateValue.call(this,B)){
			return false
		}if(B.length<1){
			this.setCode("");return true
		}var A=this.parseCode(B);if(!B||(A==false)){
			this.markInvalid(String.format(this.invalidText,B));return false
		}this.setCode(B);return true
	},
	validateBlur:function(){
		return !this.editorWin||!this.editorWin.isVisible()
	},
	onTriggerClick:function(){
		if(this.disabled){
			return
		}if(!this.editorWin){
			var A=(this.codePress&&Ext.ux.CodePress)?new Ext.ux.CodePress({
				path:this.codePressPath,
				language:this.language,
				autoResize:true,
				trim:true
			}):new Ext.form.TextArea({
				autoCreate:{
					tag:"textarea",
					style:"width:160px;height:80px;",
					autocomplete:"off",
					wrap:"off"
				},
				resize:Ext.emptyFn
			});this.editorWin=new Ext.Window({
				title:"CodeField Editor",
				iconCls:"icon-editEl",
				closable:true,
				width:600,
				height:450,
				plain:true,
				modal:true,
				maximizable:true,
				layout:"fit",
				items:A,
				closeAction:"hide",
				keys:[{
					key:27,
					scope:this,
					fn:function(){
						this.editorWin.hide();if(this.editor.cancelEdit){
							this.editor.cancelEdit()
						}
					}
				}],
				buttons:[{
					text:"Close",
					scope:this,
					handler:function(){
						this.editorWin.hide();if(this.editor.cancelEdit){
							this.editor.cancelEdit()
						}
					}
				},{
					text:"Apply",
					scope:this,
					handler:function(){
						this.setValue(A.getValue());this.editorWin.hide();this.editorWin.el.unmask();if(this.editor.completeEdit){
							this.editor.completeEdit()
						}
					}
				}]
			});this.editorWin.tf=A;this.editorWin.doLayout();this.editorWin.on("resize",function(){
				A.resize()
			})
		}this.editorWin.show();this.editorWin.tf.setValue(this.getValue())
	},
	onRender:function(B,A){
		this.editor=Ext.getCmp(B.id)||{};Ext.ux.form.CodeField.superclass.onRender.call(this,B,A)
	}
});
Ext.reg("codefield",Ext.ux.form.CodeField);

Ext.ux.guid.grid.PropertyRecord=Ext.data.Record.create([{
	name:"name",
	type:"string"
},"value","type"]);

Ext.ux.guid.grid.PropertyStore=function(A,B){
	Ext.ux.guid.grid.PropertyStore.superclass.constructor.call(this,A,B);
	this.store=new Ext.data.Store({recordType:Ext.ux.guid.grid.PropertyRecord});
	this.store.on("update",this.onUpdate,this)
};

Ext.extend(Ext.ux.guid.grid.PropertyStore,Ext.grid.PropertyStore,{
	jsonId:"__JSON__",
	getPropertyType:function(A){
		if(this.grid&&this.grid.getPropertyType){
			return this.grid.getPropertyType(A)
		}return null
	},
	setSource:function(F){
		this.source=F;
		this.store.removeAll();
		var E=[];
		for(var C in F){
			var B=(C.indexOf(this.jsonId)==0&&C!=this.jsonId)?C.substring(this.jsonId.length):null;
			if(B&&F[B]==undefined){
				C=B
			}if(C.indexOf(this.jsonId)!=0&&["items"].indexOf(C)==-1){
				var A=F[this.jsonId+C];var D=null;
				if(typeof (A)=="object"){
					//D=A.type||D;A=A.display||A.value
				}if(typeof (F[C])=="function"){
					/*E.push(new Ext.grid.PropertyRecord({
						name:C,
						value:A||String(F[C]),
						type:"function"
					},C))*/
				}else{
					if(typeof (F[C])=="object"){
						/*E.push(new Ext.grid.PropertyRecord({
							name:C,
							value:A||String(Ext.ux.JSON.encode(F[C])),
							type:"object"
						},C))*/
					}else{
						if(F[C] != "" && !Ext.isNumber(C*1) && C != "cssText" && C != "length")
							E.push(new Ext.grid.PropertyRecord({
								name:C,
								value:A||F[C],
								type:D
							},C))
					}
				}
			}
		}this.store.loadRecords({
			records:E
		},{},true)
	},
	onUpdate:function(E,A,D){
		if(D==Ext.data.Record.EDIT){
			var B=A.data.value;
			var C=A.modified.value;
			// AllStyles[SelectedElementID][A.data.name] = B;
			if(this.grid.fireEvent("beforepropertychange",this.source,A.id,B,C)!==false){
				A.data.changeValue=this.updateSource(A.data.name,B,A.data.type);
				A.commit();
				this.grid.fireEvent("propertychange",this.source,A.data.name,B,C)
			}else{
				A.reject()
			}
		}
	},
	updateSource:function(A,C,B){
		var D=this.getPropertyType(A);if(!B&&D){
			B=D.type
		}if(this.grid.fireEvent("propertyvalue",this.source,A,C,B,D)){
			try{
				this.source[A]=C
			}catch(e){}
		}return this.source[A]
	},
	setValue:function(B,A){
		this.store.getById(B).set("value",A);
		this.updateSource(B,A)
	}
});

Ext.ux.guid.grid.PropertyColumnModel=function(B,A){
	Ext.ux.guid.grid.PropertyColumnModel.superclass.constructor.call(this,B,A);
	this.setConfig([{
		header:this.nameText,
		width:40,
		resizable:true,
		sortable:true,
		dataIndex:"name",
		id:"name",
		menuDisabled:true
	},{
		header:this.valueText,
		width:60,
		resizable:false,
		dataIndex:"value",
		id:"value",
		menuDisabled:true
	}]);
	this.jsonId=B.jsonId;
	Ext.apply(this.editors,{
		regexp:new Ext.grid.GridEditor(new Ext.ux.form.CodeField({
			defaultValue:"new RegExp()",
			codePress:B.codePress,
			codePressPath:B.codePressPath
		})),
		"function":new Ext.grid.GridEditor(new Ext.ux.form.CodeField({
			defaultValue:"function(){}",
			codePress:B.codePress,
			codePressPath:B.codePressPath
		})),
		object:new Ext.grid.GridEditor(new Ext.ux.form.CodeField({
			defaultValue:"{}",
			codePress:B.codePress,
			codePressPath:B.codePressPath
		})),
		"object/array":new Ext.grid.GridEditor(new Ext.ux.form.CodeField({
			defaultValue:"[{}]",
			codePress:B.codePress,
			codePressPath:B.codePressPath
		})),
		array:new Ext.grid.GridEditor(new Ext.ux.form.CodeField({
			defaultValue:"[]",
			codePress:B.codePress,
			codePressPath:B.codePressPath
		})),
		template:new Ext.grid.GridEditor(new Ext.ux.form.CodeField({
			defaultValue:"",
			codePress:B.codePress,
			codePressPath:B.codePressPath
		})),
		mixed:new Ext.grid.GridEditor(new Ext.ux.form.CodeField({
			defaultValue:"",
			codePress:B.codePress,
			codePressPath:B.codePressPath
		})),
		html:new Ext.grid.GridEditor(new Ext.ux.form.CodeField({
			defaultValue:"",
			language:"html",
			codePress:B.codePress,
			codePressPath:B.codePressPath
		})),
		css:new Ext.grid.GridEditor(new Ext.ux.form.CodeField({
			defaultValue:"",
			language:"css",
			codePress:B.codePress,
			codePressPath:B.codePressPath
		})),
		editlist:new Ext.grid.GridEditor(new Ext.ux.form.SimpleCombo({
			forceSelection:false,
			data:[],
			editable:true,
			customProperties:true
		})),
		list:new Ext.grid.GridEditor(new Ext.ux.form.SimpleCombo({
			forceSelection:false,
			data:[],
			editable:true,
			customProperties:false
		})),
		"boolean":new Ext.grid.GridEditor(new Ext.ux.form.SimpleCombo({
			forceSelection:false,
			data:[[true,"true"],[false,"false"]],
			editable:true,
			customProperties:true
		}))
	});
	this.valueRendererDelegate=this.valueRenderer.createDelegate(this);
	this.propertyRendererDelegate=this.propertyRenderer.createDelegate(this)
};

Ext.extend(Ext.ux.guid.grid.PropertyColumnModel,Ext.grid.PropertyColumnModel,{
	getPropertyType:function(A){
		if(this.grid&&this.grid.getPropertyType){
			return this.grid.getPropertyType(A)
		}return null
	},
	getCellEditor:function(A,H){
		var D=this.store.getProperty(H);
		var G=D.data.name,E=D.data.value,B=D.data.type;

		if(this.grid.customEditors[G]){
			return this.grid.customEditors[G]
		}var F=this.getPropertyType(G);
		if(F){
			if(F.editor){
				if(typeof (F.editor)!="string"){
					return F.editor
				}B=F.editor
			}B=B||F.type;
			if(!B&&F.values){
				var C=F.editable?this.editors.editlist:this.editors.list;C.field.setList(F.values);return C
			}
		}if(B&&this.editors[B]){
			return this.editors[B]
		}else{
			if(Ext.isDate(E)){
				return this.editors.date
			}else{
				if(typeof E=="number"){
					return this.editors.number
				}else{
					if(typeof E=="boolean"){
						return this.editors["boolean"]
					}
				}
			}
		}return this.defaultEditor||this.editors[F?"string":"mixed"]
	},
	valueRenderer:function(B,C,A){
		if(typeof B=="boolean"){
			C.css=(B?"typeBoolTrue":"typeBoolFalse");return(B?"True":"False")
		}var D=this.getPropertyType(A.id);if(D&&["object","array","object/array"].indexOf(D.type)!=-1){
			C.css="typeObject"
		}if(typeof (B)=="string"&&B.length>24){
			B=B.substring(0,21)+"..."
		}var E=document.createElement("div");E.appendChild(document.createTextNode(B));return E.innerHTML
	},
	propertyRenderer:function(A,B){
		var C=this.getPropertyType(A);if(C){
			qtip=C.desc||"";B.attr='qtip="'+qtip.replace(/"/g,"&quot;")+'"'
		}return A
	},
	getRenderer:function(A){
		return A==0?this.propertyRendererDelegate:this.valueRendererDelegate
	}
});

Ext.ux.guid.grid.PropertyGrid=Ext.extend(Ext.grid.EditorGridPanel,{
	enableColumnMove:false,
	enableColumnResize: true,
	stripeRows:true,
	trackMouseOver:true,
	autoExpandColumn : "name",
	clicksToEdit:1,
	enableHdMenu:false,
	viewConfig:{
		forceFit:true
	},
	jsonId:"__JSON__",
	codePress:true,
	codePressPath:undefined,
	getPropertyType:function(A){
		if(this.propertyTypes){
			var B=this.propertyTypes.find("name",A);
			if(B!=-1){
				return this.propertyTypes.getAt(B).data
			}
		}return null
	},
	initComponent:function(){
		this.customEditors=this.customEditors||{};
		this.lastEditRow=null;
		var B=new Ext.ux.guid.grid.PropertyStore(this);
		B.jsonId=this.jsonId,this.propStore=B;
		var A=new Ext.ux.guid.grid.PropertyColumnModel(this,B);
		B.store.sort("name","ASC");
		this.addEvents("beforepropertychange","propertychange","propertyvalue");
		this.cm=A;this.ds=B.store;
		Ext.ux.guid.grid.PropertyGrid.superclass.initComponent.call(this);
		this.selModel.on("beforecellselect",function(E,D,C){
			if(C===0){
				this.startEditing.defer(200,this,[D,1]);return false
			}
		},this)
	},
	onRender:function(){
		Ext.ux.guid.grid.PropertyGrid.superclass.onRender.apply(this,arguments);this.getGridEl().addClass("x-props-grid")
	},
	afterRender:function(){
		Ext.ux.guid.grid.PropertyGrid.superclass.afterRender.apply(this,arguments);if(this.source){
			this.setSource(this.source)
		}
	},
	setSource:function(A){
		this.propStore.setSource(A)
	},
	getSource:function(){
		return this.propStore.getSource()
	}
});
Ext.reg("guidpropertygrid",Ext.ux.guid.grid.PropertyGrid);

