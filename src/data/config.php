<?php
return array (
		'debug' => 'true',
		'lang' => 'zh-CN',
		'encoding' => 'utf8',
		'data_path'=>'~/data/', //权限要求为可读写.
		//数据库配置
		'db' => array (
				0 => array (
						'provider' => 'ark\dao\mysql\MysqlProvider',
						'provider_path' => '~/modules/mysql/',
						'dbhost' => '127.0.0.1',
						'dbname' => 'test',
						'username' => 'sa',
						'passwd' => 'sa',
						'encoding' => 'utf-8' 
				)
		),
		//默认路由配置：域名：应用，控制器，动作
		'routing' => array (
				'*' => array (
						'app' => 'default',
						'controller' => 'default',
						'action' => 'index' 
				) 
		) 
);
?>