<?php
namespace Giftcards\ModRewriteBundle\Tests\Mock\Mockery\Matcher;

use Giftcards\ModRewrite\MatchState;
use Mockery\Matcher\MatcherAbstract;

class EqualsMatcher extends MatcherAbstract
{
	protected $constraint;
    /**
     * @var bool
     */
    private $throw;


    /**
	 * @param string $expected
	 */
	public function __construct($expected, $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false, $throw = false) {

		$this->constraint = new \PHPUnit_Framework_Constraint_IsEqual(
				$expected, $delta, $maxDepth, $canonicalize, $ignoreCase
		);
        $this->throw = $throw;
    }

	/**
	 * @param unkown $actual
	 */
	public function match(&$actual) {

		try {
			
			$this->constraint->evaluate($actual);
			return true;
		} catch (\PHPUnit_Framework_AssertionFailedError $e) {
		    if ($this->throw) {
		        throw $e;
            }
			return false;
		}
	}
	
	/**
	 * 
	 */
	public function __toString() {

		return '<Equals>';
	}
}
