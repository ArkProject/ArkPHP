<?php
namespace ark;
defined ( 'ARK' ) or exit ( 'access denied' );

abstract class ActionResult{
	public abstract function Execute();
}