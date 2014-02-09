<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'deny access' );

abstract class ActionResult{
	public abstract function Execute();
}