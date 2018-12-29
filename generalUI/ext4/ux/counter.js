Ext.define('Ext.form.field.HSpinner', {
	extend: 'Ext.form.field.Trigger',
	alias: 'widget.hspinnerfield',
	alternateClassName: 'Ext.form.HSpinner',
	requires: ['Ext.util.KeyNav'],
	trigger1Cls: Ext.baseCSSPrefix + 'form-spinner-plus',
	trigger2Cls: Ext.baseCSSPrefix + 'form-spinner-minus',
	spinUpEnabled: true,
	spinDownEnabled: true,
	keyNavEnabled: true,
	mouseWheelEnabled: true,
	repeatTriggerClick: true,
	onSpinUp: Ext.emptyFn,
	onSpinDown: Ext.emptyFn,
	triggerTplPlus: '<td style="{triggerStyle}" valign="top">' +
			'<div class="' + Ext.baseCSSPrefix + 'trigger-index-0 ' + Ext.baseCSSPrefix + 'form-trigger ' + Ext.baseCSSPrefix + 'form-spinner-plus role="button"></div>' +
			'</td>',
	triggerTplMinus: 
			'<td style="{triggerStyle}" valign="top">' +
			'<div class="' + Ext.baseCSSPrefix + 'trigger-index-1 ' + Ext.baseCSSPrefix + 'form-trigger ' + Ext.baseCSSPrefix + 'form-spinner-minus" role="button"></div>' +
			'</td>' +
			'</tr>',
	initComponent: function () {
		this.callParent();
		this.addEvents(
				'spin',
				'spinup',
				'spindown'
				);
	},
	onRender: function () {
		var me = this,
				triggers;
		me.callParent(arguments);
		triggers = me.triggerEl;
		me.spinUpEl = triggers.item(0);
		me.spinDownEl = triggers.item(1);
		me.setSpinUpEnabled(me.spinUpEnabled);
		me.setSpinDownEnabled(me.spinDownEnabled);
		if (me.keyNavEnabled) {
			me.spinnerKeyNav = new Ext.util.KeyNav(me.inputEl, {
				scope: me,
				up: me.spinUp,
				down: me.spinDown
			});
		}
		if (me.mouseWheelEnabled) {
			me.mon(me.bodyEl, 'mousewheel', me.onMouseWheel, me);
		}
	},
	getSubTplMarkup: function () {
		var me = this,
				field = Ext.form.field.Base.prototype.getSubTplMarkup.apply(me, arguments);
		return '<table id="' + me.id + '-triggerWrap" class="' + Ext.baseCSSPrefix + 'form-trigger-wrap" cellpadding="0" cellspacing="0">' +
				'<tbody>' +
				'<tr>' + 
					me.getTriggerMarkup('plus') +
					'<td id="' + me.id + '-inputCell">' + field + '</td>' +
					me.getTriggerMarkup('minus') +
				'</tbody></table>';
	},
	getTriggerMarkup: function (align) {
		var me = this,
				hideTrigger = (me.readOnly || me.hideTrigger);
		if(align == "plus")
			return me.getTpl('triggerTplPlus').apply({
				triggerStyle: 'width:' + me.triggerWidth + (hideTrigger ? 'px;display:none' : 'px')
			});
		else
			return me.getTpl('triggerTplMinus').apply({
				triggerStyle: 'width:' + me.triggerWidth + (hideTrigger ? 'px;display:none' : 'px')
			});
	},
	getTriggerWidth: function () {
		var me = this,
				totalTriggerWidth = 0;
		if (me.triggerWrap && !me.hideTrigger && !me.readOnly) {
			totalTriggerWidth = me.triggerWidth;
		}
		return totalTriggerWidth;
	},
	onTrigger1Click: function () {
		this.spinUp();
	},
	onTrigger2Click: function () {
		this.spinDown();
	},
	onTriggerWrapMousup: function () {
		this.inputEl.focus();
	},
	spinUp: function () {
		var me = this;
		if (me.spinUpEnabled && !me.disabled) {
			me.fireEvent('spin', me, 'up');
			me.fireEvent('spinup', me);
			me.onSpinUp();
		}
	},
	spinDown: function () {
		var me = this;
		if (me.spinDownEnabled && !me.disabled) {
			me.fireEvent('spin', me, 'down');
			me.fireEvent('spindown', me);
			me.onSpinDown();
		}
	},
	setSpinUpEnabled: function (enabled) {
		var me = this,
				wasEnabled = me.spinUpEnabled;
		me.spinUpEnabled = enabled;
		if (wasEnabled !== enabled && me.rendered) {
			me.spinUpEl[enabled ? 'removeCls' : 'addCls'](me.trigger1Cls + '-disabled');
		}
	},
	setSpinDownEnabled: function (enabled) {
		var me = this,
				wasEnabled = me.spinDownEnabled;
		me.spinDownEnabled = enabled;
		if (wasEnabled !== enabled && me.rendered) {
			me.spinDownEl[enabled ? 'removeCls' : 'addCls'](me.trigger2Cls + '-disabled');
		}
	},
	onMouseWheel: function (e) {
		var me = this,
				delta;
		if (me.hasFocus) {
			delta = e.getWheelDelta();
			if (delta > 0) {
				me.spinUp();
			} else if (delta < 0) {
				me.spinDown();
			}
			e.stopEvent();
		}
	},
	onDestroy: function () {
		Ext.destroyMembers(this, 'spinnerKeyNav', 'spinUpEl', 'spinDownEl');
		this.callParent();
	}
});
Ext.define('Ext.form.field.Counter', {
	extend: 'Ext.form.field.HSpinner',
	alias: 'widget.counterfield',
	alternateClassName: ['Ext.form.CounterField', 'Ext.form.Counter'],
	allowDecimals: true,
	decimalSeparator: '.',
	submitLocaleSeparator: true,
	decimalPrecision: 2,
	minValue: Number.NEGATIVE_INFINITY,
	maxValue: Number.MAX_VALUE,
	step: 1,
	minText: 'The minimum value for this field is {0}',
	maxText: 'The maximum value for this field is {0}',
	nanText: '{0} is not a valid number',
	negativeText: 'The value cannot be negative',
	baseChars: '0123456789',
	autoStripChars: false,
	fieldStyle: 'text-align: center;',
	initComponent: function () {
		var me = this,
				allowed;
		me.callParent();
		me.setMinValue(me.minValue);
		me.setMaxValue(me.maxValue);
		if (me.disableKeyFilter !== true) {
			allowed = me.baseChars + '';
			if (me.allowDecimals) {
				allowed += me.decimalSeparator;
			}
			if (me.minValue < 0) {
				allowed += '-';
			}
			allowed = Ext.String.escapeRegex(allowed);
			me.maskRe = new RegExp('[' + allowed + ']');
			if (me.autoStripChars) {
				me.stripCharsRe = new RegExp('[^' + allowed + ']', 'gi');
			}
		}
	},
	getErrors: function (value) {
		var me = this,
				errors = me.callParent(arguments),
				format = Ext.String.format,
				num;
		value = Ext.isDefined(value) ? value : this.processRawValue(this.getRawValue());
		if (value.length < 1) {
			return errors;
		}
		value = String(value).replace(me.decimalSeparator, '.');
		if (isNaN(value)) {
			errors.push(format(me.nanText, value));
		}
		num = me.parseValue(value);
		if (me.minValue === 0 && num < 0) {
			errors.push(this.negativeText);
		} else if (num < me.minValue) {
			errors.push(format(me.minText, me.minValue));
		}
		if (num > me.maxValue) {
			errors.push(format(me.maxText, me.maxValue));
		}
		return errors;
	},
	rawToValue: function (rawValue) {
		var value = this.fixPrecision(this.parseValue(rawValue));
		if (value === null) {
			value = rawValue || null;
		}
		return  value;
	},
	valueToRaw: function (value) {
		var me = this,
				decimalSeparator = me.decimalSeparator;
		value = me.parseValue(value);
		value = me.fixPrecision(value);
		value = Ext.isNumber(value) ? value : parseFloat(String(value).replace(decimalSeparator, '.'));
		value = isNaN(value) ? '' : String(value).replace('.', decimalSeparator);
		return value;
	},
	getSubmitValue: function () {
		var me = this,
				value = me.callParent();
		if (!me.submitLocaleSeparator) {
			value = value.replace(me.decimalSeparator, '.');
		}
		return value;
	},
	onChange: function () {
		this.toggleSpinners();
		this.callParent(arguments);
	},
	toggleSpinners: function () {
		var me = this,
				value = me.getValue();
		valueIsNull = value === null;
		me.setSpinUpEnabled(valueIsNull || value < me.maxValue);
		me.setSpinDownEnabled(valueIsNull || value > me.minValue);
	},
	setMinValue: function (value) {
		this.minValue = Ext.Number.from(value, Number.NEGATIVE_INFINITY);
		this.toggleSpinners();
	},
	setMaxValue: function (value) {
		this.maxValue = Ext.Number.from(value, Number.MAX_VALUE);
		this.toggleSpinners();
	},
	parseValue: function (value) {
		value = parseFloat(String(value).replace(this.decimalSeparator, '.'));
		return isNaN(value) ? null : value;
	},
	fixPrecision: function (value) {
		var me = this,
				nan = isNaN(value),
				precision = me.decimalPrecision;
		if (nan || !value) {
			return nan ? '' : value;
		} else if (!me.allowDecimals || precision <= 0) {
			precision = 0;
		}
		return parseFloat(Ext.Number.toFixed(parseFloat(value), precision));
	},
	beforeBlur: function () {
		var me = this,
				v = me.parseValue(me.getRawValue());
		if (!Ext.isEmpty(v)) {
			me.setValue(v);
		}
	},
	onSpinUp: function () {
		var me = this;
		if (!me.readOnly) {
			me.setValue(Ext.Number.constrain(me.getValue() + me.step, me.minValue, me.maxValue));
		}
	},
	onSpinDown: function () {
		var me = this;
		if (!me.readOnly) {
			me.setValue(Ext.Number.constrain(me.getValue() - me.step, me.minValue, me.maxValue));
		}
	}
});