BX.namespace('BX.Sale.PersonalProfileComponent');

(function() {
	BX.Sale.PersonalProfileComponent.PersonalProfileDetail = {
		init: function ()
		{
			var propertyFileList = document.getElementsByClassName('sale-personal-profile-detail-property-file');
			Array.prototype.forEach.call(propertyFileList, function(propertyFile)
			{
				var deleteFileElement = propertyFile.getElementsByClassName('profile-property-input-delete-file')[0];
				BX.bindDelegate(propertyFile, 'click', { 'class': 'profile-property-check-file' }, BX.proxy(function(event)
				{
					if (deleteFileElement.value != "")
					{
						idList = deleteFileElement.value.split(';');
						if (idList.indexOf(event.target.value) === -1)
						{
							deleteFileElement.value = deleteFileElement.value + ";" + event.target.value;
						}
						else
						{
							idList.splice(idList.indexOf(event.target.value), 1);
							deleteFileElement.value = idList.join(";");
						}
					}
					else
					{
						deleteFileElement.value = event.target.value;
					}
				}, this));
			});
		}
	}
})();
