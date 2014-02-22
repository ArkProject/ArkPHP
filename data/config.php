<?php
return array (
		'debug' => 'true',
		'lang' => 'zh-CN',
		'encoding' => 'utf8',
		'data。path'=>'~/data/', //权限要求为可读写.
		//数据库配置
		'data.db' => array (
				0 => array (
						'provider' => 'ark\dao\mysql\MysqlProvider',
						'dbhost' => '127.0.0.1',
						'dbname' => 'test',
						'username' => 'root',
						'passwd' => 'rootroot',
						'encoding' => 'utf-8' 
				)
		),
		'data.cache'=>array(),
		//默认路由配置：域名：应用，控制器，动作
		'routing' => array (
				'*' => array (
						'app' => 'myapp',
						'controller' => 'default',
						'action' => 'index' 
				) 
		) 
);
?>