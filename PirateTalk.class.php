<?php
/**
 * Pirate speak for the land lubbers
 *
 * @package "Pirate Talk" Addon for Elkarte
 * @author Spuds
 * @copyright (c) 2017 Spuds
 * @license GPL
 *
 * This file contains code covered by:
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Dougal Campbell
 * @license GPL
 */

/**
 * The Pirate Talk
 */
class Pirate_Talk
{
	/** @var int 1 in $chance of occurrence */
	protected $chance = 5;

	/** @var array The pattern to pirate conversion */
	protected $patterns = array();

	/** @var array The added pirate spice */
	protected $shouts = array();

	/** @var string The text we convert */
	protected $content = '';

	/** @var Pirate_Talk Sole private HttpReq instance */
	private static $_pirate = null;

	/**
	 * Pirate_Render constructor.
	 */
	public function __construct()
	{
		if ($this->canRender())
		{
			$this->_load_patterns();
		}
	}

	/**
	 * If the addon is enabled and in the appropriate area
	 *
	 * @return bool
	 */
	public function canRender()
	{
		global $context, $modSettings;

		return (!empty($modSettings['elk_pirate_enabled'])
			&& in_array($context['site_action'], array('display', 'messageindex', 'boardindex')));
	}

	/**
	 * Does the message translation
	 *
	 * @param string $content
	 */
	public function pirate_filter(&$content)
	{
		if (!$this->canRender())
		{
			return;
		}

		// Replace th' words matey
		$this->content = $content;
		$this->_array_apply_regexp();

		// Now do some random end of line substitutions / additions
		$this->_load_shouts();

		// End of sentence shout
		$this->chance = 5;
		$this->content = preg_replace_callback('%(\.\s)%', array($this, '_avast'), $this->content);
		$this->content = preg_replace_callback('%(\.$)%', array($this, '_avast'), $this->content);

		// Greater chance after Exclamation or Question Me hearties!
		$this->chance = 3;
		$this->content = preg_replace_callback('%([\?](?:\s|$))%', array($this, '_avast'), $this->content);
		$content = preg_replace_callback('%([\!](?:\s|$))%', array($this, '_avast'), $this->content);
	}

