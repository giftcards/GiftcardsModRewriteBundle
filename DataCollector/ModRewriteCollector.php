<?php
namespace Giftcards\ModRewriteBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ModRewriteCollector extends DataCollector
{
	public function getName()
	{
		return 'mod_rewrite';
	}

	public function collect(Request $request, Response $response, \Exception $exception = null)
	{
		$this->data['mod_rewrite_result'] = $request->attributes->get('mod_rewrite_result');
	}

	public function hasResult()
	{
		return (bool)$this->data['mod_rewrite_result'];
	}

	public function getRule()
	{
		return $this->data['mod_rewrite_result']->getMatchedRule();
	}

	public function getNewUrl()
	{
		return $this->data['mod_rewrite_result']->getUrl();
	}

	public function getResult()
	{
		return $this->data['mod_rewrite_result'];
	}
}