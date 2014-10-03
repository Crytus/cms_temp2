<div id="header" class="clearfix" align="center">{$sitename}管理画面</div>

<div id="navi2" style="float:left; width:160px;">
<ul>
{foreach from=$menu item="item"}
{if $item.act}
{if $item.act==$act}
<li class="menu"><a href="?act={$item.act}" class="sel">{$item.title}</a></li>
{else}
<li class="menu"><a href="?act={$item.act}">{$item.title}</a></li>
{/if}
{else}
<li class="comment">{$item.title}</li>
{/if}
{/foreach}
</ul>
</div>
