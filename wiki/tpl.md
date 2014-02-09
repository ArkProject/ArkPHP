
OTPL(Open Template Programming Language),开放模板编程语言。
======
##术语定义：
`LWS`:线性空白。由连续的空白( )、制表符(\t)组成。正则表达式为：\s

`E(expression)`: 语法表达式。

`CE(Condition expression)`:条件表达式。要求 CE 中不能有线性空白，如果有则必须将 CE 
用括号(())包裹。
    
`VE(Value expression)`：求值表达式。

`NV:(Named variable)`:命名变量。


##标签定义:
模板中所有标签均由定界符 ` {...}` 或   `<!--{...}-->`包裹 `Token + E` 的形式表现。这样的一组字符串被称为一个标签`Markup`,完整的 Markup 只能是 LWS(线性的),不能换行。

`语义标记(token)`：token是由字母(a-zA-Z)、数字(0-9)、下划线(_)、美元符号($)、符号(@)、符号(#)组成。

`独立(span)标签`：指一个 Markup 就可以完全表达语义的Markup类型。

	{token.../}

`块（block）标签`: 指需要多个Markup成对出现才能表达语义的Markup类型。

	{token} //块开始
	...
	{/token} //块结束

`编译时注释(comment)`：

	标签级注释：
	{// ... }

	块(区域)级注释：
	{/*}['
	   comments
	   comments
	{*/}


`冲突解决`：

	按模板原样输出，主要解决如 javascript 等脚本语言的语法问题 ：
	{literal}
	...
	{/literal}

	说明：在一个标签大括号({})中，不能再次出现 左大括号({)和右大括号(})，如果出现请用引号(""),引号("")中的引号(")请用转义符(\)转义。

	关键词：literal,iteration,break,continue,each,for,do,while,odd,even,first,last,if,elif,case,when,in,count


`模板文件名后缀`：
+ *.stpl
+ *.tpl
+ *.stpl.html
+ *.tpl.html




##语法
###符号

`@`: 内置对象或函数，如：@view 引用当前视图上下文；@request 引用当前请求上下文。该对象可自定义并注册。

    如：
    {@request.url/}
    PHP:
    echo $request->url;
    
    {@myfunc('arg')/}
    PHP:
    $view->call['myfunc']('args');
    //如要输出函数返回值，则标签为：
    {$@myfunc('args')}

`$`: 模板变量，也就是在类似 MVC 的 Controller 层等后台程序中生成并传递到模板中的对象。

	例子：
	TPL:
	{$title}
	根据不同的OTPL实现语言不同，编译后的平台代码也不同，以下为 Ark 框架为例：
	编译为PHP后:
	echo $view->bag['title'];
	编译为ASP.NET(C#)后:
	Response.Write(this.ViewData["title"]);
	
`#`: 当前上下文变量引用。

    如：
    {for:i=0 max=10 step=1}
    {#i}
    {/for}
    
    在for循环中 i 为当前生成的临时变量，在for中 要引用 i 则必须使用 #i 。
    
    编译后的PHP代码：
    ```php
        for($i=0;$i<10;$i++){
            echo $i;
        }
    ```
    
    
`.`: 获取对象属性，如：foo.bar

`[]`: 获取数组下标的值，如：arr[index];


`变量`：变量以美元符号 ($) 开头。竖线(|)为可选 其后的字符串表示格式化规则。

    {$token | format}
	注意：变量表达式根语言与数据类型有关，如：$item[3],如果变量item不是集合（数组）类型则可能出错。$obj.property 如果 $obj 不是对象或者没有 property 属性则也会出错。
	
	注意：标签内的分割均用线性空白（空格、制表符）。
	------

`set`

    {var:var_name value=(VE) type='Type String'}
    {set:var VE/}
    {$name | format="tt" type="myfunc"/}

fuc('张三'+'李四');
fuc('张三'。'李四'); str('','')=>ark_strJoin('',el)

###分支：
####if分支
	{if CE}
	...
	{elif CE}
	...
	{else}
	...
	{/if}
	CE：必须。表示条件表达式。

####switch分支
	{case:NV=VE}
	...
	{when CE}
	...
	{else}
	...
	{/case}
	NV：必须。表示本次分支的变量。
	VE：必须。表示取值表达式，可以直接是一个变量。
	CE：必须。表示条件表达式。

###循环：

####for循环：
	{for:NV[=0] to="10" [step="1"]}
	...
	{/for}
	NV：必须。表示本次循环的变量。
	to ：必须。表示本循环的最大值。
	start：可选。表示本次循环的初始值，默认为 0。
	step：可选。表示每次迭代的增量，默认为 1。

####while循环：
	{while CE}
	...
	{/while}
	expression：必须。表示条件表达式。

####do while循环：
	{do CE}
	...
	{/do}
	CE：必须。表示条件表达式。

####迭代：
	{each:NV=CE}
	...
	{/each}
	NV：必须。表示本次迭代的变量。
	in ：必须。表示本将迭代的数据源表达式（集合：数组、列表、字典等）。

`循环上下文变量`：

	停止循环，父级：for、each、do。
	{break}

	将跳过当前本次循环并进入下一次循环，父级：for、each、do。
	{continue}

	获取当前循环的次数(int)。注意：iteration 获取的次数不同于 循环体的变量 如for，它的初始值为 1，每次迭代加 1。该值只能在 for、each、do 上下文中使用。
	$iteration:NV
	var：循环的变量。

	获取当前循环的总次数(int)。注意：该值只能在 for、each、do 上下文中使用。
	$count:NV
	var：循环的变量。

	判断当前循环的第一次循环(bool)。它等同于 $iteration:var==1。注意：该值只能在 for、each、do 上下文中使用。
	$first:NV
	var：循环的变量。

	判断当前循环的最后一次循环(bool)。它等同于 $iteration:var==$count:var。注意：该值只能在 for、each、do 上下文中使用。
	$last:NV
	var：循环的变量。

	判断当前循环的次数是否是偶数(bool)。它等同于 $iteration:var % 2==0。注意：该值只能在 for、each、do 上下文中使用。
	$even:NV
	var：循环的变量。

	判断当前循环的次数是否是奇数(bool)。它等同于 !$even:var。注意：该值只能在 for、each、do 上下文中使用。
	$odd:NV
	var：循环的变量。



##预定义模板函数（内置函数）：

名称(别名)				CLR方法(c#)					说明

强制转换函数：

+ to_short(value)			ToInt16(object)				转换为int16或其默认值
+ to_int(value)			ToInt32(object)
+ to_long(value)			ToInt64(object)
+ to_ushort(value)		ToUInt16(object)
+ to_uint(value)			ToUInt32(object)
+ to_ulong(value)			ToUInt64(object)
+ to_single(value)		ToSingle(object)
+ to_double(value)		ToDouble(object)
+ to_number(value)		ToDecimal(object)
+ to_bool(value)			ToBoolean(object)
+ to_str(value)			ToString(object)
+ to_date(value)			ToDateTime(object)
+ to_guid(value)			ToGuid(object)
+ to_type(value,type)		ToType<T>(object)

类型验证函数：
+ is_short(value)			IsInt16(object)
+ is_int(value)			IsInt32(object)
+ is_long(value)			IsInt64(object)
+ is_ushort(value)		IsUInt16(object)
+ is_uint(value)			IsUInt32(object)
+ is_ulong(value)			IsUInt64(object)
+ is_single(value)		IsSingle(object)
+ is_double(value)		IsDouble(object)
+ is_number(value)		IsDecimal(object)
+ is_bool(value)			IsBoolean(object)
+ is_str(value)			IsString(object)
+ is_date(value)			IsDateTime(object)
+ is_guid(value)			IsGuid(object)

+ is_type(value,type)		IsType<T>(object)			未实现	
+ is_null(value)			IsNull(object)
+ is_empty(value)			IsNullOrEmpty(object)



获取函数：
+ get_short(index,data)		GetInt16(string,object)				转换为int16或其默认值
+ get_int(index,data)			GetInt32(string,object)
+ get_long(index,data)		GetInt64(string,object)
+ get_ushort(index,data)		GetUInt16(string,object)
+ get_uint(index,data)		GetUInt32(string,object)
+ get_ulong(index,data)		GetUInt64(string,object)
+ get_single(index,data)		GetSingle(string,object)
+ get_double(index,data)		GetDouble(string,object)
+ get_number(index,data)		GetDecimal(string,object)
+ get_bool(index,data)		GetBoolean(string,object)
+ get_str(index,data)			GetString(string,object)
+ get_date(index,data)		GetDateTime(string,object)
+ get_guid(index,data)		GetGuid(string,object)
+ get_type(index,data,type)	GetType<T>(string,object)


设置函数：

定义函数：

====================================================================================================================
{@function()}
{hspace:NV}
NV: 保留区域的ID

{var:NV value=CE [type=TYPE]}
{set:NV=VE}

{each:item data=viewdata}
    [item:name]
{/each}



[@dim:name type="string" value="ffg"]
[@set:name value="gfg"]

fgd
{/section}
{if x < y}
    cccx
{else if x==45}
    gggg
{else}
    zzzz
{/if}


{section:name}

{each:item index="index_name" data=hhf}

    [filed:name nav=(getstr("dfgd") * fg) xx="ggd"]
    [@name]
    [item:name.ff.1.hj]

{/each}
fgd
{/section}
{if x < y}
    cccx
{else if x==45}
    gggg
{else}
    zzzz
{/if}

行 8 有语法错误。模板：E:\01工作\02我的项目\Leworks\src\Leworks.WebServer\bin\Debug\Default.aspx

namespace ASPNET
{
	using System;
	public class Text_ViewRender : Leworks.Web.Template.ViewRender
    {
        public override void RenderTo(Leworks.Web.IView view)
        {
            view.Write("hello:\r\n");
            foreach (object item in view.GetViewIteration("item"))
            {
                view.Write(view.GetViewValue("name", item));

                if (ToInt32(view.GetViewValue("age", item)) < 20)
                {
                    view.Write("，太小了\r\n");
                }
                else if (ToInt32(view.GetViewValue("age", item)) > 40)
                {
                    view.Write("，太大了\r\n");
                }
            }
        }
    }
}