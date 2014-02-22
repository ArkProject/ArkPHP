﻿ArkPHP
=======
ArkPHP 是一个免费开源的，快速、简单的面向对象的轻量级PHP开发框架。
当前版本 v1.0.1 build 0

##编码规范 Coding Specification

##支持 Support

PHP v5.3或以上（开发版本，未测试其他版本）.

PHP v5.3 or later(development only,untested other PHP version).

##源码层次结构 Source Code Layout
	
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

##文档 Document
	请访问wiki：https://github.com/ArkProject/ArkPHP/wiki

##发布历史 Release History
版本必须包文件命名规则：
arkphp-{version}.(rar|7z|zip|etc.)

暂无发布

##协议 License

ArkPHP框架在无特别说明外全部源码均是遵循 [Apache License 2.0](http://www.apache.org/licenses/LICENSE-2.0.html) 协议发布。

The ArkPHP framework is released under the terms of the [Apache License 2.0](http://www.apache.org/licenses/LICENSE-2.0.html).
  
  
