Ext.define('ImageViewer', {
    extend: 'Ext.Panel',

    layout: {
        type: 'vbox',
        align: 'stretch'
    },
	
    config: {
        isMoving: false,
        imageWidth: null,
        imageHeight: null,
        originalImageWidth: null,
        originalImageHeight: null,
        clickX: null,
        clickY: null,
        lastMarginX: null,
        lastMarginY: null,
        rotation: 0
    },

    initComponent: function () {
        var me = this;

        me.tooltips = me.tooltips || {};

        me.tooltips = Ext.applyIf(me.tooltips, {
            stretchHorizontally: 'Stretch horizontally',
            stretchVertically: 'Stretch vertically',
            stretchOptimally: 'Stretch optimally',
            zoomIn: 'Zoom in',
            zoomOut: 'Zoom out',
            rotateClockwise: 'Rotate clockwise',
            rotateAntiClockwise: 'Rotate anticlockwise'
        });

		me.pdfItem = {
			xtype : "container",
			itemId: 'pdfContent',
			style: "transform-origin: top left;-webkit-transform-origin: top left;"+
				"-ms-transform-origin: top left;box-shadow: 0 0 5px 5px #888;margin-bottom:10px;height:88%",
			html : "",
			listeners: {
				afterrender : function(){
					me.loadPdf();
				}
			}
		};
		me.imageItem = {
			xtype: 'image',
			itemId: 'imageContent',
			mode: 'element',
			src: me.src.url,
			style: "transform-origin: top left;-webkit-transform-origin: top left;"+
				"-ms-transform-origin: top left;box-shadow: 0 0 5px 5px #888;margin-bottom:10px",
			listeners: {
				render: function (image) {
					image.el.dom.onload = function () {							
						me.setRotation(0);
						me.rotateImage();
						me.setOriginalImageWidth(image.el.dom.width);
						me.setOriginalImageHeight(image.el.dom.height);
						me.setImageWidth(image.el.dom.width);
						me.setImageHeight(image.el.dom.height);
						//me.stretchOptimally();
						me.stretchHorizontally();
					};
				}
			}
		};
		
		if(me.src.fileType == "pdf")
		{
			me.pdfItem.hidden = false;
			me.imageItem.hidden = true;
		}	
		else
		{
			me.pdfItem.hidden = true;
			me.imageItem.hidden = false;
		}
        me.items = [{
            xtype: 'toolbar',
            defaults: {
                tooltipType: 'title'
            },
            items: [{
                xtype: 'button',
                tooltip: me.tooltips.stretchHorizontally,
                icon: '/generalUI/ext4/resources/ImageViewer/stretch_horizontally.png',
                listeners: { click: me.stretchHorizontally, scope: me }
            }, {
                xtype: 'button',
                tooltip: me.tooltips.stretchVertically,
                icon: '/generalUI/ext4/resources/ImageViewer/stretch_vertically.png',
                listeners: { click: me.stretchVertically, scope: me }
            }, {
                xtype: 'button',
                tooltip: me.tooltips.stretchOptimally,
                icon: '/generalUI/ext4/resources/ImageViewer/stretch_optimally.png',
                listeners: { click: me.stretchOptimally, scope: me }
            }, {
                xtype: 'button',
                tooltip: me.tooltips.zoomIn,
                icon: '/generalUI/ext4/resources/ImageViewer/zoom_in.png',
                listeners: { click: me.zoomIn, scope: me }
            }, {
                xtype: 'button',
                tooltip: me.tooltips.zoomOut,
                icon: '/generalUI/ext4/resources/ImageViewer/zoom_out.png',
                listeners: { click: me.zoomOut, scope: me }
            }, {
                xtype: 'button',
                tooltip: me.tooltips.rotateClockwise,
                icon: '/generalUI/ext4/resources/ImageViewer/arrow_rotate_clockwise.png',
                listeners: { click: me.rotateClockwise, scope: me }
            }, {
                xtype: 'button',
                tooltip: me.tooltips.rotateAntiClockwise,
                icon: '/generalUI/ext4/resources/ImageViewer/arrow_rotate_anticlockwise.png',
                listeners: { click: me.rotateAntiClockwise, scope: me }
            }]
        }, {
            xtype: 'container',
            itemId: 'imagecontainer',
            flex: 1,
			autoScroll:true,
            style: {
                overflow: 'hidden',
                backgroundColor: '#f2f1f0',
                padding: '10px',
                cursor: 'pointer'
            },
            items: [me.imageItem,me.pdfItem]
        }];

        me.callParent();			
    },

	loadPdf : function(){
		if(this.src.fileType == "pdf")
		{
			pdfContainer = this.getPdf();
			pdfContainer.removeAll();
			DivId = pdfContainer.getId();
			
			var options = {
						pdfOpenParams: {
							navpanes: 0,
							toolbar: 0,
							statusbar: 0
							//view: "FitV",
							//pagemode: "thumbs",
							//page: 2
						},
						forcePDFJS: true,
						PDFJS_URL: "/generalUI/pdfobject/viewer.html"
					};
			PDFObject.embed(this.src.url, "#"+DivId, options);
		}
	},

    initEvents: function () {
        var me = this;

        me.callParent();
    },

    stretchHorizontally: function () {
        var me = this,
            imageContainerWidth = me.getImageContainer().getWidth();

        me.setImageSize({
            width: imageContainerWidth - 40,
            height: me.getOriginalImageHeight() * (imageContainerWidth - 20) / me.getOriginalImageWidth()
        });
    },

    stretchVertically: function () {
        var me = this,
            imageContainerHeight = me.getImageContainer().getHeight();

        me.setImageSize({
            width: me.getOriginalImageWidth() * (imageContainerHeight - 20) / me.getOriginalImageHeight(),
            height: imageContainerHeight - 40
        });
    },

    stretchOptimally: function () {
        var me = this,
            imageContainer = me.getImageContainer(),
            adjustedImageSize = me.getAdjustedImageSize();

        if (adjustedImageSize.width * imageContainer.getHeight() / adjustedImageSize.height > imageContainer.getWidth()) {
            me.stretchHorizontally();
        } else {
            me.stretchVertically();
        }
    },

    zoomOut: function (btn, event, opts) {
        var me = this,
            margins = me.getMargins(),
            adjustedImageSize = me.getAdjustedImageSize();

        /*me.setMargins({
            top: margins.top + adjustedImageSize.height * 0.05,
            left: margins.left + adjustedImageSize.width * 0.05
        });*/

        me.setImageSize({
            width: adjustedImageSize.width * 0.9,
            height: me.getOriginalImageHeight() * adjustedImageSize.width * 0.9 / me.getOriginalImageWidth()
        });

        event.stopEvent();
    },

    zoomIn: function (btn, event, opts) {
        var me = this,
            margins = me.getMargins(),
            adjustedImageSize = me.getAdjustedImageSize();

       /* me.setMargins({
            top: margins.top - adjustedImageSize.height * 0.05,
            left: margins.left - adjustedImageSize.width * 0.05
        });*/

        me.setImageSize({
            width: adjustedImageSize.width * 1.1,
            height: me.getOriginalImageHeight() * adjustedImageSize.width * 1.1 / me.getOriginalImageWidth()
        });

        event.stopEvent();
    },

    rotateClockwise: function () {
        var me = this,
            rotation = me.getRotation();

        rotation += 90;

        if (rotation > 360) {
            rotation -= 360;
        }

        me.setRotation(rotation);
        me.rotateImage();
    },

    rotateAntiClockwise: function () {
        var me = this,
            rotation = me.getRotation();

        rotation -= 90;

        if (rotation < 0) {
            rotation += 360;
        }

        me.setRotation(rotation);
        me.rotateImage();
    },

    rotateImage: function () {
        var me = this,
            tmpOriginalWidth,
            transformStyle = 'rotate(' + me.getRotation() + 'deg)';

		var rotation = me.getRotation();
		if(rotation == 90)
			transformStyle += "translateY(0%)";
		if(rotation == 180)
			transformStyle += "translate(-100%,-100%)";
		if(rotation == 270)
			transformStyle += " translateX(-100%) translateY(-100%)";

		tmpOriginalWidth = me.getOriginalImageWidth();
        me.setOriginalImageWidth(me.getOriginalImageHeight());
        me.setOriginalImageHeight(tmpOriginalWidth);
		
        me.getImage().getEl().applyStyles({
            'transform': transformStyle,
            '-o-transform': transformStyle,
            '-ms-transform': transformStyle,
            '-moz-transform': transformStyle,
            '-webkit-transform': transformStyle
        });

        //me.setMargins(me.getMargins());
    },

    setMargins: function (margins) {
		
        var me = this,
            rotation = me.getRotation(),
            adjustedImageSize = me.getAdjustedImageSize(),
            imageContainer = me.getImageContainer(),
            imageContainerWidth = imageContainer.getWidth(),
            imageContainerHeight = imageContainer.getHeight();

        if (adjustedImageSize.width > imageContainerWidth - 20) {
            if (margins.left > 0) {
                margins.left = 0;
            } else if (margins.left < imageContainerWidth - adjustedImageSize.width - 20) {
                margins.left = imageContainerWidth - adjustedImageSize.width - 20;
            }
        } else {
            if (margins.left < 0) {
                margins.left = 0;
            } else if (margins.left > imageContainerWidth - adjustedImageSize.width - 20) {
                margins.left = imageContainerWidth - adjustedImageSize.width - 20;
            }
        }

        if (adjustedImageSize.height > imageContainerHeight - 20) {
            if (margins.top > 0) {
                margins.top = 0;
            } else if (margins.top < imageContainerHeight - adjustedImageSize.height - 20) {
                margins.top = imageContainerHeight - adjustedImageSize.height - 20;
            }
        } else {
            if (margins.top < 0) {
                margins.top = 0;
            } else if (margins.top > imageContainerHeight - adjustedImageSize.height - 20) {
                margins.top = imageContainerHeight - adjustedImageSize.height - 20;
            }
        }

        if (rotation === 90 || rotation === 270) {
            var marginAdjustment = (me.getImageHeight() - me.getImageWidth()) / 2;
            margins.top = margins.top - marginAdjustment;
            margins.left = margins.left + marginAdjustment;
        }

        me.getImage().getEl().setStyle('margin-left', margins.left + 'px');
        me.getImage().getEl().setStyle('margin-top', margins.top + 'px');
    },

    getMargins: function () {
        var me = this,
            rotation = me.getRotation(),
            imageEl = me.getImage().getEl();

        var margins = {
            top: parseInt(imageEl.getStyle('margin-top'), 10),
            left: parseInt(imageEl.getStyle('margin-left'), 10)
        };

        if (rotation === 90 || rotation === 270) {
            var marginAdjustment = (me.getImageHeight() - me.getImageWidth()) / 2;
            margins.top = margins.top + marginAdjustment;
            margins.left = margins.left - marginAdjustment;
        }

        return margins;
    },

    getAdjustedImageSize: function () {
        var me = this,
            rotation = me.getRotation();

        if (rotation === 90 || rotation === 270) {
            return {
                width: me.getImageHeight(),
                height: me.getImageWidth()
            };
        } else {
            return {
                width: me.getImageWidth(),
                height: me.getImageHeight()
            };
        }
    },

    setImageSize: function (size) {
        var me = this,
            rotation = me.getRotation();

        if (rotation === 90 || rotation === 270) {
            me.setImageWidth(size.height);
            me.setImageHeight(size.width);
        } else {
            me.setImageWidth(size.width);
            me.setImageHeight(size.height);
        }
    },

    applyImageWidth: function (width) {
        var me = this;
        me.getImage().setWidth(width);
        return width;
    },

    applyImageHeight: function (height) {
        var me = this;
        me.getImage().setHeight(height);
        return height;
    },

    getImage: function () {
        return this.down("[itemId=imageContent]");
    },
	
	 getPdf: function () {
        return this.down("[itemId=pdfContent]");
    },

    getImageContainer: function () {
        return this.query('#imagecontainer')[0];
    }
});