	/**
	 * Loads th' array of pattern => replacement so ye can talk like a Scallywag
	 */
	private function _load_patterns()
	{
		// Lots o word boundary checks
		$this->patterns = array(
			'%\bawesome\b%' => 'bountifully bombastic',
			'%\bdidn&#039;t know\b%' => 'did nay know',
			'%\bdidn&#039;t\b%' => 'di&#039;nae',
			'%\bdon&#039;t know\b%' => 'dinna',
			'%\bdon&#039;t\b%' => 'dern&#039;t',
			'%\bhadn&#039;t\b%' => 'ha&#039;nae',
			'%\bhaven&#039;t\b%' => 'ha&#039;nae',
			'%\bhe&#039;s\b%' => 'he be',
			'%\bit&#039;s\b%' => '&#039;tis',
			'%\bshe&#039;s\b%' => 'she be',
			'%\bwasn&#039;t\b%' => 'weren&#039;t',
			'%\b(\w+)&#039;s (\w+)ing\b%' => '%1 be %2in&#039;',
			'%\bairplane\b%' => 'flying machine',
			'%\bam\b%' => 'be',
			'%\bare\b%' => 'be',
			'%\baround\b%' => 'aroun&#039;',
			'%\basshat\b%' => 'Scallywag',
			'%\battack\b%' => 'pillage',
			'%\bbastard\b%' => 'Son of a Biscuit Eater',
			'%\bbeer\b%' => 'grogs',
			'%\bbetween\b%' => 'betwixt',
			'%\bbosses\b%' => 'cap&#039;ns',
			'%\bboss\b%' => 'admiral',
			'%\bboys\b%' => 'laddies',
			'%\bboy\b%' => 'lad',
			'%\bbrt\b%' => 'I&#039;ve already shoved off.',
			'%\bcars\b%' => 'boats',
			'%\bcar\b%' => 'boat',
			'%\bcents\b%' => 'shillings',
			'%\bcheat\b%' => 'hornswaggle ',
			'%\bchildren\b%' => 'little sandcrabs',
			'%\bcoin\b%' => 'pieces of eight',
			'%\bcogg\b%' => 'that pot &#039;o gold cogg',
			'%\bco[-]?workers\b%' => 'shipmates',
			'%\bco[-]?worker\b%' => 'shipmate',
			'%\bcrazy\b%' => 'boozled',
			'%\bdead\b%' => 'be in Davy&#039;s grip',
			'%\bdied\b%' => 'snuffed it',
			'%\bdie\b%' => 'be in Davy&#039;s grip',
			'%\bdo not\b%' => 'dern&#039;t',
			'%\bdollars\b%' => 'pieces of eight',
			'%\bdrive\b%' => 'sail',
			'%\bdriving\b%' => 'sailing',
			'%\bdrunk\b%' => 'Loaded to the Gunwales',
			'%\bdude\b%' => 'lubber',
			'%\bearlier\b%' => 'afore',
			'%\belkarte\b%i' => 'Pearl of th&#039; sea',
			'%\beggs\b%i' => 'cackle fruit',
			'%\beveryone\b%' => 'all ye scallywags',
			'%\bever\b%' => 'e&#039;er',
			'%\bfellow\b%' => 'lubber',
			'%\bflagged\b%' => 'black marked',
			'%\bflag\b%' => 'Jolly Roger',
			'%\bfood\b%' => 'chow',
			'%\bfor\b%' => 'fer',
			'%\bfriends\b%' => 'maties',
			'%\bfriend\b%' => 'matey',
			'%\bgay\b%' => 'Gentlemen o&#039; fortune',
			'%\bgirls\b%' => 'lassies',
			'%\bgirl\b%' => 'lass',
			'%\bgreat\b%' => 'bountifully bombastic',
			'%\bgold\b%' => 'doubloons',
			'%\bgonna\b%' => 'goin&#039; ta',
			'%\bguys\b%' => 'hearties',
			'%\bguy\b%' => 'lubber',
			'%\bher\.\b%' => 'that lovely lass',
			'%\bher\b%' => 'that comely wench',
			'%\bhey[a]?\b%' => 'ahoy',
			'%\bHey\b%' => 'Avast',
			'%\bHe\b%' => 'The ornery cuss',
			'%\bhighways\b%' => 'oceans',
			'%\bhighway\b%' => 'ocean',
			'%\bhim\.\b%' => 'that drunken sailor',
			'%\bhim\b%' => 'that scurvey dog',
			'%\bhit\b%' => 'flog',
			'%\bhiya\b%' => 'avast',
			'%\bi see\b%' => 'Indeed it be so!',
			'%\bidea\b%' => 'notion',
			'%\bidiotic\b%' => 'addled',
			'%\bing \b%' => 'in&#039; ',
			'%\binsane\b%' => 'boozled',
			'%\binterstate\b%' => 'high sea',
			'%\bis\b%' => 'be',
			'%\bjerk\b%' => 'Scurvy dog!',
			'%\bjet\b%' => 'flying machine',
			'%\bkids\b%' => 'minnows',
			'%\bkill\b%' => 'keelhaul',
			'%\blady\b%' => 'wench',
			'%\blet&#039;s\b%' => 'let us',
			'%\bloot\b%' => 'booty',
			'%\bmachine\b%' => 'contraption',
			'%\bmanager\b%' => 'admiral',
			'%\bman\b%' => 'lubber',
			'%\bmoney\b%' => 'dubloons',
			'%\bmyself\b%' => 'meself',
			'%\bmy\b%' => 'me',
			'%\bnever\b%' => 'no nay ne&#039;er!',
			'%\bno problem\b%' => 'it be no mattar',
			'%\bNo\b%' => 'Nay',
			'%\bocean\b%' => 'wide blue',
			'%\bof\b%' => 'o&#039;',
			'%\bold\b%' => 'auld',
			'%\bomw\b%' => 'I be comin&#039;',
			'%\bover\b%' => 'o&#039;er',
			'%\bpay attention\b%i' => 'Avast ye',
			'%\bpeople\b%' => 'scallywags',
			'%\bprobably\b%' => 'likely',
			'%\bquickly\b%' => 'smartly',
			'%\breminder\b%' => 'notice to keep a weather eye open matey',
			'%\brear\b%' => 'aft',
			'%\brear end\b%' => 'dungbie',
			'%\broads\b%' => 'seas',
			'%\broad\b%' => 'sea',
			'%\bsea\b%' => 'briny deep',
			'%\bShe\b%' => 'The winsome lass',
			'%\bsilver\b%' => 'pieces o&#039; eight',
			'%\bsmf\b%i' => 'Son of a Biscuit Eater',
			'%\bStop\b%' => 'Avast',
			'%\bstreets\b%' => 'rivers',
			'%\bstreet\b%' => 'river',
			'%\bstupid\b%' => 'bamboozled',
			'%\bSUV\b%i' => 'ship',
			'%\brv\b%i' => 'great, grand ship',
			'%\bswmbo\b%' => 'admiral',
			'%\bsword\b%' => 'cutlass',
			'%\btarget\b%' => 'Swaggy',
			'%^[hH]i [tT]here%' => 'ahoy thar',
			'%\bthere\b%' => 'thar',
			'%\bthe\b%' => 'th&#039;',
			'%\bthx\b%' => 'such kindness! thank ye',
			'%\bto\b%' => 't&#039;',
			'%\btoilet\b%i' => 'terlet',
			'%\btrucks\b%' => 'schooners',
			'%\btruck\b%' => 'schooner',
			'%\bunderstand\b%' => 'reckon',
			'%\bwas\b%' => 'war',
			'%\bwhere\b%' => 'whar',
			'%\bwife\b%' => 'lady',
			'%\bwine\b%' => 'mead',
			'%\bwoman\b%' => 'wench',
			'%\bwomen\b%' => 'wenches',
			'%\byeah\b%' => 'aye',
			'%\bYeah\b%' => 'Aye',
			'%\bYes\b%' => 'Aye',
			'%\byou&#039;re\b%' => 'yar',
			'%\byour\b%' => 'yer',
			'%\b[tT]hank [yY]ou\b%' => 'i be in yer debt',
			'%\byou\b%' => 'ye',
			'%\b[Cc]aptain\b%' => 'Cap&#039;n',
			'%\b[gG]oogl%' => 'ghhhhhhhhhgl',
			'%\b[hH]ello\b%' => 'ahoy',
			'%\b[iI]&#039;m\b%' => 'i be',
			'%\b[tT]hanks\b%' => 'ye have me gratitude',
			'%\b^he\b%' => '&#039;e',
			'%ings\b%' => 'in&#039;s',
			'%ing\b%' => 'in&#039;',
		);
	}

