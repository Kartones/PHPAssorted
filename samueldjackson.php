<?php

/**
 * HackMeUp quick project: PHP File analyzer using PHP´s own token parser
 * Purpose was to learn how PHP token_get_all() works, so nothing fancy here
 *
 * @author diego@tuenti.com
 *
 * Notes:
 * - Absolutely no error checking
 * - Scope of methods/classes is stored in the "token buffer" but nothing is done with it.
 *   Could be interesting to complain about missing scopes.
 * - Line numbers could be used among with detecting closing curly brackets (if present, null id and inside token text fragment).
 *   Complaining about too long methods would be possible then.
 * - Detecting missing author tags on methods and/or classes could be easy to achieve too by storing comment block content and not just id.
 */
class SamuelDocumenterJackson {

	private $DEBUG = FALSE;
	private $filePath = "";
	private $commentsCount = 0;
	private $constantsCount = 0;
	private $functionsCount = 0;
	private $classesCount = 0;
	private $undocumentedConstants = 0;
	private $undocumentedFunctions = 0;
	private $undocumentedClasses = 0;

	/**
	 * Class constructor
	 *
	 * @param string $filePath
	 * @param bool $debug
	 */
	public function __construct($filePath, $debug) {
		$this->filePath = $filePath;
		$this->DEBUG = $debug;
	}

	/**
	 * Parses all code, sets stats data
	 */
	public function parse() {
		$source = file_get_contents($this->filePath);
		$tokens = token_get_all($source);
		$tokenBuffer = array();

		foreach ($tokens as $token)
		{
			// No use for the text, just token id and line num if present
			$id = is_numeric($token[0]) ? $token[0] : NULL;
			//$text = isset($token[1]) ? $token[1] : '';
			$lineNum = isset($token[2]) ? " (" . $token[2] . ")" : '';

			switch($id) {
				case T_ABSTRACT:
				case T_PUBLIC:
				case T_PROTECTED:
				case T_PRIVATE:
					$tokenBuffer[] = $id;
				break;
				case T_DOC_COMMENT:
				case T_COMMENT:
					$this->commentsCount++;
					$tokenBuffer[] = $id;
				break;
				case T_CLASS:
				case T_INTERFACE:
					$this->classesCount++;
					if (!in_array(T_DOC_COMMENT, $tokenBuffer) && !in_array(T_COMMENT, $tokenBuffer)) {
						$this->undocumentedClasses++;
					}
					$tokenBuffer = array();
				break;
				case T_CONST:
					$this->constantsCount++;
					if (!in_array(T_DOC_COMMENT, $tokenBuffer) && !in_array(T_COMMENT, $tokenBuffer)) {
						$this->undocumentedConstants++;
					}
					$tokenBuffer = array();
				break;
				case T_FUNCTION:
					$this->functionsCount++;
					if (!in_array(T_DOC_COMMENT, $tokenBuffer) && !in_array(T_COMMENT, $tokenBuffer)) {
						$this->undocumentedFunctions++;
					}
					$tokenBuffer = array();
				break;
				// String tokens have NULL id
				case NULL:
				case T_WHITESPACE:
					// Do nothing
				break;
				default:
					$tokenBuffer = array();
				break;
			}

			if ($this->DEBUG) {
				switch ($id) {
					case NULL:
					case T_WHITESPACE:
						// Don't output
					break;
					default:
						echo token_name($id) . $lineNum . "\n";
					break;
				}
			}
		}
	}

	/**
	 * Echo results of the analysis and more stats if desired
	 */
	public function showStats() {
		echo "\n\nSamuel Documenter Jackson says:\n";

		if ($this->classesCount > 1) {
			echo "Don't define more than one class per file motherfucker!\n";
		}
		if ($this->undocumentedClasses > 0) {
			echo "You have " . $this->undocumentedClasses . " undocumented classes/interfaces motherfucker!\n";
		}
		if ($this->undocumentedFunctions > 0) {
			echo "You have " . $this->undocumentedFunctions . " undocumented functions motherfucker!\n";
		}
		if ($this->undocumentedConstants > 0) {
			echo "You have " . $this->undocumentedConstants . " undocumented constants motherfucker!\n";
		}

		if ($this->classesCount === 1 && $this->undocumentedClasses === 0 && $this->undocumentedFunctions === 0 &&
			$this->undocumentedConstants === 0) {
			echo "The code looks good, nice work motherfucker!\n";
		}

		if ($this->DEBUG) {
			echo "\nSome stats:\n";
			echo "Classes/Interfaces: " . $this->classesCount . "\n";
			echo "Functions: " . $this->functionsCount . "\n";
			echo "Constants: " . $this->constantsCount . "\n";
			echo "Comments: " . $this->commentsCount . "\n\n";
		}
	}
}

// Instantiate with command line arguments and do the work
$parser = new SamuelDocumenterJackson($argv[1], isset($argv[2]) ? $argv[2] : FALSE);
$parser->parse();
$parser->showStats();