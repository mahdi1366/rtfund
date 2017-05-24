
Ext.isVersion=function(C,F){
	if(C instanceof Array){
		F=formVersion[1];C=formVersion[0]
	}var A=function(G){
		var H=G.match(/^(\d)\.\d/);H=H?H[1]:0;var J=G.match(/^\d\.(\d)/);J=J?J[1]:0;var I=G.match(/^\d\.\d\.(\d)/);I=I?I[1]:0;return(H*1)+(J*0.1)+(I*0.001)
	};var D=A(C||Ext.version);var B=A(F||Ext.version);var E=A(Ext.version);return(E>=D&&E<=B)
};
Ext.overrideIf=function(A,C,B,D){
	if(Ext.isVersion(B,D)){
		return Ext.override(A,C)
	}return A
};
Ext.applyVersion=function(B,C,A,D){
	if(Ext.isVersion(A,D)){
		return Ext.apply(B,C)
	}return B
};
Ext.applyIfVersion=function(B,C,A,D){
	if(Ext.isVersion(A,D)){
		return Ext.applyIf(B,C)
	}return B
};

Ext.overrideIf(Ext.Panel,{
	getKeyMap:function(){
		if(!this.keyMap){
			if(Ext.isArray(this.keys)){
				for(var B=0,A=this.keys.length;B<A;B++){
					this.keys[B].scope=this.keys[B].scope||this
				}
			}else{
				if(this.keys&&!this.keys.scope){
					this.keys.scope=this
				}
			}this.keyMap=new Ext.KeyMap(this.el,this.keys)
		}return this.keyMap
	}
},"2.0");

Ext.overrideIf(Ext.FormPanel,{
	initFields:function(){
		this.initItems();var C=this.form;var A=this;var B=function(D){
			if(D.doLayout&&D!=A){
				Ext.applyIf(D,{
					labelAlign:D.ownerCt.labelAlign,
					labelWidth:D.ownerCt.labelWidth,
					itemCls:D.ownerCt.itemCls
				});if(D.items){
					D.items.each(B)
				}
			}else{
				if(D.isFormField){
					C.add(D)
				}
			}
		};this.items.each(B)
	}
},"2.0");

Ext.overrideIf(Ext.Button,{
	setTooltip:function(A){
		var B=this.getEl().child(this.buttonSelector);Ext.QuickTips.register({
			target:B.id,
			text:A
		})
	}
},"2.0");

Ext.applyIfVersion(Ext,{
	isGecko3:!Ext.isSafari&&navigator.userAgent.toLowerCase().indexOf("rv:1.9")>-1
},"2.0","2.2");

Ext.ComponentMgr=function(A){
	var C={};function B(G,D){
		if(!G||!D){
			return
		}for(var F=D.split(","),E=0;E<F.length;E++){
			C[F[E]]=F[E]
		}
	}B(Ext.isVersion("2.0"),"box,button,colorpalette,component,container,cycle,dataview,datepicker,editor,editorgrid,grid,paging,panel,progress,propertygrid,splitbutton,tabpanel,treepanel,viewport,window,toolbar,tbbutton,tbfill,tbitem,tbseparator,tbspacer,tbsplit,tbtext,form,checkbox,combo,datefield,field,fieldset,hidden,htmleditor,label,numberfield,radio,textarea,textfield,timefield,trigger");B(Ext.isVersion("2.1"),"slider,statusbar");return Ext.applyIf({
		registerType:function(E,D){
			A.registerType(E,D);C[E]=E
		},
		isTypeAvailable:function(D){
			return !!C[D]
		},
		allTypes:function(){
			var D=[];for(var E in C){
				D.push(E)
			}return D
		}
	},A)
}
(Ext.ComponentMgr);

Ext.reg=Ext.ComponentMgr.registerType;



Ext.ux.IFrameComponent=Ext.extend(Ext.BoxComponent,{
	url:null,
	onRender:function(C,A){
		var B=this.url;B+=(B.indexOf("?")!=-1?"&":"?")+"_dc="+(new Date().getTime());this.el=C.createChild({
			tag:"iframe",
			id:"iframe-"+this.id,
			frameBorder:0,
			src:B
		})
	}
});

Ext.reg("iframe",Ext.ux.IFrameComponent);
Ext.namespace("Ext.ux")

