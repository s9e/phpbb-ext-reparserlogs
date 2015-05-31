<?php

/**
* @package   s9e\reparserlogs
* @copyright Copyright (c) 2015 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\reparserlogs;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use s9e\TextFormatter\Bundles\MediaPack;

class listener implements EventSubscriberInterface
{
	/**
	* @var string
	*/
	protected $dir;

	/**
	* Constructor
	*
	* @param string $dir
	*/
	public function __construct($dir)
	{
		$this->dir = $dir;
	}

	public static function getSubscribedEvents()
	{
		return array('core.text_formatter_s9e_parse_after' => 'onParse');
	}

	public function onParse($event)
	{
		$parser = $event['parser']->get_parser();
		$text   = $parser->getText();
		$crc    = crc32($text);
		$log    = '';
		foreach ($parser->getLogger()->get() as $entry)
		{
			list($level, $msg, $context) = $entry;
			if ($level === 'debug')
			{
				continue;
			}

			if (isset($context['tag']))
			{
				$tag = $context['tag'];
				$context['tag'] = array(
					'name' => $tag->getName(),
					'pos'  => $tag->getPos(),
					'len'  => $tag->getLen()
				);
			}
			$log .= "$level $msg " . json_encode($context) . "\n";
		}
		if ($log !== '')
		{
			file_put_contents($this->dir . '/' . $crc . '.txt', $text);
			file_put_contents($this->dir . '/' . $crc . '.log', $log);
		}
	}
}