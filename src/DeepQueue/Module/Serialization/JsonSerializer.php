<?php
namespace DeepQueue\Module\Serialization;


use DeepQueue\Base\Serialization\IToString;
use DeepQueue\Base\Serialization\IFromString;


class JsonSerializer implements IFromString, IToString
{
	use \DeepQueue\Base\Serialization\TFromJson;
	use \DeepQueue\Base\Serialization\TToJson;
}