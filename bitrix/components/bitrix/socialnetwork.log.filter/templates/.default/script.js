__logOnDateChange = function(sel)
{
	var bShowFrom=false, bShowTo=false, bShowHellip=false, bShowDays=false;

	if(sel.value == 'interval')
		bShowFrom = bShowTo = bShowHellip = true;
	else if(sel.value == 'before')
		bShowTo = true;
	else if(sel.value == 'after' || sel.value == 'exact')
		bShowFrom = true;
	else if(sel.value == 'days')
		bShowDays = true;

	BX('flt_date_from_span').style.display = (bShowFrom? '':'none');
	BX('flt_date_to_span').style.display = (bShowTo? '':'none');
	BX('flt_date_hellip_span').style.display = (bShowHellip? '':'none');
	BX('flt_date_day_span').style.display = (bShowDays? 'inline-block':'none');
	BX('flt_date_day_text_span').style.display = (bShowDays? 'inline-block':'none');
};

function __logOnReload(log_counter)
{
	if (BX("menu-popup-lenta-sort-popup"))
	{
		var arMenuItems = BX.findChildren(BX("menu-popup-lenta-sort-popup"), { className: 'lenta-sort-item' }, true);

		if (!BX.hasClass(arMenuItems[0], 'lenta-sort-item-selected'))
		{
			for (var i = 0; i < arMenuItems.length; i++)
			{
				if (i == 0)
					BX.addClass(arMenuItems[i], 'lenta-sort-item-selected');
				else if (i != (arMenuItems.length-1))
					BX.removeClass(arMenuItems[i], 'lenta-sort-item-selected');
			}
		}
	}

	if (BX("lenta-sort-button"))
	{
		var menuButtonText = BX.findChild(BX("lenta-sort-button"), { className: 'lenta-sort-button-text-internal' }, true, false);
		if (menuButtonText)
			menuButtonText.innerHTML = BX.message('sonetLFAllMessages');
	}

	var counter_cont = BX("sonet_log_counter_preset", true);
	if (counter_cont)
	{
		if (parseInt(log_counter) > 0)
		{
			counter_cont.style.display = "inline-block";
			counter_cont.innerHTML = log_counter;
		}
		else
		{
			counter_cont.innerHTML = '';
			counter_cont.style.display = "none";
		}
	}
}

BitrixLFFilter = function ()
{
	this.filterPopup = false;
	this.currentName = null;

	this.obInputName = {};
	this.obSearchInput = {};

	this.obInputContainerName = {};
	this.obContainerInput = {};
};

BitrixLFFilter.prototype.initFilter = function(params)
{
	__logOnDateChange(document.forms['log_filter'].flt_date_datesel);
	BX('flt_date_from_span').onclick = function(){
		BX.calendar({node: this, field: BX('flt_date_from'), bTime: false});
	};
	BX('flt_date_to_span').onclick = function(){
		BX.calendar({node: this, field: BX('flt_date_to'), bTime: false});
	};
};

BitrixLFFilter.prototype.initDestination = function(params)
{
	this.obInputName[params.name] = params.inputName;
	this.obSearchInput[params.name] = BX(params.inputName);
	this.obInputContainerName[params.name] = params.inputContainerName;
	this.obContainerInput[params.name] = BX(params.inputContainerName);

	if (
		typeof params.items != 'undefined'
		&& typeof params.items.department != 'undefined'
	)
	{
		if (typeof params.items.extranetRoot != 'undefined')
		{
			for(var key in params.items.extranetRoot)
			{
				if (params.items.extranetRoot.hasOwnProperty(key))
				{
					params.items.department[key] = params.items.extranetRoot[key];
				}
			}
		}

		if (!params.items.departmentRelation)
		{
			params.items.departmentRelation = BX.SocNetLogDestination.buildDepartmentRelation(params.items.department);
		}
	}

	BX.SocNetLogDestination.init({
		name : params.name,
		searchInput : this.obSearchInput[params.name],
		extranetUser : !!params.extranetUser,
		departmentSelectDisable : !!params.departmentSelectDisable,
		bindMainPopup : {
			node: params.bindNode,
			offsetTop: '5px',
			offsetLeft: '15px'
		},
		bindSearchPopup : {
			node: params.bindNode,
			offsetTop : '5px',
			offsetLeft: '15px'
		},
		callback : {
			select : BX.proxy(this.onSelectDestination, {
				name: params.name,
				containerInput: BX(params.inputContainerName),
				inputContainerName: params.inputContainerName,
				inputName: params.inputName,
				searchInput: BX(params.inputName),
				resultFieldName: params.resultFieldName
			}),
			unSelect : BX.proxy(this.onUnSelectDestination, {
				name: params.name,
				inputContainerName: params.inputContainerName,
				inputName: params.inputName,
				searchInput: BX(params.inputName)
			})
		},
		items : params.items,
		itemsLast : params.itemsLast,
		itemsSelected : params.itemsSelected,
		isCrmFeed : false,
		useClientDatabase: true,
		destSort: params.destSort,
		allowAddUser: false,
		allowSearchEmailUsers: !params.extranetUser,
		userNameTemplate: params.userNameTemplate
	});
	BX.bind(this.obSearchInput[params.name], 'click', function(e) {
		oLFFilter.currentName = params.name;
		BX.SocNetLogDestination.openDialog(params.name);
		return BX.PreventDefault(e);
	});
	BX.bind(this.obSearchInput[params.name], 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
		formName: params.name,
		inputName: oLFFilter.obInputName[params.name]
	}));
	BX.bind(this.obSearchInput[params.name], 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
		formName: params.name,
		inputName: oLFFilter.obInputName[params.name]
	}));

};

