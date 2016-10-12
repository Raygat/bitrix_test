<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CJSCore::Init(array('im_desktop'));
?>
<div id="bx-notifier-panel" class="bx-notifier-panel bx-messenger-hide">
	<span class="bx-notifier-panel-left"></span><span class="bx-notifier-panel-center"><span class="bx-notifier-drag">
	</span><span class="bx-notifier-indicators"><a href="javascript:void(0)" class="bx-notifier-indicator bx-notifier-call" title=""><span class="bx-notifier-indicator-text"></span><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count"></span>
		</a><a href="javascript:void(0)" class="bx-notifier-indicator bx-notifier-message" title=""><span class="bx-notifier-indicator-text"></span><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count"></span>
		</a><a href="javascript:void(0)" class="bx-notifier-indicator bx-notifier-notify" title=""><span class="bx-notifier-indicator-text"></span><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count"></span>
		</a><a class="bx-notifier-indicator bx-notifier-mail" href="#mail" title="" target="_blank"><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count"></span>
		</a><a class="bx-notifier-indicator bx-notifier-network" href="#network" title="" target="_blank"><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count"></span>
		</a></span>
	</span><span class="bx-notifier-panel-right"></span>
</div>

<script type="text/javascript">
	BX.ready(function(){
		BX.desktop.init();
	});
<?=CIMMessenger::GetTemplateJS(Array(), $arResult)?>
	if (location.hash)
	{
		BX.ready(function(){
			setTimeout(function(){
				BXIM.openMessenger(location.hash.substr(1));
			}, 500);
		});
	}
</script>