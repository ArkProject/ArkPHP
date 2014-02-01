Ark PHP Framework v1.0.1
support php framework v5.4(development only,nut not test other version).

ArkPHP
	src
		framework
			ark.php
			functions.php
			core
				application.class.php
				controller.class.php
				module.class.php
			module
		content
			css
			scripts
			images
			plugs
		app_data
			config.php
			locals
				en-US.php
				zh-CN.php
		apps
			default
				app.php
				controllers
				models
				views
	
	doc
		readme.txt
	build
		arkphp-version-encoding.rar
	
	
routing	
	MVC controls params:
		_a: defined app name. defaults: default
		_c: defined controller name. defaults: default
		_v: defined action name. default: index
	
		