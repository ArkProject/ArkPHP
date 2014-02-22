<?php
namespace myapp;
defined ( 'ARK' ) or exit ( 'access denied' );
class App extends \ark\Application{
	function init(){
		echo '<h1>im in here</h1>';
		parent::init();
	}
}
?>