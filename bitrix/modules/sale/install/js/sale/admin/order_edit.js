BX.namespace("BX.Sale.Admin.OrderEditPage");

BX.Sale.Admin.OrderEditPage =
{
	formId:  "",
	fieldsUpdaters: {},
	fieldsUpdatersContexts: {},
	statusesNames: {},
	orderId: 0,
	languageId: "",
	siteId: "",
	currency: "",
	currencyLang: "",
	form: null,
	adminTabControlId: "",
	discountRefreshTimeoutId: 0,
	autoPriceChange: true,

	getForm: function()
	{
		if(!BX.Sale.Admin.OrderEditPage.form)
			BX.Sale.Admin.OrderEditPage.form = BX(BX.Sale.Admin.OrderEditPage.formId);

		return BX.Sale.Admin.OrderEditPage.form;
	},

	toggleFix: function(pinObjId, blockObjId)
	{
		var block = BX(blockObjId),
			pinObj = BX(pinObjId);

		if(!block || !pinObj)
			return;

		var isFixed = !BX.hasClass(block, 'adm-detail-tabs-block-pin');

		if(isFixed)
		{
			BX.addClass(block, 'adm-detail-tabs-block-pin');
			pinObj.title = BX.message("SALE_ORDEREDIT_FIX");
			BX.UnFix(block);
		}
		else
		{
			BX.removeClass(block, 'adm-detail-tabs-block-pin');
			pinObj.title = BX.message("SALE_ORDEREDIT_UNFIX");
			BX.Fix(block, {type: 'top'});

			// Table tabs switch off
			window[this.adminTabControlId].ToggleFix('top', false);
			//window[this.adminTabControlId].ToggleFix('bottom', false);
		}

		isFixed = !isFixed;
		BX.userOptions.save('sale_admin', 'sale_order_edit', 'fix_'+blockObjId, (isFixed ? 'Y': 'N'));
	},

	disableSavingButtons: function(disable)
	{
		var i, btn,	elements = ['apply', 'save'];

		for(i in elements)
		{
			if(!elements.hasOwnProperty(i))
				continue;

			btn = BX.findChild(document, {attr : {'name': elements[i]}}, true);

			if (btn)
				btn.disabled = disable;
		}
	},

	showDialog: function(text, title)
	{
		alert(text);

		/*
		var dialog = new BX.CDialog({
			title: title || "",
			content: text,
			height: 100,
			width: 300,
			draggable: true,
			resizable: false
		});

		dialog.ClearButtons();
		dialog.SetButtons([BX.CDialog.btnClose]);
		dialog.Show();
		*/
	},

	/* Fields events handlers */
	onSaveStatusButton: function(orderId, selectId)
	{
		BX.Sale.Admin.OrderAjaxer.sendRequest(
			this.ajaxRequests.saveStatus(orderId, selectId)
		);
	},

	onCancelStatusButton: function(orderId, canceled)
	{
		this.toggleCancelDialog();

		BX.Sale.Admin.OrderAjaxer.sendRequest(
			this.ajaxRequests.cancelOrder(orderId, canceled, BX("FORM_REASON_CANCELED").value)
		);
	},

	getElementValue: function(elementId)
	{
		var element = BX(elementId);

		if(element && typeof element.value != 'undefined')
			return element.value;

		return "";
	},

	getAllFormData: function()
	{
		var form = this.getForm();

		if(!form)
			return {};

		var prepared = BX.ajax.prepareForm(form);

		return !!prepared && prepared.data ? prepared.data : {};
	},

	unRegisterFieldUpdater: function(fieldName, fieldUpdater)
	{
		if(!this.fieldsUpdaters[fieldName])
			return;

		for(var i = this.fieldsUpdaters[fieldName].length-1; i >= 0; i--)
			if(this.fieldsUpdaters[fieldName][i] == fieldUpdater)
				delete(this.fieldsUpdaters[fieldName][i]);
	},

	unRegisterProductFieldsUpdaters: function(basketCode)
	{
		for(var i in this.fieldsUpdaters)
			if(this.fieldsUpdaters.hasOwnProperty(i))
				if(i.indexOf("PRODUCT["+basketCode+"]") != -1)
					delete(this.fieldsUpdaters[i]);
	},

	unRegisterFieldsUpdaters: function(fieldNames)
	{
		for(var i in fieldNames)
			if(fieldNames.hasOwnProperty(i))
				if(this.fieldsUpdaters[fieldNames[i]])
					delete(this.fieldsUpdaters[fieldNames[i]]);
	},

	registerFieldsUpdaters: function(updaters)
	{
		for(var i in updaters)
		{
			if(!updaters.hasOwnProperty(i))
				continue;

			if(typeof this.fieldsUpdaters[i] == 'undefined')
				this.fieldsUpdaters[i] = [];

			this.fieldsUpdaters[i].push(updaters[i]);
		}
	},

	callFieldsUpdaters: function(orderData)
	{
		var ordered = ["DISCOUNTS_LIST", "DELIVERY_PRICE"],
			orderedDone = {};

		for(var i = 0, l = ordered.length-1; i<=l; i++)
		{
			var fieldName = ordered[i];

			if(typeof orderData[fieldName] !== "undefined")
				this.callConcreteFieldUpdater(fieldName, orderData[fieldName]);

			orderedDone[fieldName] = true;
		}

		for(i in orderData)
		{
			if(!orderData.hasOwnProperty(i))
				continue;

			if(orderedDone[i])
				continue;

			this.callConcreteFieldUpdater(i, orderData[i]);
		}
	},

	callConcreteFieldUpdater: function(fieldId, fieldData)
	{
		var context = null,
			callback = null;

		for(var j in this.fieldsUpdaters[fieldId])
		{
			if(!this.fieldsUpdaters[fieldId].hasOwnProperty(j))
				continue;

			var data = this.fieldsUpdaters[fieldId][j];

			if(data.context && data.callback)
			{
				context = data.context;
				callback = data.callback;
			}
			else
			{
				context = null;
				callback = this.fieldsUpdaters[fieldId][j];
			}

			if(callback && typeof callback == "function")
				callback.call(context, fieldData);
		}
	},

	currencyFormat: function(summ, hideCurrency)
	{
		if(BX.Currency && BX.Currency.currencyFormat)
		{
			summ = BX.Currency.currencyFormat(
				summ,
				this.currency,
				hideCurrency ? false : true
			);
		}

		return summ;
	},

	restoreFormData: function(data)
	{
		var form = this.getForm();

		if(!form)
		{
			BX.debug("BX.Sale.Admin.OrderEditPage:restoreFormData() can't find form");
			return false;
		}

		for(var fieldName in data)
			if(data.hasOwnProperty(fieldName))
				if(typeof(form.elements[fieldName]) != "undefined")
					form.elements[fieldName].value = data[fieldName];

		return true;
	},

	createFormBlocker: function()
	{
		var scrollHeight = document.documentElement.scrollHeight,
			clientHeight = document.documentElement.clientHeight,
			height = Math.max(scrollHeight, clientHeight);

		return BX.create('div',{
			props: {
				className: "bx-core-dialog-overlay",
				id: "sale-adm-order-form-blocker"
			},
			style: {
				zIndex: "10001",
				width: "100%",
				height: height+"px",
				backgroundColor: "rgba(57,60,67,0.1)"
			},
			children: [
				BX.create('span',{
					style: {
						zIndex: "10002",
						top: "5%",
						left:"85%",
						position: "fixed",
						background: 'url("/bitrix/panel/main/images/submenu-bg.png") repeat 0 0',
						padding: "15px",
						borderRadius: "5px",
						fontSize: "14px",
						border: "4px solid rgb(230, 230, 230)"
					},
					html: BX.message("SALE_ORDEREDIT_REFRESHING_DATA")
				})
			]
		});
	},

	blockForm: function()
	{
		document.body.appendChild(this.createFormBlocker());
	},

	unBlockForm: function()
	{
		var blocker = BX("sale-adm-order-form-blocker");

		if(blocker)
			blocker.parentNode.removeChild(blocker);
	},

	toggleCancelDialog: function()
	{
		var dialog = BX("sale-adm-status-cancel-dialog");

		if(dialog)
			BX.toggleClass(dialog, "active");
	},

	setStatus: function(statusId)
	{
		BX("STATUS_ID").value = statusId;
	},

	desktopMakeCall: function(phone)
	{
		BX.Sale.Admin.OrderEditPage.desktopRunningCheck(
			function(){ location.href = 'bx://callto/phone/'+phone; },
			function(){ location.href = 'tel:'+phone; }
		);
	},

	desktopRunningCheck: function(successCallback, failureCallback)
	{
		if(typeof(successCallback) == 'undefined')
		{
			return false;
		}
		if(typeof(failureCallback) == 'undefined')
		{
			failureCallback = function(){};
		}

		var dateCheck = (+new Date());

		if(typeof(BXIM) == 'undefined' || BXIM.desktop.ready() || !BXIM.desktopStatus || BXIM.desktopVersion < 18)
		{
			failureCallback(false, dateCheck);
			return false;
		}
		else if(BXIM.desktopVersion < 35)
		{
			successCallback(true, dateCheck);
			return true;
		}

		var checkElement = BX.create("img", {
			attrs : {
				"src" : this.checkUrl+"icon.png?"+dateCheck,
				"data-id": dateCheck
			},
			props : {className : "bx-messenger-out-of-view"},
			events : {
				"error" : function () {
					var checkId = this.getAttribute('data-id');
					failureCallback(false, checkId);
					clearTimeout(BX.desktopUtils.runningCheckTimeout[checkId]);
					BX.remove(this);
				},
				"load" : function () {
					var checkId = this.getAttribute('data-id');
					successCallback(true, checkId);
					clearTimeout(BX.desktopUtils.runningCheckTimeout[checkId]);
					BX.remove(this);
				}
			}
		});
		document.body.appendChild(checkElement);
		this.runningCheckTimeout[dateCheck] = setTimeout(function(){
			failureCallback(false, dateCheck);
			clearTimeout(BX.desktopUtils.runningCheckTimeout[dateCheck]);
			BX.remove(this);
		}, 500);

		return true;
	},

	changeCancelBlock: function(orderId, params)
	{
		var block = BX("sale-adm-status-cancel-blocktext"),
			cancelReasonNode = BX("FORM_REASON_CANCELED"),
			buttonNode = BX("sale-adm-status-cancel-dialog-btn"),
			newBlockContent = "";

		if(params.CANCELED == "Y")
		{
			newBlockContent = '<div class="adm-s-select-popup-element-selected-bad">' +
				'<span>'+BX.message("SALE_ORDER_STATUS_CANCELED")+'</span>' +
				params.DATE_CANCELED +
				'<a href="/bitrix/admin/user_edit.php?lang='+BX.Sale.Admin.OrderEditPage.languageId+'&ID='+params.EMP_CANCELED_ID+'">'+
					BX.util.htmlspecialchars(params.EMP_CANCELED_NAME) +
				'</a>' +
			'</div>';

			block.style.textAlign = "start";
			cancelReasonNode.disabled = true;
			buttonNode.innerHTML = BX.message("SALE_ORDER_STATUS_CANCEL_CANCEL");
			buttonNode.onclick = function(){ BX.Sale.Admin.OrderEditPage.onCancelStatusButton(orderId,"Y"); };
		}
		else
		{
			newBlockContent = '<a href="javascript:void(0);" onclick="BX.Sale.Admin.OrderEditPage.toggleCancelDialog();">'+BX.message("SALE_ORDER_STATUS_CANCELING")+'</a>';
			block.style.textAlign = "center";
			cancelReasonNode.disabled = false;
			buttonNode.innerHTML = BX.message("SALE_ORDER_STATUS_CANCEL");
			buttonNode.onclick = function(){ BX.Sale.Admin.OrderEditPage.onCancelStatusButton(orderId,"N"); };
		}

		block.innerHTML = newBlockContent;
	},

	onRefreshOrderDataAndSave: function()
	{
		BX.Sale.Admin.OrderEditPage.blockForm();
		var form = this.getForm();

		form.appendChild(
			BX.create('input',{
				props: {
					name: 'refresh_data_and_save',
					type: 'hidden',
					value: 'Y'
				}
			})
		);

		form.submit();
	},

	onOrderCopy: function(params)
	{
		BX.Sale.Admin.OrderEditPage.blockForm();
		var form = this.getForm();
		form.action = params;
		form.submit();
	},

	/**
	 * @param {string} itemCode
	 * @param {string} itemType
	 * @param {array} itemDiscounts
	 * @param {array} discountsList
	 * @param {string} mode
	 * @returns {div}
	 */
	createDiscountsNode: function(itemCode, itemType, itemDiscounts, discountsList, mode)
	{
		var discountsNode = null,
			i,
			l,
			discountId;

		if(itemDiscounts && discountsList && discountsList.DISCOUNT_LIST)
		{
			discountsNode = BX.create('table');

			for(i = 0, l = itemDiscounts.length; i<l; i++)
			{
				if(!itemDiscounts[i])
					continue;

				discountId = itemDiscounts[i].DISCOUNT_ID;

				if(discountsList.DISCOUNT_LIST[discountId])
				{
					this.addDiscountItemRow(
						itemCode,
						itemType,
						itemDiscounts[i],
						discountsList.DISCOUNT_LIST[discountId],
						discountsNode,
						mode
					);
				}
			}
		}
		else
		{
			discountsNode = BX.create('span',{
				html: "&nbsp;"
			});
		}

		return BX.create('div',{ children: [discountsNode] });
	},

	/**
	 *
	 * @param {string} itemCode
	 * @param {string} itemType
	 * @param {array} itemDiscount
	 * @param {array} discountParams
	 * @param {HTMLElement} table
	 * @param {string} mode
	 * @returns {HTMLElement}
	 */
	addDiscountItemRow: function(itemCode, itemType, itemDiscount,  discountParams, table, mode)
	{
		var row = table.insertRow(-1),
			itemAttrs = {'data-discount-id': discountParams.DISCOUNT_ID},
			name,
			checkbox;

		if (itemType == 'DISCOUNT_LIST')
		{
			itemAttrs['data-discount'] = 'Y';
			itemAttrs['data-use-coupons'] = (discountParams.USE_COUPONS);
		}
		if (itemType === 'BASKET' || itemType === 'DELIVERY')
		{
			itemAttrs['data-coupon-id'] = (itemDiscount.COUPON_ID ? itemDiscount.COUPON_ID : '-');
			itemAttrs['data-discount-target'] = 'Y';
		}

		name = "DISCOUNTS["+itemType+"]"+(itemCode != "" ? "["+itemCode+"]" : "")+"["+discountParams.DISCOUNT_ID+"]";
		checkbox = BX.create('input',{
				props: {
					type: "checkbox",
					name: name,
					checked: itemDiscount.APPLY == "Y",
					value: "Y",
					disabled: (mode == "VIEW")
				},
				attrs: itemAttrs
			});

		row.appendChild(
			BX.create('td',{
				children: [
					BX.create('input',{
						props: {
							type: "hidden",
							name: name,
							value: "N"
						}
					}),
					checkbox
				]
			})
		);

		if(mode == "EDIT")
		{
			BX.bind(checkbox, "click", function(e){
				BX.Sale.Admin.OrderEditPage.setDiscountCheckbox(e);
				BX.Sale.Admin.OrderEditPage.refreshDiscounts();
			});
		}

		var value = "";

		if(typeof itemDiscount.DESCR == "object")
		{
			if(itemDiscount.DESCR)
			{
				for(var i in itemDiscount.DESCR)
					if(itemDiscount.DESCR.hasOwnProperty(i))
						value += itemDiscount.DESCR[i];
			}
			else
			{
				value = BX.message("SALE_ORDEREDIT_DISCOUNT_UNKNOWN")+" %";
			}
		}
		else
		{
			value = itemDiscount.DESCR;
		}

		row.appendChild(
			BX.create('td',{
				html: "<strong>"+value+"</strong>"
			})
		);

		if(discountParams.EDIT_PAGE_URL)
		{
			row.appendChild(
				BX.create('td',{
					children: [
						BX.create('a',{
							props: {
								href: discountParams.EDIT_PAGE_URL,
								className: "adm-s-detail-content-sale-link"
							},
							html: discountParams.NAME
						})
					]
				})
			);
		}
		else
		{
			row.appendChild(
				BX.create('td',{
					children: [
						BX.create('span',{
							html: discountParams.NAME
						})
					]
				})
			);
		}

		return row;
	},

	setDiscountCheckbox: function(e)
	{
		var target = e.target,
			coll,
			i,
			summaryChecked,
			itemCoupon;

		if (!!target && target.hasAttribute('data-discount-id'))
		{
			if (target.hasAttribute('data-coupon'))
			{
				coll = BX.findChild(
					BX.Sale.Admin.OrderEditPage.getForm(),
					{ attribute: {
						'data-discount-id': target.getAttribute('data-discount-id'),
						'data-coupon-id': target.getAttribute('data-discount-coupon')
					}},
					true,
					true
				);
				if (coll.length > 0)
				{
					for (i = 0; i < coll.length; i++)
						coll[i].checked = target.checked;
				}

				summaryChecked = false;
				coll = BX.findChild(
					BX.Sale.Admin.OrderEditPage.getForm(),
					{ attribute: {
						'data-discount-id': target.getAttribute('data-discount-id'),
						'data-coupon': 'Y'
					}},
					true,
					true
				);
				if (coll.length > 0)
				{
					for (i = 0; i < coll.length; i++)
					{
						if (coll[i].checked)
							summaryChecked = true;
					}
				}

				coll = BX.findChild(
					BX.Sale.Admin.OrderEditPage.getForm(),
					{ attribute: {
						'data-discount-id': target.getAttribute('data-discount-id'),
						'data-discount': 'Y',
						'data-use-coupons': 'Y'
					}},
					true,
					false
				);
				if (coll)
					coll.checked = summaryChecked;
				coll = null;
			}
			else if (target.hasAttribute('data-discount'))
			{
				coll = BX.findChild(
					BX.Sale.Admin.OrderEditPage.getForm(),
					{ attribute: {
						'data-discount-id': target.getAttribute('data-discount-id')
					}},
					true,
					true
				);
				if (coll.length > 0)
				{
					for (i = 0; i < coll.length; i++)
						coll[i].checked = target.checked;
				}
				coll = null;
			}
			else if (target.hasAttribute('data-discount-target'))
			{
				if (target.checked)
				{
					coll = BX.findChild(
						BX.Sale.Admin.OrderEditPage.getForm(),
						{ attribute: {
							'data-discount-id': target.getAttribute('data-discount-id'),
							'data-discount': 'Y'
						}},
						true,
						false
					);
					if (coll)
						coll.checked = true;
					if (target.hasAttribute('data-coupon-id'))
					{
						itemCoupon = target.getAttribute('data-coupon-id');
						if (itemCoupon != '' && itemCoupon != '-')
						{
							coll = BX.findChild(
								BX.Sale.Admin.OrderEditPage.getForm(),
								{ attribute: {
									'data-discount-id': target.getAttribute('data-discount-id'),
									'data-discount-coupon': itemCoupon
								}},
								true,
								false
							);
							if (coll)
								coll.checked = true;
						}
					}
					coll = null;
				}
			}
		}
	},

	onProblemCloseClick: function(orderId, blockId)
	{
		BX.Sale.Admin.OrderAjaxer.sendRequest(
			this.ajaxRequests.unmarkOrder(orderId, blockId)
		);
	},

	refreshDiscounts: function()
	{
		if(this.discountRefreshTimeoutId > 0)
			return;

		this.discountRefreshTimeoutId = setInterval(function(){

				BX.Sale.Admin.OrderAjaxer.sendRequest(
					BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData({
							operation: "DISCOUNTS_REFRESH"
						}
					)
				);

				clearInterval(BX.Sale.Admin.OrderEditPage.discountRefreshTimeoutId);
				BX.Sale.Admin.OrderEditPage.discountRefreshTimeoutId = 0;
			},
		500
		);
	},

	/* Ajax request templates */
	ajaxRequests: {
		addProductToBasket: function(productId, quantity, replaceBasketCode, columns, customPrice)
		{
			var postData = {
				action: "addProductToBasket",
				productId: productId,
				quantity: quantity,
				replaceBasketCode: replaceBasketCode ? replaceBasketCode : "",
				columns: columns,
				callback: BX.Sale.Admin.OrderAjaxer.refreshOrderData.callback
			};

			if(customPrice !== false)
				postData.customPrice = customPrice;

			return BX.Sale.Admin.OrderAjaxer.refreshOrderData.modifyParams(postData);
		},

		cancelOrder: function(orderId, canceled, comment)
		{
			return {
				action: "cancelOrder",
				orderId: orderId,
				canceled: canceled,
				comment: comment,
				callback: function(result)
				{
					BX.Sale.Admin.OrderEditPage.unBlockForm();

					if(result && !result.ERROR)
						BX.Sale.Admin.OrderEditPage.changeCancelBlock(orderId, result);
					else if(result && result.ERROR)
						BX.Sale.Admin.OrderEditPage.showDialog(BX.message("SALE_ORDER_STATUS_CANCEL_ERROR") + ": "+result.ERROR);
					else
						BX.debug(BX.message("SALE_ORDER_STATUS_CANCEL_ERROR"));
				}
			};
		},
		saveStatus: function(orderId, selectId)
		{
			var select = BX(selectId);

			if(!select)
				BX.debug("Error getting select object with id: "+selectId);

			if(typeof select.value == 'undefined')
				BX.debug("Error getting select value id: "+selectId);

			return {
				action: "saveStatus",
				orderId: orderId,
				statusId: select.value,
				callback: function(result)
				{
					var message;
					result.CAN_USER_EDIT = "Y";
					if(result && result.CAN_USER_EDIT && !result.ERROR)
					{
						BX.Sale.Admin.OrderEditPage.callFieldsUpdaters({STATUS_ID: select.value});
						BX.Sale.Admin.OrderEditPage.disableSavingButtons(result.CAN_USER_EDIT != "Y");
						message = BX.message("SALE_ORDER_STATUS_CHANGED_SUCCESS");
					}
					else if(result && result.ERROR)
					{
						message = BX.message("SALE_ORDER_STATUS_CHANGE_ERROR")+": " + result.ERROR;
					}
					else
					{
						message = BX.message("SALE_ORDER_STATUS_CHANGE_ERROR");
					}

					BX.Sale.Admin.OrderEditPage.showDialog(message);
				}
			};
		},

		getOrderFields: function(params, refreshFormDataAfter)
		{
			return  {
				action: "getOrderFields",
				givenFields: params.givenFields,
				demandFields: params.demandFields,
				callback: function(result)
				{
					if(result && result.RESULT_FIELDS && !result.ERROR)
					{
						BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result.RESULT_FIELDS);

						if(refreshFormDataAfter)
						{
							BX.Sale.Admin.OrderAjaxer.sendRequest(
								BX.Sale.Admin.OrderEditPage.ajaxRequests.refreshOrderData()
							);
						}
					}
					else if(result && result.ERROR)
					{
						BX.debug("Error receiving fields: " + result.ERROR);
					}
					else
					{
						BX.debug("Error receiving fields!");
					}
				}
			};
		},

		refreshOrderData: function(additional)
		{
			if(!BX.Sale.Admin.OrderAjaxer.refreshOrderData.getFlag())
			{
				return BX.Sale.Admin.OrderAjaxer.refreshOrderData.modifyParams({
					action: "refreshOrderData",
					additional: additional,
					callback: BX.Sale.Admin.OrderAjaxer.refreshOrderData.callback
				});
			}
		},

		unmarkOrder: function(orderId, blockId)
		{
			return {
				action: "unmarkOrder",
				orderId: orderId,
				callback: function(result)
				{
					BX.Sale.Admin.OrderEditPage.unBlockForm();

					if(result && !result.ERROR)
						BX(blockId).style.display = 'none';
					else if(result && result.ERROR)
						BX.Sale.Admin.OrderEditPage.showDialog(BX.message("SALE_ORDEREDIT_UNMARK_ERROR") + ": "+result.ERROR);
					else
						BX.debug(BX.message("SALE_ORDEREDIT_UNMARK_ERROR"));
				}
			};
		},

		getOrderTails: function(orderId, formType, idPrefix)
		{
			return {
				action: "getOrderTails",
				orderId: orderId,
				formType: formType,
				idPrefix: idPrefix,
				callback: function(result)
				{
					if(result && !result.ERROR)
					{
						BX.Sale.Admin.OrderEditPage.callFieldsUpdaters(result);
						var node;

						if(typeof result.ANALYSIS != 'undefined')
						{
							node = BX('sale-adm-order-analysis-content');

							if(node)
								node.innerHTML = result.ANALYSIS;
						}

						if(typeof result.SHIPMENTS != 'undefined')
						{
							node = BX('sale-adm-order-shipments-content');

							if(node)
							{
								var data = BX.processHTML(result.SHIPMENTS);
								BX.loadCSS(data['STYLE']);

								node.innerHTML = data['HTML'];

								for (var i in data['SCRIPT'])
									BX.evalGlobal(data['SCRIPT'][i]['JS']);
							}
						}
					}
					else if(result && result.ERROR)
					{
						BX.Sale.Admin.OrderEditPage.showDialog(result.ERROR);
					}
					else
					{
						BX.debug("Can't order view tails");
					}
				}
			};
		}
	}
};