BitrixLFFilter.prototype.clearInput = function()
{
	if (this.obContainerInput[this.currentName])
	{
		var arItems = BX.findChildren(this.obContainerInput[this.currentName], { className: 'feed-add-post-destination' }, false);
		for (var i = 0; i < arItems.length; i++)
		{
			BX.SocNetLogDestination.deleteItem(arItems[i].attributes['data-id'].value, arItems[i].attributes['data-type'].value, this.currentName);
		}
	}
};

BitrixLFFilter.prototype.onSelectDestination = function(item, type, search, bUndeleted)
{
	oLFFilter.clearInput();

	BX.SocNetLogDestination.BXfpSelectCallback({
		formName: this.name,
		item: item,
		type: type,
		search: search,
		bUndeleted: bUndeleted,
		containerInput: this.containerInput,
		valueInput: this.searchInput,
		varName: this.resultFieldName
	});

	this.searchInput.style.display = "none";
	if (
		this.name == 'feed-filter-created-by'
		&& BX("flt_comments_cont")
	)
	{
		BX("flt_comments_cont").style.display = "block";
	}

	BX.SocNetLogDestination.closeDialog();
	BX.SocNetLogDestination.closeSearch();
};

BitrixLFFilter.prototype.onUnSelectDestination = function(item)
{
	var elements = BX.findChildren(BX(this.inputContainerName), {attribute: {'data-id': '' + item.id + ''}}, true);
	if (elements !== null)
	{
		for (var j = 0; j < elements.length; j++)
		{
			BX.remove(elements[j]);
		}
	}
	BX(this.inputName).value = '';

	this.searchInput.style.display = "inline-block";
	if (
		this.name == 'feed-filter-created-by'
		&& BX("flt_comments_cont")
	)
	{
		BX("flt_comments_cont").style.display = "none";
	}
};

BitrixLFFilter.prototype.ShowFilterPopup = function(bindElement)
{
	if (!oLFFilter.filterPopup)
	{
		BX.ajax.get(BX.message('sonetLFAjaxPath'), function(data)
		{
			BX.closeWait(bindElement);

			oLFFilter.filterPopup = new BX.PopupWindow(
				'bx_log_filter_popup',
				bindElement,
				{
					closeIcon : false,
					offsetTop: 5,
					autoHide: true,
					zIndex : -100,
					//angle : { offset : 59},
					className : 'sonet-log-filter-popup-window',
					events : {
						onPopupClose: function() {
							if (!BX.hasClass(this.bindElement, "pagetitle-menu-filter-set"))
								BX.removeClass(this.bindElement, "pagetitle-menu-filter-selected")
						},
						onPopupShow: function() { BX.addClass(this.bindElement, "pagetitle-menu-filter-selected")}
					}
				}
			);

			var filter_block = BX.create('DIV', {html: BX.util.trim(data)});
			oLFFilter.filterPopup.setContent(filter_block.firstChild);
			oLFFilter.filterPopup.show();
		});
	}
	else
	{
		oLFFilter.filterPopup.show();
	}
};

BitrixLFFilter.prototype.__SLFShowExpertModePopup = function(bindObj)
{
	var modalWindow = new BX.PopupWindow('setExpertModePopup', bindObj, {
		closeByEsc: false,
		closeIcon: false,
		autoHide: false,
		overlay: true,
		events: {},
		buttons: [],
		zIndex : 0,
		content: BX.create('DIV', {
			children: [
				BX.create('DIV', {
					props: {
						className: 'bx-slf-popup-title'
					},
					text: BX.message('sonetLFExpertModePopupTitle')
				}),
				BX.create('DIV', {
					props: {
						className: 'bx-slf-popup-content'
					},
					children: [
						BX.create('DIV', {
							props: {
								className: 'bx-slf-popup-cont-title'
							},
							html: BX.message('sonetLFExpertModePopupText1')
						}),
						BX.create('DIV', {
							props: {
								className: 'bx-slf-popup-descript'
							},
							children: [
								BX.create('DIV', {
									html: BX.message('sonetLFExpertModePopupText2')
								}),
								BX.create('IMG', {
									props: {
										className: 'bx-slf-popup-descript-img'
									},
									attrs: {
										src: BX.message('sonetLFExpertModeImagePath'),
										width: 354,
										height: 201
									}
								})
							]
						})
					]
				}),
				BX.create('DIV', {
					props: {
						className: 'popup-window-buttons'
					},
					children: [
						BX.create('SPAN', {
							props: {
								className: 'popup-window-button popup-window-button-accept'
							},
							events: {
								click: function () {
									BX.ajax({
										method: 'POST',
										dataType: 'json',
										url: BX.message('ajaxControllerURL'),
										data: {
											sessid : BX.bitrix_sessid(),
											closePopup: 'Y'
										},
										onsuccess: function(response)
										{
											if (
												typeof (response) != 'undefined'
												&& typeof (response.SUCCESS) != 'undefined'
												&& response.SUCCESS == 'Y'
											)
											{
												modalWindow.close();
												top.location = top.location.href;
											}
										}
									});
								}
							},
							children: [
								BX.create('SPAN', {
									props: {
										className: 'popup-window-button-left'
									}
								}),
								BX.create('SPAN', {
									props: {
										className: 'popup-window-button-text'
									},
									text: BX.message('sonetLFDialogRead')
								}),
								BX.create('SPAN', {
									props: {
										className: 'popup-window-button-right'
									}
								})
							]
						})
					]
				})
			]
		})
	});
	modalWindow.show();
};

oLFFilter = new BitrixLFFilter;
window.oLFFilter = oLFFilter;