Ext.define('MultiImageViewer', {
    extend: 'ImageViewer',

    requires: ['Ext.XTemplate'],

    config: {
        currentImage: 0,
        length: 0,
        sources: null
    },

    initComponent: function () {
        var me = this;

        me.setSources(me.src);
        me.setLength(me.src.length);

        me.currentImageTemplate = me.currentImageTemplate || 'مشاهده تصویر {i} از {total}';
        me.currentImage = 0;
		
		me.src = me.src[0];

        me.on('beforerender', me.insertPageUI, me);

        me.callParent();
    },

    insertPageUI: function () {
        var me = this,
            toolbar = this.down('toolbar');

        toolbar.add([{
            xtype: 'tbfill'
        }, {
            xtype: 'button',
            icon: '/generalUI/ext4/resources/ImageViewer/resultset_previous.png',
            listeners: { click: me.previousImage, scope: me }
        }, {
            xtype: 'tbtext'
        }, {
            xtype: 'button',
            icon: '/generalUI/ext4/resources/ImageViewer/resultset_next.png',
            listeners: { click: me.nextImage, scope: me }
        }]);

        me.updateImageText();
    },

    nextImage: function () {
        var me = this,
            index = this.getCurrentImage();

        index += 1;

        if (index === me.getLength()) {
            index = 0;
        }

        me.setCurrentImage(index);
        me.updateImageText();
    },

    previousImage: function () {
        var me = this,
            index = this.getCurrentImage();

        index -= 1;

        if (index < 0) {
            index = me.getLength() - 1;
        }

        me.setCurrentImage(index);
        me.updateImageText();
    },

    applyCurrentImage: function (index) {
        var me = this;
		
		if(me.getSources()[index].fileType == "pdf")
		{
			me.getImage().hide();
			me.getPdf().show();
			me.src = me.getSources()[index];
			me.loadPdf();
		}	
		else
		{
			me.getImage().show();
			me.getPdf().hide();
			me.getImage().el.dom.src = me.getSources()[index].url;
		}
		

        return index;
    },

    updateImageText: function () {
        var me = this,
            tpl = new Ext.XTemplate(me.currentImageTemplate);

        me.down('toolbar').down('tbtext').setText(tpl.apply({
            i: me.getCurrentImage() + 1,
            total: me.getLength()
        }));
    },

    _isCurrentImageInitialized: function () {
        return true;
    }
});

