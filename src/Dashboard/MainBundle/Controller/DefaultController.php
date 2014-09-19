<?php

namespace Dashboard\MainBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Elasticsearch;
use Elasticsearch\Common\Exceptions\RuntimeException;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template("DashboardMainBundle:Default:index.html.twig")
     */
    public function index()
    {
    	$params = array();
		$params['hosts'] = array (
		    '127.0.0.1:9200'
		);

		$client = new Elasticsearch\Client($params);
		
		$params = array(
			"index" => "dash-mail-*",
			"type" => "mail",
			"body" => array(
				"query" => array(
					"filtered" => array(
						"filter" => array(
							"bool" => array(
								"must" => array(
									array(
										"missing" => array(
											"field" => "flags"
											)
										),
									array(
										"term" => array(
											"folderFullName" => "INBOX"
											)
										)
									)
								)
							)
						)
					)
				)
			);

		$results_mail = $client->search($params);
		
		$params = array(
			"index" => "dash-rss-*",
			"type" => "page",
			);

		$results_rss = $client->count($params);
		
		$params = array(
			"index" => "dash-twitter-*",
			"type" => "status",
			);

		$results_twitter = $client->count($params);

        return array("mail_unread" => $results_mail['hits']['total'], "rss_total" => $results_rss['count'], "twitter_total" => $results_twitter['count']);
    }

    /**
     * @Route("/ajax/{type}/{action}", name="ajax", requirements={"type" = "mail|rss|twitter", "action" = "unread|total"})
     */
    public function ajax($type, $action) {
		$client = new Elasticsearch\Client(array("hosts" => array("127.0.0.1:9200")));
		$method = null;
		$params = array("index" => "dash-".$type."-*");

		switch ($type) {
			case "rss":
				$params["type"] = "page";
				break;
				
			case "twitter":
				$params["type"] = "status";
				break;

			default:
				$params["type"] = $type;
		}

    	switch ($action) {
    		case "total":
    			$method = "count";
    			break;

    		case "unread":
    			$method = "search";

    			switch ($type) {
					case "mail":
    					$params["body"] = array("query" => array("filtered" => array("filter" => array("bool" => array("must" => array(array("missing" => array("field" => "flags")), array("term" => array("folderFullName" => "INBOX"))))))));
						break;

					default:
						throw new RuntimeException("Not implemented for /".$type."/".$action);
				}
    			break;
    	}

    	$response = $client->{$method}($params);


    	return new JsonResponse($response);
    }
}
