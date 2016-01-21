<?php

/**
 * Created by PhpStorm.
 * User: Матюхин_МП
 * Date: 15.01.2016
 * Time: 14:02
 */
class CHtml {

	public static function createTag($tagName, $htmlOptions = [], $content = []) {

		if (!is_array($content)) $content = array($content);
		if (!is_array($htmlOptions)) $htmlOptions = array($htmlOptions);

		$result = '<' . $tagName;
		foreach ($htmlOptions as $param => $option) {
			$result .= $option !== null ? sprintf(' %s="%s"', $param, $option) : sprintf(' %s', $param);
		}

		if (!count($content)) return $result . '/>' . PHP_EOL; // <input type="text"/>

		$result .= '>' . PHP_EOL;
		$result .= join(PHP_EOL, $content);
		$result .= '</' . $tagName . '>' . PHP_EOL;

		return $result;
	}

	public static function createButton($text, $options = null) {

		$options['class'] = get_param($options, 'class', 'btn btn-default');
		$options['type'] = get_param($options, 'type', 'button');

		return self::createTag('button', $options, $text);
	}

	public static function createLink($text, $href = '#', $options = null) {

		$options['href'] = $href;

		return self::createTag('a', $options, $text);
	}

	public static function createOption($title, $value, $options = null) {
		$options['value'] = $value;

		return self::createTag('option', $options, $title);
	}
}