Ext.ux.Json=Ext.extend(Ext.ux.Util,{
	indentString:"  ",
	readable:true,
	licenseText:null,
	jsonId:null,
	scope:{},
	evalException:true,
	fullEncode:false,
	initialize:function(){
		Ext.ux.Json.superclass.initialize.call(this);this.addEvents({
			beforeapply:true,
			afterapply:true
		})
	},
	getScope:function(){
		return this.scope
	},
	isEmpty:function(B){
		if(B instanceof Array){
			for(var A=0;A<B.length;A++){
				if(!this.isEmpty(B[A])){
					return false
				}
			}
		}else{
			if(typeof (B)=="object"){
				for(var A in B){
					if((!this.useHasOwn||B.hasOwnProperty(A))&&(!this.jsonId||A.indexOf(this.jsonId)!=0)){
						return false
					}
				}
			}else{
				if(B!=undefined){
					return false
				}
			}
		}return true
	},
	load:function(A,B){
		if(B&&B instanceof Ext.Container){
			return this.load(A,function(C){
				this.apply(B,C)
			}.createDelegate(this))
		}else{
			if(typeof (B)=="function"){
				Ext.Ajax.request({
					url:A,
					nocache:this.nocache,
					callback:function(E,G,D){
						try{
							if(G){
								var C=this.decode(D.responseText);if(typeof callback=="function"){
									callback(C)
								}
							}else{
								throw new Error("Failure during load")
							}
						}catch(F){
							this.fireEvent("error","load",F)
						}
					},
					scope:this
				});return null
			}else{
				return this.decode((typeof (A)=="object")?this.syncContent(A.url,A.nocache==undefined?this.nocache:A.nocache):this.syncContent(A.url,this.nocache))
			}
		}
	},
	set:function(G,K,L){
		var B=true,C=G||this;L=L||{};if(L.nocache==undefined){
			L.nocache=this.nocache
		}if(K){
			if(L.scopeOnly){
				L=Ext.apply({
					evalException:false
				},L)
			}if(typeof (K)=="string"){
				K=this.decode(K,L)
			}for(var H in K){
				var F=H;if(H=="required_js"){
					if(K[H]){
						var A=K[H].replace(",",";").split(";");for(var I=0;I<A.length;I++){
							if(document.getElementById(A[I])){
								continue
							}if(!this.scriptLoader(A[I],L.nocache)){
								var J=new Error("Failed to load javascript "+A[I]);if(this.fireEvent("error","set",J)){
									throw J
								}
							}
						}
					}
				}else{
					if(H=="required_css"){
						if(K[H]){
							var A=K[H].replace(",",";").split(";");for(var I=0;I<A.length;I++){
								if(document.getElementById(A[I])){
									continue
								}Ext.util.CSS.swapStyleSheet(A[I],A[I])
							}
						}
					}else{
						var D=C;if(H.indexOf("scope.")==0){
							D=L.scope?L.scope:this.getScope();F=H.substring(6);if(F.charAt(0)=="!"){
								F=F.substring(1);if(D[F]){
									continue
								}
							}
						}else{
							if(L.scopeOnly){
								continue
							}
						}var E="set"+F.substring(0,1).toUpperCase()+F.substring(1);try{
							if(D[E]&&typeof D[E]=="function"){
								D[E].call(C,K[H])
							}else{
								if(D[F]&&typeof D[F]=="function"){
									D[F].call(C,K[H])
								}else{
									D[F]=K[H]
								}
							}
						}catch(J){
							if(L.ignoreError){
								B=false
							}else{
								B|=(this.fireEvent("error","set("+E+")",J)===false)
							}
						}
					}
				}
			}
		}return B
	},
	clean:function(C){
		var G=0;for(var B in C){
			if(!this.useHasOwn||C.hasOwnProperty(B)){
				if(B=="items"){
					if(C[B] instanceof Array){
						var F=[];for(var D=0,A=C[B];D<A.length;D++){
							var E=this.clean(A[D]);if(E!=null){
								F.push(E)
							}
						}C[B]=(F.length>0)?F:null
					}else{
						C[B]=this.clean(C[B])
					}
				}if(C[B]===undefined||C[B]===null||(typeof C[B]=="string"&&C[B]=="")){
					delete C[B]
				}else{
					G++
				}
			}
		}return G?C:null
	},
	editable:function(C){
		var A=C||{};if(typeof (A)!=="object"){
			A=this.decode(C)
		}if(!this.jsonId){
			return A
		}if(A instanceof Array){
			for(var B=0;B<A.length;B++){
				A[B]=this.editable(A[B])
			}return A
		}if(!A[this.jsonId]){
			A[this.jsonId]=Ext.id();if(A.items){
				A.items=this.editable(A.items)
			}
		}return A
	},
	apply:function(D,C,B){
		var A;try{
			A=this.jsonId?this.editable(C):C||{};if(typeof (A)!=="object"){
				A=this.decode(C)
			}if(A&&(A instanceof Array||typeof (A)=="object")){
				if(B!==false){
					A=this.clean(A)
				}this.fireEvent("beforeapply",D,A);if(D instanceof Ext.Container){
					while(D.items&&D.items.first()){
						D.remove(D.items.first(),true)
					}if(!this.isEmpty(A)){
						if(A instanceof Array){
							D.add.apply(D,A)
						}else{
							D.add(A)
						}this.set(D,A.json)
					}
				}else{
					this.set(D,A)
				}if(D.rendered&&D.layout&&D.layout.layout){
					D.doLayout()
				}
			}
		}catch(E){
			if(this.fireEvent("error","apply",E)){
				throw E
			}
		}finally{
			this.fireEvent("afterapply",D,A)
		}return A
	},
	encodeString:function(B){
		var A={
			"\b":"\\b",
			"\t":"\\t",
			"\n":"\\n",
			"\f":"\\f",
			"\r":"\\r",
			'"':'\\"',
			"\\":"\\\\"
		};if(/["\\\x00-\x1f]/.test(B)){
			return'"'+B.replace(/([\x00-\x1f\\"])/g,function(D,C){
				var E=A[C];if(E){
					return E
				}E=C.charCodeAt();return"\\u00"+Math.floor(E/16).toString(16)+(E%16).toString(16)
			})+'"'
		}return'"'+B+'"'
	},
	indentStr:function(C){
		var B="",A=0;while(this.readable&&A<C){
			B+=this.indentString;A++
		}return B
	},
	encodeArray:function(G,B,H){
		B=B||0;var D=["["],A,F,C=G.length,E;for(var F=0;F<C;F+=1){
			E=G[F];switch(typeof E){
				case"undefined":case"unknown":break;default:if(A){
					D.push(",")
				}D.push(E===null?"null":this.encode(E,B+1,H));A=true
			}
		}D.push("]");return D.join("")
	},
	encodeDate:function(B){
		var A=function(C){
			return C<10?"0"+C:C
		};return'"'+B.getFullYear()+"-"+A(B.getMonth()+1)+"-"+A(B.getDate())+"T"+A(B.getHours())+":"+A(B.getMinutes())+":"+A(B.getSeconds())+'"'
	},
	encode:function(D,E,C,I){
		var A=this.readable?"\n":"";var G=this.readable?" : ":"";E=E||0;if(E==0){
			var K=[],H=(!I&&this.licenseText)?this.licenseText+"\n":"";K.push(H,this.encode(D,1,C));return K.join("")
		}if(D==undefined||D===null){
			return"null"
		}else{
			if(D instanceof Array){
				return this.encodeArray(D,E,C)
			}else{
				if(D instanceof Date){
					return this.encodeDate(D)
				}else{
					if(typeof D=="number"){
						return isFinite(D)?String(D):"null"
					}else{
						if(typeof D=="string"&&!isNaN(D)&&D!=""){
							return D
						}else{
							if(typeof D=="string"&&["true","false"].indexOf(D)!=-1){
								return D
							}else{
								if(typeof D=="boolean"){
									return String(D)
								}else{
									if(typeof D=="string"){
										return this.encodeString(D)
									}else{
										var K=[],J,F,L;K.push(this.indentStr(E-1),"{"+A);for(var F in D){
											L=D[F];
											var B=(F.indexOf(this.jsonId)==0&&F!=this.jsonId)?F.substring(this.jsonId.length):null;
											if((!B&&this.jsonId&&D[this.jsonId+F])||(!C&&F==this.jsonId)){
												continue
											}if(B){
												if(typeof (L)=="object"&&(typeof (L.value)!="string"||String(L.value).replace(/\s+$/,""))){
													if(J){
														K.push(","+A)
													}if(L.encode===false){
														K.push(this.indentStr(E),B,G,L.value)
													}else{
														K.push(this.indentStr(E),B,G,this.encode(L.value,E+1,C))
													}
												}else{
													if(typeof (L)!="object"&&String(L).replace(/\s+$/,"")){
														if(J){
															K.push(","+A)
														}K.push(this.indentStr(E),B,G,L)
													}else{
														continue
													}
												}J=true
											}else{
												if(!this.useHasOwn||D.hasOwnProperty(F)){
													switch(typeof L){
														case"undefined":case"unknown":break;case"function":if(J){
															K.push(","+A)
														}K.push(this.indentStr(E),F,G,""+L);J=true;break;case"object":case"string":if(!L){
															break
														}default:if(J){
															K.push(","+A)
														}K.push(this.indentStr(E),F,G,L===null?"null":this.encode(L,E+1,C));J=true
													}
												}
											}
										}K.push(A+this.indentStr(E-2)+"}");return K.join("")
									}
								}
							}
						}
					}
				}
			}
		}
	},
	getObjectRawValue:function(B,C){
		if(this.jsonId&&B[this.jsonId+C]){
			var A=B[this.jsonId+C];return typeof (A)=="object"?A.value:A
		}return B[C]
	},
	setJsonValue:function(A,C,D){
		A=A||{};if(!A.json){
			A.json={}
		}var B=new Ext.ux.Json({
			jsonId:this.jsonId,
			nocache:nocache,
			evalException:false
		});var E=B.decode(A[this.jsonId+"json"])||{};E[C]=D;A.json[C]=D;A[this.jsonId+"json"]=B.encode(E);return A
	},
	setObjectValue:function(A,B,E,D,C){
		C=C||this.getScope();if(B=="json"){
			this.set(C,E,{
				scopeOnly:true,
				scope:C,
				nocache:this.nocache
			})
		}if(typeof (E)=="string"){
			E=E.replace(/\s+$/,"")
		}if(E===null||E===""){
			delete A[B];if(this.jsonId){
				delete A[this.jsonId+B]
			}return E
		}A[B]=E;if(this.jsonId){
			if(D&&typeof (D)=="object"){
				A[this.jsonId+B]=D
			}else{
				if(D){
					try{
						if(typeof (D)=="string"){
							D=D.replace(/\s+$/,"")
						}A[B]=this.decode(D,{
							exceptionOnly:true,
							scope:C
						});if(typeof (A[B])=="string"&&([E,"'"+E+"'",this.encodeString(E)].indexOf(D)!=-1)){
							delete A[this.jsonId+B]
						}else{
							A[this.jsonId+B]=D
						}
					}catch(F){
						A[this.jsonId+B]={
							value:D,
							encode:true
						}
					}
				}else{
					delete A[this.jsonId+B]
				}
			}
		}return E
	},
	codeEval:function(code,options){
		options=options||{};var self=this;var scope=options.scope||this.getScope();var evalException=options.evalException==undefined?this.evalException:options.evalException;if(!code||!String(code).replace(/\s+$/,"")){
			return null
		}var myEval=function(code){
			try{
				return eval("({fix:"+code+"})").fix
			}catch(e){
				e=new SyntaxError("Invalid code: "+code+" ("+e.message+")");if(options.exceptionOnly){
					throw e
				}if(evalException&&self.fireEvent("error","codeEval",e)){
					throw e
				}return code
			}
		}.createDelegate(scope);return myEval(code)
	},
	decode:function(T,E){
		E=E||{};var K=0,J=" ",M=this;
		var C=E.scope||this.getScope();
		var U=E.fullDecode==undefined?this.fullDecode:E.fullDecode;
		function R(W){
			var X=new SyntaxError(W);X.at=K-1;X.json=T;throw X
		}
		function O(){
			J=T.charAt(K);K+=1;return J
		}
		function N(W){
			K-=W?W:1;J=T.charAt(K);K+=1;return J
		}
		function P(X,Y){
			if(Y==undefined){
				Y=-1
			}var W=0;for(;W<X.length&&T.charAt(K+W+Y)==X.charAt(W);W++){}if(W>=X.length){
				K+=Y+W;O();return true
			}return false
		}
		function F(){
			while(J){
				if(J<=" "){
					O()
				}else{
					if(J=="/"){
						switch(O()){
							case"/":while(O()&&J!="\n"&&J!="\r"){}break;case"*":O();for(;;){
								if(J){
									if(J=="*"){
										if(O()=="/"){
											O();break
										}
									}else{
										O()
									}
								}else{
									R("Unterminated comment")
								}
							}break;default:N(2);return
						}
					}else{
						break
					}
				}
			}
		}function H(){
			var W=J;while(O()&&": \t\n\r-+={(}[])'\"".indexOf(J)==-1){
				W+=J
			}return W
		}function A(a){
			a=a||J;var b=K-1,Y,Z="",X,W;if(J==a){
				outer:while(O()){
					if(J==a){
						O();return Z
					}else{
						if(J=="\\"){
							switch(O()){
								case"b":Z+="\b";break;case"f":Z+="\f";break;case"n":Z+="\n";break;case"r":Z+="\r";break;case"t":Z+="\t";break;case"u":W=0;for(Y=0;Y<4;Y+=1){
									X=parseInt(O(),16);if(!isFinite(X)){
										break outer
									}W=W*16+X
								}Z+=String.fromCharCode(W);break;default:Z+=J
							}
						}else{
							Z+=J
						}
					}
				}
			}R("Bad string "+T.substring(b,K-1))
		}function G(X){
			var Y=K-1,W=[];if(J=="["){
				O();F();if(J=="]"){
					O();return W
				}while(J){
					W.push(Q(X)[0]);F();if(J=="]"){
						O();return W
					}else{
						if(J!=","){
							break
						}
					}O();F()
				}
			}R("Bad array "+T.substring(Y,K-1))
		}function V(Y){
			var a=K-1,X,Z={},W;if(J=="{"){
				O();F();if(J=="}"){
					O();return Z
				}while(J){
					X=J=='"'||J=="'"?A():H();F();if(J!=":"){
						R("Bad key("+J+") seprator for object "+T)
					}O();F();W=Q(X!="items");M.setObjectValue(Z,X,W[0],W[1],C);F();if(J=="}"){
						O();return Z
					}else{
						if(J!=","){
							break
						}
					}O();F()
				}
			}R("Bad object ["+X+"]"+T.substring(a,K-1))
		}function D(){
			var X="",W;if(J=="-"){
				X="-";O()
			}while(J>="0"&&J<="9"){
				X+=J;O()
			}if(J=="."){
				X+=".";while(O()&&J>="0"&&J<="9"){
					X+=J
				}
			}if(J=="e"||J=="E"){
				X+="e";O();if(J=="-"||J=="+"){
					X+=J;O()
				}while(J>="0"&&J<="9"){
					X+=J;O()
				}
			}W=+X;if(!isFinite(W)){
				R("Bad number "+W)
			}else{
				return W
			}
		}function L(W){
			while(O()){
				F();switch(J){
					case W:return ;case"(":L(")");break;case"[":L("]");break;case"{":L("}");break;case'"':case"'":A(J);K--
				}
			}R("Unexpected end of code")
		}function B(Y){
			K--;var Z=K-(Y?8:0);var W;while(O()){
				F();switch(J){
					case"(":L(")");break;case"[":L("]");break;case'"':case"'":A(J);N(2);break;case"{":L("}");if(!Y){
						break
					}O();case",":case"]":case"}":var X=T.substring(Z,K-1);return[M.codeEval(X,E),X]
				}
			}var X=T.substring(Z,K-1);return[M.codeEval(X,E),X]
		}function Q(W){
			lastCode=null;F();switch(J){
				case"{":return W&&!U?B():[V(false)];case"[":return W&&!U?B():[G(false)];default:if(P("true")){
					return[true]
				}else{
					if(P("false")){
						return[false]
					}else{
						if(P("null")){
							return[null]
						}else{
							if("-.0123456789".indexOf(J)>=0){
								return[D()]
							}else{
								if(P("function")){
									return B(true)
								}
							}
						}
					}
				}return B()
			}
		}try{
			if(!T){
				return null
			}var I=Q(false)[0];F();if(J){
				R("Invalid Json")
			}if(this.jsonId&&typeof (I)=="object"){
				I=this.editable(I)
			}return I
		}catch(S){
			if(E.exceptionOnly){
				throw S
			}if(this.fireEvent("error","decode",S)){
				throw S
			}
		}
	},
	clone:function(A){
		return this.decode(this.encode(A))
	}
});

Ext.ux.JSON=new Ext.ux.Json({
	jsonId:"__JSON__",
	licenseText:"/* This file is created or modified by Ext.ux.Json */"
});

Ext.ux.JsonPanel=Ext.extend(Ext.Panel,{
	layout:"fit",
	border:false,
	bodyBorder:false,
	single:true,
	json:null,
	nocache:false,
	initComponent:function(){
		if(this.autoLoad){
			if(typeof this.autoLoad!=="object"){
				this.autoLoad={
					url:this.autoLoad
				}
			}if(typeof this.autoLoad.nocache=="undefined"){
				this.autoLoad.nocache=this.nocache
			}
		}Ext.ux.JsonPanel.superclass.initComponent.call(this);this.json=new Ext.ux.Json({
			scope:this.scope||this,
			nocache:this.nocache
		});this.addEvents({
			beforejsonload:true,
			afterjsonload:true,
			failedjsonload:false
		})
	},
	setListeners:function(A){
		this.on(A)
	},
	onRender:function(C,A){
		Ext.ux.JsonPanel.superclass.onRender.call(this,C,A);var B=this.getUpdater();B.showLoadIndicator=false;B.on("failure",function(E,D){
			if(this.ownerCt){
				this.ownerCt.el.unmask()
			}if(this.json.fireEvent("error","failedjsonload","url in autoLoad not valid")){
				this.fireEvent("failedjsonload",D)
			}
		}.createDelegate(this));B.on("beforeupdate",function(E,D,F){
			if(this.loadMask&&this.ownerCt){
				this.ownerCt.el.mask(this.loadMsg,this.msgCls)
			}
		}.createDelegate(this));B.setRenderer({
			render:function(E,D,F,G){
				this.apply(D.responseText,G)
			}.createDelegate(this)
		})
	},
	apply:function(A,C){
		this.fireEvent("beforejsonload",A);try{
			this.json.apply(this,A);this.fireEvent("afterjsonload");if(C){
				C()
			}return true
		}catch(B){
			if(this.json.fireEvent("error","failedjsonload",B)&&this.fireEvent("failedjsonload",A,B)){
				Ext.Msg.alert("Failure","Failed to decode load Json:"+B.message)
			}return false
		}
	}
});
Ext.reg("jsonpanel",Ext.ux.JsonPanel);

Ext.ux.JsonWindow=Ext.extend(Ext.Window,{
	layout:"fit",
	border:false,
	single:true,
	json:null,
	nocache:false,
	initComponent:function(){
		if(this.autoLoad){
			if(typeof this.autoLoad!=="object"){
				this.autoLoad={
					url:this.autoLoad
				}
			}if(typeof this.autoLoad.nocache=="undefined"){
				this.autoLoad.nocache=this.nocache
			}
		}this.json=new Ext.ux.Json({
			scope:this.scope||this,
			nocache:this.nocache
		});this.addEvents({
			beforejsonload:true,
			afterjsonload:true,
			failedjsonload:false
		});Ext.ux.JsonWindow.superclass.initComponent.call(this)
	},
	setX:function(A){
		this.setPosition(A,this.y)
	},
	setY:function(A){
		this.setPosition(this.x,A)
	},
	setAlignTo:function(A){
		if(this.rendered){
			this.alignTo(A[0],A[1],A[2])
		}
	},
	setAnchorTo:function(A){
		this.anchorTo(A[0],A[1],A[2],A[3])
	},
	setListeners:function(A){
		this.on(A)
	},
	onRender:function(C,A){
		Ext.ux.JsonWindow.superclass.onRender.call(this,C,A);var B=this.getUpdater();B.showLoadIndicator=false;B.on("failure",function(E,D){
			if(this.ownerCt){
				this.ownerCt.el.unmask()
			}if(this.json.fireEvent("error","failedjsonload","url in autoLoad not valid")){
				this.fireEvent("failedjsonload",D)
			}
		}.createDelegate(this));B.on("beforeupdate",function(E,D,F){
			if(this.loadMask&&this.ownerCt){
				this.ownerCt.el.mask(this.loadMsg,this.msgCls)
			}
		}.createDelegate(this));B.setRenderer({
			render:function(E,D,F,G){
				this.apply(D.responseText,G)
			}.createDelegate(this)
		})
	},
	apply:function(A,C){
		this.fireEvent("beforejsonload",A);try{
			this.json.apply(this,A);this.fireEvent("afterjsonload");if(C){
				C()
			}return true
		}catch(B){
			if(this.json.fireEvent("error","failedjsonload",B)&&this.fireEvent("failedjsonload",A,B)){
				Ext.Msg.alert("Failure","Failed to decode load Json:"+B.message)
			}return false
		}
	}
});
Ext.reg("jsonwindow",Ext.ux.JsonWindow);

Ext.namespace("Ext.ux.guid.tree");
Ext.ux.guid.tree.CodeLoader=function(B,A){
	Ext.apply(this,A);this.designer=B;Ext.tree.TreeLoader.superclass.constructor.call(this)
};
Ext.extend(Ext.ux.guid.tree.CodeLoader,Ext.tree.TreeLoader,{
	jsonId:"__JSON__",
	elementToText:function(C){
		var A=[];C=C||{};if(C[this.jsonId+"xtype"]){
			var B=C[this.jsonId+"xtype"];A.push(typeof (B)=="object"?B.display||B.value:B)
		}else{
			if(C.xtype){
				A.push(C.xtype)
			}
		}if(C.fieldLabel){
			A.push("["+C.fieldLabel+"]")
		}if(C.boxLabel){
			A.push("["+C.boxLabel+"]")
		}if(C.layout){
			A.push("<i>"+C.layout+"</i>")
		}if(C.title){
			A.push("<b>"+C.title+"</b>")
		}if(C.text){
			A.push("<b>"+C.text+"</b>")
		}if(C.region){
			A.push("<i>("+C.region+")</i>")
		}return(A.length==0?"Element":A.join(" "))
	},
	load:function(A,B){
		A.beginUpdate();while(A.firstChild){
			A.removeChild(A.firstChild)
		}if(this.doLoad(A,this.designer.getConfig())){
			if(typeof B=="function"){
				B()
			}
		}A.endUpdate()
	},
	doLoad:function(E,F){
		if(F){
			if(F instanceof Array){
				for(var B=0;B<F.length;B++){
					this.doLoad(E,F[B])
				}
			}else{
				if(!this.designer.isEmpty(F)){
					var D=this.designer.isContainer(this.designer.findByJsonId(F[this.jsonId]));var C={
						text:this.elementToText(F),
						cls:D?"folder":"file",
						leaf:D?false:true,
						jsonId:F[this.jsonId]
					};var G=E.appendChild(new Ext.tree.TreeNode(C));if(F.items){
						for(var B=0,A=F.items.length;B<A;B++){
							this.doLoad(G,F.items[B])
						}
					}
				}
			}
		}return !!F
	}
});
Ext.namespace("Ext.ux.guid.tree");
Ext.ux.guid.tree.JsonTreeLoader=Ext.extend(Ext.tree.TreeLoader,{
	nocache:false,
	createNode:function(attr){
		var childeren=attr.childeren;delete attr.childeren;if(this.baseAttrs){
			Ext.applyIf(attr,this.baseAttrs)
		}if(this.applyLoader!==false){
			attr.loader=this
		}if(typeof attr.uiProvider=="string"){
			attr.uiProvider=this.uiProviders[attr.uiProvider]||eval(attr.uiProvider)
		}if(!childeren){
			return(attr.leaf===false?new Ext.tree.AsyncTreeNode(attr):new Ext.tree.TreeNode(attr))
		}else{
			var node=new Ext.tree.TreeNode(Ext.applyIf(attr,{
				draggable:false
			}));var self=this;for(var i=0,len=childeren.length;i<len;i++){
				if(Ext.isVersion(childeren[i].isVersion)){
					if(childeren[i].wizard){
						childeren[i]["config"]=function(callback){
							var w=new Ext.ux.JsonWindow({
								x:-1000,
								y:-1000,
								autoLoad:this.wizard,
								callback:callback,
								modal:true,
								nocache:self.nocache
							});w.json.on("error",function(type,exception){
								Ext.Msg.alert("Wizard Load Error",type+" "+(typeof (exception)=="object"?exception.message||exception:exception));this.close();return false
							},w);w.show()
						}.createDelegate(childeren[i])
					}var n=this.createNode(childeren[i]);if(n){
						node.appendChild(n)
					}
				}
			}return node
		}
	},
	requestData:function(B,D){
		if(this.dataUrl instanceof Array){
			var C=this.dataUrl;for(var A=0;A<C.length;A++){
				this.dataUrl=C[A];Ext.ux.guid.tree.JsonTreeLoader.superclass.requestData.call(this,B,D)
			}this.dataUrl=C
		}else{
			Ext.ux.guid.tree.JsonTreeLoader.superclass.requestData.call(this,B,D)
		}
	}
});Ext.namespace("Ext.ux.guid.grid");
