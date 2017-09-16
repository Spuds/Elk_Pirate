<?php

/**
 * Pirate speak for the land lubbers
 *
 * @package "Pirate Talk" Addon for Elkarte
 * @author Spuds
 * @copyright (c) 2017 Spuds
 * @license GPL
 *
 * @version 1.0
 *
 */

/**
 * Integration hook, integrate_general_mod_settings
 *
 * - Not a lot of settings for the pirate so we add them under the predefined
 * Miscellaneous
 *
 * @param mixed[] $config_vars
 */
function igm_pirate(&$config_vars)
{
	loadLanguage('PirateTalk');

	if (!empty($config_vars))
	{
		$config_vars = array_merge($config_vars, array(''));
	}

	$config_vars = array_merge($config_vars, array(
		array('check', 'elk_pirate_enabled'),
	));
}

/**
 * Integration hook, Called from bbcparser, integrate_post_bbc_parser,
 *
 * - Allows access to the message after the BBC parser has run on it
 *
 * @param string $message
 */
function ipbp_pirate(&$message)
{
	require_once(SUBSDIR . '/PirateTalk.class.php');

	$pirate = \Pirate_Talk::instance();
	$pirate->pirate_filter($message);
}