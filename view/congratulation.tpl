{include file="_header_frontend.tpl"}
<div class="main_content">
	{if isset($challenge)}
	<div class="header_bar">
		<div class="page_title">
			<h3 class="left">Congratulation!</h3>
		</div>
	</div><br/>
	<table class="user_add show_challenge" style="height: auto;">
		<tr>
			<td><div class="congratulation_page">{$challenge->congratulation_page}<br/><hr/></div></td>
		</tr>
	</table>
	{/if}
</div>
{include file="_footer_frontend.tpl"}
