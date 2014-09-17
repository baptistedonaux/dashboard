<?php

namespace Dashboard\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Elasticsearch;

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
		
		$params = array();
		$params['index'] = 'gmail';
		$params['type']  = 'mail';
		$params['body']['query']['filtered']['filter']['query']["match"]["flags"] = 'Seen';

		$results = $client->search($params);

        return array("mail_read" => $results['hits']['total']);
    }
}
