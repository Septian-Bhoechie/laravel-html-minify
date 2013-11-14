<?php namespace Fitztrev\LaravelHtmlMinify;

use Illuminate\View\Compilers\BladeCompiler;

class LaravelHtmlMinifyCompiler extends BladeCompiler {

	private $config;

	public function __construct($config, $files, $cachePath) {
		parent::__construct($files, $cachePath);

		$this->config = $config;

		// Add Minify to the list of compilers
		if ( $this->config['enabled'] === true ) {
			$this->compilers[] = 'Minify';
		}
	}

	/**
	* We'll only compress a view if none of the following conditions are met.
	*
	* @param  string $value
	* @return bool
	*/
	public function shouldMinify($value) {
		if (
			preg_match('/<(pre|textarea)/', $value)                     || // <pre> or <textarea> tags
			preg_match('/<script[^\??>]*>[^<\/script>]/', $value)       || // Embedded javascript (opening <script> tag not immediately followed by </script>)
			preg_match('/value=("|\')(.*)([ ]{2,})(.*)("|\')/', $value)    // Value attribute that contains 2 or more adjacent spaces
		) {
			return false;
		} else {
			return true;
		}
	}

	/**
	* Compress the HTML output before saving it
	*
	* @param  string  $value
	* @return string
	*/
	protected function compileMinify($value)
	{
		if ( $this->shouldMinify($value) ) {
			$replace = array(
				'/<!--[^\[](.*?)[^\]]-->/s' => '',       // HTML comments (except IE conditional comments)
				"/<\?php/"                  => '<?php ', // Opening PHP tags
				"/\n/"                      => '',       // New lines
				"/\r/"                      => '',       // Carriage returns
				"/\t/"                      => ' ',      // Tabs
				"/ +/"                      => ' ',      // Multiple spaces
			);
			return preg_replace(array_keys($replace), array_values($replace), $value);
		} else {
			return $value;
		}

	}

}