Ext.define('ImageGallery', {
    extend: 'Ext.Panel',

    layout: {
        type: 'vbox',
        align: 'stretch'
    },
	
    config: {
		cls: 'galleria',
        isMoving: false,
        imageWidth: null,
        imageHeight: null,
        originalImageWidth: null,
        originalImageHeight: null,
        clickX: null,
        clickY: null,
        lastMarginX: null,
        lastMarginY: null,
        rotation: 0
    },
	height : 300,
	width : 600,
	galleria_image: null,
	thumnail_list: null,
	nav_left: null,
	nav_right: null,
	current_image_index: 0,

    initComponent: function () {
        var me = this;
		
		me.items = [{
			xtype : "container",
			itemId : "galleria_imageContainer",
			cls : "galleria-image-container",
			height : this.height-65,
			items : [{
				xtype : "image",
				itemId : "galleria_image"				
			}]
		},{
			xtype: 'panel',
			layout: 'hbox',
			style: 'border: 1px solid #D0D0D0;margin-top:5px',	
			items : [{
				xtype: 'button',
				itemId: 'nav_right',
				cls: 'galleria-image-nav-right',
				style: 'border:0'
			},{
				xtype : "container",
				flex : 1,
				style : "text-align:left",
				items :{
					xtype: 'dataview',
					itemId : "thumnail_list",
					cls : "galleria-dataview",
					itemSelector: 'div.x-list-item',
					autoScroll : true,
					selectedItemCls: 'gallery-image-selected',
					store : me.store,
					tpl  : Ext.create('Ext.XTemplate',
						'<tpl for=".">',
							'<div class=x-list-item>',
								'<img src={url} />',
							'</div>',
						'</tpl>'
					)
				}
			},{
				xtype: 'button',
				itemId: 'nav_left',
				cls: 'galleria-image-nav-left',
				style: 'border:0'
			}]
		}];
				
		me.callParent();	
		this.galleria_imageContainer = this.down('[itemId=galleria_imageContainer]');
		this.galleria_image = this.down('image[itemId=galleria_image]');
		this.thumnail_list = this.down('[itemId=thumnail_list]');

		this.nav_left = this.down('button[itemId=nav_left]');
		this.nav_right = this.down('button[itemId=nav_right]');

		this.thumnail_list.on('itemclick', this.onChangeImage, this);
		this.nav_left.on('click', this.onNavLeft, this);
		this.nav_right.on('click', this.onNavRight, this);
		
    },
	constructor: function () {
        this.callParent(arguments);
        if(this.store.totalCount > 0){
			this.thumnail_list.select(0);
			this.moveSelection();
		}
    },
    initEvents: function () {
        var me = this;
        me.callParent();
    },
	selectFirst : function(){
		if(this.store.totalCount > 0)
			this.thumnail_list.select(0);
	},
	onNavLeft: function (btn, e, eOpts) {
		this.current_image_index--;
		if (this.current_image_index < 0)
			this.current_image_index = this.thumnail_list.getStore().data.length - 1;

		this.moveSelection(btn, e, eOpts);
	},
	onNavRight: function (btn, e, eOpts) {
		this.current_image_index++;
		if (this.current_image_index > this.thumnail_list.getStore().data.length - 1)
			this.current_image_index = 0;

		this.moveSelection(btn, e, eOpts);
	},
	moveSelection: function (btn, e, eOpts) {
			
		this.thumnail_list.select(this.current_image_index);
		this.galleria_image.setSrc(this.thumnail_list.getStore().getAt(this.current_image_index).data.url);
		
		el = this.thumnail_list.getEl();
		el.setLeft(this.current_image_index*(-50));
		this.stretchOptimally();
	},
	onChangeImage: function (view, record, item, index, e, eOpts) {
		this.current_image_index = index;
		this.galleria_image.setSrc(record.data.url);
		this.stretchOptimally();
	},
	stretchOptimally: function () {
        var me = this,
            image = me.galleria_image,
            containerSize = {
				height : this.galleria_imageContainer.getHeight(),
				width : this.galleria_imageContainer.getWidth()
			};

		var d = this.galleria_image.getEl().dom;
		if (d.naturalWidth/containerSize.width > d.naturalHeight/containerSize.height) 
		{
            me.galleria_image.setWidth(containerSize.width);
			me.galleria_image.setHeight((containerSize.width/d.naturalWidth)*d.naturalHeight);
        } else {
			me.galleria_image.setHeight(containerSize.height);
			me.galleria_image.setWidth((containerSize.height/d.naturalHeight)*d.naturalWidth);
        }
    }
});


