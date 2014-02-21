<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo $view->__get('hello'); ?></title>
</head>
<body>
今天是：<?php echo $view->format('datetime','Y-m-d',$view->__get('now')); ?>
<br/>
b

=========
{if($val<15)}
if
{ elif #y<14}
elif 1
{elif (#y<14)}
elif 2
{else}
else
{/if}
===========
{for:i=0 max=18 step=1}
for
{/for}
{for:i max=18 step=1}
for2{#i}
{/for}
{while('tru\'e')}
	{@request}
{/while}

</body>
</html>
