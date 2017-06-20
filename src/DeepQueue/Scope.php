<?php
namespace DeepQueue;


use Skeleton\Skeleton;


class Scope
{
	use \Objection\TStaticClass;
	
	
	/** @var Skeleton */
	private static $skeleton;
	
	/**
	 * @return mixed
	 */
	public static function skeleton(string $interface)
	{
		if (!self::$skeleton)
			self::$skeleton = SkeletonSetup::create();
		
		return self::$skeleton->get($interface);
	}
}