	/**
	 * Loads th' array of pattern => replacement so ye can talk like a Scallywag
	 */
	private function _load_shouts()
	{
		$this->shouts = array(
			', avast~~1~~',
			'~~1~~ Ahoy!',
			'~~1~~ Blimey! ',
			'~~1~~ Me hearties! ',
			', and a bottle of rum!',
			', by Blackbeard&#039;s sword~~1~~',
			', by Davy Jones&#039; locker~~1~~',
			'~~1~~ Walk the plank!',
			'~~1~~ Aarrr!',
			'~~1~~ Yaaarrrrr!',
			', pass the grog!',
			', and dinna spare the whip!',
			', with a chest full of booty~~1~~',
			', and a bucket o&#039; chum~~1~~',
			', we&#039;ll keel-haul ye!',
			'~~1~~ Shiver me timbers!',
			'~~1~~ And hoist the mainsail!',
			'~~1~~ And swab the deck!',
			', ye scurvey dog~~1~~',
			'~~1~~ Fire the cannons!',
			', to be sure~~1~~',
			', I&#039;ll warrant ye~~1~~',
			', on a dead man&#039;s chest!',
			'~~1~~ Load the cannons!',
			'~~1~~ Prepare to be boarded!',
			'~~1~~ Ye&#039;ll be sleepin&#039; with the fishes!',
			'~~1~~ The sharks will eat well tonight!',
			'~~1~~ Oho!',
			'~~1~~ Fetch me spyglass!',
		);

		shuffle($this->shouts);
	}

	/**
	 * This function takes an array of pattern => replacement pairs
	 * and applies them all to the string.
	 */
	private function _array_apply_regexp()
	{
		// Extract the values:
		$keys = array_keys($this->patterns);
		$values = array_values($this->patterns);

		// Replace the words:
		$this->content = preg_replace($keys, $values, $this->content);
	}

	/**
	 * Support function for pre_replace_callback
	 *
	 * @param array $matches
	 * @return string
	 */
	private function _avast($matches)
	{
		// Use and consume or do nothing, its all chance
		return (((1 === mt_rand(1, $this->chance)) ? str_replace('~~1~~', $matches[0], array_shift($this->shouts)) : $matches[0]) . ' ');
	}

	/**
	 * Retrieve the sole instance of this class.
	 *
	 * @return Pirate_Talk
	 */
	public static function instance()
	{
		if (self::$_pirate === null)
		{
			self::$_pirate = new \Pirate_Talk();
		}

		return self::$_pirate;
	}
}
