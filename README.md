ArkPHP
=======
ArkPHP 是一个免费开源的，快速、简单的面向对象的轻量级PHP开发框架。
当前版本 v1.0.1 build 0

##支持
  PHP v5.3或以上（开发版本，未测试其他版本）

##源码架层次结构
	
	|	README.md
	|	.gitignore
	+---ark						//ArkPHP 框架目录
	|	|	ark.php
	|	|	*.class.php
	|	+---sub namespace		//二级名字空间
	|	|	|	*.class.php
	+---apps					//应用目录。 类似于 .net 的 Areas
	|	+---appname				//应用，必须符合 MVC结构.
	|	|	|	App.class.php	//特殊类,类似于 .Net 下的 Global.cs，必须继承 ark\Application类，并且类名只能是 appname\App. 它不是必须的。
	|	|	+---controllers		//控制器类目录
	|	|	+---models			//数据模型类目录
	|	|	+---views			//视图文件目录
	|
	\---data
	|	+---.ark				//ark框架运行时目录，必须有可读写权限。如果目录不存在则会尝试自动创建。
	|	|	config.php
	|		
	\---web
	|	+---content				//全局静态文件目录
	|	|		+---css
	|	|		+---scripts
	|	|		+---images
	|	|	index.php			//入口文件

##文档
	请访问wiki：https://github.com/ArkProject/ArkPHP/wiki

##发布历史

arkphp-{version}-{encoding}.rar //版本规则

暂无发布

##协议
  ArkPHP遵循 Apache License 2.0 发布。协议参考地址：http://www.apache.org/licenses/LICENSE-2.0.html
